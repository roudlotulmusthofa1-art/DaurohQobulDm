<?php

namespace App\Http\Controllers\Psb;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePendaftaranRequest;
use App\Models\PendaftaranSiswa;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;

class PendaftaranController extends Controller
{
    public function create()
    {
        return view('psb.daftar');
    }

    public function store(StorePendaftaranRequest $request, WhatsAppService $wa)
    {
        $data = $request->safe()->except(['berkas_foto', 'berkas_kk', 'berkas_akta']);
        $data['setuju'] = true;

        $pendaftar = DB::transaction(function () use ($request, $data) {
            $nomor = PendaftaranSiswa::buatNomor();
            $dir   = "psb/{$nomor}";

            return PendaftaranSiswa::create($data + [
                'nomor_pendaftaran' => $nomor,
                'berkas_foto'       => $request->file('berkas_foto')->store($dir, 'public'),
                'berkas_kk'         => $request->file('berkas_kk')->store($dir, 'public'),
                'berkas_akta'       => $request->file('berkas_akta')->store($dir, 'public'),
                'status'            => PendaftaranSiswa::MENUNGGU,
            ]);
        });

        // Chat konfirmasi (gagal kirim tidak membatalkan pendaftaran)
        $wa->kirim($pendaftar->no_hp_wali,
            "Assalamu'alaikum, pendaftaran santri *{$pendaftar->nama_lengkap}* berhasil kami terima.\n"
            . "Nomor pendaftaran: *{$pendaftar->nomor_pendaftaran}*\n"
            . "Data sedang diverifikasi admin. Kami akan menghubungi kembali setelah ada keputusan."
        );

        return response()->json([
            'message'  => 'Pendaftaran berhasil.',
            'redirect' => route('psb.sukses', $pendaftar->nomor_pendaftaran),
        ]);
    }

    public function sukses(string $nomor)
    {
        $pendaftar = PendaftaranSiswa::where('nomor_pendaftaran', $nomor)->firstOrFail();

        return view('psb.sukses', compact('pendaftar'));
    }
}
