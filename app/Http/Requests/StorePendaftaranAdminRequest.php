<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi formulir pendaftaran admin.
 * Aturan tiap isian disamakan dengan pendaftaran asatid.
 */
class StorePendaftaranAdminRequest extends FormRequest
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
            'alamat'            => ['required', 'string', 'max:500', "regex:$polaArab"],
            'no_hp'             => ['required', 'string', 'regex:/^\+62[0-9]+$/', 'max:20'],

            // Email tidak boleh sudah dipakai pendaftar lain atau akun mana pun
            'email_login'       => [
                'required', 'email', 'max:255',
                'unique:pendaftaran_admin,email',
                'unique:pendaftaran_asatid,email',
                'unique:pendaftaran_siswas,email',
                'unique:users,email',
            ],
            'password_login'    => ['required', 'string', 'min:8', 'max:100'],

            'berkas_foto'       => ['required', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'                => ':attribute wajib diisi.',
            'image'                   => ':attribute harus berupa gambar.',
            'max'                     => ':attribute terlalu besar atau terlalu panjang.',
            'no_hp.regex'             => 'Format nomor WhatsApp tidak valid (contoh: +6281234567890).',
            '*.regex'                 => 'Kolom ini harus ditulis menggunakan huruf Arab.',
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
            'alamat'            => 'Alamat',
            'no_hp'             => 'Nomor WhatsApp',
            'email_login'       => 'Email login',
            'password_login'    => 'Password login',
            'berkas_foto'       => 'Pas foto',
        ];
    }
}
