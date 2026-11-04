<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAdmin;
use App\Services\WhatsAppService;
use App\Support\PesanWa;
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

    /** Tambah Cepat: admin langsung membuat data + akun login (status diterima) */
    public function tambahCepat(Request $request, WhatsAppService $wa)
    {
        [$baru, $pesan] = \App\Support\TambahCepat::simpan($request, PendaftaranAdmin::class, 'admin_terima', $wa);

        return redirect()->route('admin.data-admin.show', $baru)->with('success', $pesan);
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

        // Isi pesan diatur di Pengaturan > Pesan
        $pesan = PesanWa::buat('admin_terima', ['nama' => $a->nama_lengkap, 'catatan' => $a->catatan_admin, 'email' => $a->email]);
        if (! $pesan) {
            return redirect()->route('admin.data-admin.show', $a)->with('success', 'Admin diterima. (Pesan WhatsApp dimatikan di Pengaturan › Pesan.)');
        }

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

        if ($pesan = PesanWa::buat('admin_tolak', ['nama' => $dataAdmin->nama_lengkap, 'alasan' => $request->catatan_admin])) {
            $wa->kirim($dataAdmin->no_hp, $pesan);
        }

        return redirect()->route('admin.data-admin.index')->with('success', 'Pendaftar admin ditolak.');
    }

    /** Buat link pendaftaran baru; link lama langsung tidak berlaku. */
    public function linkBaru()
    {
        PendaftaranAdmin::buatKodeLinkBaru();

        return back()->with('success', 'Link pendaftaran admin baru sudah dibuat. Link lama tidak bisa dipakai lagi.');
    }

    /** Hapus data admin beserta pas foto dan akun login-nya. Akun sendiri tidak bisa dihapus. */
    /**
     * Edit data admin (khusus Super Admin, karena email & password akun admin ikut bisa diganti).
     * Bila akun login sudah dibuat, nama/email/password akun ikut diperbarui dan disalin ke semua sesi.
     */
    public function edit(PendaftaranAdmin $dataAdmin)
    {
        abort_unless(\App\Support\MenuAdmin::izinAkun(Auth::user()) === null, 403, __('Edit data admin hanya bisa dilakukan Super Admin.'));

        return view('admin.data-admin.edit', ['a' => $dataAdmin]);
    }

    public function update(Request $request, PendaftaranAdmin $dataAdmin)
    {
        abort_unless(\App\Support\MenuAdmin::izinAkun(Auth::user()) === null, 403, __('Edit data admin hanya bisa dilakukan Super Admin.'));

        $a = $dataAdmin;
        $polaArab = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}-]+\z/u';
        // Alamat: huruf Arab + angka & tanda baca alamat (nomor rumah, RT/RW)
        $polaAlamat = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}0-9\/,()-]+\z/u';
        $userId = $a->user_id;

        $data = $request->validate([
            'nama_lengkap'      => ['required', 'string', 'max:150'],
            'nama_lengkap_arab' => ['nullable', 'string', 'max:150', "regex:$polaArab"],
            'no_hp'             => ['required', 'string', 'regex:/^\+62[0-9]+$/', 'max:20'],
            'alamat'            => ['nullable', 'string', 'max:500', "regex:$polaAlamat"],
            'email'             => [
                'required', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('pusat.pendaftaran_admin', 'email')->ignore($a->id),
                'unique:pusat.pendaftaran_asatid,email',
                'unique:pendaftaran_siswas,email',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId),
                \Illuminate\Validation\Rule::unique('pusat.users', 'email')->ignore($userId),
            ],
            'password_baru'     => ['nullable', 'string', 'min:8', 'max:100'],
            'berkas_foto'       => ['nullable', 'image', 'max:5120'],
            'catatan_admin'     => ['nullable', 'string', 'max:1000'],
        ], [
            // Pesan per kolom (bukan '*.regex', supaya tidak menimpa pesan nomor WhatsApp)
            'nama_lengkap_arab.regex' => __('Kolom ini harus ditulis menggunakan huruf Arab.'),
            'alamat.regex'      => __('Kolom ini harus ditulis menggunakan huruf Arab.'),
            'no_hp.regex'       => __('Format nomor WhatsApp tidak valid (contoh: +6281234567890).'),
            'email.unique'      => __('Email ini sudah dipakai akun lain.'),
            'password_baru.min' => __('Password baru minimal 8 karakter.'),
            'berkas_foto.max'   => __('Pas foto maksimal 5 MB.'),
        ], [
            'nama_lengkap' => __('Nama lengkap'), 'nama_lengkap_arab' => __('Nama Arab'), 'no_hp' => __('Nomor WhatsApp'),
            'alamat' => __('Alamat'), 'berkas_foto' => __('Pas foto'),
        ]);

        $fotoLama = null;
        if ($request->hasFile('berkas_foto')) {
            $fotoLama = $a->berkas_foto;
            $data['berkas_foto'] = $request->file('berkas_foto')->store("admin/{$a->nomor_pendaftaran}", 'bersama');
        } else {
            unset($data['berkas_foto']);
        }

        $passwordBaru = $data['password_baru'] ?? null;
        unset($data['password_baru']);
        if ($passwordBaru) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($passwordBaru);
        }

        DB::connection('pusat')->transaction(function () use ($a, $data) {
            $a->update($data);

            // Akun login ikut diperbarui (disimpan lewat model -> disalin ke semua sesi)
            if ($user = $a->akunLogin()) {
                $user->forceFill(array_filter([
                    'name'     => $a->nama_lengkap,
                    'email'    => $a->email,
                    'password' => $data['password'] ?? null, // sudah ter-hash
                ]))->save();
            }
        });

        if ($fotoLama) {
            Storage::disk('bersama')->delete($fotoLama);
        }

        return redirect()->route('admin.data-admin.show', $a)->with('success', __('Data :nama berhasil diperbarui.', ['nama' => $a->nama_lengkap]));
    }

    /** Reset password akun login admin (khusus Super Admin). Aturan & pesannya di App\Support\ResetPasswordAkun. */
    public function resetPassword(Request $request, PendaftaranAdmin $dataAdmin, WhatsAppService $wa)
    {
        return \App\Support\ResetPasswordAkun::jalankan($request, $dataAdmin, $wa);
    }

    public function destroy(PendaftaranAdmin $dataAdmin)
    {
        if ($dataAdmin->user_id && (int) $dataAdmin->user_id === (int) Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $nama = $dataAdmin->nama_lengkap;
        $foto = $dataAdmin->berkas_foto;

        DB::connection('pusat')->transaction(function () use ($dataAdmin) {
            $user = $dataAdmin->user;
            $dataAdmin->delete();

            if ($user && $user->role === PendaftaranAdmin::ROLE) {
                $user->delete();
            }
        });

        if ($foto) {
            Storage::disk('bersama')->delete($foto);
        }

        return redirect()->route('admin.data-admin.index')->with('success', "Data {$nama} berhasil dihapus.");
    }
}
