<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\GaleriFoto;
use App\Models\GaleriKiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Aturan mengirim album foto galeri, dipakai bersama admin (Galeri Foto) dan asatid/siswa (Kirim Foto).
 * Foto diperkecil dulu di browser (window.perkecilFoto di berita/partials/skrip-tulis).
 */
class KirimFoto
{
    /** $baru = album baru (minimal 1 foto wajib) */
    public static function validasi(Request $request, bool $baru = true): array
    {
        return $request->validate([
            'foto' => [$baru ? 'required' : 'nullable', 'array', 'max:' . GaleriKiriman::MAKS_FOTO],
            'foto.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif'],
            'hapus_foto' => ['nullable', 'array'],
            'hapus_foto.*' => ['integer'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'foto.required' => __('Pilih foto dulu.'),
            'foto.max' => __('Satu album paling banyak :n foto.', ['n' => GaleriKiriman::MAKS_FOTO]),
            'foto.*.mimes' => __('Foto harus berupa gambar JPG, PNG, WEBP, atau GIF.'),
            'foto.*.file' => __('Ada foto yang gagal terkirim. Coba pilih ulang.'),
            'keterangan.max' => __('Keterangan paling panjang 500 huruf.'),
        ]);
    }

    /** Simpan album baru / perubahan + foto. $tambahan = kolom lain (pengirim, status, ...). */
    public static function simpan(Request $request, array $data, ?GaleriKiriman $album = null, array $tambahan = []): GaleriKiriman
    {
        $isian = [
            'keterangan' => filled($data['keterangan'] ?? null) ? trim($data['keterangan']) : null,
            'tanggal' => $data['tanggal'] ?? null,
        ] + $tambahan;

        $sisa = $album ? $album->foto()->whereNotIn('id', $data['hapus_foto'] ?? [])->count() : 0;
        $jumlahBaru = count($request->file('foto', []));
        if ($sisa + $jumlahBaru > GaleriKiriman::MAKS_FOTO) {
            throw ValidationException::withMessages(['foto' => __('Satu album paling banyak :n foto.', ['n' => GaleriKiriman::MAKS_FOTO])]);
        }
        if ($sisa + $jumlahBaru === 0) {
            throw ValidationException::withMessages(['foto' => __('Album harus berisi minimal satu foto.')]);
        }

        $baru = [];
        try {
            DB::connection('pusat')->transaction(function () use (&$album, $isian, $request, $data, &$baru) {
                if ($album) {
                    $album->update($isian);
                } else {
                    $album = GaleriKiriman::create($isian);
                }

                foreach ($album->foto()->whereIn('id', $data['hapus_foto'] ?? [])->get() as $f) {
                    Storage::disk(Berita::DISK)->delete($f->path);
                    $f->delete();
                }

                $urutan = (int) $album->foto()->max('urutan');
                foreach ($request->file('foto', []) as $file) {
                    $path = $file->store('galeri', Berita::DISK);
                    if (! $path) {
                        throw new \RuntimeException('Foto gagal disimpan.');
                    }
                    $baru[] = $path;
                    GaleriFoto::create([
                        'kiriman_id' => $album->id,
                        'path' => $path,
                        'urutan' => ++$urutan,
                        'diunggah_oleh' => $album->penulis_nama,
                    ]);
                }
            });
        } catch (Throwable $e) {
            foreach ($baru as $p) {
                Storage::disk(Berita::DISK)->delete($p);
            }
            throw $e;
        }

        return $album->fresh('foto');
    }
}
