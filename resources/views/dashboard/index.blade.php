{{--
resources/views/admin/dashboard.blade.php

View dashboard admin Ribath — direplikasi dari desain referensi (sidebar hijau tua + konten).
Stack: Blade + Tailwind CSS (CDN) + Vue 3 (CDN).
Ini HANYA view. Route & Controller diasumsikan sudah ada di project Anda.

Cara pakai singkat:
- Ganti data dummy di dalam `data() { return {...} }` pada script Vue di bawah
dengan data asli dari Controller, misal via:
const menuItems = @json($menuItems ?? []);
const jadwalHari = @json($jadwalHariIni ?? []);
const tugasList = @json($tugas ?? []);
const kelasMinggu = @json($kelasMendatang ?? []);
lalu masukkan ke dalam `return { ... }` menggantikan array dummy.
- Kalau file ini nanti di-extend dari layout utama, cukup ambil bagian
<div id="ribath-dashboard"> ... </div> beserta <script>
    Vue-nya,
      lalu buang bagian <html><head> yang sudah ada di layout Anda.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Ribath</title>

    <script src="https://cdn.tailwindcss.com">
</script>
<script>
    tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ribath: {
                            dark: '#0E1F17',
                            darker: '#0A1912',
                            green: '#3FAE4A',
                            greenLight: '#7ED957',
                            bg: '#F3F6F3',
                        }
                    }
                }
            }
        }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

<style>
    [v-cloak] {
        display: none;
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }

    .scrollbar-thin::-webkit-scrollbar {
        width: 4px;
    }
</style>
</head>

