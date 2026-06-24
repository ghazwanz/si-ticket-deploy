<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinalPayoutDisbursedNotification extends Notification implements ShouldQueue
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
        $netAmountFormatted = 'Rp '.number_format($this->payout->net_amount, 0, ',', '.');
        $reference = $this->payout->transfer_reference ?? '-';

        return (new MailMessage)
            ->subject("Pencairan Dana Akhir Selesai: {$event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Pencairan dana akhir (Final Payout) untuk acara \"{$event->name}\" yang telah selesai, kini telah berhasil disalurkan.")
            ->line('Detail Pencairan Akhir:')
            ->line("- Jumlah yang Ditransfer: {$netAmountFormatted}")
            ->line("- Bank Penerima: {$this->payout->payout_bank_name}")
            ->line("- Nomor Rekening: {$this->payout->payout_account_number}")
            ->line("- Nama Pemilik Rekening: {$this->payout->payout_account_holder}")
            ->line("- Referensi Transfer: {$reference}")
            ->line('Terima kasih telah menggunakan JoinFest untuk menyelenggarakan acara Anda!');
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
