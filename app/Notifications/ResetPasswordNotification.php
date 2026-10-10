<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        // Replikasi cara Breeze membangun URL reset, biar tetap konsisten
        // dengan route password.reset bawaan (support custom frontend URL juga).
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        if (static::$createUrlCallback) {
            $resetUrl = call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        $request = request();

        $message = (new MailMessage)
            ->view(['html' => 'mail.notification-inline', 'text' => 'mail.notification-text'])
            ->subject('Permintaan Reset Password Akun GrowPOS Anda')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mereset password akun GrowPOS Anda.')
            ->line('Klik tombol di bawah ini untuk membuat password baru. Jika Anda tidak meminta ini, abaikan saja email ini dan password Anda tidak akan berubah.')
            ->action('Reset Password', $resetUrl)
            ->line('Tautan reset password ini akan kedaluwarsa dalam '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60).' menit demi keamanan akun Anda.')
            ->salutation("Salam hangat,\nTim Ekosistem GrowPOS Indonesia");

        $message->viewData = [
            'preheader' => 'Permintaan reset password untuk akun GrowPOS Anda',
            'securityInfo' => [
                'time' => now()->translatedFormat('d M Y, H:i').' WIB',
                'ip' => $request?->ip() ?? '-',
                'device' => $request ? substr($request->userAgent() ?? '-', 0, 60) : '-',
            ],
        ];

        return $message;
    }
}