<body class="font-sans bg-ribath-bg text-gray-800 antialiased">

    <div id="ribath-dashboard" v-cloak class="flex h-screen overflow-hidden">

        {{-- ============================= SIDEBAR ============================= --}}
        <aside class="w-64 shrink-0 bg-ribath-dark text-gray-300 flex flex-col px-5 py-6">

            {{-- Logo --}}
            <div class="flex items-center gap-2.5 px-1 mb-8">
                <div
                    class="w-9 h-9 rounded-xl bg-ribath-greenLight/90 flex items-center justify-center text-ribath-darker font-bold text-lg">
                    R
                </div>
                <span class="text-white font-bold text-lg tracking-tight">Ribath</span>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 space-y-1">
                <a v-for="item in menuItems" :key="item.key" href="#" @click.prevent="setActive(item.key)" :class="[
                    'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors',
                    activeMenu === item.key
                        ? 'bg-ribath-greenLight text-ribath-darker font-semibold shadow-sm'
                        : 'text-gray-400 hover:bg-white/5 hover:text-gray-100'
               ]">
                    <span v-html="item.icon" class="w-5 h-5 shrink-0"></span>
                    <span v-text="item.label"></span>
                </a>
            </nav>

            {{-- Kartu bantuan (pengganti "Go Premium") --}}
            <div class="mt-6 rounded-2xl bg-white/5 border border-white/10 p-4 text-center">
                <div
                    class="w-10 h-10 mx-auto rounded-full bg-ribath-greenLight/20 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-ribath-greenLight" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.451.999-1.451 1.827v.75M12 17.25h.008v.008H12v-.008Z" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </div>
                <p class="text-white text-sm font-semibold mb-1">Butuh Bantuan?</p>
                <p class="text-gray-400 text-xs mb-3 leading-relaxed">Lihat panduan penggunaan dashboard admin</p>
                <button
                    class="w-full bg-ribath-greenLight text-ribath-darker text-xs font-semibold py-2 rounded-lg hover:brightness-95 transition">
                    Lihat Panduan
                </button>
            </div>
        </aside>

        {{-- ============================= KONTEN ============================= --}}
        <div class="flex-1 overflow-y-auto">

            {{-- Topbar --}}
            <header class="flex items-center justify-between px-8 pt-7 pb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Selamat Datang, {{ auth()->user()->name ?? 'Admin' }} 👋
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative hidden md:block">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" placeholder="Cari Santri, Ustadz..."
                            class="bg-white border border-gray-200 rounded-full text-sm pl-10 pr-4 py-2.5 w-64 focus:outline-none focus:ring-2 focus:ring-ribath-green/30" />
                    </div>

                    <button
                        class="relative w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="absolute top-1.5 right-2 w-2 h-2 rounded-full bg-ribath-green"></span>
                    </button>

                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0E1F17&color=fff"
                        class="w-10 h-10 rounded-full object-cover" alt="Avatar admin" />
                </div>
            </header>

            <main class="px-8 pb-10 space-y-5">

                {{-- ===== Stat cards ===== --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div v-for="stat in statCards" :key="stat.label"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-0.5" v-text="stat.label"></p>
                                <p class="text-xs text-emerald-600 font-medium" v-text="stat.trend"></p>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-ribath-bg flex items-center justify-center text-gray-500"
                                v-html="stat.icon"></div>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 mb-2" v-text="stat.value"></p>
                        <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                            <span v-text="stat.sublabel"></span>
                            <span v-text="stat.fraction"></span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-ribath-green rounded-full" :style="{ width: stat.percent + '%' }">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Chart + Jadwal Hari Ini + Kalender/Tugas ===== --}}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">

                    {{-- Jam Mengajar --}}
                    <div class="xl:col-span-5 bg-ribath-dark rounded-2xl p-5 text-white">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold">Jam Mengajar</h3>
                            <select v-model="chartRange"
                                class="bg-white/10 text-xs rounded-lg px-2.5 py-1.5 border-0 focus:outline-none">
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">Total Jam: <span class="text-white font-semibold">
                            {{-- {{ '{{ totalJam }}' }} jam --}}
                        100 /sementara
                        </span></p>

                        <div class="flex items-center gap-4 text-xs text-gray-400 mb-2">
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2 h-2 rounded-full bg-gray-400"></span> Teori</span>
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2 h-2 rounded-full bg-ribath-greenLight"></span> Praktik</span>
                        </div>

                        <svg viewBox="0 0 340 130" class="w-full h-32">
                            <polyline fill="none" stroke="#6B7280" stroke-width="2"
                                points="0,70 48,55 96,80 144,40 192,60 240,35 288,58 336,30" />
                            <polyline fill="none" stroke="#7ED957" stroke-width="2.5"
                                points="0,95 48,85 96,60 144,90 192,45 240,70 288,40 336,65" />
                            <line x1="192" y1="0" x2="192" y2="130" stroke="#ffffff33" stroke-dasharray="3 3" />
                            <circle cx="192" cy="45" r="4" fill="#7ED957" />
                            <rect x="165" y="10" width="54" height="22" rx="6" fill="#ffffff" />
                            <text x="192" y="25" text-anchor="middle" font-size="11" fill="#0E1F17" font-weight="700">18
                                jam</text>
                        </svg>
                        <div class="grid grid-cols-7 text-[10px] text-gray-500 mt-1">
                            <span class="text-center" v-for="hari in ['Sen','Sel','Rab','Kam','Jum','Sab','Min']"
                                v-text="hari"></span>
                        </div>
                    </div>

                    {{-- Jadwal Hari Ini --}}
                    <div class="xl:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">Jadwal Hari Ini</h3>
                        <ul class="space-y-4">
                            <li v-for="(jadwal, i) in jadwalHariIni" :key="i" class="flex gap-3">
                                <span class="w-2 h-2 rounded-full mt-1.5 shrink-0" :class="jadwal.color"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800" v-text="jadwal.judul"></p>
                                    <p class="text-xs text-gray-400" v-text="jadwal.waktu"></p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- Kalender mini + Tugas --}}
                    <div class="xl:col-span-4 space-y-5">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 text-sm" v-text="bulanTahun"></h3>
                                <div class="flex gap-1">
                                    <button
                                        class="w-6 h-6 rounded-md hover:bg-gray-100 text-gray-400 text-xs">‹</button>
                                    <button
                                        class="w-6 h-6 rounded-md hover:bg-gray-100 text-gray-400 text-xs">›</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 gap-y-2 text-center text-xs">
                                <span v-for="hari in ['Sen','Sel','Rab','Kam','Jum','Sab','Min']" :key="hari"
                                    class="text-gray-400" v-text="hari"></span>
                                <span v-for="tgl in kalenderTanggal" :key="tgl.date" :class="[
                                    'w-7 h-7 mx-auto flex items-center justify-center rounded-lg',
                                    tgl.aktif ? 'bg-ribath-green text-white font-semibold' : 'text-gray-600'
                                  ]" v-text="tgl.date"></span>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 text-sm">Tugas</h3>
                                <button class="text-gray-400 hover:text-gray-600">+</button>
                            </div>
                            <ul class="space-y-3">
                                <li v-for="(tugas, i) in tugasList" :key="i" class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800" v-text="tugas.judul"></p>
                                        <p class="text-xs text-gray-400" v-text="tugas.waktu"></p>
                                    </div>
                                    <span
                                        :class="['text-[10px] font-semibold px-2.5 py-1 rounded-full', tugas.badgeClass]"
                                        v-text="tugas.status"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- ===== Jadwal Kelas Mendatang ===== --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Jadwal Kelas Mendatang</h3>
                        <div class="flex bg-ribath-bg rounded-full p-1 text-xs">
                            <button v-for="v in ['Hari','Minggu','Bulan']" :key="v" @click="tampilanJadwal = v"
                                :class="['px-3 py-1.5 rounded-full font-medium transition', tampilanJadwal === v ? 'bg-ribath-dark text-white' : 'text-gray-500']"
                                v-text="v"></button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse min-w-[640px]">
                            <thead>
                                <tr>
                                    <th class="w-16"></th>
                                    <th v-for="hari in kelasMendatang" :key="hari.tanggal"
                                        class="text-left text-gray-400 font-medium pb-3 px-2">
                                        <span v-text="hari.hari"></span> <span class="text-gray-300"
                                            v-text="hari.tanggal"></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="jam in jamSlot" :key="jam" class="border-t border-gray-100">
                                    <td class="py-3 pr-2 text-gray-400 align-top" v-text="jam"></td>
                                    <td v-for="hari in kelasMendatang" :key="hari.tanggal + jam"
                                        class="align-top py-2 px-2">
                                        <div v-if="hari.kelas[jam]"
                                            :class="['rounded-lg px-2.5 py-2', hari.kelas[jam].warna]">
                                            <p class="font-semibold text-[11px]" v-text="hari.kelas[jam].judul"></p>
                                            <p class="text-[10px] opacity-80" v-text="hari.kelas[jam].topik"></p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

