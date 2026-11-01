{{-- Admin > Landing Page > Hero & Statistik --}}
@extends('layouts.admin')

@section('title', 'Hero & Statistik')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$statistik = [
    [
        'judul'  => 'Jumlah Peserta',
        'angka'  => $jumlahPeserta,
        'teks'   => 'Jumlah pendaftar santri berstatus',
        'warna'  => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'ikon'   => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'link'   => route('admin.data-masuk.index'),
        'label'  => 'Lihat Data Masuk Peserta',
    ],
    [
        'judul'  => 'Jumlah Assatid',
        'angka'  => $jumlahAssatid,
        'teks'   => 'Jumlah pendaftar asatid berstatus',
        'warna'  => 'bg-amber-50 text-amber-600 ring-amber-100',
        'ikon'   => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
        'link'   => route('admin.asatid.index'),
        'label'  => 'Lihat Data Asatid',
    ],
];
@endphp

<div class="space-y-6 max-w-4xl">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Landing Page</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Hero &amp; Statistik</h1>
            <p class="mt-1.5 text-sm text-white/70">Angka yang tampil di bagian atas beranda website. Semuanya dihitung otomatis.</p>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        @foreach ($statistik as $s)
        <div class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl ring-1 flex items-center justify-center {{ $s['warna'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['ikon'] }}"/></svg>
                </span>
                <div>
                    <h2 class="text-[15px] font-bold text-slate-800">{{ $s['judul'] }}</h2>
                    <p class="text-xs text-slate-400">Dihitung otomatis</p>
                </div>
            </div>
            <p class="mt-5 text-5xl font-extrabold text-slate-800 tracking-tight">{{ $s['angka'] }}</p>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed">
                {{ $s['teks'] }} <span class="font-semibold text-emerald-700">diterima</span>.
                Angka ini bertambah sendiri setiap admin menerima pendaftar baru.
            </p>
            <a href="{{ $s['link'] }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:underline">
                {{ $s['label'] }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
