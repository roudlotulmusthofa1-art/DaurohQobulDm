<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\PesanAsatid;
use App\Models\WaktuPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman-halaman akun asatid (role "asatid").
 * Beranda berisi kartu menu; tiap kartu membuka halamannya sendiri.
 * Semua halaman memakai layout resources/views/layouts/asatid.blade.php.
 */
class AsatidDashboardController extends Controller
{
    /** Beranda: kartu menu */
    public function index()
    {
        return $this->tampil('asatid.dashboard', [
            'jumlahPeserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->count(),
            // Jumlah jam mengajar per minggu (Akademik > Jadwal Mengajar, bulan aktif)
            'jumlahJam'     => self::jadwalSaya()->count(),
            'jumlahPesanBaru' => PesanAsatid::jumlahBaru(Auth::id()),
            'jumlahPesanSaya' => PesanAsatid::where('user_id', Auth::id())->dariAsatid()->count(),
        ]);
    }

    public function profil()
    {
        return $this->tampil('asatid.profil');
    }

    /** Menu "Ganti Password": asatid mengganti password sendiri (butuh password lama) */
    public function gantiPassword()
    {
        return $this->tampil('asatid.ganti-password');
    }

    public function simpanGantiPassword(Request $request)
    {
        \App\Support\GantiPasswordSendiri::simpan($request, PendaftaranAsatid::where('user_id', Auth::id())->first());

        return redirect()->route('asatid.ganti-password')->with('password_diganti', true);
    }

    /** Daftar peserta (santri yang sudah diterima), bisa dicari & disaring per kelas */
    public function peserta(Request $request)
    {
        $peserta = PendaftaranSiswa::query()
            ->with(['kelasMaster', 'kamarMaster'])
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->when($request->q, fn ($q, $kata) => $q->where(fn ($w) => $w
                ->where('nama_lengkap', 'like', "%{$kata}%")
                ->orWhere('nama_lengkap_arab', 'like', "%{$kata}%")
                ->orWhere('nomor_pendaftaran', 'like', "%{$kata}%")))
            ->when($request->kelas, fn ($q, $k) => $q->where('kelas_id', $k))
            ->orderBy('nama_lengkap')
            ->paginate(12)
            ->withQueryString();

        $kelasList = Kelas::where('is_active', true)->ordered()->get(['id', 'nama']);

        return $this->tampil('asatid.peserta', compact('peserta', 'kelasList'));
    }

    /** Detail lengkap satu peserta, hanya untuk dilihat (tidak bisa diubah) */
    public function pesertaShow(PendaftaranSiswa $siswa)
    {
        abort_unless($siswa->status === PendaftaranSiswa::DITERIMA, 404);

        $siswa->load(['kelasMaster', 'kamarMaster']);

        // Rekap absensi peserta ini: ['hadir' => 3, 'terlambat' => 1, ...]
        $jumlah = \App\Models\AbsensiDetail::where('pendaftaran_siswa_id', $siswa->id)
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $absensi = collect(\App\Models\Absensi::STATUS)->map(fn ($s, $k) => (int) ($jumlah[$k] ?? 0))->all();

        return $this->tampil('asatid.peserta-detail', compact('siswa', 'absensi'));
    }

    public function jadwal()
    {
        return $this->tampil('asatid.jadwal', [
            'bulan'     => BulanAjaran::aktif(),
            'jadwal'    => self::jadwalSaya()->load(['kelas', 'kitab.bidangIlmu', 'waktu']),
            'jamList'   => WaktuPelajaran::where('is_active', true)->ordered()->get(),
        ]);
    }

    /** Pesan dari Admin (kotak masuk). Pesan yang tampil otomatis ditandai sudah dibaca. */
    public function pesan()
    {
        $pesan = PesanAsatid::with('pengirim:id,name')->where('user_id', Auth::id())->dariAdmin()->latest('id')->paginate(15);

        $baru = $pesan->getCollection()->whereNull('dibaca_at')->pluck('id');
        // Saat admin "Masuk sebagai" (hanya melihat), pesan tidak ditandai dibaca
        if ($baru->isNotEmpty() && ! \App\Support\MasukSebagai::aktif()) {
            PesanAsatid::whereIn('id', $baru)->update(['dibaca_at' => now()]);
        }

        return $this->tampil('asatid.pesan', ['pesan' => $pesan, 'idBaru' => $baru->all()]);
    }

    /** Pesan untuk Admin: tulis pesan + riwayat pesan beserta balasan admin */
    public function pesanSaya()
    {
        $pesan = PesanAsatid::where('user_id', Auth::id())->dariAsatid()->with('balasan')->latest('id')->paginate(10);

        return $this->tampil('asatid.pesan-saya', compact('pesan'));
    }