createApp({
    data() {
        return {
           
            activeMenu: 'beranda',
            menuItems: [
                { key: 'beranda', label: 'Beranda', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>' },
                { key: 'santri', label: 'Data Santri', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443" /></svg>' },
                { key: 'ustadz', label: 'Data Ustadz', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>' },
                { key: 'pesan', label: 'Pesan', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>' },
                { key: 'notifikasi', label: 'Notifikasi', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>' },
                { key: 'kalender', label: 'Kalender', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>' },
                { key: 'komunitas', label: 'Komunitas', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>' },
                { key: 'pengaturan', label: 'Pengaturan', icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>' },
            ],

            // ---- Ganti dengan data asli dari Controller ----
            statCards: [
                { label: 'Santri Aktif', value: '186', trend: '+12 bulan ini', sublabel: 'Aktif', fraction: '186/210', percent: 88, icon: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>' },
                { label: 'Jam Mengajar', value: '18.5', trend: '+3.2 minggu ini', sublabel: 'progres', fraction: '+3.2', percent: 62, icon: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>' },
                { label: 'Ustadz Aktif', value: '24', trend: '4 pengajar baru', sublabel: 'Pending', fraction: '24/30', percent: 80, icon: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347" /></svg>' },
                { label: 'Tingkat Kehadiran', value: '96%', trend: '+5% bulan ini', sublabel: 'Kelas', fraction: '186/194', percent: 96, icon: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>' },
            ],

            chartRange: 'mingguan',
            totalJam: '6',

            jadwalHariIni: [
                { judul: 'Kajian Kitab (Fiqih)', waktu: '08:00 - 09:00', color: 'bg-gray-800' },
                { judul: 'Tahfidz Dasar', waktu: '02:00 - 03:00', color: 'bg-gray-800' },
                { judul: 'Kelas Bahasa Arab', waktu: '02:00 - 03:00', color: 'bg-gray-800' },
                { judul: 'Nahwu Sharaf', waktu: '04:30 - 05:00', color: 'bg-ribath-green' },
            ],

            bulanTahun: 'Jan 2026',
            kalenderTanggal: [
                ...[12, 13, 14, 15, 16, 17, 18].map((d, i) => ({ date: d, aktif: i === 1 })),
            ],

            tugasList: [
                { judul: 'Setoran Hafalan', waktu: '02:00 PM - 06:00 PM', status: 'Berjalan', badgeClass: 'bg-violet-100 text-violet-600' },
                { judul: 'Ujian Nahwu', waktu: '02:00 PM - 06:00 PM', status: 'Selesai', badgeClass: 'bg-emerald-100 text-emerald-600' },
                { judul: 'Praktik Muhadatsah', waktu: '02:00 PM - 06:00 PM', status: 'Mendatang', badgeClass: 'bg-orange-100 text-orange-600' },
            ],

            tampilanJadwal: 'Minggu',
            jamSlot: ['08:00', '09:00', '10:00', '11:00'],
            kelasMendatang: [
                {
                    hari: 'Sen', tanggal: '29',
                    kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-gray-800 text-white' },
                        '10:00': { judul: '', topik: '', warna: '' },
                    }
                },
                {
                    hari: 'Sel', tanggal: '30',
                    kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                        '10:00': { judul: 'Kajian Kitab', topik: 'Bab thaharah', warna: 'bg-gray-100 text-gray-700' },
                    }
                },
                {
                    hari: 'Rab', tanggal: '31',
                    kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                        '11:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-gray-800 text-white' },
                    }
                },
                {
                    hari: 'Kam', tanggal: '01',
                    kelas: {
                        '09:00': { judul: 'Kajian Kitab', topik: 'Bab thaharah', warna: 'bg-gray-100 text-gray-700' },
                        '11:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                    }
                },
            ],
        }
    },
    methods: {
        setActive(key) {
            this.activeMenu = key;
        }
    }
}).mount('#ribath-dashboard');
    </script>

</body>

</html>