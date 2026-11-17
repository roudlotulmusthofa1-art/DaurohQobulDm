{{--
    Admin > Akademik > Absensi Kegiatan (AbsensiKegiatanController).
    Admin membuat absensi kegiatan (nama dari Master Data > Jenis Kegiatan + tanggal + jam), lalu mengaktifkannya.
    Yang AKTIF tampil di dasbor semua asatid dan dicatat bersama-sama. Tutup = peserta yang belum tercatat jadi Alfa.
    Form tambah/ubah memakai <dialog> (lihat _form-absensi-kegiatan). Vue: #halaman-absensi-kegiatan (metodeHalaman).
    Kegiatan BERULANG (seperti alarm HP, JadwalKegiatan): satu kartu per jadwal di depan daftar (tab Berjalan / Semua),
    bergaya sama dengan kartu absensi; berisi hari + saklar ON/OFF + absensi hari ini (Absen / Tutup) + Hasil terakhir.
    Tab Selesai hanya absensi sekali; hasil kegiatan berulang dilihat di kartunya / Laporan Absensi.
--}}
@extends('layouts.admin')

@section('title', __('Absensi Kegiatan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$tab = fn ($kunci) => route('admin.absensi-kegiatan.index', array_filter(['saring' => $kunci, 'cari' => $cari]));
$hariIni = now('Asia/Jakarta')->toDateString();
@endphp

<div id="halaman-absensi-kegiatan">
<div class="space-y-5 mx-0 md:mx-6 lg:mx-12">
    @include('admin.akademik._flash')

    <x-header-admin :label="__('Penilaian')" :judul="__('Absensi Kegiatan')"
        :keterangan="__('Buat absensi kegiatan & atur waktunya. Yang aktif tampil di dasbor semua asatid, sehingga bisa diabsen bersama-sama.')">
        <x-slot:aksi>
            <button type="button" @click="bukaDialog('dialog-kegiatan-baru')"
                class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-amber-300 shadow-lg shadow-amber-500/30 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                {{ __('Buat Absensi Kegiatan') }}
            </button>
        </x-slot:aksi>
    </x-header-admin>

    {{-- Saringan --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach (['berjalan' => [__('Berjalan'), $jumlah['berjalan']], 'selesai' => [__('Selesai'), $jumlah['selesai']], 'semua' => [__('Semua'), $jumlah['berjalan'] + $jumlah['selesai']]] as $kunci => [$nama, $n])
        <a href="{{ $tab($kunci) }}"
            class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors {{ $saring === $kunci ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">
            {{ $nama }} <span class="rounded-full px-2 text-[11px] {{ $saring === $kunci ? 'bg-white/20' : 'bg-slate-100' }}">{{ $n }}</span>
        </a>
        @endforeach
    </div>

    {{-- Cari nama kegiatan (tekan Enter) --}}
    <form method="GET" class="flex gap-2">
        <input type="hidden" name="saring" value="{{ $saring }}">
        <input type="search" name="cari" value="{{ $cari }}" placeholder="{{ __('Cari nama kegiatan…') }}"
            class="flex-1 sm:max-w-sm rounded-xl bg-white ring-1 ring-slate-200 border-0 px-4 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none">
        <button class="shrink-0 px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Cari') }}</button>
        @if ($cari !== '')
        <a href="{{ route('admin.absensi-kegiatan.index', ['saring' => $saring]) }}" class="shrink-0 px-4 py-2.5 rounded-xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Hapus') }}</a>
        @endif
    </form>

    @if ($kegiatanList->isEmpty())
    <div class="rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 text-sm text-amber-800">
        {{ __('Belum ada nama kegiatan aktif. Tambahkan dulu di') }}
        <a href="{{ route('admin.master-data.index', ['tab' => 'jenis-kegiatan']) }}" class="font-semibold underline">{{ __('Master Data') }} › {{ __('Jenis Kegiatan') }}</a>.
    </div>
    @endif

    {{-- ===== Daftar (kartu kegiatan berulang di depan, lalu absensi sekali) ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($jadwalList as $j)
        @php
            $hariJ = array_map('intval', $j->hari ?? []);
            $absenJ = $berulangBerjalan[$j->id] ?? null;      // absensi hari ini yang sedang berjalan
            $hasilJ = $berulangTerakhir[$j->id] ?? null;      // absensi terakhir yang sudah ditutup
            $tercatatJ = $absenJ?->tercatat_count ?? 0;
            $persenJ = $jumlahPeserta ? min(100, round($tercatatJ / $jumlahPeserta * 100)) : 0;
            $namaPencatatJ = $absenJ ? ($pencatat[$absenJ->id] ?? collect()) : collect();
            [$labelStatusJ, $gayaStatusJ] = ! $j->nyala
                ? [__('OFF'), 'bg-slate-100 text-slate-500 ring-slate-200']
                : ($absenJ
                    ? ($absenJ->sudahDibuka() ? [__('Aktif'), 'bg-emerald-50 text-emerald-700 ring-emerald-200'] : [__('Belum waktunya'), 'bg-amber-50 text-amber-700 ring-amber-200'])
                    : [__('ON'), 'bg-emerald-50 text-emerald-700 ring-emerald-200']);
        @endphp
        <article class="flex flex-col rounded-3xl bg-white ring-1 overflow-hidden shadow-[0_1px_2px_rgba(16,24,40,0.04)] {{ $absenJ && $j->nyala ? 'ring-emerald-300' : 'ring-slate-200/80' }}">
            <div class="h-1.5 {{ $j->kegiatan?->warna ?: 'bg-emerald-400' }}"></div>
            <div class="flex-1 p-5">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </span>
                    <div class="min-w-0 flex-1" v-pre>
                        <h3 class="font-bold text-slate-800 truncate">{{ $j->kegiatan?->nama ?? __('Kegiatan') }}</h3>
                        <p class="text-xs text-slate-500">↻ {{ __('Berulang') }} · {{ $j->label_hari }}</p>
                        <div class="mt-1.5 flex items-center gap-1">
                            @foreach (\App\Models\JadwalKegiatan::HARI as $no => $huruf)
                            <span title="{{ __(\App\Models\JadwalKegiatan::NAMA_HARI[$no]) }}"
                                class="w-6 h-6 rounded-full text-[10px] font-bold flex items-center justify-center {{ in_array($no, $hariJ, true) ? ($j->nyala ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-white') : 'text-slate-300' }}">{{ $huruf }}</span>
                            @endforeach
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $gayaStatusJ }}">{{ $labelStatusJ }}</span>
                </div>

                {{-- Waktu --}}
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    @foreach ([[__('Mulai'), $j->jam_mulai, 'bg-emerald-50 text-emerald-700'], [__('Terlambat'), $j->batas_terlambat, 'bg-amber-50 text-amber-700'], [__('Alfa'), $j->batas_alfa, 'bg-rose-50 text-rose-700']] as [$lbl, $jam, $gaya])
                    <div class="rounded-2xl px-2 py-2 {{ $gaya }}">
                        <p class="text-[10px] font-semibold uppercase tracking-wider opacity-70">{{ $lbl }}</p>
                        <p class="text-sm font-extrabold tabular-nums">{{ substr((string) $jam, 0, 5) }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Absensi hari ini --}}
                <div class="mt-4">
                    @if ($absenJ)
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-600">{{ __('Hari ini:') }} {{ __(':n dari :total peserta tercatat', ['n' => $tercatatJ, 'total' => $jumlahPeserta]) }}</span>
                        <span class="text-slate-400">{{ $j->metode === 'qr' ? __('Pindai QR') : __('Manual') }}</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ $persenJ }}%"></div></div>
                    @unless ($absenJ->sudahDibuka())
                    <p class="mt-1.5 text-[11px] text-amber-700">{{ __('Tampil di dasbor asatid mulai pukul :jam.', ['jam' => $absenJ->waktuDibuka()->format('H:i')]) }}</p>
                    @endunless
                    @if ($namaPencatatJ->isNotEmpty())
                    <div class="mt-2 text-[11px] text-slate-500" v-pre>{{ __('Dicatat oleh:') }}
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            @foreach ($namaPencatatJ as $orangPencatat)
                            <span class="min-w-0"><span class="block font-semibold text-slate-700">{{ $orangPencatat['nama'] }}</span><x-nama-arab :teks="$orangPencatat['arab']" kelas="text-xs text-slate-400" /></span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @elseif (! $j->nyala)
                    <p class="text-xs text-slate-400">{{ __('Saklar OFF: absensi tidak dibuat sampai dinyalakan.') }}</p>
                    @elseif (! in_array(now('Asia/Jakarta')->dayOfWeekIso, $hariJ, true))
                    <p class="text-xs text-slate-400">{{ __('Hari ini tidak ada jadwal kegiatan ini.') }}</p>
                    @else
                    <p class="text-xs text-slate-400">{{ __('Absensi hari ini sudah selesai (atau waktunya sudah lewat).') }}</p>
                    @endif
                </div>
            </div>

            {{-- Tombol --}}
            <div class="flex flex-wrap gap-2 border-t border-slate-100 p-3">
                @if ($absenJ)
                <a href="{{ route('admin.absensi-kegiatan.catat', $absenJ) }}"
                    class="basis-full sm:basis-auto sm:flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0b332c] py-2 px-3 text-sm font-bold text-white hover:bg-[#135347] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    {{ __('Absen') }}
                </a>
                @endif
                {{-- Saklar ON / OFF --}}
                <form method="POST" action="{{ route('admin.absensi-kegiatan.jadwal.nyala', $j) }}" class="flex-1">
                    @csrf
                    <button class="w-full rounded-xl py-2 text-sm font-semibold transition-colors {{ $j->nyala ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-500' }}">
                        {{ $j->nyala ? __('Matikan (OFF)') : __('Nyalakan (ON)') }}
                    </button>
                </form>
                @if ($absenJ)
                <form method="POST" action="{{ route('admin.absensi-kegiatan.tutup', $absenJ) }}" class="flex-1" @submit="konfirmasi"
                    data-konfirmasi="{{ __('Tutup absensi ini? Peserta yang belum tercatat akan dihitung Alfa, dan asatid tidak bisa mencatat lagi.') }}">
                    @csrf
                    <button class="w-full rounded-xl bg-sky-50 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100 transition-colors">{{ __('Tutup') }}</button>
                </form>
                @elseif ($hasilJ)
                <a href="{{ route('admin.laporan-absensi.show', $hasilJ) }}" class="flex-1 rounded-xl bg-teal-50 py-2 text-center text-sm font-semibold text-teal-700 hover:bg-teal-100 transition-colors">{{ __('Hasil terakhir') }}</a>
                @endif
                <button type="button" @click="bukaDialog('dialog-jadwal-{{ $j->id }}')" title="{{ __('Ubah') }}" aria-label="{{ __('Ubah') }}"
                    class="rounded-xl bg-slate-50 px-3 text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.absensi-kegiatan.jadwal.hapus', $j) }}" @submit="konfirmasi"
                    data-konfirmasi="{{ __('Hapus kegiatan berulang ini? Absensi yang sudah dibuat sebelumnya tetap ada.') }}">
                    @csrf @method('DELETE')
                    <button title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}" class="h-full rounded-xl bg-rose-50 px-3 text-rose-600 hover:bg-rose-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </form>
            </div>
        </article>
        <dialog id="dialog-jadwal-{{ $j->id }}" ref="dialog-jadwal-{{ $j->id }}" class="dialog-akademik" @click.self="tutupDialog">
            @include('admin.akademik._form-absensi-kegiatan', ['aksi' => route('admin.absensi-kegiatan.jadwal.ubah', $j), 'metode' => 'PUT', 'judul' => __('Ubah Kegiatan Berulang'), 'a' => null, 'j' => $j])
        </dialog>
        @endforeach

        @forelse ($daftar as $a)
        @php
            $selesai = (bool) $a->selesai_at;
            $tercatat = $a->tercatat_count;
            $persen = $jumlahPeserta ? min(100, round($tercatat / $jumlahPeserta * 100)) : 0;
            $namaPencatat = $pencatat[$a->id] ?? collect();
            [$labelStatus, $gayaStatus] = $selesai
                ? [__('Selesai'), 'bg-slate-100 text-slate-600 ring-slate-200']
                : ($a->aktif ? [__('Aktif'), 'bg-emerald-50 text-emerald-700 ring-emerald-200'] : [__('Belum aktif'), 'bg-amber-50 text-amber-700 ring-amber-200']);
        @endphp
        <article class="flex flex-col rounded-3xl bg-white ring-1 overflow-hidden shadow-[0_1px_2px_rgba(16,24,40,0.04)] {{ ! $selesai && $a->aktif ? 'ring-emerald-300' : 'ring-slate-200/80' }}">
            <div class="h-1.5 {{ $a->kegiatan?->warna ?: 'bg-emerald-400' }}"></div>
            <div class="flex-1 p-5">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/></svg>
                    </span>
                    <div class="min-w-0 flex-1" v-pre>
                        <h3 class="font-bold text-slate-800 truncate">{{ $a->kegiatan?->nama ?? __('Kegiatan') }}</h3>
                        <p class="text-xs text-slate-500">
                            {{ $a->tanggal->locale('id')->translatedFormat('l, d M Y') }}
                            @if ($a->tanggal->toDateString() === $hariIni) · <span class="font-semibold text-emerald-600">{{ __('hari ini') }}</span> @endif
                        </p>
                        @if ($a->tanggal_hijriyah)<p class="text-[11px] text-slate-400">{{ $a->tanggal_hijriyah }}</p>@endif
                        @if ($a->jadwal_kegiatan_id)<span class="mt-1 inline-block rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700 ring-1 ring-sky-100">↻ {{ __('Berulang') }}</span>@endif
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $gayaStatus }}">{{ $labelStatus }}</span>
                </div>

                {{-- Waktu --}}
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    @foreach ([[__('Mulai'), $a->jam_mulai, 'bg-emerald-50 text-emerald-700'], [__('Terlambat'), $a->batas_terlambat, 'bg-amber-50 text-amber-700'], [__('Alfa'), $a->batas_alfa, 'bg-rose-50 text-rose-700']] as [$lbl, $jam, $gaya])
                    <div class="rounded-2xl px-2 py-2 {{ $gaya }}">
                        <p class="text-[10px] font-semibold uppercase tracking-wider opacity-70">{{ $lbl }}</p>
                        <p class="text-sm font-extrabold tabular-nums">{{ substr((string) $jam, 0, 5) }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Kemajuan pencatatan --}}
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-600">{{ __(':n dari :total peserta tercatat', ['n' => $tercatat, 'total' => $selesai ? $a->detail_count : $jumlahPeserta]) }}{{ $selesai && $a->detail_count > $tercatat ? ' · ' . __('sisanya Alfa') : '' }}</span>
                        <span class="text-slate-400">{{ $a->metode === 'qr' ? __('Pindai QR') : __('Manual') }}</span>
                    </div>
                    @unless ($selesai)
                    <div class="mt-1.5 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ $persen }}%"></div></div>
                    @endunless
                    @if ($namaPencatat->isNotEmpty())
                    <div class="mt-2 text-[11px] text-slate-500" v-pre>{{ __('Dicatat oleh:') }}
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            @foreach ($namaPencatat as $orangPencatat)
                            <span class="min-w-0"><span class="block font-semibold text-slate-700">{{ $orangPencatat['nama'] }}</span><x-nama-arab :teks="$orangPencatat['arab']" kelas="text-xs text-slate-400" /></span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tombol --}}
            <div class="flex flex-wrap gap-2 border-t border-slate-100 p-3">
                @unless ($selesai)
                {{-- Admin ikut mengabsen (halaman catat sama dengan asatid); hanya saat aktif --}}
                @if ($a->aktif)
                <a href="{{ route('admin.absensi-kegiatan.catat', $a) }}"
                    class="basis-full sm:basis-auto sm:flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0b332c] py-2 px-3 text-sm font-bold text-white hover:bg-[#135347] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    {{ __('Absen') }}
                </a>
                @endif
                <form method="POST" action="{{ route('admin.absensi-kegiatan.aktif', $a) }}" class="flex-1">
                    @csrf
                    <button class="w-full rounded-xl py-2 text-sm font-semibold transition-colors {{ $a->aktif ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-500' }}">
                        {{ $a->aktif ? __('Nonaktifkan') : __('Aktifkan') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.absensi-kegiatan.tutup', $a) }}" class="flex-1" @submit="konfirmasi"
                    data-konfirmasi="{{ __('Tutup absensi ini? Peserta yang belum tercatat akan dihitung Alfa, dan asatid tidak bisa mencatat lagi.') }}">
                    @csrf
                    <button class="w-full rounded-xl bg-sky-50 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100 transition-colors">{{ __('Tutup') }}</button>
                </form>
                <button type="button" @click="bukaDialog('dialog-kegiatan-{{ $a->id }}')" title="{{ __('Ubah') }}" aria-label="{{ __('Ubah') }}"
                    class="rounded-xl bg-slate-50 px-3 text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </button>
                @else
                <a href="{{ route('admin.laporan-absensi.show', $a) }}" class="flex-1 rounded-xl bg-teal-50 py-2 text-center text-sm font-semibold text-teal-700 hover:bg-teal-100 transition-colors">{{ __('Lihat hasil') }}</a>
                @endunless
                <form method="POST" action="{{ route('admin.absensi-kegiatan.destroy', $a) }}" @submit="konfirmasi"
                    data-konfirmasi="{{ __('Hapus absensi kegiatan ini beserta semua catatannya?') }}">
                    @csrf @method('DELETE')
                    <button title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}" class="h-full rounded-xl bg-rose-50 px-3 text-rose-600 hover:bg-rose-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </form>
            </div>
        </article>

        @unless ($selesai)
        <dialog id="dialog-kegiatan-{{ $a->id }}" ref="dialog-kegiatan-{{ $a->id }}" class="dialog-akademik" @click.self="tutupDialog">
            @include('admin.akademik._form-absensi-kegiatan', ['aksi' => route('admin.absensi-kegiatan.update', $a), 'metode' => 'PUT', 'judul' => __('Ubah Absensi Kegiatan'), 'a' => $a, 'j' => null])
        </dialog>
        @endunless
        @empty
        @if ($jadwalList->isEmpty())
        <div class="md:col-span-2 xl:col-span-3 {{ $kartu }} py-16 px-6 text-center">
            @if ($cari !== '')
            <p class="font-bold text-slate-700">{{ __('Tidak ada kegiatan yang cocok dengan ":cari".', ['cari' => $cari]) }}</p>
            @else
            <p class="font-bold text-slate-700">{{ $saring === 'selesai' ? __('Belum ada absensi kegiatan yang selesai') : __('Belum ada absensi kegiatan') }}</p>
            @endif
            <p class="mt-1 text-sm text-slate-500">{{ __('Klik "Buat Absensi Kegiatan", isi nama kegiatan & waktunya, lalu aktifkan.') }}</p>
        </div>
        @endif
        @endforelse
    </div>

    @if ($daftar->hasPages())
    <div>{{ $daftar->links() }}</div>
    @endif
</div>

<dialog id="dialog-kegiatan-baru" ref="dialog-kegiatan-baru" class="dialog-akademik" @click.self="tutupDialog">
    @include('admin.akademik._form-absensi-kegiatan', ['aksi' => route('admin.absensi-kegiatan.store'), 'metode' => 'POST', 'judul' => __('Buat Absensi Kegiatan'), 'a' => null, 'j' => null])
</dialog>
</div>
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            methods: {
                ...metodeHalaman, // bukaDialog, tutupDialog, konfirmasi (partials/vue-metode)

                /** Jam mulai diganti -> batas terlambat = +15 menit, batas alfa = +45 menit (masih bisa diubah manual) */
                geserBatas(e) {
                    const form = e.target.form;
                    const [j, m] = (e.target.value || '').split(':').map(Number);
                    if (!form || isNaN(j) || isNaN(m)) return;
                    // Paling lambat 23:59 supaya tidak loncat ke hari berikutnya
                    const jadi = (tambah) => {
                        const t = Math.min(j * 60 + m + tambah, 23 * 60 + 59);
                        return String(Math.floor(t / 60)).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0');
                    };
                    form.elements.batas_terlambat.value = jadi(15);
                    form.elements.batas_alfa.value = jadi(45);
                },

                /** Tombol cepat hari kegiatan berulang: Setiap hari / Senin–Jumat / Sabtu & Ahad */
                pilihHari(e, daftar) {
                    const form = e.target.form;
                    if (!form) return;
                    form.querySelectorAll('input[name="hari[]"]').forEach((c) => { c.checked = daftar.includes(Number(c.value)); });
                },

                /** Pengulangan diganti: Sekali -> tanggal wajib; Berulang -> tanggal disembunyikan & tidak dikirim, pilihan hari muncul */
                gantiPengulangan(e) {
                    const form = e.target.form;
                    if (!form) return;
                    const berulang = e.target.value === 'berulang';
                    const tanggal = form.querySelector('.blok-tanggal');
                    const hari = form.querySelector('.blok-hari');
                    if (tanggal) {
                        tanggal.classList.toggle('hidden', berulang);
                        const isian = tanggal.querySelector('input[name="tanggal"]');
                        if (isian) { isian.disabled = berulang; isian.required = !berulang; }
                    }
                    if (hari) hari.classList.toggle('hidden', !berulang);
                },
            },
            mounted() {
                // Gagal simpan (isian salah) -> buka lagi form baru supaya isian bisa diperbaiki
                @if ($errors->any() && old('_form') === 'baru')
                this.bukaDialog('dialog-kegiatan-baru');
                @endif
            },
        }).mount('#halaman-absensi-kegiatan');
    });
</script>
@endpush
