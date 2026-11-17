{{--
    Admin > Laporan > Laporan Nilai (Admin\LaporanNilaiController@index).
    Ringkasan nilai Kelas & Pelajaran per santri: Harian %, Lisan %, Ujian %, Nilai Akhir % (rata-rata bagian yang ada),
    warna nilai (tulisan predikat Mumtaz/Jayyid dihapus atas permintaan), dan ranking (dihitung ulang kalau satu kelas dipilih).
    Dua tampilan: Kartu Siswa (klik = rincian semua nilai) | Semua Siswa (tabel + ranking).
    Semua data dikirim sekali ($santri), saringan & ranking dihitung Vue: #halaman-laporan-nilai.
--}}
@extends('layouts.admin')

@section('title', __('Laporan Nilai'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$tutup = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

<div id="halaman-laporan-nilai" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Laporan')" :judul="__('Laporan Nilai')"
        :keterangan="__('Ringkasan nilai harian, ujian lisan, dan ujian setiap santri, lengkap dengan persentase dan ranking.')">
        <x-slot:aksi>
            <a href="{{ route('admin.penilaian.kelas.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-emerald-400 text-[#0b332c] hover:bg-emerald-300">
                {{ __('Kelas & Pelajaran') }} &rsaquo;
            </a>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Santri dinilai') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums"><span v-text="ringkas.dinilai"></span><span class="text-base text-slate-400">/<span v-text="ringkas.semua"></span></span></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ __('punya minimal satu nilai') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Rata-rata nilai akhir') }}</p>
            <p class="mt-1 text-3xl font-extrabold tabular-nums" :class="predikat(ringkas.rata).teks" v-text="persen(ringkas.rata)"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5 min-w-0">
            <p class="text-xs font-semibold text-slate-500">{{ __('Nilai tertinggi') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-amber-500 tabular-nums" v-text="ringkas.terbaik ? persen(ringkas.terbaik.akhir) : '–'"></p>
            <p class="mt-1 text-[11px] text-slate-400 truncate" v-text="ringkas.terbaik ? ringkas.terbaik.nama : ''"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Rata-rata per bagian') }}</p>
            <div class="mt-2 space-y-1.5">
                <div v-for="b in BAGIAN" :key="b.kunci" class="flex items-center gap-2 text-xs">
                    <span class="w-12 shrink-0 font-semibold text-slate-500" v-text="b.label"></span>
                    <span class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden"><span class="block h-full rounded-full" :class="b.batang" :style="{ width: (ringkas.bagian[b.kunci] || 0) + '%' }"></span></span>
                    <span class="w-10 shrink-0 text-right font-bold text-slate-700 tabular-nums" v-text="persen(ringkas.bagian[b.kunci])"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Saringan + pilihan tampilan ===== --}}
    <div class="{{ $kartu }} p-4 sm:p-5 flex flex-col lg:flex-row lg:items-end gap-3">
        <label class="block lg:w-56"><span class="text-xs font-semibold text-slate-500">{{ __('Kelas') }}</span>
            <select v-model="kelas" class="mt-1 {{ $input }}">
                <option value="">{{ __('Semua kelas') }}</option>
                <option v-for="k in kelasList" :key="k.id" :value="k.id" v-text="k.nama"></option>
            </select></label>
        <label class="block flex-1"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
            <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor santri…') }}" class="mt-1 {{ $input }}"></label>
        <div class="grid grid-cols-2 gap-1.5 rounded-2xl bg-slate-100 p-1.5 lg:w-80">
            <button type="button" @click="pilihTampil('kartu')" class="py-2 rounded-xl text-sm font-bold transition"
                :class="tampil === 'kartu' ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'">{{ __('Kartu Siswa') }}</button>
            <button type="button" @click="pilihTampil('tabel')" class="py-2 rounded-xl text-sm font-bold transition"
                :class="tampil === 'tabel' ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'">{{ __('Semua Siswa') }}</button>
        </div>
    </div>

    <p v-if="!tampilList.length" class="{{ $kartu }} py-14 text-center text-sm text-slate-400">{{ __('Tidak ada santri yang cocok.') }}</p>

    {{-- ================= Tampilan 1: Kartu Siswa ================= --}}
    <div v-else-if="tampil === 'kartu'" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
        <button v-for="s in tampilList" :key="s.id" type="button" @click="buka(s)"
            class="group relative overflow-hidden text-left {{ $kartu }} p-5 transition hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-18px_rgba(11,51,44,0.35)] focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <span class="pointer-events-none absolute -top-16 -right-16 w-40 h-40 rounded-full opacity-60 blur-2xl" :class="predikat(s.akhir).cahaya"></span>
            <span v-if="s.rank" class="absolute top-4 right-4 min-w-[2.25rem] h-9 px-2 rounded-xl text-sm font-extrabold flex items-center justify-center ring-1" :class="warnaRank(s.rank)" v-text="'#' + s.rank"></span>

            <div class="relative flex items-center gap-3 pr-12">
                <img v-if="s.foto" :src="s.foto" alt="" class="w-12 h-12 shrink-0 rounded-2xl object-cover bg-slate-100">
                <span v-else class="w-12 h-12 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-[#0b332c] text-white text-lg font-extrabold flex items-center justify-center" v-text="inisial(s.nama)"></span>
                <span class="min-w-0">
                    <span class="block font-bold text-slate-800 truncate" v-text="s.nama"></span>
                    <x-nama-arab vue="s.arab" />
                    <span class="block text-xs text-slate-400 truncate" v-text="[s.kelas, s.nomor].filter(Boolean).join(' · ')"></span>
                </span>
            </div>

            <div class="relative mt-4 flex items-center gap-4">
                {{-- Lingkaran nilai akhir --}}
                <span class="relative w-20 h-20 shrink-0">
                    <svg class="w-20 h-20 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3.2" class="text-slate-100"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"
                            :class="predikat(s.akhir).teks" :stroke-dasharray="(s.akhir || 0) + ' 100'"/>
                    </svg>
                    <span class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-lg font-extrabold leading-none tabular-nums text-slate-800" v-text="s.akhir === null ? '–' : Math.round(s.akhir) + '%'"></span>
                        <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Akhir') }}</span>
                    </span>
                </span>
                {{-- Tiga batang --}}
                <span class="flex-1 min-w-0 space-y-2">
                    <span v-for="b in BAGIAN" :key="b.kunci" class="block">
                        <span class="flex items-center justify-between text-[11px]">
                            <span class="font-semibold text-slate-500" v-text="b.label"></span>
                            <span class="font-bold text-slate-700 tabular-nums" v-text="persen(s.persen[b.kunci])"></span>
                        </span>
                        <span class="mt-0.5 block h-1.5 rounded-full bg-slate-100 overflow-hidden"><span class="block h-full rounded-full" :class="b.batang" :style="{ width: (s.persen[b.kunci] || 0) + '%' }"></span></span>
                    </span>
                </span>
            </div>

            <div class="relative mt-4 flex items-center justify-end">
                <span class="text-xs font-semibold text-emerald-700 opacity-0 group-hover:opacity-100 transition">{{ __('Lihat rincian') }} &rsaquo;</span>
            </div>
        </button>
    </div>

    {{-- ================= Tampilan 2: Semua Siswa (tabel + ranking) ================= --}}
    <section v-else class="{{ $kartu }} p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="py-2.5 pr-3 w-14">{{ __('Rank') }}</th>
                        <th class="py-2.5 pr-3">{{ __('Santri') }}</th>
                        <th class="py-2.5 px-2">{{ __('Kelas') }}</th>
                        <th v-for="b in BAGIAN" :key="b.kunci" class="py-2.5 px-2 text-center" v-text="b.label + ' %'"></th>
                        <th class="py-2.5 px-2 text-center">{{ __('Nilai Akhir') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in tampilList" :key="s.id" @click="buka(s)" class="border-b border-slate-50 hover:bg-[#f8fbfa] cursor-pointer">
                        <td class="py-2.5 pr-3">
                            <span v-if="s.rank" class="inline-flex min-w-[2rem] h-8 px-1.5 rounded-lg text-xs font-extrabold items-center justify-center ring-1" :class="warnaRank(s.rank)" v-text="s.rank"></span>
                            <span v-else class="text-slate-300">–</span>
                        </td>
                        <td class="py-2.5 pr-3 min-w-44">
                            <span class="block font-semibold text-slate-800" v-text="s.nama"></span>
                            <x-nama-arab vue="s.arab" />
                        </td>
                        <td class="py-2.5 px-2 text-slate-500 whitespace-nowrap" v-text="s.kelas || '–'"></td>
                        <td v-for="b in BAGIAN" :key="b.kunci" class="py-2.5 px-2 text-center tabular-nums">
                            <span class="font-semibold" :class="s.persen[b.kunci] === null ? 'text-slate-300' : 'text-slate-700'" v-text="persen(s.persen[b.kunci])"></span>
                            <span v-if="s.jumlah[b.kunci]" class="block text-[10px] text-slate-400" v-text="s.jumlah[b.kunci] + ' ' + teks.nilai"></span>
                        </td>
                        <td class="py-2.5 px-2 text-center">
                            <span class="text-base font-extrabold tabular-nums" :class="s.akhir === null ? 'text-slate-300' : predikat(s.akhir).teks" v-text="persen(s.akhir)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-[11px] text-slate-400">{{ __('Nilai Akhir = rata-rata dari Harian, Lisan, dan Ujian yang sudah ada nilainya. Klik baris untuk melihat rincian.') }}</p>
    </section>
</div>

{{-- ================= Jendela rincian semua nilai satu santri ================= --}}
<div v-if="dipilih" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="dipilih = null">
    <div class="w-full max-w-3xl rounded-3xl bg-white shadow-xl overflow-hidden">
        {{-- Kepala --}}
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="dipilih = null" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <div class="flex items-center gap-4 pr-10">
                <img v-if="dipilih.foto" :src="dipilih.foto" alt="" class="w-16 h-16 shrink-0 rounded-2xl object-cover ring-2 ring-white/20">
                <span v-else class="w-16 h-16 shrink-0 rounded-2xl bg-white/15 text-2xl font-extrabold flex items-center justify-center" v-text="inisial(dipilih.nama)"></span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-extrabold truncate" v-text="dipilih.nama"></h3>
                    <x-nama-arab vue="dipilih.arab" kelas="text-sm text-white/70" />
                    <p class="text-xs text-white/60 truncate" v-text="[dipilih.kelas, dipilih.nomor].filter(Boolean).join(' · ')"></p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-3xl font-extrabold tabular-nums" v-text="persen(dipilih.akhir)"></p>
                    <p class="text-[11px] text-white/60">
                        <template v-if="dipilih.rank">{{ __('Ranking') }} <b class="text-amber-300" v-text="'#' + dipilih.rank"></b></template>
                    </p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2">
                <div v-for="b in BAGIAN" :key="b.kunci" class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-3 py-2">
                    <p class="text-[11px] text-white/60" v-text="b.label"></p>
                    <p class="text-lg font-extrabold tabular-nums" v-text="persen(dipilih.persen[b.kunci])"></p>
                </div>
            </div>
        </div>

        <div class="px-4 sm:px-6 py-5 space-y-6">
            {{-- Nilai Harian --}}
            <section>
                <h4 class="flex items-center gap-2 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>{{ __('Nilai Harian') }}
                    <span class="ml-auto text-xs font-semibold text-slate-400" v-text="dipilih.rincian.harian.length + ' ' + teks.kitab"></span></h4>
                <div v-if="dipilih.rincian.harian.length" class="mt-2 overflow-x-auto rounded-2xl ring-1 ring-slate-100">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="py-2 px-3 text-left font-semibold">{{ __('Kitab') }}</th>
                                <th v-for="(a, i) in aspek" :key="a.kunci" class="py-2 px-1.5 text-center font-semibold" :title="a.label" v-text="'A' + (i + 1)"></th>
                                <th class="py-2 px-3 text-center font-semibold">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(h, i) in dipilih.rincian.harian" :key="'h' + i" class="border-t border-slate-100">
                                <td class="py-2 px-3 min-w-36">
                                    <span class="block font-semibold text-slate-700" v-text="h.kitab"></span>
                                    <span v-if="h.bulan" class="block text-[10px] text-slate-400" v-text="h.bulan"></span>
                                </td>
                                <td v-for="(n, j) in h.nilai" :key="j" class="py-2 px-1.5 text-center tabular-nums text-slate-600" v-text="n"></td>
                                <td class="py-2 px-3 text-center font-extrabold tabular-nums" :class="predikat(h.total).teks" v-text="h.total"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="dipilih.rincian.harian.length" class="mt-1.5 text-[10px] text-slate-400 leading-relaxed">
                    <span v-for="(a, i) in aspek" :key="'k' + a.kunci" class="mr-2 whitespace-nowrap" v-text="'A' + (i + 1) + ' = ' + a.label + ' (' + a.maks + ')'"></span>
                </p>
                <p v-else class="mt-2 text-sm text-slate-400">{{ __('Belum ada nilai harian.') }}</p>
            </section>

            {{-- Ujian Lisan & Ujian --}}
            <section v-for="b in [{ kunci: 'lisan', label: teks.lisan, titik: 'bg-violet-500', kosong: teks.kosongLisan }, { kunci: 'ujian', label: teks.ujian, titik: 'bg-orange-500', kosong: teks.kosongUjian }]" :key="b.kunci">
                <h4 class="flex items-center gap-2 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full" :class="b.titik"></span><span v-text="b.label"></span>
                    <span class="ml-auto text-xs font-semibold text-slate-400" v-text="dipilih.rincian[b.kunci].length + ' ' + teks.nilai"></span></h4>
                <div v-if="dipilih.rincian[b.kunci].length" class="mt-2 overflow-x-auto rounded-2xl ring-1 ring-slate-100">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="py-2 px-3 text-left font-semibold">{{ __('Ujian / kitab') }}</th>
                                <th class="py-2 px-3 text-left font-semibold">{{ __('Tanggal') }}</th>
                                <th class="py-2 px-3 text-center font-semibold">{{ __('Nilai') }}</th>
                                <th class="py-2 px-3 text-center font-semibold">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(n, i) in dipilih.rincian[b.kunci]" :key="b.kunci + i" class="border-t border-slate-100">
                                <td class="py-2 px-3 min-w-40 font-semibold text-slate-700" v-text="n.nama"></td>
                                <td class="py-2 px-3 whitespace-nowrap text-slate-500" v-text="tanggal(n.tanggal)"></td>
                                <td class="py-2 px-3 text-center tabular-nums text-slate-600" v-text="n.nilai + ' / ' + n.maks"></td>
                                <td class="py-2 px-3 text-center font-extrabold tabular-nums" :class="predikat(n.persen).teks" v-text="Math.round(n.persen) + '%'"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="mt-2 text-sm text-slate-400" v-text="b.kosong"></p>
            </section>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex justify-end">
            <button type="button" @click="dipilih = null" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Tutup') }}</button>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        const BAGIAN = [
            { kunci: 'harian', label: @js(__('Harian')), batang: 'bg-sky-500' },
            { kunci: 'lisan', label: @js(__('Lisan')), batang: 'bg-violet-500' },
            { kunci: 'ujian', label: @js(__('Ujian')), batang: 'bg-orange-500' },
        ];
        // Predikat dari nilai akhir (persen)
        const PREDIKAT = [
            { min: 90, nama: @js(__('Mumtaz')), teks: 'text-emerald-600', lencana: 'bg-emerald-50 text-emerald-700 ring-emerald-200', cahaya: 'bg-emerald-200' },
            { min: 80, nama: @js(__('Jayyid Jiddan')), teks: 'text-teal-600', lencana: 'bg-teal-50 text-teal-700 ring-teal-200', cahaya: 'bg-teal-200' },
            { min: 70, nama: @js(__('Jayyid')), teks: 'text-sky-600', lencana: 'bg-sky-50 text-sky-700 ring-sky-200', cahaya: 'bg-sky-200' },
            { min: 60, nama: @js(__('Maqbul')), teks: 'text-amber-600', lencana: 'bg-amber-50 text-amber-700 ring-amber-200', cahaya: 'bg-amber-200' },
            { min: 0, nama: @js(__('Perlu bimbingan')), teks: 'text-rose-600', lencana: 'bg-rose-50 text-rose-700 ring-rose-200', cahaya: 'bg-rose-200' },
        ];
        const BELUM = { nama: @js(__('Belum dinilai')), teks: 'text-slate-400', lencana: 'bg-slate-50 text-slate-500 ring-slate-200', cahaya: 'bg-slate-100' };

        Vue.createApp({
            data: () => ({
                BAGIAN,
                santri: @json($santri),
                aspek: @json($aspek),
                kelasList: @json($kelasList),
                kelas: '',
                cari: '',
                tampil: 'kartu', // kartu | tabel
                dipilih: null,
                teks: {
                    nilai: @js(__('nilai')), kitab: @js(__('kitab')),
                    lisan: @js(__('Ujian Lisan')), ujian: @js(__('Ujian')),
                    kosongLisan: @js(__('Belum ada nilai ujian lisan.')), kosongUjian: @js(__('Belum ada nilai ujian.')),
                },
            }),
            computed: {
                // Santri di kelas terpilih + ranking (nilai sama = ranking sama; belum dinilai = tanpa ranking, paling bawah)
                berperingkat() {
                    const list = this.santri.filter((s) => !this.kelas || s.kelas_id === this.kelas)
                        .slice().sort((a, b) => (b.akhir ?? -1) - (a.akhir ?? -1) || a.nama.localeCompare(b.nama));
                    let rank = 0, sebelum = null;
                    return list.map((s, i) => {
                        if (s.akhir === null) return { ...s, rank: null };
                        if (s.akhir !== sebelum) { rank = i + 1; sebelum = s.akhir; }
                        return { ...s, rank };
                    });
                },
                tampilList() {
                    const k = kecil(this.cari.trim());
                    return k ? this.berperingkat.filter((s) => kecil(s.nama).includes(k) || kecil(s.nomor).includes(k) || (s.arab || '').includes(this.cari.trim())) : this.berperingkat;
                },
                ringkas() {
                    const list = this.berperingkat;
                    const dinilai = list.filter((s) => s.akhir !== null);
                    const rata = (arr) => (arr.length ? arr.reduce((n, v) => n + v, 0) / arr.length : null);
                    return {
                        semua: list.length,
                        dinilai: dinilai.length,
                        rata: rata(dinilai.map((s) => s.akhir)),
                        terbaik: dinilai[0] || null,
                        bagian: Object.fromEntries(BAGIAN.map((b) => [b.kunci, rata(list.map((s) => s.persen[b.kunci]).filter((v) => v !== null))])),
                    };
                },
            },
            mounted() {
                try { const t = localStorage.getItem('laporan-nilai-tampil'); if (t === 'kartu' || t === 'tabel') this.tampil = t; } catch (e) {}
                window.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.dipilih = null; });
            },
            methods: {
                persen: (v) => (v === null || v === undefined ? '–' : (Math.round(v * 10) / 10) + '%'),
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                tanggal: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}` : '–'),
                predikat(v) { return v === null || v === undefined ? BELUM : PREDIKAT.find((p) => v >= p.min); },
                warnaRank(r) {
                    if (r === 1) return 'bg-amber-100 text-amber-700 ring-amber-300';
                    if (r === 2) return 'bg-slate-100 text-slate-600 ring-slate-300';
                    if (r === 3) return 'bg-orange-100 text-orange-700 ring-orange-300';
                    return 'bg-white text-slate-500 ring-slate-200';
                },
                pilihTampil(t) {
                    this.tampil = t;
                    try { localStorage.setItem('laporan-nilai-tampil', t); } catch (e) {}
                },
                buka(s) { this.dipilih = s; },
            },
        }).mount('#halaman-laporan-nilai');
    });
</script>
@endpush
