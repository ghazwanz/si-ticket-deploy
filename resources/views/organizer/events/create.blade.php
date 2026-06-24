@extends('layouts.organizer')
@section('title', 'Buat Acara Baru')
@section('page-title', 'Buat Acara Baru')

@section('content')
    <div class="space-y-6">
        <x-organizer.page-hero
            eyebrow="Manajemen Acara"
            title="Buat Acara Baru"
            description="Susun informasi utama, lokasi, jadwal, dan kategori tiket dalam alur yang rapi sebelum acara dikirim untuk dipublikasikan."
            icon="calendar-days" />

        @include('organizer.events.partials.form', [
            'event' => null,
            'categories' => $categories,
            'action' => route('organizer.events.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Acara',
        ])
    </div>
@endsection