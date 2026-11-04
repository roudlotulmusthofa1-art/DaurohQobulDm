{{--
    Akun Siswa > Pengumuman Kelulusan: hasil KELULUSAN AKHIR dauroh (bukan hasil seleksi pendaftaran).
    Diisi admin di Akademik > Kelulusan dan baru terlihat setelah admin menekan "Umumkan" ($diumumkan).
--}}
@extends('layouts.siswa')

@section('judul', __('Pengumuman Kelulusan'))

@section('content')
@php
use App\Models\PendaftaranSiswa;
$s = $siswa;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$peserta = $s->status === PendaftaranSiswa::DITERIMA;

$hasil = match (true) {
    ! $peserta => [
        'judul' => __('BELUM MENJADI PESERTA'),
        'teks' => __('Pengumuman kelulusan hanya untuk peserta dauroh. Status pendaftaran Anda: :status.', ['status' => __($s->label_status)]),
        'latar' => 'from-slate-500 via-slate-600 to-slate-700',
        'ikon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    ],
    ! $diumumkan => [
        'judul' => __('BELUM DIUMUMKAN'),
        'teks' => __('Pengumuman kelulusan dibuka di akhir masa dauroh. Silakan cek kembali halaman ini nanti.'),
        'latar' => 'from-amber-400 via-amber-500 to-orange-600',
        'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    $s->status_kelulusan === PendaftaranSiswa::LULUS => [
        'judul' => __('SELAMAT, ANDA LULUS'),
        'teks' => __('Alhamdulillah, Anda dinyatakan lulus dari Dauroh Qobul. Semoga ilmu yang didapat berkah dan bermanfaat.'),
        'latar' => 'from-emerald-500 via-emerald-600 to-teal-700',
        'ikon' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
    ],
    $s->status_kelulusan === PendaftaranSiswa::TIDAK_LULUS => [
        'judul' => __('MOHON MAAF, ANDA BELUM LULUS'),
        'teks' => __('Tetap semangat dalam menuntut ilmu. Silakan hubungi admin untuk penjelasan dan langkah selanjutnya.'),
        'latar' => 'from-rose-500 via-rose-600 to-pink-700',
        'ikon' => 'M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z',
    ],
    default => [
        'judul' => __('HASIL BELUM TERSEDIA'),
        'teks' => __('Kelulusan sudah diumumkan, tetapi hasil Anda belum diisi. Silakan hubungi admin.'),
        'latar' => 'from-slate-500 via-slate-600 to-slate-700',
        'ikon' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
    ],
};
$tampilHasil = $peserta && $diumumkan;
$langkah = [
    [__('Diterima jadi peserta'), $peserta],
    [__('Masa belajar dauroh'), $peserta],
    [__('Pengumuman kelulusan'), $tampilHasil],
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
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/75">{{ __('Pengumuman Kelulusan') }} · {{ $s->nomor_pendaftaran }}</p>
            <h2 class="mt-1 text-2xl sm:text-4xl font-extrabold tracking-tight">{{ $hasil['judul'] }}</h2>
            <p class="mt-1 text-lg font-bold text-white/90">{{ $s->nama_lengkap }}</p>

            @if ($tampilHasil && $s->status_kelulusan === PendaftaranSiswa::LULUS && $s->predikat_kelulusan)
            <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/20 ring-1 ring-white/30 px-4 py-1.5 text-sm font-bold">
                <svg class="w-4 h-4 text-amber-200" fill="currentColor" viewBox="0 0 24 24"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                {{ __('Predikat:') }} {{ $s->predikat_kelulusan }}
            </p>
            @endif

            <p class="mx-auto mt-3 max-w-xl text-sm text-white/80">{{ $hasil['teks'] }}</p>
            @if ($tampilHasil)
            <p class="mt-3 text-xs text-white/70">{{ __('Diumumkan') }} {{ $diumumkan->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</p>
            @endif
        </div>
    </section>

    {{-- ===== Catatan dari admin (bila ada) ===== --}}
    @if ($tampilHasil && $s->catatan_kelulusan)
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h3 class="flex items-center gap-2.5 font-bold text-slate-800"><span class="w-2 h-6 rounded-full bg-gradient-to-b from-sky-400 to-blue-600"></span>{{ __('Catatan dari Admin') }}</h3>
        <p dir="auto" class="mt-3 text-[15px] leading-relaxed text-slate-700 whitespace-pre-line">{{ $s->catatan_kelulusan }}</p>
    </section>
    @endif

    {{-- ===== Tahapan ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <ol class="grid grid-cols-3 gap-2">
            @foreach ($langkah as $i => [$nama, $selesai])
            <li class="relative text-center">
                @if ($i > 0)<span class="absolute top-4 right-1/2 w-full h-0.5 {{ $selesai ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>@endif
                <span class="relative mx-auto w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $selesai ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 ring-1 ring-slate-200' }}">{{ $selesai ? '✓' : $i + 1 }}</span>
                <p class="mt-2 text-xs sm:text-sm font-semibold text-slate-700">{{ $nama }}</p>
            </li>
            @endforeach
        </ol>
    </section>

    <p class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 px-4 py-3 text-sm text-sky-900">
        {{ __('Ada pertanyaan tentang kelulusan? Kirim pesan lewat menu') }}
        <a href="{{ route('siswa.pesan-saya') }}" class="font-semibold underline">{{ __('Pesan untuk Admin') }}</a>.
    </p>
</div>
@endsection
