{{-- Akun Siswa > Profil: data diri dari formulir pendaftaran (hanya dilihat) --}}
@extends('layouts.siswa')

@section('judul', 'Profil Saya')

@section('content')
@php
$s = $siswa;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div class="space-y-5">
    {{-- ===== Kartu identitas ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row items-center sm:items-end gap-5 text-center sm:text-left">
            @if ($s->foto_url)
                <img src="{{ $s->foto_url }}" alt="Foto {{ $s->nama_lengkap }}" class="w-28 h-36 rounded-3xl object-cover ring-4 ring-white/15 shadow-2xl">
            @else
                <span class="w-28 h-36 rounded-3xl bg-gradient-to-br from-emerald-400 to-teal-600 ring-4 ring-white/15 text-4xl font-extrabold flex items-center justify-center">{{ mb_strtoupper(mb_substr($s->nama_lengkap, 0, 1)) }}</span>
            @endif
            <div class="flex-1 min-w-0">
                <span class="inline-flex rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-[11px] font-semibold text-white/80">No. Pendaftaran {{ $s->nomor_pendaftaran }}</span>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $s->nama_lengkap }}</h2>
                @if ($s->nama_lengkap_arab)
                    <p class="mt-0.5 text-xl text-white/80" dir="rtl">{{ $s->nama_lengkap_arab }}</p>
                @endif
                <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">{{ $s->label_status }}</span>
                    @if ($s->kelasMaster)<span class="rounded-full bg-emerald-300/15 ring-1 ring-emerald-200/25 px-3 py-1.5 text-emerald-100">Kelas: {{ $s->kelasMaster->nama }}</span>@endif
                    @if ($s->kamarMaster)<span class="rounded-full bg-amber-300/15 ring-1 ring-amber-200/25 px-3 py-1.5 text-amber-100">Kamar: {{ $s->kamarMaster->nama }}{{ $s->nomor_kasur ? ' · Kasur ' . $s->nomor_kasur : '' }}</span>@endif
                </div>
                <p class="mt-3 text-xs text-white/60">Email login: {{ $s->email }}</p>
            </div>
            @if ($s->qr_svg)
            <div class="shrink-0 rounded-2xl bg-white p-2.5 text-center">
                <div class="w-24 h-24 [&_svg]:w-full [&_svg]:h-full">{!! $s->qr_svg !!}</div>
                <p class="mt-1 text-[10px] font-semibold text-slate-500">Kode absensi</p>
            </div>
            @endif
        </div>
    </section>

    <p class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 px-4 py-3 text-sm text-sky-900">
        Data ini sesuai formulir pendaftaran. Bila ada yang salah, kirim pesan lewat menu
        <a href="{{ route('siswa.pesan-saya') }}" class="font-semibold underline">Pesan untuk Admin</a>.
    </p>

    @include('partials.rincian-peserta', ['s' => $s, 'kartu' => $kartu])

    {{-- ===== Berkas ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h3 class="flex items-center gap-2.5 font-bold text-slate-800">
            <span class="w-2 h-6 rounded-full bg-gradient-to-b from-slate-400 to-slate-600"></span>Berkas yang Diunggah
        </h3>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($s::BERKAS as $kolom => $label)
                @if ($s->$kolom)
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($s->$kolom) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:ring-emerald-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    {{ is_array($label) ? ($label[0] ?? $kolom) : $label }}
                </a>
                @endif
            @endforeach
        </div>
    </section>
</div>
@endsection
