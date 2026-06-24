<x-mail::message>
# Pendaftaran Anda Ditangguhkan / Ditolak

Halo **{{ $user->name }}**,

Mohon maaf, pendaftaran organisasi Anda, **{{ $user->organizerProfile->organization_name }}**, sebagai Penyelenggara Acara (Event Organizer) di JoinFest belum dapat disetujui oleh Administrator karena alasan berikut:

> **Alasan Penolakan:**
> {{ $rejectionReason }}

Silakan masuk ke akun Anda untuk memperbarui data pendaftaran, mengunggah ulang dokumen legalitas yang valid, atau silakan hubungi tim dukungan kami jika Anda merasa ini adalah kesalahan.

<x-mail::button :url="route('organizer.dashboard')">
Masuk ke Konsol & Perbarui Profil
</x-mail::button>

Terima kasih,<br>
Tim {{ config('app.name') }}
</x-mail::message>
