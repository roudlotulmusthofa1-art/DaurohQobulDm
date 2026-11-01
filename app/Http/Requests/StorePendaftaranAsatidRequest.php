<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi formulir pendaftaran asatid.
 * Aturan tiap isian disamakan dengan pendaftaran siswa (StorePendaftaranRequest).
 */
class StorePendaftaranAsatidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $polaArab = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}-]+\z/u';

        return [
            'nama_lengkap'      => ['required', 'string', 'max:150'],
            'nama_lengkap_arab' => ['required', 'string', 'max:150', "regex:$polaArab"],
            'tempat_lahir'      => ['required', 'string', 'max:100', "regex:$polaArab"],
            'tanggal_lahir'     => ['required', 'date', 'before:today'],
            'no_hp'             => ['required', 'string', 'regex:/^\+62[0-9]+$/', 'max:20'],
            'alamat'            => ['required', 'string', 'max:500', "regex:$polaArab"],

            'lama_belajar'      => ['required', 'string', 'max:100'],
            'tahun_selesai'     => ['required', 'digits:4'],

            // Email tidak boleh sudah dipakai siswa, asatid lain, atau akun mana pun
            'email_login'       => [
                'required', 'email', 'max:255',
                'unique:pendaftaran_asatid,email',
                'unique:pendaftaran_siswas,email',
                'unique:users,email',
            ],
            'password_login'    => ['required', 'string', 'min:8', 'max:100'],

            'berkas_foto'       => ['required', 'image', 'max:5120'],
            'catatan_peserta'   => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'                => ':attribute wajib diisi.',
            'digits'                  => ':attribute harus :digits digit angka.',
            'image'                   => ':attribute harus berupa gambar.',
            'max'                     => ':attribute terlalu besar atau terlalu panjang.',
            'no_hp.regex'             => 'Format nomor WhatsApp tidak valid (contoh: +6281234567890).',
            '*.regex'                 => 'Kolom ini harus ditulis menggunakan huruf Arab.',
            'tanggal_lahir.before'    => 'Tanggal lahir harus sebelum hari ini.',
            'email_login.required'    => 'Email login wajib diisi.',
            'email_login.email'       => 'Format email tidak valid.',
            'email_login.unique'      => 'Email ini sudah digunakan. Silakan gunakan email lain.',
            'password_login.required' => 'Password login wajib diisi.',
            'password_login.min'      => 'Password minimal 8 karakter.',
            'berkas_foto.max'         => 'Pas foto maksimal 5 MB.',
            'berkas_foto.uploaded'    => 'Pas foto gagal diunggah. Pastikan ukurannya maksimal 5 MB lalu coba lagi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap'      => 'Nama lengkap',
            'nama_lengkap_arab' => 'Nama lengkap bahasa arab',
            'tempat_lahir'      => 'Tempat lahir',
            'tanggal_lahir'     => 'Tanggal lahir',
            'no_hp'             => 'Nomor WhatsApp',
            'alamat'            => 'Alamat',
            'lama_belajar'      => 'Lama belajar',
            'tahun_selesai'     => 'Tahun selesai belajar',
            'email_login'       => 'Email login',
            'password_login'    => 'Password login',
            'berkas_foto'       => 'Pas foto',
            'catatan_peserta'   => 'Catatan untuk admin',
        ];
    }
}
