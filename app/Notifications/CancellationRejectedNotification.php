<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CancellationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CancellationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly CancellationRequest $request) {}

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
        $event = $this->request->event;

        return (new MailMessage)
            ->subject("Pengajuan Pembatalan Ditolak: {$event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Kami ingin memberitahukan bahwa permohonan pembatalan untuk acara \"{$event->name}\" telah ditolak oleh Admin.")
            ->line('Alasan Penolakan:')
            ->line("\"{$this->request->rejection_reason}\"")
            ->line('Status acara saat ini dikembalikan menjadi aktif (published).')
            ->line('Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim dukungan kami.');
    }
}
