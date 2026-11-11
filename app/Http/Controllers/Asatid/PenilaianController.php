<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\CatatanSiswa;
use App\Models\Kelas;
use App\Models\NilaiHarian;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Support\DaftarKitab;
use App\Support\DataCatatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Dasbor asatid > Penilaian.
 * - Menu: Nilai Harian, Ujian Lisan, Ujian (alur menyusul), Catatan
 * - Nilai Harian: pilih kelas -> kitab -> santri -> isi 6 penilaian (batasnya di NilaiHarian::ASPEK).
 *   Satu santri + kitab + BULAN AJARAN AKTIF = satu nilai, diteruskan setiap hari (bukan nilai baru tiap hari).
 *   Masuk ke Admin > Penilaian > Kelas & Pelajaran.
 * - Catatan: asatid memilih santri, jenis (Pencapaian / Pelanggaran), menulis catatan, lalu tanggal & jam
 *   (otomatis hari ini & jam sekarang, bisa diubah). Langsung masuk ke Admin > Penilaian > Catatan.
 * Asatid hanya melihat & menghapus catatannya sendiri.
 */
class PenilaianController extends Controller
{
    /** Halaman Ujian Lisan / Ujian (sementara "segera hadir"). Nilai Harian punya halaman sendiri. */
    public const BAGIAN_KELAS = ['lisan', 'ujian'];

    /** GET /asatid/penilaian — menu */
    public function index()
    {
        return $this->tampil('asatid.penilaian.index', [
            'bulan'  => BulanAjaran::aktif(),
            'jumlah' => CatatanSiswa::where('dicatat_oleh', Auth::id())->count(),
            'jumlahNilai' => NilaiHarian::where('dinilai_oleh', Auth::id())->count(),
        ]);
    }

    /** GET /asatid/penilaian/kelas/{bagian} */
    public function kelas(string $bagian)
    {
        return $this->tampil('asatid.penilaian.kelas', ['bagian' => $bagian]);
    }

    /* =====================================================================
     |  Nilai Harian
     * ===================================================================== */

    /**
     * GET /asatid/penilaian/harian
     * Dari tombol "Nilai Harian" di absensi kelas: ?kelas_id=&siswa_id=&kitab_id=&tanggal= (form langsung terisi)
     */
    public function harian(Request $request)
    {
        return $this->tampil('asatid.penilaian.harian', [
            'kelasList' => Kelas::where('is_active', true)->ordered()
                ->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get(['id', 'nama', 'kode']),
            'kitabList' => DaftarKitab::pilihan(),
            'terjadwal' => DaftarKitab::terjadwal(), // kitab yang terjadwal di tiap kelas (ditaruh paling atas)
            'aspek'     => NilaiHarian::daftarAspek(),
            'bulan'     => BulanAjaran::aktif(),
            'awal'      => $request->only(['kelas_id', 'siswa_id', 'kitab_id', 'bidang_ilmu_id', 'tanggal']),
        ]);
    }

