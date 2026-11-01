<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun dinonaktifkan. Hubungi pemilik usaha.'], 403);
            }

            return redirect()->route('login')->withErrors(['email' => 'Akun dinonaktifkan. Hubungi pemilik usaha.']);
        }

        return $next($request);
    }
}
