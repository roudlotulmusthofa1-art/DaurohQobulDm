<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\BeritaFoto;
use App\Models\GaleriFoto;
use App\Support\Galeri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * Admin > Landing Page > Galeri Foto (hanya admin yang mengunggah).
 * - Unggahan admin: banyak foto sekaligus (diperkecil di browser) + keterangan & tanggal, ubah, hapus
 * - Dari Berita: foto berita yang sudah ditampilkan otomatis masuk galeri; bisa disembunyikan / ditampilkan lagi
 * Semua aksi membalas JSON berisi daftar terbaru.
 */
class GaleriController extends Controller
{
    /** Batas foto per sekali unggah */
    public const MAKS_UNGGAH = 30;

    public function index(): View
    {
        return view('admin.galeri.index', $this->data() + ['maksUnggah' => self::MAKS_UNGGAH]);
    }

    public function unggah(Request $request): JsonResponse
    {
        $data = $request->validate([
            'foto' => ['required', 'array', 'min:1', 'max:' . self::MAKS_UNGGAH],
            'foto.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif'],
            'keterangan' => ['nullable', 'string', 'max:200'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'foto.required' => 'Pilih foto dulu.',
            'foto.max' => 'Sekali unggah paling banyak ' . self::MAKS_UNGGAH . ' foto.',
            'foto.*.mimes' => 'Foto harus berupa gambar JPG, PNG, WEBP, atau GIF.',
        ]);

        $baru = [];
        try {
            foreach ($request->file('foto') as $file) {
                $path = $file->store('galeri', Berita::DISK);
                if (! $path) {
                    throw new \RuntimeException('Foto gagal disimpan.');
                }
                $baru[] = GaleriFoto::create([
                    'path' => $path,
                    'keterangan' => filled($data['keterangan'] ?? null) ? trim($data['keterangan']) : null,
                    'tanggal' => $data['tanggal'] ?? null,
                    'diunggah_oleh' => Auth::user()->name,
                ]);
            }
        } catch (Throwable $e) {
            foreach ($baru as $f) {
                $f->hapusSemua();
            }
            throw $e;
        }

        return $this->balas(__(':jumlah foto ditambahkan ke galeri.', ['jumlah' => count($baru)]));
    }

    public function ubah(Request $request, GaleriFoto $foto): JsonResponse
    {
        $data = $request->validate([
            'keterangan' => ['nullable', 'string', 'max:200'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $foto->update([
            'keterangan' => filled($data['keterangan'] ?? null) ? trim($data['keterangan']) : null,
            'tanggal' => $data['tanggal'] ?? null,
        ]);

        return $this->balas(__('Foto diperbarui.'));
    }

    public function hapus(GaleriFoto $foto): JsonResponse
    {
        $foto->hapusSemua();

        return $this->balas(__('Foto dihapus dari galeri.'));
    }

    /** Foto berita: sembunyikan dari galeri / tampilkan lagi (beritanya tidak berubah) */
    public function sembunyikan(BeritaFoto $foto): JsonResponse
    {
        $foto->update(['sembunyi_galeri' => ! $foto->sembunyi_galeri]);

        return $this->balas($foto->sembunyi_galeri ? __('Foto berita disembunyikan dari galeri.') : __('Foto berita tampil lagi di galeri.'));
    }

    private function data(): array
    {
        return [
            'fotoAdmin' => GaleriFoto::latest('id')->get()->map(fn ($f) => Galeri::barisAdmin($f))->values(),
            'fotoBerita' => Galeri::fotoBerita()->get()->map(fn ($f) => Galeri::barisBerita($f))->values(),
        ];
    }

    private function balas(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => $pesan] + $this->data());
    }
}
