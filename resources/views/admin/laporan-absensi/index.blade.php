{{-- Admin > Laporan > Laporan Absensi --}}
@extends('layouts.admin')

@section('title', __('Laporan Absensi'))

@section('content')
@php
use App\Models\Absensi;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$gaya = [
    'hadir'     => ['bg-emerald-500', 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'text-emerald-600'],
    'terlambat' => ['bg-amber-500', 'bg-amber-50 text-amber-700 ring-amber-200', 'text-amber-600'],
    'sakit'     => ['bg-sky-500', 'bg-sky-50 text-sky-700 ring-sky-200', 'text-sky-600'],
    'izin'      => ['bg-violet-500', 'bg-violet-50 text-violet-700 ring-violet-200', 'text-violet-600'],
    'alfa'      => ['bg-rose-500', 'bg-rose-50 text-rose-700 ring-rose-200', 'text-rose-600'],
];
$warnaPersen = fn ($p) => match (true) {
    $p === null => ['bg-slate-300', 'text-slate-400'],
    $p >= 90 => ['bg-emerald-500', 'text-emerald-600'],
    $p >= 80 => ['bg-teal-500', 'text-teal-600'],
    $p >= 70 => ['bg-amber-500', 'text-amber-600'],
    default => ['bg-rose-500', 'text-rose-600'],
};
$titikKesimpulan = ['emerald' => 'bg-emerald-500', 'teal' => 'bg-teal-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500', 'slate' => 'bg-slate-400'];
$periode = \Illuminate\Support\Carbon::parse($f['dari'])->locale('id')->translatedFormat('d M Y') . ' – ' . \Illuminate\Support\Carbon::parse($f['sampai'])->locale('id')->translatedFormat('d M Y');
$cepat = [
    __('Hari ini') => [now()->toDateString(), now()->toDateString()],
    __('7 hari') => [now()->subDays(6)->toDateString(), now()->toDateString()],
    __('Bulan ini') => [now()->startOfMonth()->toDateString(), now()->toDateString()],
    __('Bulan lalu') => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
];
$saringLain = array_filter(['jenis' => $f['jenis'], 'kelas_id' => $f['kelas_id'], 'jenis_kegiatan_id' => $f['jenis_kegiatan_id']]);
$labelStatus = fn ($k) => __(Absensi::STATUS[$k][0]);
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    @if (session('success'))
    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        {{ session('success') }}
    </div>
    @endif

    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Laporan') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Laporan Absensi') }}</h1>
                <p class="mt-1.5 text-sm text-white/70">{{ __('Rekap kehadiran peserta dari absensi kelas dan kegiatan yang diisi asatid.') }}</p>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5 text-xs font-semibold">
                    <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    {{ $periode }}
                </p>
                <div class="mt-5 grid grid-cols-3 gap-2 max-w-md">
                    @foreach ([[__('Total absensi'), $jumlahSesi], [__('Absensi kelas'), $jumlahSesiKelas], [__('Absensi kegiatan'), $jumlahSesiKegiatan]] as [$l, $n])
                    <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-3 py-2.5">
                        <span class="block text-2xl font-extrabold">{{ $n }}</span>
                        <span class="block text-[11px] text-white/60">{{ $l }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="relative w-40 h-40 shrink-0 self-center">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="url(#gradHadir)" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $persen ?? 0 }} 100"/>
                    <defs><linearGradient id="gradHadir" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6ee7b7"/><stop offset="100%" stop-color="#fcd34d"/></linearGradient></defs>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-extrabold">{{ $persen !== null ? $persen . '%' : '–' }}</span>
                    <span class="text-[11px] text-white/60">{{ __('kehadiran') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Saringan ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Dari') }}</span>
                <input type="date" name="dari" value="{{ $f['dari'] }}" class="mt-1 {{ $input }}"></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Sampai') }}</span>
                <input type="date" name="sampai" value="{{ $f['sampai'] }}" class="mt-1 {{ $input }}"></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Jenis') }}</span>
                <select name="jenis" class="mt-1 {{ $input }}">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="kelas" @selected($f['jenis'] === 'kelas')>{{ __('Absensi kelas') }}</option>
                    <option value="kegiatan" @selected($f['jenis'] === 'kegiatan')>{{ __('Absensi kegiatan') }}</option>
                </select></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kelas') }}</span>
                <select name="kelas_id" class="mt-1 {{ $input }}">
                    <option value="">{{ __('Semua kelas') }}</option>
                    @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($f['kelas_id'] == $k->id)>{{ $k->nama }}</option>@endforeach
                </select></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kegiatan') }}</span>
                <select name="jenis_kegiatan_id" class="mt-1 {{ $input }}">
                    <option value="">{{ __('Semua kegiatan') }}</option>
                    @foreach ($kegiatanList as $k)<option value="{{ $k->id }}" @selected($f['jenis_kegiatan_id'] == $k->id)>{{ $k->nama }}</option>@endforeach
                </select></label>
            <div class="flex gap-2">
                <button class="flex-1 px-4 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Terapkan') }}</button>
                <a href="{{ route('admin.laporan-absensi.index') }}" title="{{ __('Atur ulang') }}" class="px-3 py-2.5 rounded-xl bg-white ring-1 ring-slate-200 text-slate-500 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </a>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($cepat as $l => [$d, $s])
            @php $aktif = $f['dari'] === $d && $f['sampai'] === $s; @endphp
            <a href="{{ route('admin.laporan-absensi.index', ['dari' => $d, 'sampai' => $s] + $saringLain) }}"
                class="px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition-colors {{ $aktif ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">{{ $l }}</a>
            @endforeach
        </div>
    </form>

    @if ($total === 0)
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-3xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg>
        </div>
        <p class="mt-4 font-bold text-slate-700">{{ __('Belum ada data absensi pada periode ini') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Data muncul setelah asatid mengisi absensi kelas atau kegiatan dari dasbor asatid.') }}</p>
    </div>
    @else

    {{-- ===== Jumlah per keterangan ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-slate-800">{{ __('Rekap keterangan') }}</h2>
            <span class="text-xs text-slate-400">{{ __(':jumlah catatan kehadiran', ['jumlah' => number_format($total)]) }}</span>
        </div>
        <div class="mt-4 flex h-3.5 rounded-full overflow-hidden bg-slate-100">
            @foreach ($hitung as $k => $n)
                @if ($n)<div class="{{ $gaya[$k][0] }}" style="width: {{ $n / $total * 100 }}%" title="{{ $labelStatus($k) }}: {{ $n }}"></div>@endif
            @endforeach
        </div>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-3">
            @foreach ($hitung as $k => $n)
            <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3.5">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-500"><span class="w-2 h-2 rounded-full {{ $gaya[$k][0] }}"></span>{{ $labelStatus($k) }}</span>
                <span class="mt-1 block text-2xl font-extrabold text-slate-800">{{ number_format($n) }}</span>
                <span class="text-[11px] font-semibold {{ $gaya[$k][2] }}">{{ round($n / $total * 100, 1) }}%</span>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Kesimpulan ===== --}}
    <section class="relative overflow-hidden {{ $kartu }} p-5 sm:p-6">
        <div class="pointer-events-none absolute -top-16 -right-16 w-48 h-48 rounded-full bg-amber-300/20 blur-3xl"></div>
        <h2 class="relative flex items-center gap-2 font-bold text-slate-800">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
            </span>
            {{ __('Kesimpulan') }}
        </h2>
        <ul class="relative mt-4 space-y-2.5">
            @foreach ($kesimpulan as [$teks, $warna])
            <li class="flex items-start gap-3 text-sm text-slate-600 [&_b]:text-slate-900">
                <span class="mt-1.5 w-2 h-2 shrink-0 rounded-full {{ $titikKesimpulan[$warna] ?? 'bg-slate-400' }}"></span>
                {{-- teks dibuat di LaporanAbsensiController, nama sudah di-escape --}}
                <span>{!! $teks !!}</span>
            </li>
            @endforeach
        </ul>
    </section>

    {{-- ===== Tren harian + perlu perhatian ===== --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- max-lg:overflow-x-clip: keterangan batang di tepi kanan tidak membuat halaman HP bergeser ke samping --}}
        <section class="lg:col-span-2 {{ $kartu }} p-5 sm:p-6 max-lg:overflow-x-clip">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Kehadiran per hari') }}</h2>
                <span class="text-xs text-slate-400">{{ __('% hadir + terlambat') }}</span>
            </div>
            <div class="mt-6 h-48 flex items-end gap-1.5 sm:gap-2 border-b border-slate-100">
                @foreach ($harian as $h)
                @php [$batang] = $warnaPersen($h['persen']); @endphp
                <div class="group relative flex-1 min-w-0 h-full flex flex-col justify-end items-center">
                    <span class="pointer-events-none absolute -top-7 z-10 whitespace-nowrap rounded-lg bg-slate-800 text-white text-[10px] font-semibold px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        {{ $h['tanggal']->locale('id')->translatedFormat('d M') }} · {{ $h['persen'] }}% · {{ __(':jumlah catatan', ['jumlah' => $h['total']]) }}
                    </span>
                    <div class="w-full max-w-[28px] rounded-t-lg {{ $batang }} opacity-85 group-hover:opacity-100 transition-all" style="height: {{ max($h['persen'], 3) }}%"></div>
                </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-1.5 sm:gap-2">
                @foreach ($harian as $h)
                <span class="flex-1 min-w-0 text-center text-[10px] text-slate-400 truncate">{{ $h['tanggal']->format('d/m') }}</span>
                @endforeach
            </div>
        </section>

        <section class="{{ $kartu }} p-5 sm:p-6">
            <h2 class="font-bold text-slate-800">{{ __('Perlu perhatian') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Kehadiran < 75% atau alfa ≥ 3 kali') }}</p>
            <div class="mt-4 space-y-2.5">
                @forelse ($perhatian as $p)
                <a href="{{ route('admin.pendaftaran.show', $p['id']) }}" class="flex items-center gap-3 p-2.5 -mx-1 rounded-2xl hover:bg-[#f5f9f7] transition-colors">
                    @if ($p['foto'])
                        <img data-perbesar="{{ $p['nama'] ?? '' }}" src="{{ $p['foto'] }}" alt="" class="w-10 h-10 rounded-xl object-cover ring-1 ring-slate-200">
                    @else
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p['nama'], 0, 1)) }}</span>
                    @endif
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-semibold text-slate-800 truncate">{{ $p['nama'] }}</span>
                        <span class="block text-[11px] text-slate-400">{{ $p['kelas'] ?? __('Tanpa kelas') }} · {{ __('alfa :jumlah×', ['jumlah' => $p['alfa']]) }}</span>
                    </span>
                    <span class="shrink-0 text-sm font-extrabold {{ $warnaPersen($p['persen'])[1] }}">{{ $p['persen'] }}%</span>
                </a>
                @empty
                <div class="py-8 text-center">
                    <span class="mx-auto w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 ring-1 ring-emerald-100 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Semua peserta kehadirannya baik.') }}</p>
                </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ===== Rekap per kelas & kegiatan ===== --}}
    <div class="grid lg:grid-cols-2 gap-6">
        @foreach ([[__('Rekap per kelas'), $perKelas, __('Belum ada absensi kelas.')], [__('Rekap per kegiatan'), $perKegiatan, __('Belum ada absensi kegiatan.')]] as [$judul, $rekap, $kosong])
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h2 class="font-bold text-slate-800">{{ $judul }}</h2>
            <div class="mt-4 space-y-4">
                @forelse ($rekap as $r)
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-800 truncate">{{ $r['nama'] }}</span>
                            <span class="block text-[11px] text-slate-400">{{ __(':jumlah kali absen', ['jumlah' => $r['sesi']]) }} · {{ __('H') }} {{ $r['hadir'] }} · {{ __('T') }} {{ $r['terlambat'] }} · {{ __('S') }} {{ $r['sakit'] }} · {{ __('I') }} {{ $r['izin'] }} · {{ __('A') }} {{ $r['alfa'] }}</span>
                        </span>
                        <span class="shrink-0 text-sm font-extrabold {{ $warnaPersen($r['persen'])[1] }}">{{ $r['persen'] }}%</span>
                    </div>
                    <div class="mt-2 flex h-2 rounded-full overflow-hidden bg-slate-100">
                        @foreach (array_keys(Absensi::STATUS) as $k)
                            @if ($r[$k])<div class="{{ $gaya[$k][0] }}" style="width: {{ $r[$k] / $r['total'] * 100 }}%"></div>@endif
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="py-6 text-center text-sm text-slate-400">{{ $kosong }}</p>
                @endforelse
            </div>
        </section>
        @endforeach
    </div>

    {{-- ===== Rekap per peserta ===== --}}
    <section class="{{ $kartu }} overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <h2 class="font-bold text-slate-800">{{ __('Rekap per peserta') }}</h2>
                <p class="text-xs text-slate-400">{{ __(':jumlah peserta', ['jumlah' => $jumlahPeserta]) }} · {{ __('urut dari kehadiran terendah') }}</p>
            </div>
            <form method="GET" class="flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 pl-3 pr-1 py-1 sm:w-72">
                @foreach (array_filter(['dari' => $f['dari'], 'sampai' => $f['sampai']] + $saringLain) as $n => $v)<input type="hidden" name="{{ $n }}" value="{{ $v }}">@endforeach
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="search" name="q" value="{{ $f['q'] }}" placeholder="{{ __('Cari peserta…') }}" class="flex-1 min-w-0 bg-transparent border-0 p-1.5 text-sm outline-none focus:ring-0">
            </form>
        </div>
        {{-- ===== HP (di bawah lg): satu peserta = satu kartu ===== --}}
        <div class="lg:hidden px-3 pb-3 space-y-2.5">
            @forelse ($tabelPeserta as $p)
            @php [$batang, $teks] = $warnaPersen($p['persen']); @endphp
            <article class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-[0_1px_2px_rgba(16,24,40,0.04)]">
                <a href="{{ route('admin.pendaftaran.show', $p['id']) }}" class="flex items-center gap-3">
                    @if ($p['foto'])
                        <img src="{{ $p['foto'] }}" alt="" class="w-11 h-11 shrink-0 rounded-xl object-cover ring-1 ring-slate-200">
                    @else
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p['nama'], 0, 1)) }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block font-bold text-slate-800 truncate">{{ $p['nama'] }}</span>
                        <span class="block text-[11px] text-slate-400 truncate">{{ $p['nomor'] }}{{ $p['kelas'] ? ' · ' . $p['kelas'] : '' }}</span>
                    </span>
                    <span class="shrink-0 text-sm font-extrabold {{ $teks }}">{{ $p['persen'] }}%</span>
                </a>
                {{-- Jumlah tiap keterangan, warnanya sama dengan rekap di atas --}}
                <div class="mt-3 grid grid-cols-5 gap-1.5">
                    @foreach (Absensi::STATUS as $k => [$l, $singkat])
                    <span class="rounded-lg ring-1 py-1 text-center {{ $p[$k] ? $gaya[$k][1] : 'bg-slate-50 text-slate-300 ring-slate-100' }}" title="{{ __($l) }}">
                        <span class="block text-[10px] font-semibold">{{ __($singkat) }}</span>
                        <span class="block text-sm font-extrabold">{{ $p[$k] }}</span>
                    </span>
                    @endforeach
                </div>
                <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full {{ $batang }}" style="width: {{ $p['persen'] }}%"></div></div>
            </article>
            @empty
            <p class="rounded-2xl bg-white py-10 text-center text-sm text-slate-400 ring-1 ring-slate-200/80">{{ __('Peserta tidak ditemukan.') }}</p>
            @endforelse
        </div>

        {{-- ===== Laptop (lg ke atas): tabel ===== --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#f8fbfa] text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">{{ __('Peserta') }}</th>
                        @foreach (Absensi::STATUS as $k => [$l, $p])
                        <th class="px-2 py-3 text-center font-semibold" title="{{ __($l) }}"><span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full {{ $gaya[$k][0] }}"></span>{{ __($p) }}</span></th>
                        @endforeach
                        <th class="px-5 py-3 text-left font-semibold w-48">{{ __('Kehadiran') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tabelPeserta as $p)
                    @php [$batang, $teks] = $warnaPersen($p['persen']); @endphp
                    <tr class="hover:bg-[#fafcfb]">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.pendaftaran.show', $p['id']) }}" class="flex items-center gap-3 min-w-[12rem]">
                                @if ($p['foto'])
                                    <img data-perbesar="{{ $p['nama'] ?? '' }}" src="{{ $p['foto'] }}" alt="" class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200">
                                @else
                                    <span class="w-9 h-9 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p['nama'], 0, 1)) }}</span>
                                @endif
                                <span class="min-w-0">
                                    <span class="block font-semibold text-slate-800 truncate hover:text-emerald-700">{{ $p['nama'] }}</span>
                                    <span class="block text-[11px] text-slate-400">{{ $p['nomor'] }}{{ $p['kelas'] ? ' · ' . $p['kelas'] : '' }}</span>
                                </span>
                            </a>
                        </td>
                        @foreach (array_keys(Absensi::STATUS) as $k)
                        <td class="px-2 py-3 text-center font-semibold {{ $p[$k] ? 'text-slate-700' : 'text-slate-300' }}">{{ $p[$k] }}</td>
                        @endforeach
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full {{ $batang }}" style="width: {{ $p['persen'] }}%"></div></div>
                                <span class="w-10 text-right text-xs font-extrabold {{ $teks }}">{{ $p['persen'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Peserta tidak ditemukan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tabelPeserta->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $tabelPeserta->links() }}</div>
        @endif
    </section>

    @endif

    {{-- ===== Riwayat absensi ===== --}}
    @if ($jumlahSesi > 0)
    <section class="{{ $kartu }} overflow-hidden">
        <div class="p-5 sm:p-6">
            <h2 class="font-bold text-slate-800">{{ __('Riwayat absensi') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Klik untuk melihat detail per peserta dan catatan mudarris') }}</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($daftarSesi as $s)
            @php
                $kegiatan = $s->jenis === Absensi::KEGIATAN;
                $p = $s->detail_count ? (int) round($s->hadir_count / $s->detail_count * 100) : null;
            @endphp
            <a href="{{ route('admin.laporan-absensi.show', $s) }}" class="px-5 sm:px-6 py-3.5 flex items-center gap-3 hover:bg-[#fafcfb] transition-colors">
                <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center ring-1 text-[10px] font-extrabold {{ $kegiatan ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-sky-50 text-sky-600 ring-sky-100' }}">{{ $kegiatan ? __('KGT') : __('KLS') }}</span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-semibold text-slate-800 truncate">{{ $s->judul }}</span>
                    <span class="block text-xs text-slate-400 truncate">
                        {{ $s->tanggal->locale('id')->translatedFormat('d M Y') }} · {{ $s->tanggal_hijriyah }} · {{ $s->keterangan_waktu }} · {{ __('oleh') }} {{ $s->pengabsen?->name ?? '-' }}
                    </span>
                </span>
                @if ($kegiatan && ! $s->selesai_at)
                    <span class="shrink-0 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-amber-50 text-amber-700 ring-1 ring-amber-200">{{ __('Berjalan') }}</span>
                @endif
                <span class="shrink-0 text-right">
                    <span class="block text-sm font-extrabold {{ $warnaPersen($p)[1] }}">{{ $p !== null ? $p . '%' : '–' }}</span>
                    <span class="block text-[11px] text-slate-400">{{ __(':jumlah peserta', ['jumlah' => $s->detail_count]) }}</span>
                </span>
            </a>
            @endforeach
        </div>
        @if ($daftarSesi->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $daftarSesi->links() }}</div>
        @endif
    </section>
    @endif
</div>
@endsection
