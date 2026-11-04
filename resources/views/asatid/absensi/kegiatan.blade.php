{{-- Akun Asatid > Absensi > Absensi Kegiatan (langkah 1: atur kegiatan & waktu) --}}
@extends('layouts.asatid')

@section('judul', __('Absensi Kegiatan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$warnaHex = [
    'bg-slate-100' => '#94a3b8', 'bg-red-100' => '#f87171', 'bg-orange-100' => '#fb923c', 'bg-amber-100' => '#fbbf24',
    'bg-yellow-100' => '#facc15', 'bg-lime-100' => '#a3e635', 'bg-green-100' => '#4ade80', 'bg-emerald-100' => '#34d399',
    'bg-teal-100' => '#2dd4bf', 'bg-cyan-100' => '#22d3ee', 'bg-sky-100' => '#38bdf8', 'bg-blue-100' => '#60a5fa',
    'bg-indigo-100' => '#818cf8', 'bg-violet-100' => '#a78bfa', 'bg-purple-100' => '#c084fc', 'bg-pink-100' => '#f472b6', 'bg-rose-100' => '#fb7185',
];
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$nomor = 'w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center text-xs font-extrabold';
@endphp

<form method="POST" action="{{ route('asatid.absensi.kegiatan.mulai') }}" class="space-y-5" id="form-kegiatan">
    @csrf

    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 via-teal-600 to-[#0b332c] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-lime-300/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-100/80">{{ __('Langkah 1 dari 2') }}</p>
            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Atur absensi kegiatan') }}</h2>
            <p class="mt-1.5 text-sm text-white/75">{{ __('Pilih kegiatan dan atur jamnya. Status terlambat dan alfa dihitung otomatis dari jam kedatangan.') }}</p>
        </div>
    </section>

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700">
        <p class="font-semibold">{{ __('Periksa kembali isian Anda:') }}</p>
        <ul class="mt-1 list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- 1. Kegiatan --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span> {{ __('Nama kegiatan') }}</h3>
        @if ($kegiatanList->isEmpty())
            <p class="mt-4 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada jenis kegiatan aktif. Minta admin mengisinya di Master Data') }} &rsaquo; {{ __('Jenis Kegiatan.') }}</p>
        @else
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($kegiatanList as $k)
            <label class="relative cursor-pointer p-4 rounded-2xl ring-1 ring-slate-200/80 bg-[#f8fbfa] hover:bg-white hover:ring-emerald-300 transition-all
                has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500 has-[:checked]:shadow-[0_8px_24px_-12px_rgba(16,185,129,0.6)]">
                <input type="radio" name="jenis_kegiatan_id" value="{{ $k->id }}" class="peer sr-only" @checked(old('jenis_kegiatan_id') == $k->id) required>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-extrabold text-sm" style="background-color: {{ $warnaHex[$k->warna] ?? '#14b8a6' }}">
                    {{ mb_strtoupper(mb_substr($k->nama, 0, 1)) }}
                </span>
                <span class="mt-3 block font-bold text-slate-800 leading-tight">{{ $k->nama }}</span>
                @if ($k->deskripsi)
                <span class="mt-0.5 block text-[11px] text-slate-500 line-clamp-2">{{ $k->deskripsi }}</span>
                @endif
                <span class="absolute top-3 right-3 w-5 h-5 rounded-full bg-emerald-500 text-white items-center justify-center hidden peer-checked:flex">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
            </label>
            @endforeach
        </div>
        @endif
    </section>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- 2. Tanggal --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span> {{ __('Tanggal') }}</h3>
            <input type="date" name="tanggal" id="tanggal" v-model="tanggal" required class="mt-4 {{ $input }}">
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 px-3 py-2.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-sky-600">{{ __('Masehi') }}</span>
                    <span class="mt-0.5 block font-semibold text-slate-700" v-text="teksMasehi"></span>
                </div>
                <div class="rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 px-3 py-2.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600">{{ __('Hijriyah') }}</span>
                    <span class="mt-0.5 block font-semibold text-slate-700" v-text="teksHijriyah"></span>
                </div>
            </div>
        </section>

        {{-- 4. Metode --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">4</span> {{ __('Cara absen') }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @foreach ([
                    'qr' => [__('Pindai QR'), __('Pakai kamera HP untuk memindai kartu QR peserta'), 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z'],
                    'manual' => [__('Cari Nama'), __('Ketik nama peserta lalu pilih keterangannya'), 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z'],
                ] as $nilai => [$judul, $ket, $ikon])
                <label class="relative cursor-pointer p-4 rounded-2xl ring-1 ring-slate-200/80 bg-[#f8fbfa] hover:bg-white transition-all
                    has-[:checked]:bg-[#0b332c] has-[:checked]:ring-[#0b332c] has-[:checked]:text-white group">
                    <input type="radio" name="metode" value="{{ $nilai }}" class="sr-only" @checked(old('metode', 'qr') === $nilai)>
                    <svg class="w-7 h-7 text-emerald-600 group-has-[:checked]:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                    <span class="mt-2 block font-bold">{{ $judul }}</span>
                    <span class="mt-0.5 block text-[11px] opacity-70">{{ $ket }}</span>
                </label>
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-slate-400">{{ __('Bisa berganti cara kapan saja di halaman berikutnya.') }}</p>
        </section>
    </div>

    {{-- 3. Waktu --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">3</span> {{ __('Waktu kegiatan') }}</h3>
        <div class="mt-4 grid sm:grid-cols-3 gap-3">
            @foreach ([
                'jam_mulai' => [__('Waktu dimulai'), 'bg-emerald-500', __('Kegiatan dimulai')],
                'batas_terlambat' => [__('Dianggap terlambat'), 'bg-amber-500', __('Datang mulai jam ini = Terlambat')],
                'batas_alfa' => [__('Dianggap tidak hadir (alfa)'), 'bg-rose-500', __('Datang mulai jam ini = Alfa')],
            ] as $nama => [$label, $titik, $ket])
            <label class="block rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 p-4">
                <span class="flex items-center gap-2 text-xs font-bold text-slate-600"><span class="w-2.5 h-2.5 rounded-full {{ $titik }}"></span>{{ $label }}</span>
                <input type="time" name="{{ $nama }}" id="{{ $nama }}" v-model="jam.{{ $nama }}" required
                    class="mt-2 w-full bg-transparent border-0 p-0 text-2xl font-extrabold text-slate-800 outline-none">
                <span class="mt-1 block text-[11px] text-slate-400">{{ $ket }}</span>
            </label>
            @endforeach
        </div>

        {{-- Garis waktu --}}
        <div class="mt-5">
            <div class="flex h-3 rounded-full overflow-hidden">
                <div class="flex-[2] bg-gradient-to-r from-emerald-400 to-emerald-500"></div>
                <div class="flex-[2] bg-gradient-to-r from-amber-400 to-amber-500"></div>
                <div class="flex-1 bg-gradient-to-r from-rose-400 to-rose-500"></div>
            </div>
            <div class="mt-2 grid grid-cols-5 text-[11px] font-semibold">
                <span class="col-span-2 text-emerald-700">{{ __('Hadir') }} <span class="text-slate-400 font-normal" v-text="jam.batas_terlambat ? '(' + @js(__('sebelum')) + ' ' + jam.batas_terlambat + ')' : ''"></span></span>
                <span class="col-span-2 text-amber-700">{{ __('Terlambat') }} <span class="text-slate-400 font-normal" v-text="jam.batas_terlambat && jam.batas_alfa ? '(' + jam.batas_terlambat + '–' + jam.batas_alfa + ')' : ''"></span></span>
                <span class="text-rose-700">{{ __('Alfa') }} <span class="text-slate-400 font-normal" v-text="jam.batas_alfa ? '(' + @js(__('mulai')) + ' ' + jam.batas_alfa + ')' : ''"></span></span>
            </div>
        </div>
    </section>

    <div class="sticky bottom-4 z-10">
        <button type="submit" @disabled($kegiatanList->isEmpty())
            class="w-full flex items-center justify-center gap-2 py-4 rounded-2xl bg-[#0b332c] text-white text-base font-extrabold hover:bg-[#135347] shadow-[0_16px_32px_-12px_rgba(11,51,44,0.6)] disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none">
            {{ __('Mulai Absensi Kegiatan') }}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dua = (n) => String(n).padStart(2, '0');
        const sekarang = new Date();
        // Jam sekarang + menit, dibulatkan ke 5 menit (jam HP/komputer guru)
        const tambahMenit = (menit) => {
            const d = new Date(sekarang.getTime() + menit * 60000);
            return dua(d.getHours()) + ':' + dua(Math.floor(d.getMinutes() / 5) * 5);
        };
        const FMT_MASEHI = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        const FMT_HIJRIYAH = new Intl.DateTimeFormat('id-u-ca-islamic-umalqura', { day: 'numeric', month: 'long', year: 'numeric' });

        Vue.createApp({
            data() {
                // Isian kosong diisi otomatis memakai tanggal & jam di HP ini (isian lama dipakai bila gagal validasi)
                return {
                    tanggal: @json(old('tanggal')) || `${sekarang.getFullYear()}-${dua(sekarang.getMonth() + 1)}-${dua(sekarang.getDate())}`,
                    jam: {
                        jam_mulai: @json(old('jam_mulai')) || tambahMenit(0),
                        batas_terlambat: @json(old('batas_terlambat')) || tambahMenit(15),
                        batas_alfa: @json(old('batas_alfa')) || tambahMenit(60),
                    },
                };
            },
            computed: {
                tanggalDate() {
                    return this.tanggal ? new Date(this.tanggal + 'T12:00:00') : null;
                },
                teksMasehi() {
                    return this.tanggalDate ? FMT_MASEHI.format(this.tanggalDate) : '-';
                },
                teksHijriyah() {
                    return this.tanggalDate ? FMT_HIJRIYAH.format(this.tanggalDate) : '-';
                },
            },
        }).mount('#form-kegiatan');
    });
</script>
@endpush
