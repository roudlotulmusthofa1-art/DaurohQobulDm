{{-- Akun Asatid > Ganti Password (formulirnya: partials/ganti-password) --}}
@extends('layouts.asatid')

@section('judul', __('Ganti Password'))
@section('kembali', route('asatid.dashboard'))

@section('content')
@include('partials.ganti-password', ['aksi' => route('asatid.ganti-password.simpan')])
@endsection
