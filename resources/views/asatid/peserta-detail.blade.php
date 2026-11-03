{{--
    Akun Asatid > Detail Peserta (hanya lihat).
    Tampilan data SAMA dengan profil di dasbor siswa (partials/detail-santri, mode tanpa tombol admin),
    ditambah rekap kehadiran peserta di bawahnya. Mode "rahasia": email, catatan admin & berkas tidak ditampilkan.
--}}
@extends('layouts.asatid')

@section('judul', 'Detail Peserta')
{{-- Panah kiri atas kembali ke halaman sebelumnya (mis. absensi kelas), bukan ke beranda --}}
@section('kembali', route('asatid.peserta'))

@section('content')
@php
use App\Models\Absensi;
$p = $siswa; // nama variabel yang dipakai partials/detail-santri
$kartuAbsen = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$totalAbsen = array_sum($absensi);
$persen = $totalAbsen ? round(($absensi['hadir'] + $absensi['terlambat']) / $totalAbsen * 100) : null;
$warnaAbsen = ['hadir' => 'bg-emerald-500', 'terlambat' => 'bg-amber-500', 'sakit' => 'bg-sky-500', 'izin' => 'bg-violet-500', 'alfa' => 'bg-rose-500'];
@endphp

<div id="halaman-detail-peserta">
<div class="space-y-6 pb-4">
    <a href="{{ route('asatid.peserta') }}" @click="kembali"
        class="inline-flex items-center gap-2 rounded-2xl bg-white ring-1 ring-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:ring-emerald-200 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke halaman sebelumnya
    </a>

    {{-- Data peserta: tampilan sama dengan Profil di dasbor siswa --}}
    @include('partials.detail-santri', ['admin' => false, 'judul' => 'Detail Peserta', 'rahasia' => true, 'sisipan' => 'siswa._persyaratan'])

    {{-- ===== Rekap kehadiran (khusus asatid) ===== --}}
    <section class="{{ $kartuAbsen }} p-5 sm:p-6">
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
</div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: tombol kembali ke halaman sebelumnya (metode dari partials/vue-metode.blade.php)
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-detail-peserta'));
</script>
@endpush
