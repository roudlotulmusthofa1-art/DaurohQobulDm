<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\CatatanPenilaian;
use App\Models\PelanggaranSiswa;
use App\Models\PencapaianSiswa;
use App\Models\PendaftaranSiswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Penilaian > Pelanggaran & Pencapaian: pengaturan dan data bersama untuk halaman admin & asatid.
 *
 * Satu baris laporan (untuk Vue) bentuknya selalu:
 *   id, sumber ('guru' | 'absensi'), siswa_id, siswa, arab, nomor, kelas, jenis_id, jenis, warna,
 *   tanggal (Y-m-d), hari, jam (H:i), catatan, keterangan, oleh, oleh_id, url
 * Pelanggaran juga memuat setiap Terlambat & Alfa dari absensi kelas / kegiatan (sumber 'absensi',
 * dibaca langsung dari tabel absensi, jadi ikut berubah bila absensinya dikoreksi).
 */
class JenisPenilaian
{
    public const TIPE = ['pelanggaran', 'pencapaian'];

    /** Jenis tetap dari absensi (hanya untuk pelanggaran) */
    public const DARI_ABSENSI = ['terlambat', 'alfa'];

    /** Pengaturan satu tipe. Tipe tidak dikenal -> 404. */
    public static function ambil(string $tipe): array
    {
        return match ($tipe) {
            'pelanggaran' => [
                'tipe'        => 'pelanggaran',
                'model'       => PelanggaranSiswa::class,
                'label'       => __('Pelanggaran'),
                'keterangan'  => __('Laporan pelanggaran siswa dari asatid, ditambah setiap Terlambat & Alfa dari absensi kelas dan absensi kegiatan.'),
                'tab_master'  => 'jenis-pelanggaran',
                'label_master'=> __('Jenis Pelanggaran'),
                'dari_absensi'=> true,
                'warna'       => 'rose',
            ],
            'pencapaian' => [
                'tipe'        => 'pencapaian',
                'model'       => PencapaianSiswa::class,
                'label'       => __('Pencapaian'),
                'keterangan'  => __('Laporan pencapaian / prestasi siswa dari asatid.'),
                'tab_master'  => 'jenis-pencapaian',
                'label_master'=> __('Jenis Pencapaian'),
                'dari_absensi'=> false,
                'warna'       => 'emerald',
            ],
            default => abort(404),
        };
    }

