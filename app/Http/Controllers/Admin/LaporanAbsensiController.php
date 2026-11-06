<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JenisKegiatan;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan > Laporan Absensi
 * Semua hasil absensi kelas & kegiatan dari asatid, dengan rekap dan kesimpulan.
 * "Kehadiran" = hadir + terlambat.
 */
class LaporanAbsensiController extends Controller
{
    public function index(Request $request)
    {
        Absensi::tutupYangLewat(); // supaya laporan sudah memuat Alfa dari kegiatan yang waktunya habis

        $f = $this->saringan($request);
        $sesi = $this->querySesi($f);

        $hitung = $this->hitungStatus(clone $sesi);
        $total = array_sum($hitung);

        $perKelas = $this->rekapKelompok(clone $sesi, 'kelas_id', Absensi::KELAS,
            Kelas::pluck('nama', 'id'));
        $perKegiatan = $this->rekapKelompok(clone $sesi, 'jenis_kegiatan_id', Absensi::KEGIATAN,
            JenisKegiatan::pluck('nama', 'id'));
        $perPeserta = $this->rekapPeserta(clone $sesi);
        $harian = $this->rekapHarian(clone $sesi);

        $perhatian = $perPeserta->filter(fn ($p) => $p['persen'] < 75 || $p['alfa'] >= 3)->take(8);

        // Tabel peserta: cari + halaman
        $kata = mb_strtolower(trim((string) $request->q));
        $tersaring = $kata === '' ? $perPeserta
            : $perPeserta->filter(fn ($p) => str_contains(mb_strtolower($p['nama']), $kata) || str_contains(mb_strtolower((string) $p['nomor']), $kata));
        $halaman = max((int) $request->query('hal', 1), 1);
        $tabelPeserta = new LengthAwarePaginator($tersaring->forPage($halaman, 15)->values(), $tersaring->count(), 15, $halaman,
            ['path' => $request->url(), 'pageName' => 'hal', 'query' => $request->except('hal')]);

        $daftarSesi = (clone $sesi)->with(['kelas', 'pelajaran', 'waktuPelajaran', 'kegiatan', 'pengabsen'])
            ->withCount('detail')
            ->withCount(['detail as hadir_count' => fn ($q) => $q->whereIn('status', ['hadir', 'terlambat'])])
            ->terbaru()
            ->paginate(10, ['*'], 'sesi')
            ->withQueryString();

        return view('admin.laporan-absensi.index', [
            'f' => $f,
            'hitung' => $hitung,
            'total' => $total,
            'persen' => $this->persen($hitung),
            'jumlahSesi' => (clone $sesi)->count(),
            'jumlahSesiKelas' => (clone $sesi)->where('jenis', Absensi::KELAS)->count(),
            'jumlahSesiKegiatan' => (clone $sesi)->where('jenis', Absensi::KEGIATAN)->count(),
            'perKelas' => $perKelas,
            'perKegiatan' => $perKegiatan,
            'perhatian' => $perhatian,
            'tabelPeserta' => $tabelPeserta,
            'jumlahPeserta' => $perPeserta->count(),
            'harian' => $harian,
            'daftarSesi' => $daftarSesi,
            'kesimpulan' => $this->kesimpulan($hitung, $perKelas, $perKegiatan, $perhatian, $perPeserta),
            'kelasList' => Kelas::ordered()->get(['id', 'nama']),
            'kegiatanList' => JenisKegiatan::ordered()->get(['id', 'nama']),
        ]);
    }

    public function show(Absensi $absensi)
    {
        $absensi->load(['kelas', 'pelajaran', 'waktuPelajaran', 'kegiatan', 'pengabsen', 'detail.siswa.kelasMaster']);
        $detail = $absensi->detail->sortBy(fn ($d) => $d->siswa?->nama_lengkap)->values();
        $hitung = collect(Absensi::STATUS)->map(fn ($s, $k) => $detail->where('status', $k)->count())->all();

        return view('admin.laporan-absensi.show', [
            'absensi' => $absensi,
            'detail' => $detail,
            'hitung' => $hitung,
            'persen' => $this->persen($hitung),
        ]);
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();

        return redirect()->route('admin.laporan-absensi.index')->with('success', 'Data absensi berhasil dihapus.');
    }

