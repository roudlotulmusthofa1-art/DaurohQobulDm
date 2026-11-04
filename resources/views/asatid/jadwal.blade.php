{{-- Akun Asatid > Jadwal Pelajaran: jadwal mengajar milik asatid ini (dari Akademik > Jadwal Mengajar, bulan aktif) --}}
@extends('layouts.asatid')

@section('judul', __('Jadwal Pelajaran'))

@section('content')
@php
use App\Models\Absensi;
use App\Models\JadwalMengajar;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$hariIni = now('Asia/Jakarta')->dayOfWeekIso;
$urutJam = $jamList->pluck('urutan', 'id');
$perHari = $jadwal->sortBy(fn ($j) => $urutJam[$j->waktu_pelajaran_id] ?? 99)->groupBy('hari');
// Hari ini ditampilkan paling atas, lalu hari-hari berikutnya
$urutanHari = collect(range(0, 6))->map(fn ($i) => (($hariIni - 1 + $i) % 7) + 1);
@endphp

<div class="grid lg:grid-cols-5 gap-6 items-start">
    <div class="lg:col-span-3 space-y-4">
        @if ($jadwal->isEmpty())
            @include('asatid.partials.segera-hadir', [
                'judul' => __('Jadwal mengajar Anda belum diatur'),
                'teks'  => __('Admin belum memasukkan Anda ke Jadwal Mengajar bulan ini. Setelah diatur, kelas dan kitab yang Anda ampu akan tampil di sini.'),
                'warna' => 'from-amber-400 to-orange-500',
                'ikon'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
                'poin'  => [__('Hari dan jam mengajar'), __('Kelas dan kitab yang diampu'), __('Pengingat jadwal hari ini')],
            ])
        @else
            @foreach ($urutanHari as $h)
            @php $daftar = $perHari[$h] ?? collect(); $hariIniKah = $h === $hariIni; @endphp
            @if ($daftar->isNotEmpty() || $hariIniKah)
            <section class="{{ $kartu }} overflow-hidden {{ $hariIniKah ? 'ring-2 ring-amber-400' : '' }}">
                <div class="flex items-center justify-between px-5 py-3 {{ $hariIniKah ? 'bg-gradient-to-r from-amber-400 to-orange-500 text-white' : 'bg-[#f8fbfa]' }}">
                    <h2 class="font-extrabold">{{ __(JadwalMengajar::HARI[$h]) }}</h2>
                    <span class="text-xs font-semibold {{ $hariIniKah ? 'text-white/90' : 'text-slate-400' }}">{{ $hariIniKah ? __('Hari ini') . ' · ' : '' }}{{ __(':jumlah jam', ['jumlah' => $daftar->count()]) }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($daftar as $j)
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <div class="w-20 shrink-0 text-center">
                            <p class="text-xs font-bold text-slate-700 leading-tight" dir="auto">{{ $j->waktu?->nama }}</p>
                            @if ($j->waktu?->jam_mulai)<p class="text-[11px] text-slate-400 tabular-nums">{{ $j->waktu->jam_mulai }}–{{ $j->waktu->jam_selesai }}</p>@endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 truncate">{{ $j->kitab?->nama ?? __('Kitab belum ditentukan') }}</p>
                            <p class="text-xs text-slate-500">{{ $j->kitab?->bidangIlmu?->nama }}{{ $j->catatan ? ' · ' . $j->catatan : '' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">{{ $j->kelas?->nama }}</span>
                        @if ($hariIniKah)
                        <a href="{{ route('asatid.absensi.kelas', array_filter(['kelas_id' => $j->kelas_id, 'bidang_ilmu_id' => $j->kitab?->bidang_ilmu_id, 'waktu_pelajaran_id' => $j->waktu_pelajaran_id])) }}"
                            class="shrink-0 rounded-xl bg-[#0b332c] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#135347]">{{ __('Absen') }}</a>
                        @endif
                    </div>
                    @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">{{ __('Tidak ada jadwal mengajar hari ini.') }}</p>
                    @endforelse
                </div>
            </section>
            @endif
            @endforeach
        @endif
    </div>

    <div class="lg:col-span-2 space-y-6">
        @if ($bulan)
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-400 to-orange-500 text-white p-5 sm:p-6">
            <div class="pointer-events-none absolute -right-8 -bottom-10 w-36 h-36 rounded-full bg-white/15 blur-xl"></div>
            <p class="relative text-xs font-semibold text-white/80">{{ __('Bulan ajaran aktif') }}</p>
            <p class="relative mt-1 text-xl font-extrabold">{{ __('Bulan ke-:urutan', ['urutan' => $bulan->urutan]) }} · {{ $bulan->nama }}</p>
            <p class="relative text-sm text-white/85">{{ __('TA') }} {{ $bulan->tahunAjaran?->nama }}</p>
            @if ($bulan->tanggal_mulai)
            <p class="relative mt-3 text-xs text-white/85">{{ $bulan->periode }}</p>
            <p class="relative text-xs text-white/70">{{ Absensi::hijriyah($bulan->tanggal_mulai->toDateString()) }} – {{ Absensi::hijriyah($bulan->tanggal_selesai->toDateString()) }}</p>
            @endif
            <p class="relative mt-4 inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-bold">{{ __(':jumlah jam mengajar / minggu', ['jumlah' => $jadwal->count()]) }}</p>
        </div>
        @endif

        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h2 class="text-[15px] font-bold text-slate-800">{{ __('Jam Pelajaran') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Pembagian jam yang berlaku') }}</p>
                </div>
            </div>

            @forelse ($jamList as $j)
            <div class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                <span class="w-8 h-8 shrink-0 rounded-xl bg-[#f5f9f7] text-slate-500 text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                <span class="flex-1 min-w-0 text-sm font-semibold text-slate-700 truncate" dir="auto">{{ $j->nama }}</span>
                <span class="shrink-0 text-xs font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-2.5 py-1 tabular-nums">
                    {{ $j->jam_mulai }} – {{ $j->jam_selesai }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-400">{{ __('Jam pelajaran belum diatur di Master Data.') }}</p>
            @endforelse
        </section>
    </div>
</div>
@endsection
