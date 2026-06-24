<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdvancePayoutRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Payout $payout)
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
        $event = $this->payout->event;
        $requestedAmountFormatted = 'Rp '.number_format($this->payout->requested_amount, 0, ',', '.');

        return (new MailMessage)
            ->subject("Pengajuan Pembayaran Awal Ditolak: {$event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Permohonan Pembayaran Awal (Advance Payout) Anda untuk acara \"{$event->name}\" sebesar {$requestedAmountFormatted} telah ditolak oleh Admin.")
            ->line('Alasan Penolakan:')
            ->line("\"{$this->payout->rejection_reason}\"")
            ->line('Jika Anda perlu melakukan koreksi atau pengajuan ulang, silakan periksa detailnya di dashboard payout Anda.')
            ->action('Lihat Dashboard Payout', url('/panitia/payouts'))
            ->line('Terima kasih atas perhatian Anda.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
