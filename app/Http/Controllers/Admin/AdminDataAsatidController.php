<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAsatid;
use App\Services\WhatsAppService;
use App\Support\PesanWa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Admin > Manajemen Data > Data Asatid
 * Daftar asatid yang mendaftar lewat link khusus, lalu diterima/ditolak admin.
 */
class AdminDataAsatidController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;

        $asatid = PendaftaranAsatid::query()
            ->cari($request->q)
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stat = [
            'total'    => PendaftaranAsatid::count(),
            'menunggu' => PendaftaranAsatid::where('status', PendaftaranAsatid::MENUNGGU)->count(),
            'diterima' => PendaftaranAsatid::where('status', PendaftaranAsatid::DITERIMA)->count(),
            'ditolak'  => PendaftaranAsatid::where('status', PendaftaranAsatid::DITOLAK)->count(),
        ];

        // Link pendaftaran yang dibagikan admin ke calon asatid
        $linkDaftar = route('asatid.daftar', PendaftaranAsatid::kodeLink());

        return view('admin.asatid.index', compact('asatid', 'stat', 'status', 'linkDaftar'));
    }

    public function show(PendaftaranAsatid $asatid)
    {
        return view('admin.asatid.show', ['a' => $asatid]);
    }

    public function terima(Request $request, PendaftaranAsatid $asatid, WhatsAppService $wa)
    {
        if ($asatid->status === PendaftaranAsatid::DITERIMA) {
            return back()->with('error', 'Asatid ini sudah diterima sebelumnya.');
        }

        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        // Terima + buat akun di tabel users dengan role asatid
        $asatid->terima($request->input('catatan_admin'));

        // Isi pesan diatur di Pengaturan > Pesan
        $pesan = PesanWa::buat('asatid_terima', ['nama' => $asatid->nama_lengkap, 'catatan' => $asatid->catatan_admin, 'email' => $asatid->email]);
        if (! $pesan) {
            return redirect()->route('admin.asatid.show', $asatid)->with('success', 'Asatid diterima. (Pesan WhatsApp dimatikan di Pengaturan › Pesan.)');
        }

        $terkirim = $wa->kirim($asatid->no_hp, $pesan);

        return redirect()->route('admin.asatid.show', $asatid)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Asatid diterima dan chat WhatsApp terkirim.'
                : 'Asatid diterima, tetapi chat WhatsApp GAGAL terkirim. Hubungi lewat ikon WhatsApp di halaman ini.'
        );
    }

    public function tolak(Request $request, PendaftaranAsatid $asatid, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500'], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $asatid->tolak($request->catatan_admin);

        if ($pesan = PesanWa::buat('asatid_tolak', ['nama' => $asatid->nama_lengkap, 'alasan' => $request->catatan_admin])) {
            $wa->kirim($asatid->no_hp, $pesan);
        }

        return redirect()->route('admin.asatid.index')->with('success', 'Pendaftar asatid ditolak.');
    }

    /** Buat link pendaftaran baru; link lama langsung tidak berlaku. */
    public function linkBaru()
    {
        PendaftaranAsatid::buatKodeLinkBaru();

        return back()->with('success', 'Link pendaftaran baru sudah dibuat. Link lama tidak bisa dipakai lagi.');
    }

    /** Hapus data asatid beserta pas foto dan akun login-nya (hanya akun ber-role asatid). */
    public function destroy(PendaftaranAsatid $asatid)
    {
        $nama = $asatid->nama_lengkap;
        $foto = $asatid->berkas_foto;

        DB::connection('pusat')->transaction(function () use ($asatid) {
            $user = $asatid->user;
            $asatid->delete();

            if ($user && $user->role === PendaftaranAsatid::ROLE) {
                $user->delete();
            }
        });

        if ($foto) {
            Storage::disk('bersama')->delete($foto);
        }

        return redirect()->route('admin.asatid.index')->with('success', "Data {$nama} berhasil dihapus.");
    }
}
