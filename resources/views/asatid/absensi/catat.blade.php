{{--
    Akun Asatid > Absensi Kegiatan (langkah 2: mencatat kehadiran).
    Isi halamannya dipakai bersama dengan admin: resources/views/absensi/partials/catat-kegiatan.blade.php
--}}
@extends('layouts.asatid')

@section('judul', __('Absensi') . ' ' . ($absensi->kegiatan?->nama ?? __('Kegiatan')))

@section('content')
@include('absensi.partials.catat-kegiatan', ['rute' => [
    'simpan'  => route('asatid.absensi.kegiatan.simpan', $absensi),
    'status'  => route('asatid.absensi.kegiatan.status', $absensi),
    'selesai' => $absensi->dari_admin ? null : route('asatid.absensi.kegiatan.selesai', $absensi),
]])
@endsection
