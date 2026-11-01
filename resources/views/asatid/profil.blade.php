{{-- Akun Asatid > Profil --}}
@extends('layouts.asatid')

@section('judul', 'Profil Saya')

@section('content')
@php
$nama = $asatid?->nama_lengkap ?? $user->name;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$grup = $asatid ? [
    ['Data Diri', 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', [
        'Nama (Indonesia)' => $asatid->nama_lengkap,
        'Nama (Arab)'      => $asatid->nama_lengkap_arab,
        'Tempat Lahir'     => $asatid->tempat_lahir,
        'Tanggal Lahir'    => optional($asatid->tanggal_lahir)->translatedFormat('d F Y'),
        'No. WhatsApp'     => $asatid->no_hp,
        'Alamat'           => $asatid->alamat,
    ]],
    ['Riwayat di Darul Musthofa', 'bg-amber-50 text-amber-600 ring-amber-100', 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342', [
        'Lama Belajar'  => $asatid->lama_belajar,
        'Tahun Selesai' => $asatid->tahun_selesai,
    ]],
    ['Akun', 'bg-sky-50 text-sky-600 ring-sky-100', 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', [
        'Email Login'     => $user->email,
        'Bergabung Sejak' => optional($asatid->diproses_at ?? $user->created_at)->translatedFormat('d F Y'),
    ]],
] : [];
@endphp

<div class="grid lg:grid-cols-3 gap-6 items-start">
    {{-- Kartu identitas --}}
    <div class="lg:sticky lg:top-24 relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 text-center">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-16 w-64 h-64 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative">
            @if ($asatid?->foto_url)
            <img src="{{ $asatid->foto_url }}" alt="Foto {{ $nama }}" class="mx-auto w-32 h-40 rounded-3xl object-cover ring-4 ring-white/10 shadow-2xl shadow-black/30">
            @else
            <span class="mx-auto w-32 h-40 rounded-3xl bg-gradient-to-br from-emerald-300 to-lime-200 text-[#0b332c] text-5xl font-extrabold flex items-center justify-center">{{ mb_strtoupper(mb_substr($nama, 0, 1)) }}</span>
            @endif
            <h2 class="mt-5 text-xl font-extrabold leading-tight">{{ $nama }}</h2>
            @if ($asatid?->nama_lengkap_arab)
            <p class="mt-1 text-xl text-emerald-100/80" dir="rtl">{{ $asatid->nama_lengkap_arab }}</p>
            @endif
            <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-300/30 px-3 py-1 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Asatid aktif
                </span>
                @if ($asatid)
                <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 font-semibold"># {{ $asatid->nomor_pendaftaran }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Data --}}
    <div class="lg:col-span-2 space-y-6">
        @if ($asatid?->catatan_admin)
        <div class="rounded-3xl bg-amber-50 ring-1 ring-amber-200/70 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Catatan dari admin</p>
            <p dir="auto" class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $asatid->catatan_admin }}</p>
        </div>
        @endif

        @forelse ($grup as [$judul, $warna, $ikon, $baris])
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-2xl ring-1 flex items-center justify-center {{ $warna }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </span>
                <h3 class="text-[15px] font-bold text-slate-800">{{ $judul }}</h3>
            </div>
            <dl class="grid sm:grid-cols-2 gap-2.5 text-sm">
                @foreach ($baris as $label => $nilai)
                <div @class(['rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3', 'sm:col-span-2' => $label === 'Alamat'])>
                    <dt class="text-xs font-medium text-slate-400">{{ $label }}</dt>
                    <dd dir="auto" class="mt-0.5 text-[15px] font-semibold text-slate-800 break-words whitespace-pre-line">{{ $nilai ?: '-' }}</dd>
                </div>
                @endforeach
            </dl>
        </section>
        @empty
        <div class="{{ $kartu }} p-6 text-sm text-slate-500">Data pendaftaran tidak ditemukan untuk akun ini.</div>
        @endforelse

        <p class="text-xs text-slate-400 text-center">Ada data yang perlu diubah? Hubungi admin.</p>
    </div>
</div>
@endsection
