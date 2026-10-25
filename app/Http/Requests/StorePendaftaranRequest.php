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
        return [
            // Step 1
            'nama_lengkap'    => 'required|string|max:150',
            'nama_lengkap_arab' => 'required|string|max:150',
            'nik'             => 'required|digits:16',
            'nisn'            => 'nullable|digits:10',
            'jenis_kelamin'   => 'required|in:L,P',
            'tempat_lahir'    => 'required|string|max:100',
            'tanggal_lahir'   => 'required|date|before:today',
            'anak_ke'         => 'nullable|integer|min:1|max:30',
            'jumlah_saudara'  => 'nullable|integer|min:0|max:30',
            // Step 2
            'alamat'          => 'required|string|max:500',
            'provinsi'        => 'required|string|max:100',
            'kota'            => 'required|string|max:100',
            'kecamatan'       => 'required|string|max:100',
            'kelurahan'       => 'nullable|string|max:100',
            'kode_pos'        => 'nullable|string|max:10',
            // Step 3
            'nama_ayah'       => 'required|string|max:150',
            'pekerjaan_ayah'  => 'nullable|string|max:100',
            'nama_ibu'        => 'required|string|max:150',
            'pekerjaan_ibu'   => 'nullable|string|max:100',
            'nama_wali'       => 'nullable|string|max:150',
            'no_hp_wali'      => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            // Step 4
            'asal_sekolah'    => 'required|string|max:150',
            'tahun_lulus'     => 'nullable|digits:4',
            'program_pilihan' => 'required|string|max:100',
            'hafalan_juz'     => 'nullable|integer|min:0|max:30',
            // Step 5
            'berkas_foto'     => 'required|image|max:2048',
            'berkas_kk'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'berkas_akta'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'setuju'          => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'required'         => ':attribute wajib diisi.',
            'digits'           => ':attribute harus :digits digit angka.',
            'image'            => ':attribute harus berupa gambar.',
            'max'              => ':attribute terlalu besar atau terlalu panjang.',
            'accepted'         => 'Anda harus menyetujui pernyataan kebenaran data.',
            'no_hp_wali.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'Nama lengkap',
            'nama_lengkap_arab' => 'Nama lengkap bahasa arab',
            'nik' => 'NIK',
            'nisn' => 'NISN',
            'jenis_kelamin' => 'Jenis kelamin',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'alamat' => 'Alamat',
            'provinsi' => 'Provinsi',
            'kota' => 'Kota/Kabupaten',
            'kecamatan' => 'Kecamatan',
            'nama_ayah' => 'Nama ayah',
            'nama_ibu' => 'Nama ibu',
            'no_hp_wali' => 'Nomor WhatsApp',
            'asal_sekolah' => 'Asal sekolah',
            'program_pilihan' => 'Program pilihan',
            'berkas_foto' => 'Pas foto',
            'berkas_kk' => 'Kartu Keluarga',
            'berkas_akta' => 'Akta kelahiran',
        ];
    }
}
