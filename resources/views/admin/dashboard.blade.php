{{--
resources/views/admin/dashboard.blade.php

Halaman Beranda/Dashboard admin — sidebar & topbar diwarisi dari
layouts/app.blade.php. File ini HANYA berisi konten.
--}}
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div id="dashboard-app" v-cloak>

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
                <div class="h-full bg-ribath-green rounded-full" :style="{ width: stat.percent + '%' }"></div>
            </div>
        </div>
    </div>

    {{-- ===== Chart + Jadwal Hari Ini + Kalender/Tugas ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 mt-5">

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
            <p class="text-xs text-gray-400 mb-4">Total Jam: <span class="text-white font-semibold"
                    v-text="totalJam"></span> jam</p>

            <div class="flex items-center gap-4 text-xs text-gray-400 mb-2">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Teori</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-ribath-green-light"></span>
                    Praktik</span>
            </div>

            <svg viewBox="0 0 340 130" class="w-full h-32">
                <polyline fill="none" stroke="#6B7280" stroke-width="2"
                    points="0,70 48,55 96,80 144,40 192,60 240,35 288,58 336,30" />
                <polyline fill="none" stroke="#7ED957" stroke-width="2.5"
                    points="0,95 48,85 96,60 144,90 192,45 240,70 288,40 336,65" />
                <line x1="192" y1="0" x2="192" y2="130" stroke="#ffffff33" stroke-dasharray="3 3" />
                <circle cx="192" cy="45" r="4" fill="#7ED957" />
                <rect x="165" y="10" width="54" height="22" rx="6" fill="#ffffff" />
                <text x="192" y="25" text-anchor="middle" font-size="11" fill="#0E1F17" font-weight="700">18 jam</text>
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
                        <button class="w-6 h-6 rounded-md hover:bg-gray-100 text-gray-400 text-xs">‹</button>
                        <button class="w-6 h-6 rounded-md hover:bg-gray-100 text-gray-400 text-xs">›</button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-y-2 text-center text-xs">
                    <span v-for="hari in ['Sen','Sel','Rab','Kam','Jum','Sab','Min']" :key="hari" class="text-gray-400"
                        v-text="hari"></span>
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
                        <span :class="['text-[10px] font-semibold px-2.5 py-1 rounded-full', tugas.badgeClass]"
                            v-text="tugas.status"></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ===== Jadwal Kelas Mendatang ===== --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mt-5">
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
                            <span v-text="hari.hari"></span> <span class="text-gray-300" v-text="hari.tanggal"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="jam in jamSlot" :key="jam" class="border-t border-gray-100">
                        <td class="py-3 pr-2 text-gray-400 align-top" v-text="jam"></td>
                        <td v-for="hari in kelasMendatang" :key="hari.tanggal + jam" class="align-top py-2 px-2">
                            <div v-if="hari.kelas[jam]" :class="['rounded-lg px-2.5 py-2', hari.kelas[jam].warna]">
                                <p class="font-semibold text-[11px]" v-text="hari.kelas[jam].judul"></p>
                                <p class="text-[10px] opacity-80" v-text="hari.kelas[jam].topik"></p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    

    createApp({
        data() {
            return {
                
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
                kalenderTanggal: [12, 13, 14, 15, 16, 17, 18].map((d, i) => ({ date: d, aktif: i === 1 })),

                tugasList: [
                    { judul: 'Setoran Hafalan', waktu: '02:00 PM - 06:00 PM', status: 'Berjalan', badgeClass: 'bg-violet-100 text-violet-600' },
                    { judul: 'Ujian Nahwu', waktu: '02:00 PM - 06:00 PM', status: 'Selesai', badgeClass: 'bg-emerald-100 text-emerald-600' },
                    { judul: 'Praktik Muhadatsah', waktu: '02:00 PM - 06:00 PM', status: 'Mendatang', badgeClass: 'bg-orange-100 text-orange-600' },
                ],

                tampilanJadwal: 'Minggu',
                jamSlot: ['08:00', '09:00', '10:00', '11:00'],
                kelasMendatang: [
                    { hari: 'Sen', tanggal: '29', kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-gray-800 text-white' },
                    }},
                    { hari: 'Sel', tanggal: '30', kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                        '10:00': { judul: 'Kajian Kitab', topik: 'Bab thaharah', warna: 'bg-gray-100 text-gray-700' },
                    }},
                    { hari: 'Rab', tanggal: '31', kelas: {
                        '08:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                        '11:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-gray-800 text-white' },
                    }},
                    { hari: 'Kam', tanggal: '01', kelas: {
                        '09:00': { judul: 'Kajian Kitab', topik: 'Bab thaharah', warna: 'bg-gray-100 text-gray-700' },
                        '11:00': { judul: 'Kelas Tahsin', topik: 'Materi tajwid dasar', warna: 'bg-ribath-green text-white' },
                    }},
                ],
            }
        }
    }).mount('#dashboard-app');
</script>
@endpush