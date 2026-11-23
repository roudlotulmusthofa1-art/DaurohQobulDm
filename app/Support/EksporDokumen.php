<?php

namespace App\Support;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

/**
 * Mengubah satu tampilan Blade menjadi: halaman Print, file PDF, atau file Word (.doc).
 * Dipakai oleh Export Data Santri dan Export Jadwal Mengajar.
 */
class EksporDokumen
{
    public const FORMAT = ['cetak', 'pdf', 'word'];

    /** Kirim dokumen sesuai format yang dipilih */
    public static function kirim(string $view, array $data, string $format, string $namaFile, bool $mendatar = false)
    {
        $namaFile = Str::slug($namaFile) . '-' . now('Asia/Jakarta')->format('Y-m-d');
        $profil = Pengaturan::profilPesantren();
        $data += [
            'format'   => $format,
            'mendatar' => $mendatar,
            'profil'   => $profil,
            'logo'     => self::logo($profil, $format),
            'dicetak'  => now('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB',
        ];

        if ($format === 'cetak') {
            return view($view, $data);
        }

        $html = view($view, $data)->render();

        if ($format === 'word') {
            return response("\xEF\xBB\xBF" . $html, 200, [
                'Content-Type'        => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $namaFile . '.doc"',
            ]);
        }

        return response(self::pdf($html, $mendatar), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '.pdf"',
        ]);
    }

    /** Teks aman untuk HTML; bagian berhuruf Arab diberi font Arab dan arah kanan-ke-kiri */
    public static function teks(?string $teks): string
    {
        $arab = '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}';

        return preg_replace("/[{$arab}]+(?:\s+[{$arab}]+)*/u", '<span class="arab" lang="ar" dir="rtl">$0</span>', e((string) $teks));
    }

    private static function pdf(string $html, bool $mendatar): string
    {
        $folderSementara = storage_path('framework/cache/mpdf');
        if (! is_dir($folderSementara)) {
            mkdir($folderSementara, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => $mendatar ? 'A4-L' : 'A4',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 14,
            'tempDir'       => $folderSementara,
            'default_font'  => 'dejavusans',
        ]);
        $mpdf->SetFooter('{PAGENO} / {nbpg}');
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    /** PDF membaca logo dari file di server; Word tidak memakai logo supaya filenya berdiri sendiri */
    private static function logo(array $profil, string $format): ?string
    {
        if ($format === 'word') {
            return null;
        }
        if ($format === 'cetak') {
            return $profil['logo_url'];
        }

        $file = $profil['logo'] ? Storage::disk('public')->path($profil['logo']) : public_path('images/Logo dar4.png');

        return is_file($file) ? $file : null;
    }
}
