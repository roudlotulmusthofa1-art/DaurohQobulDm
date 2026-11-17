{{--
    Admin > Sistem > Aktivitas Pengguna (AktivitasPenggunaController@index)
    Semua aktivitas asatid & siswa: masuk/keluar + simpan/ubah/hapus (App\Models\AktivitasPengguna), terbaru dulu.
    Juga tampil di lonceng notifikasi (24 jam terakhir). Saringan Semua/Asatid/Siswa + cari nama/aksi, tanpa Vue.
--}}
@extends('layouts.admin')

@section('title', __('Aktivitas Pengguna'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$tab = fn ($kunci) => route('admin.aktivitas.index', array_filter(['peran' => $kunci, 'cari' => $cari]));
$warnaPeran = ['asatid' => 'bg-violet-50 text-violet-700 ring-violet-100', 'siswa' => 'bg-sky-50 text-sky-700 ring-sky-100'];
$tanggalSebelum = null;
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10">
    <x-header-admin :label="__('Sistem')" :judul="__('Aktivitas Pengguna')"
        :keterangan="__('Semua yang dilakukan asatid & siswa: masuk, keluar, dan setiap simpan, ubah, atau hapus data. Aktivitas 24 jam terakhir juga muncul di lonceng notifikasi.')" />

    {{-- Saringan peran --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach (['' => __('Semua'), 'asatid' => __('Asatid'), 'siswa' => __('Siswa')] as $kunci => $nama)
        <a href="{{ $tab($kunci) }}"
            class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors {{ $peran === $kunci ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">
            {{ $nama }} <span class="rounded-full px-2 text-[11px] {{ $peran === $kunci ? 'bg-white/20' : 'bg-slate-100' }}">{{ number_format($jumlah[$kunci]) }}</span>
        </a>
        @endforeach
    </div>

    {{-- Cari nama / aktivitas --}}
    <form method="GET" class="flex gap-2">
        @if ($peran)<input type="hidden" name="peran" value="{{ $peran }}">@endif
        <input type="search" name="cari" value="{{ $cari }}" placeholder="{{ __('Cari nama atau aktivitas…') }}"
            class="flex-1 sm:max-w-sm rounded-xl bg-white ring-1 ring-slate-200 border-0 px-4 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none">
        <button class="shrink-0 px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Cari') }}</button>
    </form>

    <section class="{{ $kartu }} p-3 sm:p-5">
        @forelse ($daftar as $a)
        @php $waktu = $a->created_at?->timezone('Asia/Jakarta'); $tgl = $waktu?->toDateString(); @endphp
        @if ($tgl !== $tanggalSebelum)
        <p class="px-2 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
            {{ $waktu?->isToday() ? __('Hari ini') : ($waktu?->isYesterday() ? __('Kemarin') : $waktu?->locale('id')->translatedFormat('l, d F Y')) }}
        </p>
        @php $tanggalSebelum = $tgl; @endphp
        @endif
        <div class="flex items-start gap-3 rounded-2xl px-2 py-2.5 hover:bg-[#f8fbfa]">
            <span class="w-12 shrink-0 pt-0.5 text-xs font-bold tabular-nums text-slate-500">{{ $waktu?->format('H:i') }}</span>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-slate-700"><b class="font-semibold text-slate-900">{{ $a->nama ?? __('Pengguna') }}</b> {{ $a->aksi }}</p>
                <p class="text-[11px] text-slate-400">{{ collect([$a->sesi ? __('Sesi') . ' ' . $a->sesi : null, $a->ip])->filter()->implode(' · ') }}</p>
            </div>
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $warnaPeran[$a->peran] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ $a->peran === 'asatid' ? __('Asatid') : __('Siswa') }}</span>
        </div>
        @empty
        <div class="py-16 text-center">
            <p class="font-bold text-slate-700">{{ __('Belum ada aktivitas') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('Aktivitas asatid & siswa akan muncul di sini setelah mereka masuk atau menyimpan data.') }}</p>
        </div>
        @endforelse
    </section>

    @if ($daftar->hasPages())
    <div>{{ $daftar->links() }}</div>
    @endif
</div>
@endsection
