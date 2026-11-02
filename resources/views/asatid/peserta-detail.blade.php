{{--
    Akun Asatid > Detail Peserta (hanya lihat, tidak ada tombol ubah).
    Data rahasia (email, password, catatan admin, berkas dokumen) sengaja tidak ditampilkan.
--}}
@extends('layouts.asatid')

@section('judul', 'Detail Peserta')
{{-- Panah kiri atas kembali ke halaman sebelumnya (mis. absensi kelas), bukan ke beranda --}}
@section('kembali', route('asatid.peserta'))

@section('content')
@php
use App\Models\Absensi;
$s = $siswa;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';


$totalAbsen = array_sum($absensi);
$persen = $totalAbsen ? round(($absensi['hadir'] + $absensi['terlambat']) / $totalAbsen * 100) : null;
$warnaAbsen = ['hadir' => 'bg-emerald-500', 'terlambat' => 'bg-amber-500', 'sakit' => 'bg-sky-500', 'izin' => 'bg-violet-500', 'alfa' => 'bg-rose-500'];
@endphp

<div id="halaman-detail-peserta">
<div class="space-y-5">
    <a href="{{ route('asatid.peserta') }}" @click="kembali"
        class="inline-flex items-center gap-2 rounded-2xl bg-white ring-1 ring-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:ring-emerald-200 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke halaman sebelumnya
    </a>

    {{-- ===== Profil ===== --}}
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
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-[11px] font-semibold text-white/80">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Hanya untuk dilihat
                </span>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $s->nama_lengkap }}</h2>
                @if ($s->nama_lengkap_arab)
                    <p class="mt-0.5 text-xl text-white/80" dir="rtl">{{ $s->nama_lengkap_arab }}</p>
                @endif
                <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-emerald-300/15 ring-1 ring-emerald-200/25 px-3 py-1.5 text-emerald-100">Kelas: {{ $s->kelasMaster?->nama ?? 'Belum ada' }}</span>
                    <span class="rounded-full bg-amber-300/15 ring-1 ring-amber-200/25 px-3 py-1.5 text-amber-100">Kamar: {{ $s->kamarMaster?->nama ?? 'Belum ada' }}{{ $s->nomor_kasur ? ' · Kasur ' . $s->nomor_kasur : '' }}</span>
                    @if ($s->pondok_terakhir)
                    <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5" dir="auto">{{ $s->pondok_terakhir }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Rekap kehadiran ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-bold text-slate-800">Rekap kehadiran</h3>
            <span class="text-sm font-extrabold {{ $persen === null ? 'text-slate-400' : ($persen >= 80 ? 'text-emerald-600' : ($persen >= 70 ? 'text-amber-600' : 'text-rose-600')) }}">
                {{ $persen !== null ? $persen . '% hadir' : 'Belum ada absensi' }}
            </span>
        </div>
        @if ($totalAbsen)
        <div class="mt-3 flex h-2.5 rounded-full overflow-hidden bg-slate-100">
            @foreach ($absensi as $k => $n)
                @if ($n)<div class="{{ $warnaAbsen[$k] }}" style="width: {{ $n / $totalAbsen * 100 }}%"></div>@endif
            @endforeach
        </div>
        @endif
        <div class="mt-4 grid grid-cols-5 gap-2">
            @foreach ($absensi as $k => $n)
            <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-2 py-2.5 text-center">
                <span class="block text-xl font-extrabold text-slate-800">{{ $n }}</span>
                <span class="flex items-center justify-center gap-1 text-[10px] sm:text-[11px] text-slate-500"><span class="w-1.5 h-1.5 rounded-full {{ $warnaAbsen[$k] }}"></span>{{ Absensi::STATUS[$k][0] }}</span>
            </div>
            @endforeach
        </div>
    </section>

    @include('partials.rincian-peserta', ['s' => $s, 'kartu' => $kartu])
</div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: tombol kembali ke halaman sebelumnya (metode dari partials/vue-metode.blade.php)
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-detail-peserta'));
</script>
@endpush
