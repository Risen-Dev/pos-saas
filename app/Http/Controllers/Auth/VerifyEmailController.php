<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Jika user belum punya toko, arahkan ke setup bisnis dengan pesan sukses verifikasi
        if (! $request->user()->tenant_id) {
            return redirect()->route('tenants.create')
                ->with('status', 'Email Anda berhasil diverifikasi! Silakan mendaftarkan bisnis/outlet GrowPOS Anda.');
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1')
            ->with('status', 'Email Anda berhasil diverifikasi! Selamat datang di GrowPOS.');
    }
}
