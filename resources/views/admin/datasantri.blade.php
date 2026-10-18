{{--
View: Data Santri - mode demo.
Semua data di bawah ini adalah data dummy JavaScript yang dikelola Vue 3.
View tidak membutuhkan data dari controller atau database.
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
        class="bg-linear-to-r from-ribath-green to-ribath-green-light rounded-2xl px-6 py-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mx-4 md:mx-10 lg:mx-20 xl:mx-50">
        <div>
            <h1 class="text-white text-xl font-bold">Data Santri</h1>
            <p class="text-teal-100 text-sm mt-0.5">Total @{{ totalSantri }} Santri</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="kelolaMassal = !kelolaMassal"
                class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z" />
                </svg>
                Kelola Massal
            </button>

            <a href="#" @click.prevent="notifikasiDemo('Form tambah santri')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Santri (Mode Detail)
            </a>

            <a href="#" @click.prevent="notifikasiDemo('Form tambah cepat')"
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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6 mx-4 md:mx-10 lg:mx-20 xl:mx-50">
        <div v-for="kelas in kelasStats" :key="kelas.nama"
            class="bg-[#0E1F17]/90 border border-4 border-ribath-green-light rounded-xl p-4 shadow-sm hover:shadow-xl transition">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" :class="kelas.warna">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z" />
                    <path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5" />
                </svg>
            </div>
            <p class="text-sm text-gray-300">@{{ kelas.nama }}</p>
            <p class="text-2xl font-bold text-gray-100 mt-0.5">@{{ kelas.jumlah }}</p>
        </div>
    </div>

    {{-- ================= SEARCH + FILTER ================= --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4 mx-4 md:mx-10 lg:mx-20 xl:mx-50">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" v-model="pencarian" placeholder="Cari santri (nama, tempat lahir, alamat)..."
                class="w-full border border-ribath-green-light rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ribath-green focus:border-transparent">
        </div>
        <button type="button" @click="filterTerbuka = !filterTerbuka"
            class="inline-flex items-center justify-center gap-2 border border-ribath-green-light rounded-lg px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Filter
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </button>
    </div>

    {{-- ================= TABEL SANTRI ================= --}}
    <div
        class="bg-white border border-ribath-green-light rounded-xl overflow-hidden shadow-sm mx-4 md:mx-10 lg:mx-20 xl:mx-50">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-ribath-green-light/30 text-left text-gray-800 border-b border-ribath-green-light/50">
                        <th class="px-4 py-5 w-10">
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
                        <th class="px-4 py-5 font-medium text-base">Tanggal Masuk</th>
                        <th class="px-4 py-5 font-medium text-base">Wali Santri</th>
                        <th class="px-4 py-5 font-medium text-base">Status</th>
                        <th class="px-4 py-5 font-medium text-base">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(santri, index) in santriTersaring" :key="santri.id"
                        class="border-b border-ribath-green-light/40 last:border-0 hover:bg-ribath-green/5 transition">
                        <td class="px-4 py-3">
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
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
                                    </svg>
                                </div>
                                <span class="text-gray-700 lowercase">@{{ santri.nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-md"
                                :class="warnaBadgeKelas(santri.kelas)">
                                @{{ santri.kelas }}
                            </span>
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
                                <a href="#" @click.prevent="lihatDetail(santri)"
                                    class="text-gray-400 hover:text-teal-600 transition" title="Lihat detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>
                                <button type="button" @click="hapusSantri(santri)"
                                    class="text-red-400 hover:text-red-600 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="santriTersaring.length === 0">
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
                            Tidak ada santri yang cocok dengan pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@once
@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                kelasMeta: [
                    { nama: 'Tamhidi', warna: 'bg-blue-500' },
                    { nama: 'Ibtida 1', warna: 'bg-green-500' },
                    { nama: 'Ibtida 2', warna: 'bg-amber-400' },
                    { nama: 'Tsanawiyah 1', warna: 'bg-purple-500' },
                    { nama: 'Tsanawiyah 2', warna: 'bg-red-500' },
                    { nama: 'Tahfidz 1', warna: 'bg-emerald-700' },
                    { nama: 'Tahfidz 2', warna: 'bg-emerald-600' },
                    { nama: 'Tahfidz 3', warna: 'bg-emerald-800' },
                    { nama: 'Takhassus 1', warna: 'bg-orange-400' },
                    { nama: 'Takhassus 2', warna: 'bg-orange-600' },
                    { nama: 'Takhassus 3', warna: 'bg-orange-800' },
                ],
                santriList: [
                    { id: 1, nama: 'Ahmad Fauzan', kelas: 'Tamhidi', tanggal_masuk: '10 Jul 2026', wali: 'H. Rahmat', status: 'aktif', punya_skema_biaya: false },
                    { id: 2, nama: 'Bilal Ramadhan', kelas: 'Ibtida 1', tanggal_masuk: '11 Jul 2026', wali: 'Abdullah', status: 'aktif', punya_skema_biaya: true },
                    { id: 3, nama: 'Yusuf Al Farizi', kelas: 'Ibtida 1', tanggal_masuk: '11 Jul 2026', wali: 'Hasan Basri', status: 'aktif', punya_skema_biaya: true },
                    { id: 4, nama: 'Zaid Nur Hidayat', kelas: 'Ibtida 2', tanggal_masuk: '12 Jul 2026', wali: 'M. Hidayat', status: 'aktif', punya_skema_biaya: false },
                    { id: 5, nama: 'Hamzah Hakim', kelas: 'Tsanawiyah 1', tanggal_masuk: '13 Jul 2026', wali: 'Abdul Hakim', status: 'aktif', punya_skema_biaya: true },
                    { id: 6, nama: 'Fikri Maulana', kelas: 'Tahfidz 1', tanggal_masuk: '14 Jul 2026', wali: 'Maulana Yusuf', status: 'aktif', punya_skema_biaya: true },
                    { id: 7, nama: 'Naufal Akbar', kelas: 'Tahfidz 1', tanggal_masuk: '14 Jul 2026', wali: null, status: 'aktif', punya_skema_biaya: false },
                    { id: 8, nama: 'Rafi Pratama', kelas: 'Tahfidz 2', tanggal_masuk: '15 Jul 2026', wali: 'Dedi Pratama', status: 'aktif', punya_skema_biaya: true },
                    { id: 9, nama: 'Salman Al Ghifari', kelas: 'Takhassus 1', tanggal_masuk: '16 Jul 2026', wali: 'Ghifari Ahmad', status: 'nonaktif', punya_skema_biaya: true },
                    { id: 10, nama: 'Ilyas Kurniawan', kelas: 'Tahfidz 3', tanggal_masuk: '17 Jul 2026', wali: 'Agus Kurniawan', status: 'aktif', punya_skema_biaya: false },
                ],
                pencarian: '',
                filterTerbuka: false,
                kelolaMassal: false,
                pilihSemua: false,
                terpilih: [],
            };
        },
        computed: {
            kelasStats() {
                return this.kelasMeta.map(kelas => ({
                    ...kelas,
                    jumlah: this.santriList.filter(santri => santri.kelas === kelas.nama).length,
                }));
            },
            totalSantri() {
                return this.santriList.length;
            },
            tanpaSkemaBiaya() {
                return this.santriList.filter(santri => !santri.punya_skema_biaya).length;
            },
            santriTersaring() {
                const kata = this.pencarian.toLowerCase().trim();
                if (!kata) return this.santriList;

                return this.santriList.filter(santri =>
                    santri.nama.toLowerCase().includes(kata) ||
                    (santri.kelas || '').toLowerCase().includes(kata) ||
                    (santri.wali || '').toLowerCase().includes(kata)
                );
            },
        },
        methods: {
            toggleSemua() {
                this.terpilih = this.pilihSemua
                    ? this.santriTersaring.map(santri => santri.id)
                    : [];
            },
            warnaBadgeKelas(nama) {
                const map = {
                    'Tamhidi': 'bg-blue-50 text-blue-600',
                    'Ibtida 1': 'bg-green-50 text-green-600',
                    'Ibtida 2': 'bg-amber-50 text-amber-600',
                    'Tsanawiyah 1': 'bg-purple-50 text-purple-600',
                    'Tsanawiyah 2': 'bg-red-50 text-red-600',
                    'Tahfidz 1': 'bg-emerald-50 text-emerald-700',
                    'Tahfidz 2': 'bg-emerald-50 text-emerald-600',
                    'Tahfidz 3': 'bg-emerald-50 text-emerald-800',
                    'Takhassus 1': 'bg-orange-50 text-orange-600',
                    'Takhassus 2': 'bg-orange-50 text-orange-700',
                    'Takhassus 3': 'bg-orange-50 text-orange-800',
                };

                return map[nama] || 'bg-gray-50 text-gray-600';
            },
            notifikasiDemo(fitur) {
                window.alert(fitur + ' belum diaktifkan karena halaman sedang memakai data dummy.');
            },
            lihatDetail(santri) {
                window.alert('Demo detail santri: ' + santri.nama);
            },
            hapusSantri(santri) {
                if (!window.confirm('Hapus data dummy "' + santri.nama + '"?')) return;

                this.santriList = this.santriList.filter(item => item.id !== santri.id);
                this.terpilih = this.terpilih.filter(id => id !== santri.id);
                this.pilihSemua = false;
            },
        },
    }).mount('#data-santri-app');
</script>
@endpush
@endonce
@endsection