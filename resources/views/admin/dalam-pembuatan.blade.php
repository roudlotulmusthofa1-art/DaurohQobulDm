{{--
    Halaman umum "Dalam masa pembuatan" untuk menu admin yang halamannya belum dibuat.
    Daftar menunya: App\Support\HalamanDalamPembuatan::DAFTAR (rutenya dibuat otomatis di routes/web.php).
    Data: $judul (nama menu), $kelompok (mis. KEUANGAN), $ikon (nama ikon lucide, sama dengan ikon di sidebar).
    Ikon digambar lucide.createIcons() dari layouts/admin.blade.php.
--}}
@extends('layouts.admin')

@section('title', $judul)

@section('content')
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="$kelompok" :judul="$judul" :keterangan="__('Halaman ini sedang dalam masa pembuatan.')" />

    <section class="relative overflow-hidden rounded-[28px] bg-white ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)] px-6 py-12 sm:px-10 sm:py-16 text-center">
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-64 rounded-full bg-gradient-to-br from-amber-300 to-emerald-500 opacity-15 blur-3xl"></div>

        <div class="relative">
            <span class="mx-auto w-20 h-20 rounded-[26px] bg-gradient-to-br from-emerald-500 to-[#0b332c] text-white flex items-center justify-center shadow-xl shadow-black/10">
                <i data-lucide="{{ $ikon }}" class="w-10 h-10"></i>
            </span>

            <span class="mt-6 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-amber-800 bg-amber-50 ring-1 ring-amber-200 rounded-full px-3 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> {{ __('Dalam masa pembuatan') }}
            </span>

            <h2 class="mt-3 text-xl sm:text-2xl font-extrabold text-slate-800">{{ $judul }}</h2>
            <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                {{ __('Halaman ini sedang disiapkan dan akan segera bisa dipakai. Terima kasih atas kesabarannya.') }}
            </p>

            <a href="{{ route('admin.dashboard') }}"
                class="mt-8 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                {{ __('Kembali ke Dashboard') }}
            </a>
        </div>
    </section>
</div>
@endsection
