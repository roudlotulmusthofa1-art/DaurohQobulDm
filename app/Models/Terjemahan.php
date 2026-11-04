<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Terjemahan Arab buatan admin (Sistem > Pengaturan > Bahasa).
 * Satu baris = satu tulisan Indonesia (__('...')) beserta terjemahan Arabnya.
 * Dibaca oleh App\Support\PemuatTerjemahan saat bahasa Arab dipilih.
 */
class Terjemahan extends Model
{
    protected $connection = 'pusat';

    protected $table = 'terjemahan';

    protected $fillable = ['kunci', 'teks_id', 'teks_ar'];

    private const CACHE = 'terjemahan_ar';

    /** Semua terjemahan: ['Teks Indonesia' => 'نص عربي']. Disimpan di cache supaya tidak membaca database tiap halaman. */
    public static function semua(): array
    {
        try {
            return Cache::rememberForever(self::CACHE, function () {
                if (! Schema::connection('pusat')->hasTable('terjemahan')) {
                    return [];
                }

                return static::pluck('teks_ar', 'teks_id')->all();
            });
        } catch (Throwable $e) {
            return []; // database belum siap: tampilkan tulisan Indonesia saja
        }
    }

    /** Simpan satu terjemahan. Isian kosong = terjemahan dihapus (tulisan kembali tampil dalam bahasa Indonesia). */
    public static function simpanSatu(string $teksId, ?string $teksAr): void
    {
        $teksAr = trim((string) $teksAr);
        $kunci = md5($teksId);

        if ($teksAr === '') {
            static::where('kunci', $kunci)->delete();
        } else {
            static::updateOrCreate(['kunci' => $kunci], ['teks_id' => $teksId, 'teks_ar' => $teksAr]);
        }
    }

    public static function lupakanCache(): void
    {
        Cache::forget(self::CACHE);
    }
}
