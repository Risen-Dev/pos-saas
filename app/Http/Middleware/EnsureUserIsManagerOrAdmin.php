<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsManagerOrAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'manager'], true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Akses khusus pengelola toko.');
        }

        return redirect()->route('pos.index')->with('error', 'Anda tidak memiliki hak akses untuk menu tersebut.');
    }
}
