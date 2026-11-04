{{--
    Admin > Landing Page > Pita Pengumuman
    Kabar singkat di paling atas landing page, tampil otomatis sesuai jadwal + hitung mundur.
    - Daftar pengumuman: status, naik/turun urutan, hidup/mati, ubah, hapus.
    - Pop-up Tambah/Ubah dengan pratinjau langsung (sama persis dengan tampilan di landing page).
    Semua interaksi diatur Vue (window.Vue dari Vite); simpan lewat fetch JSON ke PitaPengumumanController.
--}}
@extends('layouts.admin')

@section('title', __('Pita Pengumuman'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
$label = 'block text-xs font-semibold text-slate-600 mb-1.5';
@endphp

<div id="halaman-pita" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10">

    {{-- ===== Judul + tombol ===== --}}
    <x-header-admin :label="__('Landing Page')" :judul="__('Pita Pengumuman')"
        :keterangan="__('Kabar singkat yang tampil dan hilang sendiri sesuai jadwal. Pilih tempatnya: landing page, dasbor asatid, atau dasbor peserta.')">
        <x-slot:aksi>
        <a href="{{ route('landing') }}" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            {{ __('Lihat landing page') }}
        </a>
        <button type="button" @click="bukaTambah"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Tambah Pengumuman') }}
        </button>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div v-for="r in ringkasan" :key="r.label" class="{{ $kartu }} px-4 py-3.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400" v-text="r.label"></p>
            <p class="mt-1 text-2xl font-extrabold" :class="r.warna" v-text="r.nilai"></p>
        </div>
    </div>

    {{-- ===== Tampilan pita (berlaku untuk semua pengumuman) ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800">{{ __('Tampilan pita') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Letak dan ukuran pita di') }} <b>{{ __('landing page') }}</b> {{ __('(semua pengumuman landing). Di dasbor asatid & peserta, pita selalu tampil di atas isi halaman.') }}</p>
            </div>
            <button type="button" @click="simpanTampilan" :disabled="sibuk || !tampilanBerubah"
                class="px-4 py-2 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-40" v-text="tampilanBerubah ? @js(__('Simpan tampilan')) : @js(__('Tersimpan'))"></button>
        </div>

        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="space-y-4">
                <div>
                    <span class="{{ $label }}">{{ __('Letak') }}</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="(nama, kunci) in pilihanPosisi" :key="kunci" type="button" @click="tampilan.posisi = kunci"
                            class="rounded-xl px-3 py-2.5 text-sm font-semibold ring-1 transition-colors"
                            :class="tampilan.posisi === kunci ? 'bg-emerald-50 ring-emerald-500 text-emerald-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50'" v-text="nama"></button>
                    </div>
                </div>
                <div>
                    <span class="{{ $label }}">{{ __('Ukuran') }}</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="(nama, kunci) in pilihanUkuran" :key="kunci" type="button" @click="tampilan.ukuran = kunci"
                            class="rounded-xl px-3 py-2.5 text-sm font-semibold ring-1 transition-colors"
                            :class="tampilan.ukuran === kunci ? 'bg-emerald-50 ring-emerald-500 text-emerald-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50'" v-text="nama"></button>
                    </div>
                </div>
            </div>
            {{-- Contoh letak & ukuran (pakai pengumuman pertama, atau contoh) --}}
            <div>
                <span class="{{ $label }}">{{ __('Contoh') }}</span>
                <div v-for="pita in [pitaContoh]" :key="'contoh'">
                    @include('admin._pita-pratinjau')
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Daftar ===== --}}
    <section class="{{ $kartu }} overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <h2 class="font-bold text-slate-800">{{ __('Daftar pengumuman') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Urutan atas = tampil lebih dulu') }}</p>
        </div>
        {{-- Saring per tempat tampil --}}
        <div class="px-4 sm:px-6 pt-3 pb-1 flex gap-2 overflow-x-auto">
            <button v-for="t in tabTujuan" :key="t.kunci" type="button" @click="saring = t.kunci"
                class="shrink-0 inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-semibold ring-1 transition-colors"
                :class="saring === t.kunci ? 'bg-[#0f6b5c] text-white ring-[#0f6b5c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-text="t.nama"></span>
                <span class="rounded-full px-1.5 text-[11px]" :class="saring === t.kunci ? 'bg-white/20' : 'bg-slate-100 text-slate-500'" v-text="t.jumlah"></span>
            </button>
        </div>

        <div v-if="!daftarTampil.length" class="px-6 py-14 text-center">
            <span class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/></svg>
            </span>
            <p class="mt-3 font-semibold text-slate-700">{{ __('Belum ada pengumuman') }}</p>
            <p class="mt-1 text-sm text-slate-500" v-text="saring === 'semua' ? @js(__('Klik "Tambah Pengumuman" untuk membuat kabar pertama.')) : @js(__('Belum ada pengumuman untuk :tujuan.')).replace(':tujuan', tujuan[saring])"></p>
        </div>

        <ul v-else class="divide-y divide-slate-100">
            <li v-for="(p, i) in daftarTampil" :key="p.id" class="px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-3" :class="p.is_active ? '' : 'bg-slate-50/70'">
                {{-- Naik / turun --}}
                <div class="hidden lg:flex flex-col gap-1 shrink-0">
                    <button type="button" @click="geser(i, -1)" :disabled="i === 0 || sibuk" title="{{ __('Naikkan') }}" class="w-7 h-7 rounded-lg ring-1 ring-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                    </button>
                    <button type="button" @click="geser(i, 1)" :disabled="i === daftarTampil.length - 1 || sibuk" title="{{ __('Turunkan') }}" class="w-7 h-7 rounded-lg ring-1 ring-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                </div>

                {{-- Warna + isi --}}
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center shadow-sm" :class="[gayaPita(p).contoh, p.warna === 'sendiri' && terangPita(p) ? 'text-slate-900' : 'text-white']" :style="latarPita(p)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="ikonPita(p)"></path></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-semibold rounded-full px-2 py-0.5" :class="warnaTujuan[p.tujuan]" v-text="tujuan[p.tujuan]"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400" v-text="p.label"></span>
                            <span class="text-[11px] font-semibold rounded-full px-2 py-0.5 ring-1" :class="infoStatus(p).kelas" v-text="infoStatus(p).teks"></span>
                        </div>
                        <p class="mt-0.5 font-bold text-slate-800 leading-snug" v-text="p.judul"></p>
                        <p v-if="p.keterangan" class="text-sm text-slate-500 leading-snug" v-text="p.keterangan"></p>
                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span v-text="p.rentang"></span>
                            </span>
                            <span v-if="p.tampil_sejak_lokal" class="inline-flex items-center gap-1" v-text="@js(__('Tampil sejak')) + ' ' + formatLokal(p.tampil_sejak_lokal)"></span>
                            <span v-if="p.tombol_teks" class="inline-flex items-center gap-1 text-emerald-700" v-text="@js(__('Tombol:')) + ' ' + p.tombol_teks"></span>
                            <span v-if="p.hitung_mundur" class="text-slate-400">{{ __('Hitung mundur') }}</span>
                        </p>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="flex items-center gap-2 shrink-0 lg:pl-3">
                    <div class="flex lg:hidden gap-1 mr-auto">
                        <button type="button" @click="geser(i, -1)" :disabled="i === 0 || sibuk" class="px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-xs text-slate-600 disabled:opacity-30">{{ __('Naik') }}</button>
                        <button type="button" @click="geser(i, 1)" :disabled="i === daftarTampil.length - 1 || sibuk" class="px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-xs text-slate-600 disabled:opacity-30">{{ __('Turun') }}</button>
                    </div>
                    <button type="button" @click="hidupMati(p)" :disabled="sibuk" :title="p.is_active ? @js(__('Matikan')) : @js(__('Hidupkan'))"
                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors disabled:opacity-50" :class="p.is_active ? 'bg-emerald-500' : 'bg-slate-300'">
                        <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="p.is_active ? 'translate-x-5' : ''"></span>
                    </button>
                    <button type="button" @click="bukaUbah(p)" class="px-3 py-1.5 rounded-xl ring-1 ring-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(p)" :disabled="sibuk" class="px-3 py-1.5 rounded-xl ring-1 ring-rose-200 text-sm font-semibold text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                </div>
            </li>
        </ul>
    </section>

    {{-- ===== Pop-up Tambah / Ubah ===== --}}
    <div v-if="form" class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center bg-slate-900/50 p-0 sm:p-4" @click.self="tutupForm">
        <div class="w-full sm:max-w-3xl max-h-[94vh] overflow-y-auto bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl">
            <div class="sticky top-0 z-10 bg-white px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-800" v-text="form.id ? @js(__('Ubah Pengumuman')) : @js(__('Tambah Pengumuman'))"></h3>
                <button type="button" @click="tutupForm" class="w-9 h-9 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Pratinjau langsung --}}
            <div class="px-5 sm:px-6 pt-5">
                <p class="text-xs font-semibold text-slate-500 mb-2">
                    <span v-text="@js(__('Pratinjau di')) + ' ' + tujuan[form.tujuan]"></span>
                    <span v-if="form.tujuan === 'landing'" class="font-normal text-slate-400" v-text="' (' + pilihanPosisi[tampilan.posisi] + ', ' + (tampilan.ukuran === 'ringkas' ? @js(__('ringkas')) : @js(__('penuh'))) + ')'"></span>
                </p>
                <div v-if="form.tujuan === 'landing'">@include('admin._pita-pratinjau')</div>
                {{-- Pratinjau di dasbor asatid / peserta: pita di atas isi halaman, di bawah header --}}
                <div v-else class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-[#eef4f1]">
                    <div class="h-10 bg-white border-b border-slate-100 flex items-center px-4 gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100"></span><span class="w-24 h-2.5 rounded bg-slate-200"></span>
                        <span class="ml-auto w-6 h-6 rounded-full bg-slate-100"></span><span class="w-6 h-6 rounded-full bg-slate-200"></span>
                    </div>
                    <div class="p-3 space-y-2.5">
                        <div class="rounded-2xl overflow-hidden shadow ring-1 ring-black/5">
                            @include('partials.pita-pengumuman-tampilan')
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <span class="h-12 rounded-xl bg-white"></span><span class="h-12 rounded-xl bg-white"></span>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="simpan" class="px-5 sm:px-6 py-5 space-y-5">
                <div v-if="galat" class="p-3 rounded-2xl ring-1 ring-rose-200 bg-rose-50 text-rose-800 text-sm" v-text="galat"></div>

                {{-- Tempat tampil --}}
                <div>
                    <span class="{{ $label }}">{{ __('Tampil di') }} <span class="text-rose-500">*</span></span>
                    <div class="grid grid-cols-3 gap-2">
                        <button v-for="(nama, kunci) in tujuan" :key="kunci" type="button" @click="pilihTujuan(kunci)"
                            class="rounded-xl px-3 py-2.5 text-sm font-semibold ring-1 transition-colors"
                            :class="form.tujuan === kunci ? 'bg-emerald-50 ring-emerald-500 text-emerald-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50'" v-text="nama"></button>
                    </div>
                </div>

                {{-- Jenis: 3 bawaan + jenis tambahan buatan admin --}}
                <div>
                    <span class="{{ $label }}">{{ __('Jenis pengumuman') }}</span>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(nama, kunci) in jenis" :key="kunci" class="relative inline-flex">
                            <button type="button" @click="pilihJenis(kunci)"
                                class="rounded-xl px-4 py-2.5 text-sm font-semibold ring-1 transition-colors"
                                :class="[form.jenis === kunci ? 'bg-emerald-50 ring-emerald-500 text-emerald-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50', jenisBawaan.includes(kunci) ? '' : 'pr-8']" v-text="nama"></button>
                            <button v-if="!jenisBawaan.includes(kunci)" type="button" @click="hapusJenis(kunci)" :title="@js(__('Hapus jenis')) + ' ' + nama"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </span>
                        {{-- Tambah jenis baru --}}
                        <button v-if="!jenisBaru.buka" type="button" @click="bukaJenisBaru"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-emerald-700 ring-1 ring-dashed ring-emerald-300 border border-dashed border-emerald-300 hover:bg-emerald-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                            {{ __('Jenis baru') }}
                        </button>
                        <span v-else class="inline-flex items-center gap-1.5">
                            <input v-model="jenisBaru.nama" ref="isianJenisBaru" type="text" maxlength="20" placeholder="{{ __('cth. Ujian, Libur') }}"
                                @keydown.enter.prevent="tambahJenis" @keydown.esc.prevent="jenisBaru.buka = false"
                                class="w-40 rounded-xl border border-emerald-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <button type="button" @click="tambahJenis" :disabled="sibuk || !jenisBaru.nama.trim()"
                                class="rounded-xl bg-[#0f6b5c] px-3 py-2 text-sm font-bold text-white disabled:opacity-50">{{ __('Tambah') }}</button>
                            <button type="button" @click="jenisBaru.buka = false" class="rounded-xl px-2.5 py-2 text-sm text-slate-500 hover:bg-slate-100">{{ __('Batal') }}</button>
                        </span>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="sm:col-span-2">
                        <span class="{{ $label }}">{{ __('Judul') }} <span class="text-rose-500">*</span></span>
                        <input v-model="form.judul" type="text" maxlength="150" required class="{{ $isian }}" placeholder="{{ __('cth. Pendaftaran Dauroh Gelombang 1 dibuka') }}">
                    </label>
                    <label class="sm:col-span-2">
                        <span class="{{ $label }}">{{ __('Keterangan singkat') }} <span class="font-normal text-slate-400">{{ __('(boleh kosong)') }}</span></span>
                        <input v-model="form.keterangan" type="text" maxlength="255" class="{{ $isian }}" placeholder="{{ __('cth. Kuota terbatas, segera daftar') }}">
                    </label>
                    <label>
                        <span class="{{ $label }}">{{ __('Mulai (WIB)') }} <span class="text-rose-500">*</span></span>
                        <input v-model="form.mulai_lokal" type="datetime-local" required class="{{ $isian }}">
                    </label>
                    <label>
                        <span class="{{ $label }}">{{ __('Selesai (WIB)') }} <span class="text-rose-500">*</span></span>
                        <input v-model="form.selesai_lokal" type="datetime-local" required class="{{ $isian }}">
                    </label>
                    <label class="sm:col-span-2">
                        <span class="{{ $label }}">{{ __('Tampilkan lebih awal sejak') }} <span class="font-normal text-slate-400">{{ __('(boleh kosong)') }}</span></span>
                        <input v-model="form.tampil_sejak_lokal" type="datetime-local" class="{{ $isian }}">
                        <span class="block mt-1 text-xs text-slate-400">{{ __('Kalau diisi, pita sudah tampil sebelum mulai dengan tulisan "Dimulai dalam ...". Kalau kosong, pita tampil tepat saat mulai.') }}</span>
                    </label>
                </div>

                {{-- Tombol --}}
                <div class="rounded-2xl ring-1 ring-slate-200 p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-slate-700">{{ __('Tombol di pita') }} <span class="font-normal text-slate-400">{{ __('(boleh kosong)') }}</span></span>
                        <button type="button" @click="isiTombolDaftar" class="text-xs font-semibold text-emerald-700 hover:underline">{{ __('Pakai "Daftar sekarang"') }}</button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input v-model="form.tombol_teks" type="text" maxlength="40" class="{{ $isian }}" placeholder="{{ __('Tulisan tombol, cth. Daftar sekarang') }}">
                        <input v-model="form.tombol_url" type="text" maxlength="255" class="{{ $isian }}" placeholder="{{ __('Alamat, cth. /psb/daftar') }}">
                    </div>
                </div>

                {{-- Warna --}}
                <div>
                    <span class="{{ $label }}">{{ __('Warna pita') }}</span>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="w in warnaBawaan" :key="w" type="button" @click="form.warna = w"
                            class="inline-flex items-center gap-2 rounded-xl pl-1.5 pr-3 py-1.5 text-sm ring-1 transition-colors"
                            :class="form.warna === w ? 'ring-2 ring-emerald-500 bg-emerald-50/50 font-semibold text-slate-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50'">
                            <span class="w-6 h-6 rounded-lg" :class="gayaPita({ warna: w }).contoh"></span>
                            <span v-text="gayaPita({ warna: w }).nama"></span>
                        </button>
                        {{-- Warna sendiri: pilih bebas --}}
                        <label class="inline-flex items-center gap-2 rounded-xl pl-1.5 pr-3 py-1.5 text-sm ring-1 cursor-pointer transition-colors"
                            :class="form.warna === 'sendiri' ? 'ring-2 ring-emerald-500 bg-emerald-50/50 font-semibold text-slate-800' : 'ring-slate-200 text-slate-600 hover:bg-slate-50'"
                            @click="form.warna = 'sendiri'">
                            <input v-model="form.warna_hex" type="color" class="w-6 h-6 rounded-lg border-0 p-0 bg-transparent cursor-pointer" title="{{ __('Pilih warna') }}">
                            <span>{{ __('Warna sendiri') }}</span>
                            <span v-if="form.warna === 'sendiri'" class="font-mono text-xs text-slate-400" v-text="form.warna_hex"></span>
                        </label>
                    </div>
                </div>

                {{-- Pilihan --}}
                <div class="grid sm:grid-cols-2 gap-3">
                    <label class="flex items-center justify-between gap-3 rounded-2xl ring-1 ring-slate-200 px-4 py-3 cursor-pointer">
                        <span>
                            <span class="block text-sm font-semibold text-slate-700">{{ __('Hitung mundur') }}</span>
                            <span class="block text-xs text-slate-400">{{ __('Tampilkan sisa hari, jam, menit, detik') }}</span>
                        </span>
                        <input v-model="form.hitung_mundur" type="checkbox" class="w-5 h-5 rounded accent-emerald-600">
                    </label>
                    <label class="flex items-center justify-between gap-3 rounded-2xl ring-1 ring-slate-200 px-4 py-3 cursor-pointer">
                        <span>
                            <span class="block text-sm font-semibold text-slate-700">{{ __('Aktif') }}</span>
                            <span class="block text-xs text-slate-400">{{ __('Matikan untuk menyimpan tanpa menampilkan') }}</span>
                        </span>
                        <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded accent-emerald-600">
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="tutupForm" class="px-4 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                    <button type="submit" :disabled="sibuk" class="px-5 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-60" v-text="sibuk ? @js(__('Menyimpan...')) : @js(__('Simpan'))"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Pesan singkat ===== --}}
    <div v-if="toast" class="fixed bottom-24 lg:bottom-6 left-1/2 -translate-x-1/2 z-[90] px-4 py-2.5 rounded-2xl shadow-lg text-sm font-semibold text-white"
        :class="toast.galat ? 'bg-rose-600' : 'bg-emerald-700'" v-text="toast.teks"></div>
</div>
@endsection

@push('scripts')
@include('partials.pita-pengumuman-gaya')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const URL_DASAR = @json(route('admin.landing-pengumuman.index'));
        const TOKEN = @json(csrf_token());
        let JENIS = @json($jenis); // bawaan + tambahan (bisa bertambah dari form)
        // Dari isian datetime-local (WIB) -> ISO dengan zona +07:00
        const keIso = (v) => (v ? v + ':00+07:00' : '');
        const fmt = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        Vue.createApp({
            mixins: [window.mixinPita],
            data() {
                return {
                    daftar: @json($daftar),
                    jenis: JENIS,
                    jenisBawaan: @json($jenisBawaan),
                    jenisBaru: { buka: false, nama: '' },
                    tujuan: @json($tujuan), // landing | asatid | siswa
                    warnaTujuan: { landing: 'bg-emerald-50 text-emerald-700', asatid: 'bg-sky-50 text-sky-700', siswa: 'bg-violet-50 text-violet-700' },
                    saring: 'semua',
                    warna: @json($warna),
                    form: null,
                    galat: '',
                    sibuk: false,
                    toast: null,
                    // Tampilan pita (letak & ukuran) untuk semua pengumuman
                    tampilan: { ...@json($tampilan) },
                    tampilanTersimpan: @json($tampilan),
                    pilihanPosisi: @json($pilihanPosisi),
                    pilihanUkuran: @json($pilihanUkuran),
                    // dipakai template pita (pratinjau): tanpa tombol tutup & pindah
                    bisaTutup: false,
                    banyakPita: 1,
                };
            },
            computed: {
                daftarTampil() {
                    return this.saring === 'semua' ? this.daftar : this.daftar.filter((p) => p.tujuan === this.saring);
                },
                tabTujuan() {
                    return [{ kunci: 'semua', nama: @js(__('Semua')), jumlah: this.daftar.length }].concat(
                        Object.entries(this.tujuan).map(([kunci, nama]) => ({ kunci, nama, jumlah: this.daftar.filter((p) => p.tujuan === kunci).length })));
                },
                warnaBawaan() {
                    return this.warna.filter((w) => w !== 'sendiri');
                },
                tampilanBerubah() {
                    return this.tampilan.posisi !== this.tampilanTersimpan.posisi || this.tampilan.ukuran !== this.tampilanTersimpan.ukuran;
                },
                // Contoh di kartu "Tampilan pita": pengumuman pertama, atau contoh buatan
                pitaContoh() {
                    const p = this.daftar.find((x) => x.tujuan === 'landing');
                    if (p) return p;
                    const nanti = new Date(Date.now() + 3 * 86400000).toISOString();
                    return { jenis: 'pendaftaran', label: @js(__('Pendaftaran')), judul: @js(__('Pendaftaran Dauroh Gelombang 1 dibuka')), keterangan: '', warna: 'hijau',
                        hitung_mundur: true, tombol_teks: @js(__('Daftar sekarang')), tombol_url: '#', mulai: new Date().toISOString(), selesai: nanti, rentang: '10 Nov 2026, 08.00 – 20 Nov 2026, 23.59 WIB' };
                },
                // Pratinjau dari isian form
                pita() {
                    const f = this.form || {};
                    return {
                        ...f,
                        label: this.jenis[f.jenis] || f.jenis || @js(__('Info')),
                        mulai: keIso(f.mulai_lokal),
                        selesai: keIso(f.selesai_lokal),
                        rentang: this.rentangLokal(f.mulai_lokal, f.selesai_lokal),
                    };
                },
                ringkasan() {
                    const hitung = (s) => this.daftar.filter((p) => s.includes(p.status)).length;
                    return [
                        { label: @js(__('Tampil sekarang')), nilai: hitung(['berlangsung', 'akan']), warna: 'text-emerald-600' },
                        { label: @js(__('Terjadwal')), nilai: hitung(['menunggu']), warna: 'text-sky-600' },
                        { label: @js(__('Selesai')), nilai: hitung(['selesai']), warna: 'text-slate-400' },
                        { label: @js(__('Dimatikan')), nilai: hitung(['nonaktif']), warna: 'text-slate-400' },
                    ];
                },
            },
            methods: {
                ...metodeHalaman,
                formatLokal(v) {
                    return v ? fmt.format(new Date(keIso(v))) : '';
                },
                rentangLokal(m, s) {
                    if (!m || !s) return @js(__('Isi tanggal mulai & selesai'));
                    return this.formatLokal(m) + ' – ' + this.formatLokal(s) + ' WIB';
                },
                infoStatus(p) {
                    return {
                        berlangsung: { teks: @js(__('Sedang tampil')), kelas: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
                        akan: { teks: @js(__('Tampil · belum mulai')), kelas: 'bg-amber-50 text-amber-700 ring-amber-200' },
                        menunggu: { teks: @js(__('Terjadwal')), kelas: 'bg-sky-50 text-sky-700 ring-sky-200' },
                        selesai: { teks: @js(__('Selesai')), kelas: 'bg-slate-100 text-slate-500 ring-slate-200' },
                        nonaktif: { teks: @js(__('Dimatikan')), kelas: 'bg-slate-100 text-slate-500 ring-slate-200' },
                    }[p.status];
                },
                tampilkanToast(teks, galat = false) {
                    this.toast = { teks, galat };
                    clearTimeout(this.jedaToast);
                    this.jedaToast = setTimeout(() => (this.toast = null), 2800);
                },
                // Kirim ke server; daftar terbaru dikembalikan controller
                async kirim(metode, url, isi) {
                    this.sibuk = true;
                    try {
                        const res = await fetch(url, {
                            method: metode,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                            body: isi ? JSON.stringify(isi) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const pesan = data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan. Coba lagi.')));
                            throw new Error(pesan);
                        }
                        this.daftar = data.daftar;
                        this.tampilkanToast(data.pesan);
                        return true;
                    } catch (e) {
                        if (this.form) this.galat = e.message; else this.tampilkanToast(e.message, true);
                        return false;
                    } finally {
                        this.sibuk = false;
                    }
                },
                formBaru() {
                    const besok = new Date(Date.now() + 86400000);
                    const tgl = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta' }).format(besok); // YYYY-MM-DD
                    const tujuanAwal = this.saring !== 'semua' ? this.saring : 'landing';
                    const diLanding = tujuanAwal === 'landing';
                    return {
                        id: null, tujuan: tujuanAwal, jenis: diLanding ? 'pendaftaran' : 'info', judul: '', keterangan: '',
                        mulai_lokal: tgl + 'T08:00', selesai_lokal: '', tampil_sejak_lokal: '',
                        hitung_mundur: true, tombol_teks: diLanding ? 'Daftar sekarang' : '', tombol_url: diLanding ? '/psb/daftar' : '',
                        warna: diLanding ? 'hijau' : 'biru', warna_hex: '#0f766e', is_active: true,
                    };
                },
                bukaTambah() {
                    this.galat = '';
                    this.form = this.formBaru();
                },
                bukaUbah(p) {
                    this.galat = '';
                    this.form = {
                        id: p.id, tujuan: p.tujuan || 'landing', jenis: p.jenis, judul: p.judul, keterangan: p.keterangan || '',
                        mulai_lokal: p.mulai_lokal, selesai_lokal: p.selesai_lokal, tampil_sejak_lokal: p.tampil_sejak_lokal || '',
                        hitung_mundur: p.hitung_mundur, tombol_teks: p.tombol_teks || '', tombol_url: p.tombol_url || '',
                        warna: p.warna, warna_hex: p.warna_hex || '#0f766e', is_active: p.is_active,
                    };
                },
                tutupForm() {
                    this.form = null;
                },
                // Ganti tempat tampil: tombol "Daftar sekarang" hanya cocok untuk landing page
                pilihTujuan(kunci) {
                    this.form.tujuan = kunci;
                    if (!this.form.id && kunci !== 'landing' && this.form.tombol_url === '/psb/daftar') {
                        this.form.tombol_teks = '';
                        this.form.tombol_url = '';
                    }
                },
                // Ganti jenis: isi tombol & warna yang cocok bila masih bawaan
                pilihJenis(kunci) {
                    this.form.jenis = kunci;
                    if (!this.form.id && this.form.tujuan === 'landing') {
                        const bawaan = { pendaftaran: ['Daftar sekarang', '/psb/daftar', 'hijau'], dauroh: ['', '', 'emas'], info: ['', '', 'biru'] }[kunci];
                        if (bawaan) [this.form.tombol_teks, this.form.tombol_url, this.form.warna] = bawaan; // jenis tambahan: biarkan
                    }
                },
                bukaJenisBaru() {
                    this.jenisBaru = { buka: true, nama: '' };
                    this.$nextTick(() => this.$refs.isianJenisBaru?.focus());
                },
                // Kirim jenis baru ke server (pakai fetch sendiri: jawabannya daftar jenis, bukan daftar pengumuman)
                async kirimJenis(alamat, nama) {
                    this.sibuk = true;
                    try {
                        const res = await fetch(URL_DASAR + alamat, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                            body: JSON.stringify({ nama }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan.'))));
                        this.jenis = data.jenis;
                        this.tampilkanToast(data.pesan);
                        return data;
                    } catch (e) {
                        this.galat = e.message;
                        return null;
                    } finally {
                        this.sibuk = false;
                    }
                },
                async tambahJenis() {
                    const nama = this.jenisBaru.nama.trim();
                    if (!nama) return;
                    const data = await this.kirimJenis('/jenis', nama);
                    if (data) {
                        this.jenisBaru = { buka: false, nama: '' };
                        this.pilihJenis(data.nama); // langsung terpilih
                    }
                },
                async hapusJenis(nama) {
                    if (!confirm(@js(__('Hapus jenis ":nama" dari pilihan?')).replace(':nama', nama))) return;
                    if (await this.kirimJenis('/jenis/hapus', nama) && this.form.jenis === nama) {
                        this.form.jenis = 'info';
                    }
                },
                isiTombolDaftar() {
                    this.form.tombol_teks = 'Daftar sekarang';
                    this.form.tombol_url = '/psb/daftar';
                },
                async simpanTampilan() {
                    if (await this.kirim('POST', URL_DASAR + '/tampilan', this.tampilan)) {
                        this.tampilanTersimpan = { ...this.tampilan };
                    }
                },
                async simpan() {
                    this.galat = '';
                    const isi = { ...this.form };
                    const ok = this.form.id
                        ? await this.kirim('PUT', URL_DASAR + '/' + this.form.id, isi)
                        : await this.kirim('POST', URL_DASAR, isi);
                    if (ok) this.form = null;
                },
                hidupMati(p) {
                    this.kirim('PATCH', URL_DASAR + '/' + p.id + '/toggle');
                },
                hapus(p) {
                    if (!confirm(@js(__('Hapus pengumuman ":judul"?')).replace(':judul', p.judul))) return;
                    this.kirim('DELETE', URL_DASAR + '/' + p.id);
                },
                geser(i, arah) {
                    const j = i + arah;
                    const tampil = this.daftarTampil;
                    if (j < 0 || j >= tampil.length) return;
                    const ids = this.daftar.map((p) => p.id);
                    const a = ids.indexOf(tampil[i].id);
                    const b = ids.indexOf(tampil[j].id);
                    [ids[a], ids[b]] = [ids[b], ids[a]];
                    this.kirim('POST', URL_DASAR + '/urutan', { ids });
                },
            },
        }).mount('#halaman-pita');
    });
</script>
@endpush
