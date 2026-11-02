{{-- Akun Asatid > Absensi > hasil satu absensi --}}
@extends('layouts.asatid')

@section('judul', 'Hasil Absensi')

@section('content')
@php
use App\Models\Absensi;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$kegiatan = $absensi->jenis === Absensi::KEGIATAN;
$gaya = [
    'hadir'     => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
    'terlambat' => ['bg-amber-50 text-amber-700 ring-amber-200', 'bg-amber-500'],
    'sakit'     => ['bg-sky-50 text-sky-700 ring-sky-200', 'bg-sky-500'],
    'izin'      => ['bg-violet-50 text-violet-700 ring-violet-200', 'bg-violet-500'],
    'alfa'      => ['bg-rose-50 text-rose-700 ring-rose-200', 'bg-rose-500'],
];
$total = max($detail->count(), 1);
$persenHadir = round((($hitung['hadir'] + $hitung['terlambat']) / $total) * 100);
@endphp

<div class="space-y-5">
    @if (session('sukses'))
    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        {{ session('sukses') }}
    </div>
    @endif

    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br {{ $kegiatan ? 'from-emerald-500 via-teal-600 to-[#0b332c]' : 'from-sky-500 via-blue-600 to-indigo-700' }} text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">{{ $kegiatan ? 'Absensi Kegiatan' : 'Absensi Kelas' }}</p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $absensi->judul }}</h2>
                <p class="mt-1 text-sm text-white/75">
                    {{ $absensi->tanggal->locale('id')->translatedFormat('l, d F Y') }} · {{ $absensi->tanggal_hijriyah }}
                </p>
                <p class="mt-3 inline-flex flex-wrap gap-2 text-xs font-semibold">
                    @if ($kegiatan)
                        <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">Mulai {{ substr($absensi->jam_mulai, 0, 5) }}</span>
                        <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">Terlambat {{ substr($absensi->batas_terlambat, 0, 5) }}</span>
                        <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">Alfa {{ substr($absensi->batas_alfa, 0, 5) }}</span>
                    @else
                        <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">Jam ke: {{ $absensi->waktuPelajaran?->nama ?? '-' }}</span>
                    @endif
                </p>
            </div>
            {{-- Lingkaran persen kehadiran --}}
            <div class="relative w-32 h-32 shrink-0 self-center">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3.2"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round"
                        stroke-dasharray="{{ $persenHadir }} 100"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-extrabold">{{ $persenHadir }}%</span>
                    <span class="text-[10px] text-white/70">kehadiran</span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2.5">
        @foreach (Absensi::STATUS as $k => [$label])
        <div class="{{ $kartu }} p-3.5">
            <span class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-2 h-2 rounded-full {{ $gaya[$k][1] }}"></span>{{ $kegiatan || $k !== 'alfa' ? $label : 'Tidak Hadir' }}</span>
            <span class="mt-1 block text-2xl font-extrabold text-slate-800">{{ $hitung[$k] }}</span>
        </div>
        @endforeach
    </div>

    <section class="{{ $kartu }} overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Daftar peserta <span class="text-slate-400 font-semibold">({{ $detail->count() }})</span></h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($detail as $d)
            <div class="px-5 py-3.5 flex items-center gap-3">
                @if ($d->siswa?->foto_url)
                    <img src="{{ $d->siswa->foto_url }}" alt="" class="w-11 h-11 rounded-2xl object-cover ring-1 ring-slate-200">
                @else
                    <span class="w-11 h-11 shrink-0 rounded-2xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($d->siswa?->nama_lengkap ?? '?', 0, 1)) }}</span>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $d->siswa?->nama_lengkap ?? 'Peserta dihapus' }}</p>
                    @if ($d->catatan)
                        <p class="text-xs text-slate-500">“{{ $d->catatan }}”</p>
                    @else
                        <p class="text-xs text-slate-400 truncate">{{ $d->siswa?->nomor_pendaftaran }}{{ $kegiatan && $d->waktu ? ' · ' . substr($d->waktu, 0, 5) : '' }}</p>
                    @endif
                </div>
                <span class="shrink-0 text-[11px] font-bold rounded-full px-2.5 py-1 ring-1 {{ $gaya[$d->status][0] ?? '' }}">
                    {{ $kegiatan || $d->status !== 'alfa' ? (Absensi::STATUS[$d->status][0] ?? $d->status) : 'Tidak Hadir' }}
                </span>
            </div>
            @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">Belum ada peserta tercatat.</p>
            @endforelse
        </div>
    </section>

    <div class="flex flex-col sm:flex-row gap-2">
        <a href="{{ route('asatid.absensi') }}" class="flex-1 text-center py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">Kembali ke Absensi</a>
        @unless ($kegiatan)
        <a href="{{ route('asatid.absensi.kelas', ['kelas_id' => $absensi->kelas_id, 'bidang_ilmu_id' => $absensi->bidang_ilmu_id, 'waktu_pelajaran_id' => $absensi->waktu_pelajaran_id, 'tanggal' => $absensi->tanggal->toDateString()]) }}"
            class="flex-1 text-center py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ubah Absensi</a>
        @endunless
        <form method="POST" action="{{ route('asatid.absensi.destroy', $absensi) }}" onsubmit="return confirm('Hapus absensi ini? Data kehadirannya ikut terhapus.')" class="sm:w-auto">
            @csrf @method('DELETE')
            <button class="w-full px-5 py-3 rounded-2xl bg-white ring-1 ring-rose-200 text-sm font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
        </form>
    </div>
</div>
@endsection
