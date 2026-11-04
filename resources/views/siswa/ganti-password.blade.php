{{-- Akun Peserta > Ganti Password (formulirnya: partials/ganti-password) --}}
@extends('layouts.siswa')

@section('judul', __('Ganti Password'))
@section('kembali', route('siswa.dashboard'))

@section('content')
@include('partials.ganti-password', ['aksi' => route('siswa.ganti-password.simpan')])
@endsection
