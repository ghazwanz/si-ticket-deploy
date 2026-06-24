@extends('layouts.organizer')
@section('title', 'Edit Acara')
@section('page-title', 'Edit Acara')

@section('content')
    <div class="space-y-6">
        <x-organizer.page-hero
            eyebrow="Penyempurnaan Acara"
            title="Edit {{ $event->name }}"
            description="Perbarui informasi acara dengan hati-hati agar rincian publikasi, jadwal, lokasi, dan kategori tiket tetap akurat."
            icon="pencil-square" />

        @include('organizer.events.partials.form', [
            'event' => $event,
            'categories' => $categories,
            'action' => route('organizer.events.update', $event),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
@endsection
