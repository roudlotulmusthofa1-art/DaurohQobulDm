<?php

namespace App\Support;

use App\Models\PendaftaranAdmin;
use App\Models\PendaftaranAsatid;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * "Tambah Cepat" di Data Asatid & Data Admin.
 * Admin cukup mengisi nama, WhatsApp, email, password -> data langsung DITERIMA dan akun login dibuat.
 * Data lain (nama Arab, alamat, dst.) dilengkapi belakangan lewat menu Edit.
 */
class TambahCepat
{
    /**
     * @param  class-string<PendaftaranAsatid|PendaftaranAdmin>  $model
     * @param  string  $kunciPesan  template WhatsApp (Pengaturan > Pesan), mis. "asatid_terima"
     * @return array{0: PendaftaranAsatid|PendaftaranAdmin, 1: string} [data baru, pesan untuk admin]
     */
    public static function simpan(Request $request, string $model, string $kunciPesan, WhatsAppService $wa): array
    {
        // "0812..." / "62812..." / "+62 812-..." -> "+62812..."
        $hp = preg_replace('/\D/', '', (string) $request->no_hp);
        if (str_starts_with($hp, '0')) {
            $hp = '62' . substr($hp, 1);
        }
        $request->merge(['no_hp' => $hp ? '+' . $hp : '']);

        $data = $request->validateWithBag('tambahCepat', [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'no_hp'        => ['required', 'string', 'regex:/^\+62[0-9]{8,14}$/'],
            'email'        => [
                'required', 'email', 'max:255',
                'unique:pusat.pendaftaran_asatid,email',
                'unique:pusat.pendaftaran_admin,email',
                'unique:pendaftaran_siswas,email',
                'unique:users,email',
                'unique:pusat.users,email',
            ],
            'password'     => ['required', 'string', 'min:8', 'max:100'],
            'kirim_wa'     => ['nullable', 'boolean'],
        ], [
            'required'       => ':attribute wajib diisi.',
            'no_hp.regex'    => 'Nomor WhatsApp tidak valid (contoh: 081234567890).',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah dipakai akun lain.',
            'password.min'   => 'Password minimal 8 karakter.',
        ], [
            'nama_lengkap' => 'Nama lengkap', 'no_hp' => 'Nomor WhatsApp', 'email' => 'Email', 'password' => 'Password',
        ]);

        $baru = DB::connection('pusat')->transaction(function () use ($model, $data) {
            $item = $model::create([
                'nomor_pendaftaran' => $model::buatNomor(),
                'nama_lengkap'      => $data['nama_lengkap'],
                'no_hp'             => $data['no_hp'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'status'            => $model::MENUNGGU,
            ]);

            // Langsung diterima: akun login (users) dibuat & disalin ke semua sesi
            $item->terima('Ditambahkan langsung oleh admin (tambah cepat).');

            return $item;
        });

        $pesan = 'Data ' . $baru->nama_lengkap . ' ditambahkan dan akun login sudah aktif. Lengkapi datanya lewat tombol Edit.';

        if (! empty($data['kirim_wa'])) {
            $teks = PesanWa::buat($kunciPesan, ['nama' => $baru->nama_lengkap, 'catatan' => null, 'email' => $baru->email]);
            if ($teks) {
                $pesan .= $wa->kirim($baru->no_hp, $teks) ? ' Chat WhatsApp terkirim.' : ' Chat WhatsApp GAGAL terkirim.';
            }
        }

        return [$baru, $pesan];
    }
}
