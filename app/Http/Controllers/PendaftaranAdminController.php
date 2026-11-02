<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranAdminRequest;
use App\Models\PendaftaranAdmin;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Formulir pendaftaran admin (publik, tapi hanya bisa dibuka lewat link khusus
 * yang dibagikan dari halaman Manajemen Data > Data Admin).
 */
class PendaftaranAdminController extends Controller
{
    public function create(string $kode)
    {
        $this->cekKode($kode);

        return view('pendaftaran-admin.daftar', compact('kode'));
    }

    public function store(StorePendaftaranAdminRequest $request, string $kode, WhatsAppService $wa)
    {
        $this->cekKode($kode);

        $data = $request->safe()->except(['berkas_foto', 'email_login', 'password_login']);

        $admin = DB::transaction(function () use ($request, $data) {
            $nomor = PendaftaranAdmin::buatNomor();

            return PendaftaranAdmin::create($data + [
                'nomor_pendaftaran' => $nomor,
                'email'             => $request->input('email_login'),
                'password'          => Hash::make($request->input('password_login')),
                'berkas_foto'       => $request->file('berkas_foto')->store("admin/{$nomor}", 'public'),
                'status'            => PendaftaranAdmin::MENUNGGU,
            ]);
        });

        $wa->kirim(
            $admin->no_hp,
            "Assalamu'alaikum *{$admin->nama_lengkap}*, pendaftaran Anda sebagai admin berhasil kami terima.\n"
                . "Nomor pendaftaran: *{$admin->nomor_pendaftaran}*\n"
                . "Data sedang diverifikasi. Kami akan menghubungi kembali setelah ada keputusan."
        );

        session(['admin_daftar_sukses' => $admin->nomor_pendaftaran]);

        return response()->json([
            'message'  => 'Pendaftaran berhasil.',
            'redirect' => route('pendaftaran-admin.sukses', $admin->nomor_pendaftaran),
        ]);
    }

    public function sukses(string $nomor)
    {
        abort_unless(session('admin_daftar_sukses') === $nomor, 404);

        $admin = PendaftaranAdmin::where('nomor_pendaftaran', $nomor)->firstOrFail();

        return view('pendaftaran-admin.sukses', compact('admin'));
    }

    /** Link lama / salah ketik -> halaman 404 */
    private function cekKode(string $kode): void
    {
        abort_unless(hash_equals(PendaftaranAdmin::kodeLink(), $kode), 404);
    }
}
