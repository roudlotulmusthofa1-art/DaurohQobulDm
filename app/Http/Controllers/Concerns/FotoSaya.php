<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GaleriKiriman;
use App\Support\KirimFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Menu "Kirim Foto" untuk asatid & siswa (dipakai AsatidDashboardController & SiswaDashboardController).
 * Album masuk ke Admin > Landing Page > Galeri Foto > Kotak Masuk (status menunggu).
 * Pengirim bisa mengubah/menghapus selama belum tampil; yang ditolak bisa diperbaiki lalu dikirim ulang.
 *
 * Kelas pemakai wajib punya tampil() dan penulisBerita() (lihat trait BeritaSaya).
 */
trait FotoSaya
{
    public function foto(): View
    {
        $peran = Auth::user()->role;

        return $this->tampil('galeri.kirim', [
            'layout' => 'layouts.' . $peran,
            'rute' => $peran . '.foto',
            'daftar' => $this->daftarFotoSaya(),
            'maksFoto' => GaleriKiriman::MAKS_FOTO,
        ]);
    }

    public function simpanFoto(Request $request): JsonResponse
    {
        $data = KirimFoto::validasi($request);
        KirimFoto::simpan($request, $data, null, $this->penulisBerita() + ['status' => GaleriKiriman::MENUNGGU]);

        return $this->balasFoto(__('Foto terkirim ke admin. Foto akan tampil di Galeri setelah diperiksa admin.'));
    }

    public function ubahFoto(Request $request, GaleriKiriman $album): JsonResponse
    {
        $this->pastikanAlbumSaya($album);
        abort_if($album->status === GaleriKiriman::TAMPIL, 422, __('Foto yang sudah ditampilkan admin tidak bisa diubah.'));

        $data = KirimFoto::validasi($request, false);
        KirimFoto::simpan($request, $data, $album, ['status' => GaleriKiriman::MENUNGGU, 'catatan_admin' => null]);

        return $this->balasFoto(__('Perubahan foto terkirim ke admin.'));
    }

    public function hapusFoto(GaleriKiriman $album): JsonResponse
    {
        $this->pastikanAlbumSaya($album);
        $album->hapusSemua();

        return $this->balasFoto(__('Album dihapus.'));
    }

    private function pastikanAlbumSaya(GaleriKiriman $album): void
    {
        $p = $this->penulisBerita();
        $milik = $album->penulis_peran === $p['penulis_peran'] && (int) $album->penulis_user_id === (int) $p['penulis_user_id']
            && ($p['penulis_peran'] !== 'siswa' || (int) $album->sesi_id === (int) $p['sesi_id']);
        abort_unless($milik, 403);
    }

    private function daftarFotoSaya(): array
    {
        $p = $this->penulisBerita();

        return GaleriKiriman::where('penulis_peran', $p['penulis_peran'])->where('penulis_user_id', $p['penulis_user_id'])
            ->when($p['penulis_peran'] === 'siswa', fn ($q) => $q->where('sesi_id', $p['sesi_id']))
            ->with('foto')->withCount('komentar')->latest('id')->get()->map->baris()->all();
    }

    private function balasFoto(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => $pesan, 'daftar' => $this->daftarFotoSaya()]);
    }
}
