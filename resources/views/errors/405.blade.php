@extends('errors.layout')

@section('title', __('Metode Tidak Diizinkan'))
@section('code', '405')
@section('message', __('Metode HTTP tidak diizinkan untuk permintaan ini.'))
