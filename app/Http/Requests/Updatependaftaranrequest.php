<?php

namespace App\Http\Requests;

use App\Models\PendaftaranSiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan isian saat ADMIN mengedit data pendaftar.
 * Sengaja tidak memaksa huruf Arab (admin boleh memperbaiki data apa adanya).
 * Status, nomor pendaftaran, kode QR, dan akun tidak bisa diubah dari sini.
 */
class UpdatePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pembatasan akses sudah dilakukan middleware route (auth + peran:admin)
        return true;
    }

    public function rules(): array
    {
        $hp = ['required', 'regex:/^(\+62|62)8[0-9]{7,12}$/'];

        return [
            // Data pribadi
            'nama_lengkap'      => 'required|string|max:150',
            'nama_lengkap_arab' => 'required|string|max:150',
            'tempat_lahir'      => 'required|string|max:100',
            'tanggal_lahir'     => 'required|date|before:today',
            'status_pernikahan' => ['required', Rule::in(array_keys(PendaftaranSiswa::STATUS_PERNIKAHAN))],
            'no_hp'             => $hp,

            // Alamat
            'provinsi'  => 'required|string|max:100',
            'kota'      => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'alamat'    => 'required|string|max:500',

            // Wali & muarrif
            'nama_wali'      => 'required|string|max:150',
            'pekerjaan_wali' => 'nullable|string|max:150',
            'hubungan_wali'  => 'nullable|string|max:100',
            'no_hp_wali'     => $hp,
            'nama_muarrif'   => 'required|string|max:150',
            'no_hp_muarrif'  => $hp,
            'nama_butuh'     => 'required|string|max:150',
            'no_hp_nya'      => $hp,

            // Pendidikan formal
            'pendidikan_terakhir' => ['required', Rule::in(array_keys(PendaftaranSiswa::PENDIDIKAN_TERAKHIR))],
            'nama_sekolah'        => 'required|string|max:150',
            'nama_daerah'         => 'required|string|max:150',

            // Riwayat pondok (daftar; minimal 1, maksimal 5)
            'riwayat_pondok'                 => 'required|array|min:1|max:5',
            'riwayat_pondok.*.asal_pondok'   => 'required|string|max:150',
            'riwayat_pondok.*.nama_pengasuh' => 'required|string|max:150',
            'riwayat_pondok.*.nama_daerah'   => 'required|string|max:150',
            'riwayat_pondok.*.tahun_belajar' => 'required|integer|min:0|max:30',

            // Riwayat kitab (daftar; minimal 1, maksimal 20)
            'riwayat_kitab'                  => 'required|array|min:1|max:20',
            'riwayat_kitab.*.nama_kitab'     => 'required|string|max:150',
            'riwayat_kitab.*.fan_kitab'      => ['required', Rule::in(PendaftaranSiswa::FAN_KITAB)],
            'riwayat_kitab.*.sudah_diujikan' => ['required', Rule::in(['sudah', 'belum'])],

            // Data tambahan
            'marhalah'         => 'required|string|max:150',
            'lama_belajar'     => 'required|string|max:150',
            'kesehatan'        => 'required|string|max:255',
            'status_ekonomi'   => 'required|string|max:255',
            'persentasimuslim' => 'required|numeric|min:0|max:100',
            'alumni'           => 'required|string|max:255',
            'tujuan'           => 'required|string|max:2000',
            'rencana'          => 'required|string|max:2000',

            // Berkas: boleh kosong (berarti tidak diganti)
            'berkas_foto'          => 'nullable|image|max:2048',
            'berkas_rekomendasi'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'berkas_raport_ijazah' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'berkas_izin_belajar'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute terlalu panjang atau terlalu besar.',
            'in'       => ':attribute tidak valid.',
            'regex'    => 'Format :attribute tidak valid (contoh: 6281234567890 atau +6281234567890).',
            'image'    => ':attribute harus berupa gambar.',
            'mimes'    => ':attribute harus berformat JPG, PNG, atau PDF.',
            'integer'  => ':attribute harus berupa bilangan bulat.',
            'numeric'  => ':attribute harus berupa angka.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'Nama lengkap',
            'nama_lengkap_arab' => 'Nama lengkap (Arab)',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'status_pernikahan' => 'Status pernikahan',
            'no_hp' => 'No. WhatsApp',
            'provinsi' => 'Provinsi',
            'kota' => 'Kota/Kabupaten',
            'kecamatan' => 'Kecamatan',
            'alamat' => 'Alamat',
            'nama_wali' => 'Nama wali',
            'no_hp_wali' => 'No. WhatsApp wali',
            'nama_muarrif' => 'Nama muarrif',
            'no_hp_muarrif' => 'No. WhatsApp muarrif',
            'nama_butuh' => 'Nama kontak darurat',
            'no_hp_nya' => 'No. WhatsApp kontak darurat',
            'pendidikan_terakhir' => 'Pendidikan terakhir',
            'nama_sekolah' => 'Nama sekolah',
            'nama_daerah' => 'Daerah lembaga',
            'riwayat_pondok' => 'Riwayat pondok',
            'riwayat_pondok.*.asal_pondok' => 'Asal pondok',
            'riwayat_pondok.*.nama_pengasuh' => 'Nama pengasuh',
            'riwayat_pondok.*.nama_daerah' => 'Daerah pondok',
            'riwayat_pondok.*.tahun_belajar' => 'Lama belajar di pondok',
            'riwayat_kitab' => 'Riwayat kitab',
            'riwayat_kitab.*.nama_kitab' => 'Nama kitab',
            'riwayat_kitab.*.fan_kitab' => 'Fan kitab',
            'riwayat_kitab.*.sudah_diujikan' => 'Status ujian kitab',
            'marhalah' => 'Marhalah',
            'lama_belajar' => 'Lama belajar',
            'kesehatan' => 'Kesehatan',
            'status_ekonomi' => 'Status ekonomi',
            'persentasimuslim' => 'Persentase muslim',
            'alumni' => 'Alumni',
            'tujuan' => 'Tujuan belajar',
            'rencana' => 'Rencana setelah lulus',
            'berkas_foto' => 'Pas foto',
            'berkas_rekomendasi' => 'Surat rekomendasi',
            'berkas_raport_ijazah' => 'Raport/ijazah',
            'berkas_izin_belajar' => 'Surat izin belajar',
        ];
    }
}
