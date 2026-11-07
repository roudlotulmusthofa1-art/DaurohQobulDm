{{-- Akun Asatid > Penilaian: menu Nilai Harian, Ujian Lisan, Ujian, Catatan (Asatid\PenilaianController@index) --}}
@extends('layouts.asatid')

@section('judul', __('Penilaian'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$kelompok = [
    [
        'judul' => __('Kelas & Pelajaran'),
        'menu'  => [
            ['url' => route('asatid.penilaian.kelas', 'harian'), 'judul' => __('Nilai Harian'), 'ket' => __('Nilai harian peserta per kelas & pelajaran.'), 'warna' => 'from-sky-400 to-blue-600', 'cahaya' => 'bg-sky-400/30', 'segera' => true,
                'ikon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10'],
            ['url' => route('asatid.penilaian.kelas', 'lisan'), 'judul' => __('Ujian Lisan'), 'ket' => __('Nilai ujian lisan (setoran) peserta.'), 'warna' => 'from-violet-400 to-purple-600', 'cahaya' => 'bg-violet-400/30', 'segera' => true,
                'ikon' => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z'],
            ['url' => route('asatid.penilaian.kelas', 'ujian'), 'judul' => __('Ujian'), 'ket' => __('Nilai ujian tulis peserta.'), 'warna' => 'from-amber-400 to-orange-500', 'cahaya' => 'bg-amber-400/30', 'segera' => true,
                'ikon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
        ],
    ],
    [
        'judul' => __('Perilaku & Prestasi'),
        'menu'  => [
            ['url' => route('asatid.penilaian.catatan'), 'judul' => __('Catatan'), 'ket' => __('Catat pencapaian atau pelanggaran santri. Langsung masuk ke admin.'), 'warna' => 'from-rose-400 via-amber-400 to-emerald-500', 'cahaya' => 'bg-amber-400/30', 'segera' => false, 'jumlah' => $jumlah,
                'ikon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125'],
        ],
    ],
];
@endphp

<div class="space-y-6">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-violet-400/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Penilaian') }}</p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Apa yang ingin Anda nilai?') }}</h2>
            <p class="mt-1.5 text-sm text-white/70">{{ __('Semua penilaian yang Anda isi langsung masuk ke admin.') }}</p>
            @if ($bulan)
            <span class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5 text-xs">
                {{ __('Bulan ajaran aktif') }}: <b>{{ $bulan->nama }}{{ $bulan->tahunAjaran ? ' · ' . __('TA') . ' ' . $bulan->tahunAjaran->nama : '' }}</b>
            </span>
            @endif
        </div>
    </section>

    @foreach ($kelompok as $g)
    <section>
        <h3 class="mb-3 px-1 text-xs font-bold uppercase tracking-wider text-slate-400">{{ $g['judul'] }}</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($g['menu'] as $m)
            <a href="{{ $m['url'] }}" class="group relative overflow-hidden {{ $kartu }} p-5 hover:-translate-y-1 hover:shadow-[0_24px_48px_-20px_rgba(11,51,44,0.35)] transition-all duration-300">
                <div class="pointer-events-none absolute -top-16 -right-16 w-44 h-44 rounded-full {{ $m['cahaya'] }} blur-3xl opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $m['warna'] }} text-white flex items-center justify-center shadow-lg shadow-black/10 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $m['ikon'] }}"/></svg>
                    </span>
                    @if ($m['segera'])
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-500 bg-slate-100 rounded-full px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> {{ __('Segera hadir') }}
                    </span>
                    @else
                    <span class="text-right">
                        <span class="block text-xl font-extrabold text-slate-800 tabular-nums">{{ $m['jumlah'] }}</span>
                        <span class="block text-[10px] text-slate-400">{{ __('catatan Anda') }}</span>
                    </span>
                    @endif
                </div>
                <h4 class="relative mt-4 text-lg font-extrabold text-slate-800">{{ $m['judul'] }}</h4>
                <p class="relative mt-1 text-sm text-slate-500">{{ $m['ket'] }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endforeach
</div>
@endsection
