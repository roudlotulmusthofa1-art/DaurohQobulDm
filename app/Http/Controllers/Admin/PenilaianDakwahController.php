<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NilaiDakwah;
use App\Models\PendaftaranSiswa;
use App\Models\PilihanDakwah;
use App\Support\DataCatatan;
use App\Support\NamaArab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Admin > Penilaian > Penilaian Dakwah (dua panel: Khitoba' · Khurujud Dakwah)
 * dan Admin > Laporan > Laporan Dakwah (ringkasan gabungan keduanya).
 *
 * Alur menilai: tombol "Beri Nilai" -> santri -> (waktu khitoba') -> tanggal -> nilai -> catatan.
 * Pilihan nilai & waktu ditulis admin sendiri (tabel pilihan_dakwah, lihat App\Models\PilihanDakwah).
 */
class PenilaianDakwahController extends Controller
{
    /** GET /admin/penilaian/dakwah?tab=khitobah|khuruj */
    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->query('tab'), NilaiDakwah::JENIS) ? $request->query('tab') : NilaiDakwah::KHITOBAH;

        return view('admin.penilaian.dakwah', [
            'tab'     => $tab,
            'pilihan' => PilihanDakwah::untukVue(),
            'siswa'   => DataCatatan::daftarSiswa(),
            'nilai'   => NilaiDakwah::lengkap()->orderByDesc('tanggal')->orderByDesc('id')->get()
                ->tap(fn ($n) => NamaArab::untukUser($n->pluck('dinilai_oleh'))) // nama Arab penilai sekaligus
                ->map(fn (NilaiDakwah $n) => $n->baris())->values(),
            'hariIni' => now('Asia/Jakarta')->toDateString(),
        ]);
    }

    /** POST /admin/penilaian/dakwah/pilihan — tambah satu pilihan (tombol "+") */
    public function simpanPilihan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelompok' => ['required', Rule::in(array_keys(PilihanDakwah::KELOMPOK))],
            'nama'     => [
                'required', 'string', 'max:100',
                Rule::unique('pilihan_dakwah', 'nama')->where('kelompok', $request->input('kelompok')),
            ],
        ], [
            'nama.required' => __('Tulis namanya dulu.'),
            'nama.unique'   => __('Nama ini sudah ada.'),
        ]);

        $pilihan = PilihanDakwah::create($data + [
            'urutan' => (int) PilihanDakwah::where('kelompok', $data['kelompok'])->max('urutan') + 1,
        ]);

        return response()->json([
            'message' => __('Pilihan ditambahkan.'),
            'pilihan' => ['id' => $pilihan->id, 'nama' => $pilihan->nama],
        ]);
    }

    /** DELETE /admin/penilaian/dakwah/pilihan/{pilihan} — nilai lama yang memakai nama ini tetap ada */
    public function hapusPilihan(PilihanDakwah $pilihan): JsonResponse
    {
        $pilihan->delete();

        return response()->json(['message' => __('Pilihan dihapus.')]);
    }

    /** POST /admin/penilaian/dakwah/nilai — simpan satu penilaian */
    public function simpanNilai(Request $request): JsonResponse
    {
        $nilai = NilaiDakwah::create($this->isianNilai($request) + ['dinilai_oleh' => Auth::id()]);

        return response()->json([
            'message' => __('Nilai :nama disimpan.', ['nama' => $nilai->siswa?->nama_lengkap]),
            'baris'   => NilaiDakwah::lengkap()->find($nilai->id)->baris(),
        ]);
    }

    /** PUT /admin/penilaian/dakwah/nilai/{nilai} — ubah satu penilaian (penilai awal tetap tercatat) */
    public function ubahNilai(Request $request, NilaiDakwah $nilai): JsonResponse
    {
        $nilai->update($this->isianNilai($request, $nilai));

        return response()->json([
            'message' => __('Nilai :nama diperbarui.', ['nama' => $nilai->siswa?->nama_lengkap]),
            'baris'   => NilaiDakwah::lengkap()->find($nilai->id)->baris(),
        ]);
    }

    /**
     * Cek isian Beri Nilai / Ubah Nilai -> kolom tabel nilai_dakwah.
     * Saat mengubah, nama nilai / waktu yang lama tetap boleh dipakai walau pilihannya sudah dihapus.
     */
    private function isianNilai(Request $request, ?NilaiDakwah $lama = null): array
    {
        $khitobah = $request->input('jenis') === NilaiDakwah::KHITOBAH;
        $kelompokNilai = $khitobah ? 'nilai_khitobah' : 'nilai_khuruj';
        // Harus ada di pengaturan, kecuali isiannya sama dengan nilai lama (tidak diganti)
        $adaDi = fn (string $kelompok, string $kolom, ?string $nilaiLama) => $nilaiLama !== null && $request->input($kolom) === $nilaiLama
            ? null
            : Rule::exists('pilihan_dakwah', 'nama')->where('kelompok', $kelompok);

        $data = $request->validate([
            'jenis'    => ['required', Rule::in(array_keys(NilaiDakwah::JENIS))],
            'siswa_id' => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'waktu'    => $khitobah
                ? array_filter(['required', 'string', 'max:100', $adaDi('waktu_khitobah', 'waktu', $lama?->waktu)])
                : ['nullable'],
            'tanggal'  => ['required', 'date'],
            'nilai'    => array_filter(['required', 'string', 'max:100', $adaDi($kelompokNilai, 'nilai', $lama?->nilai)]),
            'catatan'  => ['nullable', 'string', 'max:5000'],
        ], [
            'siswa_id.required' => __('Pilih santri dulu.'),
            'siswa_id.exists'   => __('Santri tidak ditemukan / belum diterima.'),
            'waktu.required'    => __("Pilih waktu khitoba' dulu."),
            'waktu.exists'      => __("Waktu khitoba' tidak ada di pengaturan."),
            'tanggal.required'  => __('Isi tanggal dulu.'),
            'nilai.required'    => __('Pilih nilai dulu.'),
            'nilai.exists'      => __('Nilai tidak ada di pengaturan.'),
        ]);

        return [
            'jenis'                => $data['jenis'],
            'pendaftaran_siswa_id' => $data['siswa_id'],
            'waktu'                => $khitobah ? $data['waktu'] : null,
            'tanggal'              => $data['tanggal'],
            'nilai'                => $data['nilai'],
            'catatan'              => trim((string) ($data['catatan'] ?? '')) ?: null,
        ];
    }

    /** DELETE /admin/penilaian/dakwah/nilai/{nilai} */
    public function hapusNilai(NilaiDakwah $nilai): JsonResponse
    {
        $nilai->delete();

        return response()->json(['message' => __('Nilai dihapus.')]);
    }

    /** GET /admin/laporan-dakwah?dari=&sampai= — ringkasan Khitoba' + Khurujud Dakwah (rekap dihitung Vue) */
    public function laporan(Request $request)
    {
        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        return view('admin.laporan-dakwah.index', [
            'nilai' => NilaiDakwah::lengkap()
                ->when($f['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
                ->when($f['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
                ->orderByDesc('tanggal')->orderByDesc('id')->get()
                ->tap(fn ($n) => NamaArab::untukUser($n->pluck('dinilai_oleh'))) // nama Arab penilai sekaligus
                ->map(fn (NilaiDakwah $n) => $n->baris())->values(),
            'pilihan' => PilihanDakwah::untukVue(),
            'dari'    => $f['dari'] ?? null,
            'sampai'  => $f['sampai'] ?? null,
        ]);
    }
}
