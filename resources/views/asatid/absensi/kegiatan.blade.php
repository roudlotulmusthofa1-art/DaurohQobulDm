{{--
    Akun Asatid > Absensi > Absensi Kegiatan.
    Absensi kegiatan DIBUAT & DIAKTIFKAN ADMIN (Admin > Akademik > Absensi Kegiatan).
    Di sini asatid memilih kegiatan yang aktif lalu mengabsen — semua asatid bisa mencatat di kegiatan yang sama.
--}}
@extends('layouts.asatid')

@section('judul', __('Absensi Kegiatan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$warnaHex = [
    'bg-slate-100' => '#94a3b8', 'bg-red-100' => '#f87171', 'bg-orange-100' => '#fb923c', 'bg-amber-100' => '#fbbf24',
    'bg-yellow-100' => '#facc15', 'bg-lime-100' => '#a3e635', 'bg-green-100' => '#4ade80', 'bg-emerald-100' => '#34d399',
    'bg-teal-100' => '#2dd4bf', 'bg-cyan-100' => '#22d3ee', 'bg-sky-100' => '#38bdf8', 'bg-blue-100' => '#60a5fa',
    'bg-indigo-100' => '#818cf8', 'bg-violet-100' => '#a78bfa', 'bg-purple-100' => '#c084fc', 'bg-pink-100' => '#f472b6', 'bg-rose-100' => '#fb7185',
];
$hariIni = now('Asia/Jakarta')->toDateString();
$jumlahPeserta = \App\Models\PendaftaranSiswa::where('status', \App\Models\PendaftaranSiswa::DITERIMA)->count();
@endphp

<div class="space-y-5">
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 via-teal-600 to-[#0b332c] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-lime-300/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-100/80">{{ __('Absensi bersama') }}</p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Pilih kegiatan untuk diabsen') }}</h2>
            <p class="mt-1.5 text-sm text-white/75">{{ __('Kegiatan dan waktunya diatur admin. Semua asatid bisa mencatat di kegiatan yang sama, jadi bisa saling membantu.') }}</p>
        </div>
    </section>

    @forelse ($kegiatanAdmin as $a)
    @php
        $warna = $warnaHex[$a->kegiatan?->warna] ?? '#14b8a6';
        $persen = $jumlahPeserta ? min(100, round($a->detail_count / $jumlahPeserta * 100)) : 0;
    @endphp
    <a href="{{ route('asatid.absensi.kegiatan.catat', $a) }}"
        class="{{ $kartu }} group block p-5 sm:p-6 hover:-translate-y-0.5 hover:shadow-[0_20px_40px_-20px_rgba(11,51,44,0.35)] transition-all">
        <div class="flex items-start gap-4">
            <span class="w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center text-white text-lg font-extrabold" style="background-color: {{ $warna }}" v-pre>
                {{ mb_strtoupper(mb_substr($a->kegiatan?->nama ?? 'K', 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1" v-pre>
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-extrabold text-slate-800">{{ $a->kegiatan?->nama ?? __('Kegiatan') }}</h3>
                    @if ($a->tanggal->toDateString() === $hariIni)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('Hari ini') }}
                    </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500">{{ $a->tanggal->locale('id')->translatedFormat('l, d F Y') }}@if ($a->tanggal_hijriyah) · {{ $a->tanggal_hijriyah }}@endif</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-emerald-50 text-emerald-700 px-3 py-1">{{ __('Mulai') }} {{ substr((string) $a->jam_mulai, 0, 5) }}</span>
                    <span class="rounded-full bg-amber-50 text-amber-700 px-3 py-1">{{ __('Terlambat') }} {{ substr((string) $a->batas_terlambat, 0, 5) }}</span>
                    <span class="rounded-full bg-rose-50 text-rose-700 px-3 py-1">{{ __('Alfa') }} {{ substr((string) $a->batas_alfa, 0, 5) }}</span>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between text-[11px] text-slate-500">
                        <span>{{ __(':n dari :total peserta sudah tercatat', ['n' => $a->detail_count, 'total' => $jumlahPeserta]) }}</span>
                        <span>{{ $persen }}%</span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ $persen }}%"></div></div>
                </div>
            </div>
            <span class="hidden sm:inline-flex shrink-0 items-center gap-2 self-center rounded-2xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white group-hover:bg-[#135347]">
                {{ __('Absen') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </span>
        </div>
        <span class="sm:hidden mt-4 flex items-center justify-center gap-2 rounded-2xl bg-[#0b332c] py-3 text-sm font-bold text-white">{{ __('Absen kegiatan ini') }}</span>
    </a>
    @empty
    <div class="{{ $kartu }} px-6 py-14 text-center">
        <span class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
        </span>
        <p class="mt-3 font-semibold text-slate-700">{{ __('Belum ada absensi kegiatan yang aktif') }}</p>
        <p class="mt-1 text-sm text-slate-400">{{ __('Absensi kegiatan dibuat dan diaktifkan oleh admin. Hubungi admin bila ada kegiatan yang perlu diabsen.') }}</p>
    </div>
    @endforelse
</div>
@endsection
