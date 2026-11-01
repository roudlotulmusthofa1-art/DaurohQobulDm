<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePendaftaranAsatidRequest;
use App\Models\PendaftaranAsatid;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Formulir pendaftaran asatid (publik, tapi hanya bisa dibuka lewat link khusus
 * yang dibagikan admin dari halaman Data Asatid).
 */
class PendaftaranAsatidController extends Controller
{
    public function create(string $kode)
    {
        $this->cekKode($kode);

        return view('asatid.daftar', compact('kode'));
    }

    public function store(StorePendaftaranAsatidRequest $request, string $kode, WhatsAppService $wa)
    {
        $this->cekKode($kode);

        $data = $request->safe()->except(['berkas_foto', 'email_login', 'password_login']);

        $asatid = DB::transaction(function () use ($request, $data) {
            $nomor = PendaftaranAsatid::buatNomor();

            return PendaftaranAsatid::create($data + [
                'nomor_pendaftaran' => $nomor,
                'email'             => $request->input('email_login'),
                'password'          => Hash::make($request->input('password_login')),
                'berkas_foto'       => $request->file('berkas_foto')->store("asatid/{$nomor}", 'public'),
                'status'            => PendaftaranAsatid::MENUNGGU,
            ]);
        });

        $wa->kirim(
            $asatid->no_hp,
            "Assalamu'alaikum *{$asatid->nama_lengkap}*, pendaftaran Anda sebagai asatid berhasil kami terima.\n"
                . "Nomor pendaftaran: *{$asatid->nomor_pendaftaran}*\n"
                . "Data sedang diverifikasi admin. Kami akan menghubungi kembali setelah ada keputusan."
        );

        session(['asatid_sukses' => $asatid->nomor_pendaftaran]);

        return response()->json([
            'message'  => 'Pendaftaran berhasil.',
            'redirect' => route('asatid.sukses', $asatid->nomor_pendaftaran),
        ]);
    }

    public function sukses(string $nomor)
    {
        abort_unless(session('asatid_sukses') === $nomor, 404);

        $asatid = PendaftaranAsatid::where('nomor_pendaftaran', $nomor)->firstOrFail();

        return view('asatid.sukses', compact('asatid'));
    }

    /** Link lama / salah ketik -> halaman 404 */
    private function cekKode(string $kode): void
    {
        abort_unless(hash_equals(PendaftaranAsatid::kodeLink(), $kode), 404);
    }
}
