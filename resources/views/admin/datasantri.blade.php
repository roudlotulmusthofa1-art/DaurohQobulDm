{{--
View: Data Santri.
Data dari database (AdminSantriController@index):
- $santri     : pendaftar berstatus "diterima", lengkap dengan nama kelas & kamar dari Master Data
- $kelasStats : jumlah santri per kelas (Master Data > Kelas)
- $tanpaKelas : jumlah santri yang belum ditempatkan di kelas
Vue 3 (window.Vue dari resources/js/app.js) hanya mengatur pencarian & centang.
--}}
@extends('layouts.admin')

@section('title', __('Data Santri'))

@section('content')
<div id="data-santri-app">


    {{-- ================= HEADER: DATA SANTRI =================
         Gaya sama dengan header Data Asatid & Data Admin (kartu hijau tua + pola titik). --}}
    <div class="relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7 mb-6 mx-4 md:mx-10 lg:mx-20 mt-8">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-end gap-5">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Manajemen Data') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Data Santri') }}</h1>
                <p class="mt-1.5 text-sm text-white/70">{{ __('Santri yang sudah diterima: atur kelas, kamar, dan cetak kartu QR.') }}</p>
                <span class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-white/10 ring-1 ring-white/15 px-4 py-2 text-sm">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    {{ __('Total') }} <b class="font-extrabold">@{{ totalSantri }}</b> {{ __('Santri') }}
                </span>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- Tambah Cepat: form singkat, langsung jadi santri --}}
                <button type="button" @click="bukaTambahCepat"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Tambah Cepat') }}
                </button>

                {{-- Mode Detail: formulir PSB lengkap di tab baru, lalu diterima lewat Data Masuk Peserta --}}
                <a href="{{ route('psb.daftar') }}" target="_blank" rel="noopener"
                    title="{{ __('Buka formulir pendaftaran lengkap (tab baru). Setelah dikirim, terima lewat menu Data Masuk Peserta.') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-400 text-[#0b332c] text-sm font-bold hover:bg-emerald-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    {{ __('Tambah Santri (Mode Detail)') }}
                </a>

                {{-- Kelola Massal: munculkan centang + panel pindah kelas/kamar --}}
                <button type="button" @click="toggleMassal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-sky-400 text-[#0b332c] text-sm font-bold hover:bg-sky-300 transition-colors"
                    :class="kelolaMassal ? 'ring-2 ring-white ring-offset-2 ring-offset-[#0b332c]' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span v-text="kelolaMassal ? @js(__('Selesai Kelola')) : @js(__('Kelola Massal'))"></span>
                </button>

                {{-- Cetak Kartu QR semua santri (tab baru, siap print A4) --}}
                <a :href="urlKartuQr" target="_blank" rel="noopener" title="{{ __('Cetak kartu QR semua santri') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4M20 17v3"/></svg>
                    {{ __('Cetak Kartu QR') }}
                </a>
            </div>
        </div>
    </div>

    {{-- ================= KARTU KELAS & KAMAR =================
         Kartu kecil yang bisa diklik. Klik kartu -> panel di bawahnya menampilkan anggotanya
         (kamar: lengkap dengan denah kasur). Klik lagi kartu yang sama untuk menutup. --}}
    <div class="mb-6 mx-4 md:mx-10 lg:mx-20 mt-8 space-y-4">
        {{-- ----- Kelas ----- --}}
        <div>
            <p class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z" /><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5" /></svg>
                {{ __('Kelas') }} <span class="font-normal normal-case tracking-normal text-gray-400">· {{ __('klik untuk melihat anggota') }}</span>
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2.5">
                <button v-for="kelas in kelasStats" :key="'kelas-' + kelas.id" type="button" @click="bukaGrup('kelas', kelas.id)"
                    :style="{ '--aksen': kelas.aksen }"
                    class="group relative overflow-hidden rounded-xl p-3 text-left text-white
                           bg-[linear-gradient(145deg,#0f4238_0%,#0b332c_60%,#0a2b25_100%)]
                           ring-1 shadow-[0_6px_18px_-12px_rgba(11,51,44,.7)] transition duration-300 hover:-translate-y-0.5"
                    :class="aktif('kelas', kelas.id) ? 'ring-2 ring-(--aksen) -translate-y-0.5 shadow-[0_14px_30px_-14px_var(--aksen)]' : 'ring-white/10 hover:ring-(--aksen)'">
                    <span class="pointer-events-none absolute -right-6 -top-6 h-16 w-16 rounded-full bg-(--aksen) opacity-25 blur-xl"></span>
                    <span class="relative flex items-center justify-between gap-2">
                        <span class="min-w-0 truncate text-xs font-semibold text-white/75" :title="kelas.nama" v-text="kelas.nama"></span>
                        <span class="h-2 w-2 shrink-0 rounded-full bg-(--aksen)"></span>
                    </span>
                    <span class="relative mt-1 block text-xl font-bold leading-none">
                        <span v-text="kelas.jumlah"></span> <span class="text-[10px] font-normal text-white/45">{{ __('santri') }}</span>
                    </span>
                    <span class="relative mt-2 block h-1 rounded-full bg-white/10 overflow-hidden">
                        <span class="block h-full rounded-full bg-(--aksen)" :style="{ width: kelas.persen + '%' }"></span>
                    </span>
                </button>

                {{-- Santri tanpa kelas --}}
                <button v-if="tanpaKelas > 0" type="button" @click="bukaGrup('kelas', 'kosong')"
                    class="relative overflow-hidden rounded-xl p-3 text-left text-white bg-[linear-gradient(145deg,#0f4238_0%,#0b332c_60%,#0a2b25_100%)] border border-dashed transition duration-300 hover:-translate-y-0.5"
                    :class="aktif('kelas', 'kosong') ? 'border-amber-300 ring-2 ring-amber-300' : 'border-amber-300/60'">
                    <span class="block truncate text-xs font-semibold text-amber-200">{{ __('Belum ada kelas') }}</span>
                    <span class="mt-1 block text-xl font-bold leading-none"><span v-text="tanpaKelas"></span> <span class="text-[10px] font-normal text-white/45">{{ __('santri') }}</span></span>
                    <span class="mt-2 block text-[10px] text-amber-300/80 truncate">{{ __('Atur lewat Kelola Massal') }}</span>
                </button>

                <a v-if="kelasStats.length === 0" href="{{ route('admin.master-data.index', ['tab' => 'kelas']) }}"
                    class="col-span-2 sm:col-span-4 lg:col-span-6 flex items-center rounded-xl border-2 border-dashed border-gray-300 p-3 text-sm text-gray-500 hover:bg-white/60">
                    {{ __('Belum ada kelas di Master Data. Klik di sini untuk menambahkan kelas.') }}
                </a>
            </div>
        </div>

        {{-- ----- Kamar ----- --}}
        <div>
            <p class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16" /><path d="M2 8h18a2 2 0 0 1 2 2v10" /><path d="M2 17h20" /><path d="M6 8v9" /></svg>
                {{ __('Kamar') }} <span class="font-normal normal-case tracking-normal text-gray-400">· {{ __('klik untuk melihat anggota & denah') }}</span>
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2.5">
                <button v-for="kamar in kamarStats" :key="'kamar-' + kamar.id" type="button" @click="bukaGrup('kamar', kamar.id)"
                    class="group relative overflow-hidden rounded-xl bg-white p-3 text-left ring-1 shadow-[0_6px_18px_-14px_rgba(11,51,44,.5)] transition duration-300 hover:-translate-y-0.5"
                    :class="aktif('kamar', kamar.id) ? 'ring-2 ring-amber-400 -translate-y-0.5 shadow-[0_14px_30px_-16px_rgba(245,158,11,.7)]' : 'ring-gray-200 hover:ring-amber-300'">
                    <span class="flex items-center justify-between gap-2">
                        <span class="min-w-0 truncate text-xs font-semibold text-gray-600" :title="kamar.nama" v-text="kamar.nama"></span>
                        <span v-if="kamar.penuh" class="shrink-0 rounded-full bg-rose-50 px-1.5 py-0.5 text-[9px] font-bold text-rose-600">{{ __('Penuh') }}</span>
                    </span>
                    <span class="mt-1 block text-xl font-bold leading-none text-gray-800">
                        <span v-text="kamar.jumlah"></span><span class="text-xs font-semibold text-gray-400" v-text="kamar.kapasitas ? ' / ' + kamar.kapasitas : ''"></span>
                        <span class="text-[10px] font-normal text-gray-400">{{ __('santri') }}</span>
                    </span>
                    <span class="mt-2 block h-1 rounded-full bg-gray-100 overflow-hidden">
                        <span class="block h-full rounded-full" :class="kamar.penuh ? 'bg-rose-400' : 'bg-amber-400'" :style="{ width: kamar.persen + '%' }"></span>
                    </span>
                </button>

                <button v-if="tanpaKamar > 0" type="button" @click="bukaGrup('kamar', 'kosong')"
                    class="rounded-xl bg-amber-50/60 p-3 text-left border border-dashed transition duration-300 hover:-translate-y-0.5"
                    :class="aktif('kamar', 'kosong') ? 'border-amber-400 ring-2 ring-amber-300' : 'border-amber-300'">
                    <span class="block truncate text-xs font-semibold text-amber-700">{{ __('Belum ada kamar') }}</span>
                    <span class="mt-1 block text-xl font-bold leading-none text-amber-800"><span v-text="tanpaKamar"></span> <span class="text-[10px] font-normal text-amber-600/70">{{ __('santri') }}</span></span>
                    <span class="mt-2 block text-[10px] text-amber-600/80 truncate">{{ __('Atur lewat Kelola Massal') }}</span>
                </button>

                <a v-if="kamarStats.length === 0" href="{{ route('admin.master-data.index', ['tab' => 'kamar']) }}"
                    class="col-span-2 sm:col-span-4 lg:col-span-6 flex items-center rounded-xl border-2 border-dashed border-gray-300 p-3 text-sm text-gray-500 hover:bg-white/60">
                    {{ __('Belum ada kamar di Master Data. Klik di sini untuk menambahkan kamar.') }}
                </a>
            </div>
        </div>

        {{-- ----- Panel anggota (muncul setelah kartu diklik) ----- --}}
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 -translate-y-2">
            <section v-if="grup" class="rounded-2xl bg-white ring-1 ring-gray-200 shadow-[0_18px_40px_-24px_rgba(11,51,44,.45)] overflow-hidden">
                <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-gray-100"
                    :class="grup.jenis === 'kelas' ? 'bg-[linear-gradient(145deg,#0f4238_0%,#0b332c_100%)] text-white' : 'bg-amber-50'">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                        :class="grup.jenis === 'kelas' ? 'bg-white/10 text-emerald-300' : 'bg-amber-400 text-white'">
                        <svg v-if="grup.jenis === 'kelas'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z" /><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5" /></svg>
                        <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16" /><path d="M2 8h18a2 2 0 0 1 2 2v10" /><path d="M2 17h20" /><path d="M6 8v9" /></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider" :class="grup.jenis === 'kelas' ? 'text-white/60' : 'text-amber-700'"
                            v-text="grup.jenis === 'kelas' ? @js(__('Anggota kelas')) : @js(__('Anggota kamar'))"></p>
                        <h3 class="truncate text-lg font-bold" :class="grup.jenis === 'kelas' ? '' : 'text-gray-800'" v-text="grup.nama"></h3>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="grup.jenis === 'kelas' ? 'bg-white/10 text-white' : 'bg-white text-amber-700 ring-1 ring-amber-200'"
                        v-text="grup.anggota.length + ' ' + @js(__('santri')) + (grup.kapasitas ? ' / ' + grup.kapasitas + ' ' + @js(__('kasur')) : '')"></span>
                    <button type="button" @click="tampilkanDiTabel"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="grup.jenis === 'kelas' ? 'bg-white/10 hover:bg-white/20' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'">{{ __('Tampilkan di tabel') }}</button>
                    <button type="button" @click="grupAktif = null" title="{{ __('Tutup') }}"
                        class="flex h-8 w-8 items-center justify-center rounded-lg transition"
                        :class="grup.jenis === 'kelas' ? 'hover:bg-white/10' : 'text-gray-500 hover:bg-white'">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-5" :class="grup.denah ? 'grid gap-6 lg:grid-cols-5' : ''">
                    {{-- Denah kasur (khusus kamar) --}}
                    <div v-if="grup.denah" class="lg:col-span-3">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ __('Denah kamar') }}</p>
                            <a href="{{ route('admin.master-data.index', ['tab' => 'kamar']) }}" class="text-xs font-medium text-emerald-700 hover:underline">{{ __('Atur denah di Master Data') }} &rsaquo;</a>
                        </div>
                        <div class="rounded-2xl bg-amber-50/50 p-3 ring-1 ring-amber-100">
                            <div class="grid gap-2" :style="{ gridTemplateColumns: 'repeat(' + grup.kolom + ', minmax(0, 1fr))' }">
                                <component v-for="kasur in grup.denah" :key="kasur.nomor" :is="kasur.santri ? 'a' : 'div'"
                                    :href="kasur.santri ? kasur.santri.detail_url : undefined"
                                    :title="kasur.santri ? @js(__('Kasur')) + ' ' + kasur.nomor + ': ' + kasur.santri.nama : @js(__('Kasur')) + ' ' + kasur.nomor + ': ' + @js(__('kosong'))"
                                    class="relative flex flex-col items-center rounded-xl border-2 px-1.5 pb-2 pt-1.5 text-center transition"
                                    :class="kasur.santri ? 'border-amber-200 bg-white shadow-sm hover:-translate-y-0.5 hover:border-amber-400' : 'border-dashed border-gray-300 bg-white/60'">
                                    <span class="absolute left-1.5 top-1 text-[10px] font-semibold text-gray-400" v-text="kasur.nomor"></span>
                                    <span class="mb-1.5 h-1.5 w-1/2 rounded-full" :class="kasur.santri ? 'bg-amber-200' : 'bg-gray-200'"></span>
                                    <img v-if="kasur.santri && kasur.santri.foto" :src="kasur.santri.foto" alt="" class="h-9 w-9 rounded-full object-cover">
                                    <span v-else-if="kasur.santri" class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700" v-text="inisial(kasur.santri.nama)"></span>
                                    <span v-else class="flex h-9 w-9 items-center justify-center text-[10px] text-gray-300">{{ __('kosong') }}</span>
                                    <span class="mt-1 w-full truncate text-[11px] font-medium" :class="kasur.santri ? 'text-gray-800' : 'text-gray-400'"
                                        v-text="kasur.santri ? kasur.santri.nama : @js(__('Kosong'))"></span>
                                </component>
                            </div>
                            <div class="mt-2 flex justify-center">
                                <span class="rounded-b-lg border-x-2 border-b-2 border-amber-300 bg-amber-50 px-6 py-0.5 text-[10px] font-medium text-amber-700">{{ __('Pintu') }}</span>
                            </div>
                        </div>
                        <p v-if="grup.tanpaKasur" class="mt-2 text-xs text-amber-700"
                            v-text="grup.tanpaKasur + ' ' + @js(__('santri di kamar ini belum mendapat nomor kasur. Atur di detail santri (Persyaratan & Ketentuan) atau di denah Master Data.'))"></p>
                    </div>
                    <p v-else-if="grup.jenis === 'kamar' && grup.id !== 'kosong'" class="mb-4 rounded-xl bg-gray-50 p-3 text-sm text-gray-500">
                        {{ __('Kapasitas kamar ini belum diatur, jadi denah belum bisa ditampilkan.') }}
                        <a href="{{ route('admin.master-data.index', ['tab' => 'kamar']) }}" class="font-medium text-emerald-700 hover:underline">{{ __('Atur di Master Data') }}</a>.
                    </p>

                    {{-- Daftar anggota --}}
                    <div :class="grup.denah ? 'lg:col-span-2' : ''">
                        <p v-if="grup.denah" class="mb-3 text-sm font-semibold text-gray-800">{{ __('Daftar anggota') }}</p>
                        <div v-if="grup.anggota.length" class="grid gap-2" :class="grup.denah ? '' : 'sm:grid-cols-2 lg:grid-cols-3'">
                            <a v-for="santri in grup.anggota" :key="santri.id" :href="santri.detail_url"
                                class="flex items-center gap-3 rounded-xl p-2.5 ring-1 ring-gray-100 bg-gray-50/60 transition hover:bg-white hover:ring-emerald-200 hover:shadow-sm">
                                <img :data-perbesar="santri.nama" v-if="santri.foto" :src="santri.foto" alt="" class="h-10 w-10 shrink-0 rounded-full object-cover">
                                <span v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700" v-text="inisial(santri.nama)"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-800" v-text="santri.nama"></span>
                                    <span class="block truncate text-[11px] text-gray-400" dir="auto" v-text="santri.nama_arab || santri.nomor"></span>
                                </span>
                                <span v-if="grup.jenis === 'kelas'" class="shrink-0 text-[11px] font-medium rounded-md px-2 py-0.5"
                                    :class="santri.kamar ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-400'"
                                    v-text="santri.kamar ? santri.kamar + (santri.nomor_kasur ? ' · ' + @js(__('Kasur')) + ' ' + santri.nomor_kasur : '') : @js(__('Tanpa kamar'))"></span>
                                <span v-else class="shrink-0 text-[11px] font-bold rounded-md px-2 py-0.5"
                                    :class="santri.nomor_kasur ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-400'"
                                    v-text="santri.nomor_kasur ? @js(__('Kasur')) + ' ' + santri.nomor_kasur : @js(__('Belum ada kasur'))"></span>
                            </a>
                        </div>
                        <p v-else class="py-8 text-center text-sm text-gray-400">{{ __('Belum ada santri.') }}</p>
                    </div>
                </div>
            </section>
        </transition>
    </div>

    {{-- ================= SEARCH + FILTER ================= --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4 mx-4 md:mx-10 lg:mx-20">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" v-model="pencarian" placeholder="{{ __('Cari santri (nama latin/Arab, no. pendaftaran, kelas, kamar, wali)...') }}"
                class="w-full border border-ribath-green-light rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green focus:border-transparent">
        </div>
        <button type="button" @click="filterTerbuka = !filterTerbuka"
                    class="inline-flex items-center justify-center gap-2 border rounded-lg px-4 py-2.5 text-sm font-medium transition"
                    :class="jumlahFilterAktif ? 'border-ribath-green bg-ribath-green text-white' : 'border-ribath-green-light text-gray-600 hover:bg-gray-50'">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            {{ __('Filter') }}
                        <span v-if="jumlahFilterAktif" class="rounded-full bg-white px-1.5 text-[11px] font-bold text-ribath-green"
                            v-text="jumlahFilterAktif"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </button>
    </div>
{{-- ================= PANEL FILTER ================= --}}
    <div v-if="filterTerbuka"
        class="mb-4 mx-4 md:mx-10 lg:mx-20 rounded-xl border border-ribath-green-light bg-white px-4 py-3 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="text-xs font-medium text-gray-500">{{ __('Kelas') }}
                <select v-model="filter.kelas"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">{{ __('Semua kelas') }}</option>
                    <option value="kosong">{{ __('Belum ada kelas') }}</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                </select>
            </label>
            <label class="text-xs font-medium text-gray-500">{{ __('Kamar') }}
                <select v-model="filter.kamar"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">{{ __('Semua kamar') }}</option>
                    <option value="kosong">{{ __('Belum ada kamar') }}</option>
                    <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                </select>
            </label>
            <label class="text-xs font-medium text-gray-500">{{ __('Status') }}
                <select v-model="filter.status"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">{{ __('Semua status') }}</option>
                    <option value="aktif">{{ __('Aktif') }}</option>
                    <option value="belum punya akun">{{ __('Belum punya akun') }}</option>
                </select>
            </label>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>{{ __('Menampilkan') }} <b v-text="santriTersaring.length"></b> {{ __('dari') }} <b v-text="santriList.length"></b> {{ __('santri') }}</span>
            <button type="button" @click="filter = { kelas: '', kamar: '', status: '' }"
                class="font-medium text-red-600 hover:underline">{{ __('Reset filter') }}</button>
        </div>
    </div>
    {{-- ================= PANEL KELOLA MASSAL ================= --}}
    <div v-if="kelolaMassal"
        class="mb-4 mx-4 md:mx-10 lg:mx-20 xl:mx-50 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            <p class="text-sm font-medium text-purple-800 shrink-0">
                <span v-text="terpilih.length"></span> {{ __('santri dipilih') }}
            </p>
            <div class="flex flex-1 flex-col sm:flex-row gap-2">
                <select v-model="massal.kelas_id" class="flex-1 rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm">
                    <option :value="null">{{ __('Kelas: tidak diubah') }}</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                </select>
                <select v-model="massal.kamar_id" class="flex-1 rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm">
                    <option :value="null">{{ __('Kamar: tidak diubah') }}</option>
                    <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                </select>
            </div>
            <button type="button" @click="terapkanMassal" :disabled="massal.loading"
                class="shrink-0 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-60"
                v-text="massal.loading ? @js(__('Menyimpan…')) : @js(__('Terapkan'))"></button>
            {{-- Cetak kartu QR santri yang dicentang --}}
            <button type="button" @click="cetakQrTerpilih" :disabled="!terpilih.length"
                class="shrink-0 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4M20 17v3"/></svg>
                <span v-text="@js(__('Cetak QR')) + ' (' + terpilih.length + ')'"></span>
            </button>
        </div>
        <p v-if="massal.error" class="mt-2 text-xs text-red-600" v-text="massal.error"></p>
        <p class="mt-1 text-xs text-purple-700/70">{{ __('Centang santri di tabel, pilih kelas dan/atau kamar tujuan, lalu klik Terapkan. Atau klik Cetak QR untuk mencetak kartu santri yang dicentang.') }}</p>
    </div>

    {{-- ================= TABEL SANTRI ================= --}}
    <div
        class="bg-white border border-ribath-green-light rounded-xl overflow-hidden shadow-sm mx-4 md:mx-10 lg:mx-20">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-ribath-green-light/30 text-left text-gray-800 border-b border-ribath-green-light/50">
                        <th v-if="kelolaMassal" class="px-4 py-5 w-10">
                            <label class="relative inline-flex w-5 h-5 cursor-pointer">
                                <input type="checkbox" v-model="pilihSemua" @change="toggleSemua" class="peer sr-only">

                                <span
                                    class="absolute inset-0 rounded-full border-2 border-gray-300 bg-white
                                         peer-checked:border-ribath-green peer-checked:bg-ribath-green-light
                                         peer-focus:ring-2 peer-focus:ring-ribath-green-light peer-focus:ring-offset-1">
                                </span>

                                <svg class="pointer-events-none absolute inset-0 w-5 h-5 p-0.5 text-white
                                       opacity-0 peer-checked:opacity-100" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="4">
                                    <path d="M5 12l4 4L19 7" />
                                </svg>
                            </label>
                        </th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('No') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Nama Santri') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Kelas') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Kamar') }}</th>
                        <th class="px-4 py-5 font-medium text-base whitespace-nowrap">{{ __('No. Kasur') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Tanggal Masuk') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Wali Santri') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Status') }}</th>
                        <th class="px-4 py-5 font-medium text-base">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(santri, index) in santriTersaring" :key="santri.id"
                        class="border-b border-ribath-green-light/40 last:border-0 hover:bg-ribath-green/5 transition">
                        <td v-if="kelolaMassal" class="px-4 py-3">
                            <label class="relative inline-flex w-4 h-4 cursor-pointer">
                                <input type="checkbox" :value="santri.id" v-model="terpilih" class="peer sr-only">

                                <span
                                    class="absolute inset-0 rounded-full border-2 border-gray-300 bg-white
                                             peer-checked:border-ribath-green peer-checked:bg-ribath-green-light
                                             peer-focus:ring-2 peer-focus:ring-ribath-green-light peer-focus:ring-offset-1">
                                </span>

                                <svg class="pointer-events-none absolute inset-0 w-4 h-4 p-0.5 text-white
                                           opacity-0 peer-checked:opacity-100" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="4">
                                    <path d="M5 12l4 4L19 7" />
                                </svg>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-gray-800">@{{ index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                {{-- Pas foto santri. Kalau belum ada / gagal dimuat -> ikon orang --}}
                                                                <img data-perbesar v-if="santri.foto" :src="santri.foto" :alt="@js(__('Foto')) + ' ' + santri.nama" v-on:error="santri.foto = null" loading="lazy"
                                                                    class="w-10 h-10 rounded-full object-cover shrink-0 ring-2 ring-ribath-green-light/60">
                                                                <div v-else class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <circle cx="12" cy="8" r="4" />
                                                                        <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
                                                                    </svg>
                                                                </div>
                                <div>
                                    <span class="block text-gray-700">@{{ santri.nama }}</span>
                                    <span v-if="santri.nama_arab" class="block text-sm text-gray-500" dir="rtl" lang="ar">@{{ santri.nama_arab }}</span>

                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="santri.kelas" class="inline-block text-xs font-medium px-2.5 py-1 rounded-md"
                                :class="warnaBadgeKelas(santri.kelas)">
                                @{{ santri.kelas }}
                            </span>
                            <span v-else class="text-xs text-amber-600">{{ __('Belum dipilih') }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <span v-if="santri.kamar">@{{ santri.kamar }}</span>
                            <span v-else class="text-xs text-amber-600">{{ __('Belum dipilih') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="santri.nomor_kasur" class="inline-flex items-center justify-center min-w-8 h-7 px-2 rounded-lg bg-amber-50 text-amber-700 ring-1 ring-amber-200 text-xs font-bold">@{{ santri.nomor_kasur }}</span>
                            <span v-else-if="santri.kamar" class="text-xs text-amber-600">{{ __('Belum dipilih') }}</span>
                            <span v-else class="text-xs text-gray-300">-</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">@{{ santri.tanggal_masuk }}</td>
                        <td class="px-4 py-3 text-gray-400">@{{ santri.wali || '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-md"
                                :class="santri.status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500'"
                                v-text="({ 'aktif': @js(__('aktif')), 'belum punya akun': @js(__('belum punya akun')) })[santri.status] || santri.status"></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                {{-- Detail = halaman pendaftaran santri (di sana kelas/kamar bisa diubah) --}}
                                <a :href="santri.detail_url"
                                    class="text-gray-400 hover:text-teal-600 transition" title="{{ __('Lihat detail / ubah kelas & kamar') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>

                                {{-- Cetak kartu QR santri ini saja (tab baru) --}}
                                <a :href="urlKartuQr + '?ids=' + santri.id" target="_blank" rel="noopener"
                                    class="text-gray-400 hover:text-emerald-600 transition" title="{{ __('Cetak kartu QR santri ini') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4M20 17v3"/></svg>
                                </a>

                                {{-- Tempat sampah: hapus santri (konfirmasi dulu) --}}
                                <button type="button" @click="hapus(santri)" :disabled="menghapus === santri.id"
                                    class="text-red-400 hover:text-red-600 transition disabled:opacity-40" title="{{ __('Hapus santri') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="santriTersaring.length === 0">
                        <td :colspan="kelolaMassal ? 10 : 9" class="px-4 py-10 text-center text-gray-400 text-sm">
                            <span v-text="santriList.length === 0 ? @js(__('Belum ada santri. Santri muncul di sini setelah pendaftarannya diterima.')) : @js(__('Tidak ada santri yang cocok dengan pencarian.'))"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= MODAL: TAMBAH CEPAT ================= --}}
    <div v-if="tambah.buka" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4"
        @mousedown.self="tambah.buka = false">
        <form class="w-full max-w-lg rounded-2xl bg-white shadow-xl" @submit.prevent="simpanTambahCepat">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Tambah Cepat Santri') }}</h3>
                <p class="text-xs text-gray-500">{{ __('Santri langsung tersimpan dengan status') }} <strong>{{ __('Diterima') }}</strong>. {{ __('Data lengkap bisa dilengkapi nanti dari halaman detail.') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4 px-6 py-5">
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Nama Santri') }} <span class="text-red-500">*</span></label>
                    <input id="tambah-nama" v-model="tambah.form.nama_lengkap" type="text" required dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                    <p v-if="tambah.errors.nama_lengkap" class="mt-1 text-xs text-red-600" v-text="tambah.errors.nama_lengkap[0]"></p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Nama Wali') }}</label>
                    <input v-model="tambah.form.nama_wali" type="text" dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('No. WA Wali') }}</label>
                    <input v-model="tambah.form.no_hp_wali" type="tel" placeholder="08..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                    <p v-if="tambah.errors.no_hp_wali" class="mt-1 text-xs text-red-600" v-text="tambah.errors.no_hp_wali[0]"></p>
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Kota') }}</label>
                    <input v-model="tambah.form.kota" type="text" dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Kelas') }}</label>
                    <select v-model="tambah.form.kelas_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option :value="null">{{ __('Belum dipilih') }}</option>
                        <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Kamar') }}</label>
                    <select v-model="tambah.form.kamar_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option :value="null">{{ __('Belum dipilih') }}</option>
                        <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                    </select>
                    <p v-if="tambah.errors.kamar_id" class="mt-1 text-xs text-red-600" v-text="tambah.errors.kamar_id[0]"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="tambah.buka = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="tambah.loading"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60"
                    v-text="tambah.loading ? @js(__('Menyimpan...')) : @js(__('Simpan'))"></button>
            </div>
        </form>
    </div>

    {{-- ================= NOTIFIKASI ================= --}}
    <div v-if="toast.show" class="fixed bottom-6 right-6 z-[60] rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
        :class="toast.error ? 'bg-red-600' : 'bg-[#0b332c]'" v-text="toast.text"></div>
</div>

@push('scripts')
{{-- Vue 3 dari Vite: window.Vue disediakan resources/js/app.js (dimuat oleh layouts.admin) --}}
<script type="module">
    const { createApp } = window.Vue;

    // Warna kotak & badge kelas, dipakai bergiliran sesuai urutan kelas di Master Data
    // Warna aksen kartu kelas: terang, supaya kontras dengan latar hijau gelap sidebar
    const WARNA_AKSEN = ['#60a5fa', '#4ade80', '#facc15', '#c084fc', '#f87171', '#2dd4bf', '#fb923c', '#38bdf8', '#f472b6', '#a3e635'];
    function rapikan(teks) {
    return (teks || '')
    .toString()
    .toLowerCase()
    .replace(/[\u064B-\u0652\u0670\u0640]/g, '') // harakat & tatwil
    .replace(/[أإآٱ]/g, 'ا')
    .replace(/ة/g, 'ه')
    .replace(/ى/g, 'ي')
    .trim();
    }
    const WARNA_BADGE = ['bg-blue-50 text-blue-600', 'bg-green-50 text-green-600', 'bg-amber-50 text-amber-600', 'bg-purple-50 text-purple-600', 'bg-red-50 text-red-600', 'bg-emerald-50 text-emerald-700', 'bg-orange-50 text-orange-600', 'bg-sky-50 text-sky-600', 'bg-pink-50 text-pink-600', 'bg-teal-50 text-teal-700'];

    // Kirim data ke server (JSON). Melempar error berisi status & pesan bila gagal.
    const CSRF = @json(csrf_token());
    async function kirim(url, body = {}, method = 'POST') {
        const res = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        const isJson = (res.headers.get('content-type') || '').includes('application/json');
        const data = isJson ? await res.json() : {};
        if (!res.ok || !isJson) {
            const pesan = res.status === 419 ? @js(__('Sesi habis, silakan muat ulang halaman.')) : (data.message || @js(__('Terjadi kesalahan. Coba lagi.')));
            throw Object.assign(new Error(pesan), { status: res.status, errors: data.errors || {} });
        }
        return data;
    }

    const URL_TAMBAH = @json(route('admin.santri.store'));
    const URL_MASSAL = @json(route('admin.santri.massal'));
    const formKosong = () => ({ nama_lengkap: '', nama_wali: '', no_hp_wali: '', kota: '', kelas_id: null, kamar_id: null });

    createApp({
        data() {
            return {
                // Data asli dari controller
                santriList: @json($santri),
                urlKartuQr: @json(route('admin.santri.kartu-qr')), // halaman cetak kartu QR
                kelasOptions: @json($kelasOptions),
                kamarOptions: @json($kamarOptions),
                pencarian: '',
                filterTerbuka: false,
                filter: { kelas: '', kamar: '', status: '' },
                kelolaMassal: false,
                pilihSemua: false,
                terpilih: [],
                massal: { kelas_id: null, kamar_id: null, loading: false, error: '' },
                tambah: { buka: false, form: formKosong(), errors: {}, loading: false },
                toast: { show: false, text: '', error: false },
                menghapus: null, // id santri yang sedang dihapus
                grupAktif: null, // kartu kelas/kamar yang sedang dibuka: { jenis, id }
            };
        },
        computed: {
            // Jumlah per kelas dihitung dari tabel, jadi langsung ikut berubah setelah pindah kelas
            kelasStats() {
                const total = this.santriList.length;
                return this.kelasOptions.map((kelas, i) => {
                    const jumlah = this.santriList.filter(santri => santri.kelas_id === kelas.id).length;
                    return {
                        id: kelas.id,
                        nama: kelas.nama,
                        jumlah,
                        persen: total ? Math.round(jumlah / total * 100) : 0, // untuk bar & label %
                        aksen: WARNA_AKSEN[i % WARNA_AKSEN.length],
                    };
                });
            },
            tanpaKelas() {
                return this.santriList.filter(santri => !santri.kelas_id).length;
            },
            // Kartu kamar: jumlah penghuni / kapasitas (ikut berubah setelah pindah kamar)
            kamarStats() {
                return this.kamarOptions.map(kamar => {
                    const jumlah = this.santriList.filter(santri => santri.kamar_id === kamar.id).length;
                    return {
                        id: kamar.id,
                        nama: kamar.nama,
                        kapasitas: kamar.kapasitas,
                        jumlah,
                        penuh: kamar.kapasitas > 0 && jumlah >= kamar.kapasitas,
                        persen: kamar.kapasitas > 0 ? Math.min(100, Math.round(jumlah / kamar.kapasitas * 100)) : (jumlah ? 100 : 0),
                    };
                });
            },
            tanpaKamar() {
                return this.santriList.filter(santri => !santri.kamar_id).length;
            },
            // Isi panel anggota untuk kartu yang sedang dibuka (kamar: lengkap dengan denah)
            grup() {
                const g = this.grupAktif;
                if (!g) return null;
                const kolom = g.jenis === 'kelas' ? 'kelas_id' : 'kamar_id';
                const anggota = this.santriList
                    .filter(santri => (g.id === 'kosong' ? !santri[kolom] : santri[kolom] === g.id))
                    .sort((a, b) => a.nama.localeCompare(b.nama));

                if (g.jenis === 'kelas') {
                    const kelas = this.kelasOptions.find(k => k.id === g.id);
                    return { ...g, nama: kelas ? kelas.nama : @js(__('Belum ada kelas')), anggota };
                }

                const kamar = this.kamarOptions.find(k => k.id === g.id);
                if (!kamar) return { ...g, nama: @js(__('Belum ada kamar')), anggota };

                // Urut menurut nomor kasur; yang belum punya kasur di belakang
                anggota.sort((a, b) => (a.nomor_kasur || 999) - (b.nomor_kasur || 999));

                // Denah: kasur 1..kapasitas (atau sampai nomor kasur terbesar yang terpakai)
                const terbesar = Math.max(0, ...anggota.map(s => s.nomor_kasur || 0));
                const jumlahKasur = Math.max(kamar.kapasitas, terbesar);
                const denah = jumlahKasur ? Array.from({ length: jumlahKasur }, (_, i) => ({
                    nomor: i + 1,
                    santri: anggota.find(s => s.nomor_kasur === i + 1) || null,
                })) : null;

                return {
                    ...g, nama: kamar.nama, kapasitas: kamar.kapasitas, anggota, denah,
                    kolom: Math.min(kamar.kasur_per_baris || 4, Math.max(jumlahKasur, 1)),
                    tanpaKasur: anggota.filter(s => !s.nomor_kasur).length,
                };
            },
            totalSantri() {
                return this.santriList.length;
            },
            santriTersaring() {
                        const kata = rapikan(this.pencarian);
                        const f = this.filter;

                        return this.santriList.filter(santri => {
                        // 1) Filter kelas
                        if (f.kelas === 'kosong' && santri.kelas_id) return false;
                        if (f.kelas && f.kelas !== 'kosong' && santri.kelas_id !== f.kelas) return false;

                        // 2) Filter kamar
                        if (f.kamar === 'kosong' && santri.kamar_id) return false;
                        if (f.kamar && f.kamar !== 'kosong' && santri.kamar_id !== f.kamar) return false;

                        // 3) Filter status
                        if (f.status && santri.status !== f.status) return false;

                        // 4) Kotak pencarian
                        if (!kata) return true;
                        return [santri.nama, santri.nama_arab, santri.nomor, santri.kelas, santri.kamar, santri.wali]
                                                .some(nilai => rapikan(nilai).includes(kata));
                        });
                        },
                        // Berapa filter yang sedang dipakai (untuk angka di tombol Filter)
                        jumlahFilterAktif() {
                        return Object.values(this.filter).filter(nilai => nilai !== '').length;
                        },
        },
        methods: {
            // ----- Kartu kelas / kamar -----
            bukaGrup(jenis, id) {
                this.grupAktif = this.aktif(jenis, id) ? null : { jenis, id };
            },
            aktif(jenis, id) {
                return !!this.grupAktif && this.grupAktif.jenis === jenis && this.grupAktif.id === id;
            },
            inisial(nama) {
                return (nama || '?').trim().charAt(0).toUpperCase();
            },
            // Saring tabel di bawah sesuai kartu yang dibuka
            tampilkanDiTabel() {
                const g = this.grupAktif;
                this.filter = { kelas: '', kamar: '', status: '', [g.jenis]: g.id };
                this.pencarian = '';
                this.$nextTick(() => document.querySelector('#data-santri-app table')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            },

            notif(text, error = false) {
                this.toast = { show: true, text, error };
                clearTimeout(this._toast);
                this._toast = setTimeout(() => (this.toast.show = false), 3000);
            },
            toggleSemua() {
                this.terpilih = this.pilihSemua
                    ? this.santriTersaring.map(santri => santri.id)
                    : [];
            },
            warnaBadgeKelas(nama) {
                const i = this.kelasOptions.findIndex(kelas => kelas.nama === nama);
                return i === -1 ? 'bg-gray-50 text-gray-600' : WARNA_BADGE[i % WARNA_BADGE.length];
            },
            // Ganti baris lama dengan versi terbaru dari server (atau tambahkan kalau baru)
            perbaruiBaris(baris) {
                baris.forEach(baru => {
                    const i = this.santriList.findIndex(s => s.id === baru.id);
                    if (i === -1) this.santriList.push(baru);
                    else this.santriList.splice(i, 1, baru);
                });
            },

            // ----- Cetak kartu QR santri yang dicentang (tab baru) -----
            cetakQrTerpilih() {
                if (!this.terpilih.length) return;
                window.open(this.urlKartuQr + '?ids=' + this.terpilih.join(','), '_blank');
            },

            // ----- Kelola Massal -----
            toggleMassal() {
                this.kelolaMassal = !this.kelolaMassal;
                this.terpilih = [];
                this.pilihSemua = false;
                this.massal = { kelas_id: null, kamar_id: null, loading: false, error: '' };
            },
            async terapkanMassal() {
                if (!this.terpilih.length) return (this.massal.error = @js(__('Centang minimal satu santri.')));
                if (!this.massal.kelas_id && !this.massal.kamar_id) return (this.massal.error = @js(__('Pilih kelas atau kamar tujuan.')));

                this.massal.loading = true;
                this.massal.error = '';
                try {
                    const res = await kirim(URL_MASSAL, { ids: this.terpilih, kelas_id: this.massal.kelas_id, kamar_id: this.massal.kamar_id });
                    this.perbaruiBaris(res.santri);
                    this.kamarOptions = res.kamarOptions;
                    this.notif(res.message);
                    this.toggleMassal();
                } catch (e) {
                    this.massal.error = Object.values(e.errors)[0]?.[0] || e.message;
                } finally {
                    this.massal.loading = false;
                }
            },

            // ----- Hapus -----
            async hapus(santri) {
                if (!window.confirm(@js(__('Hapus santri ":nama"?')).replace(':nama', santri.nama) + '\n' + @js(__('Berkas unggahan dan akun login siswanya juga ikut terhapus.')))) return;

                this.menghapus = santri.id;
                try {
                    const res = await kirim(santri.hapus_url, {}, 'DELETE');
                    this.santriList = this.santriList.filter(s => s.id !== santri.id);
                    this.terpilih = this.terpilih.filter(id => id !== santri.id);
                    this.notif(res.message);
                } catch (e) {
                    this.notif(e.message, true);
                } finally {
                    this.menghapus = null;
                }
            },

            // ----- Tambah Cepat -----
            bukaTambahCepat() {
                this.tambah = { buka: true, form: formKosong(), errors: {}, loading: false };
                setTimeout(() => document.getElementById('tambah-nama')?.focus());
            },
            async simpanTambahCepat() {
                this.tambah.loading = true;
                this.tambah.errors = {};
                try {
                    const res = await kirim(URL_TAMBAH, this.tambah.form);
                    this.perbaruiBaris([res.santri]);
                    this.kamarOptions = res.kamarOptions;
                    this.tambah.buka = false;
                    this.notif(res.message);
                } catch (e) {
                    if (e.status === 422) this.tambah.errors = e.errors;
                    else this.notif(e.message, true);
                } finally {
                    this.tambah.loading = false;
                }
            },
        },
    }).mount('#data-santri-app');
</script>
@endpush
@endsection
