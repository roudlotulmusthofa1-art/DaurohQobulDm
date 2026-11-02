<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidangIlmu;
use App\Models\JenisKegiatan;
use App\Models\JenisKhidmah;
use App\Models\JenisPelanggaran;
use App\Models\Kamar;
use App\Models\KategoriBerita;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\WaktuPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Master Data: Bidang Ilmu, Waktu Pelajaran, Kelas, Kamar, Jenis Kegiatan, Semester,
 * Jenis Pelanggaran, Jenis Khidmah, Kategori Berita.
 *
 * Satu controller untuk 9 model.
 * Definisi tab (kolom tabel, isian form, ikon & warna menu) ada di method tabs() di bawah.
 *
 * Cara menambah menu baru: buat tabel (migration) + model (pakai trait IsMasterData),
 * lalu tambahkan di TYPES, rules(), dan tabs(). Route & tampilan otomatis ikut.
 */
class MasterDataController extends Controller
{
    /** Kunci tab (dipakai di URL) => Model. */
    public const TYPES = [
        'bidang-ilmu' => BidangIlmu::class,
        'waktu-pelajaran' => WaktuPelajaran::class,
        'kelas' => Kelas::class,
        'kamar' => Kamar::class,
        'jenis-kegiatan' => JenisKegiatan::class,
        'semester' => Semester::class,
        'jenis-pelanggaran' => JenisPelanggaran::class,
        'jenis-khidmah' => JenisKhidmah::class,
        'kategori-berita' => KategoriBerita::class,
    ];

    /** Pilihan warna: class Tailwind => hex (swatch pakai hex, jadi tak perlu safelist). */
    private const COLORS = [
        'bg-slate-100' => '#f1f5f9',
        'bg-red-100' => '#fee2e2',
        'bg-orange-100' => '#ffedd5',
        'bg-amber-100' => '#fef3c7',
        'bg-yellow-100' => '#fef9c3',
        'bg-lime-100' => '#ecfccb',
        'bg-green-100' => '#dcfce7',
        'bg-emerald-100' => '#d1fae5',
        'bg-teal-100' => '#ccfbf1',
        'bg-cyan-100' => '#cffafe',
        'bg-sky-100' => '#e0f2fe',
        'bg-blue-100' => '#dbeafe',
        'bg-indigo-100' => '#e0e7ff',
        'bg-violet-100' => '#ede9fe',
        'bg-purple-100' => '#f3e8ff',
        'bg-pink-100' => '#fce7f3',
        'bg-rose-100' => '#ffe4e6',
    ];

    /* =====================================================================
     |  Halaman
     * ===================================================================== */

    /** GET /master-data — markup dirender Blade, interaksi oleh Vue. */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab');
        $tab = array_key_exists($tab, self::TYPES) ? $tab : array_key_first(self::TYPES);

        // Data awal semua tab langsung dikirim, jadi tidak ada loading saat halaman dibuka
        $data = [];
        foreach (self::TYPES as $key => $model) {
            $data[$key] = $this->query($model)->get();
        }

