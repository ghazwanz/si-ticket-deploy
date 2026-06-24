<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CancellationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CancellationApprovedNotification extends Notification implements ShouldQueue
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
            ->subject("Pengajuan Pembatalan Disetujui: {$event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Kami ingin memberitahukan bahwa permohonan pembatalan untuk acara \"{$event->name}\" telah disetujui oleh Admin.")
            ->line('Status acara saat ini telah diubah menjadi dibatalkan, dan semua dana pembayaran akan diproses pengembaliannya ke pembeli tiket.')
            ->line('Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim dukungan kami.');
    }
}