    /** Jenis dari Master Data (yang aktif saja, atau semua untuk rekap) */
    public static function daftarJenis(string $tipe, bool $aktifSaja = true): Collection
    {
        $model = self::ambil($tipe)['model'];
        $kelasJenis = $model::MODEL_JENIS;

        $jenis = $kelasJenis::query()
            ->when($aktifSaja, fn ($q) => $q->where('is_active', true))
            ->orderBy('urutan')->orderBy('nama')
            ->get(['id', 'nama', 'warna', 'deskripsi'])
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama, 'warna' => $j->warna, 'deskripsi' => $j->deskripsi]);

        // Pelanggaran: Terlambat & Alfa dari absensi ikut menjadi jenis di rekap
        if (! $aktifSaja && $tipe === 'pelanggaran') {
            $jenis->push(['id' => 'terlambat', 'nama' => __('Terlambat (absensi)'), 'warna' => 'bg-amber-100', 'deskripsi' => null]);
            $jenis->push(['id' => 'alfa', 'nama' => __('Alfa (absensi)'), 'warna' => 'bg-rose-100', 'deskripsi' => null]);
        }

        return $jenis->values();
    }

    /** Semua laporan dalam periode (null = semua), terbaru dulu */
    public static function semuaBaris(string $tipe, ?string $dari = null, ?string $sampai = null): Collection
    {
        $model = self::ambil($tipe)['model'];

        $baris = $model::with(['siswa.kelasMaster', 'jenis', 'pencatat:id,name'])
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->get()
            ->map(fn (CatatanPenilaian $c) => self::barisGuru($c));

        if (self::ambil($tipe)['dari_absensi']) {
            $baris = $baris->concat(self::barisAbsensi($dari, $sampai));
        }

        return $baris->sortByDesc(fn ($b) => $b['tanggal'] . ' ' . ($b['jam'] ?? '00:00'))->values();
    }

    /** Satu laporan dari asatid -> baris untuk Vue */
    public static function barisGuru(CatatanPenilaian $c): array
    {
        return self::siswa($c->siswa) + [
            'id'         => 'g' . $c->id,
            'nomor_id'   => $c->id,
            'sumber'     => 'guru',
            'jenis_id'   => $c->{$c::KOLOM_JENIS},
            'jenis'      => $c->jenis?->nama ?? __('(jenis sudah dihapus)'),
            'warna'      => $c->jenis?->warna,
            'tanggal'    => $c->tanggal->toDateString(),
            'hari'       => $c->tanggal->locale('id')->translatedFormat('l'),
            'jam'        => $c->jam ? substr((string) $c->jam, 0, 5) : null,
            'catatan'    => $c->catatan,
            'keterangan' => null,
            'oleh'       => $c->pencatat?->name,
            'oleh_id'    => $c->dicatat_oleh,
            'url'        => null,
        ];
    }

    /** Terlambat & Alfa dari absensi kelas / kegiatan (absensi kegiatan yang belum ditutup: Alfa belum ada) */
    public static function barisAbsensi(?string $dari = null, ?string $sampai = null): Collection
    {
        return AbsensiDetail::query()
            ->select('absensi_detail.*')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->whereIn('absensi_detail.status', self::DARI_ABSENSI)
            ->when($dari, fn ($q) => $q->whereDate('absensi.tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('absensi.tanggal', '<=', $sampai))
            ->with(['siswa.kelasMaster', 'pencatat:id,name', 'absensi.kelas', 'absensi.pelajaran', 'absensi.kegiatan', 'absensi.waktuPelajaran', 'absensi.pengabsen:id,name'])
            ->get()
            ->filter(fn ($d) => $d->siswa && $d->absensi)
            ->map(function (AbsensiDetail $d) {
                $a = $d->absensi;
                $kegiatan = $a->jenis === Absensi::KEGIATAN;
                $jam = $d->waktu ?: ($kegiatan ? $a->jam_mulai : $a->waktuPelajaran?->jam_mulai);

                return self::siswa($d->siswa) + [
                    'id'         => 'a' . $d->id,
                    'nomor_id'   => null,
                    'sumber'     => 'absensi',
                    'jenis_id'   => $d->status,
                    'jenis'      => $d->status === 'alfa' ? __('Alfa (absensi)') : __('Terlambat (absensi)'),
                    'warna'      => $d->status === 'alfa' ? 'bg-rose-100' : 'bg-amber-100',
                    'tanggal'    => $a->tanggal->toDateString(),
                    'hari'       => $a->tanggal->locale('id')->translatedFormat('l'),
                    'jam'        => $jam ? substr((string) $jam, 0, 5) : null,
                    'catatan'    => $d->catatan,
                    'keterangan' => ($kegiatan ? __('Absensi kegiatan') : __('Absensi kelas')) . ' · ' . $a->judul,
                    'oleh'       => $d->pencatat?->name ?? $a->pengabsen?->name,
                    'oleh_id'    => $d->dicatat_oleh ?? $a->user_id,
                    'url'        => route('admin.laporan-absensi.show', $a),
                ];
            })
            ->values();
    }

    /** Data singkat siswa (untuk baris laporan & pilihan siswa) */
    public static function siswa(?PendaftaranSiswa $s): array
    {
        return [
            'siswa_id' => $s?->id,
            'siswa'    => $s?->nama_lengkap ?? __('(siswa dihapus)'),
            'arab'     => $s?->nama_lengkap_arab,
            'nomor'    => $s?->nomor_pendaftaran,
            'kelas'    => $s?->kelasMaster?->nama,
            'foto'     => $s?->foto_url,
        ];
    }

    /** Siswa yang sudah diterima (pilihan di form asatid) */
    public static function daftarSiswa(): Collection
    {
        return PendaftaranSiswa::with('kelasMaster')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($s) => self::siswa($s));
    }

    /** Nama hari dari tanggal Y-m-d, mis. "Jumat" */
    public static function hari(string $tanggal): string
    {
        return Carbon::parse($tanggal)->locale('id')->translatedFormat('l');
    }
}
