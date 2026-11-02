{{-- Akun Siswa > Pesan dari Admin: pesan khusus & balasan admin. Pesan yang tampil otomatis ditandai dibaca. --}}
@extends('layouts.siswa')

@section('judul', 'Pesan dari Admin')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div class="space-y-4 max-w-3xl mx-auto">
    @forelse ($pesan as $p)
    @php $baru = in_array($p->id, $idBaru); @endphp
    <article class="{{ $kartu }} p-5 sm:p-6 {{ $baru ? 'ring-2 ring-sky-300' : '' }}">
        <div class="flex items-start gap-3">
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-bold text-slate-800">{{ $p->judul ?: ($p->balasan_untuk_id ? 'Balasan admin' : 'Pesan dari admin') }}</h2>
                    @if ($baru)<span class="rounded-full bg-sky-500 px-2 py-0.5 text-[10px] font-bold text-white">BARU</span>@endif
                </div>
                <p class="text-xs text-slate-400">{{ $p->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y · H:i') }}</p>
            </div>
        </div>
        @if ($p->dibalas)
        <div class="mt-4 rounded-2xl bg-slate-50 ring-1 ring-slate-100 px-4 py-3 text-xs text-slate-500">
            <p class="font-semibold text-slate-600">Membalas pesan Anda:</p>
            <p class="mt-0.5 line-clamp-2">{{ $p->dibalas->judul ? $p->dibalas->judul . ' — ' : '' }}{{ $p->dibalas->isi }}</p>
        </div>
        @endif
        <p class="mt-4 text-[15px] leading-relaxed text-slate-700 whitespace-pre-line break-words">{{ $p->isi }}</p>
    </article>
    @empty
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <span class="mx-auto w-14 h-14 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        </span>
        <p class="mt-3 font-bold text-slate-700">Belum ada pesan dari admin</p>
        <p class="mt-1 text-sm text-slate-500">Pesan khusus untuk Anda akan muncul di sini.</p>
    </div>
    @endforelse

    {{ $pesan->links() }}
</div>
@endsection
