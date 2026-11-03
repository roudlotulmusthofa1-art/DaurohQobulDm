<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman form login.
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Proses percobaan login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Pendaftar santri lama yang belum punya akun: buatkan dari email & password formulir pendaftaran
        if (! \App\Models\User::where('email', $credentials['email'])->exists()) {
            $pendaftar = \App\Models\PendaftaranSiswa::where('email', $credentials['email'])->first();
            if ($pendaftar?->password && \Illuminate\Support\Facades\Hash::check($credentials['password'], $pendaftar->password)) {
                $pendaftar->buatAkun();
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Akun siswa baru bisa dipakai setelah admin menyatakan DITERIMA
            if ($user->role === 'siswa' && ! $user->pendaftaran?->bolehMasukDasbor()) {
                $pesan = $user->pendaftaran?->alasanBelumBolehMasuk() ?? 'Akun siswa ini belum terhubung ke data pendaftaran.';
                Auth::logout();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => $pesan])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Admin boleh kembali ke halaman yang tadi dituju; role lain langsung ke halamannya sendiri
            return $user->role === 'admin'
                ? redirect()->intended($user->halamanAwal())
                : redirect($user->halamanAwal());
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])
            ->onlyInput('email');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
