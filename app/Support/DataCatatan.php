<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\CatatanSiswa;
use App\Models\IzinSantri;
use App\Models\PendaftaranSiswa;
use Illuminate\Support\Collection;

/**
 * Penilaian > Catatan: data bersama untuk halaman admin & asatid.
 *
 * Satu baris (untuk Vue) bentuknya selalu:
 *   id, nomor_id, sumber ('guru' | 'absensi' | 'izin'), asal ('terlambat' | 'alfa' | null), jenis ('pencapaian' | 'pelanggaran'), label,
 *   siswa_id, siswa, arab, nomor, kelas, foto, tanggal (Y-m-d), hari, jam (H:i), catatan, keterangan, oleh, url
 * Pelanggaran juga memuat setiap Terlambat & Alfa dari absensi kelas / kegiatan (sumber 'absensi'),
 * dan setiap izin yang terlambat kembali dari Perizinan (sumber 'izin').
 * Keduanya dibaca langsung dari tabelnya, jadi ikut berubah bila absensi / waktu kembali dikoreksi.
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
            // nama Arab pencatat diambil sekaligus (tanpa kueri per baris)
            ->tap(fn ($c) => NamaArab::untukUser($c->pluck('dicatat_oleh')))
            ->map(fn (CatatanSiswa $c) => self::baris($c));

        return $catatan->concat(self::barisAbsensi($dari, $sampai))->concat(self::barisIzin($dari, $sampai))
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
            'oleh_arab'  => NamaArab::user($c->dicatat_oleh),
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
            ->with(['siswa.kelasMaster', 'pencatat:id,name', 'absensi.kelas', 'absensi.pelajaran', 'absensi.kitab', 'absensi.kegiatan', 'absensi.waktuPelajaran', 'absensi.pengabsen:id,name'])
            ->get()
            ->filter(fn ($d) => $d->siswa && $d->absensi)
            ->tap(fn ($d) => NamaArab::untukUser($d->pluck('dicatat_oleh')->merge($d->pluck('absensi.user_id'))))
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
                    'oleh_arab'  => NamaArab::user($d->pencatat?->id ?? $a->pengabsen?->id),
                    'url'        => route('admin.laporan-absensi.show', $a),
                ];
            })
            ->values();
    }

    /** Izin yang terlambat kembali (atau belum kembali padahal batas lewat) = pelanggaran. Tanggal = tanggal batas izin. */
    public static function barisIzin(?string $dari = null, ?string $sampai = null): Collection
    {
        return IzinSantri::terlambat()
            ->when($dari, fn ($q) => $q->whereDate('selesai', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('selesai', '<=', $sampai))
            ->with(['siswa.kelasMaster', 'pemberi:id,name'])
            ->get()
            ->filter(fn ($i) => $i->siswa)
            ->tap(fn ($i) => NamaArab::untukUser($i->pluck('diberi_oleh')))
            ->map(function (IzinSantri $i) {
                $batas = $i->waktu('selesai');
                $kembali = $i->waktu('kembali');
                $lama = IzinSantri::lama($i->menitTerlambat());

                return self::siswa($i->siswa) + [
                    'id'         => 'i' . $i->id,
                    'nomor_id'   => null,
                    'sumber'     => 'izin',
                    'asal'       => null,
                    'jenis'      => CatatanSiswa::PELANGGARAN,
                    'label'      => __('Terlambat izin'),
                    'tanggal'    => $batas->toDateString(),
                    'hari'       => $batas->locale('id')->translatedFormat('l'),
                    'jam'        => $batas->format('H:i'),
                    'catatan'    => $kembali
                        ? __('Izin :jenis sampai :batas, kembali :kembali (terlambat :lama).', [
                            'jenis' => $i->jenis, 'batas' => $batas->format('d/m H:i'), 'kembali' => $kembali->format('d/m H:i'), 'lama' => $lama,
                        ])
                        : __('Izin :jenis sampai :batas, belum kembali (sudah lewat :lama).', [
                            'jenis' => $i->jenis, 'batas' => $batas->format('d/m H:i'), 'lama' => $lama,
                        ]),
                    'keterangan' => __('Perizinan') . ' · ' . $i->jenis,
                    'oleh'       => $i->pemberi?->name,
                    'oleh_arab'  => NamaArab::user($i->diberi_oleh),
                    'url'        => route('admin.penilaian.perizinan.index'),
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
