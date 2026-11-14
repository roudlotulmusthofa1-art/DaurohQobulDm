<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\NilaiUjian;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\SoalUjian;
use App\Models\Ujian;
use App\Support\NamaArab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Akun Asatid > Ujian (tulis). Dua halaman:
 * - Soal Ujian (menu sendiri di beranda, 2 tab): "Buat Soal" + "Soal Disetujui" (soal semua asatid yang disetujui admin).
 *   Buat Soal  : pilih halaqoh (boleh beberapa) -> ujian & kelompok kitab (sesuai cara pembuatan soal dari admin:
 *                per kitab / seluruh fan / kelompok, lihat Ujian::kelompok) -> tulis pertanyaan, jawaban,
 *                nilai tiap pertanyaan -> kirim ke admin. Di bawahnya soal milik asatid ini: yang masih menunggu boleh
 *                diubah / dihapus; yang sudah disetujui tampil versi koreksi admin.
 * - Nilai Ujian: pilih ujian & kelompok kitab (sama dengan kelompok soal dari admin) -> halaqoh -> tanggal
 *                -> tulis nilai tiap santri (satu angka) -> simpan.
 *                Satu santri + ujian + kelompok = satu nilai (diisi lagi = diperbarui). Langsung masuk ke admin.
 */
class UjianController extends Controller
{
    /* =====================================================================
     |  Buat Soal
     * ===================================================================== */

    /** GET /asatid/soal-ujian?tab=buat|disetujui — menu Soal Ujian di beranda asatid */
    public function soal(Request $request)
    {
        // Tab "Soal Disetujui": soal semua asatid yang sudah disetujui admin (hanya dibaca)
        $disetujui = SoalUjian::with(['pembuat:id,name', 'pemeriksa:id,name'])->where('status', SoalUjian::DISETUJUI)
            ->latest('diperiksa_at')->latest('id')->get();
        NamaArab::untukUser($disetujui->pluck('dibuat_oleh'));

        return $this->tampil('asatid.soal-ujian', [
            'tab'           => $request->query('tab') === 'disetujui' ? 'disetujui' : 'buat',
            'soalDisetujui' => $disetujui->map(fn (SoalUjian $s) => $s->baris())->values(),
            'ujian'     => Ujian::daftar(true),
            'kelasList' => $this->daftarKelas(),
            'soalSaya'  => SoalUjian::with(['pembuat:id,name', 'pemeriksa:id,name'])->where('dibuat_oleh', Auth::id())
                ->latest('id')->get()->map(fn (SoalUjian $s) => $s->baris())->values(),
        ]);
    }

