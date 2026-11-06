{{--
    Admin > Penilaian > Pelanggaran  /  Pencapaian (PenilaianController@catatan). Satu halaman untuk dua menu,
    pengaturannya ($atur) dari App\Support\JenisPenilaian.
    - Ringkasan: total laporan, hari ini, jumlah siswa, siswa terbanyak
    - Rekap per siswa: jumlah per jenis (+ Terlambat & Alfa dari absensi untuk pelanggaran)
    - Semua laporan: laporan asatid + Terlambat/Alfa absensi, bisa disaring; laporan asatid bisa dihapus
    Semua data dikirim sekali ($baris), saringan & rekap dihitung Vue: #halaman-penilaian-catatan.
--}}
@extends('layouts.admin')

@section('title', $atur['label'])

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$rute = 'admin.penilaian.' . $atur['tipe'] . '.index';
$hariIni = now('Asia/Jakarta');
$cepat = [
    __('Semua') => [null, null],
    __('Hari ini') => [$hariIni->toDateString(), $hariIni->toDateString()],
    __('7 hari') => [$hariIni->copy()->subDays(6)->toDateString(), $hariIni->toDateString()],
    __('Bulan ini') => [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->toDateString()],
];
$pelanggaran = $atur['tipe'] === 'pelanggaran';
// Warna utama halaman: merah (pelanggaran) / hijau (pencapaian)
$utama = $pelanggaran
    ? ['grad' => 'from-rose-500 to-red-600', 'lembut' => 'bg-rose-50 text-rose-700 ring-rose-100', 'teks' => 'text-rose-600']
    : ['grad' => 'from-emerald-500 to-teal-600', 'lembut' => 'bg-emerald-50 text-emerald-700 ring-emerald-100', 'teks' => 'text-emerald-600'];
@endphp

