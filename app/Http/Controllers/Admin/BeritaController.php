<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\BeritaKomentar;
use App\Support\TulisBerita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin > Landing Page > Berita
 * - Kotak Masuk: tulisan asatid & siswa yang menunggu diperiksa -> Tampilkan (+ batas waktu) / Tolak
 * - Sedang Tampil / Selesai tayang (bisa Tampilkan lagi) / Ditolak / Semua
 * - Tulis Berita: tulisan admin langsung tampil
 * - Komentar: lihat & hapus
 * Semua aksi membalas JSON berisi daftar terbaru.
 */
class BeritaController extends Controller
{
    public function index(): View
    {
        return view('admin.berita.index', [
            'daftar' => $this->daftar(),
            'kategori' => TulisBerita::kategori(),
            'maksFoto' => TulisBerita::MAKS_FOTO,
        ]);
    }

    /** Admin menulis berita: langsung tampil */
    public function store(Request $request): JsonResponse
    {
        $data = TulisBerita::validasi($request, true);
        $user = Auth::user();

        TulisBerita::simpan($request, $data, null, TulisBerita::penulis($user) + [
            'status' => Berita::TAMPIL,
            'tampil_mulai' => now(),
            'tampil_sampai' => TulisBerita::batas($data['tampil_sampai'] ?? null),
            'diperiksa_oleh' => $user->name,
            'diperiksa_at' => now(),
        ]);

        return $this->balas('Berita diterbitkan dan langsung tampil di landing page.');
    }

    /** Admin mengubah berita siapa pun (penulis tetap) */
    public function update(Request $request, Berita $berita): JsonResponse
    {
        $data = TulisBerita::validasi($request, true);
        $tambahan = [];
        if ($berita->status === Berita::TAMPIL) {
            $tambahan['tampil_sampai'] = TulisBerita::batas($data['tampil_sampai'] ?? null);
        }
        TulisBerita::simpan($request, $data, $berita, $tambahan);

        return $this->balas('Berita diperbarui.');
    }

    /** Tampilkan / Tampilkan lagi, dengan batas waktu (kosong = terus) */
    public function tampilkan(Request $request, Berita $berita): JsonResponse
    {
        $data = $request->validate(['tampil_sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:' . now(Berita::ZONA)->format('Y-m-d')]], [
            'tampil_sampai.after_or_equal' => 'Batas waktu tampil tidak boleh sebelum hari ini.',
        ]);

        $berita->update([
            'status' => Berita::TAMPIL,
            'tampil_mulai' => now(),
            'tampil_sampai' => TulisBerita::batas($data['tampil_sampai'] ?? null),
            'catatan_admin' => null,
            'diperiksa_oleh' => Auth::user()->name,
            'diperiksa_at' => now(),
        ]);

        return $this->balas('Berita ditampilkan di landing page.');
    }

    /** Turunkan dari landing page sekarang (jadi "Selesai tayang", bisa ditampilkan lagi) */
    public function turunkan(Berita $berita): JsonResponse
    {
        abort_unless($berita->status === Berita::TAMPIL, 422, 'Berita ini tidak sedang tampil.');
        $berita->update(['tampil_sampai' => now()->subSecond()]);

        return $this->balas('Berita diturunkan dari landing page.');
    }

    public function tolak(Request $request, Berita $berita): JsonResponse
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $berita->update([
            'status' => Berita::DITOLAK,
            'tampil_mulai' => null,
            'tampil_sampai' => null,
            'catatan_admin' => $data['catatan'] ?? null,
            'diperiksa_oleh' => Auth::user()->name,
            'diperiksa_at' => now(),
        ]);

        return $this->balas('Berita ditolak. Penulis dapat melihat catatan Anda.');
    }

    public function destroy(Berita $berita): JsonResponse
    {
        $berita->hapusSemua();

        return $this->balas('Berita dihapus.');
    }

    /** Daftar komentar satu berita */
    public function komentar(Berita $berita): JsonResponse
    {
        return response()->json([
            'komentar' => $berita->komentar()->get()->map(fn ($k) => $k->baris())->all(),
        ]);
    }

    public function hapusKomentar(BeritaKomentar $komentar): JsonResponse
    {
        $berita = $komentar->berita;
        $komentar->delete();

        return response()->json([
            'pesan' => __('Komentar dihapus.'),
            'komentar' => $berita->komentar()->get()->map(fn ($k) => $k->baris())->all(),
            'daftar' => $this->daftar(),
        ]);
    }

    private function daftar(): array
    {
        return Berita::with('foto')->withCount('komentar')->latest('id')->get()->map->baris()->all();
    }

    private function balas(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => __($pesan), 'daftar' => $this->daftar()]);
    }
}
