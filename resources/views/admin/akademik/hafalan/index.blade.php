{{--
    Admin > Akademik > Hafalan (Admin\HafalanController@index). Alamat ?tab=rekap|setoran.
    - Tombol Pengaturan: kitab yang wajib dihafal (dari Kurikulum & Kitab) + pilihan nilai yang ditulis admin
      (PUT admin.hafalan.pengaturan, disimpan per sesi: pengaturan hafalan_kitab / hafalan_nilai).
    - Tombol Nilai Hafalan: admin ikut menilai (admin.hafalan.nilai = halaman isi yang sama dengan asatid).
    - Tab Rekap Santri: tiap santri x tiap kitab (jumlah setoran, halaman terakhir, nilai terakhir, tanda ✓ Hafal).
    - Tab Semua Setoran: semua laporan dari para asatid (Buka = langsung ke santri & kitab itu, Hapus).
    Vue: #halaman-hafalan.
--}}
@extends('layouts.admin')

@section('title', __('Hafalan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$tutup = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

<div id="halaman-hafalan" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8 pb-6">
    <x-header-admin :label="__('Akademik')" :judul="__('Hafalan')"
        :keterangan="__('Laporan setoran hafalan santri dari para asatid, per kitab.')" />

    <p v-if="!kitabList.length" class="rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 text-sm text-amber-900">{{ __('Belum ada kitab hafalan. Tekan tombol Pengaturan lalu centang kitab yang wajib dihafal.') }}</p>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Santri') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800 tabular-nums" v-text="santri.length"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Setoran') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-teal-600 tabular-nums" v-text="setoran.length"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Kitab sudah hafal') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 tabular-nums" v-text="totalHafal"></p>
        </div>
    </div>

    {{-- ===== Tab ===== --}}
    <div class="grid grid-cols-2 gap-1.5 rounded-3xl bg-white ring-1 ring-slate-200/70 p-1.5">
        <button v-for="t in TAB" :key="t.kunci" type="button" @click="pilihTab(t.kunci)"
            class="py-3 px-4 rounded-2xl text-sm font-bold transition"
            :class="tab === t.kunci ? 'bg-[#0b332c] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'" v-text="t.label"></button>
    </div>

    {{-- ===== Saringan + tombol kecil: Pengaturan & Nilai Hafalan ===== --}}
    <div class="{{ $kartu }} p-4 sm:p-5 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-[1fr_1fr_1fr_auto] gap-3">
        <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
            <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor santri…') }}" class="mt-1 {{ $input }}"></label>
        <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Halaqoh') }}</span>
            <select v-model="kelas" class="mt-1 {{ $input }}">
                <option value="" v-text="teks.semuaHalaqoh"></option>
                <option v-for="k in kelasList" :key="k.id" :value="k.id" v-text="k.nama"></option>
            </select></label>
        <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kitab') }}</span>
            <select v-model="kitab" class="mt-1 {{ $input }}">
                <option value="" v-text="teks.semuaKitab"></option>
                <option v-for="k in kitabList" :key="k.id" :value="k.id" v-text="k.nama"></option>
            </select></label>
        <div class="flex gap-1.5 md:col-span-3 lg:col-span-1 lg:items-end">
            <button type="button" @click="bukaPengaturan" :title="kitabList.length + ' ' + teks.kitab + ' · ' + nilaiList.length + ' ' + teks.pilihanNilai"
                class="inline-flex items-center gap-1.5 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold text-slate-600 hover:ring-emerald-400 hover:text-[#0b332c] whitespace-nowrap transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                {{ __('Pengaturan') }}
            </button>
            <a href="{{ route('admin.hafalan.nilai') }}" title="{{ __('Admin ikut mengisi setoran (sama seperti asatid)') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0b332c] px-3 py-2.5 text-xs font-bold text-white hover:bg-[#135347] whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                {{ __('Nilai Hafalan') }}
            </a>
        </div>
    </div>

    {{-- ===== Tab Rekap Santri ===== --}}
    <template v-if="tab === 'rekap'">
        <p v-if="!rekapTampil.length" class="{{ $kartu }} py-14 text-center text-sm text-slate-400">{{ __('Tidak ada santri yang cocok.') }}</p>
        <div v-else class="{{ $kartu }} overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold sticky left-0 bg-white">{{ __('Santri') }}</th>
                        <th v-for="k in kolomKitab" :key="k.id" class="px-3 py-3 font-semibold min-w-[11rem]" v-text="k.nama"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="s in rekapTampil" :key="s.id">
                        <td class="px-5 py-3 sticky left-0 bg-white">
                            <span class="block font-bold text-slate-800 whitespace-nowrap" v-text="s.nama"></span>
                            <x-nama-arab vue="s.arab" />

                            <span class="block text-[11px] text-slate-400 whitespace-nowrap" v-text="[s.kelas, s.nomor].filter(Boolean).join(' · ')"></span>
                        </td>
                        <td v-for="k in kolomKitab" :key="k.id" class="px-3 py-3 align-top">
                            <a :href="linkBuka(s.kelas_id, s.id, k.id)" class="block rounded-xl px-3 py-2 ring-1 transition hover:ring-teal-400"
                                :class="r(s, k.id).selesai ? 'bg-emerald-50 ring-emerald-200' : (r(s, k.id).jumlah ? 'bg-[#f8fbfa] ring-slate-200' : 'bg-white ring-slate-100')">
                                <span v-if="r(s, k.id).selesai" class="inline-block rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white" v-text="teks.hafal"></span>
                                <template v-if="r(s, k.id).jumlah">
                                    <span class="block text-xs font-bold text-slate-700" v-text="teks.sampaiHal + ' ' + r(s, k.id).halaman"></span>
                                    <span class="block text-[11px] text-slate-400" v-text="r(s, k.id).jumlah + ' ' + teks.setoranKecil + ' · ' + r(s, k.id).nilai"></span>
                                </template>
                                <span v-else-if="!r(s, k.id).selesai" class="block text-[11px] text-slate-300" v-text="teks.belum"></span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>

    {{-- ===== Tab Semua Setoran ===== --}}
    <template v-else>
        <p v-if="!setoranTampil.length" class="{{ $kartu }} py-14 text-center text-sm text-slate-400">{{ __('Belum ada setoran yang cocok.') }}</p>
        <div v-else class="{{ $kartu }} overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">{{ __('Tanggal') }}</th>
                        <th class="px-3 py-3 font-semibold">{{ __('Santri') }}</th>
                        <th class="px-3 py-3 font-semibold">{{ __('Kitab') }}</th>
                        <th class="px-3 py-3 font-semibold">{{ __('Halaman') }}</th>
                        <th class="px-3 py-3 font-semibold text-center">{{ __('Ragu') }}</th>
                        <th class="px-3 py-3 font-semibold text-center">{{ __('Salah') }}</th>
                        <th class="px-3 py-3 font-semibold">{{ __('Nilai') }}</th>
                        <th class="px-3 py-3 font-semibold">{{ __('Penilai') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="h in setoranHalaman" :key="h.id">
                        <td class="px-5 py-3 whitespace-nowrap text-slate-600" v-text="tanggal(h.tanggal)"></td>
                        <td class="px-3 py-3">
                            <span class="block font-bold text-slate-800 whitespace-nowrap" v-text="h.siswa"></span>
                            <x-nama-arab vue="h.arab" />
                            <span class="block text-[11px] text-slate-400 whitespace-nowrap" v-text="h.kelas || '–'"></span>
                        </td>
                        <td class="px-3 py-3 text-slate-700 whitespace-nowrap">
                            <span v-text="h.kitab"></span>
                            <span v-if="selesai(h.siswa_id, h.kitab_id)" class="ml-1 rounded-full bg-emerald-500 px-1.5 py-0.5 text-[10px] font-bold text-white" v-text="teks.hafal"></span>
                        </td>
                        <td class="px-3 py-3 font-semibold text-slate-800 whitespace-nowrap tabular-nums" v-text="h.dari + ' – ' + h.sampai"></td>
                        <td class="px-3 py-3 text-center tabular-nums" :class="h.ragu ? 'text-amber-600 font-bold' : 'text-slate-400'" v-text="h.ragu"></td>
                        <td class="px-3 py-3 text-center tabular-nums" :class="h.salah ? 'text-rose-600 font-bold' : 'text-slate-400'" v-text="h.salah"></td>
                        <td class="px-3 py-3"><span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-700 ring-1 ring-teal-100 whitespace-nowrap" v-text="h.nilai"></span></td>
                        <td class="px-3 py-3 text-xs text-slate-500 whitespace-nowrap" v-text="h.oleh || '–'"></td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a :href="linkBuka(h.kelas_id, h.siswa_id, h.kitab_id)" class="text-xs font-semibold text-teal-700 hover:underline">{{ __('Buka') }}</a>
                            <button type="button" @click="hapus(h)" class="ml-2 text-xs font-semibold text-rose-600 hover:underline">{{ __('Hapus') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="setoranTampil.length > batas" class="border-t border-slate-100 px-5 py-3 text-center">
                <button type="button" @click="batas += 100" class="text-sm font-bold text-teal-700 hover:underline"
                    v-text="teks.lebihBanyak + ' (' + (setoranTampil.length - batas) + ')'"></button>
            </div>
        </div>
    </template>
</div>

{{-- ================= Pengaturan Hafalan ================= --}}
<div v-if="atur" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="atur = null">
    <div class="w-full max-w-3xl rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="atur = null" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Pengaturan') }}</p>
            <h3 class="mt-1 text-xl font-extrabold">{{ __('Pengaturan Hafalan') }}</h3>
            <p class="mt-1 text-sm text-white/70">{{ __('Pilih kitab yang wajib dihafal dan tulis pilihan nilainya.') }}</p>
        </div>

        <div class="p-5 sm:p-6 space-y-6">
            {{-- 1. Kitab --}}
            <section>
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="font-bold text-slate-800">1. {{ __('Kitab yang wajib dihafal') }}</h4>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700" v-text="atur.kitab.length + ' ' + teks.dipilih"></span>
                    <input v-if="semuaKitab.length > 8" type="search" v-model="atur.cari" placeholder="{{ __('Cari kitab…') }}" class="ml-auto w-full sm:w-52 {{ $input }} py-2">
                </div>
                <p v-if="!semuaKitab.length" class="mt-3 text-sm text-slate-400">{{ __('Belum ada kitab. Tambahkan dulu di Akademik › Kurikulum & Kitab.') }}</p>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
                    <label v-for="k in kitabAtur" :key="k.id" class="flex items-start gap-2.5 rounded-xl px-3 py-2.5 ring-1 cursor-pointer transition"
                        :class="atur.kitab.includes(k.id) ? 'bg-emerald-50 ring-emerald-300' : 'bg-[#f8fbfa] ring-slate-200 hover:bg-slate-50'">
                        <input type="checkbox" :value="k.id" v-model="atur.kitab" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-800" v-text="k.nama"></span>
                            <span class="block text-[11px] text-slate-400" v-text="[k.bidang, k.aktif ? '' : teks.nonaktif].filter(Boolean).join(' · ')"></span>
                        </span>
                    </label>
                </div>
            </section>

            {{-- 2. Pilihan nilai --}}
            <section>
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="font-bold text-slate-800">2. {{ __('Jenis penilaian (pilihan nilai)') }}</h4>
                    <button type="button" @click="pakaiContoh" class="ml-auto text-xs font-semibold text-slate-500 hover:text-emerald-700">{{ __('Pakai contoh') }}</button>
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ __('Asatid memilih salah satu nilai ini saat mengisi setoran. Urutan di sini = urutan pilihan.') }}</p>
                <div class="mt-3 space-y-2">
                    <div v-for="(n, i) in atur.nilai" :key="i" class="flex items-center gap-2">
                        <span class="w-6 text-center text-xs font-bold text-slate-400" v-text="i + 1"></span>
                        <input type="text" v-model="atur.nilai[i]" maxlength="100" placeholder="{{ __('mis. Mumtaz') }}" class="{{ $input }} py-2">
                        <button type="button" @click="geser(i, -1)" :disabled="i === 0" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-slate-200 text-slate-500 disabled:opacity-30" title="{{ __('Naik') }}">&uarr;</button>
                        <button type="button" @click="geser(i, 1)" :disabled="i === atur.nilai.length - 1" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-slate-200 text-slate-500 disabled:opacity-30" title="{{ __('Turun') }}">&darr;</button>
                        <button type="button" @click="atur.nilai.splice(i, 1)" class="w-8 h-9 shrink-0 rounded-lg ring-1 ring-rose-200 text-rose-500 hover:bg-rose-50 flex items-center justify-center" title="{{ __('Hapus') }}">{!! $tutup !!}</button>
                    </div>
                </div>
                <button type="button" @click="atur.nilai.push('')" class="mt-2 text-xs font-bold text-emerald-700 hover:underline">+ {{ __('Tambah pilihan nilai') }}</button>
            </section>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap justify-end gap-2">
            <button type="button" @click="atur = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button type="button" @click="simpanPengaturan" :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
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
            pengaturan: @js(route('admin.hafalan.pengaturan')),
            hapus: @js(route('admin.hafalan.hapus', '__ID__')),
            nilai: @js(route('admin.hafalan.nilai')),
        };
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
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        const gagal = (pesan) => (window.notifikasi ? window.notifikasi.gagal(pesan) : alert(pesan));

        Vue.createApp({
            data: () => ({
                TAB: [
                    { kunci: 'rekap', label: @js(__('Rekap Santri')) },
                    { kunci: 'setoran', label: @js(__('Semua Setoran')) },
                ],
                tab: @js($tab),
                kitabList: @json($kitabList),
                kitabPilih: @json($kitabPilih),
                nilaiList: @json($nilaiList),
                nilaiBawaan: @json($nilaiBawaan),
                semuaKitab: @json($semuaKitab),
                kelasList: @json($kelasList),
                santri: @json($santri),
                setoran: @json($setoran),
                cari: '',
                kelas: '',
                kitab: '',
                batas: 100,
                atur: null,       // { kitab: [id], nilai: [teks], cari }
                menyimpan: false,
                teks: {
                    kitab: @js(__('kitab')), pilihanNilai: @js(__('pilihan nilai')), dipilih: @js(__('dipilih')),
                    semuaHalaqoh: @js(__('Semua halaqoh')), semuaKitab: @js(__('Semua kitab')),
                    hafal: @js(__('✓ Hafal')), belum: @js(__('Belum setor')), sampaiHal: @js(__('Sampai hal.')), setoranKecil: @js(__('setoran')),
                    lebihBanyak: @js(__('Tampilkan lebih banyak')), nonaktif: @js(__('nonaktif')),
                    simpan: @js(__('Simpan pengaturan')), menyimpan: @js(__('Menyimpan…')),
                    yakinHapus: @js(__('Hapus setoran :nama (:kitab, hal. :hal)?')),
                    yakinContoh: @js(__('Ganti pilihan nilai dengan contoh bawaan?')),
                    nilaiKosong: @js(__('Tulis minimal satu pilihan nilai.')),
                },
            }),
            computed: {
                kolomKitab() { return this.kitab ? this.kitabList.filter((k) => k.id === this.kitab) : this.kitabList; },
                totalHafal() {
                    return this.santri.reduce((n, s) => n + this.kitabList.filter((k) => this.r(s, k.id).selesai).length, 0);
                },
                rekapTampil() {
                    const k = kecil(this.cari.trim());
                    return this.santri
                        .filter((s) => !this.kelas || s.kelas_id === this.kelas)
                        .filter((s) => !k || kecil(s.nama).includes(k) || kecil(s.nomor).includes(k) || (s.arab || '').includes(this.cari.trim()));
                },
                setoranTampil() {
                    const k = kecil(this.cari.trim());
                    return this.setoran
                        .filter((h) => !this.kelas || h.kelas_id === this.kelas)
                        .filter((h) => !this.kitab || h.kitab_id === this.kitab)
                        .filter((h) => !k || kecil(h.siswa).includes(k) || kecil(h.nomor).includes(k) || (h.arab || '').includes(this.cari.trim()));
                },
                setoranHalaman() { return this.setoranTampil.slice(0, this.batas); },
                kitabAtur() {
                    const k = kecil((this.atur && this.atur.cari) || '');
                    return this.semuaKitab.filter((x) => !k || kecil(x.nama).includes(k) || kecil(x.bidang).includes(k));
                },
            },
            mounted() {
                window.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.atur = null; });
            },
            methods: {
                r(s, kitabId) { return (s.kitab && s.kitab[kitabId]) || {}; },
                selesai(siswaId, kitabId) {
                    const s = this.santri.find((x) => x.id === siswaId);
                    return s ? !!this.r(s, kitabId).selesai : false;
                },
                tanggal: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}` : '–'),
                linkBuka(kelasId, siswaId, kitabId) {
                    return RUTE.nilai + '?kelas=' + (kelasId || '') + '&siswa=' + siswaId + '&kitab=' + kitabId;
                },
                pilihTab(t) {
                    this.tab = t;
                    const url = new URL(location.href);
                    url.searchParams.set('tab', t);
                    history.replaceState(null, '', url);
                },
                async hapus(h) {
                    if (!confirm(this.teks.yakinHapus.replace(':nama', h.siswa).replace(':kitab', h.kitab).replace(':hal', h.dari + '–' + h.sampai))) return;
                    try {
                        const json = await kirimJson(RUTE.hapus.replace('__ID__', h.id), 'DELETE');
                        this.setoran = this.setoran.filter((x) => x.id !== h.id);
                        // Ringkasan santri itu untuk kitab itu, dari riwayat terbaru
                        const s = this.santri.find((x) => x.id === h.siswa_id);
                        if (s) {
                            const b = json.baris;
                            s.kitab = { ...(s.kitab || {}) };
                            s.kitab[h.kitab_id] = b.length || json.selesai
                                ? { jumlah: b.length, halaman: b.length ? Math.max(...b.map((x) => x.sampai)) : null, nilai: b.length ? b[b.length - 1].nilai : null, selesai: !!json.selesai }
                                : {};
                        }
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    }
                },
                // ===== Pengaturan =====
                bukaPengaturan() {
                    this.atur = { kitab: [...this.kitabPilih], nilai: [...this.nilaiList], cari: '' };
                },
                pakaiContoh() {
                    if (confirm(this.teks.yakinContoh)) this.atur.nilai = [...this.nilaiBawaan];
                },
                geser(i, arah) {
                    const list = this.atur.nilai;
                    const j = i + arah;
                    if (j < 0 || j >= list.length) return;
                    [list[i], list[j]] = [list[j], list[i]];
                },
                async simpanPengaturan() {
                    const nilai = this.atur.nilai.map((n) => (n || '').trim()).filter(Boolean);
                    if (!nilai.length) return gagal(this.teks.nilaiKosong);
                    // Kitab tetap urut sesuai pilihan lama, yang baru dicentang di belakang
                    const kitab = [...this.kitabPilih.filter((id) => this.atur.kitab.includes(id)), ...this.atur.kitab.filter((id) => !this.kitabPilih.includes(id))];
                    this.menyimpan = true;
                    try {
                        const json = await kirimJson(RUTE.pengaturan, 'PUT', { kitab, nilai });
                        this.kitabList = json.kitabList;
                        this.kitabPilih = json.kitabPilih;
                        this.nilaiList = json.nilaiList;
                        if (this.kitab && !this.kitabPilih.includes(this.kitab)) this.kitab = '';
                        window.notifikasi?.sukses(json.pesan);
                        this.atur = null;
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
            },
        }).mount('#halaman-hafalan');
    });
</script>
@endpush
