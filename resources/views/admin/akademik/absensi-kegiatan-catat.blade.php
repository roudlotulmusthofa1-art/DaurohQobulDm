{{--
    Admin > Penilaian > Absensi Kegiatan > tombol "Absen" (AbsensiKegiatanController@catat).
    Admin ikut mengabsen kegiatan, sama seperti asatid (pindai QR / cari nama). Catatan admin & asatid saling terlihat.
    Isi halamannya dipakai bersama dengan asatid: resources/views/absensi/partials/catat-kegiatan.blade.php
--}}
@extends('layouts.admin')

@section('title', __('Absensi') . ' ' . ($absensi->kegiatan?->nama ?? __('Kegiatan')))

@section('content')
<div class="mx-4 md:mx-10 lg:mx-20 mt-6 mb-10 space-y-4">
    <a href="{{ route('admin.absensi-kegiatan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-800">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        {{ __('Kembali ke Absensi Kegiatan') }}
    </a>

    @include('absensi.partials.catat-kegiatan', ['rute' => [
        'simpan'  => route('admin.absensi-kegiatan.catat.simpan', $absensi),
        'status'  => route('admin.absensi-kegiatan.catat.status', $absensi),
        'selesai' => null, // admin menutup lewat tombol "Tutup" di daftar kegiatan
    ]])
</div>
@endsection
