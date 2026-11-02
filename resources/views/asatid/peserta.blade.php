{{-- Akun Asatid > Daftar Peserta --}}
@extends('layouts.asatid')

@section('judul', 'Daftar Peserta')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

{{-- Ringkasan + pencarian --}}
<div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-500 via-pink-600 to-fuchsia-700 text-white p-5 sm:p-7">
    <div class="pointer-events-none absolute inset-0 opacity-50"
        style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-end gap-4">
        <div class="flex-1">
            <p class="text-xs font-semibold text-white/75">Peserta diterima</p>
            <p class="text-4xl sm:text-5xl font-extrabold tracking-tight">{{ $peserta->total() }}</p>
            <p class="mt-1 text-sm text-white/75">
                @if (request('q') || request('kelas')) sesuai pencarian Anda @else santri yang sudah aktif @endif
            </p>
        </div>
    </div>

    <form method="GET" class="relative mt-5 flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor..."
                class="w-full bg-white rounded-2xl py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-white/30">
        </div>
        <select name="kelas" onchange="this.form.submit()"
            class="sm:w-48 bg-white rounded-2xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:ring-4 focus:ring-white/30">
            <option value="">Semua kelas</option>
            @foreach ($kelasList as $k)
            <option value="{{ $k->id }}" @selected((string) request('kelas') === (string) $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <button class="px-5 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] transition-colors">Cari</button>
    </form>
</div>

{{-- Daftar --}}
@if ($peserta->count())
<div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($peserta as $p)
    <a href="{{ route('asatid.peserta.show', $p) }}" class="{{ $kartu }} p-4 flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-[0_20px_40px_-20px_rgba(11,51,44,0.35)] transition-all">
        @if ($p->foto_url)
        <img src="{{ $p->foto_url }}" alt="" class="w-16 h-20 rounded-2xl object-cover ring-1 ring-slate-200 shrink-0">
        @else
        <span class="w-16 h-20 rounded-2xl bg-gradient-to-br from-rose-200 to-pink-100 text-rose-700 text-2xl font-extrabold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}</span>
        @endif
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800 truncate">{{ $p->nama_lengkap }}</p>
            @if ($p->nama_lengkap_arab)
            <p class="text-sm text-slate-500 truncate" dir="rtl">{{ $p->nama_lengkap_arab }}</p>
            @endif
            <div class="mt-2 flex flex-wrap gap-1.5 text-[11px] font-semibold">
                <span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5">{{ $p->nomor_pendaftaran }}</span>
                <span class="rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 px-2 py-0.5">{{ $p->kelasMaster?->nama ?? 'Belum ada kelas' }}</span>
                @if ($p->kamarMaster)
                <span class="rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100 px-2 py-0.5" dir="auto">{{ $p->kamarMaster->nama }}{{ $p->nomor_kasur ? ' · Kasur ' . $p->nomor_kasur : '' }}</span>
                @endif
            </div>
        </div>
        <svg class="w-5 h-5 shrink-0 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    </a>
    @endforeach
</div>

@if ($peserta->hasPages())
<div class="mt-6">{{ $peserta->links() }}</div>
@endif
@else
<div class="{{ $kartu }} mt-6 px-6 py-14 text-center">
    <span class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
    </span>
    <p class="mt-3 font-semibold text-slate-700">Tidak ada peserta ditemukan</p>
    <p class="mt-1 text-sm text-slate-400">Coba kata kunci atau kelas lain.</p>
</div>
@endif
@endsection