    /** GET /asatid/penilaian/harian/peserta — JSON: santri di kelas itu + nilainya pada kitab itu di bulan ajaran aktif (kalau sudah ada) */
    public function pesertaHarian(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'kitab_id' => ['required', 'integer', 'exists:kitab,id'],
        ]);
        $bulan = $this->bulanAktif();

        $nilai = NilaiHarian::with('penilai:id,name')
            ->where('kitab_id', $data['kitab_id'])->where('bulan_ajaran_id', $bulan->id)
            ->get()->keyBy('pendaftaran_siswa_id');

        $peserta = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->where('kelas_id', $data['kelas_id'])
            ->orderBy('nama_lengkap')->get()
            ->map(function ($s) use ($nilai) {
                $n = $nilai[$s->id] ?? null;

                return [
                    'id'    => $s->id,
                    'nama'  => $s->nama_lengkap,
                    'arab'  => $s->nama_lengkap_arab,
                    'nomor' => $s->nomor_pendaftaran,
                    'foto'  => $s->foto_url,
                    'nilai' => $n ? collect(NilaiHarian::ASPEK)->mapWithKeys(fn ($a, $k) => [$k => (int) $n->{$k}])->all() : null,
                    'total' => $n?->total,
                    'oleh'  => $n?->penilai?->name,
                    'terakhir' => $n?->tanggal?->toDateString(),
                ];
            });

        return response()->json(['peserta' => $peserta]);
    }

    /** POST /asatid/penilaian/harian — simpan / teruskan nilai satu santri di bulan ajaran aktif (nilai lebih dari batas ditolak) */
    public function simpanHarian(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'kitab_id' => ['required', 'integer', 'exists:kitab,id'],
            'siswa_id' => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
        ] + NilaiHarian::aturan(), [
            'siswa_id.exists' => __('Santri tidak ditemukan / belum diterima.'),
            '*.max'           => __('Nilai ":attribute" tidak boleh lebih dari :max.'),
        ], collect(NilaiHarian::ASPEK)->mapWithKeys(fn ($a, $k) => [$k => __($a[0])])->all());

        $bulan = $this->bulanAktif();

        $nilai = NilaiHarian::updateOrCreate(
            ['pendaftaran_siswa_id' => $data['siswa_id'], 'kitab_id' => $data['kitab_id'], 'bulan_ajaran_id' => $bulan->id],
            collect($data)->only(array_keys(NilaiHarian::ASPEK))->all() + [
                'kelas_id'     => $data['kelas_id'],
                'tanggal'      => now('Asia/Jakarta')->toDateString(), // terakhir dinilai
                'dinilai_oleh' => Auth::id(),
            ],
        );

        return response()->json([
            'message' => __('Nilai harian :nama tersimpan: :total dari 100.', [
                'nama' => $nilai->siswa?->nama_lengkap, 'total' => $nilai->total,
            ]),
            'total' => $nilai->total,
            'oleh'  => Auth::user()->name,
            'terakhir' => $nilai->tanggal->toDateString(),
        ]);
    }

    /* =====================================================================
     |  Catatan
     * ===================================================================== */

    /** GET /asatid/penilaian/catatan  (?siswa=ID dari tombol "Catatan" di absensi kelas: santri itu langsung terpilih) */
    public function catatan(Request $request)
    {
        return $this->tampil('asatid.penilaian.catatan', [
            'awalSiswa' => (int) $request->query('siswa') ?: null,
            'siswa'   => DataCatatan::daftarSiswa(),
            'riwayat' => CatatanSiswa::with(['siswa.kelasMaster', 'pencatat:id,name'])
                ->where('dicatat_oleh', Auth::id())
                ->orderByDesc('tanggal')->orderByDesc('jam')->orderByDesc('id')
                ->limit(50)->get()
                ->map(fn ($c) => DataCatatan::baris($c))->values(),
        ]);
    }

    /** POST /asatid/penilaian/catatan — satu catatan untuk satu atau beberapa santri sekaligus */
    public function simpan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'siswa'   => ['required', 'array', 'min:1', 'max:200'],
            'siswa.*' => ['integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'jenis'   => ['required', Rule::in(array_keys(CatatanSiswa::JENIS))],
            'catatan' => ['required', 'string', 'max:2000'],
            'tanggal' => ['required', 'date'],
            'jam'     => ['nullable', 'date_format:H:i'],
        ], [
            'siswa.required'   => __('Pilih minimal satu santri.'),
            'siswa.*.exists'   => __('Ada santri yang tidak ditemukan / belum diterima.'),
            'jenis.required'   => __('Pilih jenisnya: Pencapaian atau Pelanggaran.'),
            'catatan.required' => __('Catatan wajib diisi.'),
            'tanggal.required' => __('Tanggal wajib diisi.'),
        ]);

        $baru = DB::transaction(fn () => collect(array_unique($data['siswa']))->map(fn ($id) => CatatanSiswa::create([
            'pendaftaran_siswa_id' => $id,
            'jenis'                => $data['jenis'],
            'catatan'              => trim($data['catatan']),
            'tanggal'              => $data['tanggal'],
            'jam'                  => $data['jam'] ?? null,
            'dicatat_oleh'         => Auth::id(),
        ])));

        $baru->each->load(['siswa.kelasMaster', 'pencatat:id,name']);

        return response()->json([
            'message' => __(':jumlah catatan :jenis tersimpan dan sudah masuk ke admin.', [
                'jumlah' => $baru->count(), 'jenis' => strtolower(__(CatatanSiswa::JENIS[$data['jenis']])),
            ]),
            'baris' => $baru->map(fn ($c) => DataCatatan::baris($c))->values(),
        ]);
    }

    /** DELETE /asatid/penilaian/catatan/{id} — hanya catatan milik sendiri */
    public function hapus(string $id): JsonResponse
    {
        CatatanSiswa::where('dicatat_oleh', Auth::id())->findOrFail($id)->delete();

        return response()->json(['message' => __('Catatan dihapus.')]);
    }

    /** Bulan ajaran yang sedang aktif; kalau admin belum mengaktifkan, berhenti dengan pesan yang jelas */
    private function bulanAktif(): BulanAjaran
    {
        return BulanAjaran::aktif() ?? abort(response()->json([
            'message' => __('Belum ada bulan ajaran aktif. Minta admin mengaktifkannya di Tahun & Bulan Ajaran.'),
        ], 422));
    }

    /** Sama seperti halaman asatid lain: profil singkat + notifikasi */
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
