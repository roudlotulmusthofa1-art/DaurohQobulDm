<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Berita;
use App\Support\TulisBerita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Menu "Tulis Berita" untuk asatid & siswa (dipakai AsatidDashboardController & SiswaDashboardController).
 * Tulisan masuk ke Kotak Masuk admin (status menunggu). Penulis bisa mengubah/menghapus selama
 * belum tampil; tulisan yang ditolak bisa diperbaiki lalu dikirim ulang.
 *
 * Kelas pemakai wajib punya:
 * - private function tampil(string $view, array $data = [])   -> halaman dengan layout masing-masing
 * - protected function penulisBerita(): array                  -> TulisBerita::penulis(...)
 */
trait BeritaSaya
{
    public function berita(): View
    {
        $peran = Auth::user()->role;

        return $this->tampil('berita.tulis', [
            'layout' => 'layouts.' . $peran,
            'rute' => $peran . '.berita',
            'daftar' => $this->daftarBeritaSaya(),
            'kategori' => TulisBerita::kategori(),
            'maksFoto' => TulisBerita::MAKS_FOTO,
        ]);
    }

    public function simpanBerita(Request $request): JsonResponse
    {
        $data = TulisBerita::validasi($request);
        TulisBerita::simpan($request, $data, null, $this->penulisBerita() + ['status' => Berita::MENUNGGU]);

        return $this->balasBerita('Berita terkirim ke admin. Berita akan tampil setelah diperiksa admin.');
    }

    /** Ubah tulisan sendiri yang belum tampil. Yang ditolak -> dikirim ulang (menunggu lagi). */
    public function ubahBerita(Request $request, Berita $berita): JsonResponse
    {
        $this->pastikanMilikSaya($berita);
        abort_if($berita->status === Berita::TAMPIL, 422, __('Berita yang sudah ditampilkan admin tidak bisa diubah.'));

        $data = TulisBerita::validasi($request);
        TulisBerita::simpan($request, $data, $berita, ['status' => Berita::MENUNGGU, 'catatan_admin' => null]);

        return $this->balasBerita('Perubahan berita terkirim ke admin.');
    }

    public function hapusBerita(Berita $berita): JsonResponse
    {
        $this->pastikanMilikSaya($berita);
        $berita->hapusSemua();

        return $this->balasBerita('Berita dihapus.');
    }

    private function pastikanMilikSaya(Berita $berita): void
    {
        $p = $this->penulisBerita();
        $milik = $berita->penulis_peran === $p['penulis_peran'] && (int) $berita->penulis_user_id === (int) $p['penulis_user_id']
            // akun siswa milik sesinya masing-masing (id bisa sama di sesi lain)
            && ($p['penulis_peran'] !== 'siswa' || (int) $berita->sesi_id === (int) $p['sesi_id']);
        abort_unless($milik, 403);
    }

    private function daftarBeritaSaya(): array
    {
        $p = $this->penulisBerita();

        return Berita::where('penulis_peran', $p['penulis_peran'])->where('penulis_user_id', $p['penulis_user_id'])
            ->when($p['penulis_peran'] === 'siswa', fn ($q) => $q->where('sesi_id', $p['sesi_id']))
            ->with('foto')->withCount('komentar')->latest('id')->get()->map->baris()->all();
    }

    private function balasBerita(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => __($pesan), 'daftar' => $this->daftarBeritaSaya()]);
    }
}
