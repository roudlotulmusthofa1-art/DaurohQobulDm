<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSiswa;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AdminPendaftaranController extends Controller
{
    public function index(Request $request)
    {
        // status tetap dari route (data-diterima); kalau tidak ada, pakai dropdown filter
        $statusTetap = $request->route('status');
        $status = $statusTetap ?: $request->status;

        $pendaftar = PendaftaranSiswa::query()
            ->cari($request->q)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stat = [
            'total'    => PendaftaranSiswa::count(),
            'menunggu' => PendaftaranSiswa::where('status', 'menunggu')->count(),
            'diterima' => PendaftaranSiswa::where('status', 'diterima')->count(),
            'ditolak'  => PendaftaranSiswa::where('status', 'ditolak')->count(),
        ];

        return view('admin.pendaftaran.index', compact('pendaftar', 'stat', 'statusTetap'));
    }

    public function show(PendaftaranSiswa $pendaftaran)
    {
        return view('admin.pendaftaran.show', ['p' => $pendaftaran]);
    }

    public function terima(Request $request, PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        if ($pendaftaran->status === PendaftaranSiswa::DITERIMA) {
            return back()->with('error', 'Pendaftar ini sudah diterima sebelumnya.');
        }

        $kode = $pendaftaran->terima($request->input('catatan_admin'));

        $terkirim = $wa->kirim(
            $pendaftaran->no_hp_wali,
            "Assalamu'alaikum, alhamdulillah santri *{$pendaftaran->nama_lengkap}* dinyatakan *DITERIMA*.\n\n"
                . "Silakan masuk ke akun siswa:\n" . url('/login') . "\n"
                . "Email: *{$pendaftaran->emailLogin()}*\n"
                . "Kode akses (password): *{$kode}*\n\n"
                . "Mohon simpan kode ini dan jangan dibagikan."
        );

        return redirect()->route('admin.pendaftaran.show', $pendaftaran)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Pendaftar diterima dan chat WhatsApp terkirim.'
                : "Pendaftar diterima, tetapi chat WhatsApp GAGAL terkirim. Kode akses (kirim manual): {$kode}"
        );
    }

    public function tolak(Request $request, PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500']);

        $pendaftaran->tolak($request->catatan_admin);

        $wa->kirim(
            $pendaftaran->no_hp_wali,
            "Assalamu'alaikum, mohon maaf pendaftaran santri *{$pendaftaran->nama_lengkap}* belum dapat kami terima.\n"
                . "Catatan: {$request->catatan_admin}"
        );

        return redirect()->route('admin.pendaftaran.index')->with('success', 'Pendaftar ditolak.');
    }

    public function destroy(PendaftaranSiswa $pendaftaran)
    {
        $pendaftaran->delete();

        return redirect()->route('admin.pendaftaran.index')->with('success', 'Data pendaftar dihapus.');
    }
}
