<?php

namespace App\Http\Controllers;

use App\Models\GaleriKiriman;
use App\Models\GaleriKomentar;
use App\Models\GaleriSuka;
use App\Models\PendaftaranAsatid;
use App\Support\Galeri;
use App\Support\SesiAplikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Halaman publik Galeri Foto:
 * - /galeri            album yang sudah tampil + foto berita. ?asal=admin|kiriman|berita
 * - /galeri/{id}       album: semua foto + suka, bagikan, komentar (tanpa login)
 * Satu browser dikenali dari cookie "pengunjung_berita" (kode acak, sama dengan berita).
 * Balas komentar: hanya admin & pengirim album (sedang login). Admin boleh menghapus semua komentar.
 */
class GaleriController extends Controller
{
    private const PER_HALAMAN = 24;

    private const COOKIE = 'pengunjung_berita';

    public function index(Request $request): View
    {
        $asal = in_array($request->query('asal'), Galeri::ASAL, true) ? $request->query('asal') : null;
        $semua = Galeri::semua($asal);
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        $foto = new LengthAwarePaginator(
            $semua->forPage($halaman, self::PER_HALAMAN)->values(),
            $semua->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => array_filter(['asal' => $asal])],
        );

        return view('galeri.index', compact('foto', 'asal'));
    }

    public function show(Request $request, GaleriKiriman $album): View
    {
        // Album yang belum/tidak tampil hanya bisa dilihat admin & pengirimnya (pratinjau)
        $pratinjau = $album->status !== GaleriKiriman::TAMPIL;
        abort_if($pratinjau && ! $this->milikAtauAdmin($album), 404);

        $kunciLihat = 'galeri_dilihat.' . $album->id;
        if (! $pratinjau && ! $request->session()->has($kunciLihat)) {
            $request->session()->put($kunciLihat, true);
            $album->increment('jumlah_dilihat');
        }

        $kunci = $this->kunci($request);
        $album->load('foto');

        return view('galeri.show', [
            'album' => $album,
            'pratinjau' => $pratinjau,
            'sudahSuka' => $kunci && GaleriSuka::where('kiriman_id', $album->id)->where('kunci', $kunci)->exists(),
            'komentar' => $this->daftarKomentar($album, $kunci),
            'bolehBalas' => $this->milikAtauAdmin($album),
            'namaLogin' => $this->namaLogin(),
            'lainnya' => GaleriKiriman::tampil()->where('id', '!=', $album->id)->with('foto')->latest('id')->take(4)->get()
                ->filter(fn ($a) => $a->foto->isNotEmpty()),
        ]);
    }

    /** Suka / batal suka */
    public function suka(Request $request, GaleriKiriman $album): JsonResponse
    {
        abort_unless($album->status === GaleriKiriman::TAMPIL, 404);
        $kunci = $this->kunci($request) ?? Str::random(40);

        $ada = GaleriSuka::where('kiriman_id', $album->id)->where('kunci', $kunci)->first();
        if ($ada) {
            $ada->delete();
        } else {
            GaleriSuka::firstOrCreate(['kiriman_id' => $album->id, 'kunci' => $kunci]);
        }
        $jumlah = GaleriSuka::where('kiriman_id', $album->id)->count();
        $album->forceFill(['jumlah_suka' => $jumlah])->saveQuietly();

        return response()->json(['suka' => ! $ada, 'jumlah' => $jumlah])
            ->withCookie(cookie()->forever(self::COOKIE, $kunci));
    }

    public function komentar(Request $request, GaleriKiriman $album): JsonResponse
    {
        abort_unless($album->status === GaleriKiriman::TAMPIL, 404);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'isi' => ['required', 'string', 'max:2000'],
            'induk_id' => ['nullable', 'integer'],
            'situs' => ['prohibited'], // jebakan bot
        ], [
            'nama.required' => __('Nama wajib diisi.'),
            'isi.required' => __('Tulis komentar Anda terlebih dahulu.'),
            'isi.max' => __('Komentar maksimal 2000 karakter.'),
            'situs.prohibited' => __('Komentar tidak dapat dikirim.'),
        ]);

        $kunci = $this->kunci($request) ?? Str::random(40);
        $user = Auth::user();

        $indukId = null;
        if (! empty($data['induk_id'])) {
            abort_unless($this->milikAtauAdmin($album), 403, __('Hanya admin dan pengirim foto yang bisa membalas komentar.'));
            $induk = $album->komentar()->whereKey($data['induk_id'])->whereNull('induk_id')->first();
            abort_unless($induk, 422, __('Komentar yang dibalas sudah tidak ada.'));
            $indukId = $induk->id;
        }

        $album->komentar()->create([
            'induk_id' => $indukId,
            'nama' => trim($data['nama']),
            'isi' => trim($data['isi']),
            'user_id' => $user?->id,
            'peran' => $user?->role,
            'kunci' => $kunci,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'pesan' => $indukId ? __('Balasan terkirim.') : __('Komentar terkirim.'),
            'komentar' => $this->daftarKomentar($album, $kunci),
        ])->withCookie(cookie()->forever(self::COOKIE, $kunci));
    }

    /** Hapus komentar: milik sendiri (browser yang sama) atau admin */
    public function hapusKomentar(Request $request, GaleriKomentar $komentar): JsonResponse
    {
        $kunci = $this->kunci($request);
        abort_unless($this->adalahAdmin() || ($kunci && $komentar->kunci === $kunci), 403);
        $album = $komentar->kiriman;
        $komentar->delete();

        return response()->json([
            'pesan' => __('Komentar dihapus.'),
            'komentar' => $this->daftarKomentar($album, $kunci),
        ]);
    }

    private function daftarKomentar(GaleriKiriman $album, ?string $kunci): array
    {
        $admin = $this->adalahAdmin();

        return $album->komentar()->whereNull('induk_id')->with('balasan')->take(200)->get()
            ->map(fn ($k) => $k->baris($kunci, $admin))->all();
    }

    private function adalahAdmin(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    /** Admin, atau pengirim album ini (siswa: sesinya juga cocok) */
    private function milikAtauAdmin(GaleriKiriman $album): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }

        return $album->penulis_peran === $user->role
            && (int) $album->penulis_user_id === (int) $user->id
            && ($user->role !== 'siswa' || (int) $album->sesi_id === (int) SesiAplikasi::aktif()?->id);
    }

    private function kunci(Request $request): ?string
    {
        $k = $request->cookie(self::COOKIE);

        return is_string($k) && strlen($k) >= 20 && strlen($k) <= 64 ? $k : null;
    }

    private function namaLogin(): ?string
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        if ($user->role === 'asatid') {
            return rescue(fn () => PendaftaranAsatid::where('user_id', $user->id)->value('nama_lengkap'), null, false) ?: $user->name;
        }

        return $user->name;
    }
}
