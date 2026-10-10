<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $tenant = $user?->tenant;

        // 1. Cek apakah tenant terdaftar
        if (! $tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        // 2. Cek apakah tenant memiliki langganan aktif
        $activeSubscription = $tenant->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->startOfDay())
            ->first();

        if (! $activeSubscription) {
            // Jika request AJAX / API (seperti pada POS store)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Masa langganan Anda telah habis. Silakan perbarui paket di menu Billing.',
                ], 403);
            }

            // Jika akses via browser, arahkan ke halaman Billing
            return redirect()->route('billing.index')
                ->with('warning', 'Masa aktif langganan Anda telah habis. Silakan pilih paket untuk melanjutkan.');
        }

        return $next($request);
    }
}
