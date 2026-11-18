{{-- Semua berita yang sedang tampil (/berita) --}}
@extends('layouts.app')

@section('title', __('Berita') . ' - ' . __('Dauroh Qobul Darul Musthofa Tarim'))

@section('content')
<div class="bg-[#f7f5f0] min-h-screen pt-28 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('landing') }}" class="font-semibold text-teal-700 hover:underline">{{ __('Beranda') }}</a>
            <span class="mx-1.5">/</span>{{ __('Berita') }}
        </nav>
        <div class="mt-4 text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0b332c]">{{ __('Berita Terkini') }}</h1>
            @include('berita.partials.pemisah', ['kelas' => 'mt-4'])
        </div>

        @if ($berita->isEmpty())
        <p class="mt-16 text-center text-slate-500">{{ __('Belum ada berita.') }}</p>
        @else
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($berita as $b)
            @include('berita.partials.kartu', ['b' => $b])
            @endforeach
        </div>
        <div class="mt-10">{{ $berita->links() }}</div>
        @endif
    </div>
</div>
@endsection
