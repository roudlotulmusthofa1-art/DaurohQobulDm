<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Support\JenisPenilaian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Dasbor asatid > Penilaian.
 * - Menu: Nilai Harian, Ujian Lisan, Ujian (alur menyusul), Pelanggaran, Pencapaian
 * - Pelanggaran / Pencapaian: asatid memilih siswa + jenis (Master Data) + tanggal & jam
 *   (otomatis hari ini & jam sekarang, bisa diubah). Laporan langsung masuk ke Admin > Penilaian.
 * Asatid hanya melihat & menghapus laporannya sendiri.
 */
class PenilaianController extends Controller
{
    /** Halaman Nilai Harian / Ujian Lisan / Ujian (sementara "segera hadir") */
    public const BAGIAN_KELAS = ['harian', 'lisan', 'ujian'];

    /** GET /asatid/penilaian — menu */
    public function index()
    {
        $jumlah = collect(JenisPenilaian::TIPE)->mapWithKeys(fn ($tipe) => [
            $tipe => JenisPenilaian::ambil($tipe)['model']::where('dicatat_oleh', Auth::id())->count(),
        ]);

        return $this->tampil('asatid.penilaian.index', [
            'bulan'  => BulanAjaran::aktif(),
            'jumlah' => $jumlah,
        ]);
    }

    /** GET /asatid/penilaian/kelas/{bagian} */
    public function kelas(string $bagian)
    {
        return $this->tampil('asatid.penilaian.kelas', ['bagian' => $bagian]);
    }

    /** GET /asatid/penilaian/pelanggaran  &  /asatid/penilaian/pencapaian */
    public function catatan(string $tipe)
    {
        $atur = JenisPenilaian::ambil($tipe);

        return $this->tampil('asatid.penilaian.catatan', [
            'atur'    => $atur,
            'jenis'   => JenisPenilaian::daftarJenis($tipe),
            'siswa'   => JenisPenilaian::daftarSiswa(),
            'riwayat' => $this->riwayat($tipe),
        ]);
    }

    /** POST /asatid/penilaian/{tipe} — simpan laporan untuk satu atau beberapa siswa sekaligus */
    public function simpan(Request $request, string $tipe): JsonResponse
    {
        $atur = JenisPenilaian::ambil($tipe);
        $model = $atur['model'];
        $kolom = $model::KOLOM_JENIS;
        $tabelJenis = (new ($model::MODEL_JENIS))->getTable();

        $data = $request->validate([
            'siswa'   => ['required', 'array', 'min:1', 'max:200'],
            'siswa.*' => ['integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'jenis'   => ['required', Rule::exists($tabelJenis, 'id')->where('is_active', true)],
            'tanggal' => ['required', 'date'],
            'jam'     => ['nullable', 'date_format:H:i'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'siswa.required' => __('Pilih minimal satu siswa.'),
            'siswa.*.exists' => __('Ada siswa yang tidak ditemukan / belum diterima.'),
            'jenis.required' => __('Pilih jenis :label.', ['label' => strtolower($atur['label'])]),
            'jenis.exists'   => __('Jenis yang dipilih tidak ditemukan / sudah nonaktif.'),
            'tanggal.required' => __('Tanggal wajib diisi.'),
        ]);

        $baru = DB::transaction(fn () => collect(array_unique($data['siswa']))->map(fn ($id) => $model::create([
            'pendaftaran_siswa_id' => $id,
            $kolom                 => $data['jenis'],
            'tanggal'              => $data['tanggal'],
            'jam'                  => $data['jam'] ?? null,
            'catatan'              => trim((string) ($data['catatan'] ?? '')) ?: null,
            'dicatat_oleh'         => Auth::id(),
        ])));

        $baru->each->load(['siswa.kelasMaster', 'jenis', 'pencatat:id,name']);

        return response()->json([
            'message' => __(':jumlah laporan :label tersimpan dan sudah masuk ke admin.', ['jumlah' => $baru->count(), 'label' => strtolower($atur['label'])]),
            'baris'   => $baru->map(fn ($c) => JenisPenilaian::barisGuru($c))->values(),
        ]);
    }

    /** DELETE /asatid/penilaian/{tipe}/{id} — hanya laporan milik sendiri */
    public function hapus(Request $request, string $id): JsonResponse
    {
        $model = JenisPenilaian::ambil($request->route('tipe'))['model'];
        $model::where('dicatat_oleh', Auth::id())->findOrFail($id)->delete();

        return response()->json(['message' => __('Laporan dihapus.')]);
    }

    /** 50 laporan terakhir milik asatid ini */
    private function riwayat(string $tipe)
    {
        $model = JenisPenilaian::ambil($tipe)['model'];

        return $model::with(['siswa.kelasMaster', 'jenis', 'pencatat:id,name'])
            ->where('dicatat_oleh', Auth::id())
            ->orderByDesc('tanggal')->orderByDesc('jam')->orderByDesc('id')
            ->limit(50)->get()
            ->map(fn ($c) => JenisPenilaian::barisGuru($c))->values();
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
