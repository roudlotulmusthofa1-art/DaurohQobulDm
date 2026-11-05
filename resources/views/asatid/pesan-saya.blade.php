{{-- Akun Asatid > Pesan untuk Admin: tulis pesan + riwayat pesan beserta balasan admin (pola sama dengan siswa/pesan-saya) --}}
@extends('layouts.asatid')

@section('judul', __('Pesan untuk Admin'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1.5 w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$waktu = fn ($t) => $t->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y · H:i');
@endphp

<div class="grid lg:grid-cols-5 gap-5 items-start">
    {{-- ===== Tulis pesan ===== --}}
    <form method="POST" action="{{ route('asatid.pesan-saya.kirim') }}" class="lg:col-span-2 {{ $kartu }} p-5 sm:p-6 space-y-4 lg:sticky lg:top-24">
        @csrf
        <div>
            <h2 class="font-bold text-slate-800">{{ __('Tulis pesan') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Pertanyaan, izin, laporan, atau hal lain. Balasan admin muncul di bawah pesan Anda dan di menu Pesan dari Admin.') }}</p>
        </div>
        @if ($errors->any())
        <div class="rounded-2xl bg-rose-50 ring-1 ring-rose-100 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif
        <label class="block"><span class="text-xs font-semibold text-slate-600">{{ __('Judul') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></span>
            <input name="judul" maxlength="150" value="{{ old('judul') }}" placeholder="{{ __('cth. Izin tidak mengajar') }}" class="{{ $input }}"></label>
        <label class="block"><span class="text-xs font-semibold text-slate-600">{{ __('Pesan') }} *</span>
            <textarea name="isi" rows="6" maxlength="2000" required placeholder="{{ __('Tulis pesan Anda…') }}" class="{{ $input }}">{{ old('isi') }}</textarea></label>
        <button class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0b332c] px-5 py-3 text-sm font-bold text-white hover:bg-[#135347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            {{ __('Kirim ke Admin') }}
        </button>
    </form>

    {{-- ===== Riwayat ===== --}}
    <div class="lg:col-span-3 space-y-4">
        <h2 class="font-bold text-slate-800">{{ __('Pesan yang sudah dikirim') }}</h2>
        @forelse ($pesan as $p)
        <article class="{{ $kartu }} p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800">{{ $p->judul ?: __('Tanpa judul') }}</p>
                    <p class="text-xs text-slate-400">{{ $waktu($p->created_at) }}</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $p->balasan->isNotEmpty() ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : ($p->dibaca_at ? 'bg-sky-50 text-sky-700 ring-sky-100' : 'bg-slate-100 text-slate-500 ring-slate-200') }}">
                    {{ $p->balasan->isNotEmpty() ? __('Dibalas') : ($p->dibaca_at ? __('Dibaca admin') : __('Terkirim')) }}
                </span>
            </div>
            <p dir="auto" class="mt-3 text-sm text-slate-700 whitespace-pre-line break-words">{{ $p->isi }}</p>

            @foreach ($p->balasan as $b)
            <div class="mt-3 ml-4 sm:ml-8 rounded-2xl rounded-tl-sm bg-emerald-50 ring-1 ring-emerald-100 px-4 py-3">
                <p class="text-[11px] font-bold text-emerald-700">{{ __('Balasan admin') }} · {{ $waktu($b->created_at) }}</p>
                <p dir="auto" class="mt-1 text-sm text-slate-700 whitespace-pre-line break-words">{{ $b->isi }}</p>
            </div>
            @endforeach
        </article>
        @empty
        <div class="{{ $kartu }} py-12 px-6 text-center text-sm text-slate-500">{{ __('Belum ada pesan yang Anda kirim.') }}</div>
        @endforelse

        {{ $pesan->links() }}
    </div>
</div>
@endsection