<div id="halaman-penilaian-catatan" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="$atur['label']" :keterangan="$atur['keterangan']">
        <x-slot:aksi>
            <a href="{{ route('admin.master-data.index', ['tab' => $atur['tab_master']]) }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 ring-1 ring-white/20 hover:bg-white/20 px-4 py-2.5 text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                {{ $atur['label_master'] }}
            </a>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Periode ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Dari') }}</span>
                <input type="date" name="dari" value="{{ $dari }}" class="mt-1 {{ $input }}"></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Sampai') }}</span>
                <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 {{ $input }}"></label>
            <div class="col-span-2 flex gap-2">
                <button class="flex-1 lg:flex-none px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Terapkan') }}</button>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($cepat as $l => [$d, $s])
            @php $aktif = $dari === $d && $sampai === $s; @endphp
            <a href="{{ route($rute, array_filter(['dari' => $d, 'sampai' => $s])) }}"
                class="px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition-colors {{ $aktif ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">{{ $l }}</a>
            @endforeach
        </div>
    </form>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Total laporan') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="baris.length"></p>
            @if ($pelanggaran)
            <p class="mt-1 text-[11px] text-slate-400">
                <span v-text="jumlahSumber.guru"></span> {{ __('dari asatid') }} ·
                <span v-text="jumlahSumber.absensi"></span> {{ __('dari absensi') }}
            </p>
            @endif
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Hari ini') }}</p>
            <p class="mt-1 text-3xl font-extrabold tabular-nums {{ $utama['teks'] }}" v-text="jumlahHariIni"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $hariIni->locale('id')->translatedFormat('l, d M Y') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Siswa') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="rekap.length"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $pelanggaran ? __('siswa pernah melanggar') : __('siswa punya pencapaian') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5 min-w-0">
            <p class="text-xs font-semibold text-slate-500">{{ __('Terbanyak') }}</p>
            <template v-if="rekap.length">
                <p class="mt-1 font-extrabold text-slate-800 truncate" v-text="rekap[0].siswa"></p>
                <p class="text-[11px] font-semibold {{ $utama['teks'] }}"><span v-text="rekap[0].total"></span> {{ __('laporan') }}</p>
            </template>
            <p v-else class="mt-1 text-3xl font-extrabold text-slate-300">–</p>
        </div>
    </div>

    {{-- ===== Tab ===== --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        <button type="button" @click="tab = 'rekap'"
            class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
            :class="tab === 'rekap' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            {{ __('Rekap per siswa') }}
        </button>
        <button type="button" @click="tab = 'laporan'"
            class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
            :class="tab === 'laporan' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            {{ __('Semua laporan') }}
            <span class="rounded-full px-2 text-[11px]" :class="tab === 'laporan' ? 'bg-white/20' : 'bg-slate-100'" v-text="baris.length"></span>
        </button>
    </div>

    {{-- Kosong --}}
    <div v-if="!baris.length" class="{{ $kartu }} py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-3xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        </div>
        <p class="mt-4 font-bold text-slate-700">{{ __('Belum ada laporan pada periode ini') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Laporan muncul setelah asatid mengisinya dari dasbor asatid > Penilaian.') }}</p>
    </div>

    {{-- ================= Rekap per siswa ================= --}}
    <section v-else-if="tab === 'rekap'" class="{{ $kartu }} p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <h2 class="flex-1 font-bold text-slate-800">{{ __('Rekap per siswa') }}</h2>
            <input type="search" v-model="cariRekap" placeholder="{{ __('Cari nama / nomor…') }}" class="sm:w-64 {{ $input }}">
        </div>

        {{-- HP: kartu --}}
        <div class="mt-4 space-y-2.5 lg:hidden">
            <button v-for="r in rekapTersaring" :key="r.siswa_id" type="button" @click="lihatSiswa(r)"
                class="w-full text-left rounded-2xl ring-1 ring-slate-100 bg-[#fbfdfc] p-3.5 hover:bg-[#f5f9f7] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br {{ $utama['grad'] }} text-white font-bold flex items-center justify-center" v-text="inisial(r.siswa)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="r.siswa"></span>
                        <span class="block text-xs text-slate-400 truncate" v-text="[r.nomor, r.kelas].filter(Boolean).join(' · ')"></span>
                    </span>
                    <span class="shrink-0 text-right">
                        <span class="block text-xl font-extrabold tabular-nums {{ $utama['teks'] }}" v-text="r.total"></span>
                        <span class="block text-[10px] text-slate-400">{{ __('laporan') }}</span>
                    </span>
                </span>
                <span class="mt-2.5 flex flex-wrap gap-1.5">
                    <template v-for="j in jenisDipakai" :key="j.id">
                        <span v-if="r.per[j.id]" class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold text-slate-700" :class="j.warna || 'bg-slate-100'">
                            <span v-text="j.nama"></span> · <b v-text="r.per[j.id]"></b>
                        </span>
                    </template>
                </span>
            </button>
        </div>

        {{-- Laptop: tabel --}}
        <div class="mt-4 hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="py-2.5 pr-3 w-10">#</th>
                        <th class="py-2.5 pr-3">{{ __('Siswa') }}</th>
                        <th v-for="j in jenisDipakai" :key="j.id" class="py-2.5 px-2 text-center whitespace-nowrap">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-slate-700" :class="j.warna || 'bg-slate-100'" v-text="j.nama"></span>
                        </th>
                        <th class="py-2.5 px-2 text-center">{{ __('Total') }}</th>
                        <th class="py-2.5 pl-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(r, i) in rekapTersaring" :key="r.siswa_id" class="border-b border-slate-50 hover:bg-[#f8fbfa]">
                        <td class="py-2.5 pr-3 text-slate-400 tabular-nums" v-text="i + 1"></td>
                        <td class="py-2.5 pr-3">
                            <span class="block font-semibold text-slate-800" v-text="r.siswa"></span>
                            <span class="block text-xs text-slate-400" v-text="[r.nomor, r.kelas].filter(Boolean).join(' · ')"></span>
                        </td>
                        <td v-for="j in jenisDipakai" :key="j.id" class="py-2.5 px-2 text-center tabular-nums"
                            :class="r.per[j.id] ? 'font-bold text-slate-800' : 'text-slate-300'" v-text="r.per[j.id] || '–'"></td>
                        <td class="py-2.5 px-2 text-center text-lg font-extrabold tabular-nums {{ $utama['teks'] }}" v-text="r.total"></td>
                        <td class="py-2.5 pl-2 text-right">
                            <button type="button" @click="lihatSiswa(r)" class="rounded-xl px-3 py-1.5 text-xs font-semibold ring-1 {{ $utama['lembut'] }} hover:brightness-95">{{ __('Rincian') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="!rekapTersaring.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Tidak ada siswa yang cocok.') }}</p>
    </section>

    {{-- ================= Semua laporan ================= --}}
    <section v-else class="{{ $kartu }} p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="search" v-model="saring.cari" placeholder="{{ __('Cari nama / nomor / catatan…') }}" class="{{ $input }}">
            <select v-model="saring.jenis" class="{{ $input }}">
                <option value="">{{ __('Semua jenis') }}</option>
                <option v-for="j in jenisDipakai" :key="j.id" :value="String(j.id)" v-text="j.nama"></option>
            </select>
            @if ($pelanggaran)
            <select v-model="saring.sumber" class="{{ $input }}">
                <option value="">{{ __('Semua sumber') }}</option>
                <option value="guru">{{ __('Laporan asatid') }}</option>
                <option value="absensi">{{ __('Dari absensi') }}</option>
            </select>
            @endif
            <select v-model="saring.oleh" class="{{ $input }}">
                <option value="">{{ __('Semua pelapor') }}</option>
                <option v-for="o in daftarPelapor" :key="o" :value="o" v-text="o"></option>
            </select>
        </div>

        <div v-if="saring.siswa" class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#0b332c] text-white pl-3 pr-1 py-1 text-xs font-semibold">
            {{ __('Siswa:') }} <span v-text="saring.siswaNama"></span>
            <button type="button" @click="saring.siswa = null" class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center" aria-label="{{ __('Hapus saringan siswa') }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <p class="mt-4 text-xs text-slate-400"><span v-text="laporanTersaring.length"></span> {{ __('laporan') }}</p>

        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="b in laporanTampil" :key="b.id" class="py-3.5 flex items-start gap-3">
                <span class="w-12 shrink-0 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 py-1.5 text-center">
                    <span class="block text-lg font-extrabold leading-none text-slate-800" v-text="b.tanggal.slice(8, 10)"></span>
                    <span class="block mt-0.5 text-[10px] font-semibold uppercase text-slate-400" v-text="bulan(b.tanggal)"></span>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <button type="button" @click="lihatSiswa(b)" class="font-semibold text-slate-800 hover:underline text-left" v-text="b.siswa"></button>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold text-slate-700" :class="b.warna || 'bg-slate-100'" v-text="b.jenis"></span>
                    </span>
                    <span class="block mt-0.5 text-xs text-slate-400">
                        <span v-text="b.hari"></span>, <span v-text="tanggalPanjang(b.tanggal)"></span>
                        <template v-if="b.jam"> · {{ __('jam') }} <span v-text="b.jam"></span></template>
                        <template v-if="b.kelas"> · <span v-text="b.kelas"></span></template>
                    </span>
                    <span v-if="b.catatan" class="block mt-1 text-sm text-slate-600 whitespace-pre-line" v-text="b.catatan"></span>
                    <a v-if="b.url" :href="b.url" class="block mt-1 text-xs font-semibold text-sky-600 hover:underline" v-text="b.keterangan"></a>
                    <span v-if="b.oleh" class="block mt-1 text-[11px] text-slate-400">
                        <template v-if="b.sumber === 'absensi'">{{ __('Dicatat oleh') }}</template>
                        <template v-else>{{ __('Dilaporkan oleh') }}</template>
                        <b class="text-slate-500" v-text="b.oleh"></b>
                    </span>
                </span>
                <button v-if="b.sumber === 'guru'" type="button" @click="hapus(b)" :disabled="menghapus === b.id"
                    title="{{ __('Hapus laporan') }}" aria-label="{{ __('Hapus laporan') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </li>
        </ul>

        <p v-if="!laporanTersaring.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Tidak ada laporan yang cocok.') }}</p>
        <div v-if="laporanTersaring.length > batas" class="mt-4 text-center">
            <button type="button" @click="batas += 50" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                {{ __('Tampilkan lebih banyak') }}
            </button>
        </div>
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const HAPUS = @json(route('admin.penilaian.' . $atur['tipe'] . '.hapus', ['id' => '__ID__']));
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();

        Vue.createApp({
            data: () => ({
                baris: @json($baris),
                jenis: @json($jenis),
                hariIni: @json($hariIni->toDateString()),
                tab: 'rekap',
                cariRekap: '',
                saring: { cari: '', jenis: '', sumber: '', oleh: '', siswa: null, siswaNama: '' },
                batas: 50,
                menghapus: null,
            }),
            computed: {
                jumlahSumber() {
                    return { guru: this.baris.filter((b) => b.sumber === 'guru').length, absensi: this.baris.filter((b) => b.sumber === 'absensi').length };
                },
                jumlahHariIni() { return this.baris.filter((b) => b.tanggal === this.hariIni).length; },
                // Jumlah per siswa, terbanyak dulu
                rekap() {
                    const per = {};
                    this.baris.forEach((b) => {
                        const r = per[b.siswa_id] ??= { siswa_id: b.siswa_id, siswa: b.siswa, nomor: b.nomor, kelas: b.kelas, total: 0, per: {} };
                        r.total++;
                        r.per[b.jenis_id] = (r.per[b.jenis_id] || 0) + 1;
                    });
                    return Object.values(per).sort((a, b) => b.total - a.total || a.siswa.localeCompare(b.siswa));
                },
                rekapTersaring() {
                    const k = kecil(this.cariRekap.trim());
                    return k ? this.rekap.filter((r) => kecil(r.siswa).includes(k) || kecil(r.nomor).includes(k)) : this.rekap;
                },
                // Jenis yang muncul di data saja (urutan Master Data), supaya tabel tidak terlalu lebar
                jenisDipakai() {
                    const ada = new Set(this.baris.map((b) => String(b.jenis_id)));
                    const hasil = this.jenis.filter((j) => ada.has(String(j.id)));
                    // Laporan yang jenisnya sudah dihapus dari Master Data
                    if (ada.has('null')) hasil.push({ id: null, nama: @js(__('(jenis sudah dihapus)')), warna: 'bg-slate-100' });
                    return hasil;
                },
                daftarPelapor() {
                    return [...new Set(this.baris.map((b) => b.oleh).filter(Boolean))].sort();
                },
                laporanTersaring() {
                    const s = this.saring;
                    const k = kecil(s.cari.trim());
                    return this.baris.filter((b) =>
                        (!s.siswa || b.siswa_id === s.siswa) &&
                        (!s.jenis || String(b.jenis_id) === s.jenis) &&
                        (!s.sumber || b.sumber === s.sumber) &&
                        (!s.oleh || b.oleh === s.oleh) &&
                        (!k || kecil(b.siswa).includes(k) || kecil(b.nomor).includes(k) || kecil(b.catatan).includes(k)));
                },
                laporanTampil() { return this.laporanTersaring.slice(0, this.batas); },
            },
            watch: {
                saring: { deep: true, handler() { this.batas = 50; } },
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                bulan: (t) => BULAN[Number(t.slice(5, 7)) - 1] || '',
                tanggalPanjang: (t) => `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}`,

                /** Buka semua laporan satu siswa */
                lihatSiswa(r) {
                    this.saring = { cari: '', jenis: '', sumber: '', oleh: '', siswa: r.siswa_id, siswaNama: r.siswa };
                    this.tab = 'laporan';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async hapus(b) {
                    const tanya = @js(__('Hapus laporan :jenis untuk :nama?'));
                    if (!confirm(tanya.replace(':jenis', b.jenis).replace(':nama', b.siswa))) return;
                    this.menghapus = b.id;
                    try {
                        const res = await fetch(HAPUS.replace('__ID__', b.nomor_id), {
                            method: 'DELETE',
                            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(json.message || @js(__('Gagal menghapus.')));
                        this.baris = this.baris.filter((x) => x.id !== b.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) {
                        window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                    } finally {
                        this.menghapus = null;
                    }
                },
            },
        }).mount('#halaman-penilaian-catatan');
    });
</script>
@endpush
