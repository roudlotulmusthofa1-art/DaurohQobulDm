<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PeranMiddleware
{
    public function handle(Request $request, Closure $next, string $peran)
    {
        if ($request->user()?->role !== $peran) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
