<?php

namespace App\Support;

use App\Models\Sesi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Mengatur sesi aktif:
 * - koneksi "pusat"  = database utama (daftar sesi, login, cache, akun bersama, pendaftar asatid/admin)
 * - koneksi bawaan   = database sesi aktif (semua data menu lain)
 * - disk "public"    = folder file sesi aktif; disk "bersama" = folder file bersama
 */
class SesiAplikasi
{
    private static ?Sesi $aktif = null;

    private static bool $sudahDicek = false;

    /** Dipanggil di register(): koneksi "pusat" = salinan koneksi bawaan sebelum dipindah. */
    public static function daftarkanKoneksiPusat(): void
    {
        $bawaan = config('database.default');
        config(['database.connections.pusat' => config("database.connections.{$bawaan}")]);
    }

    /** Sesi yang sedang aktif (null bila tabel sesi belum ada, mis. sebelum migrasi). */
    public static function aktif(): ?Sesi
    {
        if (! self::$sudahDicek) {
            self::$sudahDicek = true;
            try {
                if (Schema::connection('pusat')->hasTable('sesi')) {
                    self::$aktif = Sesi::where('is_active', true)->first() ?? Sesi::whereNull('database')->first();
                }
            } catch (Throwable $e) {
                Log::warning('Sesi aktif tidak bisa dibaca: ' . $e->getMessage());
            }
        }

        return self::$aktif;
    }

    /** Pindahkan koneksi bawaan & disk "public" ke sesi aktif. Dipanggil sekali di awal tiap request web. */
    public static function terapkan(): void
    {
        $sesi = self::aktif();

        if ($sesi && ! $sesi->utama) {
            $bawaan = config('database.default');
            try {
                // Pastikan database sesi ada; kalau tidak, tetap di database utama
                DB::connection('pusat')->select('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?', [$sesi->database])
                    ?: throw new \RuntimeException("Database {$sesi->database} tidak ditemukan");
                config(["database.connections.{$bawaan}.database" => $sesi->database]);
                DB::purge($bawaan);
            } catch (Throwable $e) {
                Log::error("Sesi {$sesi->nama} gagal dibuka, kembali ke database utama: " . $e->getMessage());
                self::$aktif = Sesi::whereNull('database')->first();
                $sesi = self::$aktif;
            }
        }

        // Alamat file mengikuti alamat yang sedang dibuka (sama seperti asset())
        $folder = $sesi?->folder ?? '';
        config([
            'filesystems.disks.public.root' => storage_path('app/public' . ($folder ? '/' . $folder : '')),
            'filesystems.disks.public.url' => asset('storage' . ($folder ? '/' . $folder : '')),
            'filesystems.disks.bersama.url' => asset('storage'),
        ]);
        Storage::forgetDisk(['public', 'bersama']);
    }

    /** Nama koneksi Laravel untuk sebuah sesi (sesi utama = "pusat"). */
    public static function koneksiSesi(Sesi $sesi): string
    {
        if ($sesi->utama) {
            return 'pusat';
        }

        $nama = 'sesi_' . $sesi->id;
        if (! config("database.connections.{$nama}")) {
            config(["database.connections.{$nama}" => array_merge(config('database.connections.pusat'), ['database' => $sesi->database])]);
        }

        return $nama;
    }

    /** Koneksi semua sesi yang databasenya ada: [koneksi => Sesi] */
    public static function semuaKoneksi(): array
    {
        $hasil = [];
        try {
            if (! Schema::connection('pusat')->hasTable('sesi')) {
                return ['pusat' => null];
            }
            $ada = collect(DB::connection('pusat')->select('SELECT SCHEMA_NAME AS n FROM INFORMATION_SCHEMA.SCHEMATA'))->pluck('n')->all();
            foreach (Sesi::orderBy('id')->get() as $sesi) {
                if ($sesi->utama || in_array($sesi->database, $ada, true)) {
                    $hasil[self::koneksiSesi($sesi)] = $sesi;
                }
            }
        } catch (Throwable $e) {
            Log::warning('Daftar sesi tidak bisa dibaca: ' . $e->getMessage());
        }

        return $hasil ?: ['pusat' => null];
    }

    /** Apakah dua koneksi menunjuk database yang sama */
    public static function sama(string $a, string $b): bool
    {
        return config("database.connections.{$a}.database") === config("database.connections.{$b}.database")
            && config("database.connections.{$a}.host") === config("database.connections.{$b}.host");
    }

    /** Nama database untuk sesi baru: {database utama}_sesi_{id} */
    public static function namaDatabaseBaru(int $id): string
    {
        return config('database.connections.pusat.database') . '_sesi_' . $id;
    }

    /** Untuk pengujian / perintah artisan: lupakan sesi yang sudah dibaca */
    public static function lupakan(): void
    {
        self::$aktif = null;
        self::$sudahDicek = false;
    }
}
