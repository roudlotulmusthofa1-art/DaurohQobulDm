{{-- Akun Asatid > Beranda: kartu menu --}}
@extends('layouts.asatid')

@section('content')
@php
$nama = $asatid?->nama_lengkap ?? $user->name;
$hariIni = now()->locale('id')->translatedFormat('l, d F Y');
$jam = (int) now()->format('H');
$salam = $jam < 11 ? __('Selamat pagi') : ($jam < 15 ? __('Selamat siang') : ($jam < 18 ? __('Selamat sore') : __('Selamat malam')));

// Kartu menu (9 kartu = laptop 3 x 3). 'separuh' = di laptop selebar setengah baris; 'penuhHp' = di HP selebar layar.
$kartuMenu = [
    [
        'judul' => __('Profil'),
        'ket'   => __('Data diri dan akun Anda'),
        'url'   => route('asatid.profil'),
        'warna' => 'from-emerald-400 to-teal-600',
        'cahaya'=> 'bg-emerald-400/30',
        'lencana' => [__('Lengkap'), 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
        'ikon'  => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
    ],
    [
        'judul' => __('Absensi'),
        'ket'   => __('Absen peserta di kelas dan kegiatan'),
        'url'   => route('asatid.absensi'),
        'warna' => 'from-sky-400 to-blue-600',
        'cahaya'=> 'bg-sky-400/30',
        'lencana' => [__('Kelas & kegiatan'), 'bg-sky-50 text-sky-700 ring-sky-100'],
        'ikon'  => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    ],
    [
        'judul' => __('Jadwal Pelajaran'),
        'ket'   => __('Jadwal mengajar milik Anda'),
        'url'   => route('asatid.jadwal'),
        'warna' => 'from-amber-400 to-orange-500',
        'cahaya'=> 'bg-amber-400/30',
        'lencana' => $jumlahJam > 0
            ? [__(':jumlah jam / minggu', ['jumlah' => $jumlahJam]), 'bg-amber-50 text-amber-700 ring-amber-100']
            : [__('Belum diatur'), 'bg-slate-100 text-slate-500 ring-slate-200'],
        'ikon'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z',
    ],
    [
        'judul' => __('Penilaian'),
        'ket'   => __('Input dan rekap nilai peserta'),
        'url'   => route('asatid.penilaian'),
        'warna' => 'from-violet-400 to-purple-600',
        'cahaya'=> 'bg-violet-400/30',
        'lencana' => [__('Nilai & catatan'), 'bg-violet-50 text-violet-700 ring-violet-100'],
        'ikon'  => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    ],
    [
        'judul' => __('Soal Ujian'),
        'ket'   => __('Buat soal ujian dan lihat soal yang disetujui admin'),
        'url'   => route('asatid.soal-ujian'),
        'warna' => 'from-amber-400 to-orange-500',
        'cahaya'=> 'bg-amber-400/30',
        'lencana' => $jumlahSoalDisetujui > 0
            ? [__(':jumlah soal disetujui', ['jumlah' => $jumlahSoalDisetujui]), 'bg-amber-50 text-amber-700 ring-amber-100']
            : [__('Buat soal'), 'bg-slate-100 text-slate-500 ring-slate-200'],
        'ikon'  => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    ],
    [
        'judul' => __('Daftar Peserta'),
        'ket'   => __('Lihat semua peserta yang sudah diterima'),
        'url'   => route('asatid.peserta'),
        'warna' => 'from-rose-400 to-pink-600',
        'cahaya'=> 'bg-rose-400/30',
        'lencana' => [__(':jumlah peserta', ['jumlah' => $jumlahPeserta]), 'bg-rose-50 text-rose-700 ring-rose-100'],
        'ikon'  => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    ],
    [
        'judul' => __('Pesan dari Admin'),
        'ket'   => __('Pengumuman dan pesan khusus untuk Anda'),
        'url'   => route('asatid.pesan'),
        'warna' => 'from-violet-400 to-purple-600',
        'cahaya'=> 'bg-violet-400/30',
        'lencana' => $jumlahPesanBaru > 0
            ? [__(':jumlah pesan baru', ['jumlah' => $jumlahPesanBaru]), 'bg-rose-50 text-rose-700 ring-rose-100']
            : [__('Tidak ada pesan baru'), 'bg-slate-100 text-slate-500 ring-slate-200'],
        'ikon'  => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    ],
    [
        'judul' => __('Pesan untuk Admin'),
        'ket'   => __('Kirim pertanyaan, izin, atau laporan ke admin'),
        'url'   => route('asatid.pesan-saya'),
        'warna' => 'from-teal-400 to-cyan-600',
        'cahaya'=> 'bg-teal-400/30',
        'lencana' => $jumlahPesanSaya > 0
            ? [__(':jumlah pesan terkirim', ['jumlah' => $jumlahPesanSaya]), 'bg-teal-50 text-teal-700 ring-teal-100']
            : [__('Tulis pesan'), 'bg-slate-100 text-slate-500 ring-slate-200'],
        'ikon'  => 'M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5',
    ],
    [
        'judul' => __('Ganti Password'),
        'ket'   => __('Ubah password untuk masuk ke akun Anda'),
        'url'   => route('asatid.ganti-password'),
        'warna' => 'from-slate-500 to-slate-700',
        'cahaya'=> 'bg-slate-400/30',
        'lencana' => [__('Keamanan akun'), 'bg-slate-100 text-slate-600 ring-slate-200'],
        'ikon'  => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        'penuhHp' => true, // HP: 9 kartu di 2 kolom -> kartu terakhir selebar layar
    ],
];
@endphp

{{-- ===== Sambutan ===== --}}
<section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-7 sm:px-9 sm:py-9">
    <div class="pointer-events-none absolute inset-0 opacity-60"
        style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-emerald-400/25 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/4 w-80 h-80 rounded-full bg-amber-300/10 blur-3xl"></div>

    <div class="relative">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/90">{{ $hariIni }}</p>
        <h2 class="mt-2 text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
            {{ __('Ahlan Wasahlan ,') }}<br><span class="text-emerald-300">{{ __('Ustad') }} {{ $nama }}</span>
        </h2>
        @if ($asatid?->nama_lengkap_arab)
        <p class="mt-1 text-xl sm:text-2xl text-emerald-100/80" dir="rtl">{{ $asatid->nama_lengkap_arab }}</p>
        @endif
        <p class="mt-3 text-sm text-white/65 max-w-lg">{{ __('Pilih menu di bawah untuk mulai. Semoga hari ini penuh keberkahan.') }}</p>
    </div>
</section>

{{-- ===== Kartu menu ===== --}}
<div class="mt-6 sm:mt-8 flex items-end justify-between">
    <h3 class="text-lg sm:text-xl font-extrabold text-slate-800">{{ __('Menu') }}</h3>
    <p class="text-xs text-slate-400">{{ __(':jumlah menu', ['jumlah' => count($kartuMenu)]) }}</p>
</div>

<div class="mt-4 grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-5">
    @foreach ($kartuMenu as $k)
    <a href="{{ $k['url'] }}"
        @class([
            'group relative overflow-hidden rounded-3xl bg-white ring-1 ring-[#0b332c]/[0.06] p-4 sm:p-6 flex flex-col min-h-[170px] sm:min-h-[210px]',
            'shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]',
            'transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_48px_-20px_rgba(11,51,44,0.35)] hover:ring-emerald-200',
            // HP: 2 kolom ('penuhHp' = selebar layar). Laptop: 3 kolom; 'separuh' = setengah baris.
            'col-span-2 lg:col-span-2' => !empty($k['penuhHp']) && empty($k['separuh']),
            'lg:col-span-2' => empty($k['penuhHp']) && empty($k['separuh']),
            'lg:col-span-3' => !empty($k['separuh']),
        ])>
        {{-- cahaya warna di pojok --}}
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
@endsection
