<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAdmin;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\Role;
use App\Models\User;
use App\Support\MenuAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Sistem > Daftar User: semua akun login beserta role-nya.
 * - Akun admin & asatid dipakai bersama semua sesi; akun peserta hanya dari sesi yang sedang aktif.
 * - Super Admin bisa mengganti role pengurus akun admin (Super Admin / Bendahara / ...).
 * Pencarian, saring role & halaman diatur Vue di tampilan (resources/views/admin/sistem/user.blade.php).
 */
class DaftarUserController extends Controller
{
    public function index()
    {
        $roles = Role::orderByDesc('bawaan')->orderBy('id')->get();
        $bawaan = $roles->where('bawaan', true)->keyBy('kunci');
        $tambahan = $roles->where('bawaan', false)->keyBy('id');

        // Link ke halaman data masing-masing akun
        $dataAdmin = PendaftaranAdmin::whereNotNull('user_id')->pluck('id', 'user_id');
        $dataAsatid = PendaftaranAsatid::whereNotNull('user_id')->pluck('id', 'user_id');
        $dataSiswa = PendaftaranSiswa::whereNotNull('user_id')->pluck('id', 'user_id');

        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role', 'role_id', 'created_at'])
            ->map(function (User $u) use ($bawaan, $tambahan, $dataAdmin, $dataAsatid, $dataSiswa) {
                $role = ($u->role === 'admin' && $u->role_id) ? ($tambahan[$u->role_id] ?? null) : ($bawaan[$u->role] ?? null);

                $detail = match ($u->role) {
                    'admin'  => isset($dataAdmin[$u->id]) ? route('admin.data-admin.show', $dataAdmin[$u->id]) : null,
                    'asatid' => isset($dataAsatid[$u->id]) ? route('admin.asatid.show', $dataAsatid[$u->id]) : null,
                    'siswa'  => isset($dataSiswa[$u->id]) ? route('admin.pendaftaran.show', $dataSiswa[$u->id]) : null,
                    default  => null,
                };

                return [
                    'id'       => $u->id,
                    'nama'     => $u->name,
                    'email'    => $u->email,
                    'dasar'    => $u->role,                       // admin / asatid / siswa
                    'role_id'  => $u->role_id,
                    'role'     => $role?->nama ?? ($u->role ? ucfirst($u->role) . ($u->role_id ? ' (role terhapus)' : '') : 'Tanpa role'),
                    'warna'    => $role?->warna ?? 'slate',
                    'daftar'   => $u->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y'),
                    'urut'     => $u->created_at?->timestamp ?? 0,
                    'detail'   => $detail,
                    'saya'     => $u->id === Auth::id(),
                ];
            });

        return view('admin.sistem.user', [
            'users'      => $users,
            // Pilihan role untuk akun admin: Super Admin (role_id kosong) + role pengurus
            'roleAdmin'  => collect([['id' => null, 'nama' => $bawaan['admin']->nama ?? 'Super Admin', 'warna' => $bawaan['admin']->warna ?? 'emerald']])
                ->concat($tambahan->values()->map(fn (Role $r) => ['id' => $r->id, 'nama' => $r->nama, 'warna' => $r->warna])),
            'bisaUbah'   => MenuAdmin::izinAkun(Auth::user()) === null, // hanya Super Admin
            'bisaLihat'  => \App\Support\MasukSebagai::bolehDipakai(Auth::user()), // tombol "Masuk sebagai"
        ]);
    }

    /** Ganti role pengurus akun admin (khusus Super Admin, tidak bisa untuk akun sendiri) */
    public function ubahRole(Request $request, User $user)
    {
        abort_unless(MenuAdmin::izinAkun(Auth::user()) === null, 403, 'Hanya Super Admin yang bisa mengganti role.');

        if ($user->role !== 'admin') {
            return back()->with('error', 'Role pengurus hanya untuk akun admin. Akun asatid & peserta mengikuti menu datanya masing-masing.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Role akun Anda sendiri tidak bisa diganti di sini (agar tidak terkunci).');
        }

        $data = $request->validate([
            'role_id' => ['nullable', 'integer', Rule::exists('pusat.roles', 'id')->where('bawaan', false)],
        ]);

        // Disimpan lewat model: otomatis disalin ke semua sesi (App\Support\AkunBersama)
        $user->role_id = $data['role_id'] ?? null;
        $user->save();

        $nama = $user->role_id ? Role::find($user->role_id)->nama : 'Super Admin';

        return back()->with('success', "Role {$user->name} diganti menjadi {$nama}.");
    }
}
