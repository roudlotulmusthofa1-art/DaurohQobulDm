<?php

namespace App\Http\Controllers\Psb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePendaftaranRequest;
use App\Models\PendaftaranSiswa;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



class PendaftaranController extends Controller
{
    public function create()
    {
        return view('psb.daftar');
    }

    public function store(StorePendaftaranRequest $request, WhatsAppService $wa)
    {
        // Ambil data yang lolos validasi, TANPA berkas dan TANPA email/password dari form
        $data = $request->safe()->except([
            'berkas_foto',
            'berkas_rekomendasi',
            'berkas_raport_ijazah',
            'berkas_izin_belajar',
            'email_login',
            'password_login',
        ]);
        $data['setuju'] = true;

        $pendaftar = DB::transaction(function () use ($request, $data) {
            $nomor = PendaftaranSiswa::buatNomor();
            $dir   = "psb/{$nomor}";

            $p = PendaftaranSiswa::create($data + [
                'nomor_pendaftaran'    => $nomor,

                // Email & password disimpan di tabel pendaftaran (password di-hash),
                // lalu akun login (role siswa) dibuat agar peserta bisa langsung masuk dasbor siswa.
                'email'                => $request->input('email_login'),
                'password'             => Hash::make($request->input('password_login')),

                'berkas_foto'          => $request->file('berkas_foto')->store($dir, 'public'),
                'berkas_rekomendasi'   => $request->file('berkas_rekomendasi')->store($dir, 'public'),
                'berkas_raport_ijazah' => $request->file('berkas_raport_ijazah')->store($dir, 'public'),
                'berkas_izin_belajar'  => $request->file('berkas_izin_belajar')->store($dir, 'public'),
                'status'               => PendaftaranSiswa::MENUNGGU,
            ]);
            $p->buatAkun();

            return $p;
        });

        // Isi pesan diatur di Pengaturan > Pesan
        if ($pesan = \App\Support\PesanWa::buat('psb_daftar', ['nama' => $pendaftar->nama_lengkap, 'nomor' => $pendaftar->nomor_pendaftaran])) {
            $wa->kirim($pendaftar->no_hp_wali, $pesan);
        }
        session(['psb_sukses' => $pendaftar->nomor_pendaftaran]);
        return response()->json([
            'message'  => 'Pendaftaran berhasil.',
            'redirect' => route('psb.sukses', $pendaftar->nomor_pendaftaran),
        ]);
    }

    public function sukses(string $nomor)
    {
        abort_unless(session('psb_sukses') === $nomor, 404);
        $pendaftar = PendaftaranSiswa::where(
            'nomor_pendaftaran',
            $nomor
        )->firstOrFail();

        return view('psb.sukses', compact('pendaftar'));
    }
}