    /* =====================================================================
     |  Bantuan
     * ===================================================================== */

    private function saringan(Request $request): array
    {
        $tanggal = fn ($v, $bawaan) => rescue(fn () => $v ? Carbon::parse($v)->toDateString() : $bawaan, $bawaan, false);

        $dari = $tanggal($request->dari, now()->startOfMonth()->toDateString());
        $sampai = $tanggal($request->sampai, now()->toDateString());
        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'jenis' => in_array($request->jenis, [Absensi::KELAS, Absensi::KEGIATAN], true) ? $request->jenis : '',
            'kelas_id' => $request->integer('kelas_id') ?: null,
            'jenis_kegiatan_id' => $request->integer('jenis_kegiatan_id') ?: null,
            'q' => (string) $request->q,
        ];
    }

    private function querySesi(array $f): Builder
    {
        return Absensi::query()
            ->whereBetween('tanggal', [$f['dari'], $f['sampai']])
            ->when($f['jenis'], fn ($q, $j) => $q->where('jenis', $j))
            ->when($f['kelas_id'], fn ($q, $id) => $q->where('kelas_id', $id))
            ->when($f['jenis_kegiatan_id'], fn ($q, $id) => $q->where('jenis_kegiatan_id', $id));
    }

    /** Detail absensi dari sesi-sesi yang tersaring */
    private function detail(Builder $sesi)
    {
        return DB::table('absensi_detail as d')
            ->join('absensi as a', 'a.id', '=', 'd.absensi_id')
            ->whereIn('d.absensi_id', (clone $sesi)->select('absensi.id'));
    }

    /** ['hadir' => 10, 'terlambat' => 2, ...] (semua status selalu ada) */
    private function hitungStatus(Builder $sesi): array
    {
        $n = $this->detail($sesi)->selectRaw('d.status, count(*) as n')->groupBy('d.status')->pluck('n', 'status');

        return collect(Absensi::STATUS)->map(fn ($s, $k) => (int) ($n[$k] ?? 0))->all();
    }

    private function persen(array $hitung): ?int
    {
        $total = array_sum($hitung);

        return $total ? (int) round((($hitung['hadir'] ?? 0) + ($hitung['terlambat'] ?? 0)) / $total * 100) : null;
    }

    /** Rekap per kelas / per kegiatan, diurutkan dari kehadiran terendah */
    private function rekapKelompok(Builder $sesi, string $kolom, string $jenis, Collection $nama): Collection
    {
        $baris = $this->detail($sesi)->where('a.jenis', $jenis)->whereNotNull("a.$kolom")
            ->selectRaw("a.$kolom as kunci, d.status, count(*) as n, count(distinct a.id) as sesi")
            ->groupBy("a.$kolom", 'd.status')->get()->groupBy('kunci');

        $jumlahSesi = (clone $sesi)->where('jenis', $jenis)->whereNotNull($kolom)
            ->selectRaw("$kolom as kunci, count(*) as n")->groupBy($kolom)->pluck('n', 'kunci');

        return $baris->map(function ($rows, $kunci) use ($nama, $jumlahSesi) {
            $hitung = collect(Absensi::STATUS)->map(fn ($s, $k) => (int) ($rows->firstWhere('status', $k)->n ?? 0))->all();

            return ['nama' => $nama[$kunci] ?? 'Terhapus', 'sesi' => (int) ($jumlahSesi[$kunci] ?? 0), 'total' => array_sum($hitung), 'persen' => $this->persen($hitung)] + $hitung;
        })->sortBy('persen')->values();
    }

    /** Rekap per peserta, diurutkan dari kehadiran terendah */
    private function rekapPeserta(Builder $sesi): Collection
    {
        $baris = $this->detail($sesi)
            ->selectRaw('d.pendaftaran_siswa_id as id, d.status, count(*) as n')
            ->groupBy('d.pendaftaran_siswa_id', 'd.status')->get()->groupBy('id');

        $siswa = PendaftaranSiswa::with('kelasMaster')->whereIn('id', $baris->keys())->get()->keyBy('id');

        return $baris->map(function ($rows, $id) use ($siswa) {
            $hitung = collect(Absensi::STATUS)->map(fn ($s, $k) => (int) ($rows->firstWhere('status', $k)->n ?? 0))->all();
            $s = $siswa[$id] ?? null;

            return [
                'id' => (int) $id,
                'nama' => $s?->nama_lengkap ?? 'Peserta dihapus',
                'nomor' => $s?->nomor_pendaftaran,
                'kelas' => $s?->kelasMaster?->nama,
                'foto' => $s?->foto_url,
                'total' => array_sum($hitung),
                'persen' => $this->persen($hitung),
            ] + $hitung;
        })->sortBy([['persen', 'asc'], ['alfa', 'desc'], ['nama', 'asc']])->values();
    }

    /** Persen kehadiran per tanggal (untuk grafik batang) */
    private function rekapHarian(Builder $sesi): Collection
    {
        return $this->detail($sesi)
            ->selectRaw("a.tanggal, sum(case when d.status in ('hadir','terlambat') then 1 else 0 end) as hadir, count(*) as total")
            ->groupBy('a.tanggal')->orderBy('a.tanggal')->get()
            ->map(fn ($r) => [
                'tanggal' => Carbon::parse($r->tanggal),
                'persen' => $r->total ? (int) round($r->hadir / $r->total * 100) : 0,
                'total' => (int) $r->total,
            ])->take(-31)->values();
    }

    /** Kalimat kesimpulan otomatis */
    private function kesimpulan(array $hitung, Collection $perKelas, Collection $perKegiatan, Collection $perhatian, Collection $perPeserta): array
    {
        $total = array_sum($hitung);
        if (! $total) {
            return [];
        }

        $persen = $this->persen($hitung);
        $nilai = match (true) {
            $persen >= 90 => ['sangat baik', 'emerald'],
            $persen >= 80 => ['baik', 'teal'],
            $persen >= 70 => ['cukup', 'amber'],
            default => ['perlu ditingkatkan', 'rose'],
        };

        $k = [];
        $k[] = ['Tingkat kehadiran keseluruhan <b>' . $persen . '%</b>, tergolong <b>' . $nilai[0] . '</b>.', $nilai[1]];

        $tidak = $hitung['alfa'];
        $k[] = ['Tercatat <b>' . $hitung['terlambat'] . '</b> kali terlambat, <b>' . $hitung['sakit'] . '</b> sakit, <b>'
            . $hitung['izin'] . '</b> izin, dan <b>' . $tidak . '</b> kali alfa / tidak hadir.', $tidak > $hitung['izin'] + $hitung['sakit'] ? 'rose' : 'slate'];

        if ($perKelas->count() > 1) {
            $rendah = $perKelas->first();
            $tinggi = $perKelas->last();
            $k[] = ['Kelas dengan kehadiran tertinggi <b>' . \App\Support\TeksAman::e($tinggi['nama']) . '</b> (' . $tinggi['persen'] . '%), terendah <b>' . \App\Support\TeksAman::e($rendah['nama']) . '</b> (' . $rendah['persen'] . '%).', 'sky'];
        } elseif ($perKelas->count() === 1) {
            $k[] = ['Kehadiran kelas <b>' . \App\Support\TeksAman::e($perKelas->first()['nama']) . '</b>: ' . $perKelas->first()['persen'] . '%.', 'sky'];
        }

        if ($perKegiatan->isNotEmpty()) {
            $rendah = $perKegiatan->first();
            $k[] = ['Kegiatan dengan kehadiran terendah <b>' . \App\Support\TeksAman::e($rendah['nama']) . '</b> (' . $rendah['persen'] . '%).', 'teal'];
        }

        $jumlahPerhatian = $perPeserta->filter(fn ($p) => $p['persen'] < 75 || $p['alfa'] >= 3)->count();
        $k[] = $jumlahPerhatian
            ? ['<b>' . $jumlahPerhatian . '</b> peserta perlu perhatian (kehadiran di bawah 75% atau alfa 3 kali lebih).', 'amber']
            : ['Tidak ada peserta yang kehadirannya di bawah 75%.', 'emerald'];

        return $k;
    }
}
