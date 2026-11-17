<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\PesanAsatid;
use App\Models\PesanPeserta;
use App\Support\NamaArab;
use App\Support\NotifikasiAdmin;
use App\Support\SesiAplikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Admin > Dashboard. Semua angka diambil dari data sesi aktif dan tiap kartu menuju menunya.
 * Kotak "Pesan Peserta": daftar percakapan + baca & balas langsung (?pesan={id peserta}).
 *
 * Dua tampilan dengan data yang sama:
 * - admin/dashboard.blade.php     : laptop/komputer
 * - admin/dashboard-hp.blade.php  : HP (seperti aplikasi, menu di bawah)
 * Dipilih otomatis dari jenis perangkat; admin bisa berganti manual (diingat lewat cookie "tampilan_dasbor").
 */
class DashboardController extends Controller
{
    /** Nama cookie pilihan tampilan: "hp" atau "laptop" */
    private const COOKIE_TAMPILAN = 'tampilan_dasbor';

    public function index(Request $request): View
    {
        if ($this->pakaiTampilanHp($request)) {
            // versi HP membuka percakapan di menu Kotak Pesan (tidak ada percakapan terbuka, pesan tidak ditandai dibaca)
            return view('admin.dashboard-hp', $this->data($request, false));
        }

        return view('admin.dashboard', $this->data($request, true));
    }

    /** GET /admin/beranda/tampilan/{mode} — tombol "Tampilan HP" / "Tampilan laptop" */
    public function tampilan(string $mode)
    {
        abort_unless(in_array($mode, ['hp', 'laptop'], true), 404);

        return redirect()->route('admin.dashboard')
            ->withCookie(cookie(self::COOKIE_TAMPILAN, $mode, 60 * 24 * 365)); // diingat 1 tahun di perangkat ini
    }

    /** Pilihan manual (cookie) didahulukan; kalau belum pernah memilih, dilihat dari jenis perangkat */
    private function pakaiTampilanHp(Request $request): bool
    {
        $pilihan = $request->cookie(self::COOKIE_TAMPILAN);
        if (in_array($pilihan, ['hp', 'laptop'], true)) {
            return $pilihan === 'hp';
        }

        return (bool) preg_match('/Mobi|Android|iPhone|iPod/i', (string) $request->userAgent());
    }

