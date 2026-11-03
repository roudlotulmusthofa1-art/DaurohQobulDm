{{-- Akun Siswa > Beranda: kartu menu (tampilan sama dengan dasbor asatid) --}}
@extends('layouts.siswa')

@section('content')
@php
use App\Models\PendaftaranSiswa;
$hariIni = now()->locale('id')->translatedFormat('l, d F Y');
$status = [
    PendaftaranSiswa::MENUNGGU => ['Menunggu pengumuman', 'bg-amber-50 text-amber-700 ring-amber-100'],
    PendaftaranSiswa::DITERIMA => ['Diterima', 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
    PendaftaranSiswa::DITOLAK => ['Tidak diterima', 'bg-rose-50 text-rose-700 ring-rose-100'],
][$siswa->status] ?? ['-', 'bg-slate-100 text-slate-500 ring-slate-200'];

$kartuMenu = [
    [
        'judul' => 'Profil',
        'ket'   => 'Data diri yang Anda isi saat mendaftar',
        'url'   => route('siswa.profil'),
        'warna' => 'from-emerald-400 to-teal-600',
        'cahaya'=> 'bg-emerald-400/30',
        'lencana' => [$siswa->nomor_pendaftaran, 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
        'ikon'  => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
    ],
    [
        'judul' => 'Pengumuman Kelulusan',
        'ket'   => 'Hasil seleksi, kelas, dan kamar Anda',
        'url'   => route('siswa.kelulusan'),
        'warna' => 'from-amber-400 to-orange-500',
        'cahaya'=> 'bg-amber-400/30',
        'lencana' => $status,
        'ikon'  => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    ],
    [
        'judul' => 'Pesan dari Admin',
        'ket'   => 'Pesan khusus dan balasan dari admin',
        'url'   => route('siswa.pesan-admin'),
        'warna' => 'from-sky-400 to-blue-600',
        'cahaya'=> 'bg-sky-400/30',
        'lencana' => $jumlahPesanBaru > 0
            ? [$jumlahPesanBaru . ' pesan baru', 'bg-rose-50 text-rose-700 ring-rose-100']
            : ['Tidak ada pesan baru', 'bg-slate-100 text-slate-500 ring-slate-200'],
        'ikon'  => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    ],
    [
        'judul' => 'Pesan untuk Admin',
        'ket'   => 'Tanyakan atau sampaikan sesuatu ke admin',
        'url'   => route('siswa.pesan-saya'),
        'warna' => 'from-violet-400 to-purple-600',
        'cahaya'=> 'bg-violet-400/30',
        'lencana' => [$jumlahPesanSaya ? $jumlahPesanSaya . ' pesan terkirim' : 'Tulis pesan', 'bg-violet-50 text-violet-700 ring-violet-100'],
        'ikon'  => 'M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5',
    ],
];
@endphp

{{-- ===== Sambutan ===== --}}
<section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-7 sm:px-9 sm:py-9">
    <div class="pointer-events-none absolute inset-0 opacity-60"
        style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-emerald-400/25 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/4 w-80 h-80 rounded-full bg-amber-300/10 blur-3xl"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-end gap-5">
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/90">{{ $hariIni }}</p>
            <h2 class="mt-2 text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                Ahlan Wasahlan,<br><span class="text-emerald-300">{{ $siswa->nama_lengkap }}</span>
            </h2>
            @if ($siswa->nama_lengkap_arab)
            <p class="mt-1 text-xl sm:text-2xl text-emerald-100/80" dir="rtl">{{ $siswa->nama_lengkap_arab }}</p>
            @endif
            <p class="mt-3 text-sm text-white/65 max-w-lg">Pilih menu di bawah untuk melihat profil, pengumuman, dan pesan dari admin.</p>
        </div>
        <div class="shrink-0 rounded-2xl bg-white/10 ring-1 ring-white/15 px-4 py-3">
            <p class="text-[11px] text-white/60">Status pendaftaran</p>
            <p class="mt-0.5 font-extrabold {{ $siswa->status === PendaftaranSiswa::DITERIMA ? 'text-emerald-300' : ($siswa->status === PendaftaranSiswa::DITOLAK ? 'text-rose-300' : 'text-amber-200') }}">{{ $status[0] }}</p>
        </div>
    </div>
</section>

{{-- ===== Kartu menu ===== --}}
<div class="mt-6 sm:mt-8 flex items-end justify-between">
    <h3 class="text-lg sm:text-xl font-extrabold text-slate-800">Menu</h3>
    <p class="text-xs text-slate-400">{{ count($kartuMenu) }} menu</p>
</div>

<div class="mt-4 grid grid-cols-2 gap-3 sm:gap-5">
    @foreach ($kartuMenu as $k)
    <a href="{{ $k['url'] }}"
        class="group relative overflow-hidden rounded-3xl bg-white ring-1 ring-[#0b332c]/[0.06] p-4 sm:p-6 flex flex-col min-h-[170px] sm:min-h-[210px] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_48px_-20px_rgba(11,51,44,0.35)] hover:ring-emerald-200">
        <span class="pointer-events-none absolute -top-10 -right-10 w-32 h-32 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 {{ $k['cahaya'] }}"></span>

        <div class="relative flex items-start justify-between gap-2">
            <span class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br {{ $k['warna'] }} text-white flex items-center justify-center shadow-lg shadow-black/10 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['ikon'] }}"/></svg>
            </span>
            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#f5f9f7] text-slate-400 flex items-center justify-center group-hover:bg-[#0b332c] group-hover:text-white transition-colors">
                <svg class="w-4 h-4 -rotate-45 group-hover:rotate-0 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </span>
        </div>

        <div class="relative mt-auto pt-5">
            <h4 class="text-base sm:text-xl font-extrabold text-slate-800 leading-tight">{{ $k['judul'] }}</h4>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 leading-snug">{{ $k['ket'] }}</p>
            <span class="mt-3 inline-flex text-[10px] sm:text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 {{ $k['lencana'][1] }}">{{ $k['lencana'][0] }}</span>
        </div>
    </a>
    @endforeach
</div>

{{-- ===== Kartu QR: tombol melayang "QR Saya" + panel buka-tutup ===== --}}
@include('siswa._kartu-qr')
@endsection
