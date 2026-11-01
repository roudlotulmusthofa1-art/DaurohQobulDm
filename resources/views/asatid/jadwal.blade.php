{{-- Akun Asatid > Jadwal Pelajaran --}}
@extends('layouts.asatid')

@section('judul', 'Jadwal Pelajaran')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div class="grid lg:grid-cols-5 gap-6 items-start">
    {{-- Jadwal mengajar milik asatid --}}
    <div class="lg:col-span-3">
        @include('asatid.partials.segera-hadir', [
            'judul' => 'Jadwal mengajar Anda belum diatur',
            'teks'  => 'Admin belum membagikan jadwal mengajar untuk Anda. Setelah diatur, jadwal kelas yang Anda ampu akan tampil di sini.',
            'warna' => 'from-amber-400 to-orange-500',
            'ikon'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            'poin'  => ['Hari dan jam mengajar', 'Kelas dan kitab yang diampu', 'Pengingat jadwal hari ini'],
        ])
    </div>

    {{-- Info umum dari Master Data --}}
    <div class="lg:col-span-2 space-y-6">
        @if ($semester)
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-400 to-orange-500 text-white p-5 sm:p-6">
            <div class="pointer-events-none absolute -right-8 -bottom-10 w-36 h-36 rounded-full bg-white/15 blur-xl"></div>
            <p class="relative text-xs font-semibold text-white/80">Semester aktif</p>
            <p class="relative mt-1 text-xl font-extrabold">{{ $semester->nama }}</p>
            @if ($semester->tahun_ajaran)
            <p class="relative text-sm text-white/85">{{ $semester->tahun_ajaran }}</p>
            @endif
            @if ($semester->tanggal_mulai && $semester->tanggal_selesai)
            <p class="relative mt-3 text-xs text-white/80">
                {{ \Illuminate\Support\Carbon::parse($semester->tanggal_mulai)->translatedFormat('d M Y') }}
                – {{ \Illuminate\Support\Carbon::parse($semester->tanggal_selesai)->translatedFormat('d M Y') }}
            </p>
            @endif
        </div>
        @endif

        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h2 class="text-[15px] font-bold text-slate-800">Jam Pelajaran</h2>
                    <p class="text-xs text-slate-400">Pembagian jam yang berlaku</p>
                </div>
            </div>

            @forelse ($jamList as $j)
            <div class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                <span class="w-8 h-8 shrink-0 rounded-xl bg-[#f5f9f7] text-slate-500 text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                <span class="flex-1 min-w-0 text-sm font-semibold text-slate-700 truncate" dir="auto">{{ $j->nama }}</span>
                <span class="shrink-0 text-xs font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-2.5 py-1 tabular-nums">
                    {{ $j->jam_mulai }} – {{ $j->jam_selesai }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-400">Jam pelajaran belum diatur di Master Data.</p>
            @endforelse
        </section>
    </div>
</div>
@endsection
