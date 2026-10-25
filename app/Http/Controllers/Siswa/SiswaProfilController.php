<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaProfilController extends Controller
{
    public function index()
    {
        $siswa = PendaftaranSiswa::where('user_id', Auth::id())->firstOrFail();

        return view('siswa.profil', compact('siswa'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
