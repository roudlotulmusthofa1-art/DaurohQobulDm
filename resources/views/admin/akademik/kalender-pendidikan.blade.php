{{--
    Admin > Akademik > Kalender Pendidikan (Admin\KalenderPendidikanController@index).
    Alamat ?bulan=2026-11 (patokan Masehi) atau ?hijri=1448-6 (patokan Hijriyah).
    - Kalender satu bulan: tiap kotak = tanggal Masehi (besar) + tanggal Hijriyah (kecil) + pita kegiatan.
      Klik kotak hari = tambah kegiatan mulai tanggal itu; klik pita = ubah kegiatan.
    - Daftar kegiatan di bawahnya (bulan ini / semua) dengan lama kegiatan + kemajuan (hari ke-x dari y).
    - Formulir Tambah/Ubah: tanggal Masehi, tulisan Hijriyah muncul otomatis (GET admin.kalender.hijriyah).
    Vue: #halaman-kalender.
--}}
@extends('layouts.admin')

@section('title', __('Kalender Pendidikan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$tutup = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
$namaHari = [__('Ahad'), __('Senin'), __('Selasa'), __('Rabu'), __('Kamis'), __('Jumat'), __('Sabtu')];
@endphp

<div id="halaman-kalender" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8 pb-6">
    <x-header-admin :label="__('Akademik')" :judul="__('Kalender Pendidikan')"
        :keterangan="__('Rencana kegiatan pembelajaran dan lainnya, lengkap dengan tanggal Masehi dan Hijriyah.')">
        <p class="mt-2 text-xs font-semibold text-amber-200/90">{{ __('Hari ini') }}: {{ $hariIniTeks }}</p>
        <x-slot:aksi>
            <button type="button" @click="bukaJenis" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-bold hover:bg-white/20">
                <span class="flex -space-x-1"><span class="w-3 h-3 rounded-full bg-emerald-400 ring-1 ring-white/40"></span><span class="w-3 h-3 rounded-full bg-amber-400 ring-1 ring-white/40"></span><span class="w-3 h-3 rounded-full bg-rose-400 ring-1 ring-white/40"></span></span>
                {{ __('Jenis & Warna') }}
            </button>
            <button type="button" @click="bukaTambah()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-400 text-[#0b332c] text-sm font-bold hover:bg-emerald-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                {{ __('Tambah Kegiatan') }}
            </button>
        </x-slot:aksi>
    </x-header-admin>

    @unless ($tabelAda)
    <p class="rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 text-sm text-amber-900">{{ __('Tabel kalender belum dibuat. Jalankan php artisan migrate dan php artisan sesi:migrate.') }}</p>
    @endunless

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Sedang berjalan') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 tabular-nums" v-text="hitung('berjalan')"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Akan datang') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-sky-600 tabular-nums" v-text="hitung('belum')"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Selesai') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-500 tabular-nums" v-text="hitung('selesai')"></p>
        </div>
    </div>

    {{-- ===== Kalender ===== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-wrap items-center gap-3 px-4 sm:px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-1">
                <a href="{{ $tautan['sebelum'] }}" class="w-9 h-9 rounded-xl ring-1 ring-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50" title="{{ __('Bulan sebelumnya') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <a href="{{ $tautan['sesudah'] }}" class="w-9 h-9 rounded-xl ring-1 ring-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50" title="{{ __('Bulan berikutnya') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ $tautan['hariIni'] }}" class="ml-1 px-3 h-9 rounded-xl ring-1 ring-slate-200 inline-flex items-center text-xs font-bold text-slate-600 hover:bg-slate-50">{{ __('Hari ini') }}</a>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg font-extrabold text-slate-800 leading-tight">{{ $judul }}</h2>
                <p class="text-xs text-slate-500">{{ $sub }}</p>
            </div>
            <div class="ml-auto grid grid-cols-2 gap-1 rounded-2xl bg-slate-100 p-1 text-xs font-bold">
                <a href="{{ $tautan['masehi'] }}" class="px-3 py-1.5 rounded-xl text-center {{ $patokan === 'masehi' ? 'bg-[#0b332c] text-white' : 'text-slate-500 hover:text-slate-700' }}">{{ __('Masehi') }}</a>
                <a href="{{ $tautan['hijri'] }}" class="px-3 py-1.5 rounded-xl text-center {{ $patokan === 'hijri' ? 'bg-[#0b332c] text-white' : 'text-slate-500 hover:text-slate-700' }}">{{ __('Hijriyah') }}</a>
            </div>
        </div>

        {{-- Nama hari --}}
        <div class="grid grid-cols-7 bg-[#f8fbfa] border-b border-slate-100 text-center text-[11px] font-bold uppercase tracking-wide text-slate-500">
            @foreach ($namaHari as $i => $nh)
            <div class="py-2 {{ $i === 5 ? 'text-emerald-700' : '' }}"><span class="hidden sm:inline">{{ $nh }}</span><span class="sm:hidden">{{ app()->getLocale() === 'ar' ? $nh : mb_substr($nh, 0, 3) }}</span></div>
            @endforeach
        </div>

        {{-- Kotak hari --}}
        <div class="grid grid-cols-7">
            <div v-for="h in hari" :key="h.tanggal" @click="pilihHari(h)"
                class="min-h-[74px] sm:min-h-[108px] border-b border-r border-slate-100 p-1 sm:p-1.5 cursor-pointer transition hover:bg-emerald-50/50"
                :class="[h.di_bulan ? 'bg-white' : 'bg-slate-50/70', h.hari_ini ? 'ring-2 ring-inset ring-emerald-500' : '']">
                <div class="flex items-start justify-between gap-1">
                    <span class="text-sm sm:text-base font-extrabold tabular-nums leading-none"
                        :class="h.hari_ini ? 'text-emerald-700' : (h.di_bulan ? (h.jumat ? 'text-emerald-700' : 'text-slate-800') : 'text-slate-300')">
                        <span v-text="h.masehi"></span><span v-if="h.bulan_masehi" class="hidden sm:inline ml-0.5 text-[9px] font-semibold text-slate-400" v-text="h.bulan_masehi"></span>
                    </span>
                    <span class="text-right leading-none" :class="h.di_bulan ? 'text-amber-700' : 'text-slate-300'">
                        <span class="text-[11px] sm:text-xs font-bold tabular-nums" v-text="h.hijri"></span>
                        <span v-if="h.bulan_hijri" class="hidden sm:block text-[9px] font-semibold" v-text="h.bulan_hijri"></span>
                    </span>
                </div>
                <div class="mt-1 space-y-0.5">
                    <button v-for="k in kegiatanHari(h.tanggal).slice(0, 3)" :key="k.id" type="button" @click.stop="bukaUbah(k)"
                        class="block w-full truncate rounded-md px-1 sm:px-1.5 py-0.5 text-left text-[9px] sm:text-[11px] font-semibold ring-1"
                        :class="k.kelas.pita" :title="k.judul" v-text="k.judul"></button>
                    <p v-if="kegiatanHari(h.tanggal).length > 3" class="px-1 text-[10px] font-semibold text-slate-400"
                        v-text="'+' + (kegiatanHari(h.tanggal).length - 3) + ' ' + teks.lagi"></p>
                </div>
            </div>
        </div>

        {{-- Keterangan warna --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 px-4 sm:px-6 py-3 text-xs text-slate-500">
            <span v-for="j in jenis" :key="j.id" class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full" :class="j.kelas.titik"></span><span v-text="j.nama"></span>
            </span>
            <button v-if="!jenis.length" type="button" @click="bukaJenis" class="font-semibold text-emerald-700 hover:underline">{{ __('Belum ada jenis kegiatan. Atur jenis & warna') }}</button>
            <span class="inline-flex items-center gap-1.5 sm:ml-auto"><span class="font-bold text-slate-700">12</span> {{ __('Masehi') }} · <span class="font-bold text-amber-700">12</span> {{ __('Hijriyah') }}</span>
        </div>
    </div>

    {{-- ===== Daftar kegiatan ===== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-wrap items-center gap-3 px-4 sm:px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800">{{ __('Daftar Kegiatan') }}</h2>
            <div class="grid grid-cols-2 gap-1 rounded-2xl bg-slate-100 p-1 text-xs font-bold">
                <button type="button" @click="lingkup = 'bulan'" class="px-3 py-1.5 rounded-xl" :class="lingkup === 'bulan' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'">{{ __('Bulan ini') }}</button>
                <button type="button" @click="lingkup = 'semua'" class="px-3 py-1.5 rounded-xl" :class="lingkup === 'semua' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'">{{ __('Semua') }}</button>
            </div>
            <input type="search" v-model="cari" placeholder="{{ __('Cari kegiatan…') }}" class="w-full sm:w-56 sm:ml-auto {{ $input }} py-2">
        </div>

        <p v-if="!daftarTampil.length" class="px-6 py-10 text-center text-sm text-slate-400">{{ __('Belum ada kegiatan. Tekan Tambah Kegiatan atau klik salah satu tanggal di kalender.') }}</p>

        <ul class="divide-y divide-slate-100">
            <li v-for="k in daftarTampil" :key="k.id" class="px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-full" :class="k.kelas.titik"></span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-slate-800" v-text="k.judul"></p>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold ring-1" :class="k.kelas.pita" v-text="k.label_jenis"></span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-600" v-text="rentangMasehi(k)"></p>
                        <p class="text-xs text-amber-700" v-text="rentangHijriyah(k)"></p>
                        <p v-if="k.keterangan" class="mt-1 text-xs text-slate-500 whitespace-pre-line" v-text="k.keterangan"></p>
                    </div>
                </div>
                <div class="lg:w-64 shrink-0">
                    <div class="flex items-center justify-between text-[11px] font-semibold">
                        <span :class="warnaStatus(k)" v-text="teksKemajuan(k)"></span>
                        <span class="text-slate-400" v-text="k.kemajuan.lama + ' ' + teks.hari"></span>
                    </div>
                    <div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full" :class="k.kelas.garis" :style="{ width: k.kemajuan.persen + '%' }"></div>
                    </div>
                </div>
                <div class="flex gap-3 shrink-0 text-xs font-semibold">
                    <button type="button" @click="bukaUbah(k)" class="text-teal-700 hover:underline">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(k)" class="text-rose-600 hover:underline">{{ __('Hapus') }}</button>
                </div>
            </li>
        </ul>
    </div>
</div>

{{-- ================= Formulir Tambah / Ubah ================= --}}
<div v-if="form" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="form = null">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="form = null" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Kalender Pendidikan') }}</p>
            <h3 class="mt-1 text-xl font-extrabold" v-text="form.id ? teks.judulUbah : teks.judulTambah"></h3>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Nama kegiatan') }}</span>
                <input type="text" v-model="form.judul" maxlength="150" placeholder="{{ __('mis. Pembelajaran Kitab Jurumiyah') }}" class="mt-1 {{ $input }}"></label>

            <div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500">{{ __('Jenis') }}</span>
                    <button type="button" @click="bukaJenis" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Atur jenis') }}</button>
                </div>
                <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <button v-for="j in pilihanJenisForm" :key="j.id || 0" type="button" @click="form.jenis_id = j.id"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold ring-1 transition"
                        :class="form.jenis_id === j.id ? j.kelas.pita + ' ring-2' : 'bg-[#f8fbfa] ring-slate-200 text-slate-600 hover:bg-slate-50'">
                        <span class="w-2.5 h-2.5 shrink-0 rounded-full" :class="j.kelas.titik"></span><span class="truncate" v-text="j.nama"></span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Tanggal mulai (Masehi)') }}</span>
                    <input type="date" v-model="form.tanggal_mulai" @change="ambilHijriyah('mulai')" class="mt-1 {{ $input }}">
                    <span class="mt-1 block text-xs font-semibold text-amber-700 min-h-[1rem]" v-text="form.hijriMulai"></span></label>
                <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Tanggal selesai (Masehi)') }}</span>
                    <input type="date" v-model="form.tanggal_selesai" :min="form.tanggal_mulai" @change="ambilHijriyah('selesai')" class="mt-1 {{ $input }}">
                    <span class="mt-1 block text-xs font-semibold text-amber-700 min-h-[1rem]" v-text="form.hijriSelesai"></span></label>
            </div>
            <p v-if="lamaForm" class="-mt-1 text-xs text-slate-500" v-text="teks.lama + ': ' + lamaForm + ' ' + teks.hari"></p>

            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Keterangan (boleh kosong)') }}</span>
                <textarea v-model="form.keterangan" rows="3" maxlength="2000" class="mt-1 {{ $input }}"></textarea></label>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap items-center gap-2">
            <button v-if="form.id" type="button" @click="hapus(form)" class="text-sm font-bold text-rose-600 hover:underline">{{ __('Hapus') }}</button>
            <div class="ml-auto flex gap-2">
                <button type="button" @click="form = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="button" @click="simpan" :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                    v-text="menyimpan ? teks.menyimpan : teks.simpan"></button>
            </div>
        </div>
    </div>
</div>

{{-- ================= Jenis & Warna (dibuat admin sendiri) ================= --}}
<div v-if="aturJenis" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="aturJenis = null">
    <div class="w-full max-w-xl rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="aturJenis = null" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Kalender Pendidikan') }}</p>
            <h3 class="mt-1 text-xl font-extrabold">{{ __('Jenis & Warna') }}</h3>
            <p class="mt-1 text-sm text-white/70">{{ __('Tulis jenis kegiatan sendiri dan pilih warna penandanya.') }}</p>
        </div>

        <div class="p-5 sm:p-6 space-y-3">
            <p v-if="!aturJenis.length" class="rounded-2xl bg-[#f8fbfa] px-4 py-5 text-center text-sm text-slate-400">{{ __('Belum ada jenis. Tekan + Tambah jenis.') }}</p>
            <div v-for="(j, i) in aturJenis" :key="j.kunci" class="rounded-2xl ring-1 ring-slate-200 p-3">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 shrink-0 rounded-full" :class="kelasWarna(j.warna).titik"></span>
                    <input type="text" v-model="j.nama" maxlength="60" placeholder="{{ __('mis. Pembelajaran, Ujian, Libur') }}" class="{{ $input }} py-2">
                    <button type="button" @click="geserJenis(i, -1)" :disabled="i === 0" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-slate-200 text-slate-500 disabled:opacity-30" title="{{ __('Naik') }}">&uarr;</button>
                    <button type="button" @click="geserJenis(i, 1)" :disabled="i === aturJenis.length - 1" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-slate-200 text-slate-500 disabled:opacity-30" title="{{ __('Turun') }}">&darr;</button>
                    <button type="button" @click="aturJenis.splice(i, 1)" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-rose-200 text-rose-500 hover:bg-rose-50 flex items-center justify-center" title="{{ __('Hapus') }}">{!! $tutup !!}</button>
                </div>
                <div class="mt-2.5 flex flex-wrap gap-2 pl-6">
                    <button v-for="w in warna" :key="w.kunci" type="button" @click="j.warna = w.kunci" :title="w.label"
                        class="w-7 h-7 rounded-full flex items-center justify-center ring-offset-2 transition"
                        :class="[w.kelas.titik, j.warna === w.kunci ? 'ring-2 ring-slate-700' : 'hover:scale-110']">
                        <svg v-if="j.warna === w.kunci" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
            <button type="button" @click="tambahJenis" class="text-sm font-bold text-emerald-700 hover:underline">+ {{ __('Tambah jenis') }}</button>
            <p class="text-xs text-slate-400">{{ __('Jenis yang dihapus tidak menghapus kegiatannya; kegiatan itu menjadi Tanpa jenis.') }}</p>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap justify-end gap-2">
            <button type="button" @click="aturJenis = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button type="button" @click="simpanJenis" :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                v-text="menyimpan ? teks.menyimpan : teks.simpan"></button>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const CSRF = @json(csrf_token());
        const RUTE = {
            simpan: @js(route('admin.kalender.simpan')),
            ubah: @js(route('admin.kalender.ubah', '__ID__')),
            hapus: @js(route('admin.kalender.hapus', '__ID__')),
            hijriyah: @js(route('admin.kalender.hijriyah')),
            jenis: @js(route('admin.kalender.jenis')),
        };
        const WARNA = @json($warna);
        const KELAS_KOSONG = (WARNA.find((w) => w.kunci === 'slate') || WARNA[0]).kelas;
        let nomorBaru = 0; // kunci sementara baris jenis baru
        async function kirimJson(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.errors ? Object.values(json.errors)[0][0] : (json.message || @js(__('Gagal menyimpan. Coba lagi.'))));
            return json;
        }
        const gagal = (pesan) => (window.notifikasi ? window.notifikasi.gagal(pesan) : alert(pesan));
        const kecil = (t) => (t || '').toString().toLowerCase();
        const AWAL = @js($awal);
        const AKHIR = @js($akhir);

        Vue.createApp({
            data: () => ({
                hari: @json($hari),
                kegiatan: @json($kegiatan),
                jenis: @json($jenis),
                warna: WARNA,
                lingkup: 'bulan',
                cari: '',
                form: null,
                aturJenis: null,  // salinan daftar jenis saat jendela Jenis & Warna terbuka
                menyimpan: false,
                teks: {
                    lagi: @js(__('lagi')), hari: @js(__('hari')), lama: @js(__('Lama kegiatan')),
                    judulTambah: @js(__('Tambah Kegiatan')), judulUbah: @js(__('Ubah Kegiatan')),
                    simpan: @js(__('Simpan')), menyimpan: @js(__('Menyimpan…')),
                    berjalan: @js(__('Hari ke-:ke dari :lama · sisa :sisa hari')),
                    belum: @js(__('Dimulai :lagi hari lagi')), besok: @js(__('Dimulai besok')),
                    selesai: @js(__('Selesai')), hariTerakhir: @js(__('Hari terakhir')),
                    yakinHapus: @js(__('Hapus kegiatan ":judul" dari kalender?')),
                    tanpaJenis: @js(__('Tanpa jenis')),
                    namaJenisKosong: @js(__('Nama jenis wajib diisi.')),
                },
            }),
            computed: {
                // Tombol jenis di formulir kegiatan: semua jenis buatan admin + "Tanpa jenis"
                pilihanJenisForm() {
                    return [...this.jenis, { id: null, nama: this.teks.tanpaJenis, kelas: KELAS_KOSONG }];
                },
                daftarTampil() {
                    const k = kecil(this.cari.trim());
                    return this.kegiatan
                        .filter((x) => this.lingkup === 'semua' || (x.mulai <= AKHIR && x.selesai >= AWAL))
                        .filter((x) => !k || kecil(x.judul).includes(k) || kecil(x.keterangan).includes(k) || kecil(x.label_jenis).includes(k))
                        .slice().sort((a, b) => (a.mulai < b.mulai ? -1 : a.mulai > b.mulai ? 1 : a.id - b.id));
                },
                lamaForm() {
                    if (!this.form || !this.form.tanggal_mulai || !this.form.tanggal_selesai) return 0;
                    const n = Math.round((Date.parse(this.form.tanggal_selesai) - Date.parse(this.form.tanggal_mulai)) / 86400000) + 1;
                    return n > 0 ? n : 0;
                },
            },
            mounted() {
                window.addEventListener('keydown', (e) => {
                    if (e.key !== 'Escape') return;
                    if (this.aturJenis) this.aturJenis = null; else this.form = null;
                });
            },
            methods: {
                hitung(status) { return this.kegiatan.filter((k) => k.kemajuan.status === status).length; },
                kegiatanHari(tgl) { return this.kegiatan.filter((k) => k.mulai <= tgl && k.selesai >= tgl); },
                rentangMasehi(k) { return k.mulai === k.selesai ? k.mulai_teks : k.mulai_teks + ' – ' + k.selesai_teks; },
                rentangHijriyah(k) { return k.mulai === k.selesai ? k.mulai_hijriyah : k.mulai_hijriyah + ' – ' + k.selesai_hijriyah; },
                teksKemajuan(k) {
                    const m = k.kemajuan;
                    if (m.status === 'selesai') return this.teks.selesai;
                    if (m.status === 'belum') return m.lagi === 1 ? this.teks.besok : this.teks.belum.replace(':lagi', m.lagi);
                    if (m.sisa === 0) return this.teks.hariTerakhir + ' (' + m.hari_ke + '/' + m.lama + ')';
                    return this.teks.berjalan.replace(':ke', m.hari_ke).replace(':lama', m.lama).replace(':sisa', m.sisa);
                },
                warnaStatus(k) {
                    return { berjalan: 'text-emerald-700', belum: 'text-sky-700', selesai: 'text-slate-400' }[k.kemajuan.status];
                },
                // ===== Formulir =====
                bukaTambah(tanggal) {
                    const t = tanggal || '';
                    this.form = { id: null, judul: '', jenis_id: this.jenis.length ? this.jenis[0].id : null, tanggal_mulai: t, tanggal_selesai: t, keterangan: '', hijriMulai: '', hijriSelesai: '' };
                    if (t) { this.ambilHijriyah('mulai'); this.ambilHijriyah('selesai'); }
                },
                pilihHari(h) { this.bukaTambah(h.tanggal); },
                bukaUbah(k) {
                    this.form = {
                        id: k.id, judul: k.judul, jenis_id: k.jenis_id || null, tanggal_mulai: k.mulai, tanggal_selesai: k.selesai,
                        keterangan: k.keterangan || '', hijriMulai: k.mulai_hijriyah, hijriSelesai: k.selesai_hijriyah,
                    };
                },
                async ambilHijriyah(bagian) {
                    const f = this.form;
                    if (!f) return;
                    if (bagian === 'mulai' && f.tanggal_mulai && (!f.tanggal_selesai || f.tanggal_selesai < f.tanggal_mulai)) {
                        f.tanggal_selesai = f.tanggal_mulai;
                        this.ambilHijriyah('selesai');
                    }
                    const tgl = bagian === 'mulai' ? f.tanggal_mulai : f.tanggal_selesai;
                    const kunci = bagian === 'mulai' ? 'hijriMulai' : 'hijriSelesai';
                    if (!tgl) { f[kunci] = ''; return; }
                    try {
                        const res = await fetch(RUTE.hijriyah + '?tanggal=' + encodeURIComponent(tgl), { headers: { Accept: 'application/json' } });
                        const json = await res.json();
                        if (this.form === f) f[kunci] = json.hijriyah || '';
                    } catch (e) {
                        f[kunci] = '';
                    }
                },
                async simpan() {
                    const f = this.form;
                    this.menyimpan = true;
                    try {
                        const body = { judul: f.judul, jenis_id: f.jenis_id, tanggal_mulai: f.tanggal_mulai, tanggal_selesai: f.tanggal_selesai, keterangan: f.keterangan };
                        const json = f.id
                            ? await kirimJson(RUTE.ubah.replace('__ID__', f.id), 'PUT', body)
                            : await kirimJson(RUTE.simpan, 'POST', body);
                        this.kegiatan = [...this.kegiatan.filter((x) => x.id !== json.kegiatan.id), json.kegiatan];
                        window.notifikasi?.sukses(json.pesan);
                        this.form = null;
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
                async hapus(k) {
                    if (!confirm(this.teks.yakinHapus.replace(':judul', k.judul))) return;
                    try {
                        const json = await kirimJson(RUTE.hapus.replace('__ID__', k.id), 'DELETE');
                        this.kegiatan = this.kegiatan.filter((x) => x.id !== k.id);
                        if (this.form && this.form.id === k.id) this.form = null;
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    }
                },
                // ===== Jenis & Warna =====
                kelasWarna(kunci) { return (WARNA.find((w) => w.kunci === kunci) || {}).kelas || KELAS_KOSONG; },
                bukaJenis() {
                    this.aturJenis = this.jenis.map((j) => ({ kunci: 'j' + j.id, id: j.id, nama: j.nama, warna: j.warna }));
                    if (!this.aturJenis.length) this.tambahJenis();
                },
                tambahJenis() {
                    // warna berikutnya yang belum dipakai, supaya tiap jenis mudah dibedakan
                    const dipakai = this.aturJenis.map((j) => j.warna);
                    const w = WARNA.find((x) => !dipakai.includes(x.kunci)) || WARNA[0];
                    this.aturJenis.push({ kunci: 'baru' + (++nomorBaru), id: null, nama: '', warna: w.kunci });
                },
                geserJenis(i, arah) {
                    const list = this.aturJenis;
                    const j = i + arah;
                    if (j < 0 || j >= list.length) return;
                    [list[i], list[j]] = [list[j], list[i]];
                },
                async simpanJenis() {
                    const kirim = this.aturJenis
                        .map((j) => ({ id: j.id, nama: (j.nama || '').trim(), warna: j.warna }))
                        .filter((j) => j.id || j.nama);   // baris baru yang masih kosong diabaikan
                    if (kirim.some((j) => !j.nama)) return gagal(this.teks.namaJenisKosong);
                    this.menyimpan = true;
                    try {
                        const json = await kirimJson(RUTE.jenis, 'PUT', { jenis: kirim });
                        this.jenis = json.jenis;
                        this.kegiatan = json.kegiatan;
                        // formulir kegiatan yang sedang terbuka: jenis yang dihapus -> Tanpa jenis
                        if (this.form && this.form.jenis_id && !this.jenis.some((j) => j.id === this.form.jenis_id)) this.form.jenis_id = null;
                        if (this.form && !this.form.id && !this.form.jenis_id && this.jenis.length) this.form.jenis_id = this.jenis[0].id;
                        window.notifikasi?.sukses(json.pesan);
                        this.aturJenis = null;
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
            },
        }).mount('#halaman-kalender');
    });
</script>
@endpush
