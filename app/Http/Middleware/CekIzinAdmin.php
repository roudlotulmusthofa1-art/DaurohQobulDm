<?php

namespace App\Http\Middleware;

use App\Support\MenuAdmin;
use Closure;
use Illuminate\Http\Request;

/**
 * Akun admin dengan role pengurus (mis. Bendahara) hanya boleh membuka menu yang dicentang
 * di Sistem > Role & Permission. Super Admin (role_id kosong) bebas membuka semua.
 * Berlaku untuk semua route bernama "admin.*" (dipasang di grup web, bootstrap/app.php).
 */
class CekIzinAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $nama = $request->route()?->getName();

        if (! $nama || ! str_starts_with($nama, 'admin.') || MenuAdmin::boleh($request->user(), $nama)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Role Anda tidak memiliki izin membuka menu ini.');
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'Role Anda tidak memiliki izin membuka menu tersebut. Hubungi Super Admin bila perlu akses.');
    }
}
