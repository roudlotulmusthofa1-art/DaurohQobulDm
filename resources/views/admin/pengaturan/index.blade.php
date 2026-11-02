{{--
    Admin > Sistem > Pengaturan (kerangka halaman)
    Menu kiri dari PengaturanController::TAB. Isi tiap tab ada di file tab-{nama}.blade.php di folder ini:
      tab-umum, tab-profil, tab-notifikasi, tab-pesan, tab-database, tab-sesi
    Menambah tab baru: tambah di PengaturanController::TAB + buat file tab-{nama}.blade.php.

    Vue: seluruh halaman satu aplikasi Vue (#halaman-pengaturan, skrip di bawah).
    Tab boleh menambah data/method sendiri dengan menulis window.tabPengaturan = { data() {...}, methods: {...} }
    di skrip file tab-nya; objek itu dipasang sebagai "mixin" (digabung ke aplikasi).
--}}
@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1.5 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-[13px] font-semibold text-slate-700';
$tombol = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors';
$ukuran = fn ($b) => $b >= 1073741824 ? number_format($b / 1073741824, 1, ',', '.') . ' GB'
    : ($b >= 1048576 ? number_format($b / 1048576, 1, ',', '.') . ' MB' : number_format($b / 1024, 0, ',', '.') . ' KB');
@endphp

<div id="halaman-pengaturan" class="max-w-6xl mx-auto space-y-6">
    {{-- ===== Judul ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-teal-600 via-[#0f6b5c] to-[#0b332c] text-white p-6 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="relative">
            <h1 class="text-2xl font-extrabold tracking-tight">Pengaturan</h1>
            <p class="mt-1 text-sm text-white/80">Konfigurasi aplikasi dan preferensi</p>
        </div>
    </section>

    @if (session('success'))
    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
        <p class="font-semibold">Periksa kembali isian Anda:</p>
        <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="grid lg:grid-cols-[220px_1fr] gap-6 items-start">
        {{-- ===== Menu kiri ===== --}}
        <nav class="{{ $kartu }} p-3 flex lg:flex-col gap-1 overflow-x-auto lg:sticky lg:top-4">
            @foreach ($tabList as $kode => $t)
            <a href="{{ route('admin.pengaturan.index', ['tab' => $kode]) }}"
                class="shrink-0 flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-colors {{ $tab === $kode ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['ikon'] }}"/></svg>
                {{ $t['label'] }}
            </a>
            @endforeach
        </nav>

        <div class="{{ $kartu }} p-5 sm:p-7">
            @include('admin.pengaturan.tab-' . $tab)
        </div>
    </div>
</div>
@endsection

{{-- Aplikasi Vue halaman Pengaturan. Skrip khusus tab (window.tabPengaturan) dimuat lebih dulu oleh file tab-nya. --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            mixins: [window.tabPengaturan ?? {}], // data & method khusus tab yang sedang dibuka
            data() {
                return {
                    // Tombol "Simpan Perubahan" baru aktif setelah ada isian yang diubah (atau setelah gagal validasi)
                    formDiubah: @json($errors->any()),
                };
            },
            methods: { ...metodeHalaman }, // bukaDialog, tutupDialog, konfirmasi (partials/vue-metode.blade.php)
        }).mount('#halaman-pengaturan');
    });
</script>
@endpush
