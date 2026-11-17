<?php

namespace App\Models;

use App\Support\MasukSebagai;
use App\Support\SesiAplikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Catatan aktivitas asatid & siswa: masuk/keluar (LoginController, event Logout) dan semua simpan/ubah/hapus
 * (Middleware\CatatAktivitas). Tampil di lonceng admin (NotifikasiAdmin: aktivitas_asatid / aktivitas_siswa)
 * dan halaman Sistem > Aktivitas Pengguna (admin/aktivitas).
 * Mode "Masuk sebagai" (admin melihat dasbor asatid/siswa) TIDAK dicatat.
 */
class AktivitasPengguna extends Model
{
    protected $connection = 'pusat';
    protected $table = 'aktivitas_pengguna';
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'peran', 'nama', 'aksi', 'url', 'ip', 'sesi'];

    /** Peran yang dicatat */
    public const PERAN = ['asatid', 'siswa'];

    /** Nama rute (asatid.* / siswa.*) yang mengubah data => kalimat aktivitas. Rute lain: kalimat umum. */
    public const AKSI = [
        'asatid.absensi.kegiatan.mulai'      => 'memulai absensi kegiatan',
        'asatid.absensi.kegiatan.simpan'     => 'mencatat absensi kegiatan',
        'asatid.absensi.kegiatan.selesai'    => 'menutup absensi kegiatan',
        'asatid.absensi.kelas.simpan'        => 'menyimpan absensi kelas',
        'asatid.absensi.destroy'             => 'menghapus absensi',
        'asatid.ganti-password.simpan'       => 'mengganti password',
        'asatid.hafalan.simpan'              => 'menyimpan setoran hafalan',
        'asatid.hafalan.selesai'             => 'menandai hafalan selesai',
        'asatid.hafalan.hapus'               => 'menghapus setoran hafalan',
        'asatid.penilaian.catatan.simpan'    => 'menyimpan catatan santri',
        'asatid.penilaian.catatan.hapus'     => 'menghapus catatan santri',
        'asatid.penilaian.harian.simpan'     => 'menyimpan nilai harian',
        'asatid.penilaian.lisan.simpan'      => 'menyimpan nilai ujian lisan',
        'asatid.penilaian.ujian.nilai.simpan' => 'menyimpan nilai ujian',
        'asatid.penilaian.ujian.soal.simpan' => 'menyimpan soal ujian',
        'asatid.penilaian.ujian.soal.hapus'  => 'menghapus soal ujian',
        'asatid.pesan-saya.kirim'            => 'mengirim pesan ke admin',
        'asatid.tanda.lihat'                 => 'melihat tanda ganti pelajaran',
        'siswa.ganti-password.simpan'        => 'mengganti password',
        'siswa.pesan-saya.kirim'             => 'mengirim pesan ke admin',
    ];

    public function scopeTerbaru(Builder $q): Builder
    {
        return $q->orderByDesc('id');
    }

    /** Catat satu aktivitas akun asatid/siswa (diam-diam gagal supaya aplikasi tidak ikut error). */
    public static function catat(?User $user, string $aksi, ?string $url = null): void
    {
        if (! $user || ! in_array($user->role, self::PERAN, true) || MasukSebagai::aktif()) {
            return;
        }

        try {
            static::create([
                'user_id' => $user->id,
                'peran'   => $user->role,
                'nama'    => $user->name,
                'aksi'    => mb_substr($aksi, 0, 255),
                'url'     => $url ? mb_substr($url, 0, 500) : null,
                'ip'      => request()?->ip(),
                'sesi'    => SesiAplikasi::aktif()?->nama,
            ]);
        } catch (\Throwable $e) {
            report($e); // mis. tabel belum dimigrasi
        }
    }

    /** Kalimat aktivitas dari nama rute */
    public static function aksiDariRute(?string $rute, string $metode): string
    {
        if ($rute && isset(self::AKSI[$rute])) {
            return self::AKSI[$rute];
        }

        return match ($metode) {
            'DELETE' => 'menghapus data',
            'PUT', 'PATCH' => 'mengubah data',
            default => 'menyimpan data',
        } . ($rute ? ' (' . $rute . ')' : '');
    }
}
