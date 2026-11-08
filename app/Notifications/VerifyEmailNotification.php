<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Sign only the route path and query so host/proxy changes do not break
     * verification links opened from email clients such as Gmail.
     */
    protected function verificationUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return parent::verificationUrl($notifiable);
        }

        $signedPath = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            absolute: false,
        );

        return rtrim((string) config('app.url'), '/').'/'.ltrim($signedPath, '/');
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->view(['html' => 'mail.notification-inline', 'text' => 'mail.notification-text'])
            ->subject('Konfirmasi Registrasi Akun GrowPOS Anda')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Klik tombol berikut untuk memverifikasi alamat email dan mengaktifkan akun GrowPOS Anda.')
            ->action('Verifikasi Email', $this->verificationUrl($notifiable))
            ->line('Tautan ini berlaku selama 60 menit. Jika Anda tidak membuat akun GrowPOS, abaikan email ini.')
            ->salutation("Salam,\nTim GrowPOS");

        $message->viewData = [
            'preheader' => 'Verifikasi alamat email untuk mengaktifkan akun GrowPOS Anda.',
            'transactionalAuth' => true,
        ];

        return $message;
    }
}
