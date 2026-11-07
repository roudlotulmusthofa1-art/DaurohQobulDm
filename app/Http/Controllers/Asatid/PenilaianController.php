<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\CatatanSiswa;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Support\DataCatatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Dasbor asatid > Penilaian.
 * - Menu: Nilai Harian, Ujian Lisan, Ujian (alur menyusul), Catatan
 * - Catatan: asatid memilih santri, jenis (Pencapaian / Pelanggaran), menulis catatan, lalu tanggal & jam
 *   (otomatis hari ini & jam sekarang, bisa diubah). Langsung masuk ke Admin > Penilaian > Catatan.
 * Asatid hanya melihat & menghapus catatannya sendiri.
 */
class PenilaianController extends Controller
{
    /** Halaman Nilai Harian / Ujian Lisan / Ujian (sementara "segera hadir") */
    public const BAGIAN_KELAS = ['harian', 'lisan', 'ujian'];

    /** GET /asatid/penilaian — menu */
    public function index()
    {
        return $this->tampil('asatid.penilaian.index', [
            'bulan'  => BulanAjaran::aktif(),
            'jumlah' => CatatanSiswa::where('dicatat_oleh', Auth::id())->count(),
        ]);
    }

    /** GET /asatid/penilaian/kelas/{bagian} */
    public function kelas(string $bagian)
    {
        return $this->tampil('asatid.penilaian.kelas', ['bagian' => $bagian]);
    }

    /** GET /asatid/penilaian/catatan */
    public function catatan()
    {
        return $this->tampil('asatid.penilaian.catatan', [
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
