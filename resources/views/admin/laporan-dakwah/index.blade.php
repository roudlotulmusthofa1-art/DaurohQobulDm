{{--
    Admin > Laporan > Laporan Dakwah (PenilaianDakwahController@laporan).
    Ringkasan gabungan Khitoba' + Khurujud Dakwah dari Admin > Penilaian > Penilaian Dakwah:
    saringan periode (GET) + jenis (Vue), angka ringkas, sebaran nilai, rekap per santri, dan semua penilaian.
    Vue: #halaman-laporan-dakwah.
--}}
@extends('layouts.admin')

@section('title', __('Laporan Dakwah'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$keterangan = __("Ringkasan hasil penilaian khitoba' dan khurujud dakwah semua santri.");
@endphp

<div id="halaman-laporan-dakwah" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Laporan')" :judul="__('Laporan Dakwah')" :keterangan="$keterangan">
        <x-slot:aksi>
            <a href="{{ route('admin.penilaian.dakwah.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-emerald-400 text-[#0b332c] hover:bg-emerald-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Beri Nilai') }}
            </a>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Saringan ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Dari') }}</span>
                <input type="date" name="dari" value="{{ $dari }}" class="mt-1 {{ $input }}"></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Sampai') }}</span>
                <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 {{ $input }}"></label>
            <div class="col-span-2 flex gap-2">
                <button class="flex-1 lg:flex-none px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Terapkan') }}</button>
                @if ($dari || $sampai)
                <a href="{{ route('admin.laporan-dakwah.index') }}" class="px-3 py-2.5 rounded-xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Reset') }}</a>
                @endif
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <button v-for="j in JENIS" :key="j.kunci" type="button" @click="jenis = j.kunci"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition"
                :class="jenis === j.kunci ? 'bg-[#0b332c] text-white shadow-md' : 'bg-[#f8fbfa] ring-1 ring-slate-200 text-slate-600 hover:bg-slate-50'"
                v-text="j.label"></button>
        </div>
    </form>

    {{-- ===== Angka ringkas ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Jumlah penilaian') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="nilaiJenis.length"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Santri dinilai') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="rekap.length"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __("Khitoba'") }}</p>
            <p class="mt-1 text-3xl font-extrabold text-teal-600 tabular-nums" v-text="jumlah.khitobah"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Khurujud Dakwah') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-violet-600 tabular-nums" v-text="jumlah.khuruj"></p>
        </div>
    </div>

    {{-- ===== Sebaran nilai ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <section v-for="s in sebaran" :key="s.kunci" class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Sebaran nilai') }} — <span v-text="s.label"></span></h3>
            <ul class="mt-3 space-y-2.5">
                <li v-for="b in s.baris" :key="b.nama">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-700" v-text="b.nama"></span>
                        <span class="tabular-nums text-slate-500" v-text="b.jumlah"></span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full" :class="s.warna" :style="{ width: (s.total ? b.jumlah / s.total * 100 : 0) + '%' }"></div>
                    </div>
                </li>
            </ul>
            <p v-if="!s.baris.length" class="mt-3 text-sm text-slate-400">{{ __('Belum ada nilai.') }}</p>
        </section>
    </div>

    {{-- ===== Rekap per santri / Semua penilaian ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="inline-flex gap-1 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-1">
                <button type="button" @click="tampil = 'rekap'" class="px-4 py-2 rounded-xl text-sm font-semibold"
                    :class="tampil === 'rekap' ? 'bg-white shadow text-slate-800' : 'text-slate-500'">{{ __('Rekap per santri') }}</button>
                <button type="button" @click="tampil = 'daftar'" class="px-4 py-2 rounded-xl text-sm font-semibold"
                    :class="tampil === 'daftar' ? 'bg-white shadow text-slate-800' : 'text-slate-500'">{{ __('Semua penilaian') }}</button>
            </div>
            <div class="flex-1"></div>
            <input type="search" v-model="cari" placeholder="{{ __('Cari santri...') }}" class="sm:max-w-xs {{ $input }}">
        </div>

        {{-- Rekap per santri --}}
        <ul v-if="tampil === 'rekap'" class="mt-3 divide-y divide-slate-100">
            <li v-for="r in rekapCari" :key="r.siswa_id" class="py-3.5 flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex items-center gap-3 sm:w-64 shrink-0 min-w-0">
                    <img v-if="r.foto" :src="r.foto" alt="" class="w-10 h-10 rounded-xl object-cover">
                    <span v-else class="w-10 h-10 rounded-xl bg-slate-200 text-slate-600 font-bold flex items-center justify-center" v-text="inisial(r.siswa)"></span>
                    <span class="min-w-0">
                        <button type="button" @click="lihatSantri(r)" class="block font-semibold text-slate-800 truncate hover:underline text-left" v-text="r.siswa"></button>
                        <span class="block text-xs text-slate-400 truncate"><span v-text="r.nomor"></span><template v-if="r.kelas"> · <span v-text="r.kelas"></span></template></span>
                    </span>
                </div>
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div v-for="j in JENIS_SAJA" v-show="!jenis || jenis === j.kunci" :key="j.kunci" class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2">
                        <p class="text-[11px] font-semibold text-slate-500">
                            <span v-text="j.label"></span>: <b class="text-slate-800" v-text="r[j.kunci].jumlah"></b>×
                        </p>
                        <div class="mt-1 flex flex-wrap gap-1">
                            <span v-for="(jml, nama) in r[j.kunci].nilai" :key="nama" class="rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1" :class="j.chip">
                                <span v-text="nama"></span> <span v-text="jml"></span>
                            </span>
                            <span v-if="!r[j.kunci].jumlah" class="text-[11px] text-slate-400">–</span>
                        </div>
                        {{-- Catatan dari Penilaian Dakwah (3 terbaru) --}}
                        <ul v-if="r[j.kunci].catatan.length" class="mt-2 space-y-1.5 border-t border-slate-200/70 pt-2">
                            <li v-for="c in r[j.kunci].catatan.slice(0, 3)" :key="c.id" class="text-xs text-slate-600">
                                <span class="font-semibold text-slate-500"><span v-text="tanggalPanjang(c.tanggal)"></span> </span>
                                <span v-if="c.waktu" class="text-slate-400"> (<span v-text="c.waktu"></span>)</span>
                                — <span class="whitespace-pre-line" v-text="c.catatan"></span>
                            </li>
                        </ul>
                        <button v-if="r[j.kunci].catatan.length > 3" type="button" @click="lihatSantri(r)" class="mt-1 text-[11px] font-semibold text-emerald-700 hover:underline">
                            {{ __('Lihat semua') }} (<span v-text="r[j.kunci].catatan.length"></span>)
                        </button>
                    </div>
                </div>
            </li>
            <li v-if="!rekapCari.length" class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada penilaian dakwah.') }}</li>
        </ul>

        {{-- Semua penilaian --}}
        <template v-else>
            <div v-if="santriDipilih" class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#0b332c] text-white pl-3 pr-1 py-1 text-xs font-semibold">
                {{ __('Santri:') }} <span v-text="santriDipilih.siswa"></span>
                <button type="button" @click="santriDipilih = null" class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center" aria-label="{{ __('Hapus saringan santri') }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <ul class="mt-3 divide-y divide-slate-100">
                <li v-for="n in daftarTampil" :key="n.id" class="py-3.5 flex items-start gap-3">
                    <span class="w-12 shrink-0 rounded-2xl py-1.5 text-center ring-1" :class="n.jenis === 'khitobah' ? 'bg-teal-50 ring-teal-100' : 'bg-violet-50 ring-violet-100'">
                        <span class="block text-lg font-extrabold leading-none text-slate-800" v-text="n.tanggal.slice(8, 10)"></span>
                        <span class="block mt-0.5 text-[10px] font-semibold uppercase text-slate-400" v-text="bulan(n.tanggal)"></span>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="font-semibold text-slate-800" v-text="n.siswa"></span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1"
                                :class="n.jenis === 'khitobah' ? 'bg-teal-50 text-teal-700 ring-teal-200' : 'bg-violet-50 text-violet-700 ring-violet-200'"
                                v-text="labelJenis(n.jenis)"></span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1 bg-emerald-50 text-emerald-700 ring-emerald-200" v-text="n.nilai"></span>
                            <span v-if="n.waktu" class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600" v-text="n.waktu"></span>
                        </span>
                        <span class="block mt-0.5 text-xs text-slate-400">
                            <span v-text="n.hari"></span>, <span v-text="tanggalPanjang(n.tanggal)"></span>
                            <template v-if="n.kelas"> · <span v-text="n.kelas"></span></template>
                        </span>
                        <span v-if="n.catatan" class="block mt-1 text-sm text-slate-700 whitespace-pre-line" v-text="n.catatan"></span>
                        <span v-if="n.oleh" class="block mt-1 text-[11px] text-slate-400">{{ __('Dinilai oleh') }} <b class="text-slate-500" v-text="n.oleh"></b></span>
                    </span>
                </li>
            </ul>
            <p v-if="!daftarCari.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Belum ada penilaian dakwah.') }}</p>
            <div v-if="daftarCari.length > batas" class="mt-4 text-center">
                <button type="button" @click="batas += 50" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    {{ __('Tampilkan lebih banyak') }}
                </button>
            </div>
        </template>
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        const JENIS_SAJA = [
            { kunci: 'khitobah', label: @js(__("Khitoba'")), kelompok: 'nilai_khitobah', warna: 'bg-teal-500', chip: 'bg-teal-50 text-teal-700 ring-teal-200' },
            { kunci: 'khuruj', label: @js(__('Khurujud Dakwah')), kelompok: 'nilai_khuruj', warna: 'bg-violet-500', chip: 'bg-violet-50 text-violet-700 ring-violet-200' },
        ];
        const JENIS = [{ kunci: '', label: @js(__('Semua')) }, ...JENIS_SAJA];

        Vue.createApp({
            data: () => ({
                JENIS, JENIS_SAJA,
                nilai: @json($nilai),
                pilihan: @json($pilihan),
                jenis: '',
                tampil: 'rekap',
                cari: '',
                santriDipilih: null,
                batas: 50,
            }),
            computed: {
                nilaiJenis() { return this.jenis ? this.nilai.filter((n) => n.jenis === this.jenis) : this.nilai; },
                jumlah() {
                    return {
                        khitobah: this.nilai.filter((n) => n.jenis === 'khitobah').length,
                        khuruj: this.nilai.filter((n) => n.jenis === 'khuruj').length,
                    };
                },
                // Jumlah tiap nilai; urutan mengikuti pengaturan, nilai lama yang pilihannya sudah dihapus di akhir
                sebaran() {
                    return JENIS_SAJA.filter((j) => !this.jenis || this.jenis === j.kunci).map((j) => {
                        const hitung = {};
                        this.nilai.filter((n) => n.jenis === j.kunci).forEach((n) => { hitung[n.nilai] = (hitung[n.nilai] || 0) + 1; });
                        const urut = this.pilihan[j.kelompok].map((p) => p.nama);
                        const nama = [...urut.filter((x) => hitung[x]), ...Object.keys(hitung).filter((x) => !urut.includes(x))];
                        return { ...j, total: Object.values(hitung).reduce((a, b) => a + b, 0), baris: nama.map((x) => ({ nama: x, jumlah: hitung[x] })) };
                    });
                },
                rekap() {
                    const per = {};
                    this.nilaiJenis.forEach((n) => {
                        const r = per[n.siswa_id] ??= {
                            siswa_id: n.siswa_id, siswa: n.siswa, nomor: n.nomor, kelas: n.kelas, foto: n.foto, total: 0,
                            khitobah: { jumlah: 0, nilai: {}, catatan: [] }, khuruj: { jumlah: 0, nilai: {}, catatan: [] },
                        };
                        r.total++;
                        r[n.jenis].jumlah++;
                        r[n.jenis].nilai[n.nilai] = (r[n.jenis].nilai[n.nilai] || 0) + 1;
                        if (n.catatan) r[n.jenis].catatan.push(n); // data sudah urut terbaru dulu
                    });
                    return Object.values(per).sort((a, b) => b.total - a.total || a.siswa.localeCompare(b.siswa));
                },
                rekapCari() {
                    const k = kecil(this.cari.trim());
                    return k ? this.rekap.filter((r) => kecil(r.siswa).includes(k) || kecil(r.nomor).includes(k)) : this.rekap;
                },
                daftarCari() {
                    const k = kecil(this.cari.trim());
                    return this.nilaiJenis.filter((n) =>
                        (!this.santriDipilih || n.siswa_id === this.santriDipilih.siswa_id) &&
                        (!k || [n.siswa, n.nomor, n.nilai, n.waktu, n.catatan].some((t) => kecil(t).includes(k))));
                },
                daftarTampil() { return this.daftarCari.slice(0, this.batas); },
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                bulan: (t) => BULAN[parseInt(t.slice(5, 7), 10) - 1],
                tanggalPanjang: (t) => parseInt(t.slice(8, 10), 10) + ' ' + BULAN[parseInt(t.slice(5, 7), 10) - 1] + ' ' + t.slice(0, 4),
                labelJenis: (k) => (JENIS_SAJA.find((j) => j.kunci === k) || {}).label || k,
                // Klik nama santri di rekap -> lihat semua penilaiannya
                lihatSantri(r) {
                    this.santriDipilih = r;
                    this.cari = '';
                    this.tampil = 'daftar';
                },
            },
        }).mount('#halaman-laporan-dakwah');
    });
</script>
@endpush
