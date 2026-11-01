{{-- Akun Asatid > Absensi --}}
@extends('layouts.asatid')

@section('judul', 'Absensi')

@section('content')
@include('asatid.partials.segera-hadir', [
    'judul' => 'Absensi peserta belum tersedia',
    'teks'  => 'Fitur absensi sedang disiapkan. Nanti Anda bisa mencatat kehadiran peserta langsung dari HP saat mengajar.',
    'warna' => 'from-sky-400 to-blue-600',
    'ikon'  => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    'poin'  => ['Catat hadir, izin, sakit, alpa', 'Per kelas dan per jam pelajaran', 'Rekap kehadiran tiap peserta'],
])
@endsection
