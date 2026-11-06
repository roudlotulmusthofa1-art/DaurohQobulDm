<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Data Santri = pendaftar yang sudah DITERIMA.
 * Kelas & kamar diambil dari Master Data (kelas_id / kamar_id).
 */
class AdminSantriController extends Controller
{
    /** GET /admin/santri — halaman Data Santri */
    public function index()
    {
        $santri = PendaftaranSiswa::with(['kelasMaster', 'kamarMaster'])
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn (PendaftaranSiswa $s) => $this->baris($s));

        return view('admin.datasantri', [
            'santri'       => $santri,
            // Kelas aktif, untuk kotak statistik & pilihan di form
            'kelasOptions' => Kelas::ordered()->where('is_active', true)->get(['id', 'nama', 'kode']),
            'kamarOptions' => $this->kamarOptions(),
            // Daftar persyaratan (kolom => nama) untuk kotak "Persyaratan belum lengkap" & saringan
            'syaratList'   => collect(PendaftaranSiswa::SYARAT)->map(fn ($nama) => __($nama)),
        ]);
    }

    /**
     * GET /admin/santri/kartu-qr?ids=1,2,3 — halaman cetak kartu QR (dibuka di tab baru).
     * Tanpa ids = semua santri yang sudah diterima. Satu santri: ?ids=5
     * Ukuran kartu, saringan kelas & pencarian diatur Vue di halaman cetak.
     */
    public function kartuQr(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($v) => (int) $v)->filter()->unique();

        $santri = PendaftaranSiswa::with(['kelasMaster', 'kamarMaster'])
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function (PendaftaranSiswa $s) {
                // Santri lama yang belum punya kode QR: dibuatkan sekarang
                if (! $s->kode_qr) {
                    $s->forceFill(['kode_qr' => PendaftaranSiswa::buatKodeQr()])->saveQuietly();
                }

                return [
                    'id'       => $s->id,
                    'nama'     => $s->nama_lengkap,
                    'arab'     => $s->nama_lengkap_arab,
                    'nomor'    => $s->nomor_pendaftaran,
                    'kelas_id' => $s->kelas_id,
                    'kelas'    => $s->kelasMaster?->nama,
                    'kamar'    => $s->kamarMaster?->nama,
                    'kasur'    => $s->nomor_kasur,
                    'foto'     => $s->berkas_foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($s->berkas_foto) : null,
                    'qr'       => $s->qr_svg,
                ];
            })->values();

        return view('admin.santri-kartu-qr', [
            'santri'  => $santri,
            'kelas'   => Kelas::ordered()->get(['id', 'nama']),
            'profil'  => \App\Models\Pengaturan::profilPesantren(),
            'pilihan' => $ids->isNotEmpty(), // dibuka untuk santri tertentu saja
        ]);
    }

    /** POST /admin/santri — tombol "Tambah Cepat": langsung jadi santri (status diterima) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_wali'    => ['nullable', 'string', 'max:255'],
            'no_hp_wali'   => ['nullable', 'string', 'max:20'],
            'kota'         => ['nullable', 'string', 'max:255'],
            'kelas_id'     => ['nullable', 'integer', Rule::exists('kelas', 'id')],
            'kamar_id'     => ['nullable', 'integer', Rule::exists('kamar', 'id')],
        ], [
            'nama_lengkap.required' => 'Nama santri wajib diisi.',
        ]);

        $this->cekKapasitas($data['kamar_id'] ?? null, 1);

        // Nomor pendaftaran dibuat di dalam transaksi agar tidak dobel
        $santri = DB::transaction(fn () => PendaftaranSiswa::create($data + [
            'nomor_pendaftaran' => PendaftaranSiswa::buatNomor(),
            'status'            => PendaftaranSiswa::DITERIMA,
            'diproses_at'       => now(),
        ]));

        return response()->json([
            'message'      => "Santri {$santri->nama_lengkap} berhasil ditambahkan.",
            'santri'       => $this->baris($santri->load(['kelasMaster', 'kamarMaster'])),
            'kamarOptions' => $this->kamarOptions(),
        ], 201);
    }

    /** POST /admin/santri/massal — tombol "Kelola Massal": pindahkan banyak santri sekaligus */
    public function massal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => ['required', 'array', 'min:1'],
            'ids.*'    => ['integer', Rule::exists('pendaftaran_siswas', 'id')],
            'kelas_id' => ['nullable', 'integer', Rule::exists('kelas', 'id')],
            'kamar_id' => ['nullable', 'integer', Rule::exists('kamar', 'id')],
        ], [
            'ids.required' => 'Pilih minimal satu santri.',
        ]);

        // Hanya kolom yang dipilih yang diubah ("Tidak diubah" = tidak dikirim)
        $ubah = array_filter([
            'kelas_id' => $data['kelas_id'] ?? null,
            'kamar_id' => $data['kamar_id'] ?? null,
        ]);

        if (! $ubah) {
            throw ValidationException::withMessages(['kelas_id' => 'Pilih kelas atau kamar tujuan.']);
        }

        if (isset($ubah['kamar_id'])) {
            // Santri yang memang sudah di kamar itu tidak dihitung dua kali
            $sudahDiSana = PendaftaranSiswa::whereIn('id', $data['ids'])->where('kamar_id', $ubah['kamar_id'])->count();
            $this->cekKapasitas($ubah['kamar_id'], count($data['ids']) - $sudahDiSana);
        }

        if (isset($ubah['kamar_id'])) {
            // Santri yang benar-benar pindah kamar: nomor kasur lamanya dikosongkan (denah kamar)
            PendaftaranSiswa::whereIn('id', $data['ids'])
                ->where(fn ($q) => $q->whereNull('kamar_id')->orWhere('kamar_id', '!=', $ubah['kamar_id']))
                ->update(['nomor_kasur' => null, 'jabatan_kamar' => null]); // jabatan Ketua/Wakil kamar lama ikut lepas
        }

        PendaftaranSiswa::whereIn('id', $data['ids'])->update($ubah);

        $santri = PendaftaranSiswa::with(['kelasMaster', 'kamarMaster'])
            ->whereIn('id', $data['ids'])
            ->get()
            ->map(fn (PendaftaranSiswa $s) => $this->baris($s));

        return response()->json([
            'message'      => count($data['ids']) . ' santri berhasil dipindahkan.',
            'santri'       => $santri,
            'kamarOptions' => $this->kamarOptions(),
        ]);
    }

    /**
     * POST /admin/santri/{santri}/jabatan-kamar — body { jabatan: 'ketua' | 'wakil' | null }
     * Satu kamar hanya 1 ketua & 1 wakil: pemegang jabatan yang lama otomatis jadi anggota biasa.
     * Hasil: jabatan semua anggota kamar itu (supaya tampilan langsung sama dengan server).
     */
    public function jabatanKamar(Request $request, PendaftaranSiswa $santri): JsonResponse
    {
        $data = $request->validate([
            'jabatan' => ['nullable', Rule::in(array_keys(PendaftaranSiswa::JABATAN_KAMAR))],
        ]);
        $jabatan = $data['jabatan'] ?? null;

        if (! $santri->kamar_id) {
            throw ValidationException::withMessages(['jabatan' => 'Santri ini belum punya kamar.']);
        }

        DB::transaction(function () use ($santri, $jabatan) {
            if ($jabatan) {
                PendaftaranSiswa::where('kamar_id', $santri->kamar_id)->where('jabatan_kamar', $jabatan)
                    ->whereKeyNot($santri->id)->update(['jabatan_kamar' => null]);
            }
            $santri->forceFill(['jabatan_kamar' => $jabatan])->saveQuietly();
        });

        $label = $jabatan ? strtolower(__(PendaftaranSiswa::JABATAN_KAMAR[$jabatan])) : __('anggota biasa');

        return response()->json([
            'message' => __(':nama sekarang :jabatan.', ['nama' => $santri->nama_lengkap, 'jabatan' => $label]),
            'jabatan' => PendaftaranSiswa::where('kamar_id', $santri->kamar_id)->pluck('jabatan_kamar', 'id'),
        ]);
    }

    /* ------------------------------------------------------------------ */

    /** Satu baris tabel untuk Vue */
    private function baris(PendaftaranSiswa $s): array
    {
        return [
            'id'            => $s->id,
            'nama'          => $s->nama_lengkap,
            'nama_arab'     => $s->nama_lengkap_arab,
            'foto'          => $s->berkas_foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($s->berkas_foto) : null,
            'nomor'         => $s->nomor_pendaftaran,
            'kelas_id'      => $s->kelas_id,
            'kamar_id'      => $s->kamar_id,
            'kelas'         => $s->kelasMaster?->nama,
            'kamar'         => $s->kamarMaster?->nama,
            'nomor_kasur'   => $s->nomor_kasur,
            'jabatan_kamar' => $s->kamar_id ? $s->jabatan_kamar : null, // ketua | wakil | null
            'jabatan_url'   => route('admin.santri.jabatan-kamar', $s),
            'tanggal_masuk' => ($s->diproses_at ?? $s->created_at)?->translatedFormat('d M Y'),
            'wali'          => $s->nama_wali,
            'status'        => $s->user_id ? 'aktif' : 'belum punya akun',
            // Kolom persyaratan yang BELUM dicentang admin, mis. ['syarat_paspor', 'syarat_raport']
            'syarat_kurang' => collect(PendaftaranSiswa::SYARAT)->reject(fn ($nama, $kolom) => $s->$kolom)->keys()->values(),
            'detail_url'    => route('admin.pendaftaran.show', $s),
            'hapus_url'     => route('admin.pendaftaran.destroy', $s), // tombol tempat sampah
        ];
    }

    /** Pilihan kamar aktif, lengkap dengan isi/kapasitas, mis. "Kamar Umar (3/12)" */
    private function kamarOptions()
    {
        return Kamar::ordered()->where('is_active', true)->withCount('santri')->get()
            ->map(fn (Kamar $k) => [
                'id' => $k->id, 'nama' => $k->nama, 'label' => $k->label_isi,
                // untuk kartu kamar & denah di halaman Data Santri
                'kapasitas' => (int) $k->kapasitas, 'kasur_per_baris' => $k->kasur_per_baris ?: 4,
            ]);
    }

    /** Tolak kalau kamar tidak cukup untuk $tambahan santri baru (kapasitas 0 = tanpa batas) */
    private function cekKapasitas(?int $kamarId, int $tambahan): void
    {
        $kamar = $kamarId ? Kamar::withCount('santri')->find($kamarId) : null;

        if (! $kamar || $kamar->kapasitas <= 0 || $tambahan <= 0) {
            return;
        }

        $sisa = $kamar->kapasitas - $kamar->santri_count;

        if ($tambahan > $sisa) {
            throw ValidationException::withMessages([
                'kamar_id' => "Kamar {$kamar->nama} hanya tersisa {$sisa} tempat (kapasitas {$kamar->kapasitas}).",
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
