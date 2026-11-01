{{-- Admin > Landing Page > Hero & Statistik --}}
@extends('layouts.admin')

@section('title', 'Hero & Statistik')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div class="space-y-6 max-w-4xl">
    @if (session('success'))
    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Landing Page</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Hero &amp; Statistik</h1>
            <p class="mt-1.5 text-sm text-white/70">Angka yang tampil di bagian atas beranda website.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.landing-hero.update') }}" class="grid sm:grid-cols-2 gap-5">
        @csrf
        @method('PUT')

        {{-- Peserta (otomatis) --}}
        <div class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </span>
                <div>
                    <h2 class="text-[15px] font-bold text-slate-800">Jumlah Peserta</h2>
                    <p class="text-xs text-slate-400">Dihitung otomatis</p>
                </div>
            </div>
            <p class="mt-5 text-5xl font-extrabold text-slate-800 tracking-tight">{{ $jumlahPeserta }}</p>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed">
                Jumlah pendaftar berstatus <span class="font-semibold text-emerald-700">diterima</span>.
                Angka ini bertambah sendiri setiap admin menerima pendaftar baru.
            </p>
            <a href="{{ route('admin.data-masuk.index') }}"
                class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:underline">
                Lihat Data Masuk Peserta
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>

        {{-- Assatid (diisi admin) --}}
        <div class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                </span>
                <div>
                    <h2 class="text-[15px] font-bold text-slate-800">Jumlah Assatid</h2>
                    <p class="text-xs text-slate-400">Diisi oleh admin</p>
                </div>
            </div>
            <label for="jumlah_assatid" class="sr-only">Jumlah assatid</label>
            <input id="jumlah_assatid" name="jumlah_assatid" type="number" min="0" max="10000"
                value="{{ old('jumlah_assatid', $jumlahAssatid) }}"
                class="mt-5 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-3 text-3xl font-extrabold text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 transition">
            @error('jumlah_assatid')
            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
            @enderror
            <p class="mt-3 text-xs text-slate-500 leading-relaxed">
                Data asatid belum tersedia di sistem, jadi angkanya diisi di sini.
            </p>
        </div>

        <div class="sm:col-span-2 flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
