<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\BeritaKomentar;
use App\Models\PendaftaranAsatid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Halaman berita untuk pengunjung:
 * - /berita            semua berita yang sedang tampil
 * - /berita/{slug}     detail + suka, bagikan, komentar
 * Suka & komentar tanpa login. Satu browser dikenali dari cookie "pengunjung_berita" (kode acak).
 */
class BeritaController extends Controller
{
    private const COOKIE = 'pengunjung_berita';

    public function index(): View
    {
        $berita = Berita::sedangTampil()->with('foto')->latest('tampil_mulai')->paginate(9);

        return view('berita.index', compact('berita'));
    }

    public function show(Request $request, Berita $berita): View
    {
        // Berita yang belum/tidak tampil hanya bisa dilihat admin & penulisnya (pratinjau)
        $pratinjau = ! $berita->sedang_tampil;
        abort_if($pratinjau && ! $this->bolehPratinjau($berita), 404);

        // Hitung dilihat sekali per sesi browser
        $kunciLihat = 'berita_dilihat.' . $berita->id;
        if (! $pratinjau && ! $request->session()->has($kunciLihat)) {
            $request->session()->put($kunciLihat, true);
            $berita->increment('jumlah_dilihat');
        }

        $kunci = $this->kunci($request);
        $berita->load('foto');

        return view('berita.show', [
            'berita' => $berita,
            'pratinjau' => $pratinjau,
            'sudahSuka' => $kunci && \App\Models\BeritaSuka::where('berita_id', $berita->id)->where('kunci', $kunci)->exists(),
            'komentar' => $berita->komentar()->take(200)->get()->map(fn ($k) => $k->baris($kunci))->all(),
            'namaLogin' => $this->namaLogin(),
            'lainnya' => Berita::sedangTampil()->where('id', '!=', $berita->id)->with('foto')->latest('tampil_mulai')->take(3)->get(),
        ]);
    }

    /** Suka / batal suka */
    public function suka(Request $request, Berita $berita): JsonResponse
    {
        abort_unless($berita->sedang_tampil, 404);
        $kunci = $this->kunci($request) ?? Str::random(40);

        $ada = \App\Models\BeritaSuka::where('berita_id', $berita->id)->where('kunci', $kunci)->first();
        if ($ada) {
            $ada->delete();
        } else {
            \App\Models\BeritaSuka::firstOrCreate(['berita_id' => $berita->id, 'kunci' => $kunci]);
        }
        $jumlah = \App\Models\BeritaSuka::where('berita_id', $berita->id)->count();
        $berita->forceFill(['jumlah_suka' => $jumlah])->saveQuietly();

        return response()->json(['suka' => ! $ada, 'jumlah' => $jumlah])
            ->withCookie(cookie()->forever(self::COOKIE, $kunci));
    }

    public function komentar(Request $request, Berita $berita): JsonResponse
    {
        abort_unless($berita->sedang_tampil, 404);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'isi' => ['required', 'string', 'max:2000'],
            'situs' => ['prohibited'], // jebakan bot
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'isi.required' => 'Tulis komentar Anda terlebih dahulu.',
            'isi.max' => 'Komentar maksimal 2000 karakter.',
            'situs.prohibited' => 'Komentar tidak dapat dikirim.',
        ]);

        $kunci = $this->kunci($request) ?? Str::random(40);
        $user = Auth::user();

        $berita->komentar()->create([
            'nama' => trim($data['nama']),
            'isi' => trim($data['isi']),
            'user_id' => $user?->id,
            'peran' => $user?->role,
            'kunci' => $kunci,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'pesan' => __('Komentar terkirim.'),
            'komentar' => $berita->komentar()->take(200)->get()->map(fn ($k) => $k->baris($kunci))->all(),
        ])->withCookie(cookie()->forever(self::COOKIE, $kunci));
    }

    /** Pengunjung menghapus komentarnya sendiri (browser yang sama) */
    public function hapusKomentar(Request $request, BeritaKomentar $komentar): JsonResponse
    {
        $kunci = $this->kunci($request);
        abort_unless($kunci && $komentar->kunci === $kunci, 403);
        $berita = $komentar->berita;
        $komentar->delete();

        return response()->json([
            'pesan' => __('Komentar dihapus.'),
            'komentar' => $berita->komentar()->take(200)->get()->map(fn ($k) => $k->baris($kunci))->all(),
        ]);
    }

    private function kunci(Request $request): ?string
    {
        $k = $request->cookie(self::COOKIE);

        return is_string($k) && strlen($k) >= 20 && strlen($k) <= 64 ? $k : null;
    }

    private function bolehPratinjau(Berita $berita): bool
    {
        $user = Auth::user();

        return $user && ($user->role === 'admin'
            || ($berita->penulis_peran === $user->role && (int) $berita->penulis_user_id === (int) $user->id));
    }

    /** Nama untuk isian komentar bila sedang login */
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
