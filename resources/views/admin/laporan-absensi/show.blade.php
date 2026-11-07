{{-- Admin > Laporan > Laporan Absensi > detail satu absensi --}}
@extends('layouts.admin')

@section('title', __('Detail Absensi'))

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
$label = fn ($k) => ! $kegiatan && $k === 'alfa' ? __('Tidak Hadir') : (isset(Absensi::STATUS[$k][0]) ? __(Absensi::STATUS[$k][0]) : $k);
$info = $kegiatan
    ? [
        [__('Kegiatan'), $absensi->kegiatan?->nama ?? '-'],
        [__('Jam mulai'), substr($absensi->jam_mulai, 0, 5)],
        [__('Batas terlambat'), substr($absensi->batas_terlambat, 0, 5)],
        [__('Batas alfa'), substr($absensi->batas_alfa, 0, 5)],
        [__('Cara absen'), $absensi->metode === 'qr' ? __('Pindai QR') : __('Cari nama')],
        [__('Status'), $absensi->selesai_at ? __('Ditutup') . ' ' . $absensi->selesai_at->locale('id')->translatedFormat('d M Y H:i') : __('Masih berjalan')],
    ]
    : [
        [__('Kelas'), $absensi->kelas?->nama ?? '-'],
        // Absensi baru memakai kitab; absensi lama hanya punya pelajaran (bidang ilmu)
        $absensi->kitab ? [__('Kitab'), $absensi->kitab->nama] : [__('Pelajaran'), $absensi->pelajaran?->nama ?? '-'],
        [__('Jam ke'), $absensi->waktuPelajaran?->nama ?? '-'],
    ];
@endphp

<div id="halaman-detail-absensi">
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <a href="{{ route('admin.laporan-absensi.index') }}" @click="kembali" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-emerald-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        {{ __('Kembali') }}
    </a>

    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br {{ $kegiatan ? 'from-emerald-500 via-teal-600 to-[#0b332c]' : 'from-sky-500 via-blue-600 to-indigo-700' }} text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">{{ $kegiatan ? __('Absensi Kegiatan') : __('Absensi Kelas') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $absensi->judul }}</h1>
                <p class="mt-1 text-sm text-white/75">{{ $absensi->tanggal->locale('id')->translatedFormat('l, d F Y') }} · {{ $absensi->tanggal_hijriyah }}</p>
                <p class="mt-1 text-sm text-white/75">{{ __('Diabsen oleh') }} <b class="text-white">{{ $absensi->pengabsen?->name ?? '-' }}</b></p>
            </div>
            <div class="relative w-32 h-32 shrink-0 self-center">
                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3.2"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-dasharray="{{ $persen ?? 0 }} 100"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-extrabold">{{ $persen !== null ? $persen . '%' : '–' }}</span>
                    <span class="text-[10px] text-white/70">{{ __('kehadiran') }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <section class="{{ $kartu }} p-5">
                <h2 class="font-bold text-slate-800">{{ __('Keterangan') }}</h2>
                <dl class="mt-3 divide-y divide-slate-100 text-sm">
                    @foreach ($info as [$l, $v])
                    <div class="py-2.5 flex justify-between gap-3"><dt class="text-slate-500">{{ $l }}</dt><dd class="font-semibold text-slate-800 text-right">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section class="{{ $kartu }} p-5">
                <h2 class="font-bold text-slate-800">{{ __('Jumlah') }}</h2>
                <div class="mt-3 space-y-2.5">
                    @foreach ($hitung as $k => $n)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-2.5 h-2.5 rounded-full {{ $gaya[$k][1] }}"></span>
                        <span class="flex-1 text-slate-600">{{ $label($k) }}</span>
                        <span class="font-extrabold text-slate-800">{{ $n }}</span>
                    </div>
                    @endforeach
                </div>
            </section>
            <form method="POST" action="{{ route('admin.laporan-absensi.destroy', $absensi) }}" @submit="konfirmasi" data-konfirmasi="{{ __('Hapus absensi ini beserta seluruh data kehadirannya?') }}">
                @csrf @method('DELETE')
                <button class="w-full py-3 rounded-2xl bg-white ring-1 ring-rose-200 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Hapus absensi ini') }}</button>
            </form>
        </div>

        <section class="lg:col-span-2 {{ $kartu }} overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800">{{ __('Daftar peserta') }} <span class="text-slate-400 font-semibold">({{ $detail->count() }})</span></h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($detail as $d)
                <div class="px-5 py-3.5 flex items-center gap-3">
                    @if ($d->siswa?->foto_url)
                        <img data-perbesar="{{ $d->siswa->nama_lengkap }}" src="{{ $d->siswa->foto_url }}" alt="" class="w-11 h-11 rounded-2xl object-cover ring-1 ring-slate-200">
                    @else
                        <span class="w-11 h-11 shrink-0 rounded-2xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($d->siswa?->nama_lengkap ?? '?', 0, 1)) }}</span>
                    @endif
                    <div class="flex-1 min-w-0">
                        @if ($d->siswa)
                            <a href="{{ route('admin.pendaftaran.show', $d->siswa) }}" class="block text-sm font-semibold text-slate-800 truncate hover:text-emerald-700">{{ $d->siswa->nama_lengkap }}</a>
                        @else
                            <p class="text-sm font-semibold text-slate-400">{{ __('Peserta dihapus') }}</p>
                        @endif
                        <p class="text-xs text-slate-400 truncate">{{ $d->siswa?->nomor_pendaftaran }}{{ $d->siswa?->kelasMaster ? ' · ' . $d->siswa->kelasMaster->nama : '' }}{{ $kegiatan && $d->waktu ? ' · ' . __('datang') . ' ' . substr($d->waktu, 0, 5) : '' }}</p>
                        @if ($d->catatan)
                            <p class="mt-1 inline-block text-xs text-slate-600 bg-amber-50 ring-1 ring-amber-100 rounded-lg px-2 py-1">{{ __('Catatan mudarris:') }} {{ $d->catatan }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-[11px] font-bold rounded-full px-2.5 py-1 ring-1 {{ $gaya[$d->status][0] ?? '' }}">{{ $label($d->status) }}</span>
                </div>
                @empty
                <p class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Belum ada peserta tercatat.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: konfirmasi hapus (metode dari partials/vue-metode.blade.php)
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-detail-absensi'));
</script>
@endpush
