<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // 1. Jika email BELUM diverifikasi, JANGAN dialihkan ke tenant setup dulu.
            // Biarkan middleware 'verified' Laravel yang mengarahkannya ke /verify-email.
            if (! auth()->user()->hasVerifiedEmail()) {
                if (! $request->routeIs('verification.*') && ! $request->is('logout')) {
                    return redirect()->route('verification.notice');
                }
            }

            // 2. Jika email SUDAH diverifikasi tapi belum punya tenant
            if (auth()->user()->tenant_id === null) {
                if (! $request->routeIs('tenants.*') && ! $request->is('logout')) {
                    return redirect()->route('tenants.create');
                }
            }
        }

        return $next($request);
    }
}
