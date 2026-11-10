{{--
    Admin > Penilaian > Penilaian Dakwah (PenilaianDakwahController@index).
    Dua panel, pindah lewat tombol di atas (alamat ?tab=khitobah|khuruj ikut berubah):
    - Khitoba'        : tombol Pengaturan Nilai (jenis nilai + waktu khitoba') & Beri Nilai
                        (santri -> waktu khitoba' -> tanggal -> nilai -> catatan), lalu rincian hasil.
    - Khurujud Dakwah : tombol Jenis Nilai & Beri Nilai (santri -> tanggal -> nilai -> catatan), lalu rincian hasil.
    Ringkasan gabungan keduanya: Admin > Laporan > Laporan Dakwah.
    Vue: #halaman-penilaian-dakwah.
--}}
@extends('layouts.admin')

@section('title', __('Penilaian Dakwah'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$keterangan = __("Nilai khitoba' dan khurujud dakwah santri. Ringkasannya ada di Laporan > Laporan Dakwah.");
$panel = [
    'khitobah' => [
        'label'     => __("Khitoba'"),
        'ket'       => __("Penilaian latihan pidato (khitoba') santri: waktu, tanggal, nilai, dan catatan."),
        'aksen'     => '#0d9488',
        'tombolAtur' => __('Pengaturan Nilai'),
        'ikon'      => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z',
    ],
    'khuruj' => [
        'label'     => __('Khurujud Dakwah'),
        'ket'       => __('Penilaian khurujud dakwah santri: tanggal, nilai, dan catatan.'),
        'aksen'     => '#7c3aed',
        'tombolAtur' => __('Jenis Nilai'),
        'ikon'      => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46',
    ],
];
@endphp

<div id="halaman-penilaian-dakwah" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="__('Penilaian Dakwah')"
        :keterangan="$keterangan">
        <x-slot:aksi>
            <a href="{{ route('admin.laporan-dakwah.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-white/10 ring-1 ring-white/20 hover:bg-white/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                {{ __('Laporan Dakwah') }}
            </a>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Tombol pindah panel ===== --}}
    <div class="-mx-1 overflow-x-auto px-1 pb-2">
        <div class="inline-flex gap-1.5 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5" role="tablist">
            @foreach ($panel as $kunci => $p)
            <a href="?tab={{ $kunci }}" role="tab" style="--aksen: {{ $p['aksen'] }}"
                :aria-selected="tab === '{{ $kunci }}'"
                @click.prevent="pilihTab('{{ $kunci }}')"
                class="group flex shrink-0 items-center gap-2.5 whitespace-nowrap rounded-xl py-1.5 pl-1.5 pr-3 text-sm font-medium transition-all duration-200"
                :class="tab === '{{ $kunci }}'
                    ? 'bg-[linear-gradient(135deg,#0f4238,#0b332c)] text-white shadow-md shadow-[#0b332c]/25'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110"
                    :class="tab === '{{ $kunci }}'
                        ? 'bg-(--aksen) text-white shadow-[0_4px_12px_-2px_var(--aksen)]'
                        : 'bg-[color-mix(in_srgb,var(--aksen)_14%,white)] text-(--aksen)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['ikon'] }}"/></svg>
                </span>
                {{ $p['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    @foreach ($panel as $kunci => $p)
    <div v-show="tab === '{{ $kunci }}'" @if ($tab !== $kunci) style="display: none" @endif class="space-y-5">
        {{-- ===== Kartu panel + tombol ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6" style="--aksen: {{ $p['aksen'] }}">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <span class="w-14 h-14 shrink-0 rounded-2xl bg-(--aksen) text-white flex items-center justify-center shadow-lg shadow-black/10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['ikon'] }}"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-extrabold text-slate-800">{{ $p['label'] }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $p['ket'] }}</p>
                </div>
                <div class="grid grid-cols-2 sm:flex gap-2">
                    <button type="button" @click="bukaAtur"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                        {{ $p['tombolAtur'] }}
                    </button>
                    <button type="button" @click="bukaNilai"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold text-white bg-(--aksen) hover:opacity-90 shadow-md shadow-black/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('Beri Nilai') }}
                    </button>
                </div>
            </div>

            {{-- Ringkasan kecil --}}
            <div class="mt-5 grid grid-cols-3 gap-2 sm:gap-3">
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3">
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('Jumlah penilaian') }}</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-slate-800 tabular-nums" v-text="nilaiTab.length"></p>
                </div>
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3">
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('Santri dinilai') }}</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-slate-800 tabular-nums" v-text="jumlahSantriTab"></p>
                </div>
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3">
                    <p class="text-[11px] font-semibold text-slate-500">{{ __('Hari ini') }}</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-(--aksen) tabular-nums" v-text="nilaiTab.filter((n) => n.tanggal === hariIni).length"></p>
                </div>
            </div>
        </section>

        {{-- ===== Rincian hasil ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <h3 class="flex-1 text-base font-extrabold text-slate-800">{{ __('Rincian hasil') }}</h3>
                <input type="search" v-model="cari" placeholder="{{ __('Cari santri, nilai, catatan...') }}" class="sm:max-w-xs {{ $input }}">
            </div>

            <ul class="mt-3 divide-y divide-slate-100">
                <li v-for="n in nilaiTampil" :key="n.id" class="py-3.5 flex items-start gap-3">
                    <span class="w-12 shrink-0 rounded-2xl py-1.5 text-center bg-[#f8fbfa] ring-1 ring-slate-100">
                        <span class="block text-lg font-extrabold leading-none text-slate-800" v-text="n.tanggal.slice(8, 10)"></span>
                        <span class="block mt-0.5 text-[10px] font-semibold uppercase text-slate-400" v-text="bulan(n.tanggal)"></span>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="font-semibold text-slate-800" v-text="n.siswa"></span>
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
                    <button type="button" @click="hapusNilai(n)" :disabled="menghapus === n.id"
                        title="{{ __('Hapus nilai') }}" aria-label="{{ __('Hapus nilai') }}"
                        class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </li>
            </ul>

            <p v-if="!nilaiCari.length" class="mt-6 text-center text-sm text-slate-400">{{ __('Belum ada nilai. Tekan "Beri Nilai" untuk mulai menilai.') }}</p>
            <div v-if="nilaiCari.length > batas" class="mt-4 text-center">
                <button type="button" @click="batas += 50" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    {{ __('Tampilkan lebih banyak') }}
                </button>
            </div>
        </section>
    </div>
    @endforeach
</div>

{{-- ===== Modal: Pengaturan Nilai / Jenis Nilai ===== --}}
<div v-if="modalAtur" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalAtur = false">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">
                <template v-if="tab === 'khitobah'">{{ __('Pengaturan Nilai') }} — {{ __("Khitoba'") }}</template>
                <template v-else>{{ __('Jenis Nilai') }} — {{ __('Khurujud Dakwah') }}</template>
            </h3>
            <button type="button" @click="modalAtur = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-6">
            <div v-for="k in kelompokTab" :key="k.kunci">
                <p class="text-sm font-bold text-slate-700" v-text="k.label"></p>
                <p class="text-xs text-slate-400" v-text="k.ket"></p>
                <div class="mt-2.5 flex flex-wrap gap-2">
                    <span v-for="p in pilihan[k.kunci]" :key="p.id" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 pl-3 pr-1 py-1 text-sm font-semibold">
                        <span v-text="p.nama"></span>
                        <button type="button" @click="hapusPilihan(k.kunci, p)" class="w-6 h-6 rounded-full hover:bg-emerald-100 flex items-center justify-center" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                    <span v-if="!pilihan[k.kunci].length" class="text-xs text-slate-400 py-1.5">{{ __('Belum ada. Tulis di bawah lalu tekan +.') }}</span>
                </div>
                <form class="mt-3 flex gap-2" @submit.prevent="tambahPilihan(k.kunci)">
                    <input type="text" v-model="baru[k.kunci]" maxlength="100" :placeholder="k.contoh" class="{{ $input }}">
                    <button :disabled="menyimpan" class="shrink-0 w-11 h-11 rounded-xl bg-[#0b332c] text-white hover:bg-[#135347] flex items-center justify-center disabled:opacity-50" title="{{ __('Tambah') }}" aria-label="{{ __('Tambah') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-4 text-right">
            <button type="button" @click="modalAtur = false" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Selesai') }}</button>
        </div>
    </div>
</div>

{{-- ===== Modal: Beri Nilai ===== --}}
<div v-if="modalNilai" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalNilai = false">
    <form class="w-full max-w-lg rounded-3xl bg-white shadow-xl" @submit.prevent="simpanNilai">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">
                {{ __('Beri Nilai') }} —
                <template v-if="tab === 'khitobah'">{{ __("Khitoba'") }}</template>
                <template v-else>{{ __('Khurujud Dakwah') }}</template>
            </h3>
            <button type="button" @click="modalNilai = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
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
                        <span class="block text-xs text-slate-500 truncate"><span v-text="santriTerpilih.nomor"></span><template v-if="santriTerpilih.kelas"> · <span v-text="santriTerpilih.kelas"></span></template></span>
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
                                    <span class="block text-[11px] text-slate-400 truncate"><span v-text="s.nomor"></span><template v-if="s.kelas"> · <span v-text="s.kelas"></span></template></span>
                                </span>
                            </button>
                        </li>
                        <li v-if="!santriCari.length" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('Santri tidak ditemukan.') }}</li>
                    </ul>
                </template>
            </div>

            {{-- 2. Waktu khitoba' (khusus Khitoba') --}}
            <div v-if="tab === 'khitobah'">
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">2.</span> {{ __("Waktu khitoba'") }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="p in pilihan.waktu_khitobah" :key="p.id" type="button" @click="form.waktu = p.nama"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="form.waktu === p.nama ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        v-text="p.nama"></button>
                </div>
                <p v-if="!pilihan.waktu_khitobah.length" class="mt-1 text-xs text-amber-600">{{ __("Belum ada waktu khitoba'. Tambahkan dulu lewat tombol Pengaturan Nilai.") }}</p>
            </div>

            {{-- 3. Tanggal --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400" v-text="tab === 'khitobah' ? '3.' : '2.'"></span> {{ __('Tanggal') }}</p>
                <input type="date" v-model="form.tanggal" required class="mt-2 {{ $input }}">
            </div>

            {{-- 4. Nilai --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400" v-text="tab === 'khitobah' ? '4.' : '3.'"></span> {{ __('Nilai') }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="p in pilihanNilaiTab" :key="p.id" type="button" @click="form.nilai = p.nama"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="form.nilai === p.nama ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        v-text="p.nama"></button>
                </div>
                <p v-if="!pilihanNilaiTab.length" class="mt-1 text-xs text-amber-600">
                    <template v-if="tab === 'khitobah'">{{ __('Belum ada jenis nilai. Tambahkan dulu lewat tombol Pengaturan Nilai.') }}</template>
                    <template v-else>{{ __('Belum ada jenis nilai. Tambahkan dulu lewat tombol Jenis Nilai.') }}</template>
                </p>
            </div>

            {{-- 5. Catatan --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400" v-text="tab === 'khitobah' ? '5.' : '4.'"></span> {{ __('Catatan') }} <span class="font-normal text-slate-400">({{ __('boleh dikosongkan') }})</span></p>
                <textarea v-model="form.catatan" rows="3" maxlength="5000" placeholder="{{ __('Tulis catatan untuk santri ini...') }}" class="mt-2 {{ $input }}"></textarea>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
            <button type="button" @click="modalNilai = false" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                <template v-else>{{ __('Simpan Nilai') }}</template>
            </button>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const RUTE = {
            pilihan: @json(route('admin.penilaian.dakwah.pilihan')),
            hapusPilihan: @json(route('admin.penilaian.dakwah.pilihan.hapus', ['pilihan' => '__ID__'])),
            nilai: @json(route('admin.penilaian.dakwah.nilai')),
            hapusNilai: @json(route('admin.penilaian.dakwah.nilai.hapus', ['nilai' => '__ID__'])),
        };
        const CSRF = @json(csrf_token());
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        // Isi modal pengaturan per panel
        const KELOMPOK = {
            khitobah: [
                { kunci: 'nilai_khitobah', label: @js(__("Jenis nilai Khitoba'")), ket: @js(__('Pilihan nilai saat menilai, mis. Sempurna, Baik.')), contoh: @js(__('mis. Sempurna')) },
                { kunci: 'waktu_khitobah', label: @js(__("Waktu Khitoba'")), ket: @js(__("Nama waktu khitoba', mis. Ba'da Subuh.")), contoh: @js(__("mis. Ba'da Subuh")) },
            ],
            khuruj: [
                { kunci: 'nilai_khuruj', label: @js(__('Jenis nilai Khurujud Dakwah')), ket: @js(__('Pilihan nilai saat menilai, mis. Sempurna, Baik.')), contoh: @js(__('mis. Sempurna')) },
            ],
        };

        // Kirim ke server (JSON); pesan error validasi pertama dilempar sebagai Error
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

        Vue.createApp({
            data: () => ({
                tab: @json($tab),
                pilihan: @json($pilihan),
                santri: @json($siswa),
                nilai: @json($nilai),
                hariIni: @json($hariIni),
                cari: '',
                batas: 50,
                menghapus: null,
                menyimpan: false,
                modalAtur: false,
                modalNilai: false,
                baru: { nilai_khitobah: '', waktu_khitobah: '', nilai_khuruj: '' },
                cariSantri: '',
                form: { siswa_id: null, waktu: '', tanggal: '', nilai: '', catatan: '' },
            }),
            computed: {
                kelompokTab() { return KELOMPOK[this.tab]; },
                pilihanNilaiTab() { return this.pilihan[this.tab === 'khitobah' ? 'nilai_khitobah' : 'nilai_khuruj']; },
                nilaiTab() { return this.nilai.filter((n) => n.jenis === this.tab); },
                jumlahSantriTab() { return new Set(this.nilaiTab.map((n) => n.siswa_id)).size; },
                nilaiCari() {
                    const k = kecil(this.cari.trim());
                    return k ? this.nilaiTab.filter((n) => [n.siswa, n.nomor, n.nilai, n.waktu, n.catatan, n.kelas].some((t) => kecil(t).includes(k))) : this.nilaiTab;
                },
                nilaiTampil() { return this.nilaiCari.slice(0, this.batas); },
                santriTerpilih() { return this.santri.find((s) => s.siswa_id === this.form.siswa_id) || null; },
                santriCari() {
                    const k = kecil(this.cariSantri.trim());
                    const hasil = k ? this.santri.filter((s) => kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || kecil(s.arab).includes(k)) : this.santri;
                    return hasil.slice(0, 100);
                },
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                bulan: (t) => BULAN[parseInt(t.slice(5, 7), 10) - 1],
                tanggalPanjang: (t) => parseInt(t.slice(8, 10), 10) + ' ' + BULAN[parseInt(t.slice(5, 7), 10) - 1] + ' ' + t.slice(0, 4),
                // Pindah panel tanpa memuat ulang; alamat ?tab=... ikut berubah
                pilihTab(kunci) {
                    this.tab = kunci;
                    this.cari = '';
                    this.batas = 50;
                    const u = new URL(location.href);
                    u.searchParams.set('tab', kunci);
                    history.replaceState(null, '', u);
                },
                bukaAtur() { this.modalAtur = true; },
                bukaNilai() {
                    // Tanggal & waktu terakhir tetap diingat supaya menilai beberapa santri berturut-turut lebih cepat
                    this.form = { siswa_id: null, waktu: this.form.waktu, tanggal: this.form.tanggal || this.hariIni, nilai: '', catatan: '' };
                    this.cariSantri = '';
                    this.modalNilai = true;
                },
                async tambahPilihan(kelompok) {
                    const nama = this.baru[kelompok].trim();
                    if (!nama) return;
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE.pilihan, 'POST', { kelompok, nama });
                        this.pilihan[kelompok].push(json.pilihan);
                        this.baru[kelompok] = '';
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                async hapusPilihan(kelompok, p) {
                    if (!confirm(@js(__('Hapus pilihan ":nama"? Nilai lama yang memakainya tetap ada.')).replace(':nama', p.nama))) return;
                    try {
                        await kirim(RUTE.hapusPilihan.replace('__ID__', p.id), 'DELETE');
                        this.pilihan[kelompok] = this.pilihan[kelompok].filter((x) => x.id !== p.id);
                    } catch (e) { gagal(e); }
                },
                async simpanNilai() {
                    const f = this.form;
                    if (!f.siswa_id) return gagal(new Error(@js(__('Pilih santri dulu.'))));
                    if (this.tab === 'khitobah' && !f.waktu) return gagal(new Error(@js(__("Pilih waktu khitoba' dulu."))));
                    if (!f.nilai) return gagal(new Error(@js(__('Pilih nilai dulu.'))));
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE.nilai, 'POST', { ...f, jenis: this.tab });
                        this.nilai.unshift(json.baris);
                        this.nilai.sort((a, b) => b.tanggal.localeCompare(a.tanggal) || b.id - a.id);
                        this.modalNilai = false;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                async hapusNilai(n) {
                    if (!confirm(@js(__('Hapus nilai :nama (:nilai, :tanggal)?')).replace(':nama', n.siswa).replace(':nilai', n.nilai).replace(':tanggal', n.tanggal))) return;
                    this.menghapus = n.id;
                    try {
                        const json = await kirim(RUTE.hapusNilai.replace('__ID__', n.id), 'DELETE');
                        this.nilai = this.nilai.filter((x) => x.id !== n.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menghapus = null; }
                },
            },
        }).mount('#halaman-penilaian-dakwah');
    });
</script>
@endpush
