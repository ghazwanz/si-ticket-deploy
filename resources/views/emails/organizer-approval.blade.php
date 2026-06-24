<x-mail::message>
# Pendaftaran Anda Telah Disetujui!

Halo **{{ $user->name }}**,

Selamat! Pendaftaran organisasi Anda, **{{ $user->organizerProfile->organization_name }}**, sebagai Penyelenggara Acara (Event Organizer) di JoinFest telah disetujui oleh Administrator.

Akun Anda sekarang telah aktif sepenuhnya. Anda sudah dapat membuat acara baru, menjual tiket, mengelola pre-order merchandise, dan menggunakan fitur pemindai QR kami.

<x-mail::button :url="route('organizer.dashboard')">
Masuk ke Konsol Penyelenggara
</x-mail::button>

Terima kasih,<br>
Tim {{ config('app.name') }}
</x-mail::message>
