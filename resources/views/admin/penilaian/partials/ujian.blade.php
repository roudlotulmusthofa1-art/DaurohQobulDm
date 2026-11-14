{{--
    Admin > Penilaian > Kelas & Pelajaran > Ujian (dimuat dari admin/penilaian/kelas.blade.php, di dalam Vue #halaman-penilaian-kelas).
    - Tombol "Pengaturan Ujian": bulan ajaran (boleh 1, 2, 3 bulan; nama ujian otomatis dari bulannya), fan,
      kitab yang diujikan (boleh beberapa), nilai maksimal, cara pembuatan soal, aktif.
    - Saringan BULAN (di atas daftar ujian) berlaku untuk daftar ujian, panel Nilai, dan panel Soal Ujian.
    - Dua panel (tombol di bawah daftar ujian; alamat ?panel=nilai|soal):
      1) Nilai      : ringkasan per kitab, Rekap per santri, Semua nilai (ubah / hapus). Nilai diisi asatid.
      2) Soal Ujian : soal kiriman asatid, diperiksa / diedit lalu disetujui admin (isi di partials/ujian-soal.blade.php).
    Data: $ujianTulis (Ujian::daftar), $nilaiUjian (NilaiUjian::baris), $soalUjian (SoalUjian::baris), $kelasList, $panelUjian, $bulanUjian.
    Vue: data/computed/methods ada di window.UJIAN_TULIS (bawah file ini), dipasang sebagai "mixins" di kelas.blade.php.
    Semua nama di sini diawali "ut" supaya tidak bentrok dengan Nilai Harian / Ujian Lisan.
--}}
@php
$inputUt = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none';
$tutupIkonUt = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

