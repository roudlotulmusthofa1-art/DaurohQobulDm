<?php

namespace App\Support;

/**
 * Pengaman teks Blade untuk halaman yang memakai Vue.
 *
 * Vue membaca HTML halaman sebagai template, sehingga tulisan "{{ ... }}" di dalam data
 * (mis. nama pendaftar yang sengaja diisi "{{ ... }}") bisa dijalankan sebagai kode.
 * Setiap {{ $teks }} di Blade dicetak lewat fungsi ini: selain di-escape seperti biasa (e()),
 * pasangan "{{" diberi karakter tak terlihat di tengahnya, jadi Vue tidak lagi mengenalinya.
 *
 * Dipasang di AppServiceProvider: Blade::setEchoFormat('\App\Support\TeksAman::e(%s)')
 */
class TeksAman
{
    public static function e($nilai, bool $doubleEncode = true): string
    {
        return str_replace('{{', "{\u{200B}{", e($nilai, $doubleEncode));
    }
}
