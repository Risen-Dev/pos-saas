<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Historical records remain readable/exportable after subscription expiry.
        if ($request->isMethod('GET') && $request->routeIs('help', 'orders.index', 'orders.show', 'orders.print', 'reports.index', 'reports.export-excel', 'reports.exports-list', 'reports.download-file', 'reports.exports-status-json', 'menu.export', 'cash.index')) {
            return $next($request);
        }
        $user = auth()->user();

        if ($user && $user->tenant) {
            $tenant = $user->tenant;

            // Cek apakah tenant memiliki paket aktif yang belum kadaluarsa
            $hasActiveSubscription = $tenant->subscriptions()
                ->where('status', 'active')->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->exists();

            // Jika tidak ada paket aktif / sudah habis masa berlakunya
            if (! $hasActiveSubscription) {
                // Kecualikan rute billing, setup bisnis, dan logout agar pengguna tetap bisa bayar
                if ($request->routeIs('billing.*') || $request->routeIs('tenants.*') || $request->is('logout')) {
                    return $next($request);
                }

                return redirect()->route('billing.index')
                    ->with('warning', 'Masa berlangganan Anda telah habis atau belum aktif. Silakan pilih paket langganan untuk melanjutkan.');
            }
        }

        return $next($request);
    }
}
