@extends('errors.layout')

@section('title', __('Layanan Tidak Tersedia'))
@section('code', '503')
@section('message', __($exception->getMessage() ?: 'Layanan sedang tidak tersedia karena pemeliharaan sistem. Silakan coba sesaat lagi.'))
