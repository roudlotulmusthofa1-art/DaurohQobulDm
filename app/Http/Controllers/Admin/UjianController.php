<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NilaiUjian;
use App\Models\SoalUjian;
use App\Models\Ujian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin > Penilaian > Ujian (tulis).
 * - Tab "Ujian" di Kelas & Pelajaran (datanya dari PenilaianController@kelas): pengaturan ujian + rekap nilai dari asatid.
 * - Panel "Soal Ujian" di tab Ujian (partials/ujian-soal.blade.php): soal kiriman asatid, diperiksa & diedit admin,
 *   lalu disetujui (= dikirim kembali ke asatid). Daftar soalnya dimuat PenilaianController@kelas.
 */
class UjianController extends Controller
{
    /* =====================================================================
     |  Pengaturan Ujian (tombol "Pengaturan Ujian")
     * ===================================================================== */

    /** POST /admin/penilaian/ujian — tambah / ubah ujian (isi "id" = ubah) */
    public function simpanUjian(Request $request): JsonResponse
    {
        // Bulan wajib dipilih. Pengecualian: ujian lama yang belum punya bulan & tidak dikirim bulannya
        // (mis. tombol Aktif/Mati) -> nama lamanya dipakai.
        $lama = $request->filled('id') ? Ujian::with('bulan:bulan_ajaran.id')->find($request->integer('id')) : null;
        $tanpaBulan = $lama && $lama->bulan->isEmpty() && ! $request->filled('bulan_ids');

        $data = $request->validate([
            'id'             => ['nullable', 'integer', 'exists:ujian,id'],
            'bulan_ids'      => [$tanpaBulan ? 'nullable' : 'required', 'array'],
            'bulan_ids.*'    => ['integer', 'exists:bulan_ajaran,id'],
            'bidang_ilmu_id' => ['required', 'integer', 'exists:bidang_ilmu,id'],
            'kitab_ids'      => ['required', 'array', 'min:1'],
            'kitab_ids.*'    => ['integer', Rule::exists('kitab', 'id')->where('bidang_ilmu_id', $request->integer('bidang_ilmu_id'))],
            'nilai_maks'     => ['required', 'integer', 'min:1', 'max:' . Ujian::MAKS_NILAI],
            'cara_soal'      => ['required', Rule::in(array_keys(Ujian::CARA_SOAL))],
            'kelompok_soal'  => ['nullable', 'array'],
            'is_active'      => ['boolean'],
        ], [
            'bulan_ids.required'      => __('Centang minimal satu bulan.'),
            'bidang_ilmu_id.required' => __('Pilih fan dulu.'),
            'kitab_ids.required'      => __('Centang minimal satu kitab.'),
            'kitab_ids.min'           => __('Centang minimal satu kitab.'),
            'kitab_ids.*.exists'      => __('Kitab yang dicentang tidak termasuk fan ini.'),
            'nilai_maks.required'     => __('Isi nilai maksimal.'),
        ]);

        $kitabIds = array_values(array_unique(array_map('intval', $data['kitab_ids'])));
        $kelompok = $data['cara_soal'] === 'kelompok' ? $this->cekKelompok($data['kelompok_soal'] ?? [], $kitabIds) : null;

        $bulanIds = array_values(array_unique(array_map('intval', $data['bulan_ids'] ?? [])));

        DB::transaction(function () use ($data, $kitabIds, $kelompok, $bulanIds, $lama) {
            $ujian = Ujian::updateOrCreate(['id' => $data['id'] ?? null], [
                'nama'           => $bulanIds ? Ujian::namaDariBulan($bulanIds) : $lama?->nama,
                'bidang_ilmu_id' => $data['bidang_ilmu_id'],
                'nilai_maks'     => $data['nilai_maks'],
                'cara_soal'      => $data['cara_soal'],
                'kelompok_soal'  => $kelompok,
                'is_active'      => $data['is_active'] ?? true,
            ]);
            $ujian->kitab()->sync($kitabIds);
            if ($bulanIds) {
                $ujian->bulan()->sync($bulanIds);
            }
        });

        return response()->json([
            'message' => ! empty($data['id']) ? __('Ujian diperbarui.') : __('Ujian ditambahkan.'),
            'ujian'   => Ujian::daftar(),
        ]);
    }