    public function kirimPesan(Request $request)
    {
        $data = $request->validate([
            'judul' => ['nullable', 'string', 'max:150'],
            'isi'   => ['required', 'string', 'max:2000'],
        ], ['isi.required' => 'Tulis pesan Anda terlebih dahulu.', 'isi.max' => 'Pesan maksimal 2000 karakter.']);

        PesanAsatid::create($data + ['user_id' => Auth::id(), 'dari' => PesanAsatid::DARI_ASATID]);

        return redirect()->route('asatid.pesan-saya')->with('success', 'Pesan terkirim ke admin. Balasan akan muncul di halaman ini.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** Data yang dipakai semua halaman asatid (profil singkat + notifikasi) */
    private function tampil(string $view, array $data = [])
    {
        $user = Auth::user();
        $asatid = PendaftaranAsatid::where('user_id', $user->id)->first();

        return view($view, $data + [
            'user'    => $user,
            'asatid'  => $asatid,
            'notif'   => self::notifikasi($asatid),
        ]);
    }

    /**
     * Notifikasi untuk asatid (lonceng di kanan atas).
     * Dihitung dari data yang ada, jadi hilang sendiri kalau sudah tidak relevan.
     */
    /** Jadwal mengajar milik asatid yang login, di bulan ajaran aktif */
    public static function jadwalSaya()
    {
        $bulan = BulanAjaran::aktif();

        return $bulan
            ? JadwalMengajar::where('bulan_ajaran_id', $bulan->id)->where('user_id', Auth::id())->get()
            : new \Illuminate\Database\Eloquent\Collection(); // tetap koleksi Eloquent agar ->load() tidak error
    }

    public static function notifikasi(?PendaftaranAsatid $asatid): array
    {
        $items = [];

        // Pesan dari admin yang belum dibaca (paling atas)
        if ($pesanBaru = PesanAsatid::jumlahBaru(Auth::id())) {
            $items[] = [
                'kunci' => 'pesan_admin:' . PesanAsatid::where('user_id', Auth::id())->dariAdmin()->belumDibaca()->max('id'),
                'teks'  => "Ada <b>{$pesanBaru} pesan baru</b> dari admin",
                'ket'   => 'Buka Pesan dari Admin',
                'url'   => route('asatid.pesan'),
                'warna' => 'bg-violet-50 text-violet-600 ring-violet-100',
                'ikon'  => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
            ];
        }

        // Peserta baru yang diterima 7 hari terakhir
        $baru = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->where('diproses_at', '>=', now()->subDays(7))->count();
        if ($baru > 0) {
            $items[] = [
                'kunci' => 'peserta_baru:' . $baru . ':' . PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->max('id'),
                'teks'  => "<b>{$baru}</b> peserta baru diterima minggu ini",
                'ket'   => 'Lihat di Daftar Peserta',
                'url'   => route('asatid.peserta'),
                'warna' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                'ikon'  => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            ];
        }

        // Catatan dari admin saat menerima pendaftaran
        if ($asatid?->catatan_admin) {
            $items[] = [
                'kunci' => 'catatan_admin:' . crc32($asatid->catatan_admin),
                'teks'  => 'Catatan dari admin: <b>' . \App\Support\TeksAman::e(\Illuminate\Support\Str::limit($asatid->catatan_admin, 80)) . '</b>',
                'ket'   => optional($asatid->diproses_at)->locale('id')->diffForHumans(),
                'url'   => route('asatid.profil'),
                'warna' => 'bg-amber-50 text-amber-600 ring-amber-100',
                'ikon'  => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
            ];
        }

        // Jadwal mengajar (Akademik > Jadwal Mengajar, bulan aktif)
        $jadwal = self::jadwalSaya();
        $hariIni = $jadwal->where('hari', now('Asia/Jakarta')->dayOfWeekIso)->count();
        $items[] = [
            'kunci' => 'jadwal:' . now('Asia/Jakarta')->toDateString() . ':' . ($jadwal->isEmpty() ? 'kosong' : $hariIni), // muncul lagi tiap hari
            'teks'  => $jadwal->isEmpty()
                ? 'Jadwal mengajar Anda <b>belum diatur</b> oleh admin'
                : ($hariIni ? "Hari ini Anda mengajar <b>{$hariIni} jam</b>" : 'Hari ini <b>tidak ada</b> jadwal mengajar'),
            'ket'   => 'Lihat Jadwal Pelajaran',
            'url'   => route('asatid.jadwal'),
            'warna' => 'bg-sky-50 text-sky-600 ring-sky-100',
            'ikon'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        ];

        return $items;
    }
}
