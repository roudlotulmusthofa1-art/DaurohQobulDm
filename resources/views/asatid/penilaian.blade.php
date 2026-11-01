{{-- Akun Asatid > Penilaian --}}
@extends('layouts.asatid')

@section('judul', 'Penilaian')

@section('content')
@if ($semester)
<div class="mb-5 flex items-center gap-3 rounded-2xl bg-white ring-1 ring-violet-100 px-4 py-3 text-sm">
    <span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-100 flex items-center justify-center shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
    </span>
    <div>
        <p class="text-xs text-slate-400">Semester aktif</p>
        <p class="font-bold text-slate-800">{{ $semester->nama }}{{ $semester->tahun_ajaran ? ' · ' . $semester->tahun_ajaran : '' }}</p>
    </div>
</div>
@endif

@include('asatid.partials.segera-hadir', [
    'judul' => 'Penilaian belum tersedia',
    'teks'  => 'Fitur penilaian sedang disiapkan. Nanti Anda bisa memasukkan dan melihat rekap nilai peserta di sini.',
    'warna' => 'from-violet-400 to-purple-600',
    'ikon'  => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    'poin'  => ['Input nilai per kitab / mata pelajaran', 'Nilai ujian dan harian', 'Rekap nilai per semester'],
])
@endsection
