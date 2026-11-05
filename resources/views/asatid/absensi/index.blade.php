{{-- Akun Asatid > Absensi: pilih Absensi Kelas / Absensi Kegiatan + riwayat --}}
@extends('layouts.asatid')

@section('judul', __('Absensi'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$hariIni = now('Asia/Jakarta');
$pilihan = [
    [
        'url'   => route('asatid.absensi.kelas'),
        'judul' => __('Absensi Kelas'),
        'ket'   => __('Absen peserta satu per satu saat mengajar di kelas.'),
        'poin'  => [__('Pilih kelas, pelajaran & jam ke'), __('Satu kartu per peserta'), __('Catatan mudarris per peserta')],
        'warna' => 'from-sky-400 to-blue-600',
        'cahaya'=> 'bg-sky-400/30',
        'aksen' => 'text-sky-600 bg-sky-50 ring-sky-100',
        'ikon'  => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    ],
    [
        'url'   => route('asatid.absensi.kegiatan'),
        'judul' => __('Absensi Kegiatan'),
        'ket'   => __('Kegiatan dari admin, diabsen bersama semua asatid.'),
        'poin'  => [__('Pindai QR kartu peserta'), __('Terlambat & alfa otomatis menurut jam'), __('Bisa dicatat bersama asatid lain')],
        'warna' => 'from-emerald-400 to-teal-600',
        'cahaya'=> 'bg-emerald-400/30',
        'aksen' => 'text-emerald-600 bg-emerald-50 ring-emerald-100',
        'ikon'  => 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z',
    ],
];
@endphp

<div class="space-y-6">
    {{-- Pesan berhasil tampil sebagai notifikasi pojok (partials/notifikasi) --}}

    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Kehadiran Peserta') }}</p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Mau mengabsen apa hari ini?') }}</h2>
            <div class="mt-4 inline-flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    {{ $hariIni->locale('id')->translatedFormat('l, d F Y') }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-300/15 ring-1 ring-amber-200/25 px-3 py-1.5 text-amber-100">
                    {{ \App\Models\Absensi::hijriyah($hariIni->toDateString()) }}
                </span>
            </div>
        </div>
    </section>

    {{-- ===== Absensi kegiatan dari admin yang sedang aktif (dicatat bersama) ===== --}}
    @foreach ($kegiatanAdmin as $k)
    <a href="{{ route('asatid.absensi.kegiatan.catat', $k) }}"
        class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 hover:bg-emerald-100/70 transition-colors">
        <span class="relative flex w-3 h-3 shrink-0">
            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
            <span class="relative inline-flex w-3 h-3 rounded-full bg-emerald-500"></span>
        </span>
        <span class="flex-1 min-w-0 text-sm text-emerald-900" v-pre>
            {{ __('Absensi kegiatan') }} <b>{{ $k->judul }}</b> · {{ $k->tanggal->locale('id')->translatedFormat('d M') }} {{ __('jam') }} {{ substr((string) $k->jam_mulai, 0, 5) }}
            <span class="block text-xs text-emerald-700/80">{{ __(':jumlah peserta sudah tercatat · ayo bantu mengabsen', ['jumlah' => $k->detail_count]) }}</span>
        </span>
        <span class="shrink-0 text-xs font-bold text-white bg-emerald-600 rounded-xl px-3 py-1.5">{{ __('Absen') }}</span>
    </a>
    @endforeach

    {{-- ===== Absensi kegiatan yang belum ditutup ===== --}}
    @foreach ($berjalan as $b)
    <a href="{{ route('asatid.absensi.kegiatan.catat', $b) }}"
        class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 hover:bg-amber-100/70 transition-colors">
        <span class="relative flex w-3 h-3 shrink-0">
            <span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 animate-ping"></span>
            <span class="relative inline-flex w-3 h-3 rounded-full bg-amber-500"></span>
        </span>
        <span class="flex-1 min-w-0 text-sm text-amber-900">
            {{ __('Absensi') }} <b>{{ $b->judul }}</b> ({{ $b->tanggal->locale('id')->translatedFormat('d M Y') }}) {{ __('belum ditutup.') }}
        </span>
        <span class="shrink-0 text-xs font-bold text-amber-800 bg-white ring-1 ring-amber-200 rounded-xl px-3 py-1.5">{{ __('Lanjutkan') }}</span>
    </a>
    @endforeach

    {{-- ===== Dua pilihan ===== --}}
    <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
        @foreach ($pilihan as $p)
        <a href="{{ $p['url'] }}" class="group relative overflow-hidden {{ $kartu }} p-6 hover:-translate-y-1 hover:shadow-[0_24px_48px_-20px_rgba(11,51,44,0.35)] transition-all duration-300">
            <div class="pointer-events-none absolute -top-16 -right-16 w-48 h-48 rounded-full {{ $p['cahaya'] }} blur-3xl opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative flex items-start justify-between">
                <span class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $p['warna'] }} text-white flex items-center justify-center shadow-lg shadow-black/10 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['ikon'] }}"/></svg>
                </span>
                <span class="w-10 h-10 rounded-full bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-400 group-hover:bg-[#0b332c] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </span>
            </div>
            <h3 class="relative mt-5 text-xl font-extrabold text-slate-800">{{ $p['judul'] }}</h3>
            <p class="relative mt-1 text-sm text-slate-500">{{ $p['ket'] }}</p>
            <ul class="relative mt-4 space-y-2">
                @foreach ($p['poin'] as $poin)
                <li class="flex items-center gap-2 text-[13px] text-slate-600">
                    <span class="w-5 h-5 rounded-full ring-1 flex items-center justify-center {{ $p['aksen'] }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    {{ $poin }}
                </li>
                @endforeach
            </ul>
        </a>
        @endforeach
    </div>

    {{-- ===== Riwayat ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800">{{ __('Riwayat absensi Anda') }}</h3>
            <span class="text-xs text-slate-400">{{ __('12 terakhir') }}</span>
        </div>

        @forelse ($riwayat as $r)
        @php $kegiatan = $r->jenis === \App\Models\Absensi::KEGIATAN; @endphp
        <a href="{{ $kegiatan && ! $r->selesai_at ? route('asatid.absensi.kegiatan.catat', $r) : route('asatid.absensi.show', $r) }}"
            class="mt-3 flex items-center gap-3 p-3 -mx-1 rounded-2xl hover:bg-[#f5f9f7] transition-colors">
            <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center ring-1 {{ $kegiatan ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-sky-50 text-sky-600 ring-sky-100' }}">
                <span class="text-[10px] font-extrabold uppercase">{{ $kegiatan ? __('KGT') : __('KLS') }}</span>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-sm font-semibold text-slate-800 truncate">{{ $r->judul }}</span>
                <span class="block text-xs text-slate-400 truncate">
                    {{ $r->tanggal->locale('id')->translatedFormat('d M Y') }} · {{ $r->tanggal_hijriyah }} · {{ $r->keterangan_waktu }}
                </span>
            </span>
            @if ($kegiatan && ! $r->selesai_at)
                <span class="shrink-0 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-amber-50 text-amber-700 ring-1 ring-amber-200">{{ __('Berjalan') }}</span>
            @else
                <span class="shrink-0 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-slate-50 text-slate-500 ring-1 ring-slate-200">{{ __(':jumlah peserta', ['jumlah' => $r->detail_count]) }}</span>
            @endif
        </a>
        @empty
        <div class="mt-4 py-10 text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-300">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            </div>
            <p class="mt-3 text-sm text-slate-500">{{ __('Belum ada absensi. Pilih salah satu di atas untuk mulai.') }}</p>
        </div>
        @endforelse
    </section>
</div>
@endsection
