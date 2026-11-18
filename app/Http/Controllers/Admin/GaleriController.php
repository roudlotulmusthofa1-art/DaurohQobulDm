<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BeritaFoto;
use App\Models\GaleriKiriman;
use App\Support\Galeri;
use App\Support\KirimFoto;
use App\Support\TulisBerita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin > Landing Page > Galeri Foto. Semua foto berbentuk ALBUM (GaleriKiriman).
 * - Kotak Masuk: album kiriman asatid & siswa yang menunggu -> Tampilkan / Tolak (+ catatan)
 * - Album: semua album yang tampil / ditolak; admin bisa unggah album baru (langsung tampil), ubah, hapus
 * - Dari Berita: foto berita yang sudah ditampilkan otomatis masuk galeri; bisa disembunyikan / ditampilkan lagi
 * Suka & komentar album ada di halaman /galeri/{id} (admin yang login bisa menghapus komentar di sana).
 * Semua aksi membalas JSON berisi daftar terbaru.
 */
class GaleriController extends Controller
{
    public function index(): View
    {
        return view('admin.galeri.index', $this->data() + ['maksFoto' => GaleriKiriman::MAKS_FOTO]);
    }

    /** Album baru dari admin: langsung tampil */
    public function unggah(Request $request): JsonResponse
    {
        $data = KirimFoto::validasi($request);
        $album = KirimFoto::simpan($request, $data, null, $this->pengirimAdmin() + $this->periksa(GaleriKiriman::TAMPIL));

        return $this->balas(__('Album berisi :jumlah foto ditambahkan ke galeri.', ['jumlah' => $album->foto->count()]));
    }

    /** Ubah album (keterangan, tanggal, tambah / buang foto). Status tidak berubah. */
    public function ubah(Request $request, GaleriKiriman $album): JsonResponse
    {
        $data = KirimFoto::validasi($request, false);
        KirimFoto::simpan($request, $data, $album);

        return $this->balas(__('Album diperbarui.'));
    }

    public function tampilkan(GaleriKiriman $album): JsonResponse
    {
        $album->update($this->periksa(GaleriKiriman::TAMPIL) + ['catatan_admin' => null]);

        return $this->balas(__('Album ditampilkan di Galeri Foto.'));
    }

    public function tolak(Request $request, GaleriKiriman $album): JsonResponse
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);
        $album->update($this->periksa(GaleriKiriman::DITOLAK) + [
            'catatan_admin' => filled($data['catatan'] ?? null) ? trim($data['catatan']) : null,
        ]);

        return $this->balas(__('Album ditolak. Pengirim bisa memperbaiki lalu mengirim ulang.'));
    }

    public function hapus(GaleriKiriman $album): JsonResponse
    {
        $album->hapusSemua();

        return $this->balas(__('Album dihapus dari galeri.'));
    }

    /** Foto berita: sembunyikan dari galeri / tampilkan lagi (beritanya tidak berubah) */
    public function sembunyikan(BeritaFoto $foto): JsonResponse
    {
        $foto->update(['sembunyi_galeri' => ! $foto->sembunyi_galeri]);

        return $this->balas($foto->sembunyi_galeri ? __('Foto berita disembunyikan dari galeri.') : __('Foto berita tampil lagi di galeri.'));
    }

    private function pengirimAdmin(): array
    {
        $user = Auth::user();

        return TulisBerita::penulis($user, $user->name);
    }

    /** Kolom pemeriksaan admin. Tampil pertama kali -> tanggal tampil diisi. */
    private function periksa(string $status): array
    {
        return [
            'status' => $status,
            'diperiksa_oleh' => Auth::user()->name,
            'diperiksa_at' => now(),
        ] + ($status === GaleriKiriman::TAMPIL ? ['tampil_mulai' => now()] : []);
    }

    private function data(): array
    {
        $album = GaleriKiriman::with('foto')->withCount('komentar')->latest('id')->get();

        return [
            'kotakMasuk' => $album->where('status', GaleriKiriman::MENUNGGU)->map->baris()->values(),
            'album' => $album->where('status', '!=', GaleriKiriman::MENUNGGU)->map->baris()->values(),
            'fotoBerita' => Galeri::fotoBerita()->get()->map(fn ($f) => Galeri::barisBerita($f))->values(),
        ];
    }

    private function balas(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => $pesan] + $this->data());
    }
}
