<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim chat WhatsApp (contoh gateway: Fonnte).
 * Ganti isi kirim() bila memakai gateway lain.
 */
class WhatsAppService
{
    /** Alasan gagal terakhir (bahasa sederhana, ditampilkan ke admin) */
    public ?string $alasan = null;

    public function kirim(?string $nomor, string $pesan): bool
    {
        $token = config('services.whatsapp.token');
        $this->alasan = null;

        if (! $token) {
            $this->alasan = 'token Fonnte belum diisi di server (WA_API_TOKEN di file .env)';
            Log::warning('WA tidak terkirim: token kosong (WA_API_TOKEN).');
            return false;
        }
        if (! $nomor) {
            $this->alasan = 'nomor WhatsApp tujuan kosong';
            Log::warning('WA tidak terkirim: nomor kosong.');
            return false;
        }

        try {
            $res = Http::timeout(15)
                // hanya untuk lokal (Laragon); di server produksi sertifikat harus valid
                ->when(app()->environment('local'), fn($h) => $h->withoutVerifying())
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post(config('services.whatsapp.url') ?: 'https://api.fonnte.com/send', [
                    'target'      => self::nomorWa($nomor),
                    'message'     => $pesan,
                    'countryCode' => '62',
                ]);

            $ok = $res->successful() && ($res->json('status') === true);

            if (! $ok) {
                $sebab = (string) $res->json('reason');
                $this->alasan = match (true) {
                    str_contains($sebab, 'disconnected') => 'nomor WhatsApp pengirim di Fonnte sedang TERPUTUS (buka fonnte.com > Device, lalu scan QR ulang)',
                    str_contains(strtolower($sebab), 'token') => 'token Fonnte salah atau kedaluwarsa',
                    str_contains(strtolower($sebab), 'quota') => 'kuota Fonnte habis',
                    $sebab !== '' => 'Fonnte menjawab: ' . $sebab,
                    default => 'Fonnte tidak bisa dihubungi (kode ' . $res->status() . ')',
                };
                Log::error('WA gagal: ' . $res->body());
            }

            return $ok;
        } catch (\Throwable $e) {
            $this->alasan = 'server tidak bisa menghubungi Fonnte (' . $e->getMessage() . ')';
            Log::error('WA gagal: ' . $e->getMessage());
            return false;
        }
    }

    /** 08123xxx / +62812xxx -> 62812xxx (format untuk Fonnte & tautan wa.me) */
    public static function nomorWa(string $nomor): string
    {
        $n = preg_replace('/\D/', '', $nomor);

        return str_starts_with($n, '0') ? '62' . substr($n, 1) : $n;
    }
}
