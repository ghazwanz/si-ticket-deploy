<x-mail::message>
# E-Tiket Acara: {{ $order->event->name }}

Halo {{ $order->user->name }},

Pembayaran Anda telah berhasil dikonfirmasi. Berikut adalah detail pesanan dan tiket Anda.

## Ringkasan Pesanan
- **ID Transaksi:** {{ $order->midtrans_order_id }}
- **Nama Acara:** {{ $order->event->name }}
- **Tanggal Transaksi:** {{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}
- **Total Pembayaran:** Rp {{ number_format($order->total_amount, 0, ',', '.') }}

---

## E-Tiket Anda

Mohon tunjukkan QR Code di bawah ini saat memasuki pintu masuk acara (gate) untuk dipindai oleh panitia.

@foreach($order->tickets as $ticket)
<x-mail::panel>
### {{ $ticket->ticketCategory->name }}
**Pemegang Tiket:** {{ $ticket->holder_name }}

<div style="text-align: center; margin: 15px 0;">
<img src="{{ $message->embedData($qrCodes[$ticket->id], 'ticket-' . $ticket->id . '.png', 'image/png') }}" alt="QR Code" width="200" height="200" style="display: inline-block; margin: 0 auto;">
</div>

*ID Tiket:* {{ $ticket->qr_token }}
</x-mail::panel>
@endforeach

@if($order->merchandise->isNotEmpty())
---

## Merchandise Anda
Tunjukkan QR Code di bawah ini ke stan pengambilan merchandise untuk menukarkan item Anda.

@foreach($order->merchandise as $merch)
<x-mail::panel>
### {{ $merch->merchandiseVariant->item->name }} ({{ $merch->merchandiseVariant->variant_value }})
**Jumlah:** {{ $merch->quantity }} pcs

<div style="text-align: center; margin: 15px 0;">
<img src="{{ $message->embedData($qrCodes[$merch->id], 'merch-' . $merch->id . '.png', 'image/png') }}" alt="QR Code" width="200" height="200" style="display: inline-block; margin: 0 auto;">
</div>

*ID Merch:* {{ $merch->merch_token }}
</x-mail::panel>
@endforeach
@endif

Terima kasih atas pembelian Anda! Sampai jumpa di acara!

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
