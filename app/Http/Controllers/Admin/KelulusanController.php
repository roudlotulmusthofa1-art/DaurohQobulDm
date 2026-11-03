<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin > Akademik > Kelulusan (kelulusan AKHIR dauroh, bukan hasil seleksi pendaftaran).
 * - Isi Lulus / Tidak Lulus, predikat & catatan untuk santri yang sudah diterima (sesi aktif).
 * - Hasil baru terlihat oleh peserta setelah admin menekan "Umumkan" (pengaturan: kelulusan_diumumkan_at).
 * Tampilan & pengisian massal diatur Vue di resources/views/admin/akademik/kelulusan.blade.php.
 */
class KelulusanController extends Controller
{
    public function index()
    {
        $santri = PendaftaranSiswa::with('kelasMaster')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn (PendaftaranSiswa $s) => [
                'id'       => $s->id,
                'nama'     => $s->nama_lengkap,
                'arab'     => $s->nama_lengkap_arab,
                'nomor'    => $s->nomor_pendaftaran,
                'foto'     => $s->foto_url,
                'kelas_id' => $s->kelas_id,
                'kelas'    => $s->kelasMaster?->nama,
                'status'   => $s->status_kelulusan ?? '',
                'predikat' => $s->predikat_kelulusan ?? '',
                'catatan'  => $s->catatan_kelulusan ?? '',
            ]);

        return view('admin.akademik.kelulusan', [
            'santri'     => $santri,
            'kelas'      => Kelas::ordered()->get(['id', 'nama']),
            'predikat'   => PendaftaranSiswa::PREDIKAT,
            'diumumkan'  => PendaftaranSiswa::kelulusanDiumumkan(),
        ]);
    }

    /** Simpan hasil kelulusan (hanya baris yang diubah dikirim dari halaman) */
    public function simpan(Request $request)
    {
        $data = $request->validate([
            'hasil'              => ['required', 'array'],
            'hasil.*.status'     => ['nullable', Rule::in([PendaftaranSiswa::LULUS, PendaftaranSiswa::TIDAK_LULUS])],
            'hasil.*.predikat'   => ['nullable', Rule::in(PendaftaranSiswa::PREDIKAT)],
            'hasil.*.catatan'    => ['nullable', 'string', 'max:500'],
        ], [
            'hasil.required'        => 'Belum ada perubahan untuk disimpan.',
            'hasil.*.catatan.max'   => 'Catatan kelulusan maksimal 500 karakter.',
        ]);

        $santri = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->whereIn('id', array_keys($data['hasil']))->get();

        foreach ($santri as $s) {
            $h = $data['hasil'][$s->id];
            $status = $h['status'] ?? null;
            $s->forceFill([
                'status_kelulusan'   => $status,
                // Predikat hanya untuk yang lulus
                'predikat_kelulusan' => $status === PendaftaranSiswa::LULUS ? ($h['predikat'] ?? null) : null,
                'catatan_kelulusan'  => $h['catatan'] ?? null,
            ])->saveQuietly();
        }

        return back()->with('success', $santri->count() . ' data kelulusan disimpan.');
    }

    /** Umumkan: hasil kelulusan mulai terlihat di dasbor peserta */
    public function umumkan()
    {
        Pengaturan::simpan('kelulusan_diumumkan_at', now()->toDateTimeString());

        return back()->with('success', 'Kelulusan diumumkan. Peserta sekarang bisa melihat hasilnya di dasbor.');
    }

    /** Tarik pengumuman: peserta kembali melihat "Belum diumumkan" */
    public function tarik()
    {
        Pengaturan::simpan('kelulusan_diumumkan_at', '');

        return back()->with('success', 'Pengumuman kelulusan ditarik. Hasil tidak lagi terlihat oleh peserta.');
    }
}
