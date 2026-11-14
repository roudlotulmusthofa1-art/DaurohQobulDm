{{--
    Admin > Penilaian > Catatan (Admin\PenilaianController@catatan).
    Catatan dari asatid dikelompokkan menjadi 2 jenis: PENCAPAIAN dan PELANGGARAN
    (pelanggaran juga memuat setiap Terlambat & Alfa dari absensi, data dari App\Support\DataCatatan).
    - Tombol Semua / Pencapaian / Pelanggaran (alamat ?jenis=... ikut berubah)
    - Ringkasan, Rekap per santri, Semua catatan (saring + hapus catatan asatid)
    Semua data dikirim sekali ($baris), saringan & rekap dihitung Vue: #halaman-catatan.
--}}
@extends('layouts.admin')

@section('title', __('Catatan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$hariIni = now('Asia/Jakarta');
$cepat = [
    __('Semua waktu') => [null, null],
    __('Hari ini') => [$hariIni->toDateString(), $hariIni->toDateString()],
    __('7 hari') => [$hariIni->copy()->subDays(6)->toDateString(), $hariIni->toDateString()],
    __('Bulan ini') => [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->toDateString()],
];
@endphp

<div id="halaman-catatan" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="__('Catatan')"
        :keterangan="__('Catatan pencapaian & pelanggaran santri dari asatid. Terlambat & Alfa dari absensi ikut dihitung sebagai pelanggaran.')">
        <x-slot:aksi>
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-4 py-2 text-right">
                    <p class="text-2xl font-extrabold leading-tight text-emerald-300" v-text="jumlah.pencapaian"></p>
                    <p class="text-xs text-white/60">{{ __('pencapaian') }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-4 py-2 text-right">
                    <p class="text-2xl font-extrabold leading-tight text-rose-300" v-text="jumlah.pelanggaran"></p>
                    <p class="text-xs text-white/60">{{ __('pelanggaran') }}</p>
                </div>
            </div>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Dua jenis catatan ===== --}}
    <div class="-mx-1 overflow-x-auto px-1 pb-1">
        <div class="inline-flex gap-1.5 rounded-2xl bg-white p-2 shadow-sm ring-1 ring-black/5">
            <button v-for="p in PILIHAN" :key="p.kunci" type="button" @click="pilihJenis(p.kunci)"
                class="flex shrink-0 items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all"
                :class="jenis === p.kunci ? p.aktif : 'text-gray-600 hover:bg-gray-50'">
                <span class="h-2.5 w-2.5 rounded-full" :class="jenis === p.kunci ? 'bg-white' : p.titik"></span>
                <span v-text="p.label"></span>
                <span class="rounded-full px-2 text-[11px]" :class="jenis === p.kunci ? 'bg-white/20' : 'bg-gray-100 text-gray-500'" v-text="p.kunci ? jumlah[p.kunci] : baris.length"></span>
            </button>
        </div>
    </div>

    {{-- ===== Periode ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 sm:p-5">
        <input type="hidden" name="jenis" :value="jenis">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Dari') }}</span>
                <input type="date" name="dari" value="{{ $dari }}" class="mt-1 {{ $input }}"></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Sampai') }}</span>
                <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 {{ $input }}"></label>
            <div class="col-span-2">
                <button class="w-full lg:w-auto px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Terapkan') }}</button>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($cepat as $l => [$d, $s])
            @php $aktif = $dari === $d && $sampai === $s; @endphp
            <a href="{{ route('admin.penilaian.catatan.index', array_filter(['dari' => $d, 'sampai' => $s])) }}" @click="tambahJenis"
                class="px-3 py-1.5 rounded-full text-xs font-semibold ring-1 transition-colors {{ $aktif ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">{{ $l }}</a>
            @endforeach
        </div>
    </form>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="flex items-center gap-1.5 text-xs font-semibold text-slate-500"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>{{ __('Pencapaian') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-emerald-600 tabular-nums" v-text="jumlah.pencapaian"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ __('catatan dari asatid') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="flex items-center gap-1.5 text-xs font-semibold text-slate-500"><span class="w-2 h-2 rounded-full bg-rose-500"></span>{{ __('Pelanggaran') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-rose-600 tabular-nums" v-text="jumlah.pelanggaran"></p>
            <p class="mt-1 text-[11px] text-slate-400">
                <span v-text="jumlah.pelanggaranGuru"></span> {{ __('dari asatid') }} ·
                <span v-text="jumlah.pelanggaran - jumlah.pelanggaranGuru"></span> {{ __('dari absensi') }}
            </p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Hari ini') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="jumlahHariIni"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ $hariIni->locale('id')->translatedFormat('l, d M Y') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Santri') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="rekap.length"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ __('santri punya catatan') }}</p>
        </div>
    </div>

    {{-- ===== Tab tampilan ===== --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        <button type="button" @click="tab = 'rekap'"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
            :class="tab === 'rekap' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            {{ __('Rekap per santri') }}
        </button>
        <button type="button" @click="tab = 'daftar'"
            class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
            :class="tab === 'daftar' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            {{ __('Semua catatan') }}
            <span class="rounded-full px-2 text-[11px]" :class="tab === 'daftar' ? 'bg-white/20' : 'bg-slate-100'" v-text="barisJenis.length"></span>
        </button>
    </div>

    {{-- Kosong --}}
    <div v-if="!barisJenis.length" class="{{ $kartu }} py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-3xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
        </div>
        <p class="mt-4 font-bold text-slate-700">{{ __('Belum ada catatan pada periode ini') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Catatan muncul setelah asatid mengisinya dari dasbor asatid > Penilaian > Catatan.') }}</p>
    </div>

    {{-- ================= Rekap per santri ================= --}}
    <section v-else-if="tab === 'rekap'" class="{{ $kartu }} p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <h2 class="flex-1 font-bold text-slate-800">{{ __('Rekap per santri') }}</h2>
            <input type="search" v-model="cariRekap" placeholder="{{ __('Cari nama / nomor…') }}" class="sm:w-64 {{ $input }}">
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="py-2.5 pr-3 w-8">#</th>
                        <th class="py-2.5 pr-3">{{ __('Santri') }}</th>
                        <th v-if="jenis !== 'pelanggaran'" class="py-2.5 px-2 text-center"><span class="rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5">{{ __('Pencapaian') }}</span></th>
                        <th v-if="jenis !== 'pencapaian'" class="py-2.5 px-2 text-center"><span class="rounded-full bg-rose-50 text-rose-700 px-2.5 py-0.5">{{ __('Pelanggaran') }}</span></th>
                        <th class="py-2.5 pl-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(r, i) in rekapTersaring" :key="r.siswa_id" class="border-b border-slate-50 hover:bg-[#f8fbfa]">
                        <td class="py-2.5 pr-3 text-slate-400 tabular-nums" v-text="i + 1"></td>
                        <td class="py-2.5 pr-3 min-w-40">
                            <span class="block font-semibold text-slate-800" v-text="r.siswa"></span>
                            <x-nama-arab vue="r.arab" />
                            <span v-if="r.kelas" class="block text-xs text-slate-400" v-text="r.kelas"></span>
                        </td>
                        <td v-if="jenis !== 'pelanggaran'" class="py-2.5 px-2 text-center">
                            <span class="text-lg font-extrabold tabular-nums" :class="r.pencapaian ? 'text-emerald-600' : 'text-slate-300'" v-text="r.pencapaian || '–'"></span>
                        </td>
                        <td v-if="jenis !== 'pencapaian'" class="py-2.5 px-2 text-center">
                            <span class="block text-lg font-extrabold tabular-nums" :class="r.pelanggaran ? 'text-rose-600' : 'text-slate-300'" v-text="r.pelanggaran || '–'"></span>
                            <span v-if="r.terlambat || r.alfa" class="block text-[10px] text-slate-400 whitespace-nowrap">
                                <template v-if="r.terlambat">{{ __('Terlambat') }} <span v-text="r.terlambat"></span></template>
                                <template v-if="r.terlambat && r.alfa"> · </template>
                                <template v-if="r.alfa">{{ __('Alfa') }} <span v-text="r.alfa"></span></template>
                            </span>
                        </td>
                        <td class="py-2.5 pl-2 text-right">
                            <button type="button" @click="lihatSantri(r)" class="rounded-xl px-3 py-1.5 text-xs font-semibold ring-1 ring-slate-200 text-slate-600 hover:bg-slate-50">{{ __('Rincian') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-if="!rekapTersaring.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Tidak ada santri yang cocok.') }}</p>
    </section>

    {{-- ================= Semua catatan ================= --}}
    <section v-else class="{{ $kartu }} p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="search" v-model="saring.cari" placeholder="{{ __('Cari nama / nomor / isi catatan…') }}" class="{{ $input }}">
            <select v-model="saring.sumber" class="{{ $input }}" :disabled="jenis === 'pencapaian'">
                <option value="">{{ __('Semua sumber') }}</option>
                <option value="guru">{{ __('Catatan asatid') }}</option>
                <option value="absensi">{{ __('Dari absensi (Terlambat/Alfa)') }}</option>
                <option value="izin">{{ __('Dari perizinan (terlambat kembali)') }}</option>
            </select>
            <select v-model="saring.oleh" class="{{ $input }}">
                <option value="">{{ __('Semua pelapor') }}</option>
                <option v-for="o in daftarPelapor" :key="o" :value="o" v-text="o"></option>
            </select>
        </div>

        <div v-if="saring.siswa" class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#0b332c] text-white pl-3 pr-1 py-1 text-xs font-semibold">
            {{ __('Santri:') }}
            <span class="min-w-0">
                <span class="block" v-text="saring.siswaNama"></span>
                <x-nama-arab vue="saring.siswaArab" kelas="text-xs font-normal text-white/70" />
            </span>
            <button type="button" @click="saring.siswa = null" class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center" aria-label="{{ __('Hapus saringan santri') }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <p class="mt-4 text-xs text-slate-400"><span v-text="daftarTersaring.length"></span> {{ __('catatan') }}</p>

        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="b in daftarTampil" :key="b.id" class="py-3.5 flex items-start gap-3">
                <span class="w-12 shrink-0 rounded-2xl py-1.5 text-center ring-1"
                    :class="b.jenis === 'pencapaian' ? 'bg-emerald-50 ring-emerald-100' : 'bg-rose-50 ring-rose-100'">
                    <span class="block text-lg font-extrabold leading-none text-slate-800" v-text="b.tanggal.slice(8, 10)"></span>
                    <span class="block mt-0.5 text-[10px] font-semibold uppercase text-slate-400" v-text="bulan(b.tanggal)"></span>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <button type="button" @click="lihatSantri(b)" class="font-semibold text-slate-800 hover:underline text-left" v-text="b.siswa"></button>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1"
                            :class="b.jenis === 'pencapaian' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200'" v-text="b.label"></span>
                    </span>
                    <x-nama-arab vue="b.arab" />
                    <span class="block mt-0.5 text-xs text-slate-400">
                        <span v-text="b.hari"></span>, <span v-text="tanggalPanjang(b.tanggal)"></span>
                        <template v-if="b.jam"> · {{ __('jam') }} <span v-text="b.jam"></span></template>
                        <template v-if="b.kelas"> · <span v-text="b.kelas"></span></template>
                    </span>
                    <span v-if="b.catatan" class="block mt-1 text-sm text-slate-700 whitespace-pre-line" v-text="b.catatan"></span>
                    <a v-if="b.url" :href="b.url" class="block mt-1 text-xs font-semibold text-sky-600 hover:underline" v-text="b.keterangan"></a>
                    <span v-if="b.oleh" class="block mt-1 text-[11px] text-slate-400">
                        {{-- spasi di dalam template: Vue membuang spasi + baris baru di antara elemen --}}
                        <template v-if="b.sumber === 'absensi'">{{ __('Dicatat oleh') }} </template>
                        <template v-else-if="b.sumber === 'izin'">{{ __('Izin diberikan oleh') }} </template>
                        <template v-else>{{ __('Ditulis oleh') }} </template>
                        <b class="text-slate-500" v-text="b.oleh"></b>
                    </span>
                    <x-nama-arab vue="b.oleh_arab" kelas="text-[11px] text-slate-400" />
                </span>
                <button v-if="b.sumber === 'guru'" type="button" @click="bukaUbah(b)"
                    title="{{ __('Ubah catatan') }}" aria-label="{{ __('Ubah catatan') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </button>
                <button v-if="b.sumber === 'guru'" type="button" @click="hapus(b)" :disabled="menghapus === b.id"
                    title="{{ __('Hapus catatan') }}" aria-label="{{ __('Hapus catatan') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </li>
        </ul>

        <p v-if="!daftarTersaring.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Tidak ada catatan yang cocok.') }}</p>
        <div v-if="daftarTersaring.length > batas" class="mt-4 text-center">
            <button type="button" @click="batas += 50" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                {{ __('Tampilkan lebih banyak') }}
            </button>
        </div>
    </section>
</div>

{{-- ===== Modal: Ubah Catatan (hanya catatan yang ditulis asatid / admin) ===== --}}
<div v-if="ubah" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="ubah = null">
    <form class="w-full max-w-lg rounded-3xl bg-white shadow-xl" @submit.prevent="simpanUbah">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="min-w-0">
                <h3 class="text-base font-extrabold text-slate-800">{{ __('Ubah Catatan') }}</h3>
                <p class="text-xs text-slate-500 truncate" v-text="ubah.siswa"></p>
            </div>
            <button type="button" @click="ubah = null" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="ubah.jenis = 'pencapaian'" class="py-2.5 rounded-xl text-sm font-bold ring-1"
                    :class="ubah.jenis === 'pencapaian' ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Pencapaian') }}</button>
                <button type="button" @click="ubah.jenis = 'pelanggaran'" class="py-2.5 rounded-xl text-sm font-bold ring-1"
                    :class="ubah.jenis === 'pelanggaran' ? 'bg-rose-600 text-white ring-rose-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Pelanggaran') }}</button>
            </div>
            <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Isi catatan') }}</span>
                <textarea v-model="ubah.catatan" rows="4" maxlength="5000" required class="mt-1 {{ $input }}"></textarea></label>
            <div class="grid grid-cols-2 gap-2">
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                    <input type="date" v-model="ubah.tanggal" required class="mt-1 {{ $input }}"></label>
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Jam') }}</span>
                    <input type="time" v-model="ubah.jam" class="mt-1 {{ $input }}"></label>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
            <button type="button" @click="ubah = null" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                <template v-else>{{ __('Simpan Perubahan') }}</template>
            </button>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const HAPUS = @json(route('admin.penilaian.catatan.hapus', ['catatan' => '__ID__']));
        const UBAH = @json(route('admin.penilaian.catatan.ubah', ['catatan' => '__ID__']));
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        const PILIHAN = [
            { kunci: '', label: @js(__('Semua')), titik: 'bg-slate-400', aktif: 'bg-[#0b332c] text-white shadow-md' },
            { kunci: 'pencapaian', label: @js(__('Pencapaian')), titik: 'bg-emerald-500', aktif: 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25' },
            { kunci: 'pelanggaran', label: @js(__('Pelanggaran')), titik: 'bg-rose-500', aktif: 'bg-rose-600 text-white shadow-md shadow-rose-600/25' },
        ];

        Vue.createApp({
            data: () => ({
                PILIHAN,
                baris: @json($baris),
                jenis: @json($jenis),
                hariIni: @json($hariIni->toDateString()),
                tab: 'rekap',
                cariRekap: '',
                saring: { cari: '', sumber: '', oleh: '', siswa: null, siswaNama: '', siswaArab: '' },
                batas: 50,
                menghapus: null,
                ubah: null,       // catatan yang sedang diubah (salinan isian)
                menyimpan: false,
            }),
            computed: {
                jumlah() {
                    const p = this.baris.filter((b) => b.jenis === 'pelanggaran');
                    return {
                        pencapaian: this.baris.filter((b) => b.jenis === 'pencapaian').length,
                        pelanggaran: p.length,
                        pelanggaranGuru: p.filter((b) => b.sumber === 'guru').length,
                    };
                },
                // Baris menurut tombol Semua / Pencapaian / Pelanggaran
                barisJenis() { return this.jenis ? this.baris.filter((b) => b.jenis === this.jenis) : this.baris; },
                jumlahHariIni() { return this.barisJenis.filter((b) => b.tanggal === this.hariIni).length; },
                rekap() {
                    const per = {};
                    this.barisJenis.forEach((b) => {
                        const r = per[b.siswa_id] ??= { siswa_id: b.siswa_id, siswa: b.siswa, arab: b.arab, nomor: b.nomor, kelas: b.kelas, pencapaian: 0, pelanggaran: 0, terlambat: 0, alfa: 0 };
                        r[b.jenis]++;
                        if (b.asal) r[b.asal]++; // asal: 'terlambat' | 'alfa' (hanya baris dari absensi)
                    });
                    const nilai = (r) => this.jenis ? r[this.jenis] : r.pencapaian + r.pelanggaran;
                    return Object.values(per).sort((a, b) => nilai(b) - nilai(a) || a.siswa.localeCompare(b.siswa));
                },
                rekapTersaring() {
                    const k = kecil(this.cariRekap.trim());
                    return k ? this.rekap.filter((r) => kecil(r.siswa).includes(k) || kecil(r.nomor).includes(k)) : this.rekap;
                },
                daftarPelapor() { return [...new Set(this.barisJenis.map((b) => b.oleh).filter(Boolean))].sort(); },
                daftarTersaring() {
                    const s = this.saring;
                    const k = kecil(s.cari.trim());
                    return this.barisJenis.filter((b) =>
                        (!s.siswa || b.siswa_id === s.siswa) &&
                        (!s.sumber || b.sumber === s.sumber) &&
                        (!s.oleh || b.oleh === s.oleh) &&
                        (!k || kecil(b.siswa).includes(k) || kecil(b.nomor).includes(k) || kecil(b.catatan).includes(k)));
                },
                daftarTampil() { return this.daftarTersaring.slice(0, this.batas); },
            },
            watch: {
                saring: { deep: true, handler() { this.batas = 50; } },
            },
            methods: {
                bulan: (t) => BULAN[Number(t.slice(5, 7)) - 1] || '',
                tanggalPanjang: (t) => `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}`,

                /** Tombol Semua / Pencapaian / Pelanggaran; alamat ?jenis=... ikut berubah */
                pilihJenis(kunci) {
                    this.jenis = kunci;
                    if (kunci === 'pencapaian') this.saring.sumber = '';
                    const u = new URL(location.href);
                    kunci ? u.searchParams.set('jenis', kunci) : u.searchParams.delete('jenis');
                    history.replaceState(null, '', u);
                },
                // Tombol periode cepat: jenis yang sedang dipilih ikut dibawa
                tambahJenis(e) {
                    if (!this.jenis) return;
                    e.preventDefault();
                    const u = new URL(e.currentTarget.href);
                    u.searchParams.set('jenis', this.jenis);
                    location.href = u;
                },
                lihatSantri(r) {
                    this.saring = { cari: '', sumber: '', oleh: '', siswa: r.siswa_id, siswaNama: r.siswa, siswaArab: r.arab };
                    this.tab = 'daftar';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                bukaUbah(b) {
                    this.ubah = { id: b.id, nomor_id: b.nomor_id, siswa: b.siswa, jenis: b.jenis, catatan: b.catatan || '', tanggal: b.tanggal, jam: b.jam || '' };
                },
                async simpanUbah() {
                    const u = this.ubah;
                    this.menyimpan = true;
                    try {
                        const res = await fetch(UBAH.replace('__ID__', u.nomor_id), {
                            method: 'PUT',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                            body: JSON.stringify({ jenis: u.jenis, catatan: u.catatan, tanggal: u.tanggal, jam: u.jam || null }),
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || @js(__('Gagal menyimpan. Coba lagi.')));
                        this.baris = this.baris.map((x) => (x.id === u.id ? json.baris : x))
                            .sort((a, b) => (b.tanggal + ' ' + (b.jam || '00:00')).localeCompare(a.tanggal + ' ' + (a.jam || '00:00')));
                        this.ubah = null;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) {
                        window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },

                async hapus(b) {
                    const tanya = @js(__('Hapus catatan :jenis untuk :nama?'));
                    if (!confirm(tanya.replace(':jenis', b.label).replace(':nama', b.siswa))) return;
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
        }).mount('#halaman-catatan');
    });
</script>
@endpush
