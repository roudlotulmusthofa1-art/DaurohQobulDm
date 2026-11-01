{{-- Admin > Sistem > Pengaturan --}}
@extends('layouts.admin')

@section('title', 'Pengaturan')

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
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Sistem</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Pengaturan</h1>
            <p class="mt-1.5 text-sm text-white/70">Atur cara kerja aplikasi sesuai kebutuhan.</p>
        </div>
    </div>

    {{-- Pengaturan Notifikasi --}}
    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="{{ $kartu }} overflow-hidden">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3 px-5 sm:px-6 py-5 border-b border-slate-100">
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-[15px] font-bold text-slate-800">Pengaturan Notifikasi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pilih jenis notifikasi yang muncul di ikon lonceng (kanan atas).</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($notifikasi as $n)
            <label class="flex items-center gap-4 px-5 sm:px-6 py-4 cursor-pointer hover:bg-[#f5f9f7] transition-colors">
                <span class="w-10 h-10 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna_kelas'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon_path'] }}"/></svg>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-semibold text-slate-800">{{ $n['judul'] }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">{{ $n['keterangan'] }}</span>
                </span>

                {{-- Saklar hidup/mati --}}
                <span class="relative shrink-0">
                    <input type="checkbox" name="notif[{{ $n['kunci'] }}]" value="1" @checked($n['aktif']) class="peer sr-only">
                    <span class="block w-11 h-6 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20"></span>
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                </span>
            </label>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4 bg-[#f5f9f7] border-t border-slate-100">
            <p class="text-xs text-slate-500">Notifikasi hilang sendiri begitu masalahnya sudah dibereskan.</p>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
