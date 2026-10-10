<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $request = request();

        $message = (new MailMessage)
            ->view(['html' => 'mail.notification-inline', 'text' => 'mail.notification-text'])
            ->subject('Konfirmasi Registrasi Akun GrowPOS Anda')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Terima kasih telah memilih GrowPOS sebagai mitra pertumbuhan bisnis Anda.')
            ->line('Satu langkah terakhir untuk mengaktifkan seluruh fitur kasir pintar dan laporan otomatis: silakan konfirmasi bahwa alamat email ini adalah milik Anda.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Tautan verifikasi ini berlaku selama 60 menit demi keamanan akun Anda.')
            ->line('Jika Anda tidak merasa mendaftar di layanan GrowPOS, Anda dapat mengabaikan email ini secara aman.')
            ->salutation("Salam hangat,\nTim Ekosistem GrowPOS Indonesia");

        // Inject data custom (preheader & info keamanan) langsung ke viewData,
        // JANGAN pakai ->with() karena method itu sudah dipakai internal untuk menambah line.
        $message->viewData = [
            'preheader' => 'Satu langkah lagi untuk mengaktifkan akun GrowPOS Anda',
            'securityInfo' => [
                'time' => now()->translatedFormat('d M Y, H:i').' WIB',
                'ip' => $request?->ip() ?? '-',
                'device' => $request ? substr($request->userAgent() ?? '-', 0, 60) : '-',
            ],
        ];

        return $message;
    }
}