    /** Semua data dashboard (dipakai kedua tampilan); $bukaPesan = buka satu percakapan di kotak pesan (laptop) */
    private function data(Request $request, bool $bukaPesan): array
    {
        $hariIni = now('Asia/Jakarta');

        // ----- Angka utama -----
        $status = PendaftaranSiswa::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');
        $pendaftarMingguIni = PendaftaranSiswa::where('created_at', '>=', $hariIni->copy()->subDays(7))->count();
        $asatid = PendaftaranAsatid::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');

        // Kehadiran 30 hari terakhir (hadir + terlambat dibanding semua catatan)
        $absen = AbsensiDetail::whereHas('absensi', fn ($q) => $q->where('tanggal', '>=', $hariIni->copy()->subDays(30)->toDateString()))
            ->selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');
        $totalAbsen = $absen->sum();
        $hadir = ($absen['hadir'] ?? 0) + ($absen['terlambat'] ?? 0);

        // Jadwal mengajar hari ini: panel Pergantian Pelajaran (Admin\PergantianPelajaranController::susun)
        $bulan = BulanAjaran::aktif();

        // ----- Kelas & kamar -----
        $diterima = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA);
        $kelas = Kelas::ordered()->get()->map(fn ($k) => ['nama' => $k->nama, 'isi' => (clone $diterima)->where('kelas_id', $k->id)->count()]);
        $kamar = Kamar::ordered()->withCount(['santri as isi' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get();

        return [
            'user' => $request->user(),
            'sesi' => SesiAplikasi::aktif(),
            'hariIni' => $hariIni,
            'hijriyah' => Absensi::hijriyah($hariIni->toDateString()),
            'angka' => [
                'diterima' => (int) ($status[PendaftaranSiswa::DITERIMA] ?? 0),
                'menunggu' => (int) ($status[PendaftaranSiswa::MENUNGGU] ?? 0),
                'ditolak' => (int) ($status[PendaftaranSiswa::DITOLAK] ?? 0),
                'pendaftar' => (int) $status->sum(),
                'pendaftar_minggu_ini' => $pendaftarMingguIni,
                'asatid' => (int) ($asatid[PendaftaranAsatid::DITERIMA] ?? 0),
                'asatid_menunggu' => (int) ($asatid[PendaftaranAsatid::MENUNGGU] ?? 0),
                'kehadiran' => $totalAbsen ? round($hadir / $totalAbsen * 100) : null,
                'catatan_absen' => $totalAbsen,
                'absensi_30' => Absensi::where('tanggal', '>=', $hariIni->copy()->subDays(30)->toDateString())->count(),
                'belum_kelas' => (clone $diterima)->whereNull('kelas_id')->count(),
                'belum_kamar' => (clone $diterima)->whereNull('kamar_id')->count(),
            ],
            'bulan' => $bulan,
            'absensiTerbaru' => Absensi::with(['kelas', 'pelajaran', 'kitab', 'kegiatan', 'pengabsen:id,name'])->withCount('detail')->latest('tanggal')->latest('id')->take(5)->get(),
            'kelas' => $kelas,
            'kamar' => $kamar,
            'tindakan' => NotifikasiAdmin::kumpulkan(), // lembar Notifikasi di tampilan HP
        ] + $this->kotakPesan($request, $bukaPesan);
    }

    /**
     * Data kotak pesan Dashboard: percakapan SISWA (PesanPeserta) dan ASATID (PesanAsatid) dalam satu daftar,
     * yang belum dibaca di atas, lalu yang terbaru. Percakapan dibuka lewat ?pesan={id peserta} / ?asatid={user id};
     * tanpa pilihan, percakapan paling atas langsung terbuka (tidak ada lagi "Pilih percakapan di sebelah kiri").
     * Tiap percakapan: jenis (siswa|asatid), id, nama, arab, foto, status (siswa), terakhir {dari_admin, teks, waktu}, belum.
     */
    private function kotakPesan(Request $request, bool $buka = true): array
    {
        $percakapan = collect();

        if (Schema::hasTable('pesan_peserta')) {
            $ringkas = PesanPeserta::query()
                ->selectRaw('pendaftaran_siswa_id, MAX(id) AS terakhir_id, SUM(CASE WHEN dari = ? AND dibaca_at IS NULL THEN 1 ELSE 0 END) AS belum', [PesanPeserta::DARI_PESERTA])
                ->groupBy('pendaftaran_siswa_id')
                ->orderByRaw('belum > 0 DESC')->orderByDesc('terakhir_id')
                ->take(10)->get();
            $terakhir = PesanPeserta::whereIn('id', $ringkas->pluck('terakhir_id'))->get()->keyBy('id');
            $peserta = PendaftaranSiswa::whereIn('id', $ringkas->pluck('pendaftaran_siswa_id'))->get(['id', 'nama_lengkap', 'nama_lengkap_arab', 'nomor_pendaftaran', 'status', 'berkas_foto'])->keyBy('id');
            foreach ($ringkas as $r) {
                $p = $peserta[$r->pendaftaran_siswa_id] ?? null;
                $t = $terakhir[$r->terakhir_id] ?? null;
                if ($p && $t) {
                    $percakapan->push((object) [
                        'jenis' => 'siswa', 'id' => $p->id, 'nama' => $p->nama_lengkap, 'arab' => $p->nama_lengkap_arab,
                        'foto' => $p->foto_url, 'status' => $p->status, 'label_status' => $p->label_status,
                        'terakhir' => (object) ['dari_admin' => $t->dari === PesanPeserta::DARI_ADMIN, 'teks' => $t->judul ?: $t->isi, 'waktu' => $t->created_at],
                        'belum' => (int) $r->belum,
                    ]);
                }
            }
        }

        if (Schema::connection('pusat')->hasColumn('pesan_asatid', 'dari')) {
            $ringkas = PesanAsatid::query()
                ->selectRaw('user_id, MAX(id) AS terakhir_id, SUM(CASE WHEN dari = ? AND dibaca_at IS NULL THEN 1 ELSE 0 END) AS belum', [PesanAsatid::DARI_ASATID])
                ->whereIn('user_id', PesanAsatid::dariAsatid()->select('user_id'))
                ->groupBy('user_id')
                ->orderByRaw('belum > 0 DESC')->orderByDesc('terakhir_id')
                ->take(10)->get();
            $terakhir = PesanAsatid::whereIn('id', $ringkas->pluck('terakhir_id'))->get()->keyBy('id');
            $asatid = PendaftaranAsatid::whereIn('user_id', $ringkas->pluck('user_id'))->get()->keyBy('user_id');
            foreach ($ringkas as $r) {
                $a = $asatid[$r->user_id] ?? null;
                $t = $terakhir[$r->terakhir_id] ?? null;
                if ($a && $t) {
                    $percakapan->push((object) [
                        'jenis' => 'asatid', 'id' => (int) $r->user_id, 'nama' => $a->nama_lengkap, 'arab' => $a->nama_lengkap_arab,
                        'foto' => $a->foto_url, 'status' => null, 'label_status' => null,
                        'terakhir' => (object) ['dari_admin' => $t->dari === PesanAsatid::DARI_ADMIN, 'teks' => $t->judul ?: $t->isi, 'waktu' => $t->created_at],
                        'belum' => (int) $r->belum,
                    ]);
                }
            }
        }

        // Belum dibaca di atas, lalu pesan terbaru
        $percakapan = $percakapan->sortBy([fn ($a, $b) => ($b->belum > 0) <=> ($a->belum > 0), fn ($a, $b) => $b->terakhir->waktu <=> $a->terakhir->waktu])
            ->take(12)->values();

        // Percakapan yang dibuka: pilihan admin, atau yang paling atas
        $dibuka = match (true) {
            ! $buka => null,
            (bool) $request->query('pesan') => $percakapan->first(fn ($c) => $c->jenis === 'siswa' && $c->id === (int) $request->query('pesan')),
            (bool) $request->query('asatid') => $percakapan->first(fn ($c) => $c->jenis === 'asatid' && $c->id === (int) $request->query('asatid')),
            default => $percakapan->first(),
        };

        $isi = collect();
        if ($dibuka) {
            if ($dibuka->jenis === 'siswa') {
                PesanPeserta::where('pendaftaran_siswa_id', $dibuka->id)->dariPeserta()->belumDibaca()->update(['dibaca_at' => now()]);
                $isi = PesanPeserta::where('pendaftaran_siswa_id', $dibuka->id)->with('pengirim:id,name')->latest('id')->take(30)->get()
                    ->map(fn ($p) => (object) ['dari_admin' => $p->dari === PesanPeserta::DARI_ADMIN, 'judul' => $p->judul, 'isi' => $p->isi,
                        'waktu' => $p->created_at, 'pengirim' => $p->pengirim?->name, 'pengirim_id' => $p->user_id, 'dibaca' => (bool) $p->dibaca_at]);
            } else {
                PesanAsatid::where('user_id', $dibuka->id)->dariAsatid()->belumDibaca()->update(['dibaca_at' => now()]);
                $isi = PesanAsatid::where('user_id', $dibuka->id)->with('pengirim:id,name')->latest('id')->take(30)->get()
                    ->map(fn ($p) => (object) ['dari_admin' => $p->dari === PesanAsatid::DARI_ADMIN, 'judul' => $p->judul, 'isi' => $p->isi,
                        'waktu' => $p->created_at, 'pengirim' => $p->pengirim?->name, 'pengirim_id' => $p->pengirim_id, 'dibaca' => (bool) $p->dibaca_at]);
            }
            $isi = $isi->reverse()->values();
            NamaArab::untukUser($isi->pluck('pengirim_id')); // nama Arab admin pengirim, sekali kueri
            $dibuka->belum = 0;
        }

        $belumSiswa = Schema::hasTable('pesan_peserta') ? PesanPeserta::dariPeserta()->belumDibaca()->count() : 0;
        $belumAsatid = Schema::connection('pusat')->hasColumn('pesan_asatid', 'dari') ? PesanAsatid::dariAsatid()->belumDibaca()->count() : 0;

        return [
            'percakapan' => $percakapan,
            'dibuka' => $dibuka,
            'isiPercakapan' => $isi,
            'pesanBelum' => $belumSiswa + $belumAsatid,
        ];
    }
}
