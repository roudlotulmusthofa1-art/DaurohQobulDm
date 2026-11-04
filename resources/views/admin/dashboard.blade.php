{{--
    Admin > Dashboard
    Semua angka & daftar diambil dari data sesi aktif (DashboardController); tiap kartu menuju menunya.
    Kotak "Pesan Peserta": pilih percakapan di kiri (?pesan=id), baca & balas di kanan.
--}}
@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
@php
use App\Models\PendaftaranSiswa;
use App\Models\PesanPeserta;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$jam = (int) $hariIni->format('H');
$salam = $jam < 11 ? __('Selamat pagi') : ($jam < 15 ? __('Selamat siang') : ($jam < 18 ? __('Selamat sore') : __('Selamat malam')));
$rute = fn (string $nama, mixed $p = []) => \Illuminate\Support\Facades\Route::has($nama) ? route($nama, $p) : '#';
$warnaStatus = [PendaftaranSiswa::DITERIMA => 'bg-emerald-50 text-emerald-700 ring-emerald-100', PendaftaranSiswa::DITOLAK => 'bg-rose-50 text-rose-600 ring-rose-100', PendaftaranSiswa::MENUNGGU => 'bg-amber-50 text-amber-700 ring-amber-100'];
$inisial = fn ($nama) => mb_strtoupper(mb_substr((string) $nama, 0, 1));
$waktuPesan = fn ($t) => $t->timezone('Asia/Jakarta')->isToday() ? $t->timezone('Asia/Jakarta')->format('H:i') : $t->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M');

