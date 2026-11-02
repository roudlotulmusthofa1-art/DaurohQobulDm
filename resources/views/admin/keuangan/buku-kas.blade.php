{{--
    Admin > Keuangan > Buku Kas
    - Kartu saldo: saldo semua waktu + total masuk/keluar/net pada periode filter.
    - Filter (tanggal, tipe, kategori) dikirim lewat URL; tombol cepat 7 Hari / 30 Hari / bulan ini / bulan lalu.
    - Pop-up Tambah/Ubah Transaksi dan Kelola Kategori.
    Semua interaksi diatur Vue (window.Vue dari Vite); data awal dari controller lewat @json.
--}}
@extends('layouts.admin')

@section('title', 'Buku Kas')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
@endphp

<div id="halaman-buku-kas" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10">
    @foreach (['success' => 'bg-emerald-50 ring-emerald-200 text-emerald-900', 'error' => 'bg-rose-50 ring-rose-200 text-rose-800'] as $kunciFlash => $gayaFlash)
    @if (session($kunciFlash))
    <div class="p-3.5 rounded-2xl ring-1 text-sm {{ $gayaFlash }}">{{ session($kunciFlash) }}</div>
    @endif
    @endforeach
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl ring-1 ring-rose-200 bg-rose-50 text-rose-800 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ===== Judul + tombol ===== --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Buku Kas</h1>
            <p class="mt-1 text-sm text-slate-500">Catat semua pemasukan dan pengeluaran kas harian pesantren.</p>
        </div>
        <button type="button" @click="bukaKategori"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Kelola Kategori
        </button>
        <button type="button" @click="bukaTambah"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Transaksi
        </button>
    </div>

    {{-- ===== Saldo ===== --}}
    <div class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <span class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-500">Saldo Saat Ini</p>
                <p class="mt-0.5 text-3xl font-extrabold tracking-tight" :class="saldo < 0 ? 'text-rose-600' : 'text-slate-800'" v-text="rupiah(saldo)">{{ 'Rp ' . number_format($saldo, 0, ',', '.') }}</p>

                <div class="mt-4 pt-4 border-t border-slate-100 grid sm:grid-cols-3 gap-4">
                    <div>
                        <p class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Masuk · <span v-text="labelPeriode"></span>
                        </p>
                        <p class="mt-1 font-bold text-slate-800" v-text="rupiah(periode.masuk)"></p>
                    </div>
                    <div>
                        <p class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Keluar · <span v-text="labelPeriode"></span>
                        </p>
                        <p class="mt-1 font-bold text-slate-800" v-text="rupiah(periode.keluar)"></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Net · <span v-text="labelPeriode"></span></p>
                        <p class="mt-1 font-bold" :class="net < 0 ? 'text-rose-600' : 'text-emerald-700'" v-text="(net < 0 ? '-' : '+') + rupiah(Math.abs(net))"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <form ref="formFilter" method="GET" action="{{ route('admin.buku-kas.index') }}" class="{{ $kartu }} p-4 sm:p-5 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <button v-for="p in preset" :key="p.label" type="button" @click="pakaiPreset(p)"
                class="px-3 py-1.5 rounded-full text-sm font-semibold transition-colors"
                :class="p.mulai === filter.start_date && p.sampai === filter.end_date ? 'bg-[#0f6b5c] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                v-text="p.label"></button>
            <span class="flex-1"></span>
            <a href="{{ route('admin.buku-kas.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Dari Tanggal</span>
                <input type="date" name="start_date" v-model="filter.start_date" @change="terapkanFilter" class="mt-1 {{ $isian }}">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Sampai Tanggal</span>
                <input type="date" name="end_date" v-model="filter.end_date" @change="terapkanFilter" class="mt-1 {{ $isian }}">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Tipe</span>
                <select name="tipe" v-model="filter.tipe" @change="filter.kategori = ''; terapkanFilter()" class="mt-1 {{ $isian }}">
                    <option value="">Semua</option>
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-medium text-slate-500">Kategori</span>
                <select name="kategori" v-model="filter.kategori" @change="terapkanFilter" class="mt-1 {{ $isian }}">
                    <option value="">Semua</option>
                    <option v-for="k in kategoriUntuk(filter.tipe)" :key="k.id" :value="String(k.id)" v-text="k.nama + (filter.tipe ? '' : ' (' + k.tipe + ')')"></option>
                </select>
            </label>
        </div>
    </form>

    {{-- ===== Tabel transaksi ===== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-left text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Deskripsi</th>
                        <th class="px-4 py-3.5 whitespace-nowrap hidden 2xl:table-cell">Sumber / Penerima</th>
                        <th class="px-4 py-3.5">Tipe</th>
                        <th class="px-4 py-3.5 text-right">Jumlah</th>
                        <th class="px-4 py-3.5 text-center">Bukti</th>
                        <th class="px-3 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="t in baris" :key="t.id" class="hover:bg-slate-50/60">
                        <td class="px-5 py-4 text-slate-700 w-28 min-w-[7rem]" v-text="t.tanggal_label"></td>
                        <td class="px-4 py-4">
                            <span v-if="t.kategori" class="inline-block px-2.5 py-0.5 rounded-full ring-1 ring-slate-200 text-xs text-slate-600 whitespace-nowrap" v-text="t.kategori"></span>
                            <span v-else class="text-slate-300">–</span>
                        </td>
                        <td class="px-4 py-4 text-slate-700 max-w-[300px]">
                            <p class="truncate" :title="t.deskripsi" v-text="t.deskripsi"></p>
                            {{-- Layar kecil: sumber/penerima ditampilkan di bawah deskripsi --}}
                            <p v-if="t.pihak" class="2xl:hidden truncate text-xs text-slate-400" v-text="(t.tipe === 'masuk' ? 'dari ' : 'kepada ') + t.pihak"></p>
                        </td>
                        <td class="px-4 py-4 text-slate-600 max-w-[180px] hidden 2xl:table-cell"><p class="truncate" :title="t.pihak" v-text="t.pihak || '–'"></p></td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold ring-1"
                                :class="t.tipe === 'masuk' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-rose-50 text-rose-600 ring-rose-100'">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span v-text="t.tipe === 'masuk' ? 'Masuk' : 'Keluar'"></span>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right font-bold whitespace-nowrap tabular-nums" :class="t.tipe === 'masuk' ? 'text-emerald-700' : 'text-rose-600'"
                            v-text="(t.tipe === 'masuk' ? '+' : '−') + rupiah(t.jumlah)"></td>
                        <td class="px-4 py-4 text-center">
                            <a v-if="t.bukti" :href="t.bukti" target="_blank" rel="noopener" title="Lihat bukti"
                                class="inline-flex w-8 h-8 items-center justify-center rounded-lg text-emerald-700 hover:bg-emerald-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                            </a>
                            <span v-else class="text-slate-300">–</span>
                        </td>
                        <td class="px-3 py-4">
                            <button type="button" @click.stop="bukaMenu(t, $event)"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Aksi">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                            </button>
                            {{-- Menu melayang (fixed) supaya tidak terpotong tepi tabel --}}
                            <div v-if="menuBuka?.id === t.id" @click.stop :style="{ left: menuBuka.x + 'px', top: menuBuka.y + 'px' }"
                                class="fixed z-50 w-36 rounded-2xl bg-white ring-1 ring-slate-200 shadow-lg p-1.5">
                                <button type="button" @click="bukaUbah(t)" class="w-full text-left px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-50">Ubah</button>
                                <button type="button" @click="hapusTransaksi(t)" class="w-full text-left px-3 py-2 rounded-xl text-sm text-rose-600 hover:bg-rose-50">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!baris.length">
                        <td colspan="8" class="px-5 py-14 text-center text-slate-400">Belum ada transaksi pada periode ini.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($transaksi->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">{{ $transaksi->links() }}</div>
        @endif
    </div>

    {{-- ===== Pop-up Tambah / Ubah Transaksi ===== --}}
    <dialog ref="dialogTransaksi" @click.self="tutupDialog" class="m-auto w-[min(560px,calc(100%-2rem))] rounded-3xl p-0 backdrop:bg-black/40">
        <form method="POST" :action="form.id ? URL + '/' + form.id : URL" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input v-if="form.id" type="hidden" name="_method" value="PUT">
            <input type="hidden" name="form_id" :value="form.id || 'baru'">
            <input type="hidden" name="tipe" :value="form.tipe">
            <input v-for="(v, k) in filterAwal" :key="k" type="hidden" :name="'_filter[' + k + ']'" :value="v">

            <div class="flex items-center gap-3">
                <h2 class="flex-1 text-lg font-bold text-slate-800" v-text="form.id ? 'Ubah Transaksi' : 'Tambah Transaksi'"></h2>
                <button type="button" @click="tutupDialog" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">✕</button>
            </div>

            <p v-if="galat" class="p-3 rounded-2xl bg-rose-50 ring-1 ring-rose-200 text-sm text-rose-700" v-text="galat"></p>
            {{-- Masuk / Keluar --}}
            <div class="grid grid-cols-2 gap-2 p-1 rounded-2xl bg-slate-100">
                <button type="button" @click="gantiTipe('masuk')" class="py-2 rounded-xl text-sm font-bold transition"
                    :class="form.tipe === 'masuk' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">Pemasukan</button>
                <button type="button" @click="gantiTipe('keluar')" class="py-2 rounded-xl text-sm font-bold transition"
                    :class="form.tipe === 'keluar' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500'">Pengeluaran</button>
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Tanggal</span>
                    <input type="date" name="tanggal" v-model="form.tanggal" required class="mt-1 {{ $isian }}">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Jumlah</span>
                    <span class="relative block mt-1">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" name="jumlah" :value="form.jumlah" @input="ketikJumlah" required placeholder="0"
                            class="{{ $isian }} pl-10 text-right font-semibold tabular-nums">
                    </span>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Kategori</span>
                    <select name="kas_kategori_id" v-model="form.kas_kategori_id" class="mt-1 {{ $isian }}">
                        <option value="">— Tanpa kategori —</option>
                        <option v-for="k in kategoriUntuk(form.tipe)" :key="k.id" :value="String(k.id)" v-text="k.nama"></option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700" v-text="form.tipe === 'masuk' ? 'Sumber (dari siapa)' : 'Penerima (kepada siapa)'"></span>
                    <input type="text" name="pihak" v-model="form.pihak" maxlength="150" :placeholder="form.tipe === 'masuk' ? 'mis. Wali santri Ahmad' : 'mis. Toko Kitab Darul Kutub'" class="mt-1 {{ $isian }}">
                </label>
                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Deskripsi</span>
                    <textarea name="deskripsi" v-model="form.deskripsi" required maxlength="255" rows="2" placeholder="mis. SPP Bulanan — Ahmad (Okt 2026)" class="mt-1 {{ $isian }}"></textarea>
                </label>
            </div>

            {{-- Bukti --}}
            <div>
                <span class="text-sm font-semibold text-slate-700">Bukti <span class="font-normal text-slate-400">(foto nota / PDF, opsional, maks 4 MB)</span></span>
                <div class="mt-1 flex items-center gap-3 p-3 rounded-2xl border border-dashed border-slate-300">
                    <img v-if="pratinjauBukti" :src="pratinjauBukti" alt="" class="w-14 h-14 rounded-xl object-cover ring-1 ring-slate-200">
                    <span v-else class="w-14 h-14 rounded-xl bg-slate-50 ring-1 ring-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400" v-text="namaBukti ? 'PDF' : '—'"></span>
                    <div class="flex-1 min-w-0 text-sm">
                        <p class="truncate text-slate-700" v-text="namaBukti || (form.bukti && !form.hapus_bukti ? 'Bukti tersimpan' : 'Belum ada file')"></p>
                        <a v-if="form.bukti && !namaBukti && !form.hapus_bukti" :href="form.bukti" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-700 hover:underline">Lihat bukti</a>
                    </div>
                    <label class="shrink-0 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 cursor-pointer">
                        Pilih file
                        <input ref="inputBukti" type="file" name="bukti" accept="image/*,application/pdf" class="hidden" @change="pilihBukti">
                    </label>
                </div>
                <label v-if="form.id && form.bukti && !namaBukti" class="mt-2 inline-flex items-center gap-2 text-xs text-rose-600 cursor-pointer">
                    <input type="checkbox" name="hapus_bukti" value="1" v-model="form.hapus_bukti" class="accent-rose-600"> Hapus bukti tersimpan
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="tutupDialog" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d]" v-text="form.id ? 'Simpan Perubahan' : 'Simpan Transaksi'"></button>
            </div>
        </form>
    </dialog>

    {{-- ===== Pop-up Kelola Kategori ===== --}}
    <dialog ref="dialogKategori" @click.self="tutupDialog" class="m-auto w-[min(640px,calc(100%-2rem))] rounded-3xl p-0 backdrop:bg-black/40">
        <div class="p-6 space-y-5">
            <div class="flex items-center gap-3">
                <h2 class="flex-1 text-lg font-bold text-slate-800">Kelola Kategori</h2>
                <button type="button" @click="tutupDialog" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">✕</button>
            </div>

            <p v-if="galat" class="p-3 rounded-2xl bg-rose-50 ring-1 ring-rose-200 text-sm text-rose-700" v-text="galat"></p>
            {{-- Tambah / ubah kategori --}}
            <form method="POST" :action="formKategori.id ? URL + '/kategori/' + formKategori.id : URL + '/kategori'" class="flex flex-wrap items-end gap-2 p-3 rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                @csrf
                <input v-if="formKategori.id" type="hidden" name="_method" value="PUT">
                <input type="hidden" name="form_id" value="kategori">
                <input v-for="(v, k) in filterAwal" :key="k" type="hidden" :name="'_filter[' + k + ']'" :value="v">
                <label class="flex-1 min-w-[180px]">
                    <span class="text-xs font-semibold text-slate-600" v-text="formKategori.id ? 'Ubah nama kategori' : 'Kategori baru'"></span>
                    <input ref="namaKategori" type="text" name="nama" v-model="formKategori.nama" required maxlength="60" placeholder="mis. Listrik & Air" class="mt-1 {{ $isian }}">
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-600">Tipe</span>
                    <select name="tipe" v-model="formKategori.tipe" class="mt-1 {{ $isian }}">
                        <option value="masuk">Masuk</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </label>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d]" v-text="formKategori.id ? 'Simpan' : 'Tambah'"></button>
                <button v-if="formKategori.id" type="button" @click="formKategori = { id: null, nama: '', tipe: formKategori.tipe }" class="px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200">Batal</button>
            </form>

            <div class="grid sm:grid-cols-2 gap-4">
                <div v-for="tp in ['masuk', 'keluar']" :key="tp">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider" :class="tp === 'masuk' ? 'text-emerald-700' : 'text-rose-600'" v-text="tp === 'masuk' ? 'Pemasukan' : 'Pengeluaran'"></p>
                    <ul class="space-y-1.5">
                        <li v-for="k in kategoriUntuk(tp)" :key="k.id" class="flex items-center gap-2 px-3 py-2 rounded-xl ring-1 ring-slate-200"
                            :class="formKategori.id === k.id ? 'bg-emerald-50 ring-emerald-300' : ''">
                            <span class="flex-1 min-w-0 truncate text-sm text-slate-700" v-text="k.nama"></span>
                            <span class="text-[11px] text-slate-400 tabular-nums" :title="k.jumlah + ' transaksi'" v-text="k.jumlah"></span>
                            <button type="button" @click="ubahKategori(k)" class="text-xs font-semibold text-slate-600 hover:text-emerald-700">Ubah</button>
                            <button type="button" @click="hapusKategori(k)" :disabled="k.jumlah > 0" :title="k.jumlah > 0 ? 'Masih dipakai transaksi' : 'Hapus'"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 disabled:text-slate-300 disabled:cursor-not-allowed">Hapus</button>
                        </li>
                        <li v-if="!kategoriUntuk(tp).length" class="text-sm text-slate-400">Belum ada kategori.</li>
                    </ul>
                </div>
            </div>
        </div>
    </dialog>

    {{-- Form hapus (action diisi Vue) --}}
    <form ref="formHapus" method="POST" :action="aksiHapus" class="hidden">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
        <input v-for="(v, k) in filterAwal" :key="k" type="hidden" :name="'_filter[' + k + ']'" :value="v">
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ANGKA = new Intl.NumberFormat('id-ID');
        const TGL_PENDEK = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });
        const TGL_TAHUN = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const BULAN = new Intl.DateTimeFormat('id-ID', { month: 'long' });

        // "2026-11-02" <-> Date (jam 12 siang supaya aman dari geser zona waktu)
        const keDate = (s) => new Date(s + 'T12:00:00');
        const keTeks = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

        Vue.createApp({
            data() {
                const filter = @json($filter);
                return {
                    URL: @json(route('admin.buku-kas.index')),
                    hariIni: @json($hariIni),
                    saldo: @json($saldo),
                    periode: @json($periode),
                    baris: @json($baris),
                    kategori: @json($kategori),
                    filter: { ...filter },
                    filterAwal: { ...filter, page: @json((string) request('page', '')) },
                    menuBuka: null,          // { id, x, y }: menu ⋮ yang terbuka & posisinya di layar
                    form: {},                // isi pop-up transaksi
                    pratinjauBukti: null,
                    namaBukti: '',
                    formKategori: { id: null, nama: '', tipe: 'keluar' },
                    aksiHapus: '',
                    galat: @json($errors->first() ?: session('error')), // pesan gagal, ditampilkan juga di dalam pop-up
                };
            },
            computed: {
                net() {
                    return this.periode.masuk - this.periode.keluar;
                },
                // "4 Okt 2026 – 2 Nov 2026"
                labelPeriode() {
                    const a = keDate(this.filterAwal.start_date), b = keDate(this.filterAwal.end_date);
                    return (a.getFullYear() === b.getFullYear() ? TGL_PENDEK.format(a) : TGL_TAHUN.format(a)) + ' – ' + TGL_TAHUN.format(b);
                },
                // Tombol cepat: 7 Hari, 30 Hari, Bulan ini, Bulan lalu
                preset() {
                    const kini = keDate(this.hariIni);
                    const mundur = (n) => { const d = new Date(kini); d.setDate(d.getDate() - n); return keTeks(d); };
                    const awalBulan = new Date(kini.getFullYear(), kini.getMonth(), 1, 12);
                    const awalLalu = new Date(kini.getFullYear(), kini.getMonth() - 1, 1, 12);
                    const akhirLalu = new Date(kini.getFullYear(), kini.getMonth(), 0, 12);
                    return [
                        { label: '7 Hari', mulai: mundur(6), sampai: this.hariIni },
                        { label: '30 Hari', mulai: mundur(29), sampai: this.hariIni },
                        { label: 'Bulan ' + BULAN.format(awalBulan), mulai: keTeks(awalBulan), sampai: this.hariIni },
                        { label: 'Bulan ' + BULAN.format(awalLalu), mulai: keTeks(awalLalu), sampai: keTeks(akhirLalu) },
                    ];
                },
            },
            methods: {
                ...metodeHalaman,
                rupiah(n) {
                    return 'Rp ' + ANGKA.format(n || 0);
                },
                kategoriUntuk(tipe) {
                    return tipe ? this.kategori.filter(k => k.tipe === tipe) : this.kategori;
                },

                // ----- Filter -----
                // Buka ulang halaman dengan filter baru (isian kosong tidak ikut ke URL)
                terapkanFilter() {
                    const isi = Object.entries(this.filter).filter(([, v]) => v);
                    location.href = this.URL + '?' + new URLSearchParams(isi);
                },
                pakaiPreset(p) {
                    this.filter.start_date = p.mulai;
                    this.filter.end_date = p.sampai;
                    this.terapkanFilter();
                },

                // ----- Transaksi -----
                isiForm(data) {
                    this.form = {
                        id: null, tanggal: this.hariIni, tipe: 'masuk', kas_kategori_id: '', jumlah: '',
                        deskripsi: '', pihak: '', bukti: null, hapus_bukti: false, ...data,
                    };
                    this.pratinjauBukti = this.form.bukti && data.bukti_gambar ? this.form.bukti : null;
                    this.namaBukti = '';
                    if (this.$refs.inputBukti) this.$refs.inputBukti.value = '';
                },
                bukaTambah() {
                    this.galat = '';
                    this.isiForm({ tipe: this.filter.tipe || 'masuk' });
                    this.$refs.dialogTransaksi.showModal();
                },
                bukaUbah(t) {
                    this.galat = '';
                    this.menuBuka = null;
                    this.isiForm({ ...t, kas_kategori_id: t.kas_kategori_id ? String(t.kas_kategori_id) : '', jumlah: ANGKA.format(t.jumlah) });
                    this.$refs.dialogTransaksi.showModal();
                },
                gantiTipe(tipe) {
                    if (this.form.tipe === tipe) return;
                    this.form.tipe = tipe;
                    this.form.kas_kategori_id = ''; // kategori masuk/keluar berbeda
                },
                // Ketik angka -> otomatis diberi titik ribuan (1.000.000)
                ketikJumlah(e) {
                    const angka = e.target.value.replace(/\D/g, '').replace(/^0+/, '');
                    this.form.jumlah = angka ? ANGKA.format(Number(angka)) : '';
                    e.target.value = this.form.jumlah;
                },
                pilihBukti(e) {
                    const file = e.target.files[0];
                    this.namaBukti = file ? file.name : '';
                    this.pratinjauBukti = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
                    if (file) this.form.hapus_bukti = false;
                },
                hapusTransaksi(t) {
                    this.menuBuka = null;
                    if (!confirm('Hapus transaksi "' + t.deskripsi + '"?')) return;
                    this.aksiHapus = this.URL + '/' + t.id;
                    this.$nextTick(() => this.$refs.formHapus.submit());
                },

                // ----- Kategori -----
                bukaKategori() {
                    this.galat = '';
                    this.formKategori = { id: null, nama: '', tipe: this.filter.tipe || 'keluar' };
                    this.$refs.dialogKategori.showModal();
                },
                ubahKategori(k) {
                    this.formKategori = { id: k.id, nama: k.nama, tipe: k.tipe };
                    this.$nextTick(() => this.$refs.namaKategori.focus());
                },
                hapusKategori(k) {
                    if (!confirm('Hapus kategori ' + k.nama + '?')) return;
                    this.aksiHapus = this.URL + '/kategori/' + k.id;
                    this.$nextTick(() => this.$refs.formHapus.submit());
                },
                // Buka menu ⋮ di bawah tombol (atau di atasnya bila dekat tepi bawah layar)
                bukaMenu(t, e) {
                    if (this.menuBuka?.id === t.id) return this.tutupMenu();
                    const r = e.currentTarget.getBoundingClientRect();
                    const muatDiBawah = window.innerHeight - r.bottom > 110;
                    this.menuBuka = { id: t.id, x: r.right - 144, y: muatDiBawah ? r.bottom + 4 : r.top - 96 };
                },
                tutupMenu() {
                    this.menuBuka = null;
                },
            },
            mounted() {
                document.addEventListener('click', this.tutupMenu);
                window.addEventListener('scroll', this.tutupMenu, true); // menu fixed: tutup saat halaman digulir

                // Setelah simpan/gagal validasi: buka lagi pop-up yang tadi dipakai
                const formLama = @json(old('form_id'));
                if (formLama === 'kategori' || @json(session('buka')) === 'kategori') {
                    this.$refs.dialogKategori.showModal();
                    if (formLama === 'kategori') this.formKategori = { id: null, nama: @json(old('nama', '')), tipe: @json(old('tipe', 'keluar')) };
                } else if (formLama) {
                    this.isiForm({
                        id: formLama === 'baru' ? null : Number(formLama),
                        tanggal: @json(old('tanggal')), tipe: @json(old('tipe', 'masuk')),
                        kas_kategori_id: @json((string) old('kas_kategori_id', '')), jumlah: @json(old('jumlah')) ? ANGKA.format(Number(@json(old('jumlah')))) : '',
                        deskripsi: @json(old('deskripsi', '')), pihak: @json(old('pihak', '')),
                        bukti: this.baris.find(t => String(t.id) === formLama)?.bukti ?? null,
                    });
                    this.$refs.dialogTransaksi.showModal();
                }
            },
            unmounted() {
                document.removeEventListener('click', this.tutupMenu);
                window.removeEventListener('scroll', this.tutupMenu, true);
            },
        }).mount('#halaman-buku-kas');
    });
</script>
@endpush