<div v-show="tab === 'ujian'" @if ($tab !== 'ujian') style="display: none" @endif class="space-y-5">
    {{-- ===== Tombol + daftar ujian ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[12rem]">
                <h2 class="font-extrabold text-slate-800">{{ __('Ujian') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Ujian tulis per fan & kitab. Soal dibuat asatid lalu diperiksa admin; nilai diisi asatid.') }}</p>
            </div>
            <button type="button" @click="utBukaAtur()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                {{ __('Pengaturan Ujian') }}
            </button>
        </div>

        {{-- Saringan bulan: ujian, nilai & soal yang tampil hanya dari ujian yang mencakup bulan ini --}}
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">{{ __('Bulan') }}:</span>
            <button type="button" @click="utPilihBulan('')" class="rounded-full px-3 py-1 text-xs font-bold ring-1"
                :class="utSaring.bulan === '' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Semua bulan') }}</button>
            <button v-for="b in utBulanDipakai" :key="b.id" type="button" @click="utPilihBulan(b.id)" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1"
                :class="utSaring.bulan === b.id ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-if="b.aktif" class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span v-text="b.nama"></span>
            </button>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button v-for="u in utUjianBulan" :key="u.id" type="button" @click="utSaring.ujian = u.id"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                :class="utSaring.ujian === u.id ? 'bg-amber-500 text-white ring-amber-500' : (u.is_active ? 'bg-amber-50 text-amber-800 ring-amber-200' : 'bg-slate-50 text-slate-400 ring-slate-200 line-through')">
                <span v-text="u.judul"></span>
                <span class="opacity-70" v-text="'· ' + u.kitab.length + ' ' + utTeks.kitab"></span>
            </button>
            <p v-if="!utUjian.length" class="w-full p-3 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada ujian. Tambahkan dulu lewat tombol Pengaturan Ujian.') }}</p>
            <p v-else-if="!utUjianBulan.length" class="w-full p-3 rounded-2xl bg-slate-50 ring-1 ring-slate-200 text-sm text-slate-500">{{ __('Belum ada ujian di bulan ini.') }}</p>
        </div>
    </section>

    {{-- ===== Tombol panel: Nilai | Soal Ujian ===== --}}
    <div class="grid grid-cols-2 gap-3">
        <button type="button" @click="utPilihPanel('nilai')" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold ring-1 transition"
            :class="utPanel === 'nilai' ? 'bg-[#0b332c] text-white ring-[#0b332c] shadow-md shadow-[#0b332c]/25' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            {{ __('Nilai') }}
        </button>
        <button type="button" @click="utPilihPanel('soal')" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold ring-1 transition"
            :class="utPanel === 'soal' ? 'bg-[#0b332c] text-white ring-[#0b332c] shadow-md shadow-[#0b332c]/25' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            {{ __('Soal Ujian') }}
            <span v-if="usJumlah('menunggu')" class="rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-extrabold text-[#0b332c]" v-text="usJumlah('menunggu') + ' ' + utTeks.menunggu"></span>
        </button>
    </div>

    {{-- ================= Panel 1: Nilai ================= --}}
    <div v-show="utPanel === 'nilai'" @if ($panelUjian !== 'nilai') style="display: none" @endif class="space-y-5">
    {{-- ===== Saringan ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Ujian') }}</span>
                <select v-model="utSaring.ujian" class="mt-1 {{ $inputUt }}">
                    <option value="">{{ __('Semua ujian') }}</option>
                    <option v-for="u in utUjianBulan" :key="u.id" :value="u.id" v-text="u.judul"></option>
                </select></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Halaqoh') }}</span>
                <select v-model="utSaring.kelas" class="mt-1 {{ $inputUt }}">
                    <option value="">{{ __('Semua halaqoh') }}</option>
                    <option v-for="k in utKelas" :key="k.id" :value="k.id" v-text="k.nama"></option>
                </select></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
                <input type="search" v-model="utSaring.cari" placeholder="{{ __('Nama / nomor santri / kitab…') }}" class="mt-1 {{ $inputUt }}"></label>
        </div>
    </section>

    {{-- ===== Ringkasan per kitab (ujian terpilih) ===== --}}
    <div v-if="utUjianTerpilih" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <section v-for="k in utRingkasan" :key="k.kunci" class="{{ $kartu }} p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-600" v-text="utUjianTerpilih.fan"></p>
            <h3 class="mt-0.5 font-extrabold text-slate-800" v-text="k.nama"></h3>
            <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-2.5"><dt class="text-[11px] text-slate-500">{{ __('Santri dinilai') }}</dt><dd class="text-lg font-extrabold text-slate-800" v-text="k.jumlah"></dd></div>
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-2.5"><dt class="text-[11px] text-slate-500">{{ __('Rata-rata') }}</dt><dd class="text-lg font-extrabold text-slate-800" v-text="k.jumlah ? bulat(k.rata) : '–'"></dd></div>
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-2.5"><dt class="text-[11px] text-slate-500">{{ __('Tertinggi') }}</dt><dd class="text-lg font-extrabold text-emerald-700" v-text="k.jumlah ? k.tinggi : '–'"></dd></div>
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-2.5"><dt class="text-[11px] text-slate-500">{{ __('Terendah') }}</dt><dd class="text-lg font-extrabold text-rose-600" v-text="k.jumlah ? k.rendah : '–'"></dd></div>
            </dl>
            <p class="mt-3 text-xs text-slate-500">
                {{ __('Soal') }}: <b class="text-emerald-700" v-text="k.soalSetuju"></b> {{ __('disetujui') }}
                · <b class="text-amber-600" v-text="k.soalTunggu"></b> {{ __('menunggu') }}
                · {{ __('nilai maks') }} <b v-text="utUjianTerpilih.nilai_maks"></b>
            </p>
        </section>
    </div>

    {{-- ===== Rekap per santri / Semua nilai ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-2">
            <button v-if="utUjianTerpilih" type="button" @click="utTampil = 'rekap'" class="px-3.5 py-2 rounded-xl text-sm font-bold ring-1"
                :class="utTampil === 'rekap' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Rekap per santri') }}</button>
            <button type="button" @click="utTampil = 'daftar'" class="px-3.5 py-2 rounded-xl text-sm font-bold ring-1"
                :class="utTampil === 'daftar' || !utUjianTerpilih ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Semua nilai') }}</button>
            <span class="ml-auto rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><span v-text="utNilaiTersaring.length"></span> {{ __('nilai') }}</span>
        </div>

        {{-- Rekap: satu baris per santri, satu kolom per kitab --}}
        <div v-if="utUjianTerpilih && utTampil === 'rekap'" class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                        <th class="py-2 pr-3 font-semibold">{{ __('Santri') }}</th>
                        <th v-for="g in utUjianTerpilih.kelompok" :key="g.kunci" class="py-2 px-2 font-semibold text-center" v-text="g.nama"></th>
                        <th class="py-2 pl-2 font-semibold text-center">{{ __('Rata-rata') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="r in utRekap" :key="r.siswa_id">
                        <td class="py-2.5 pr-3">
                            <span class="block font-semibold text-slate-800" v-text="r.siswa"></span>
                            <x-nama-arab vue="r.arab" />
                            <span v-if="r.halaqoh" class="block text-xs text-slate-400" v-text="r.halaqoh"></span>
                        </td>
                        <td v-for="g in utUjianTerpilih.kelompok" :key="g.kunci" class="py-2.5 px-2 text-center tabular-nums">
                            <span v-if="r.nilai[g.kunci] !== undefined" class="font-bold text-slate-700" v-text="r.nilai[g.kunci]"></span>
                            <span v-else class="text-slate-300">–</span>
                        </td>
                        <td class="py-2.5 pl-2 text-center">
                            <span class="inline-block min-w-[3rem] rounded-xl px-2.5 py-1 font-extrabold tabular-nums ring-1" :class="warnaTotal(r.persen)" v-text="bulat(r.rata)"></span>
                        </td>
                    </tr>
                    <tr v-if="!utRekap.length"><td :colspan="utUjianTerpilih.kelompok.length + 2" class="py-10 text-center text-sm text-slate-400">{{ __('Belum ada nilai untuk ujian ini.') }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Semua nilai --}}
        <ul v-else class="mt-3 divide-y divide-slate-100">
            <li v-for="n in utNilaiTersaring" :key="n.id" class="py-3 flex flex-wrap items-start gap-3">
                <img v-if="n.foto" :src="n.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(n.siswa)"></span>
                <div class="flex-1 min-w-[12rem]">
                    <p class="font-semibold text-slate-800">
                        <span v-text="n.siswa"></span>
                        <span v-if="n.halaqoh" class="ml-1 text-xs font-normal text-slate-400" v-text="n.halaqoh"></span>
                    </p>
                    <x-nama-arab vue="n.arab" />
                    <p class="text-xs text-slate-500">
                        <span class="font-semibold text-amber-700" v-text="n.kitab || '-'"></span>
                        <template v-if="n.fan || n.nama_ujian"> · <span v-text="[n.nama_ujian, n.fan].filter(Boolean).join(' · ')"></span></template>
                        · <span v-text="tglPanjang(n.tanggal)"></span>
                        <template v-if="n.oleh"> · {{ __('oleh') }} <span v-text="n.oleh"></span></template>
                    </p>
                    <x-nama-arab vue="n.oleh_arab" kelas="text-xs text-slate-400" />
                </div>
                <div class="flex items-center gap-1">
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="warnaTotal(n.persen)" v-text="n.nilai + '/' + n.nilai_maks"></span>
                    <button type="button" @click="utBukaUbah(n)" title="{{ __('Ubah nilai') }}" aria-label="{{ __('Ubah nilai') }}"
                        class="w-9 h-9 rounded-xl text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </button>
                    <button type="button" @click="utHapusNilai(n)" :disabled="utMenghapus === n.id" title="{{ __('Hapus nilai') }}" aria-label="{{ __('Hapus nilai') }}"
                        class="w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </div>
            </li>
            <li v-if="!utNilaiTersaring.length" class="py-10 text-center text-sm text-slate-400">{{ __('Belum ada nilai ujian. Nilai diisi asatid di menu Penilaian > Ujian.') }}</li>
        </ul>
    </section>
    </div>

    {{-- ================= Panel 2: Soal Ujian ================= --}}
    @include('admin.penilaian.partials.ujian-soal')

    {{-- ===== Modal: Pengaturan Ujian ===== --}}
    <div v-if="utModalAtur" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="utModalAtur = false">
        <div class="w-full max-w-2xl rounded-3xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-extrabold text-slate-800">{{ __('Pengaturan Ujian') }}</h3>
                <button type="button" @click="utModalAtur = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutupIkonUt !!}</button>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <p class="text-sm font-bold text-slate-700">{{ __('Daftar ujian') }}</p>
                    <ul class="mt-2 divide-y divide-slate-100 rounded-2xl ring-1 ring-slate-100">
                        <li v-for="u in utUjian" :key="u.id" class="flex flex-wrap items-center gap-2 px-3 py-2.5">
                            <div class="flex-1 min-w-[10rem]">
                                <p class="text-sm font-semibold" :class="u.is_active ? 'text-slate-800' : 'text-slate-400 line-through'" v-text="u.judul"></p>
                                <p class="text-xs text-slate-400">
                                    <span v-text="u.kitab.map((k) => k.nama).join(', ')"></span>
                                    · {{ __('maks') }} <span v-text="u.nilai_maks"></span>
                                    · <span v-text="u.jumlah_soal"></span> {{ __('soal') }} · <span v-text="u.jumlah_nilai"></span> {{ __('nilai') }}
                                </p>
                                <p class="text-xs text-amber-700">{{ __('Soal') }}: <span v-text="u.kelompok.map((g) => g.nama).join(' · ')"></span></p>
                            </div>
                            <button type="button" @click="utAktifkan(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold ring-1"
                                :class="u.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-500 ring-slate-200'">
                                <template v-if="u.is_active">{{ __('Aktif') }}</template><template v-else>{{ __('Mati') }}</template>
                            </button>
                            <button type="button" @click="utUbahUjian(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-amber-700 hover:bg-amber-50">{{ __('Ubah') }}</button>
                            <button type="button" @click="utHapusUjian(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                        </li>
                        <li v-if="!utUjian.length" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('Belum ada ujian. Isi form di bawah.') }}</li>
                    </ul>
                </div>

                <form class="rounded-2xl bg-amber-50/50 ring-1 ring-amber-100 p-4 space-y-4" @submit.prevent="utSimpanUjian">
                    <p class="text-sm font-bold text-amber-800">
                        <template v-if="utForm.id">{{ __('Ubah ujian') }}</template><template v-else>{{ __('Tambah ujian') }}</template>
                    </p>
                    {{-- Bulan ujian (boleh lebih dari satu) -> nama ujian otomatis --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Bulan ujian (boleh lebih dari satu)') }}</p>
                        <div v-for="t in utBulanPerTa" :key="t.ta" class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span v-if="t.ta" class="text-[11px] font-semibold text-slate-400" v-text="utTeks.ta + ' ' + t.ta"></span>
                            <label v-for="b in t.bulan" :key="b.id" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm ring-1 cursor-pointer"
                                :class="utForm.bulan_ids.includes(b.id) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                                <input type="checkbox" :value="b.id" v-model="utForm.bulan_ids" class="sr-only">
                                <span v-if="b.aktif" class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span v-text="b.nama"></span>
                            </label>
                        </div>
                        <p v-if="!utBulan.length" class="mt-1 text-xs text-amber-700">{{ __('Belum ada bulan ajaran. Tambahkan di Akademik > Tahun & Bulan Ajaran.') }}</p>
                        <p class="mt-1.5 text-xs text-amber-800">{{ __('Nama ujian') }}:
                            <b v-if="utNamaOtomatis" v-text="utNamaOtomatis"></b>
                            <span v-else-if="utForm.nama" class="text-slate-500"><span v-text="utForm.nama"></span> ({{ __('nama lama; centang bulan untuk menggantinya') }})</span>
                            <span v-else class="text-slate-400">{{ __('centang bulan dulu') }}</span>
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Fan / kategori') }}</span>
                            <select v-model="utForm.bidang_ilmu_id" @change="utForm.kitab_ids = []" required class="mt-1 {{ $inputUt }}">
                                <option value="">{{ __('— Pilih fan —') }}</option>
                                <option v-for="f in fanList" :key="f.id" :value="f.id" v-text="f.nama"></option>
                            </select></label>
                        <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Nilai maksimal') }}</span>
                            <input type="number" min="1" max="{{ \App\Models\Ujian::MAKS_NILAI }}" v-model.number="utForm.nilai_maks" required class="mt-1 {{ $inputUt }}"></label>
                    </div>
                    <div v-if="utForm.bidang_ilmu_id">
                        <p class="text-xs font-semibold text-slate-500">{{ __('Kitab yang diujikan (boleh lebih dari satu)') }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-2">
                            <label v-for="k in utKitabDiFan" :key="k.id" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm ring-1 cursor-pointer"
                                :class="utForm.kitab_ids.includes(k.id) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                                <input type="checkbox" :value="k.id" v-model="utForm.kitab_ids" class="sr-only">
                                <span v-text="k.nama"></span>
                            </label>
                            <p v-if="!utKitabDiFan.length" class="text-xs text-amber-700">{{ __('Fan ini belum punya kitab. Tambahkan di Kurikulum & Kitab.') }}</p>
                        </div>
                    </div>

                    {{-- Cara pembuatan soal: menentukan pilihan kitab asatid di halaman Buat Soal --}}
                    <div v-if="utForm.kitab_ids.length">
                        <p class="text-xs font-semibold text-slate-500">{{ __('Cara pembuatan soal oleh asatid') }}</p>
                        <div class="mt-1.5 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label v-for="c in utCaraSoal" :key="c.kunci" class="flex items-start gap-2 rounded-xl px-3 py-2.5 text-sm ring-1 cursor-pointer"
                                :class="utForm.cara_soal === c.kunci ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                                <input type="radio" :value="c.kunci" v-model="utForm.cara_soal" class="sr-only">
                                <span><b class="block" v-text="c.label"></b><span class="text-xs opacity-80" v-text="c.ket"></span></span>
                            </label>
                        </div>
                        <div v-if="utForm.cara_soal === 'kelompok'" class="mt-3 rounded-xl bg-white ring-1 ring-amber-100 p-3 space-y-2">
                            <p class="text-xs text-slate-500">{{ __('Masukkan setiap kitab ke satu kelompok. Kitab dengan kelompok yang sama dibuat dalam satu soal.') }}</p>
                            <label v-for="k in utKitabDipilih" :key="k.id" class="flex items-center gap-3 text-sm">
                                <span class="flex-1 font-semibold text-slate-700" v-text="k.nama"></span>
                                <select :value="utForm.grup[k.id] || 1" @change="utForm.grup[k.id] = Number($event.target.value)" class="w-40 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option v-for="n in utKitabDipilih.length" :key="n" :value="n" v-text="utTeks.kelompok + ' ' + n"></option>
                                </select>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-amber-800">{{ __('Pilihan soal untuk asatid') }}: <b v-text="utPratinjau.join(' · ')"></b></p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" v-model="utForm.is_active" class="rounded text-amber-600">
                        {{ __('Aktif (muncul di dasbor asatid)') }}
                    </label>
                    <div class="flex justify-end gap-2">
                        <button v-if="utForm.id" type="button" @click="utFormBaru()" class="px-4 py-2 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal ubah') }}</button>
                        <button :disabled="utMenyimpan" class="px-5 py-2 rounded-2xl bg-amber-600 text-white text-sm font-bold hover:bg-amber-700 disabled:opacity-50">
                            <template v-if="utMenyimpan">{{ __('Menyimpan...') }}</template>
                            <template v-else-if="utForm.id">{{ __('Simpan perubahan') }}</template>
                            <template v-else>{{ __('Tambah ujian') }}</template>
                        </button>
                    </div>
                </form>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 text-right">
                <button type="button" @click="utModalAtur = false" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Selesai') }}</button>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Ubah nilai ujian ===== --}}
    <div v-if="utUbah" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="utUbah = null">
        <form class="w-full max-w-md rounded-3xl bg-white shadow-xl" @submit.prevent="utSimpanUbah">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="min-w-0">
                    <h3 class="text-base font-extrabold text-slate-800">{{ __('Ubah Nilai Ujian') }}</h3>
                    <p class="text-xs text-slate-500 truncate" v-text="utUbah.siswa + ' · ' + (utUbah.kitab || '-')"></p>
                </div>
                <button type="button" @click="utUbah = null" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutupIkonUt !!}</button>
            </div>
            <div class="px-6 py-5 grid grid-cols-2 gap-3">
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Nilai') }} <span class="text-slate-400" v-text="'(0–' + utUbah.nilai_maks + ')'"></span></span>
                    <input type="number" min="0" :max="utUbah.nilai_maks" v-model.number="utUbah.nilai" required class="mt-1 {{ $inputUt }} text-lg font-bold text-center"></label>
                <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                    <input type="date" v-model="utUbah.tanggal" required class="mt-1 {{ $inputUt }}"></label>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                <button type="button" @click="utUbah = null" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button :disabled="utMenyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                    <template v-if="utMenyimpan">{{ __('Menyimpan...') }}</template>
                    <template v-else>{{ __('Simpan Perubahan') }}</template>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tab Ujian: dipasang sebagai mixins di aplikasi Vue halaman Kelas & Pelajaran (kelas.blade.php)
    window.UJIAN_TULIS = (function () {
        const RUTE = {
            ujian: @json(route('admin.penilaian.ujian.simpan')),
            hapusUjian: @json(route('admin.penilaian.ujian.hapus', ['ujian' => '__ID__'])),
            ubahNilai: @json(route('admin.penilaian.nilai-ujian.ubah', ['nilai' => '__ID__'])),
            hapusNilai: @json(route('admin.penilaian.nilai-ujian.hapus', ['nilai' => '__ID__'])),
        };
        const kecil = (t) => (t || '').toString().toLowerCase();
        async function kirim(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || @js(__('Gagal menyimpan. Coba lagi.')));
            return json;
        }
        const gagal = (e) => (window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message));
        const BULAN = @json($bulanUjian);   // [{id, nama, ta, aktif}] urut tahun ajaran & urutan
        const BULAN_AKTIF = (BULAN.find((b) => b.aktif) || {}).id || null;
        const formKosong = () => ({ id: null, nama: '', bulan_ids: BULAN_AKTIF ? [BULAN_AKTIF] : [], bidang_ilmu_id: '', kitab_ids: [], nilai_maks: 100, cara_soal: 'per_kitab', grup: {}, is_active: true });
        // "Atur kelompok": {kitab_id: nomor kelompok} -> [[2, 3], [5]] (urut nomor kelompok)
        const kelompokDari = (kitabIds, grup) => {
            const per = {};
            kitabIds.forEach((id) => { (per[grup[id] || 1] ||= []).push(id); });
            return Object.keys(per).sort((a, b) => a - b).map((n) => per[n]);
        };
        const UJIAN = @json($ujianTulis);
        // Saringan bulan awal: bulan aktif kalau ada ujiannya, kalau tidak "Semua bulan"
        const BULAN_AWAL = BULAN_AKTIF && UJIAN.some((u) => u.bulan_ids.includes(BULAN_AKTIF)) ? BULAN_AKTIF : '';
        const UJIAN_AWAL = UJIAN.filter((u) => !BULAN_AWAL || u.bulan_ids.includes(BULAN_AWAL));

        return {
            data: () => ({
                utUjian: UJIAN,
                utNilai: @json($nilaiUjian),
                utSoal: @json($soalUjian),
                utKelas: @json($kelasList),
                utTeks: { ta: @js(__('TA')), kitab: @js(__('kitab')), menunggu: @js(__('menunggu')), kelompok: @js(__('Kelompok')), seluruhFan: @js(__('Seluruh fan')) },
                utCaraSoal: [
                    { kunci: 'per_kitab', label: @js(__('Per kitab')), ket: @js(__('Satu soal untuk setiap kitab')) },
                    { kunci: 'seluruh_fan', label: @js(__('Seluruh fan')), ket: @js(__('Satu soal untuk semua kitab')) },
                    { kunci: 'kelompok', label: @js(__('Atur kelompok')), ket: @js(__('Admin menggabungkan kitab tertentu')) },
                ],
                utPanel: @json($panelUjian),   // nilai | soal
                utBulan: BULAN,
                utSaring: { bulan: BULAN_AWAL, ujian: (UJIAN_AWAL.find((u) => u.is_active) || UJIAN_AWAL[0] || {}).id || '', kelas: '', cari: '' },
                utTampil: 'rekap',
                utModalAtur: false,
                utForm: formKosong(),
                utUbah: null,
                utMenyimpan: false,
                utMenghapus: null,
            }),
            computed: {
                utUjianTerpilih() { return this.utUjian.find((u) => u.id === this.utSaring.ujian) || null; },
                utKitabDiFan() { return this.kitabFan.filter((k) => k.bidang_ilmu_id === Number(this.utForm.bidang_ilmu_id)); },
                // Bulan yang dipakai ujian mana pun (untuk tombol saringan)
                utBulanDipakai() {
                    const dipakai = new Set(this.utUjian.flatMap((u) => u.bulan_ids));
                    return this.utBulan.filter((b) => dipakai.has(b.id));
                },
                // Ujian di bulan terpilih ("Semua bulan" = semua ujian)
                utUjianBulan() { return this.utUjian.filter((u) => !this.utSaring.bulan || u.bulan_ids.includes(this.utSaring.bulan)); },
                utIdUjianBulan() { return new Set(this.utUjianBulan.map((u) => u.id)); },
                // Centang bulan di form, dikelompokkan per tahun ajaran
                utBulanPerTa() {
                    const per = [];
                    this.utBulan.forEach((b) => {
                        let t = per.find((x) => x.ta === b.ta);
                        if (!t) per.push((t = { ta: b.ta, bulan: [] }));
                        t.bulan.push(b);
                    });
                    return per;
                },
                // Nama ujian otomatis dari bulan yang dicentang (urut bulan ajaran)
                utNamaOtomatis() { return this.utBulan.filter((b) => this.utForm.bulan_ids.includes(b.id)).map((b) => b.nama).join(', '); },
                utKitabDipilih() { return this.utKitabDiFan.filter((k) => this.utForm.kitab_ids.includes(k.id)); },
                // Contoh pilihan yang akan dilihat asatid, sesuai cara pembuatan soal
                utPratinjau() {
                    const f = this.utForm;
                    const ids = this.utKitabDipilih.map((k) => k.id);
                    const nama = (g) => g.map((id) => (this.kitabFan.find((k) => k.id === id) || {}).nama).join(' + ');
                    if (!ids.length) return [];
                    if (f.cara_soal === 'seluruh_fan') return [this.utTeks.seluruhFan];
                    if (f.cara_soal === 'kelompok') return kelompokDari(ids, f.grup).map((g) => (g.length === ids.length && ids.length > 1 ? this.utTeks.seluruhFan : nama(g)));
                    return ids.map((id) => nama([id]));
                },
                utNilaiTersaring() {
                    const s = this.utSaring;
                    const k = kecil(s.cari.trim());
                    return this.utNilai.filter((n) => (s.ujian ? n.ujian_id === s.ujian : (!s.bulan || this.utIdUjianBulan.has(n.ujian_id)))
                        && (!s.kelas || n.kelas_id === Number(s.kelas))
                        && (!k || kecil(n.siswa).includes(k) || kecil(n.nomor).includes(k) || kecil(n.kitab).includes(k)));
                },
                // Satu kartu per kitab di ujian terpilih
                utRingkasan() {
                    const u = this.utUjianTerpilih;
                    if (!u) return [];
                    // Satu kartu per kelompok (sama dengan pilihan soal & nilai asatid); soal dicocokkan lewat kunci kelompok
                    const kunci = (ids) => [...ids].sort((a, b) => a - b).join('-');
                    const soal = (k, status) => this.utSoal.filter((x) => x.ujian_id === u.id && kunci(x.kitab_ids) === k && x.status === status).length;
                    return u.kelompok.map((k) => {
                        const d = this.utNilaiTersaring.filter((n) => n.kelompok === k.kunci).map((n) => n.nilai);
                        return {
                            ...k,
                            jumlah: d.length,
                            rata: d.length ? d.reduce((a, b) => a + b, 0) / d.length : 0,
                            tinggi: d.length ? Math.max(...d) : 0,
                            rendah: d.length ? Math.min(...d) : 0,
                            soalSetuju: soal(k.kunci, 'disetujui'),
                            soalTunggu: soal(k.kunci, 'menunggu'),
                        };
                    });
                },
                // Satu baris per santri: nilai tiap kitab + rata-rata
                utRekap() {
                    const u = this.utUjianTerpilih;
                    if (!u) return [];
                    const peta = {};
                    this.utNilaiTersaring.forEach((n) => {
                        const r = peta[n.siswa_id] ||= { siswa_id: n.siswa_id, siswa: n.siswa, arab: n.arab, halaqoh: n.halaqoh, nilai: {}, persen: [] };
                        r.nilai[n.kelompok] = n.nilai;
                        r.persen.push(n.persen);
                    });
                    return Object.values(peta).map((r) => {
                        const v = Object.values(r.nilai);
                        return { ...r, rata: v.reduce((a, b) => a + b, 0) / v.length, persen: r.persen.reduce((a, b) => a + b, 0) / r.persen.length };
                    }).sort((a, b) => b.rata - a.rata || a.siswa.localeCompare(b.siswa));
                },
            },
            methods: {
                // Pindah panel Nilai / Soal Ujian; alamat ?panel=... ikut berubah
                utPilihPanel(p) {
                    this.utPanel = p;
                    const url = new URL(location.href);
                    url.searchParams.set('panel', p);
                    history.replaceState(null, '', url);
                },
                // Ganti saringan bulan: ujian terpilih ikut pindah ke ujian pertama di bulan itu
                utPilihBulan(id) {
                    this.utSaring.bulan = id;
                    const ada = this.utUjianBulan;
                    if (!ada.some((u) => u.id === this.utSaring.ujian)) this.utSaring.ujian = (ada.find((u) => u.is_active) || ada[0] || {}).id || '';
                },
                utBukaAtur() { this.utFormBaru(); this.utModalAtur = true; },
                utFormBaru() { this.utForm = formKosong(); },
                utUbahUjian(u) {
                    // nomor kelompok tiap kitab dari kelompok_soal yang tersimpan
                    const grup = {};
                    (u.kelompok_soal || []).forEach((g, i) => g.forEach((id) => { grup[id] = i + 1; }));
                    this.utForm = {
                        id: u.id, nama: u.nama || '', bulan_ids: [...u.bulan_ids], bidang_ilmu_id: u.bidang_ilmu_id || '', kitab_ids: [...u.kitab_ids],
                        nilai_maks: u.nilai_maks, cara_soal: u.cara_soal || 'per_kitab', grup, is_active: u.is_active,
                    };
                },
                async utKirimUjian(isi) {
                    this.utMenyimpan = true;
                    try {
                        const json = await kirim(RUTE.ujian, 'POST', isi);
                        this.utUjian = json.ujian;
                        if (!this.utSaring.ujian && json.ujian.length) this.utSaring.ujian = json.ujian[0].id;
                        window.notifikasi?.sukses(json.message);
                        return true;
                    } catch (e) { gagal(e); return false; } finally { this.utMenyimpan = false; }
                },
                async utSimpanUjian() {
                    const f = this.utForm;
                    if (!f.bulan_ids.length) return gagal(new Error(@js(__('Centang minimal satu bulan.'))));
                    if (!f.kitab_ids.length) return gagal(new Error(@js(__('Centang minimal satu kitab.'))));
                    const ids = this.utKitabDipilih.map((k) => k.id);
                    if (await this.utKirimUjian({
                        id: f.id, bulan_ids: f.bulan_ids, bidang_ilmu_id: Number(f.bidang_ilmu_id), kitab_ids: ids,
                        nilai_maks: f.nilai_maks, cara_soal: f.cara_soal, is_active: f.is_active,
                        kelompok_soal: f.cara_soal === 'kelompok' ? kelompokDari(ids, f.grup) : null,
                    })) this.utFormBaru();
                },
                utAktifkan(u) {
                    this.utKirimUjian({
                        id: u.id, bulan_ids: u.bulan_ids, bidang_ilmu_id: u.bidang_ilmu_id, kitab_ids: u.kitab_ids, nilai_maks: u.nilai_maks,
                        cara_soal: u.cara_soal, kelompok_soal: u.kelompok_soal, is_active: !u.is_active,
                    });
                },
                async utHapusUjian(u) {
                    if (!confirm(@js(__('Hapus ujian :nama? Nilai & soal yang sudah ada tetap tersimpan.')).replace(':nama', u.judul))) return;
                    try {
                        const json = await kirim(RUTE.hapusUjian.replace('__ID__', u.id), 'DELETE');
                        this.utUjian = json.ujian;
                        if (this.utForm.id === u.id) this.utFormBaru();
                        if (this.utSaring.ujian === u.id) this.utSaring.ujian = '';
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); }
                },
                utBukaUbah(n) { this.utUbah = { ...n }; },
                async utSimpanUbah() {
                    const u = this.utUbah;
                    this.utMenyimpan = true;
                    try {
                        const json = await kirim(RUTE.ubahNilai.replace('__ID__', u.id), 'PUT', { nilai: u.nilai, tanggal: u.tanggal });
                        this.utNilai = this.utNilai.map((x) => (x.id === u.id ? json.baris : x));
                        this.utUbah = null;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.utMenyimpan = false; }
                },
                async utHapusNilai(n) {
                    if (!confirm(@js(__('Hapus nilai ujian :nama (:kitab)?')).replace(':nama', n.siswa).replace(':kitab', n.kitab || '-'))) return;
                    this.utMenghapus = n.id;
                    try {
                        const json = await kirim(RUTE.hapusNilai.replace('__ID__', n.id), 'DELETE');
                        this.utNilai = this.utNilai.filter((x) => x.id !== n.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.utMenghapus = null; }
                },
            },
        };
    })();
</script>
@endpush
