<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\CatatanSiswa;
use App\Models\PendaftaranSiswa;
use Illuminate\Support\Collection;

/**
 * Penilaian > Catatan: data bersama untuk halaman admin & asatid.
 *
 * Satu baris (untuk Vue) bentuknya selalu:
 *   id, nomor_id, sumber ('guru' | 'absensi'), asal ('terlambat' | 'alfa' | null), jenis ('pencapaian' | 'pelanggaran'), label,
 *   siswa_id, siswa, arab, nomor, kelas, foto, tanggal (Y-m-d), hari, jam (H:i), catatan, keterangan, oleh, url
 * Pelanggaran juga memuat setiap Terlambat & Alfa dari absensi kelas / kegiatan (sumber 'absensi'),
 * dibaca langsung dari tabel absensi, jadi ikut berubah bila absensinya dikoreksi.
 */
class DataCatatan
{
    /** Semua catatan dalam periode (null = semua), terbaru dulu */
    public static function semuaBaris(?string $dari = null, ?string $sampai = null): Collection
    {
        $catatan = CatatanSiswa::with(['siswa.kelasMaster', 'pencatat:id,name'])
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->get()
            ->map(fn (CatatanSiswa $c) => self::baris($c));

        return $catatan->concat(self::barisAbsensi($dari, $sampai))
            ->sortByDesc(fn ($b) => $b['tanggal'] . ' ' . ($b['jam'] ?? '00:00'))
            ->values();
    }

    /** Satu catatan asatid -> baris untuk Vue */
    public static function baris(CatatanSiswa $c): array
    {
        return self::siswa($c->siswa) + [
            'id'         => 'c' . $c->id,
            'nomor_id'   => $c->id,
            'sumber'     => 'guru',
            'asal'       => null,
            'jenis'      => $c->jenis,
            'label'      => __(CatatanSiswa::JENIS[$c->jenis] ?? $c->jenis),
            'tanggal'    => $c->tanggal->toDateString(),
            'hari'       => $c->tanggal->locale('id')->translatedFormat('l'),
            'jam'        => $c->jam ? substr((string) $c->jam, 0, 5) : null,
            'catatan'    => $c->catatan,
            'keterangan' => null,
            'oleh'       => $c->pencatat?->name,
            'url'        => null,
        ];
    }

    /** Terlambat & Alfa dari absensi kelas / kegiatan = pelanggaran */
    public static function barisAbsensi(?string $dari = null, ?string $sampai = null): Collection
    {
        return AbsensiDetail::query()
            ->select('absensi_detail.*')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->whereIn('absensi_detail.status', ['terlambat', 'alfa'])
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
                    'asal'       => $d->status, // terlambat | alfa
                    'jenis'      => CatatanSiswa::PELANGGARAN,
                    'label'      => $d->status === 'alfa' ? __('Alfa (absensi)') : __('Terlambat (absensi)'),
                    'tanggal'    => $a->tanggal->toDateString(),
                    'hari'       => $a->tanggal->locale('id')->translatedFormat('l'),
                    'jam'        => $jam ? substr((string) $jam, 0, 5) : null,
                    'catatan'    => $d->catatan,
                    'keterangan' => ($kegiatan ? __('Absensi kegiatan') : __('Absensi kelas')) . ' · ' . $a->judul,
                    'oleh'       => $d->pencatat?->name ?? $a->pengabsen?->name,
                    'url'        => route('admin.laporan-absensi.show', $a),
                ];
            })
            ->values();
    }

    /** Data singkat santri */
    public static function siswa(?PendaftaranSiswa $s): array
    {
        return [
            'siswa_id' => $s?->id,
            'siswa'    => $s?->nama_lengkap ?? __('(santri dihapus)'),
            'arab'     => $s?->nama_lengkap_arab,
            'nomor'    => $s?->nomor_pendaftaran,
            'kelas'    => $s?->kelasMaster?->nama,
            'foto'     => $s?->foto_url,
        ];
    }

    /** Santri yang sudah diterima (pilihan di form asatid) */
    public static function daftarSiswa(): Collection
    {
        return PendaftaranSiswa::with('kelasMaster')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($s) => self::siswa($s));
    }
}
