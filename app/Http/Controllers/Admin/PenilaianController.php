<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\BidangIlmu;
use App\Models\BulanAjaran;
use App\Models\CatatanSiswa;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\NilaiHarian;
use App\Models\NilaiUjianLisan;
use App\Models\UjianLisan;
use App\Support\DataCatatan;
use App\Support\NamaArab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin > Penilaian.
 * - Kelas & Pelajaran : Nilai Harian (6 penilaian dari asatid, lihat App\Models\NilaiHarian)
 *                       / Ujian Lisan (pengaturan + nilai dari asatid & admin, lihat App\Models\UjianLisan & NilaiUjianLisan)
 *                       / Ujian (alurnya menyusul)
 * - Catatan           : catatan Pencapaian & Pelanggaran dari asatid
 *                       (+ Terlambat/Alfa dari absensi sebagai pelanggaran), lihat App\Support\DataCatatan
 */
class PenilaianController extends Controller
{
    public const BAGIAN_KELAS = ['harian', 'lisan', 'ujian'];

    /**
     * GET /admin/penilaian/kelas?tab=harian|lisan|ujian
     * Nilai Harian disaring: &kelas_id=&kitab_id=&bulan_id= (kosong = bulan ajaran aktif, "semua" = semua bulan);
     * satu santri + kitab + bulan ajaran = satu nilai. Rekap per santri dihitung Vue.
     */
    public function kelas(Request $request)
    {
        $tab = in_array($request->query('tab'), self::BAGIAN_KELAS, true) ? $request->query('tab') : 'harian';

        $f = $request->validate([
            'kelas_id' => ['nullable', 'integer'],
            'kitab_id' => ['nullable', 'integer'],
            'bulan_id' => ['nullable', 'regex:/^(\d+|semua)$/'],
        ]);
        $bulanId = $f['bulan_id'] ?? (string) (BulanAjaran::aktif()?->id ?? 'semua');

        $nilai = NilaiHarian::with(['siswa', 'kelas:id,nama', 'kitab:id,nama', 'bulanAjaran:id,nama', 'penilai:id,name'])
            ->when($f['kelas_id'] ?? null, fn ($q, $v) => $q->where('kelas_id', $v))
            ->when($f['kitab_id'] ?? null, fn ($q, $v) => $q->where('kitab_id', $v))
            ->when($bulanId !== 'semua', fn ($q) => $q->where('bulan_ajaran_id', $bulanId))
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->get()
            ->tap(fn ($n) => NamaArab::untukUser($n->pluck('dinilai_oleh'))) // nama Arab penilai sekaligus
            ->map(fn (NilaiHarian $n) => $n->baris())->values();

        return view('admin.penilaian.kelas', [
            'tab'       => $tab,
            'nilai'     => $nilai,
            'aspek'     => NilaiHarian::daftarAspek(),
            'kelasList' => Kelas::ordered()->get(['id', 'nama']),
            'kitabList' => Kitab::orderBy('nama')->get(['id', 'nama']),
            'bulanList' => BulanAjaran::with('tahunAjaran:id,nama')->orderByDesc('tahun_ajaran_id')->orderByDesc('urutan')
                ->get(['id', 'tahun_ajaran_id', 'nama', 'urutan', 'is_active']),
            'saring'    => [
                'kelas_id' => $f['kelas_id'] ?? null,
                'kitab_id' => $f['kitab_id'] ?? null,
                'bulan_id' => $bulanId,
            ],
            'saringDiubah' => (bool) array_filter($f),
            // Ujian Lisan
            'ujianLisan' => UjianLisan::daftar(),
            'nilaiLisan' => NilaiUjianLisan::with(['siswa.kelasMaster', 'penilai:id,name'])
                ->orderByDesc('tanggal')->orderByDesc('id')->get()
                ->tap(fn ($n) => NamaArab::untukUser($n->pluck('dinilai_oleh'))) // nama Arab penilai sekaligus
                ->map(fn (NilaiUjianLisan $n) => $n->baris())->values(),
            'fanList'    => BidangIlmu::orderBy('urutan')->orderBy('nama')->get(['id', 'nama']),
            'kitabFan'   => Kitab::orderBy('nama')->get(['id', 'nama', 'bidang_ilmu_id']),
            'siswaList'  => DataCatatan::daftarSiswa(),
        ]);
    }

    /* =====================================================================
     |  Ujian Lisan
     * ===================================================================== */

