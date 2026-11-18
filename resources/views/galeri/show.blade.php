{{--
    Album Galeri Foto (/galeri/{id}): semua foto album, keterangan, pengirim,
    Suka · Bagikan (WA, Facebook, X, Telegram, Instagram/lainnya lewat tombol bagikan HP, salin tautan) · Komentar.
    Pola sama dengan berita/show. Vue #album-foto & #interaksi-album; teks dari database ditulis di luar area Vue / lewat v-text.
--}}
@extends('layouts.app')

@section('title', $album->judul . ' - ' . __('Galeri Foto'))

@section('meta')
<meta name="description" content="{{ $album->keterangan ?: __('Galeri Foto Dauroh Qobul Darul Musthofa Tarim') }}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $album->judul }}">
<meta property="og:description" content="{{ $album->keterangan ?: __('Galeri Foto Dauroh Qobul Darul Musthofa Tarim') }}">
<meta property="og:url" content="{{ $album->url }}">
@if ($album->foto_utama_url)
<meta property="og:image" content="{{ $album->foto_utama_url }}">
@endif
<meta name="twitter:card" content="summary_large_image">
@endsection

@section('content')
@php
$kartu = 'bg-white rounded-2xl ring-1 ring-slate-200/70 shadow-sm';
@endphp

<div class="bg-[#f7f5f0] min-h-screen pt-28 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <nav class="flex items-center gap-2 text-sm text-slate-500 min-w-0">
            <a href="{{ route('landing') }}" class="font-semibold text-teal-700 hover:underline shrink-0">{{ __('Beranda') }}</a>
            <span class="shrink-0">/</span>
            <a href="{{ route('galeri.index') }}" class="font-semibold text-teal-700 hover:underline shrink-0">{{ __('Galeri Foto') }}</a>
            <span class="shrink-0">/</span>
            <span class="truncate">{{ $album->judul }}</span>
        </nav>

        @if ($pratinjau)
        <div class="mt-5 rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 text-sm text-amber-800">
            <b>{{ __('Pratinjau.') }}</b> {{ __('Album ini belum/tidak tampil di Galeri Foto. Hanya admin dan pengirim yang bisa membuka halaman ini.') }}
        </div>
        @endif

        <div class="mt-5 bg-white rounded-3xl ring-1 ring-slate-200/70 shadow-xl shadow-slate-900/5 p-4 sm:p-8">
            {{-- Pengirim · tanggal --}}
            <div class="flex items-center gap-3">
                @if ($album->penulis_foto)
                <img src="{{ $album->penulis_foto }}" alt="" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow">
                @else
                <span class="w-11 h-11 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($album->penulis_nama, 0, 1)) }}</span>
                @endif
                <div class="min-w-0">
                    <p class="font-bold text-slate-800 truncate">{{ $album->penulis_nama }}
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-teal-600 text-white text-[10px] font-bold align-middle">{{ \App\Models\Berita::labelPeran($album->penulis_peran) }}</span>
                    </p>
                    <p class="text-xs text-slate-500">{{ $album->tanggal_tampil->locale('id')->translatedFormat('d F Y') }} · {{ $album->foto->count() }} {{ __('foto') }}</p>
                </div>
            </div>

            @if ($album->keterangan)
            <p class="mt-4 text-[16px] sm:text-[17px] leading-relaxed text-slate-800 whitespace-pre-line break-words">{{ $album->keterangan }}</p>
            @endif

            {{-- Foto --}}
            @if ($album->foto->isNotEmpty())
            <div id="album-foto" class="mt-5">
                <div class="relative w-fit max-w-full mx-auto rounded-2xl overflow-hidden shadow-lg bg-slate-100">
                    <img :src="foto[aktif]" src="{{ $album->foto_utama_url }}" alt="" @click="perbesar = true"
                        class="block w-auto max-w-full h-auto max-h-[65vh] cursor-zoom-in">
                    <template v-if="foto.length > 1">
                        <button type="button" @click="geser(-1)" aria-label="{{ __('Foto sebelumnya') }}"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/60">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        </button>
                        <button type="button" @click="geser(1)" aria-label="{{ __('Foto berikutnya') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/60">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                        <span class="absolute right-3 bottom-3 px-2.5 py-1 rounded-full bg-black/50 text-white text-xs font-semibold" v-text="(aktif + 1) + ' / ' + foto.length"></span>
                    </template>
                </div>
                <div v-if="foto.length > 1" class="mt-3 flex gap-2 overflow-x-auto pb-1">
                    <button v-for="(f, i) in foto" :key="i" type="button" @click="aktif = i"
                        class="w-20 h-16 shrink-0 rounded-lg overflow-hidden ring-2 transition"
                        :class="i === aktif ? 'ring-teal-600' : 'ring-transparent opacity-60 hover:opacity-100'">
                        <img :src="f" alt="" loading="lazy" class="w-full h-full object-cover">
                    </button>
                </div>

                <div v-if="perbesar" @click.self="perbesar = false" class="fixed inset-0 z-[80] bg-black/90 flex items-center justify-center p-4">
                    <img :src="foto[aktif]" alt="" class="max-w-full max-h-full object-contain">
                    <button type="button" @click="perbesar = false" aria-label="{{ __('Tutup') }}" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/15 text-white text-2xl leading-none hover:bg-white/25">&times;</button>
                    <template v-if="foto.length > 1">
                        <button type="button" @click="geser(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&lsaquo;</button>
                        <button type="button" @click="geser(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&rsaquo;</button>
                    </template>
                </div>
            </div>
            @endif
        </div>

        {{-- ================= SUKA · KOMENTAR · BAGIKAN ================= --}}
        <div id="interaksi-album" class="mt-6" v-cloak>
            <div class="{{ $kartu }} p-2 flex items-stretch divide-x divide-slate-100">
                <button type="button" @click="suka" :disabled="sibukSuka"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold transition-colors hover:bg-rose-50"
                    :class="sudahSuka ? 'text-rose-600' : 'text-slate-600'">
                    <svg class="w-5 h-5 transition-transform" :class="sudahSuka ? 'scale-110' : ''" :fill="sudahSuka ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    <span v-text="sudahSuka ? @js(__('Disukai')) : @js(__('Suka'))"></span>
                    <span class="text-xs font-semibold text-slate-400" v-text="jumlahSuka"></span>
                </button>
                <button type="button" @click="$refs.isiKomentar?.focus()"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                    {{ __('Komentar') }}
                    <span class="text-xs font-semibold text-slate-400" v-text="jumlahKomentar"></span>
                </button>
                <button type="button" @click="bukaBagikan = !bukaBagikan"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-700"
                    :class="bukaBagikan ? 'bg-teal-50 text-teal-700' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
                    {{ __('Bagikan') }}
                </button>
            </div>

            {{-- Pilihan bagikan --}}
            <div v-if="bukaBagikan" class="mt-3 {{ $kartu }} p-4">
                <p class="text-xs font-semibold text-slate-500 mb-3">{{ __('Bagikan foto ini ke:') }}</p>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <a :href="tautan.wa" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-[#25D366] text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.42-1.36a9.9 9.9 0 0 0 4.62 1.14h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.64-1.03-5.13-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.09c-.24.68-1.41 1.3-1.95 1.35-.5.05-.97.23-3.27-.68-2.77-1.09-4.53-3.93-4.67-4.11-.13-.18-1.11-1.48-1.11-2.82 0-1.34.7-2 .95-2.27.25-.27.54-.34.72-.34h.52c.17 0 .39-.06.61.47.23.54.77 1.88.84 2.02.07.13.11.29.02.47-.09.18-.13.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.55.16.27.7 1.15 1.5 1.86 1.03.92 1.9 1.2 2.17 1.34.27.13.43.11.59-.07.16-.18.68-.79.86-1.06.18-.27.36-.23.61-.13.25.09 1.59.75 1.86.88.27.14.45.2.52.32.07.11.07.66-.17 1.34z"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">WhatsApp</span>
                    </a>
                    <a :href="tautan.fb" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-[#1877F2] text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">Facebook</span>
                    </a>
                    <button type="button" @click="bagikanHp" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-linear-to-tr from-amber-400 via-pink-500 to-violet-600 text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">Instagram</span>
                    </button>
                    <a :href="tautan.x" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-black text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64z"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">X</span>
                    </a>
                    <a :href="tautan.tg" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-[#229ED9] text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9.78 15.27l-.4 5.6c.57 0 .82-.25 1.12-.54l2.69-2.57 5.57 4.08c1.02.57 1.75.27 2.02-.94l3.66-17.15c.33-1.51-.54-2.1-1.53-1.73L1.4 10.24c-1.47.57-1.45 1.39-.25 1.76l5.5 1.71 12.78-8.06c.6-.4 1.15-.18.7.22"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">Telegram</span>
                    </a>
                    <button type="button" @click="salin" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-slate-50">
                        <span class="w-11 h-11 rounded-full bg-slate-700 text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        </span>
                        <span class="text-xs font-semibold text-slate-600">{{ __('Salin tautan') }}</span>
                    </button>
                </div>
                <p class="mt-3 text-[11px] text-slate-400">{{ __('Instagram: di HP akan muncul pilihan aplikasi; di laptop tautan disalin untuk ditempel di Instagram.') }}</p>
            </div>

            {{-- Komentar --}}
            <section class="mt-6 {{ $kartu }} p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-800">{{ __('Komentar') }} <span class="text-slate-400 font-semibold" v-text="'(' + jumlahKomentar + ')'"></span></h2>

                <form @submit.prevent="kirimKomentar" class="mt-4 space-y-3">
                    <input v-model="form.nama" type="text" maxlength="100" required placeholder="{{ __('Nama Anda') }}"
                        class="w-full rounded-xl border border-slate-200 bg-[#fbfaf7] px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
                    <textarea ref="isiKomentar" v-model="form.isi" rows="3" maxlength="2000" required placeholder="{{ __('Tulis komentar…') }}"
                        class="w-full rounded-xl border border-slate-200 bg-[#fbfaf7] px-4 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500"></textarea>
                    {{-- jebakan bot: tidak terlihat, harus kosong --}}
                    <input v-model="form.situs" type="text" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-rose-600" v-text="galat"></p>
                        <button type="submit" :disabled="sibuk"
                            class="shrink-0 px-5 py-2.5 rounded-xl bg-teal-700 text-white text-sm font-bold hover:bg-teal-800 disabled:opacity-50"
                            v-text="sibuk ? @js(__('Mengirim…')) : @js(__('Kirim komentar'))"></button>
                    </div>
                </form>

                <p v-if="!komentar.length" class="mt-6 text-sm text-slate-400 text-center">{{ __('Belum ada komentar. Jadilah yang pertama.') }}</p>
                <ul class="mt-6 space-y-4">
                    <li v-for="k in komentar" :key="k.id" class="flex gap-3">
                        <span class="w-9 h-9 shrink-0 rounded-full bg-teal-100 text-teal-700 font-bold text-sm flex items-center justify-center" v-text="(k.nama || '?').charAt(0).toUpperCase()"></span>
                        <div class="min-w-0 flex-1">
                            <div class="rounded-2xl bg-[#f4f2ec] px-4 py-2.5">
                                <p class="text-sm font-bold text-slate-800">
                                    <span v-text="k.nama"></span>
                                    <span v-if="k.peran" class="ml-1.5 px-1.5 py-0.5 rounded bg-teal-600 text-white text-[10px] font-bold align-middle" v-text="k.peran"></span>
                                </p>
                                <p class="mt-0.5 text-[15px] leading-relaxed text-slate-700 whitespace-pre-line break-words" v-text="k.isi"></p>
                            </div>
                            <p class="mt-1 ml-2 text-[11px] text-slate-400">
                                <span v-text="k.waktu"></span>
                                <button v-if="bolehBalas" type="button" @click="bukaBalas(k)" class="ml-3 font-semibold hover:text-teal-700">{{ __('Balas') }}</button>
                                <button v-if="k.boleh_hapus" type="button" @click="hapusKomentar(k)" class="ml-3 font-semibold hover:text-rose-600">{{ __('Hapus') }}</button>
                            </p>

                            {{-- Balasan (admin & pengirim foto) --}}
                            <ul v-if="k.balasan && k.balasan.length" class="mt-3 space-y-3 pl-2 sm:pl-4 border-l-2 border-teal-100">
                                <li v-for="b in k.balasan" :key="b.id" class="flex gap-2.5">
                                    <span class="w-7 h-7 shrink-0 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center" v-text="(b.nama || '?').charAt(0).toUpperCase()"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="rounded-2xl bg-teal-50 px-3.5 py-2">
                                            <p class="text-sm font-bold text-slate-800">
                                                <span v-text="b.nama"></span>
                                                <span v-if="b.peran" class="ml-1.5 px-1.5 py-0.5 rounded bg-teal-600 text-white text-[10px] font-bold align-middle" v-text="b.peran"></span>
                                            </p>
                                            <p class="mt-0.5 text-[15px] leading-relaxed text-slate-700 whitespace-pre-line break-words" v-text="b.isi"></p>
                                        </div>
                                        <p class="mt-1 ml-2 text-[11px] text-slate-400">
                                            <span v-text="b.waktu"></span>
                                            <button v-if="b.boleh_hapus" type="button" @click="hapusKomentar(b)" class="ml-3 font-semibold hover:text-rose-600">{{ __('Hapus') }}</button>
                                        </p>
                                    </div>
                                </li>
                            </ul>

                            <form v-if="bolehBalas && balas.indukId === k.id" @submit.prevent="kirimBalasan(k)" class="mt-3 space-y-2">
                                <textarea :ref="'balas' + k.id" v-model="balas.isi" rows="2" maxlength="2000" required placeholder="{{ __('Tulis balasan…') }}"
                                    class="w-full rounded-xl border border-slate-200 bg-[#fbfaf7] px-3.5 py-2 text-base focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500"></textarea>
                                <div class="flex items-center justify-end gap-2">
                                    <p class="mr-auto text-xs text-rose-600" v-text="balas.galat"></p>
                                    <button type="button" @click="tutupBalas" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 hover:bg-slate-100">{{ __('Batal') }}</button>
                                    <button type="submit" :disabled="balas.sibuk"
                                        class="px-4 py-1.5 rounded-lg bg-teal-700 text-white text-xs font-bold hover:bg-teal-800 disabled:opacity-50"
                                        v-text="balas.sibuk ? @js(__('Mengirim…')) : @js(__('Kirim balasan'))"></button>
                                </div>
                            </form>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <a href="{{ route('galeri.index') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-teal-700 hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('Kembali ke Galeri Foto') }}
        </a>

        {{-- Album lainnya --}}
        @if ($lainnya->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-extrabold text-[#0b332c] text-center">{{ __('Album Lainnya') }}</h2>
            @include('berita.partials.pemisah', ['kelas' => 'mt-3'])
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($lainnya as $a)
                <a href="{{ $a->url }}" class="group relative block aspect-square rounded-2xl overflow-hidden bg-slate-200 shadow-sm hover:shadow-lg">
                    <img src="{{ $a->foto_utama_url }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @if ($a->foto->count() > 1)
                    <span class="absolute right-2 top-2 px-2 py-0.5 rounded-full bg-black/55 text-white text-[11px] font-bold">▦ {{ $a->foto->count() }}</span>
                    @endif
                    @if ($a->keterangan)
                    <span class="absolute inset-x-0 bottom-0 p-2.5 pt-8 bg-linear-to-t from-black/70 to-transparent text-xs font-semibold text-white line-clamp-2">{{ $a->keterangan }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const TOKEN = @json(csrf_token());
        const URL_ALBUM = @json($album->url);
        const JUDUL = @json($album->judul);
        const kode = encodeURIComponent;

        @if ($album->foto->isNotEmpty())
        Vue.createApp({
            data: () => ({ foto: @json($album->foto->map->url->values()), aktif: 0, perbesar: false }),
            methods: {
                geser(arah) { this.aktif = (this.aktif + arah + this.foto.length) % this.foto.length; },
            },
            mounted() {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') this.perbesar = false;
                    if (e.key === 'ArrowLeft') this.geser(-1);
                    if (e.key === 'ArrowRight') this.geser(1);
                });
            },
        }).mount('#album-foto');
        @endif

        Vue.createApp({
            data: () => ({
                sudahSuka: @json($sudahSuka),
                jumlahSuka: @json($album->jumlah_suka),
                sibukSuka: false,
                bukaBagikan: false,
                komentar: @json($komentar),
                form: { nama: @json($namaLogin ?? ''), isi: '', situs: '' },
                sibuk: false,
                galat: '',
                bolehBalas: @json($bolehBalas),
                balas: { indukId: null, isi: '', sibuk: false, galat: '' },
            }),
            computed: {
                jumlahKomentar() {
                    return this.komentar.reduce((n, k) => n + 1 + (k.balasan ? k.balasan.length : 0), 0);
                },
                tautan() {
                    return {
                        wa: 'https://wa.me/?text=' + kode(JUDUL + '\n' + URL_ALBUM),
                        fb: 'https://www.facebook.com/sharer/sharer.php?u=' + kode(URL_ALBUM),
                        x: 'https://twitter.com/intent/tweet?text=' + kode(JUDUL) + '&url=' + kode(URL_ALBUM),
                        tg: 'https://t.me/share/url?url=' + kode(URL_ALBUM) + '&text=' + kode(JUDUL),
                    };
                },
            },
            methods: {
                async kirim(metode, url, isi) {
                    const res = await fetch(url, {
                        method: metode,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                        body: isi ? JSON.stringify(isi) : null,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        if (res.status === 429) throw new Error(@js(__('Terlalu sering. Tunggu sebentar lalu coba lagi.')));
                        if (res.status === 404) throw new Error(@js(__('Foto ini belum tampil, jadi belum bisa disukai atau dikomentari.')));
                        throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal mengirim. Coba lagi.'))));
                    }
                    return data;
                },
                async suka() {
                    this.sibukSuka = true;
                    try {
                        const d = await this.kirim('POST', URL_ALBUM + '/suka');
                        this.sudahSuka = d.suka;
                        this.jumlahSuka = d.jumlah;
                    } catch (e) {
                        window.notifikasi?.gagal(e.message);
                    } finally {
                        this.sibukSuka = false;
                    }
                },
                async kirimKomentar() {
                    this.galat = '';
                    this.sibuk = true;
                    try {
                        const d = await this.kirim('POST', URL_ALBUM + '/komentar', this.form);
                        this.komentar = d.komentar;
                        this.form.isi = '';
                        window.notifikasi?.sukses(d.pesan);
                    } catch (e) {
                        this.galat = e.message;
                    } finally {
                        this.sibuk = false;
                    }
                },
                bukaBalas(k) {
                    this.balas = { indukId: k.id, isi: '', sibuk: false, galat: '' };
                    this.$nextTick(() => {
                        const el = this.$refs['balas' + k.id];
                        (Array.isArray(el) ? el[0] : el)?.focus();
                    });
                },
                tutupBalas() {
                    this.balas = { indukId: null, isi: '', sibuk: false, galat: '' };
                },
                async kirimBalasan(k) {
                    this.balas.galat = '';
                    this.balas.sibuk = true;
                    try {
                        const d = await this.kirim('POST', URL_ALBUM + '/komentar', {
                            nama: this.form.nama, isi: this.balas.isi, induk_id: k.id, situs: '',
                        });
                        this.komentar = d.komentar;
                        this.tutupBalas();
                        window.notifikasi?.sukses(d.pesan);
                    } catch (e) {
                        this.balas.galat = e.message;
                    } finally {
                        this.balas.sibuk = false;
                    }
                },
                async hapusKomentar(k) {
                    if (!confirm(@js(__('Hapus komentar ini?')))) return;
                    try {
                        const d = await this.kirim('DELETE', @json(url('galeri/komentar')) + '/' + k.id);
                        this.komentar = d.komentar;
                    } catch (e) {
                        window.notifikasi?.gagal(e.message);
                    }
                },
                async salin(pesan) {
                    try {
                        await navigator.clipboard.writeText(URL_ALBUM);
                    } catch (e) {
                        const t = document.createElement('textarea');
                        t.value = URL_ALBUM;
                        document.body.appendChild(t);
                        t.select();
                        document.execCommand('copy');
                        t.remove();
                    }
                    window.notifikasi?.sukses(typeof pesan === 'string' ? pesan : @js(__('Tautan foto disalin.')));
                },
                async bagikanHp() {
                    if (navigator.share) {
                        try {
                            await navigator.share({ title: JUDUL, text: JUDUL, url: URL_ALBUM });
                        } catch (e) { /* dibatalkan */ }
                        return;
                    }
                    this.salin(@js(__('Tautan disalin. Tempel di Instagram (story atau pesan).')));
                },
            },
        }).mount('#interaksi-album');
    });
</script>
@endsection