$statKartu = [
    [__('Santri Diterima'), number_format($angka['diterima']), $angka['pendaftar'] ? __('dari :jumlah pendaftar', ['jumlah' => $angka['pendaftar']]) : __('belum ada pendaftar'),
        $angka['pendaftar'] ? round($angka['diterima'] / $angka['pendaftar'] * 100) : 0, $rute('admin.santri.index'), 'from-emerald-400 to-teal-600',
        'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342'],
    [__('Menunggu Verifikasi'), number_format($angka['menunggu']), __('+:jumlah pendaftar 7 hari terakhir', ['jumlah' => $angka['pendaftar_minggu_ini']]),
        $angka['pendaftar'] ? round($angka['menunggu'] / $angka['pendaftar'] * 100) : 0, $rute('admin.data-masuk.index'), 'from-amber-400 to-orange-500',
        'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z'],
    [__('Asatid Aktif'), number_format($angka['asatid']), $angka['asatid_menunggu'] ? __(':jumlah pendaftar menunggu', ['jumlah' => $angka['asatid_menunggu']]) : __('tidak ada pendaftar baru'),
        ($angka['asatid'] + $angka['asatid_menunggu']) ? round($angka['asatid'] / ($angka['asatid'] + $angka['asatid_menunggu']) * 100) : 0, $rute('admin.asatid.index'), 'from-sky-400 to-blue-600',
        'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
    [__('Kehadiran 30 Hari'), $angka['kehadiran'] !== null ? $angka['kehadiran'] . '%' : '–', __(':sesi sesi absensi · :catatan catatan', ['sesi' => $angka['absensi_30'], 'catatan' => $angka['catatan_absen']]),
        $angka['kehadiran'] ?? 0, $rute('admin.laporan-absensi.index'), 'from-violet-400 to-purple-600',
        'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];
@endphp

<div id="halaman-dashboard" class="space-y-6 mx-4 md:mx-10 lg:mx-20">
    {{-- Layar kecil: tawarkan tampilan HP (lihat admin/dashboard-hp.blade.php) --}}
    <a href="{{ route('admin.dashboard.tampilan', 'hp') }}"
        class="lg:hidden mt-4 flex items-center gap-3 rounded-2xl bg-[#0b332c] text-white px-4 py-3 text-sm">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
        <span class="flex-1">{{ __('Buka') }} <b>{{ __('tampilan HP') }}</b> {{ __('yang lebih nyaman di layar kecil') }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    </a>
    @foreach (['success' => 'bg-emerald-50 ring-emerald-200 text-emerald-900', 'error' => 'bg-amber-50 ring-amber-200 text-amber-900'] as $kunciFlash => $gayaFlash)
    @if (session($kunciFlash))
    <div class="flex items-start gap-3 p-4 rounded-2xl ring-1 text-sm {{ $gayaFlash }}">{{ session($kunciFlash) }}</div>
    @endif
    @endforeach

    {{-- ===== Sapaan ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-end gap-5">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/90">{{ $hariIni->locale('id')->translatedFormat('l, d F Y') }} · {{ $hijriyah }}</p>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $salam }}, {{ $user->name }}</h1>
                <p class="mt-1.5 text-sm text-white/75">
                    {{ __('Sesi aktif:') }} <b class="text-white">{{ $sesi?->nama ?? '-' }}</b>
                    @if ($bulan) · {{ __('Bulan ajaran ke-:urutan', ['urutan' => $bulan->urutan]) }} ({{ $bulan->nama }}) @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $rute('admin.data-masuk.index') }}" class="rounded-2xl bg-white/10 ring-1 ring-white/20 px-4 py-2.5 text-sm font-semibold hover:bg-white/20">{{ __('Data Masuk') }}</a>
                <a href="{{ $rute('admin.jadwal.index') }}" class="rounded-2xl bg-white/10 ring-1 ring-white/20 px-4 py-2.5 text-sm font-semibold hover:bg-white/20">{{ __('Jadwal') }}</a>
                <a href="{{ $rute('admin.pesan-peserta.index') }}" class="relative rounded-2xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-amber-300">
                    {{ __('Pesan Peserta') }}
                    @if ($pesanBelum)<span class="absolute -top-2 -right-2 min-w-[20px] h-5 px-1 rounded-full bg-rose-500 text-white text-[11px] font-bold leading-5 text-center ring-2 ring-white">{{ $pesanBelum }}</span>@endif
                </a>
            </div>
        </div>
    </section>

    {{-- ===== Angka utama ===== --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($statKartu as [$judul, $nilai, $ket, $persen, $url, $warna, $ikon])
        <a href="{{ $url }}" class="group {{ $kartu }} p-5 hover:-translate-y-0.5 hover:ring-emerald-200 transition">
            <div class="flex items-start justify-between gap-2">
                <span class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $warna }} text-white flex items-center justify-center shadow-lg shadow-black/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </span>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-600 -rotate-45 group-hover:rotate-0 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-slate-800 tabular-nums">{{ $nilai }}</p>
            <p class="text-sm font-semibold text-slate-600">{{ $judul }}</p>
            <p class="mt-0.5 text-[11px] text-slate-400 truncate">{{ $ket }}</p>
            <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r {{ $warna }}" style="width: {{ min(100, $persen) }}%"></div></div>
        </a>
        @endforeach
    </div>

    {{-- ===== Pesan Peserta (baca & balas) ===== --}}
    <section id="pesan-peserta" class="{{ $kartu }} overflow-hidden scroll-mt-24">
        <div class="flex flex-wrap items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
            </span>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-slate-800">{{ __('Pesan Peserta') }}</h2>
                <p class="text-xs text-slate-500">{{ $pesanBelum ? __(':jumlah pesan belum dibaca', ['jumlah' => $pesanBelum]) : __('Semua pesan sudah dibaca') }} · {{ __('dari menu "Pesan untuk Admin" di dasbor siswa') }}</p>
            </div>
            <a href="{{ $rute('admin.pesan-peserta.index') }}" class="rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Semua pesan & kirim pesan baru') }}</a>
        </div>

        <div class="grid lg:grid-cols-5 min-h-[420px]">
            {{-- Daftar percakapan --}}
            <div class="lg:col-span-2 lg:border-r border-slate-100 max-h-[520px] overflow-y-auto">
                @forelse ($percakapan as $c)
                @php $aktif = $dibuka && $dibuka->id === $c->peserta->id; @endphp
                <a href="{{ route('admin.dashboard', ['pesan' => $c->peserta->id]) }}#pesan-peserta"
                    class="flex items-center gap-3 px-4 py-3 border-b border-slate-50 transition-colors {{ $aktif ? 'bg-emerald-50 border-l-4 border-l-emerald-500' : 'hover:bg-[#f8fbfa]' }}">
                    @if ($c->peserta->foto_url)
                    <img data-perbesar="{{ $c->peserta->nama_lengkap }}" src="{{ $c->peserta->foto_url }}" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover">
                    @else
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white font-bold flex items-center justify-center">{{ $inisial($c->peserta->nama_lengkap) }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm {{ $c->belum ? 'font-extrabold text-slate-900' : 'font-semibold text-slate-700' }}">{{ $c->peserta->nama_lengkap }}</p>
                            <span class="shrink-0 text-[10px] text-slate-400">{{ $c->terakhir ? $waktuPesan($c->terakhir->created_at) : '' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-xs {{ $c->belum ? 'text-slate-700' : 'text-slate-400' }}">
                                @if ($c->terakhir?->dari === PesanPeserta::DARI_ADMIN)<span class="text-sky-600">{{ __('Anda:') }}</span>@endif
                                {{ $c->terakhir?->judul ?: $c->terakhir?->isi }}
                            </p>
                            @if ($c->belum)<span class="shrink-0 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-5 text-center">{{ $c->belum }}</span>@endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <p class="text-sm font-semibold text-slate-600">{{ __('Belum ada pesan') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Pesan dari peserta akan muncul di sini.') }}</p>
                </div>
                @endforelse
            </div>

            {{-- Percakapan terbuka --}}
            <div class="lg:col-span-3 flex flex-col bg-[#f6f9f8]">
                @if ($dibuka)
                <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800 truncate">{{ $dibuka->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-500">{{ $dibuka->nomor_pendaftaran }} · <span class="rounded-full px-1.5 ring-1 {{ $warnaStatus[$dibuka->status] ?? '' }}">{{ __($dibuka->label_status) }}</span></p>
                    </div>
                    <a href="{{ route('admin.pendaftaran.show', $dibuka) }}" class="shrink-0 text-xs font-semibold text-emerald-700 hover:underline">{{ __('Detail') }}</a>
                    <a href="{{ route('admin.pesan-peserta.show', $dibuka) }}" class="shrink-0 text-xs font-semibold text-slate-500 hover:underline">{{ __('Buka penuh') }}</a>
                </div>
                <div class="flex-1 max-h-[360px] overflow-y-auto px-5 py-4 space-y-3" ref="isiPercakapan">
                    @foreach ($isiPercakapan as $p)
                    @php $dariAdmin = $p->dari === PesanPeserta::DARI_ADMIN; @endphp
                    <div class="flex {{ $dariAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 shadow-sm {{ $dariAdmin ? 'rounded-tr-sm bg-[#d9fdd3]' : 'rounded-tl-sm bg-white ring-1 ring-slate-100' }}">
                            @if ($p->judul)<p class="text-[13px] font-bold text-slate-800">{{ $p->judul }}</p>@endif
                            <p class="text-[13px] text-slate-700 whitespace-pre-line break-words">{{ $p->isi }}</p>
                            <p class="mt-1 text-right text-[10px] text-slate-400">
                                {{ $dariAdmin ? __('Admin') . ($p->pengirim ? ' · ' . $p->pengirim->name : '') . ' · ' : '' }}{{ $p->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M, H:i') }}
                                @if ($dariAdmin) · {{ $p->dibaca_at ? __('dibaca') : __('terkirim') }} @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.pesan-peserta.balas', $dibuka) }}" class="flex items-end gap-2 p-3 bg-white border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="kembali" value="dashboard">
                    <textarea name="isi" rows="2" required maxlength="3000" placeholder="{{ __('Tulis balasan untuk :nama…', ['nama' => $dibuka->nama_lengkap]) }}" @keydown.ctrl.enter.prevent="kirimBalasan" @keydown.meta.enter.prevent="kirimBalasan"
                        class="flex-1 resize-none rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    <button class="shrink-0 w-11 h-11 rounded-2xl bg-[#0b332c] text-white flex items-center justify-center hover:bg-[#135347]" title="{{ __('Kirim (Ctrl+Enter)') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </form>
                @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <span class="w-14 h-14 rounded-2xl bg-white ring-1 ring-slate-200 text-slate-300 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-600">{{ __('Pilih percakapan di sebelah kiri') }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('untuk membaca dan membalas pesan peserta.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </section>

    <div class="grid xl:grid-cols-3 gap-6">
        {{-- ===== Perlu tindakan ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Perlu Tindakan') }}</h2>
                <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-600 ring-1 ring-rose-100">{{ $tindakan['total'] }}</span>
            </div>
            <div class="mt-3 space-y-1 max-h-[340px] overflow-y-auto">
                @forelse ($tindakan['items'] as $n)
                <a href="{{ $n['url'] ?? '#' }}" class="flex items-start gap-3 rounded-2xl px-2 py-2.5 hover:bg-[#f5f9f7]">
                    <span class="w-9 h-9 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon'] }}"/></svg>
                    </span>
                    <span class="min-w-0">
                        {{-- teks sudah di-escape di NotifikasiAdmin --}}
                        <span class="block text-[13px] text-slate-700 leading-snug [&_b]:font-semibold [&_b]:text-slate-900">{!! $n['teks'] !!}</span>
                        <span class="block text-[11px] text-slate-400">{{ collect([$n['keterangan'], $n['waktu']])->filter()->implode(' · ') }}</span>
                    </span>
                </a>
                @empty
                <p class="py-8 text-center text-sm text-slate-400">{{ __('Tidak ada yang perlu ditindaklanjuti. 🎉') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Jadwal hari ini ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Jadwal Hari Ini') }}</h2>
                <a href="{{ $rute('admin.jadwal.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Jadwal Mengajar') }}</a>
            </div>
            <p class="text-xs text-slate-400">{{ $hariIni->locale('id')->translatedFormat('l') }} · {{ __(':jumlah jam pelajaran', ['jumlah' => $jadwalHariIni->count()]) }}</p>
            <div class="mt-3 space-y-2 max-h-[320px] overflow-y-auto">
                @forelse ($jadwalHariIni as $j)
                <div class="flex items-center gap-3 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2.5">
                    <div class="w-16 shrink-0 text-center">
                        <p class="text-[11px] font-bold text-slate-700 leading-tight" dir="auto">{{ $j->waktu?->nama }}</p>
                        @if ($j->waktu?->jam_mulai)<p class="text-[10px] text-slate-400 tabular-nums">{{ $j->waktu->jam_mulai }}</p>@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ $j->kitab?->nama ?? __('Tanpa kitab') }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ $namaUstadz[$j->user_id] ?? $j->ustadz?->name ?? __('Ustadz belum ditentukan') }}</p>
                        @if (filled($arabUstadz[$j->user_id] ?? null))<p class="truncate text-sm text-slate-600" dir="rtl" lang="ar">{{ $arabUstadz[$j->user_id] }}</p>@endif
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100">{{ $j->kelas?->nama }}</span>
                </div>
                @empty
                <p class="py-8 text-center text-sm text-slate-400">{{ $bulan ? __('Tidak ada jadwal hari ini.') : __('Belum ada bulan ajaran aktif.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Pendaftar terbaru ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Pendaftar Terbaru') }}</h2>
                <a href="{{ $rute('admin.data-masuk.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Data Masuk') }}</a>
            </div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($pendaftarBaru as $p)
                <a href="{{ route('admin.pendaftaran.show', $p) }}" class="flex items-center gap-3 py-2.5 hover:bg-[#f8fbfa] rounded-xl px-1">
                    @if ($p->foto_url)
                    <img data-perbesar="{{ $p->nama_lengkap }}" src="{{ $p->foto_url }}" alt="" class="w-9 h-9 shrink-0 rounded-xl object-cover">
                    @else
                    <span class="w-9 h-9 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ $inisial($p->nama_lengkap) }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ $p->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-400">{{ $p->nomor_pendaftaran }} · {{ $p->created_at->locale('id')->diffForHumans() }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $warnaStatus[$p->status] ?? '' }}">{{ __($p->label_status) }}</span>
                </a>
                @empty
                <p class="py-8 text-center text-sm text-slate-400">{{ __('Belum ada pendaftar.') }}</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        {{-- ===== Absensi terbaru ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Absensi Terbaru') }}</h2>
                <a href="{{ $rute('admin.laporan-absensi.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Laporan Absensi') }}</a>
            </div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($absensiTerbaru as $a)
                <a href="{{ $rute('admin.laporan-absensi.show', $a) }}" class="flex items-center gap-3 py-2.5 rounded-xl px-1 hover:bg-[#f8fbfa]">
                    <span class="w-9 h-9 shrink-0 rounded-xl flex items-center justify-center text-[10px] font-bold {{ $a->jenis === 'kegiatan' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700' }}">{{ $a->jenis === 'kegiatan' ? __('KGT') : __('KLS') }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800" dir="auto">{{ $a->judul }}</p>
                        <p class="text-[11px] text-slate-400">{{ $a->tanggal->locale('id')->translatedFormat('d M Y') }} · {{ $a->pengabsen?->name ?? '-' }}</p>
                    </div>
                    <span class="shrink-0 text-xs font-semibold text-slate-500">{{ __(':jumlah peserta', ['jumlah' => $a->detail_count]) }}</span>
                </a>
                @empty
                <p class="py-8 text-center text-sm text-slate-400">{{ __('Belum ada absensi.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Kelas & kamar ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-800">{{ __('Kelas & Kamar') }}</h2>
                <a href="{{ $rute('admin.santri.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Data Santri') }}</a>
            </div>
            @if ($angka['belum_kelas'] || $angka['belum_kamar'])
            <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ __(':kelas santri belum dapat kelas · :kamar belum dapat kamar', ['kelas' => $angka['belum_kelas'], 'kamar' => $angka['belum_kamar']]) }}</p>
            @endif
            <div class="mt-3 grid sm:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Kelas') }}</p>
                    @forelse ($kelas as $k)
                    <div class="flex items-center justify-between gap-2 text-sm"><span class="truncate text-slate-700" dir="auto">{{ $k['nama'] }}</span><span class="font-bold tabular-nums text-slate-800">{{ $k['isi'] }}</span></div>
                    @empty
                    <p class="text-xs text-slate-400">{{ __('Belum ada kelas.') }}</p>
                    @endforelse
                </div>
                <div class="space-y-2.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Kamar') }}</p>
                    @forelse ($kamar as $km)
                    @php $persenKamar = $km->kapasitas > 0 ? min(100, round($km->isi / $km->kapasitas * 100)) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2 text-sm"><span class="truncate text-slate-700" dir="auto">{{ $km->nama }}</span><span class="text-xs tabular-nums text-slate-500">{{ $km->isi }}{{ $km->kapasitas > 0 ? '/' . $km->kapasitas : '' }}</span></div>
                        @if ($km->kapasitas > 0)<div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full {{ $persenKamar >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $persenKamar }}%"></div></div>@endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-400">{{ __('Belum ada kamar.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            methods: {
                // Ctrl/Cmd + Enter di kolom balasan -> kirim
                kirimBalasan(e) {
                    e.target.form.requestSubmit();
                },
            },
            // Kotak pesan: langsung tampilkan pesan paling bawah (terbaru)
            mounted() {
                const isi = this.$refs.isiPercakapan;
                if (isi) isi.scrollTop = isi.scrollHeight;
            },
        }).mount('#halaman-dashboard');
    });
</script>
@endpush