    /** POST /admin/penilaian/ujian-lisan — tambah / ubah pengaturan ujian (isi "id" = ubah) */
    public function simpanUjianLisan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id'                 => ['nullable', 'integer', 'exists:ujian_lisan,id'],
            'bidang_ilmu_id'     => ['required', 'integer', 'exists:bidang_ilmu,id'],
            'kitab_id'           => ['nullable', 'integer', Rule::exists('kitab', 'id')->where('bidang_ilmu_id', (int) $request->input('bidang_ilmu_id'))],
            'nilai_pertanyaan'   => ['required', 'array', 'min:1', 'max:' . UjianLisan::MAKS_PERTANYAAN],
            'nilai_pertanyaan.*' => ['required', 'integer', 'min:1', 'max:' . UjianLisan::MAKS_NILAI],
            'is_active'          => ['boolean'],
        ], [
            'bidang_ilmu_id.required'     => __('Pilih fan / kategori.'),
            'bidang_ilmu_id.exists'       => __('Fan / kategori tidak ditemukan.'),
            'kitab_id.exists'             => __('Kitab ini bukan dari fan yang dipilih.'),
            'nilai_pertanyaan.required'   => __('Isi jumlah pertanyaan.'),
            'nilai_pertanyaan.max'        => __('Pertanyaan paling banyak :max.'),
            'nilai_pertanyaan.*.required' => __('Nilai tiap pertanyaan wajib diisi.'),
            'nilai_pertanyaan.*.min'      => __('Nilai tiap pertanyaan minimal 1.'),
            'nilai_pertanyaan.*.max'      => __('Nilai tiap pertanyaan paling besar :max.'),
        ]);

        $ujian = UjianLisan::findOrNew($data['id'] ?? null);
        $ujian->fill([
            'bidang_ilmu_id'   => $data['bidang_ilmu_id'],
            'kitab_id'         => $data['kitab_id'] ?? null,
            'nilai_pertanyaan' => $data['nilai_pertanyaan'],
            'is_active'        => $data['is_active'] ?? true,
        ])->save();

        return response()->json([
            'message' => $ujian->wasRecentlyCreated ? __('Ujian lisan ditambahkan.') : __('Pengaturan ujian lisan disimpan.'),
            'ujian'   => UjianLisan::daftar(),
        ]);
    }

    /** DELETE /admin/penilaian/ujian-lisan/{ujian} — nilai yang sudah ada tetap tersimpan (nama fan & kitab sudah disalin) */
    public function hapusUjianLisan(UjianLisan $ujian): JsonResponse
    {
        $ujian->delete();

        return response()->json(['message' => __('Ujian lisan dihapus. Nilai yang sudah ada tetap tersimpan.'), 'ujian' => UjianLisan::daftar()]);
    }

    /** POST /admin/penilaian/nilai-lisan — admin ikut menilai (sama seperti asatid) */
    public function simpanNilaiLisan(Request $request): JsonResponse
    {
        $nilai = NilaiUjianLisan::simpanDari($request);

        return response()->json([
            'message' => __('Nilai ujian lisan :nama tersimpan: :total dari :maks.', [
                'nama' => $nilai->siswa?->nama_lengkap, 'total' => $nilai->total, 'maks' => $nilai->total_maks,
            ]),
            'baris' => $nilai->baris(),
        ]);
    }

    /** DELETE /admin/penilaian/nilai-lisan/{nilai} — hapus nilai ujian lisan yang salah */
    public function hapusNilaiLisan(NilaiUjianLisan $nilai): JsonResponse
    {
        $nilai->delete();

        return response()->json(['message' => __('Nilai ujian lisan dihapus.')]);
    }

    /** PUT /admin/penilaian/nilai-harian/{nilai} — ubah 6 penilaian (batasnya dari NilaiHarian::ASPEK, total dihitung model) */
    public function ubahNilaiHarian(Request $request, NilaiHarian $nilai): JsonResponse
    {
        $aturan = collect(NilaiHarian::ASPEK)->mapWithKeys(fn ($a, $kolom) => [$kolom => ['required', 'integer', 'min:0', 'max:' . $a[1]]])->all();
        $nama = collect(NilaiHarian::ASPEK)->mapWithKeys(fn ($a, $kolom) => [$kolom => __($a[0])])->all();

        $nilai->update($request->validate($aturan, [], $nama));
        $nilai->load(['siswa', 'kelas:id,nama', 'kitab:id,nama', 'bulanAjaran:id,nama', 'penilai:id,name']);

        return response()->json([
            'message' => __('Nilai harian :nama diperbarui.', ['nama' => $nilai->siswa?->nama_lengkap]),
            'baris'   => $nilai->baris(),
        ]);
    }

    /** DELETE /admin/penilaian/nilai-harian/{nilai} — hapus nilai harian yang salah */
    public function hapusNilaiHarian(NilaiHarian $nilai): JsonResponse
    {
        $nilai->delete();

        return response()->json(['message' => __('Nilai harian dihapus.')]);
    }

    /** GET /admin/penilaian/catatan?jenis=pencapaian|pelanggaran&dari=&sampai= */
    public function catatan(Request $request)
    {
        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);
        $jenis = array_key_exists((string) $request->query('jenis'), CatatanSiswa::JENIS) ? $request->query('jenis') : '';

        Absensi::tutupYangLewat(); // Alfa dari absensi kegiatan yang waktunya habis ikut terhitung

        return view('admin.penilaian.catatan', [
            'baris'  => DataCatatan::semuaBaris($f['dari'] ?? null, $f['sampai'] ?? null),
            'jenis'  => $jenis,
            'dari'   => $f['dari'] ?? null,
            'sampai' => $f['sampai'] ?? null,
        ]);
    }

    /** PUT /admin/penilaian/catatan/{catatan} — ubah catatan asatid (jenis, isi, tanggal, jam) */
    public function ubah(Request $request, CatatanSiswa $catatan): JsonResponse
    {
        $data = $request->validate([
            'jenis'   => ['required', 'in:' . implode(',', array_keys(CatatanSiswa::JENIS))],
            'catatan' => ['required', 'string', 'max:5000'],
            'tanggal' => ['required', 'date'],
            'jam'     => ['nullable', 'date_format:H:i'],
        ], [
            'catatan.required' => __('Isi catatan dulu.'),
            'tanggal.required' => __('Isi tanggal dulu.'),
        ]);

        $catatan->update([
            'jenis'   => $data['jenis'],
            'catatan' => trim($data['catatan']),
            'tanggal' => $data['tanggal'],
            'jam'     => $data['jam'] ?? null,
        ]);
        $catatan->load(['siswa.kelasMaster', 'pencatat:id,name']);

        return response()->json(['message' => __('Catatan diperbarui.'), 'baris' => DataCatatan::baris($catatan)]);
    }

    /** DELETE /admin/penilaian/catatan/{catatan} — hapus catatan asatid yang salah */
    public function hapus(CatatanSiswa $catatan): JsonResponse
    {
        $catatan->delete();

        return response()->json(['message' => __('Catatan dihapus.')]);
    }
}
