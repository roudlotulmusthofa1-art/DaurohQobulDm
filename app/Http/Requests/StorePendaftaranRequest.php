<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Data belum valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }



    public function rules(): array
    {
        $polaArab = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}-]+\z/u';

        return [
            // Step 1: Data pribadi
            'nama_lengkap' => [
                'required',
                'string',
                'max:150',
            ],

            'nama_lengkap_arab' => [
                'required',
                'string',
                'max:150',
                "regex:$polaArab",
            ],

            'tempat_lahir' => [
                'required',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'tanggal_lahir' => [
                'required',
                'date',
                'before:today',
            ],

            'status_pernikahan' => [
                'required',
                'in:K,L',
            ],

            'no_hp' => [
                'required',
                'string',
                'regex:/^\+62[0-9]+$/',
                'max:20',
            ],

            // Step 2: Alamat
            'provinsi' => [
                'required',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'kota' => [
                'required',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'kecamatan' => [
                'required',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'kelurahan' => [
                'nullable',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'alamat' => [
                'required',
                'string',
                'max:500',
                "regex:$polaArab",
            ],

            // Step 3: Data wali dan penghubung
            'nama_wali' => [
                'required',
                'string',
                'max:150',
                "regex:$polaArab",
            ],

            'pekerjaan_wali' => [
                'nullable',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'hubungan_wali' => [
                'nullable',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'no_hp_wali' => [
                'required',
                'regex:/^(\+62|62)8[0-9]{7,12}$/',
            ],

            'nama_muarrif' => [
                'nullable',
                'string',
                'max:150',
                "regex:$polaArab",
            ],

            'no_hp_muarrif' => [
                'nullable',
                'regex:/^(\+62|62)8[0-9]{7,12}$/',
            ],

            'nama_butuh' => [
                'nullable',
                'string',
                'max:150',
                "regex:$polaArab",
            ],

            'no_hp_nya' => [
                'nullable',
                'regex:/^(\+62|62)8[0-9]{7,12}$/',
            ],

            // Step 4: Riwayat pendidikan pondok
            'riwayat_pondok' => [
                'required',
                'array',
                'min:1',
                'max:5',
            ],

            'riwayat_pondok.*.asal_pondok' => [
                'required',
                'string',
                'max:255',
            ],

            'riwayat_pondok.*.nama_pengasuh' => [
                'required',
                'string',
                'max:255',
            ],

            'riwayat_pondok.*.nama_daerah' => [
                'required',
                'string',
                'max:255',
            ],

            'riwayat_pondok.*.tahun_belajar' => [
                'required',
                'integer',
                'min:0',
                'max:30',
            ],

            'pendidikan_terakhir' => [
                'required',
                'in:K,L,M,o',
            ],

            'nama_sekolah' => [
                'nullable',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            'nama_daerah' => [
                'nullable',
                'string',
                'max:100',
                "regex:$polaArab",
            ],

            // Riwayat kitab
            'riwayat_kitab' => [
                'required',
                'array',
                'min:1',
                'max:20',
            ],

            'riwayat_kitab.*.nama_kitab' => [
                'required',
                'string',
                'max:255',
            ],

            'riwayat_kitab.*.fan_kitab' => [
                'required',
                'string',
                'in:فقه,أصول الفقه,نحو,صرف,توحيد,مصطلح الحديث,تجويد,سيرة النبي,منطق,وغيرها',
            ],

            'riwayat_kitab.*.sudah_diujikan' => [
                'required',
                'in:sudah,belum',
            ],

            // Step 5: Data tambahan dan akun
            'marhalah' => [
                'required',
                'string',
                'max:100',
            ],

            'lama_belajar' => [
                'required',
                'string',
                'max:100',
            ],

            'kesehatan' => [
                'required',
                'string',
                'max:255',
            ],

            'status_ekonomi' => [
                'required',
                'string',
                'max:100',
            ],

            'persentasimuslim' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'alumni' => [
                'required',
                'string',
                'max:100',
            ],

            'tujuan' => [
                'required',
                'string',
                'max:500',
            ],

            'rencana' => [
                'required',
                'string',
                'max:500',
            ],

            'email_login'    => ['required', 'email', 'max:255', 'unique:pendaftaran_siswas,email', 'unique:users,email'],
            'password_login' => ['required', 'string', 'min:8', 'max:100'],

            // Step 6: Berkas dan persetujuan
            'berkas_foto' => [
                'required',
                'image',
                'max:5120',
            ],

            'berkas_rekomendasi' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'berkas_raport_ijazah' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'berkas_izin_belajar' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            // Catatan peserta untuk admin (boleh kosong)
            'catatan_peserta' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'setuju' => [
                'accepted',
            ],
        ];
    }



    protected function prepareForValidation(): void
    {
        $riwayat = $this->input('riwayat_pondok');

        if (is_string($riwayat)) {
            $decoded = json_decode($riwayat, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'riwayat_pondok' => $decoded,
                ]);
            }
        }
    }

    public function messages(): array
    {
        return [
            'required'         => ':attribute wajib diisi.',
            'digits'           => ':attribute harus :digits digit angka.',
            'image'            => ':attribute harus berupa gambar.',
            'max'              => ':attribute terlalu besar atau terlalu panjang.',
            'accepted'         => 'Anda harus menyetujui pernyataan kebenaran data.',
            'berkas_*.max'      => ':attribute maksimal 5 MB.',
            'berkas_*.uploaded' => ':attribute gagal diunggah. Pastikan ukurannya maksimal 5 MB lalu coba lagi.',
            'no_hp_wali.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
            'no_hp.regex' => 'Format nomor WhatsApp tidak valid (contoh: +6281234567890).',

            'riwayat_kitab.required' => 'Riwayat kitab wajib diisi.',
            'riwayat_kitab.min' => 'Minimal masukkan satu kitab.',
            'riwayat_kitab.max' => 'Maksimal 20 kitab.',
            'riwayat_kitab.*.nama_kitab.required' => 'Nama kitab wajib diisi.',
            'riwayat_kitab.*.fan_kitab.required' => 'Fan kitab wajib dipilih.',
            'riwayat_kitab.*.fan_kitab.in' => 'Pilihan fan kitab tidak valid.',
            'riwayat_kitab.*.sudah_diujikan.required' => 'Status ujian wajib dipilih.',
            'riwayat_kitab.*.sudah_diujikan.in' => 'Status ujian tidak valid.',
            '*.regex' => 'Kolom ini harus ditulis menggunakan huruf Arab.',
            'email_login.required' => 'Email login wajib diisi.',
            'email_login.email' => 'Format email tidak valid.',
            'email_login.unique' => 'Email ini sudah digunakan. Silakan gunakan email lain.',
            'password_login.required' => 'Password login wajib diisi.',
            'password_login.min' => 'Password minimal 8 karakter.',


        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'Nama lengkap',
            'nama_lengkap_arab' => 'Nama lengkap bahasa arab',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'status_pernikahan' => 'Status pernikahan',
            'no_hp' => 'Nomor WhatsApp',
            'alamat' => 'Alamat',
            'provinsi' => 'Provinsi',
            'kota' => 'Kota/Kabupaten',
            'kecamatan' => 'Kecamatan',
            'hubungan_wali' => 'Hubungan dengan wali',
            'no_hp_wali' => 'Nomor WhatsApp',
            'nama_muarrif' => 'Nama Muarrif',
            'no_hp_muarrif' => 'Nomor WhatsApp',
            'nama_butuh' => 'Nama Yang Bisa Di Hubungi',
            'no_hp_nya' => 'Nomor WhatsApp',
            'asal_pondok' => 'Asal pondok',
            'nama_pengasuh' => 'Nama pengasuh',
            'nama_daerah' => 'Nama daerah',
            'tahun_belajar' => 'Tahun belajar',
            'nama_kitab' => 'Nama kitab',
            'fan_kitab' => 'Fan kitab',
            'sudah_diujikan' => 'Status ujian',
            'marhalah' => 'Marhalah',
            'lama_belajar' => 'Lama belajar',
            'kesehatan' => 'Kesehatan',
            'status_ekonomi' => 'Status ekonomi',
            'persentasimuslim' => 'Persentase muslim',
            'alumni' => 'Alumni',
            'tujuan' => 'Tujuan',
            'rencana' => 'Rencana',
            'email_login' => 'Email login',
            'password_login' => 'Password login',
            'berkas_foto' => 'Berkas pas foto',
            'berkas_rekomendasi' => 'Berkas surat rekomendasi',
            'berkas_raport_ijazah' => 'Berkas raport/ijazah terakhir',
            'berkas_izin_belajar' => 'Berkas surat izin belajar dari orang tua/wali',
            'catatan_peserta' => 'Catatan untuk admin',
            'setuju' => 'Pernyataan kebenaran data',
        ];
    }
}
