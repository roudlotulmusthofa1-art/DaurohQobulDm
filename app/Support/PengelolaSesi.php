<?php

namespace App\Support;

use App\Models\Sesi;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/** Membuat, menyiapkan, dan menghapus sesi (database + folder file). */
class PengelolaSesi
{
    /** Tabel master data yang bisa disalin dari sesi lain saat membuat sesi baru */
    public const MASTER_DATA = ['bidang_ilmu', 'waktu_pelajaran', 'kelas', 'kamar', 'jenis_kegiatan', 'jenis_pelanggaran', 'jenis_khidmah', 'kategori_berita'];

    /** Kunci pengaturan yang TIDAK disalin (berlaku untuk semua sesi / milik sesi asal) */
    private const PENGATURAN_TIDAK_DISALIN = ['asatid_daftar_kode', 'admin_daftar_kode', 'pesantren_logo'];

    /**
     * Buat sesi baru: database baru, tabel (migrasi), akun bersama, folder file,
     * dan bila diminta salin master data & pengaturan dari sesi lain.
     */
    public static function buat(array $data, ?Sesi $salinDari, bool $salinMaster, bool $salinPengaturan): Sesi
    {
        $sesi = Sesi::create([
            'nama' => $data['nama'],
            'kode' => self::kodeUnik($data['nama']),
            'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'is_active' => false,
        ]);
        $sesi->update(['database' => SesiAplikasi::namaDatabaseBaru($sesi->id)]);

        try {
            DB::connection('pusat')->statement("CREATE DATABASE IF NOT EXISTS `{$sesi->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            $sesi->delete();
            throw new RuntimeException(
                "Database baru tidak bisa dibuat. User database belum punya izin membuat database. " .
                "Jalankan sekali di server: GRANT ALL PRIVILEGES ON `" . config('database.connections.pusat.database') . "\\_sesi\\_%`.* TO '" .
                config('database.connections.pusat.username') . "'@'%'; FLUSH PRIVILEGES;  (Pesan asli: {$e->getMessage()})"
            );
        }

        try {
            self::siapkan($sesi);

            $koneksi = $sesi->koneksi();
            if ($salinDari && $salinMaster) {
                self::salinTabel($salinDari->koneksi(), $koneksi, self::MASTER_DATA);
            }
            if ($salinDari && $salinPengaturan) {
                $baris = DB::connection($salinDari->koneksi())->table('pengaturan')->whereNotIn('kunci', self::PENGATURAN_TIDAK_DISALIN)->get();
                foreach ($baris as $b) {
                    DB::connection($koneksi)->table('pengaturan')->updateOrInsert(['kunci' => $b->kunci], ['nilai' => $b->nilai, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        } catch (Throwable $e) {
            self::hapus($sesi);
            throw new RuntimeException('Sesi gagal disiapkan: ' . $e->getMessage(), 0, $e);
        }

        return $sesi->fresh();
    }

    /** Jalankan migrasi + samakan akun bersama + pastikan folder ada. Aman diulang (dipakai juga oleh sesi:migrate). */
    public static function siapkan(Sesi $sesi): void
    {
        $koneksi = $sesi->koneksi();

        if (! $sesi->utama) {
            $kode = Artisan::call('migrate', ['--database' => $koneksi, '--force' => true]);
            if ($kode !== 0) {
                throw new RuntimeException('Migrasi gagal: ' . Artisan::output());
            }

            // Id akun peserta sesi ini dimulai dari {id sesi} × 1.000.000 (tidak bentrok dengan akun bersama)
            $mulai = $sesi->id * AkunBersama::RENTANG_ID;
            $maks = (int) DB::connection($koneksi)->table('users')->max('id');
            if ($maks < $mulai) {
                DB::connection($koneksi)->statement('ALTER TABLE users AUTO_INCREMENT = ' . $mulai);
            }

            AkunBersama::samakan($koneksi);
        }

        File::ensureDirectoryExists(storage_path('app/public' . ($sesi->folder ? '/' . $sesi->folder : '')));
    }

    /** Salin isi tabel (id tetap sama) dari satu database ke database lain. Tabel tujuan dikosongkan dulu. */
    public static function salinTabel(string $dari, string $ke, array $tabel): void
    {
        $tujuan = DB::connection($ke);
        $tujuan->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tabel as $t) {
                $tujuan->table($t)->delete();
                DB::connection($dari)->table($t)->orderBy('id')->chunk(500, function ($baris) use ($tujuan, $t) {
                    $tujuan->table($t)->insert($baris->map(fn ($b) => (array) $b)->all());
                });
            }
        } finally {
            $tujuan->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /** Hapus sesi: database, folder file, dan cadangan. Sesi utama tidak pernah dihapus. */
    public static function hapus(Sesi $sesi): void
    {
        if ($sesi->utama) {
            throw new RuntimeException('Sesi utama tidak bisa dihapus.');
        }

        // Pengaman: hanya database berpola {database utama}_sesi_{id}
        if ($sesi->database === SesiAplikasi::namaDatabaseBaru($sesi->id)) {
            DB::connection('pusat')->statement("DROP DATABASE IF EXISTS `{$sesi->database}`");
        }
        DB::purge('sesi_' . $sesi->id);

        File::deleteDirectory(storage_path('app/public/sesi/' . $sesi->kode));
        File::deleteDirectory(CadanganDatabase::folder($sesi));
        $sesi->delete();
    }

    private static function kodeUnik(string $nama): string
    {
        $dasar = Str::limit(Str::slug($nama), 40, '') ?: 'sesi';
        $kode = $dasar;
        for ($i = 2; Sesi::where('kode', $kode)->exists() || $kode === 'utama'; $i++) {
            $kode = $dasar . '-' . $i;
        }

        return $kode;
    }
}
