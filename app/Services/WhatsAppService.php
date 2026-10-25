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
    public function kirim(?string $nomor, string $pesan): bool
    {
        $token = config('services.whatsapp.token');

        if (! $nomor || ! $token) {
            Log::warning('WA tidak terkirim: nomor atau token kosong.');
            return false;
        }

        try {
            $res = Http::timeout(15)
                // hanya untuk lokal (Laragon); di server produksi sertifikat harus valid
                ->when(app()->environment('local'), fn($h) => $h->withoutVerifying())
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post(config('services.whatsapp.url'), [
                    'target'      => $this->normalisasi($nomor),
                    'message'     => $pesan,
                    'countryCode' => '62',
                ]);

            $ok = $res->successful() && ($res->json('status') === true);

            if (! $ok) {
                Log::error('WA gagal: ' . $res->body());
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::error('WA gagal: ' . $e->getMessage());
            return false;
        }
    }

    /** 08123xxx / +62812xxx -> 62812xxx */
    private function normalisasi(string $nomor): string
    {
        $n = preg_replace('/\D/', '', $nomor);

        return str_starts_with($n, '0') ? '62' . substr($n, 1) : $n;
    }
}
