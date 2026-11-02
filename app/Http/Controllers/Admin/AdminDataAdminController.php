<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAdmin;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Admin > Manajemen Data > Data Admin
 * Daftar admin yang mendaftar lewat link khusus, lalu diterima/ditolak admin.
 * Alurnya sama dengan Data Asatid.
 */
class AdminDataAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;

        $admin = PendaftaranAdmin::query()
            ->cari($request->q)
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stat = [
            'total'    => PendaftaranAdmin::count(),
            'menunggu' => PendaftaranAdmin::where('status', PendaftaranAdmin::MENUNGGU)->count(),
            'diterima' => PendaftaranAdmin::where('status', PendaftaranAdmin::DITERIMA)->count(),
            'ditolak'  => PendaftaranAdmin::where('status', PendaftaranAdmin::DITOLAK)->count(),
        ];

        // Link pendaftaran yang dibagikan ke calon admin
        $linkDaftar = route('pendaftaran-admin.daftar', PendaftaranAdmin::kodeLink());

        return view('admin.data-admin.index', compact('admin', 'stat', 'status', 'linkDaftar'));
    }

    public function show(PendaftaranAdmin $dataAdmin)
    {
        return view('admin.data-admin.show', ['a' => $dataAdmin]);
    }

    public function terima(Request $request, PendaftaranAdmin $dataAdmin, WhatsAppService $wa)
    {
        $a = $dataAdmin;
        if ($a->status === PendaftaranAdmin::DITERIMA) {
            return back()->with('error', 'Pendaftar ini sudah diterima sebelumnya.');
        }

        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        // Terima + buat akun di tabel users dengan role admin
        $a->terima($request->input('catatan_admin'));

        $pesan = "Assalamu'alaikum *{$a->nama_lengkap}*, alhamdulillah pendaftaran Anda sebagai *ADMIN* dinyatakan *DITERIMA*.\n\n"
            . ($a->catatan_admin ? "*Catatan:*\n{$a->catatan_admin}\n\n" : '')
            . "Silakan masuk ke akun admin:\n" . url('/login') . "\n"
            . "Email: *{$a->email}*\n"
            . "Password: sesuai yang Anda isi saat mendaftar.";

        $terkirim = $wa->kirim($a->no_hp, $pesan);

        return redirect()->route('admin.data-admin.show', $a)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Admin diterima dan chat WhatsApp terkirim.'
                : 'Admin diterima, tetapi chat WhatsApp GAGAL terkirim. Hubungi lewat ikon WhatsApp di halaman ini.'
        );
    }

    public function tolak(Request $request, PendaftaranAdmin $dataAdmin, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500'], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $dataAdmin->tolak($request->catatan_admin);

        $wa->kirim(
            $dataAdmin->no_hp,
            "Assalamu'alaikum *{$dataAdmin->nama_lengkap}*, mohon maaf pendaftaran Anda sebagai admin *DITOLAK*.\n\n"
                . "Alasan:\n" . $request->catatan_admin
        );

        return redirect()->route('admin.data-admin.index')->with('success', 'Pendaftar admin ditolak.');
    }

    /** Buat link pendaftaran baru; link lama langsung tidak berlaku. */
    public function linkBaru()
    {
        PendaftaranAdmin::buatKodeLinkBaru();

        return back()->with('success', 'Link pendaftaran admin baru sudah dibuat. Link lama tidak bisa dipakai lagi.');
    }

    /** Hapus data admin beserta pas foto dan akun login-nya. Akun sendiri tidak bisa dihapus. */
    public function destroy(PendaftaranAdmin $dataAdmin)
    {
        if ($dataAdmin->user_id && (int) $dataAdmin->user_id === (int) Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $nama = $dataAdmin->nama_lengkap;
        $foto = $dataAdmin->berkas_foto;

        DB::transaction(function () use ($dataAdmin) {
            $user = $dataAdmin->user;
            $dataAdmin->delete();

            if ($user && $user->role === PendaftaranAdmin::ROLE) {
                $user->delete();
            }
        });

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return redirect()->route('admin.data-admin.index')->with('success', "Data {$nama} berhasil dihapus.");
    }
}
