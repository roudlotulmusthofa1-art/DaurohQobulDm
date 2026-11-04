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
use App\Models\PesanPeserta;
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
            $request->query->remove('pesan'); // versi HP membuka percakapan di menu Pesan Peserta

            return view('admin.dashboard-hp', $this->data($request));
        }

        return view('admin.dashboard', $this->data($request));
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

    /** Semua data dashboard (dipakai kedua tampilan) */
    private function data(Request $request): array
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

        // ----- Jadwal mengajar hari ini (bulan ajaran aktif) -----
        $bulan = BulanAjaran::aktif();
        $jadwalHariIni = $bulan
            ? JadwalMengajar::with(['kelas', 'kitab', 'waktu', 'ustadz:id,name'])
                ->where('bulan_ajaran_id', $bulan->id)->where('hari', $hariIni->dayOfWeekIso)->get()
                ->sortBy(fn ($j) => [$j->waktu?->urutan ?? 99, $j->kelas?->urutan ?? 99])->values()
            : collect();
        $namaUstadz = PendaftaranAsatid::whereNotNull('user_id')->pluck('nama_lengkap', 'user_id');
        $arabUstadz = PendaftaranAsatid::whereNotNull('user_id')->pluck('nama_lengkap_arab', 'user_id'); // nama Arab ustadz

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
            'jadwalHariIni' => $jadwalHariIni,
            'namaUstadz' => $namaUstadz,
            'arabUstadz' => $arabUstadz,
            'pendaftarBaru' => PendaftaranSiswa::latest()->take(5)->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'status', 'berkas_foto', 'created_at']),
            'absensiTerbaru' => Absensi::with(['kelas', 'pelajaran', 'kegiatan', 'pengabsen:id,name'])->withCount('detail')->latest('tanggal')->latest('id')->take(5)->get(),
            'kelas' => $kelas,
            'kamar' => $kamar,
            'tindakan' => NotifikasiAdmin::kumpulkan(),
        ] + $this->kotakPesan($request);
    }

    /** Data kotak "Pesan Peserta": percakapan terbaru + percakapan yang sedang dibuka */
    private function kotakPesan(Request $request): array
    {
        if (! Schema::hasTable('pesan_peserta')) {
            return ['percakapan' => collect(), 'dibuka' => null, 'isiPercakapan' => collect(), 'pesanBelum' => 0];
        }

        $ringkas = PesanPeserta::query()
            ->selectRaw('pendaftaran_siswa_id, MAX(id) AS terakhir_id, SUM(CASE WHEN dari = ? AND dibaca_at IS NULL THEN 1 ELSE 0 END) AS belum', [PesanPeserta::DARI_PESERTA])
            ->groupBy('pendaftaran_siswa_id')
            ->orderByRaw('belum > 0 DESC')->orderByDesc('terakhir_id')
            ->take(8)->get();
        $terakhir = PesanPeserta::whereIn('id', $ringkas->pluck('terakhir_id'))->get()->keyBy('id');
        $peserta = PendaftaranSiswa::whereIn('id', $ringkas->pluck('pendaftaran_siswa_id'))->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'status', 'berkas_foto'])->keyBy('id');

        $percakapan = $ringkas->map(fn ($r) => (object) [
            'peserta' => $peserta[$r->pendaftaran_siswa_id] ?? null,
            'terakhir' => $terakhir[$r->terakhir_id] ?? null,
            'belum' => (int) $r->belum,
        ])->filter(fn ($p) => $p->peserta)->values();

        // Percakapan dibuka hanya bila diklik (?pesan=), supaya pesan tidak tertandai dibaca tanpa dilihat
        $idDibuka = (int) $request->query('pesan');
        $dibuka = $idDibuka ? PendaftaranSiswa::find($idDibuka) : null;
        $isi = collect();
        if ($dibuka) {
            $dibuka->pesan()->dariPeserta()->belumDibaca()->update(['dibaca_at' => now()]);
            $isi = $dibuka->pesan()->with('pengirim:id,name')->latest('id')->take(30)->get()->reverse()->values();
            $percakapan->each(function ($p) use ($dibuka) {
                if ($p->peserta->id === $dibuka->id) {
                    $p->belum = 0;
                }
            });
        }

        return [
            'percakapan' => $percakapan,
            'dibuka' => $dibuka,
            'isiPercakapan' => $isi,
            'pesanBelum' => PesanPeserta::dariPeserta()->belumDibaca()->count(),
        ];
    }
}
