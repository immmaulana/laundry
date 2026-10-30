<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Batasi akses berdasarkan role. Contoh: ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            abort(403, 'Akun tidak aktif.');
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Halaman ini hanya untuk: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
