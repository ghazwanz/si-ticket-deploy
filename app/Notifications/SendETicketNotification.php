<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendETicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Order $order
    ) {}

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
        $qrCodeService = app(QrCodeService::class);
        $qrCodes = [];

        // Load relations just in case
        $this->order->loadMissing(['tickets.ticketCategory', 'merchandise.merchandiseVariant.item']);

        foreach ($this->order->tickets as $ticket) {
            if ($ticket->qr_token) {
                $qrCodes[$ticket->id] = $qrCodeService->generatePng($ticket->qr_token);
            }
        }

        foreach ($this->order->merchandise as $merch) {
            if ($merch->merch_token) {
                $qrCodes[$merch->id] = $qrCodeService->generatePng($merch->merch_token);
            }
        }

        return (new MailMessage)
            ->subject("Tiket Elektronik & Detail Transaksi Pembelian JoinFest: {$this->order->event->name}")
            ->markdown('emails.eticket', [
                'order' => $this->order,
                'qrCodes' => $qrCodes,
            ]);
    }
}