        return view('admin.master-data.index', [
            'tab' => $tab,
            'tabs' => $this->tabs(),
            'colors' => self::COLORS,
            'data' => $data,
        ]);
    }

    /* =====================================================================
     |  API JSON (dipanggil Vue)
     * ===================================================================== */

    /** GET /master-data/api/{type} */
    public function list(string $type): JsonResponse
    {
        return response()->json(['data' => $this->query($this->model($type))->get()]);
    }

    /** POST /master-data/api/{type} */
    public function store(Request $request, string $type): JsonResponse
    {
        $model = $this->model($type);
        $item = $model::create($request->validate($this->rules($type), $this->messages()));

        return response()->json([
            'message' => 'Data berhasil ditambahkan.',
            'data' => $item->fresh(),
        ], 201);
    }

    /** PUT /master-data/api/{type}/{id} */
    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $item = $this->model($type)::findOrFail($id);
        $item->update($request->validate($this->rules($type, $item->id), $this->messages()));

        return response()->json([
            'message' => 'Data berhasil diperbarui.',
            'data' => $item->fresh(),
        ]);
    }

    /** PATCH /master-data/api/{type}/{id}/toggle — aktif / nonaktif */
    public function toggle(string $type, int $id): JsonResponse
    {
        $item = $this->model($type)::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        return response()->json([
            'message' => $item->is_active ? 'Data diaktifkan.' : 'Data dinonaktifkan.',
            'data' => $item,
        ]);
    }

    /** DELETE /master-data/api/{type}/{id} */
    public function destroy(string $type, int $id): JsonResponse
    {
        $model = $this->model($type);
        $item = $model::findOrFail($id);

        // Jangan hapus jika masih dipakai (mis. bidang ilmu yang masih punya kitab)
        $relation = $model::countRelation();
        if ($relation && $item->{$relation}()->exists()) {
            return response()->json([
                'message' => 'Data masih digunakan, tidak bisa dihapus. Nonaktifkan saja.',
            ], 422);
        }

        $item->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    /** POST /master-data/api/{type}/reorder — body: { ids: [3, 1, 2, ...] } */
    public function reorder(Request $request, string $type): JsonResponse
    {
        $model = $this->model($type);
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        DB::transaction(function () use ($model, $ids) {
            foreach (array_values($ids) as $index => $id) {
                $model::whereKey($id)->update(['urutan' => $index + 1]);
            }
        });

        return response()->json(['message' => 'Urutan berhasil disimpan.']);
    }

    /* =====================================================================
     |  Validasi
     * ===================================================================== */

    private function rules(string $type, ?int $id = null): array
    {
        $table = (new (self::TYPES[$type]))->getTable();
        $active = ['is_active' => ['sometimes', 'boolean']];

        return $active + match ($type) {
            'bidang-ilmu', 'kategori-berita' => [
                'nama' => ['required', 'string', 'max:100'],
                'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique($table, 'slug')->ignore($id)],
                'warna' => ['nullable', 'string', Rule::in(array_keys(self::COLORS))],
                'deskripsi' => ['nullable', 'string', 'max:1000'],
            ],
            'waktu-pelajaran' => [
                'nama' => ['required', 'string', 'max:100'],
                'jam_mulai' => ['nullable', 'date_format:H:i'],
                'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
                'keterangan' => ['nullable', 'string', 'max:255'],
            ],
            'kelas' => [
                'nama' => ['required', 'string', 'max:100'],
                'kode' => ['nullable', 'string', 'max:30', Rule::unique($table, 'kode')->ignore($id)],
               
                'deskripsi' => ['nullable', 'string', 'max:1000'],
            ],
            'kamar' => [
                'nama' => ['required', 'string', 'max:100'],
                'kode' => ['nullable', 'string', 'max:30', Rule::unique($table, 'kode')->ignore($id)],
                'kapasitas' => ['required', 'integer', 'min:0', 'max:1000'],
                'kasur_per_baris' => ['nullable', 'integer', 'min:1', 'max:12'],
                'deskripsi' => ['nullable', 'string', 'max:1000'],
            ],
            'jenis-kegiatan', 'jenis-pelanggaran', 'jenis-khidmah' => [
                'nama' => ['required', 'string', 'max:100'],
                'warna' => ['nullable', 'string', Rule::in(array_keys(self::COLORS))],
                'deskripsi' => ['nullable', 'string', 'max:1000'],
            ],
            'semester' => [
                'nama' => ['required', 'string', 'max:100'],
                // Masehi
                'tahun_ajaran' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
                'tanggal_mulai' => ['nullable', 'date'],
                'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
                // Hijriyah
                'tahun_ajaran_hijriyah' => ['nullable', 'string', 'max:30'],
                'tanggal_mulai_hijriyah' => ['nullable', 'string', 'max:50'],
                'tanggal_selesai_hijriyah' => ['nullable', 'string', 'max:50'],
                'keterangan' => ['nullable', 'string', 'max:255'],
            ],
        };
    }

    /** Pesan error khusus (selain pesan bawaan Laravel). */
    private function messages(): array
    {
        return [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }

    /* =====================================================================
     |  Definisi tab untuk view
     |  Tipe kolom : name, color, text, count, badge, time-range, suffix,
     |               tahun-ganda, periode-ganda (Masehi + Hijriyah, untuk Semester)
     |  Tipe isian : text, number, time, date, textarea, select, color,
     |               section (judul kecil pemisah di dalam form)
     |  Isian date bisa diberi 'hijri' => 'nama_kolom' : saat tanggal Masehi dipilih,
     |  kolom itu otomatis diisi tanggal Hijriyah-nya.
     |  Kolom Urutan, Status, dan Aksi ditambahkan otomatis di view.
     |
     |  URUTAN di array ini = urutan menu (tab) di halaman.
     |  icon  : nama ikon di resources/views/components/master-data/icon.blade.php
     |  accent: warna khas menu (kode hex), dipakai untuk ikon & garis bawah menu
     * ===================================================================== */

    private function tabs(): array
    {
        return [
            'bidang-ilmu' => [
                'icon' => 'book',
                'accent' => '#3b82f6',
                'label' => 'Bidang Ilmu',
                'title' => 'Kategori Bidang Ilmu',
                'description' => 'Kelola kategori bidang ilmu (fann) untuk kurikulum kitab. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Kategori',
                'singular' => 'Bidang Ilmu',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name', 'sub' => 'slug'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                    ['key' => 'items_count', 'label' => 'Kitab', 'type' => 'count'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Nahwu'],
                    ['key' => 'slug', 'label' => 'Slug', 'type' => 'text', 'placeholder' => 'otomatis dari nama', 'hint' => 'Kosongkan untuk dibuat otomatis.'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color', 'default' => 'bg-blue-100'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            'waktu-pelajaran' => [
                'icon' => 'clock',
                'accent' => '#f59e0b',
                'label' => 'Waktu Pelajaran',
                'title' => 'Waktu Pelajaran',
                'description' => 'Kelola sesi/waktu pelajaran harian. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Waktu',
                'singular' => 'Waktu Pelajaran',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name'],
                    ['key' => 'jam', 'label' => 'Jam', 'type' => 'time-range'],
                    ['key' => 'keterangan', 'label' => 'Keterangan', 'type' => 'text'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Sesi', 'type' => 'text', 'required' => true, 'placeholder' => "cth. Ba'da Subuh"],
                    ['key' => 'jam_mulai', 'label' => 'Jam Mulai', 'type' => 'time', 'half' => true],
                    ['key' => 'jam_selesai', 'label' => 'Jam Selesai', 'type' => 'time', 'half' => true],
                    ['key' => 'keterangan', 'label' => 'Keterangan', 'type' => 'text'],
                ],
            ],

            'kelas' => [
                'icon' => 'graduation',
                'accent' => '#22c55e',
                'label' => 'Kelas',
                'title' => 'Daftar Kelas',
                'description' => 'Kelola kelas/marhalah santri. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Kelas',
                'singular' => 'Kelas',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name', 'sub' => 'kode'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                    ['key' => 'items_count', 'label' => 'Santri', 'type' => 'count'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Kelas', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Ula 1'],
                    ['key' => 'kode', 'label' => 'Kode', 'type' => 'text', 'placeholder' => 'cth. U1'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            'kamar' => [
                'icon' => 'bed',
                'accent' => '#a855f7',
                'label' => 'Kamar',
                'title' => 'Daftar Kamar',
                'description' => 'Kelola kamar asrama santri beserta kapasitasnya. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Kamar',
                'singular' => 'Kamar',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name', 'sub' => 'kode'],
                    ['key' => 'kapasitas', 'label' => 'Kapasitas', 'type' => 'suffix', 'suffix' => 'santri'],
                    ['key' => 'items_count', 'label' => 'Terisi', 'type' => 'count'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Kamar', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Kamar Abu Bakar'],
                    ['key' => 'kode', 'label' => 'Kode', 'type' => 'text', 'half' => true, 'placeholder' => 'cth. A-01'],
                    ['key' => 'kapasitas', 'label' => 'Kapasitas (jumlah kasur)', 'type' => 'number', 'half' => true, 'required' => true, 'default' => 0, 'min' => 0],
                    ['key' => 'kasur_per_baris', 'label' => 'Kasur per baris (denah)', 'type' => 'number', 'half' => true, 'default' => 4, 'min' => 1,
                        'hint' => 'Atur supaya gambar denah mirip susunan kamar aslinya.'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            // ===== BARU =====
            'jenis-kegiatan' => [
                'icon' => 'activity',
                'accent' => '#14b8a6',
                'label' => 'Jenis Kegiatan',
                'title' => 'Jenis Kegiatan',
                'description' => 'Kelola jenis/nama kegiatan pondok (mis. Muhadharah, Ro\'an, Ziarah). Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Kegiatan',
                'singular' => 'Jenis Kegiatan',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama Kegiatan', 'type' => 'name'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Kegiatan', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Muhadharah'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color', 'default' => 'bg-teal-100'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            'semester' => [
                'icon' => 'calendar',
                'accent' => '#f43f5e',
                'label' => 'Semester',
                'title' => 'Semester',
                'description' => 'Kelola semester per tahun ajaran, dengan kalender Masehi dan Hijriyah. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Semester',
                'singular' => 'Semester',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name'],
                    ['key' => 'tahun', 'label' => 'Tahun Ajaran', 'type' => 'tahun-ganda'],
                    ['key' => 'periode', 'label' => 'Periode', 'type' => 'periode-ganda'],
                    ['key' => 'keterangan', 'label' => 'Keterangan', 'type' => 'text'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Semester', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Semester Ganjil'],

                    ['key' => 'judul_masehi', 'label' => 'Kalender Masehi', 'type' => 'section', 'color' => 'sky'],
                    ['key' => 'tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'text', 'placeholder' => 'cth. 2026/2027'],
                    // 'hijri' => saat tanggal ini dipilih, isian Hijriyah-nya terisi otomatis
                    ['key' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date', 'half' => true, 'hijri' => 'tanggal_mulai_hijriyah'],
                    ['key' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'type' => 'date', 'half' => true, 'hijri' => 'tanggal_selesai_hijriyah'],

                    ['key' => 'judul_hijriyah', 'label' => 'Kalender Hijriyah', 'type' => 'section', 'color' => 'emerald',
                        'hint' => 'Terisi otomatis dari tanggal Masehi. Boleh diubah bila berbeda menurut rukyat.'],
                    ['key' => 'tahun_ajaran_hijriyah', 'label' => 'Tahun Ajaran Hijriyah', 'type' => 'text', 'placeholder' => 'cth. 1447/1448 H'],
                    ['key' => 'tanggal_mulai_hijriyah', 'label' => 'Tanggal Mulai', 'type' => 'text', 'half' => true, 'placeholder' => 'cth. 1 Muharam 1448 H'],
                    ['key' => 'tanggal_selesai_hijriyah', 'label' => 'Tanggal Selesai', 'type' => 'text', 'half' => true, 'placeholder' => 'cth. 29 Jumadil Akhir 1448 H'],

                    ['key' => 'keterangan', 'label' => 'Keterangan', 'type' => 'text'],
                ],
            ],

            'jenis-pelanggaran' => [
                'icon' => 'shield-alert',
                'accent' => '#ef4444',
                'label' => 'Jenis Pelanggaran',
                'title' => 'Jenis Pelanggaran',
                'description' => 'Kelola daftar jenis pelanggaran santri. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Pelanggaran',
                'singular' => 'Jenis Pelanggaran',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama Pelanggaran', 'type' => 'name'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Pelanggaran', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Terlambat sholat berjamaah'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color', 'default' => 'bg-red-100'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            'jenis-khidmah' => [
                'icon' => 'hand-heart',
                'accent' => '#0ea5e9',
                'label' => 'Jenis Khidmah',
                'title' => 'Jenis Khidmah',
                'description' => 'Kelola jenis tugas khidmah/pengabdian santri (mis. Kebersihan, Dapur, Keamanan). Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Khidmah',
                'singular' => 'Jenis Khidmah',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama Khidmah', 'type' => 'name'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama Khidmah', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Kebersihan Masjid'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color', 'default' => 'bg-sky-100'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],

            // Kategori Berita sengaja diletakkan paling belakang
            'kategori-berita' => [
                'icon' => 'newspaper',
                'accent' => '#ec4899',
                'label' => 'Kategori Berita',
                'title' => 'Kategori Berita',
                'description' => 'Kelola kategori untuk berita di landing page. Seret baris untuk mengubah urutan.',
                'add_label' => 'Tambah Kategori',
                'singular' => 'Kategori Berita',
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'name', 'sub' => 'slug'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'text'],
                    ['key' => 'items_count', 'label' => 'Berita', 'type' => 'count'],
                ],
                'fields' => [
                    ['key' => 'nama', 'label' => 'Nama', 'type' => 'text', 'required' => true, 'placeholder' => 'cth. Kegiatan'],
                    ['key' => 'slug', 'label' => 'Slug', 'type' => 'text', 'placeholder' => 'otomatis dari nama', 'hint' => 'Kosongkan untuk dibuat otomatis.'],
                    ['key' => 'warna', 'label' => 'Warna', 'type' => 'color', 'default' => 'bg-green-100'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea'],
                ],
            ],
        ];
    }

    /* =====================================================================
     |  Helper
     * ===================================================================== */

    /** @return class-string<Model> — 404 jika tab tidak dikenal. */
    private function model(string $type): string
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return self::TYPES[$type];
    }

    /** Query terurut + jumlah kitab/berita (sebagai items_count) bila ada. */
    private function query(string $model): Builder
    {
        $query = $model::query()->ordered();

        if ($relation = $model::countRelation()) {
            $query->withCount("{$relation} as items_count");
        }

        return $query;
    }
}
