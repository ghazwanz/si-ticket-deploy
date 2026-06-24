<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EventCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Event $event) {}

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
        $dateStr = $this->event->event_date instanceof \DateTimeInterface
            ? $this->event->event_date->format('d-m-Y')
            : (string) $this->event->event_date;

        return (new MailMessage)
            ->subject("Pembatalan Acara: {$this->event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Kami ingin menginformasikan bahwa acara \"{$this->event->name}\" yang dijadwalkan pada tanggal {$dateStr} telah dibatalkan.")
            ->line('Seluruh pembelian tiket akan diproses pengembalian dananya sesuai dengan Syarat & Ketentuan platform kami.')
            ->line('Terima kasih atas pengertian Anda.');
    }
}
