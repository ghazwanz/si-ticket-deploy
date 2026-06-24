@extends('errors.layout')

@section('title', __('Akses Ditolak'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman ini.'))
