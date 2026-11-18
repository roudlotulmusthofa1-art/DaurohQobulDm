<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\BeritaFoto;
use App\Models\KategoriBerita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Aturan menulis berita, dipakai bersama admin, asatid, dan siswa.
 * Foto diperkecil dulu di browser penulis (berita/partials/skrip-foto), jadi foto sebesar apa pun bisa dipilih.
 */
class TulisBerita
{
    /** Batas jumlah foto per berita */
    public const MAKS_FOTO = 20;

    /** Kategori aktif dari Master Data > Kategori Berita (sesi aktif): [['nama' => .., 'warna' => 'green'], ...] */
    public static function kategori(): array
    {
        return rescue(fn () => KategoriBerita::where('is_active', true)->orderBy('urutan')->get(['nama', 'warna'])
            ->map(fn ($k) => ['nama' => $k->nama, 'warna' => Berita::namaWarna($k->warna)])->values()->all(), [], false);
    }

    /** Data penulis dari akun yang login */
    public static function penulis(User $user, ?string $nama = null, ?string $foto = null): array
    {
        return [
            'penulis_user_id' => $user->id,
            'penulis_peran' => in_array($user->role, ['admin', 'asatid', 'siswa'], true) ? $user->role : 'admin',
            'penulis_nama' => $nama ?: $user->name,
            'penulis_foto' => $foto ?: $user->avatarUrl(),
            'sesi_id' => SesiAplikasi::aktif()?->id,
        ];
    }

    /** Cek isian formulir. Admin boleh mengisi batas waktu tampil. */
    public static function validasi(Request $request, bool $admin = false): array
    {
        $aturan = [
            'judul' => ['required', 'string', 'max:200'],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'isi' => ['required', 'string', 'max:60000'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'lokasi' => ['nullable', 'string', 'max:80'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'foto' => ['nullable', 'array', 'max:' . self::MAKS_FOTO],
            'foto.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif'],
            'hapus_foto' => ['nullable', 'array'],
            'hapus_foto.*' => ['integer'],
        ];
        if ($admin) {
            $aturan['tampil_sampai'] = ['nullable', 'date_format:Y-m-d'];
        }

        return $request->validate($aturan, [
            'judul.required' => 'Judul berita wajib diisi.',
            'isi.required' => 'Isi berita wajib diisi.',
            'foto.max' => 'Foto paling banyak ' . self::MAKS_FOTO . '.',
            'foto.*.mimes' => 'Foto harus berupa gambar JPG, PNG, WEBP, atau GIF.',
            'foto.*.file' => 'Ada foto yang gagal terkirim. Coba pilih ulang.',
        ], [
            'judul' => 'judul', 'ringkasan' => 'ringkasan', 'isi' => 'isi berita', 'lokasi' => 'lokasi',
        ]);
    }

    /** Tanggal "Tampil sampai" (WIB, sampai akhir hari) -> waktu UTC. Kosong = tampil terus. */
    public static function batas(?string $tanggal): ?Carbon
    {
        return $tanggal ? Carbon::parse($tanggal . ' 23:59:59', Berita::ZONA)->utc() : null;
    }

    /**
     * Simpan berita baru / perubahan + foto.
     * $tambahan = kolom lain (penulis, status, tampil_*).
     */
    public static function simpan(Request $request, array $data, ?Berita $berita = null, array $tambahan = []): Berita
    {
        $kategori = collect(self::kategori())->firstWhere('nama', $data['kategori'] ?? null);

        $isian = [
            'judul' => trim($data['judul']),
            'ringkasan' => filled($data['ringkasan'] ?? null) ? trim($data['ringkasan']) : null,
            'isi' => trim($data['isi']),
            // Kategori dari Master Data dapat warnanya; tulisan sendiri (pilihan "Lainnya") tetap disimpan tanpa warna
            'kategori' => $kategori['nama'] ?? (filled($data['kategori'] ?? null) ? trim($data['kategori']) : null),
            'kategori_warna' => $kategori['warna'] ?? ($berita && ($data['kategori'] ?? null) === $berita->kategori ? $berita->kategori_warna : null),
            'lokasi' => filled($data['lokasi'] ?? null) ? mb_strtoupper(trim($data['lokasi'])) : null,
            // Titik peta hanya disimpan bila ada nama lokasinya
            'lat' => filled($data['lokasi'] ?? null) && filled($data['lat'] ?? null) ? round((float) $data['lat'], 7) : null,
            'lng' => filled($data['lokasi'] ?? null) && filled($data['lng'] ?? null) ? round((float) $data['lng'], 7) : null,
        ] + $tambahan;

        $sisaFoto = $berita ? $berita->foto()->whereNotIn('id', $data['hapus_foto'] ?? [])->count() : 0;
        if ($sisaFoto + count($request->file('foto', [])) > self::MAKS_FOTO) {
            throw \Illuminate\Validation\ValidationException::withMessages(['foto' => 'Foto paling banyak ' . self::MAKS_FOTO . '.']);
        }

        $baru = [];
        try {
            DB::connection('pusat')->transaction(function () use (&$berita, $isian, $request, $data, &$baru) {
                if ($berita) {
                    if ($berita->judul !== $isian['judul'] && $berita->status !== Berita::TAMPIL) {
                        $isian['slug'] = Berita::buatSlug($isian['judul'], $berita->id);
                    }
                    $berita->update($isian);
                } else {
                    $berita = Berita::create($isian + ['slug' => Berita::buatSlug($isian['judul'])]);
                }

                // Foto yang dihapus
                foreach ($berita->foto()->whereIn('id', $data['hapus_foto'] ?? [])->get() as $f) {
                    Storage::disk(Berita::DISK)->delete($f->path);
                    $f->delete();
                }

                // Foto baru (urutan setelah foto lama)
                $urutan = (int) $berita->foto()->max('urutan');
                foreach ($request->file('foto', []) as $file) {
                    $path = $file->store('berita/' . $berita->id, Berita::DISK);
                    if (! $path) {
                        throw new \RuntimeException('Foto gagal disimpan.');
                    }
                    $baru[] = $path;
                    BeritaFoto::create(['berita_id' => $berita->id, 'path' => $path, 'urutan' => ++$urutan]);
                }
            });
        } catch (Throwable $e) {
            foreach ($baru as $p) {
                Storage::disk(Berita::DISK)->delete($p);
            }
            throw $e;
        }

        return $berita->fresh('foto');
    }
}
