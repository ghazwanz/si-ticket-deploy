<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyPendingEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $pendingEmail, public User $user)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'profile.verify-pending-email',
            now()->addMinutes(config('auth.verification.expire', 1440)), // 24 hours
            [
                'id' => $this->user->id,
                'hash' => sha1($this->pendingEmail),
            ]
        );

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email Baru - JoinFest')
            ->greeting('Halo, '.$this->user->name.'!')
            ->line('Kami menerima permintaan untuk mengubah alamat email akun JoinFest Anda menjadi: '.$this->pendingEmail)
            ->line('Silakan klik tombol di bawah ini untuk memverifikasi alamat email baru Anda. Email lama Anda tetap aktif untuk masuk ke platform sampai email baru telah diverifikasi.')
            ->action('Verifikasi Email Baru', $verificationUrl)
            ->line('Tautan verifikasi ini akan kedaluwarsa dalam waktu 24 jam.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pending_email' => $this->pendingEmail,
            'user_id' => $this->user->id,
        ];
    }
}
