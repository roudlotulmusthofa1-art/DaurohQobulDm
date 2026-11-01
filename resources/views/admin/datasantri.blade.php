{{--
View: Data Santri.
Data dari database (AdminSantriController@index):
- $santri     : pendaftar berstatus "diterima", lengkap dengan nama kelas & kamar dari Master Data
- $kelasStats : jumlah santri per kelas (Master Data > Kelas)
- $tanpaKelas : jumlah santri yang belum ditempatkan di kelas
Vue 3 (window.Vue dari resources/js/app.js) hanya mengatur pencarian & centang.
--}}
@extends('layouts.admin')

@section('title', 'Data Santri')

@section('content')
<div id="data-santri-app">

    {{-- ================= ALERT: SANTRI BELUM PUNYA SKEMA BIAYA ================= --}}
    {{-- <div v-if="tanpaSkemaBiaya > 0"
        class="flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-5 py-4 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 flex-shrink-0 text-amber-500" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
        <div class="text-sm">
            <p class="font-semibold">@{{ tanpaSkemaBiaya }} santri belum memiliki skema biaya</p>
            <p class="text-amber-700">Buka detail santri lalu klik tab "Biaya" untuk mengatur tarif locked.</p>
        </div>
    </div> --}}

    {{-- ================= HEADER: DATA SANTRI ================= --}}
    <div
        class="bg-linear-to-r from-[#07662ed0] to-[#1ef963cf] rounded-2xl px-6 py-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mx-4 md:mx-10 lg:mx-20 mt-8">
        <div>
            <h1 class="text-white text-xl font-bold">Data Santri</h1>
            <p class="text-teal-100 text-sm mt-0.5">Total @{{ totalSantri }} Santri</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Kelola Massal: munculkan centang + panel pindah kelas/kamar --}}
            <button type="button" @click="toggleMassal"
                class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition"
                :class="kelolaMassal ? 'ring-2 ring-white ring-offset-2 ring-offset-purple-600' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z" />
                </svg>
                <span v-text="kelolaMassal ? 'Selesai Kelola' : 'Kelola Massal'"></span>
            </button>

            {{-- Mode Detail: formulir PSB lengkap di tab baru, lalu diterima lewat Data Masuk Peserta --}}
            <a href="{{ route('psb.daftar') }}" target="_blank" rel="noopener"
                title="Buka formulir pendaftaran lengkap (tab baru). Setelah dikirim, terima lewat menu Data Masuk Peserta."
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Santri (Mode Detail)
            </a>

            {{-- Tambah Cepat: form singkat, langsung jadi santri --}}
            <a href="#" @click.prevent="bukaTambahCepat"
                class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Cepat
            </a>
        </div>
    </div>

    {{-- ================= GRID: STATISTIK PER KELAS ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6 mx-4 md:mx-10 lg:mx-20 mt-8">
        {{-- Kartu modern: latar gradasi hijau gelap (sama seperti sidebar) + warna aksen per kelas.
             --aksen = warna aksen kartu itu (dari WARNA_AKSEN di script bawah). --}}
        <div v-for="kelas in kelasStats" :key="kelas.nama" :style="{ '--aksen': kelas.aksen }"
            class="group relative overflow-hidden rounded-2xl p-4 text-white
                   bg-[linear-gradient(145deg,#0f4238_0%,#0b332c_60%,#0a2b25_100%)]
                   ring-1 ring-white/10 shadow-[0_8px_24px_-12px_rgba(11,51,44,.6)]
                   transition duration-300 hover:-translate-y-1 hover:ring-(--aksen)
                   hover:shadow-[0_18px_40px_-16px_var(--aksen)]">
            {{-- Cahaya warna aksen di pojok kanan atas (makin terang saat disorot) --}}
            <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-(--aksen) opacity-20 blur-2xl transition-opacity duration-300 group-hover:opacity-45"></span>
            {{-- Kilau yang menyapu kartu saat disorot --}}
            <span class="pointer-events-none absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/10 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
            {{-- Ikon besar samar sebagai hiasan --}}
            <svg class="pointer-events-none absolute -bottom-4 -right-4 h-20 w-20 text-white/5 transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-110"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z" />
                <path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5" />
            </svg>

            <div class="relative">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-(--aksen) text-[#0b332c] shadow-[0_6px_16px_-6px_var(--aksen)] transition-transform duration-300 group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z" />
                            <path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5" />
                        </svg>
                    </div>
                    <span class="rounded-full bg-white/10 px-2 py-0.5 text-[11px] font-medium text-white/70">@{{ kelas.persen }}%</span>
                </div>

                <p class="mt-3 text-sm text-white/60 truncate" :title="kelas.nama">@{{ kelas.nama }}</p>
                <p class="mt-0.5 text-2xl font-bold">@{{ kelas.jumlah }} <span class="text-xs font-normal text-white/45">santri</span></p>

                {{-- Bar: porsi santri kelas ini dari seluruh santri --}}
                <div class="mt-3 h-1.5 rounded-full bg-white/10 overflow-hidden">
                    <div class="h-full rounded-full bg-(--aksen) transition-[width] duration-700" :style="{ width: kelas.persen + '%' }"></div>
                </div>
            </div>
        </div>

        {{-- Santri yang belum dipilihkan kelas (aksen kuning sebagai peringatan) --}}
        <div v-if="tanpaKelas > 0" style="--aksen: #fbbf24"
            class="group relative overflow-hidden rounded-2xl p-4 text-white
                   bg-[linear-gradient(145deg,#0f4238_0%,#0b332c_60%,#0a2b25_100%)]
                   border border-dashed border-(--aksen)/60 shadow-[0_8px_24px_-12px_rgba(11,51,44,.6)]
                   transition duration-300 hover:-translate-y-1 hover:ring-(--aksen)
                   hover:shadow-[0_18px_40px_-16px_var(--aksen)]">
            <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-[var(--aksen)] opacity-20 blur-2xl transition-opacity duration-300 group-hover:opacity-45"></span>
            <span class="pointer-events-none absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/10 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>

            <div class="relative">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-(--aksen) text-[#0b332c] shadow-[0_6px_16px_-6px_var(--aksen)] animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <p class="mt-3 text-sm text-white/60">Belum ada kelas</p>
                <p class="mt-0.5 text-2xl font-bold">@{{ tanpaKelas }} <span class="text-xs font-normal text-white/45">santri</span></p>
                <p class="mt-3 text-[11px] text-amber-300/80">Pilih kelasnya lewat Kelola Massal</p>
            </div>
        </div>

        {{-- Master Data masih kosong --}}
        <a v-if="kelasStats.length === 0" href="{{ route('admin.master-data.index', ['tab' => 'kelas']) }}"
            class="col-span-2 sm:col-span-3 lg:col-span-4 flex items-center rounded-xl border-2 border-dashed border-ribath-green-light/60 p-4 text-sm text-gray-500 hover:bg-white/60">
            Belum ada kelas di Master Data. Klik di sini untuk menambahkan kelas.
        </a>
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
            <input type="text" v-model="pencarian" placeholder="Cari santri (nama latin/Arab, no. pendaftaran, kelas, kamar, wali)..."
                class="w-full border border-ribath-green-light rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green focus:border-transparent">
        </div>
        <button type="button" @click="filterTerbuka = !filterTerbuka"
                    class="inline-flex items-center justify-center gap-2 border rounded-lg px-4 py-2.5 text-sm font-medium transition"
                    :class="jumlahFilterAktif ? 'border-ribath-green bg-ribath-green text-white' : 'border-ribath-green-light text-gray-600 hover:bg-gray-50'">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Filter
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
            <label class="text-xs font-medium text-gray-500">Kelas
                <select v-model="filter.kelas"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">Semua kelas</option>
                    <option value="kosong">Belum ada kelas</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                </select>
            </label>
            <label class="text-xs font-medium text-gray-500">Kamar
                <select v-model="filter.kamar"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">Semua kamar</option>
                    <option value="kosong">Belum ada kamar</option>
                    <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                </select>
            </label>
            <label class="text-xs font-medium text-gray-500">Status
                <select v-model="filter.status"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-ribath-green-light">
                    <option value="">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="belum punya akun">Belum punya akun</option>
                </select>
            </label>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>Menampilkan <b v-text="santriTersaring.length"></b> dari <b v-text="santriList.length"></b> santri</span>
            <button type="button" @click="filter = { kelas: '', kamar: '', status: '' }"
                class="font-medium text-red-600 hover:underline">Reset filter</button>
        </div>
    </div>
    {{-- ================= PANEL KELOLA MASSAL ================= --}}
    <div v-if="kelolaMassal"
        class="mb-4 mx-4 md:mx-10 lg:mx-20 xl:mx-50 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            <p class="text-sm font-medium text-purple-800 shrink-0">
                <span v-text="terpilih.length"></span> santri dipilih
            </p>
            <div class="flex flex-1 flex-col sm:flex-row gap-2">
                <select v-model="massal.kelas_id" class="flex-1 rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm">
                    <option :value="null">Kelas: tidak diubah</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                </select>
                <select v-model="massal.kamar_id" class="flex-1 rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm">
                    <option :value="null">Kamar: tidak diubah</option>
                    <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                </select>
            </div>
            <button type="button" @click="terapkanMassal" :disabled="massal.loading"
                class="shrink-0 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-60"
                v-text="massal.loading ? 'Menyimpan…' : 'Terapkan'"></button>
        </div>
        <p v-if="massal.error" class="mt-2 text-xs text-red-600" v-text="massal.error"></p>
        <p class="mt-1 text-xs text-purple-700/70">Centang santri di tabel, pilih kelas dan/atau kamar tujuan, lalu klik Terapkan.</p>
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
                        <th class="px-4 py-5 font-medium text-base">No</th>
                        <th class="px-4 py-5 font-medium text-base">Nama Santri</th>
                        <th class="px-4 py-5 font-medium text-base">Kelas</th>
                        <th class="px-4 py-5 font-medium text-base">Kamar</th>
                        <th class="px-4 py-5 font-medium text-base">Tanggal Masuk</th>
                        <th class="px-4 py-5 font-medium text-base">Wali Santri</th>
                        <th class="px-4 py-5 font-medium text-base">Status</th>
                        <th class="px-4 py-5 font-medium text-base">Aksi</th>
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
                                                                <img v-if="santri.foto" :src="santri.foto" :alt="'Foto ' + santri.nama" v-on:error="santri.foto = null" loading="lazy"
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
                                    <span class="block text-xs text-gray-400">@{{ santri.nomor }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="santri.kelas" class="inline-block text-xs font-medium px-2.5 py-1 rounded-md"
                                :class="warnaBadgeKelas(santri.kelas)">
                                @{{ santri.kelas }}
                            </span>
                            <span v-else class="text-xs text-amber-600">Belum dipilih</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <span v-if="santri.kamar">@{{ santri.kamar }}</span>
                            <span v-else class="text-xs text-amber-600">Belum dipilih</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">@{{ santri.tanggal_masuk }}</td>
                        <td class="px-4 py-3 text-gray-400">@{{ santri.wali || '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-md"
                                :class="santri.status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500'">
                                @{{ santri.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                {{-- Detail = halaman pendaftaran santri (di sana kelas/kamar bisa diubah) --}}
                                <a :href="santri.detail_url"
                                    class="text-gray-400 hover:text-teal-600 transition" title="Lihat detail / ubah kelas & kamar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>

                                {{-- Tempat sampah: hapus santri (konfirmasi dulu) --}}
                                <button type="button" @click="hapus(santri)" :disabled="menghapus === santri.id"
                                    class="text-red-400 hover:text-red-600 transition disabled:opacity-40" title="Hapus santri">
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
                        <td :colspan="kelolaMassal ? 9 : 8" class="px-4 py-10 text-center text-gray-400 text-sm">
                            @{{ santriList.length === 0 ? 'Belum ada santri. Santri muncul di sini setelah pendaftarannya diterima.' : 'Tidak ada santri yang cocok dengan pencarian.' }}
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
                <h3 class="text-base font-semibold text-gray-900">Tambah Cepat Santri</h3>
                <p class="text-xs text-gray-500">Santri langsung tersimpan dengan status <strong>Diterima</strong>. Data lengkap bisa dilengkapi nanti dari halaman detail.</p>
            </div>
            <div class="grid grid-cols-2 gap-4 px-6 py-5">
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nama Santri <span class="text-red-500">*</span></label>
                    <input id="tambah-nama" v-model="tambah.form.nama_lengkap" type="text" required dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                    <p v-if="tambah.errors.nama_lengkap" class="mt-1 text-xs text-red-600" v-text="tambah.errors.nama_lengkap[0]"></p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nama Wali</label>
                    <input v-model="tambah.form.nama_wali" type="text" dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">No. WA Wali</label>
                    <input v-model="tambah.form.no_hp_wali" type="tel" placeholder="08..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                    <p v-if="tambah.errors.no_hp_wali" class="mt-1 text-xs text-red-600" v-text="tambah.errors.no_hp_wali[0]"></p>
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Kota</label>
                    <input v-model="tambah.form.kota" type="text" dir="auto"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green/30">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Kelas</label>
                    <select v-model="tambah.form.kelas_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option :value="null">Belum dipilih</option>
                        <option v-for="k in kelasOptions" :key="k.id" :value="k.id" v-text="k.nama"></option>
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Kamar</label>
                    <select v-model="tambah.form.kamar_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option :value="null">Belum dipilih</option>
                        <option v-for="k in kamarOptions" :key="k.id" :value="k.id" v-text="k.label"></option>
                    </select>
                    <p v-if="tambah.errors.kamar_id" class="mt-1 text-xs text-red-600" v-text="tambah.errors.kamar_id[0]"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="tambah.buka = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="tambah.loading"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60"
                    v-text="tambah.loading ? 'Menyimpan...' : 'Simpan'"></button>
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
            const pesan = res.status === 419 ? 'Sesi habis, silakan muat ulang halaman.' : (data.message || 'Terjadi kesalahan. Coba lagi.');
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
            };
        },
        computed: {
            // Jumlah per kelas dihitung dari tabel, jadi langsung ikut berubah setelah pindah kelas
            kelasStats() {
                const total = this.santriList.length;
                return this.kelasOptions.map((kelas, i) => {
                    const jumlah = this.santriList.filter(santri => santri.kelas_id === kelas.id).length;
                    return {
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

            // ----- Kelola Massal -----
            toggleMassal() {
                this.kelolaMassal = !this.kelolaMassal;
                this.terpilih = [];
                this.pilihSemua = false;
                this.massal = { kelas_id: null, kamar_id: null, loading: false, error: '' };
            },
            async terapkanMassal() {
                if (!this.terpilih.length) return (this.massal.error = 'Centang minimal satu santri.');
                if (!this.massal.kelas_id && !this.massal.kamar_id) return (this.massal.error = 'Pilih kelas atau kamar tujuan.');

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
                if (!window.confirm(`Hapus santri "${santri.nama}"?\nBerkas unggahan dan akun login siswanya juga ikut terhapus.`)) return;

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
