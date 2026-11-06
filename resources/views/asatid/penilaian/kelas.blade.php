{{-- Akun Asatid > Penilaian > Nilai Harian / Ujian Lisan / Ujian (alurnya menyusul) --}}
@extends('layouts.asatid')

@php
$isi = [
    'harian' => [__('Nilai Harian'), __('Nanti Anda bisa mengisi nilai harian peserta per kelas & pelajaran di sini.'), 'from-sky-400 to-blue-600',
        'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10'],
    'lisan' => [__('Ujian Lisan'), __('Nanti Anda bisa mengisi nilai ujian lisan (setoran) peserta di sini.'), 'from-violet-400 to-purple-600',
        'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z'],
    'ujian' => [__('Ujian'), __('Nanti Anda bisa mengisi nilai ujian tulis peserta di sini.'), 'from-amber-400 to-orange-500',
        'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
][$bagian];
@endphp

@section('judul', $isi[0])
@section('kembali', route('asatid.penilaian'))

@section('content')
@include('asatid.partials.segera-hadir', [
    'judul' => $isi[0],
    'teks'  => $isi[1],
    'warna' => $isi[2],
    'ikon'  => $isi[3],
    'poin'  => [],
])
@endsection
