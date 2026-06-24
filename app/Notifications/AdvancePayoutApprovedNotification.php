<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdvancePayoutApprovedNotification extends Notification implements ShouldQueue
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
        $approvedAmountFormatted = 'Rp '.number_format($this->payout->approved_amount, 0, ',', '.');

        return (new MailMessage)
            ->subject("Pengajuan Pembayaran Awal Disetujui: {$event->name}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Kami ingin memberitahukan bahwa permohonan Pembayaran Awal (Advance Payout) Anda untuk acara \"{$event->name}\" telah disetujui oleh Admin.")
            ->line('Detail Pembayaran Awal:')
            ->line("- Jumlah yang Disetujui: {$approvedAmountFormatted}")
            ->line("- Bank Penerima: {$this->payout->payout_bank_name}")
            ->line("- Nomor Rekening: {$this->payout->payout_account_number}")
            ->line("- Nama Pemilik Rekening: {$this->payout->payout_account_holder}")
            ->line('Dana akan segera ditransfer ke rekening Anda. Silakan periksa dashboard atau riwayat payout Anda.')
            ->action('Lihat Dashboard Payout', url('/panitia/payouts'))
            ->line('Terima kasih atas kerja sama Anda!');
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