    /** POST /asatid/penilaian/ujian/soal — kirim soal baru, atau ubah soal sendiri yang masih menunggu (isi "id") */
    public function simpanSoal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id'          => ['nullable', 'integer'],
            'ujian_id'    => ['required', 'integer', Rule::exists('ujian', 'id')->where('is_active', true)],
            'kitab_ids'   => ['required', 'array', 'min:1'],
            'kitab_ids.*' => ['integer'],
            'kelas_ids'   => ['required', 'array', 'min:1'],
            'kelas_ids.*' => ['integer', 'exists:kelas,id'],
        ], [
            'ujian_id.required'  => __('Pilih ujian dulu.'),
            'ujian_id.exists'    => __('Ujian ini tidak ditemukan atau sudah dimatikan admin.'),
            'kitab_ids.required' => __('Pilih kitab dulu.'),
            'kitab_ids.min'      => __('Pilih kitab dulu.'),
            'kelas_ids.required' => __('Pilih minimal satu halaqoh.'),
            'kelas_ids.min'      => __('Pilih minimal satu halaqoh.'),
        ]);
        $butir = SoalUjian::butirDari($request);

        $soal = ! empty($data['id'])
            ? SoalUjian::where('dibuat_oleh', Auth::id())->findOrFail($data['id'])
            : new SoalUjian(['dibuat_oleh' => Auth::id(), 'status' => SoalUjian::MENUNGGU]);
        if ($soal->exists && $soal->status !== SoalUjian::MENUNGGU) {
            throw ValidationException::withMessages(['id' => __('Soal yang sudah disetujui admin tidak bisa diubah lagi.')]);
        }

        // Kitab yang dipilih harus sama persis dengan salah satu kelompok dari pengaturan admin
        $ujian = Ujian::with(['bidangIlmu:id,nama', 'kitab:kitab.id,kitab.nama'])->findOrFail($data['ujian_id']);
        $kelompok = $ujian->cariKelompok($data['kitab_ids']);
        if (! $kelompok) {
            throw ValidationException::withMessages(['kitab_ids' => __('Pilihan kitab tidak sesuai pengaturan ujian dari admin.')]);
        }
        $soal->fill([
            'ujian_id'   => $ujian->id,
            'kitab_ids'  => $kelompok['kitab_ids'],
            'nama_ujian' => $ujian->nama,
            'fan'        => $ujian->bidangIlmu?->nama,
            'kitab'      => $kelompok['nama'],
            'kelas_ids'  => array_values(array_unique(array_map('intval', $data['kelas_ids']))),
            'butir'      => $butir,
            'butir_asli' => null, // kiriman baru dari asatid: belum ada perubahan admin
        ])->save();
        $soal->load(['pembuat:id,name', 'pemeriksa:id,name']);

        return response()->json([
            'message' => ! empty($data['id']) ? __('Soal diperbarui dan dikirim ulang ke admin.') : __('Soal terkirim ke admin. Tunggu pemeriksaan.'),
            'baris'   => $soal->baris(),
        ]);
    }

    /** DELETE /asatid/penilaian/ujian/soal/{id} — hanya soal sendiri yang masih menunggu */
    public function hapusSoal(int $id): JsonResponse
    {
        $soal = SoalUjian::where('dibuat_oleh', Auth::id())->findOrFail($id);
        if ($soal->status !== SoalUjian::MENUNGGU) {
            throw ValidationException::withMessages(['id' => __('Soal yang sudah disetujui admin tidak bisa dihapus.')]);
        }
        $soal->delete();

        return response()->json(['message' => __('Soal dihapus.')]);
    }

    /* =====================================================================
     |  Nilai Ujian
     * ===================================================================== */

    /** GET /asatid/penilaian/ujian/nilai */
    public function nilai()
    {
        return $this->tampil('asatid.penilaian.ujian-nilai', [
            'ujian'     => Ujian::daftar(true),
            'kelasList' => $this->daftarKelas(),
            'hariIni'   => now('Asia/Jakarta')->toDateString(),
            'jumlahSaya' => NilaiUjian::where('dinilai_oleh', Auth::id())->count(),
        ]);
    }

    /** GET /asatid/penilaian/ujian/nilai/peserta?ujian_id=&kitab_ids[]=&kelas_id= — santri halaqoh itu + nilainya pada kelompok kitab itu */
    public function peserta(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ujian_id' => ['required', 'integer', 'exists:ujian,id'],
            'kitab_ids'   => ['required', 'array', 'min:1'],
            'kitab_ids.*' => ['integer'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);

        $nilai = NilaiUjian::with('penilai:id,name')->where('ujian_id', $data['ujian_id'])->where('kelompok', Ujian::kunci($data['kitab_ids']))
            ->get()->keyBy('pendaftaran_siswa_id');

        return response()->json([
            'peserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->where('kelas_id', $data['kelas_id'])
                ->orderBy('nama_lengkap')->get()
                ->map(function (PendaftaranSiswa $s) use ($nilai) {
                    $n = $nilai[$s->id] ?? null;

                    return [
                        'id'    => $s->id,
                        'nama'  => $s->nama_lengkap,
                        'arab'  => $s->nama_lengkap_arab,
                        'nomor' => $s->nomor_pendaftaran,
                        'foto'  => $s->foto_url,
                        'nilai' => $n ? (int) $n->nilai : null,
                        'oleh'  => $n?->penilai?->name,
                        'tanggal' => $n?->tanggal?->toDateString(),
                    ];
                })->values(),
        ]);
    }

    /** POST /asatid/penilaian/ujian/nilai — simpan nilai beberapa santri sekaligus (lihat NilaiUjian::simpanDari) */
    public function simpanNilai(Request $request): JsonResponse
    {
        $tersimpan = NilaiUjian::simpanDari($request);

        return response()->json([
            'message' => __(':n nilai ujian tersimpan.', ['n' => count($tersimpan)]),
            'nilai'   => collect($tersimpan)->mapWithKeys(fn (NilaiUjian $n) => [$n->pendaftaran_siswa_id => (int) $n->nilai]),
        ]);
    }

    /* =====================================================================
     |  Bantuan
     * ===================================================================== */

    /** Halaqoh (kelas) aktif + jumlah santrinya */
    private function daftarKelas()
    {
        return Kelas::where('is_active', true)->ordered()
            ->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])
            ->get(['id', 'nama', 'kode']);
    }

    private function tampil(string $view, array $data = [])
    {
        $user = Auth::user();
        $asatid = PendaftaranAsatid::where('user_id', $user->id)->first();

        return view($view, $data + [
            'user'   => $user,
            'asatid' => $asatid,
            'notif'  => AsatidDashboardController::notifikasi($asatid),
        ]);
    }
}