    /**
     * "Atur kelompok": setiap kitab yang dicentang harus masuk tepat satu kelompok.
     * Hasil: [[2, 3], [5]] (kelompok kosong dibuang).
     */
    private function cekKelompok(array $kelompok, array $kitabIds): array
    {
        $hasil = [];
        $terpakai = [];
        foreach ($kelompok as $g) {
            $g = array_values(array_unique(array_map('intval', (array) $g)));
            if (! $g) {
                continue;
            }
            foreach ($g as $id) {
                if (! in_array($id, $kitabIds, true) || in_array($id, $terpakai, true)) {
                    throw ValidationException::withMessages(['kelompok_soal' => __('Setiap kitab hanya boleh masuk satu kelompok.')]);
                }
                $terpakai[] = $id;
            }
            $hasil[] = $g;
        }
        if (count($terpakai) !== count($kitabIds)) {
            throw ValidationException::withMessages(['kelompok_soal' => __('Semua kitab yang dicentang harus dimasukkan ke salah satu kelompok.')]);
        }

        return $hasil;
    }

    /** DELETE /admin/penilaian/ujian/{ujian} — nilai & soal yang sudah ada tetap tersimpan */
    public function hapusUjian(Ujian $ujian): JsonResponse
    {
        $ujian->delete();

        return response()->json(['message' => __('Ujian dihapus. Nilai & soal yang sudah ada tetap tersimpan.'), 'ujian' => Ujian::daftar()]);
    }

    /* =====================================================================
     |  Nilai Ujian (dari asatid): ubah / hapus
     * ===================================================================== */

    /** PUT /admin/penilaian/nilai-ujian/{nilai} — ubah nilai (0..nilai maksimal) */
    public function ubahNilai(Request $request, NilaiUjian $nilai): JsonResponse
    {
        $data = $request->validate([
            'nilai'   => ['required', 'integer', 'min:0', 'max:' . (int) $nilai->nilai_maks],
            'tanggal' => ['required', 'date'],
        ], [
            'nilai.max' => __('Nilai paling tinggi :maks.', ['maks' => $nilai->nilai_maks]),
        ]);

        $nilai->update($data);
        $nilai->load(NilaiUjian::MUAT);

        return response()->json([
            'message' => __('Nilai ujian :nama diperbarui.', ['nama' => $nilai->siswa?->nama_lengkap]),
            'baris'   => $nilai->baris(),
        ]);
    }

    /** DELETE /admin/penilaian/nilai-ujian/{nilai} */
    public function hapusNilai(NilaiUjian $nilai): JsonResponse
    {
        $nilai->delete();

        return response()->json(['message' => __('Nilai ujian dihapus.')]);
    }

    /* =====================================================================
     |  Soal Ujian (panel di tab Ujian)
     * ===================================================================== */

    /**
     * PUT /admin/penilaian/soal-ujian/{soal} — admin mengedit soal.
     * setujui = true -> status "disetujui" (terkirim ke asatid). Kiriman asli asatid disimpan sekali di "butir_asli".
     */
    public function simpanSoal(Request $request, SoalUjian $soal): JsonResponse
    {
        $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:5000'],
            'setujui'       => ['boolean'],
        ]);
        $butir = SoalUjian::butirDari($request);

        $soal->butir_asli ??= $soal->butir;
        $soal->butir = $butir;
        $soal->catatan_admin = trim((string) $request->input('catatan_admin')) ?: null;
        if ($request->boolean('setujui')) {
            $soal->status = SoalUjian::DISETUJUI;
            $soal->diperiksa_oleh = Auth::id();
            $soal->diperiksa_at = now();
        }
        $soal->save();
        $soal->load(['pembuat:id,name', 'pemeriksa:id,name']);

        return response()->json([
            'message' => $request->boolean('setujui')
                ? __('Soal disetujui dan dikirim ke :nama.', ['nama' => $soal->pembuat?->name ?? __('asatid')])
                : __('Perubahan soal disimpan.'),
            'baris' => $soal->baris(),
        ]);
    }

    /** DELETE /admin/penilaian/soal-ujian/{soal} */
    public function hapusSoal(SoalUjian $soal): JsonResponse
    {
        $soal->delete();

        return response()->json(['message' => __('Soal dihapus.')]);
    }
}
