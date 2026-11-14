{{--
    Admin > Penilaian > Perizinan (PerizinanController@index). Khusus admin.
    - Tombol "Jenis Izin" : tambah / hapus nama jenis izin (tombol +).
    - Tombol "Beri Izin"  : 1) santri  2) jenis izin  3) mulai (tanggal + jam)  4) berakhir (tanggal + jam)  5) catatan.
      Satu santri boleh beberapa izin dalam sehari.
    - Daftar izin + saringan status. Tombol "Sudah kembali" mencatat waktu kembali (otomatis sekarang, bisa diubah).
    - Tombol "Pindai Kembali": kamera (html5-qrcode, window.muatPemindaiQr) / alat scanner USB / ketik nomor pendaftaran
      -> izin santri yang sedang berjalan langsung ditandai kembali (PerizinanController@pindai).
    - Terlambat (kembali lewat batas / batas lewat tapi belum kembali) otomatis tampil di Penilaian > Catatan > Pelanggaran.
    Status dihitung ulang tiap menit di layar memakai jam server (WIB), jadi "Sedang izin" berubah sendiri jadi "Terlambat".
    Vue: #halaman-perizinan.
--}}
@extends('layouts.admin')

@section('title', __('Perizinan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$saringan = [
    ''          => __('Semua'),
    'sedang'    => __('Sedang izin'),
    'terlambat' => __('Terlambat'),
    'tepat'     => __('Tepat waktu'),
    'belum'     => __('Belum mulai'),
];
@endphp

<div id="halaman-perizinan" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="__('Perizinan')"
        :keterangan="__('Beri izin santri dengan batas waktu. Yang terlambat kembali otomatis masuk catatan pelanggaran.')">
        <x-slot:aksi>
            <a href="{{ route('admin.penilaian.catatan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-white/10 ring-1 ring-white/20 hover:bg-white/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                {{ __('Catatan Pelanggaran') }}
            </a>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Kartu tombol + ringkasan ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-[#0d9488] text-white flex items-center justify-center shadow-lg shadow-black/10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 4h3a2 2 0 012 2v14M2 20h3m8 0h9M10 12v.01M13 4.562v16.157a1 1 0 01-1.242.97L5 20V5.562a2 2 0 011.515-1.94l4-1A2 2 0 0113 4.561z"/></svg>
            </span>
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-extrabold text-slate-800">{{ __('Perizinan Santri') }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ __('Pilih santri, jenis izin, lalu batas waktunya. Tandai "Sudah kembali" saat santri datang.') }}</p>
            </div>
            <div class="grid grid-cols-2 sm:flex gap-2">
                <button type="button" @click="bukaPindai"
                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-black/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                    {{ __('Pindai Kembali') }}
                </button>
                <button type="button" @click="modalJenis = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    {{ __('Jenis Izin') }}
                </button>
                <button type="button" @click="bukaIzin"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold text-white bg-[#0d9488] hover:opacity-90 shadow-md shadow-black/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Beri Izin') }}
                </button>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-2 sm:gap-3">
            <button type="button" @click="status = 'sedang'" class="text-left rounded-2xl bg-sky-50 ring-1 ring-sky-100 p-3 hover:ring-sky-300">
                <p class="text-[11px] font-semibold text-sky-700">{{ __('Sedang izin') }}</p>
                <p class="mt-0.5 text-2xl font-extrabold text-sky-700 tabular-nums" v-text="jumlah.sedang"></p>
            </button>
            <button type="button" @click="status = 'terlambat'" class="text-left rounded-2xl bg-rose-50 ring-1 ring-rose-100 p-3 hover:ring-rose-300">
                <p class="text-[11px] font-semibold text-rose-700">{{ __('Terlambat') }}</p>
                <p class="mt-0.5 text-2xl font-extrabold text-rose-700 tabular-nums" v-text="jumlah.terlambat"></p>
            </button>
            <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3">
                <p class="text-[11px] font-semibold text-slate-500">{{ __('Izin hari ini') }}</p>
                <p class="mt-0.5 text-2xl font-extrabold text-slate-800 tabular-nums" v-text="jumlah.hariIni"></p>
            </div>
        </div>
    </section>

    {{-- ===== Daftar izin ===== --}}
    <section class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="-mx-1 flex-1 overflow-x-auto px-1">
                <div class="inline-flex gap-1.5">
                    @foreach ($saringan as $kunci => $label)
                    <button type="button" @click="status = '{{ $kunci }}'"
                        class="shrink-0 rounded-full px-3.5 py-1.5 text-sm font-semibold ring-1 transition"
                        :class="status === '{{ $kunci }}' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
            <input type="search" v-model="cari" placeholder="{{ __('Cari santri, jenis izin, catatan...') }}" class="lg:max-w-xs {{ $input }}">
        </div>

        <ul class="mt-3 divide-y divide-slate-100">
            <li v-for="i in izinTampil" :key="i.id" class="py-3.5 flex items-start gap-3">
                <span class="w-12 shrink-0 rounded-2xl py-1.5 text-center ring-1" :class="warna(i.status).kotak">
                    <span class="block text-lg font-extrabold leading-none text-slate-800" v-text="i.mulai_tanggal.slice(8, 10)"></span>
                    <span class="block mt-0.5 text-[10px] font-semibold uppercase text-slate-400" v-text="bulan(i.mulai_tanggal)"></span>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-semibold text-slate-800" v-text="i.siswa"></span>
                        <span dir="auto" class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600" v-text="i.jenis"></span>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1" :class="warna(i.status).label" v-text="labelStatus(i.status)"></span>
                    </span>
                    <x-nama-arab vue="i.arab" />
                    <span class="block mt-0.5 text-xs text-slate-500">
                        <span v-text="rentang(i)"></span>
                        <template v-if="i.kelas"> · <span v-text="i.kelas"></span></template>
                    </span>
                    <span v-if="i.kembali_jam" class="block mt-0.5 text-xs" :class="i.status === 'terlambat' ? 'text-rose-600 font-semibold' : 'text-emerald-700'">
                        {{ __('Kembali') }} <span v-text="waktuPendek(i.kembali_tanggal, i.kembali_jam, i.selesai_tanggal)"></span>
                        <template v-if="i.status === 'terlambat'"> · {{ __('terlambat') }} <span v-text="lamaTelat(i)"></span></template>
                    </span>
                    <span v-else-if="i.status === 'terlambat'" class="block mt-0.5 text-xs font-semibold text-rose-600">
                        {{ __('Belum kembali') }} · {{ __('lewat') }} <span v-text="lamaTelat(i)"></span>
                    </span>
                    <span v-if="i.catatan" class="block mt-1 text-sm text-slate-700 whitespace-pre-line" v-text="i.catatan"></span>
                    <span v-if="i.oleh" class="block mt-1 text-[11px] text-slate-400">{{ __('Izin diberikan oleh') }} <b class="text-slate-500" v-text="i.oleh"></b></span>
                    <x-nama-arab vue="i.oleh_arab" kelas="text-[11px] text-slate-400" />
                </span>
                <span class="shrink-0 flex flex-col items-end gap-1.5">
                    <button v-if="!i.kembali_jam" type="button" @click="bukaKembali(i)"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 whitespace-nowrap">
                        {{ __('Sudah kembali') }}
                    </button>
                    <button v-else type="button" @click="bukaKembali(i)"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50 whitespace-nowrap">
                        {{ __('Ubah kembali') }}
                    </button>
                    <button type="button" @click="hapus(i)" :disabled="menghapus === i.id"
                        title="{{ __('Hapus izin') }}" aria-label="{{ __('Hapus izin') }}"
                        class="w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </span>
            </li>
        </ul>

        <p v-if="!izinCari.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Belum ada izin. Tekan "Beri Izin" untuk memulai.') }}</p>
        <div v-if="izinCari.length > batas" class="mt-4 text-center">
            <button type="button" @click="batas += 50" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                {{ __('Tampilkan lebih banyak') }}
            </button>
        </div>
    </section>
</div>

{{-- ===== Modal: Jenis Izin ===== --}}
<div v-if="modalJenis" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalJenis = false">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Jenis Izin') }}</h3>
            <button type="button" @click="modalJenis = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-400">{{ __('Nama jenis izin yang bisa dipilih saat memberi izin, mis. Pulang, Berobat, Keluar pondok.') }}</p>
            <div class="mt-2.5 flex flex-wrap gap-2">
                <span v-for="j in jenis" :key="j.id" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 pl-3 pr-1 py-1 text-sm font-semibold">
                    <span dir="auto" v-text="j.nama"></span>
                    <button type="button" @click="hapusJenis(j)" class="w-6 h-6 rounded-full hover:bg-emerald-100 flex items-center justify-center" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
                <span v-if="!jenis.length" class="text-xs text-slate-400 py-1.5">{{ __('Belum ada. Tulis di bawah lalu tekan +.') }}</span>
            </div>
            <form class="mt-3 flex gap-2" @submit.prevent="tambahJenis">
                <input type="text" v-model="jenisBaru" maxlength="100" placeholder="{{ __('mis. Pulang') }}" class="{{ $input }}">
                <button :disabled="menyimpan" class="shrink-0 w-11 h-11 rounded-xl bg-[#0b332c] text-white hover:bg-[#135347] flex items-center justify-center disabled:opacity-50" title="{{ __('Tambah') }}" aria-label="{{ __('Tambah') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </button>
            </form>
        </div>
        <div class="border-t border-slate-100 px-6 py-4 text-right">
            <button type="button" @click="modalJenis = false" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Selesai') }}</button>
        </div>
    </div>
</div>

{{-- ===== Modal: Beri Izin ===== --}}
<div v-if="modalIzin" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalIzin = false">
    <form class="w-full max-w-lg rounded-3xl bg-white shadow-xl" @submit.prevent="simpan">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Beri Izin') }}</h3>
            <button type="button" @click="modalIzin = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-5">
            {{-- 1. Santri --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">1.</span> {{ __('Nama santri') }}</p>
                <div v-if="santriTerpilih" class="mt-2 flex items-center gap-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 p-2.5">
                    <img v-if="santriTerpilih.foto" :src="santriTerpilih.foto" alt="" class="w-10 h-10 rounded-xl object-cover">
                    <span v-else class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center" v-text="inisial(santriTerpilih.siswa)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="santriTerpilih.siswa"></span>
                        <x-nama-arab vue="santriTerpilih.arab" />
                        <span v-if="santriTerpilih.kelas" class="block text-xs text-slate-500 truncate" v-text="santriTerpilih.kelas"></span>
                    </span>
                    <button type="button" @click="form.siswa_id = null" class="px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 hover:bg-emerald-100">{{ __('Ganti') }}</button>
                </div>
                <template v-else>
                    <input type="search" v-model="cariSantri" placeholder="{{ __('Cari nama / nomor santri...') }}" class="mt-2 {{ $input }}">
                    <ul class="mt-2 max-h-56 overflow-y-auto rounded-2xl ring-1 ring-slate-100 divide-y divide-slate-100">
                        <li v-for="s in santriCari" :key="s.siswa_id">
                            <button type="button" @click="form.siswa_id = s.siswa_id" class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-slate-50">
                                <img v-if="s.foto" :src="s.foto" alt="" class="w-8 h-8 rounded-lg object-cover">
                                <span v-else class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 text-sm font-bold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800 truncate" v-text="s.siswa"></span>
                                    <x-nama-arab vue="s.arab" kelas="text-xs text-slate-500" />
                                    <span v-if="s.kelas" class="block text-[11px] text-slate-400 truncate" v-text="s.kelas"></span>
                                </span>
                                <span v-if="sedangIzin(s.siswa_id)" class="shrink-0 rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700 ring-1 ring-sky-200">{{ __('Sedang izin') }}</span>
                            </button>
                        </li>
                        <li v-if="!santriCari.length" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('Santri tidak ditemukan.') }}</li>
                    </ul>
                </template>
            </div>

            {{-- 2. Jenis izin --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">2.</span> {{ __('Jenis izin') }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="j in jenis" :key="j.id" type="button" @click="form.jenis = j.nama"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="form.jenis === j.nama ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        dir="auto" v-text="j.nama"></button>
                </div>
                <p v-if="!jenis.length" class="mt-1 text-xs text-amber-600">{{ __('Belum ada jenis izin. Tambahkan dulu lewat tombol Jenis Izin.') }}</p>
            </div>

            {{-- 3. Mulai --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">3.</span> {{ __('Mulai izin') }}</p>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                        <input type="date" v-model="form.mulai_tanggal" @change="samakanTanggal" required class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Jam') }}</span>
                        <input type="time" v-model="form.mulai_jam" required class="mt-1 {{ $input }}"></label>
                </div>
            </div>

            {{-- 4. Berakhir --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">4.</span> {{ __('Berakhir (batas kembali)') }}</p>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                        <input type="date" v-model="form.selesai_tanggal" :min="form.mulai_tanggal" required class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Jam') }}</span>
                        <input type="time" v-model="form.selesai_jam" required class="mt-1 {{ $input }}"></label>
                </div>
                <p class="mt-1.5 text-xs text-slate-500" v-text="lamaIzin"></p>
            </div>

            {{-- 5. Catatan --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">5.</span> {{ __('Catatan') }} <span class="font-normal text-slate-400">({{ __('boleh dikosongkan') }})</span></p>
                <textarea v-model="form.catatan" rows="3" maxlength="5000" placeholder="{{ __('mis. dijemput orang tua') }}" class="mt-2 {{ $input }}"></textarea>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
            <button type="button" @click="modalIzin = false" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                <template v-else>{{ __('Simpan Izin') }}</template>
            </button>
        </div>
    </form>
</div>

{{-- ===== Modal: Pindai Kembali (kartu QR santri) =====
     v-show (bukan v-if) supaya kotak kamera #pembaca-izin tetap ada; kamera dimatikan saat ditutup. --}}
<div v-show="modalPindai" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/60 p-4 sm:items-center" @mousedown.self="tutupPindai">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-800">{{ __('Pindai Kembali') }}</h3>
                <p class="text-xs text-slate-500">{{ __('Arahkan kamera ke kartu QR santri yang baru kembali.') }}</p>
            </div>
            <button type="button" @click="tutupPindai" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-4 sm:p-5">
            <div class="relative aspect-square sm:aspect-[4/3] rounded-[1.25rem] bg-[#071f1a] overflow-hidden">
                <div id="pembaca-izin" class="absolute inset-0"></div>

                {{-- Bingkai pindai --}}
                <div v-if="kameraNyala" class="pointer-events-none absolute inset-[12%] rounded-3xl ring-2 ring-white/70 animate-pulse"></div>

                {{-- Kamera mati --}}
                <div v-if="!kameraNyala" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 text-white">
                    <p class="font-bold">{{ __('Kamera belum menyala') }}</p>
                    <p class="mt-1 text-xs text-white/60 max-w-xs" v-text="galatKamera || @js(__('Bisa juga memakai alat scanner atau mengetik nomor pendaftaran di bawah.'))"></p>
                    <button type="button" @click="nyalakan" :disabled="memuatKamera"
                        class="mt-4 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-400 text-[#071f1a] text-sm font-extrabold hover:bg-emerald-300 disabled:opacity-60">
                        <span v-if="memuatKamera">{{ __('Menyalakan…') }}</span>
                        <span v-else>{{ __('Nyalakan Kamera') }}</span>
                    </button>
                </div>

                {{-- Hasil pindai --}}
                <div v-if="hasil" :key="hasil.kunci" class="absolute inset-x-3 bottom-3 rounded-2xl p-3 flex items-center gap-3 shadow-2xl"
                    :class="{ tepat: 'bg-emerald-600 text-white', terlambat: 'bg-rose-600 text-white', tidak: 'bg-white text-slate-800', galat: 'bg-amber-500 text-white' }[hasil.tipe]">
                    <img v-if="hasil.santri && hasil.santri.foto" :src="hasil.santri.foto" alt="" class="w-14 h-14 shrink-0 rounded-2xl object-cover bg-white">
                    <span v-else-if="hasil.santri" class="w-14 h-14 shrink-0 rounded-2xl bg-white/20 text-xl font-extrabold flex items-center justify-center" v-text="inisial(hasil.santri.nama)"></span>
                    <div class="flex-1 min-w-0">
                        <p v-if="hasil.santri" class="font-extrabold truncate" v-text="hasil.santri.nama"></p>
                        <x-nama-arab vue="hasil.santri && hasil.santri.arab" kelas="text-sm opacity-80" />
                        <p class="text-sm font-semibold" v-text="hasil.message"></p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="kirimKode" class="mt-4 flex items-center gap-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 pl-4 pr-1.5 py-1.5">
                <input ref="kodeInput" v-model="kodeManual" type="text" placeholder="{{ __('Ketik nomor pendaftaran, atau pakai alat scanner') }}"
                    class="flex-1 min-w-0 bg-transparent border-0 p-0 text-sm outline-none focus:ring-0">
                <button type="submit" :disabled="mengirim" class="shrink-0 px-3.5 py-2 rounded-xl bg-[#0b332c] text-white text-xs font-bold disabled:opacity-50">{{ __('Catat') }}</button>
            </form>

            {{-- Baru kembali --}}
            <div v-if="baruKembali.length" class="mt-4">
                <p class="text-xs font-bold text-slate-500">{{ __('Baru kembali') }}</p>
                <ul class="mt-2 space-y-1.5">
                    <li v-for="b in baruKembali" :key="b.kunci" class="flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2 text-sm">
                        <span class="w-2 h-2 shrink-0 rounded-full" :class="b.tipe === 'terlambat' ? 'bg-rose-500' : 'bg-emerald-500'"></span>
                        <span class="flex-1 min-w-0">
                            <span class="block truncate font-semibold text-slate-700" v-text="b.santri.nama"></span>
                            <x-nama-arab vue="b.santri.arab" kelas="text-xs text-slate-500" />
                        </span>
                        <span class="shrink-0 text-xs font-semibold" :class="b.tipe === 'terlambat' ? 'text-rose-600' : 'text-emerald-700'" v-text="b.message"></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
            <button v-if="kameraNyala" type="button" @click="matikan" class="px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Matikan kamera') }}</button>
            <button type="button" @click="tutupPindai" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Selesai') }}</button>
        </div>
    </div>
</div>

{{-- ===== Modal: Sudah kembali ===== --}}
<div v-if="kembali" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="kembali = null">
    <form class="w-full max-w-sm rounded-3xl bg-white shadow-xl" @submit.prevent="simpanKembali(false)">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Sudah kembali') }}</h3>
            <p class="mt-0.5 text-xs text-slate-500"><span v-text="kembali.izin.siswa"></span> · <span v-text="kembali.izin.jenis"></span></p>
            <x-nama-arab vue="kembali.izin.arab" kelas="text-xs text-slate-500" />
        </div>
        <div class="p-6 space-y-3">
            <p class="text-xs text-slate-500">{{ __('Batas kembali') }}: <b class="text-slate-700" v-text="waktuPendek(kembali.izin.selesai_tanggal, kembali.izin.selesai_jam)"></b></p>
            <div class="grid grid-cols-2 gap-2">
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Tanggal kembali') }}</span>
                    <input type="date" v-model="kembali.tanggal" required class="mt-1 {{ $input }}"></label>
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Jam kembali') }}</span>
                    <input type="time" v-model="kembali.jam" required class="mt-1 {{ $input }}"></label>
            </div>
            <p v-if="kembaliTelat" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-xs font-semibold text-rose-700">{{ __('Terlambat — akan masuk catatan pelanggaran.') }}</p>
            <p v-else class="rounded-xl bg-emerald-50 ring-1 ring-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700">{{ __('Tepat waktu.') }}</p>
        </div>
        <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap justify-end gap-2">
            <button v-if="kembali.izin.kembali_jam" type="button" @click="simpanKembali(true)" :disabled="menyimpan" class="mr-auto px-3 py-2.5 rounded-2xl text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Batalkan tanda kembali') }}</button>
            <button type="button" @click="kembali = null" class="px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button :disabled="menyimpan" class="px-4 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 disabled:opacity-50">{{ __('Simpan') }}</button>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const RUTE = {
            jenis: @json(route('admin.penilaian.perizinan.jenis')),
            hapusJenis: @json(route('admin.penilaian.perizinan.jenis.hapus', ['jenis' => '__ID__'])),
            simpan: @json(route('admin.penilaian.perizinan.simpan')),
            kembali: @json(route('admin.penilaian.perizinan.kembali', ['izin' => '__ID__'])),
            hapus: @json(route('admin.penilaian.perizinan.hapus', ['izin' => '__ID__'])),
            pindai: @json(route('admin.penilaian.perizinan.pindai')),
        };
        const CSRF = @json(csrf_token());
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const STATUS = {
            sedang: @js(__('Sedang izin')),
            belum: @js(__('Belum mulai')),
            terlambat: @js(__('Terlambat')),
            tepat: @js(__('Kembali tepat waktu')),
        };
        const T = { hari: @js(__('hari')), jam: @js(__('jam')), menit: @js(__('menit')), lama: @js(__('Lama izin')) };
        const kecil = (t) => (t || '').toString().toLowerCase();

        // Jam WIB dari server, berjalan sendiri di layar (tidak bergantung jam HP)
        const SERVER = new Date(@json($hariIni) + 'T' + @json($jamIni) + ':00Z').getTime();
        const DIMUAT = Date.now();
        const sekarangWib = () => new Date(SERVER + (Date.now() - DIMUAT)).toISOString().slice(0, 16).replace('T', ' ');
        const menit = (tgl, jam) => new Date(tgl + 'T' + jam + ':00Z').getTime() / 60000;
        const lama = (m) => {
            m = Math.max(0, Math.round(m));
            const h = Math.floor(m / 1440), j = Math.floor((m % 1440) / 60), s = m % 60, b = [];
            if (h) b.push(h + ' ' + T.hari);
            if (j) b.push(j + ' ' + T.jam);
            if (s || !b.length) b.push(s + ' ' + T.menit);
            return b.join(' ');
        };

        async function kirim(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const pertama = json.errors ? Object.values(json.errors)[0][0] : null;
                throw new Error(pertama || json.message || @js(__('Gagal menyimpan. Coba lagi.')));
            }
            return json;
        }
        const gagal = (e) => window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);

        // Bunyi "bip" singkat setelah pindai (nada rendah = terlambat / gagal)
        let audio;
        const bip = (frek = 880) => {
            try {
                audio ??= new (window.AudioContext || window.webkitAudioContext)();
                const o = audio.createOscillator(), g = audio.createGain();
                o.frequency.value = frek; g.gain.value = 0.08;
                o.connect(g); g.connect(audio.destination);
                o.start(); o.stop(audio.currentTime + 0.15);
            } catch (e) {}
            navigator.vibrate?.(60);
        };

        Vue.createApp({
            data: () => ({
                jenis: @json($jenis),
                santri: @json($siswa),
                izin: @json($izin),
                hariIni: @json($hariIni),
                jamIni: @json($jamIni),
                detik: sekarangWib(), // diperbarui tiap 30 detik supaya status ikut berubah
                status: '',
                cari: '',
                batas: 50,
                menyimpan: false,
                menghapus: null,
                modalJenis: false,
                modalIzin: false,
                jenisBaru: '',
                cariSantri: '',
                form: {},
                kembali: null, // { izin, tanggal, jam }
                // Pindai Kembali
                modalPindai: false,
                kameraNyala: false,
                memuatKamera: false,
                galatKamera: '',
                mengirim: false,
                kodeManual: '',
                hasil: null,       // { tipe: tepat|terlambat|tidak|galat, santri, message, kunci }
                baruKembali: [],
            }),
            computed: {
                // Status dihitung ulang dari jam sekarang (WIB)
                izinStatus() {
                    const now = this.detik;
                    return this.izin.map((i) => {
                        const batas = i.selesai_tanggal + ' ' + i.selesai_jam;
                        let s;
                        if (i.kembali_jam) s = (i.kembali_tanggal + ' ' + i.kembali_jam) > batas ? 'terlambat' : 'tepat';
                        else if (batas < now) s = 'terlambat';
                        else s = (i.mulai_tanggal + ' ' + i.mulai_jam) > now ? 'belum' : 'sedang';
                        return { ...i, status: s };
                    });
                },
                jumlah() {
                    const hari = this.detik.slice(0, 10);
                    return {
                        sedang: this.izinStatus.filter((i) => i.status === 'sedang').length,
                        terlambat: this.izinStatus.filter((i) => i.status === 'terlambat').length,
                        hariIni: this.izinStatus.filter((i) => i.mulai_tanggal === hari).length,
                    };
                },
                izinCari() {
                    const k = kecil(this.cari.trim());
                    return this.izinStatus.filter((i) => (!this.status || i.status === this.status)
                        && (!k || [i.siswa, i.nomor, i.jenis, i.catatan, i.kelas].some((t) => kecil(t).includes(k))));
                },
                izinTampil() { return this.izinCari.slice(0, this.batas); },
                santriTerpilih() { return this.santri.find((s) => s.siswa_id === this.form.siswa_id) || null; },
                santriCari() {
                    const k = kecil(this.cariSantri.trim());
                    const hasil = k ? this.santri.filter((s) => kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || kecil(s.arab).includes(k)) : this.santri;
                    return hasil.slice(0, 100);
                },
                lamaIzin() {
                    const f = this.form;
                    if (!f.mulai_tanggal || !f.mulai_jam || !f.selesai_tanggal || !f.selesai_jam) return '';
                    const m = menit(f.selesai_tanggal, f.selesai_jam) - menit(f.mulai_tanggal, f.mulai_jam);
                    return m > 0 ? T.lama + ': ' + lama(m) : @js(__('Waktu berakhir harus setelah waktu mulai.'));
                },
                kembaliTelat() {
                    const k = this.kembali;
                    return k && k.tanggal && k.jam && (k.tanggal + ' ' + k.jam) > (k.izin.selesai_tanggal + ' ' + k.izin.selesai_jam);
                },
            },
            mounted() {
                setInterval(() => { this.detik = sekarangWib(); }, 30000);
                window.addEventListener('pagehide', this.matikan);
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                bulan: (t) => BULAN[parseInt(t.slice(5, 7), 10) - 1],
                tanggalPendek: (t) => parseInt(t.slice(8, 10), 10) + ' ' + BULAN[parseInt(t.slice(5, 7), 10) - 1],
                labelStatus: (s) => STATUS[s],
                warna(s) {
                    return {
                        sedang:    { kotak: 'bg-sky-50 ring-sky-100', label: 'bg-sky-50 text-sky-700 ring-sky-200' },
                        belum:     { kotak: 'bg-slate-50 ring-slate-100', label: 'bg-slate-50 text-slate-600 ring-slate-200' },
                        terlambat: { kotak: 'bg-rose-50 ring-rose-100', label: 'bg-rose-50 text-rose-700 ring-rose-200' },
                        tepat:     { kotak: 'bg-emerald-50 ring-emerald-100', label: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
                    }[s];
                },
                // "14 Nov 08:00" (tanggal tidak ditulis kalau sama dengan tanggal pembanding)
                waktuPendek(tgl, jam, sama) { return (tgl === sama ? '' : this.tanggalPendek(tgl) + ' ') + jam; },
                rentang(i) {
                    return i.hari + ', ' + this.tanggalPendek(i.mulai_tanggal) + ' · ' + i.mulai_jam + ' – ' + this.waktuPendek(i.selesai_tanggal, i.selesai_jam, i.mulai_tanggal);
                },
                lamaTelat(i) {
                    const akhir = i.kembali_jam ? menit(i.kembali_tanggal, i.kembali_jam) : menit(this.detik.slice(0, 10), this.detik.slice(11, 16));
                    return lama(akhir - menit(i.selesai_tanggal, i.selesai_jam));
                },
                sedangIzin(id) { return this.izinStatus.some((i) => i.siswa_id === id && (i.status === 'sedang' || (i.status === 'terlambat' && !i.kembali_jam))); },
                bukaIzin() {
                    const sekarang = this.detik;
                    // Jenis terakhir diingat supaya memberi izin beberapa santri berturut-turut lebih cepat
                    this.form = {
                        siswa_id: null, jenis: this.form.jenis || '',
                        mulai_tanggal: sekarang.slice(0, 10), mulai_jam: sekarang.slice(11, 16),
                        selesai_tanggal: sekarang.slice(0, 10), selesai_jam: '',
                        catatan: '',
                    };
                    this.cariSantri = '';
                    this.modalIzin = true;
                },
                // Tanggal berakhir ikut tanggal mulai (kalau belum diubah ke tanggal setelahnya)
                samakanTanggal() {
                    if (!this.form.selesai_tanggal || this.form.selesai_tanggal < this.form.mulai_tanggal) this.form.selesai_tanggal = this.form.mulai_tanggal;
                },
                async tambahJenis() {
                    const nama = this.jenisBaru.trim();
                    if (!nama) return;
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE.jenis, 'POST', { nama });
                        this.jenis.push(json.jenis);
                        this.jenisBaru = '';
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                async hapusJenis(j) {
                    if (!confirm(@js(__('Hapus jenis izin ":nama"? Izin lama yang memakainya tetap ada.')).replace(':nama', j.nama))) return;
                    try {
                        await kirim(RUTE.hapusJenis.replace('__ID__', j.id), 'DELETE');
                        this.jenis = this.jenis.filter((x) => x.id !== j.id);
                    } catch (e) { gagal(e); }
                },
                async simpan() {
                    const f = this.form;
                    if (!f.siswa_id) return gagal(new Error(@js(__('Pilih santri dulu.'))));
                    if (!f.jenis) return gagal(new Error(@js(__('Pilih jenis izin dulu.'))));
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE.simpan, 'POST', f);
                        this.izin.unshift(json.baris);
                        this.izin.sort((a, b) => (b.mulai_tanggal + b.mulai_jam).localeCompare(a.mulai_tanggal + a.mulai_jam) || b.id - a.id);
                        this.modalIzin = false;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                bukaKembali(i) {
                    this.kembali = {
                        izin: i,
                        tanggal: i.kembali_tanggal || this.detik.slice(0, 10),
                        jam: i.kembali_jam || this.detik.slice(11, 16),
                    };
                },
                async simpanKembali(batal) {
                    const k = this.kembali;
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE.kembali.replace('__ID__', k.izin.id), 'PUT', batal ? {} : { tanggal: k.tanggal, jam: k.jam });
                        this.izin = this.izin.map((x) => (x.id === k.izin.id ? json.baris : x));
                        this.kembali = null;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                // ===== Pindai Kembali =====
                bukaPindai() {
                    this.modalPindai = true;
                    this.hasil = null;
                    this.$nextTick(() => this.$refs.kodeInput?.focus()); // alat scanner USB langsung mengetik ke sini
                    this.nyalakan();
                },
                tutupPindai() {
                    this.matikan();
                    this.modalPindai = false;
                },
                async nyalakan() {
                    if (this.kameraNyala || this.memuatKamera) return;
                    this.galatKamera = '';
                    this.memuatKamera = true;
                    try {
                        if (!window.isSecureContext) throw new Error(@js(__('Kamera hanya bisa dipakai di alamat https://.')));
                        const { Html5Qrcode, Html5QrcodeSupportedFormats } = await window.muatPemindaiQr();
                        this._pemindai ??= new Html5Qrcode('pembaca-izin', { formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], verbose: false });
                        await this._pemindai.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.72); return { width: s, height: s }; } },
                            (teks) => this.saatTerbaca(teks),
                            () => {},
                        );
                        this.kameraNyala = true;
                        if (!this.modalPindai) this.matikan(); // jendela sudah ditutup saat kamera masih menyala
                    } catch (e) {
                        const pesan = String(e?.message || e);
                        this.galatKamera = /Permission|NotAllowed/i.test(pesan)
                            ? @js(__('Izin kamera ditolak. Izinkan kamera di pengaturan browser, lalu coba lagi.'))
                            : (/NotFound|Requested device/i.test(pesan) ? @js(__('Kamera tidak ditemukan di perangkat ini.')) : pesan);
                    } finally {
                        this.memuatKamera = false;
                    }
                },
                async matikan() {
                    if (this._pemindai && this.kameraNyala) {
                        try { await this._pemindai.stop(); } catch (e) {}
                    }
                    this.kameraNyala = false;
                },
                // Kartu yang sama tidak dikirim ulang selama 4 detik
                saatTerbaca(teks) {
                    const now = Date.now();
                    if (this.mengirim || (teks === this._kodeTerakhir && now - this._waktuTerakhir < 4000)) return;
                    this._kodeTerakhir = teks;
                    this._waktuTerakhir = now;
                    this.kirimPindai(teks);
                },
                kirimKode() {
                    const kode = this.kodeManual.trim();
                    if (!kode) return;
                    this.kodeManual = '';
                    this.kirimPindai(kode);
                },
                async kirimPindai(kode) {
                    this.mengirim = true;
                    let hasil;
                    try {
                        const json = await kirim(RUTE.pindai, 'POST', { kode });
                        hasil = json;
                        if (json.baris) {
                            this.izin = this.izin.map((x) => (x.id === json.baris.id ? json.baris : x));
                            this.baruKembali = [{ ...json, kunci: Date.now() }, ...this.baruKembali].slice(0, 8);
                        }
                        bip(json.tipe === 'tepat' ? 880 : (json.tipe === 'terlambat' ? 330 : 520));
                    } catch (e) {
                        hasil = { tipe: 'galat', santri: null, message: e.message };
                        bip(220);
                    } finally {
                        this.mengirim = false;
                    }
                    this.hasil = { ...hasil, kunci: Date.now() };
                    clearTimeout(this._tHasil);
                    this._tHasil = setTimeout(() => (this.hasil = null), 3500);
                },
                async hapus(i) {
                    if (!confirm(@js(__('Hapus izin :nama (:jenis)?')).replace(':nama', i.siswa).replace(':jenis', i.jenis))) return;
                    this.menghapus = i.id;
                    try {
                        const json = await kirim(RUTE.hapus.replace('__ID__', i.id), 'DELETE');
                        this.izin = this.izin.filter((x) => x.id !== i.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menghapus = null; }
                },
            },
        }).mount('#halaman-perizinan');
    });
</script>
@endpush
