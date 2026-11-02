<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\MenuAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Sistem > Role & Permission (khusus Super Admin).
 * - Role bawaan (Super Admin, Asatid, Peserta): hanya nama/keterangan/warna yang bisa diubah.
 * - Role pengurus buatan sendiri: pilih menu admin yang boleh dibuka (izin).
 *   Akun admin diberi role ini lewat Sistem > Daftar User.
 */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderByDesc('bawaan')->orderBy('id')->get()->map(fn (Role $r) => [
            'id'         => $r->id,
            'kunci'      => $r->kunci,
            'nama'       => $r->nama,
            'keterangan' => $r->keterangan,
            'warna'      => $r->warna,
            'bawaan'     => $r->bawaan,
            'izin'       => array_values(array_intersect($r->izin ?? [], MenuAdmin::semuaKunci())),
            'jumlah'     => $r->jumlahAkun(),
        ]);

        return view('admin.sistem.role', [
            'roles'      => $roles,
            'daftarIzin' => MenuAdmin::daftarIzin(),
            'warna'      => Role::WARNA,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $kunci = Str::slug($data['nama'], '_') ?: 'role';
        $dasar = $kunci;
        for ($i = 2; Role::where('kunci', $kunci)->exists() || in_array($kunci, ['admin', 'asatid', 'siswa'], true); $i++) {
            $kunci = $dasar . '_' . $i;
        }

        $role = Role::create($data + ['kunci' => $kunci, 'bawaan' => false]);

        return redirect()->route('admin.role.index', ['role' => $role->id])
            ->with('success', "Role {$role->nama} ditambahkan. Berikan role ini ke akun admin lewat menu Daftar User.");
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validasi($request, $role);

        // Izin role bawaan tidak bisa diubah (Super Admin selalu semua menu)
        if ($role->bawaan) {
            unset($data['izin']);
        }

        $role->update($data);

        return redirect()->route('admin.role.index', ['role' => $role->id])
            ->with('success', "Role {$role->nama} disimpan.");
    }

    public function destroy(Role $role)
    {
        if ($role->bawaan) {
            return back()->with('error', 'Role bawaan tidak bisa dihapus.');
        }
        if ($n = $role->jumlahAkun()) {
            return back()->with('error', "Role {$role->nama} masih dipakai {$n} akun. Ganti role akun tersebut dulu di menu Daftar User.");
        }

        $role->delete();

        return redirect()->route('admin.role.index')->with('success', "Role {$role->nama} dihapus.");
    }

    private function validasi(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'nama'       => ['required', 'string', 'max:80', Rule::unique('pusat.roles', 'nama')->ignore($role?->id)],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'warna'      => ['required', Rule::in(Role::WARNA)],
            'izin'       => ['nullable', 'array'],
            'izin.*'     => [Rule::in(MenuAdmin::semuaKunci())],
        ], [
            'nama.unique' => 'Nama role ini sudah dipakai.',
        ]);

        $data['izin'] = array_values(array_unique($data['izin'] ?? []));

        return $data;
    }
}
