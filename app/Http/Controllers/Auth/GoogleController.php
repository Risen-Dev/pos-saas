<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $socialiteDriver = Socialite::driver('google');
            if (app()->environment('local')) {
                $socialiteDriver->stateless();
            }

            $googleUser = $socialiteDriver->user();

            // 1. Cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            $isNewUser = false;

            if ($user) {
                if (! $user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // 2. Registrasi User Baru (email_verified_at = null)
                $isNewUser = true;
                $user = User::create([
                    'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Pengguna GrowPOS',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => null,
                ]);

                // Kirim email verifikasi saat user baru dibuat
                $user->sendEmailVerificationNotification();
            }

            // 3. Login-kan user & regenerasi sesi
            Auth::login($user, true);
            request()->session()->regenerate();

            // 4. Jika BELUM VERIFIKASI -> Tahan di Halaman Verifikasi
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('status', 'verification-link-sent');
            }

            // 5. Jika SUDAH VERIFIKASI tapi belum punya toko
            if (! $user->tenant_id) {
                return redirect()->route('tenants.create');
            }

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            Log::error('Google Auth Error: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Gagal autentikasi Google: '.$e->getMessage());
        }
    }
}
