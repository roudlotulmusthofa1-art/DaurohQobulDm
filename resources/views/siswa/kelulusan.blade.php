{{-- Akun Siswa > Pengumuman Kelulusan: hasil seleksi pendaftaran (status, penempatan, persyaratan, catatan admin) --}}
@extends('layouts.siswa')

@section('judul', 'Pengumuman Kelulusan')

@section('content')
@php
use App\Models\PendaftaranSiswa;
$s = $siswa;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$hasil = match ($s->status) {
    PendaftaranSiswa::DITERIMA => [
        'judul' => 'SELAMAT, ANDA DITERIMA',
        'teks' => 'Alhamdulillah, Anda dinyatakan diterima sebagai peserta dauroh. Lengkapi persyaratan yang belum terpenuhi di bawah ini.',
        'latar' => 'from-emerald-500 via-emerald-600 to-teal-700',
        'ikon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    PendaftaranSiswa::DITOLAK => [
        'judul' => 'MOHON MAAF, ANDA BELUM DITERIMA',
        'teks' => 'Terima kasih sudah mendaftar. Semoga Allah memberikan kesempatan lain yang lebih baik.',
        'latar' => 'from-rose-500 via-rose-600 to-pink-700',
        'ikon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    default => [
        'judul' => 'MENUNGGU PENGUMUMAN',
        'teks' => 'Data Anda sedang diverifikasi admin. Hasilnya akan tampil di halaman ini dan dikirim lewat WhatsApp wali.',
        'latar' => 'from-amber-400 via-amber-500 to-orange-600',
        'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
};
$syarat = collect(PendaftaranSiswa::SYARAT)->map(fn ($nama, $kolom) => ['nama' => $nama, 'ok' => (bool) $s->$kolom]);
$jumlahOk = $syarat->where('ok', true)->count();
$langkah = [
    ['Mendaftar', $s->created_at, true],
    ['Verifikasi admin', null, $s->status !== PendaftaranSiswa::MENUNGGU],
    ['Pengumuman', $s->diproses_at, $s->status !== PendaftaranSiswa::MENUNGGU],
];
@endphp

<div class="space-y-5">
    {{-- ===== Hasil ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br {{ $hasil['latar'] }} text-white p-6 sm:p-9 text-center">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-16 w-72 h-72 rounded-full bg-white/15 blur-3xl"></div>
        <div class="relative">
            <span class="mx-auto w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white/20 ring-4 ring-white/25 flex items-center justify-center">
                <svg class="w-9 h-9 sm:w-11 sm:h-11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $hasil['ikon'] }}"/></svg>
            </span>
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/75">Hasil seleksi · {{ $s->nomor_pendaftaran }}</p>
            <h2 class="mt-1 text-2xl sm:text-4xl font-extrabold tracking-tight">{{ $hasil['judul'] }}</h2>
            <p class="mt-1 text-lg font-bold text-white/90">{{ $s->nama_lengkap }}</p>
            <p class="mx-auto mt-3 max-w-xl text-sm text-white/80">{{ $hasil['teks'] }}</p>
            @if ($s->diproses_at)
            <p class="mt-3 text-xs text-white/70">Diumumkan {{ $s->diproses_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</p>
            @endif
        </div>
    </section>

    {{-- ===== Tahapan ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <ol class="grid grid-cols-3 gap-2">
            @foreach ($langkah as $i => [$nama, $waktu, $selesai])
            <li class="relative text-center">
                @if ($i > 0)<span class="absolute top-4 right-1/2 w-full h-0.5 {{ $selesai ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>@endif
                <span class="relative mx-auto w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $selesai ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 ring-1 ring-slate-200' }}">{{ $selesai ? '✓' : $i + 1 }}</span>
                <p class="mt-2 text-xs sm:text-sm font-semibold text-slate-700">{{ $nama }}</p>
                @if ($waktu)<p class="text-[11px] text-slate-400">{{ $waktu->timezone('Asia/Jakarta')->format('d/m/Y') }}</p>@endif
            </li>
            @endforeach
        </ol>
    </section>

    @if ($s->status === PendaftaranSiswa::DITERIMA)
    <div class="grid lg:grid-cols-2 gap-5">
        {{-- ===== Penempatan ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2.5 font-bold text-slate-800"><span class="w-2 h-6 rounded-full bg-gradient-to-b from-emerald-400 to-teal-600"></span>Penempatan</h3>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 p-4">
                    <p class="text-xs text-sky-700">Kelas</p>
                    <p class="mt-1 text-lg font-extrabold text-sky-900" dir="auto">{{ $s->kelasMaster?->nama ?? 'Belum ditentukan' }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50 ring-1 ring-amber-100 p-4">
                    <p class="text-xs text-amber-700">Kamar</p>
                    <p class="mt-1 text-lg font-extrabold text-amber-900" dir="auto">{{ $s->kamarMaster?->nama ?? 'Belum ditentukan' }}</p>
                    @if ($s->nomor_kasur)<p class="text-xs font-semibold text-amber-700">Kasur nomor {{ $s->nomor_kasur }}</p>@endif
                </div>
            </div>
        </section>

        {{-- ===== Persyaratan ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <h3 class="flex items-center gap-2.5 font-bold text-slate-800"><span class="w-2 h-6 rounded-full bg-gradient-to-b from-amber-400 to-orange-500"></span>Persyaratan</h3>
                <span class="text-sm font-extrabold {{ $jumlahOk === $syarat->count() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $jumlahOk }}/{{ $syarat->count() }}</span>
            </div>
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($syarat as $sy)
                <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <span class="text-slate-700">{{ $sy['nama'] }}</span>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $sy['ok'] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-rose-50 text-rose-600 ring-1 ring-rose-100' }}">{{ $sy['ok'] ? 'Sudah' : 'Belum' }}</span>
                </li>
                @endforeach
            </ul>
        </section>
    </div>
    @endif

    @if ($s->catatan_admin && $s->status !== PendaftaranSiswa::MENUNGGU)
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h3 class="flex items-center gap-2.5 font-bold text-slate-800"><span class="w-2 h-6 rounded-full bg-gradient-to-b from-sky-400 to-blue-600"></span>{{ $s->status === PendaftaranSiswa::DITOLAK ? 'Alasan dari admin' : 'Catatan dari admin' }}</h3>
        <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">{{ $s->catatan_admin }}</p>
    </section>
    @endif

    <p class="text-center text-sm text-slate-500">Ada pertanyaan? <a href="{{ route('siswa.pesan-saya') }}" class="font-semibold text-emerald-700 hover:underline">Kirim pesan ke admin</a></p>
</div>
@endsection
