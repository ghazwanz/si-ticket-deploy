<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinalPayoutRequestedNotification extends Notification implements ShouldQueue
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
        $organizer = $this->payout->organizer;
        $netAmountFormatted = 'Rp '.number_format($this->payout->net_amount, 0, ',', '.');

        return (new MailMessage)
            ->subject("Pengajuan Pembayaran Akhir (Final Payout): {$event->name}")
            ->greeting('Halo, Admin')
            ->line("Penyelenggara Acara \"{$organizer->name}\" telah mengajukan Pembayaran Akhir (Final Payout) untuk acara \"{$event->name}\" yang telah selesai.")
            ->line('Detail Pengajuan:')
            ->line("- Sisa Dana Bersih (Net): {$netAmountFormatted}")
            ->line("- Nama Bank: {$this->payout->payout_bank_name}")
            ->line("- Nomor Rekening: {$this->payout->payout_account_number}")
            ->line("- Nama Pemilik Rekening: {$this->payout->payout_account_holder}")
            ->action('Review Pengajuan Payout', url('/admin/payouts/'.$this->payout->id))
            ->line('Silakan lakukan peninjauan dan berikan persetujuan atau penolakan.');
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
