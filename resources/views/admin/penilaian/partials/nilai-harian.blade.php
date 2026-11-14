{{--
    Admin > Penilaian > Kelas & Pelajaran > Nilai Harian (dimuat dari admin/penilaian/kelas.blade.php, di dalam Vue #halaman-penilaian-kelas).
    - Saringan kelas, kitab, bulan ajaran (form GET biasa; awalnya bulan ajaran aktif)
    - Satu santri + kitab + bulan ajaran = satu nilai (diteruskan asatid setiap hari, "tanggal" = terakhir dinilai)
    - Ringkasan, Rekap per santri (rata-rata total & tiap penilaian), Semua nilai (bisa dihapus)
    Data: $nilai (NilaiHarian::baris), $aspek (6 penilaian + batasnya), $saring.
--}}
@php
$inputSaring = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-sky-500 outline-none';
@endphp

<div v-show="tab === 'harian'" @if ($tab !== 'harian') style="display: none" @endif class="space-y-5">
    {{-- ===== Saringan ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 sm:p-5">
        <input type="hidden" name="tab" value="harian">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kelas') }}</span>
                <select name="kelas_id" class="mt-1 {{ $inputSaring }}">
                    <option value="">{{ __('Semua kelas') }}</option>
                    @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected((int) $saring['kelas_id'] === $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select></label>
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kitab') }}</span>
                <select name="kitab_id" class="mt-1 {{ $inputSaring }}">
                    <option value="">{{ __('Semua kitab') }}</option>
                    @foreach ($kitabList as $k)
                    <option value="{{ $k->id }}" @selected((int) $saring['kitab_id'] === $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select></label>
            <label class="block col-span-2"><span class="text-xs font-semibold text-slate-500">{{ __('Bulan ajaran') }}</span>
                <select name="bulan_id" class="mt-1 {{ $inputSaring }}">
                    <option value="semua" @selected($saring['bulan_id'] === 'semua')>{{ __('Semua bulan') }}</option>
                    @foreach ($bulanList as $b)
                    <option value="{{ $b->id }}" @selected($saring['bulan_id'] === (string) $b->id)>{{ __('Bulan ke-:n', ['n' => $b->urutan]) }} · {{ $b->nama }}{{ $b->tahunAjaran ? ' · ' . $b->tahunAjaran->nama : '' }}{{ $b->is_active ? ' (' . __('aktif') . ')' : '' }}</option>
                    @endforeach
                </select></label>
            <div class="col-span-2 lg:col-span-1 flex gap-2">
                <button class="flex-1 px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Terapkan') }}</button>
                @if ($saringDiubah)
                <a href="?tab=harian" class="px-3 py-2.5 rounded-xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Reset') }}</a>
                @endif
            </div>
        </div>
    </form>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Jumlah penilaian') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="nilaiTersaring.length"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Santri dinilai') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800 tabular-nums" v-text="rekap.length"></p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Rata-rata nilai') }}</p>
            <p class="mt-1 text-3xl font-extrabold text-sky-600 tabular-nums" v-text="bulat(rataSemua)"></p>
            <p class="mt-1 text-[11px] text-slate-400">{{ __('dari 100') }}</p>
        </div>
        <div class="{{ $kartu }} p-4 sm:p-5">
            <p class="text-xs font-semibold text-slate-500">{{ __('Yang dinilai') }}</p>
            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                <span v-for="(a, i) in aspek" :key="a.kunci" class="block truncate"><span v-text="(i + 1) + '. ' + a.label"></span> <b class="text-slate-700" v-text="'(' + a.maks + ')'"></b></span>
            </p>
        </div>
    </div>

    {{-- ===== Tab tampilan + cari ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex gap-2 overflow-x-auto pb-1 flex-1">
            <button type="button" @click="tampil = 'rekap'" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
                :class="tampil === 'rekap' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Rekap per santri') }}</button>
            <button type="button" @click="tampil = 'daftar'" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors"
                :class="tampil === 'daftar' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">{{ __('Semua nilai') }}</button>
        </div>
        <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor / kitab…') }}" class="sm:w-64 {{ $inputSaring }}">
    </div>

    {{-- Kosong --}}
    <div v-if="!nilaiTersaring.length" class="{{ $kartu }} py-16 px-6 text-center">
        <p class="font-bold text-slate-700">{{ __('Belum ada nilai harian') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Nilai muncul setelah asatid mengisinya dari dasbor asatid > Penilaian > Nilai Harian.') }}</p>
    </div>

    {{-- ================= Rekap per santri ================= --}}
    <section v-else-if="tampil === 'rekap'" class="{{ $kartu }} p-4 sm:p-6">
        {{-- HP: kartu --}}
        <div class="lg:hidden divide-y divide-slate-100">
            <div v-for="(r, i) in rekap" :key="r.siswa_id" class="py-3">
                <div class="flex items-center gap-3">
                    <img v-if="r.foto" :src="r.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                    <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(r.siswa)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="r.siswa"></span>
                        <x-nama-arab vue="r.arab" />
                        <span class="block text-xs text-slate-400 truncate" v-text="[r.kelas, r.jumlah + ' ' + @js(__('kitab dinilai'))].filter(Boolean).join(' · ')"></span>
                    </span>
                    <span class="shrink-0 rounded-xl px-2.5 py-1.5 text-sm font-extrabold tabular-nums ring-1" :class="warnaTotal(r.rata)" v-text="bulat(r.rata)"></span>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                    <span v-for="a in aspek" :key="a.kunci" class="flex justify-between gap-2 text-slate-500">
                        <span class="truncate" v-text="a.label"></span>
                        <b class="shrink-0 text-slate-700 tabular-nums" v-text="bulat(r.rataAspek[a.kunci]) + '/' + a.maks"></b>
                    </span>
                </div>
            </div>
        </div>
        {{-- Layar lebar: tabel --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="py-2.5 pr-3 w-8">#</th>
                        <th class="py-2.5 pr-3">{{ __('Santri') }}</th>
                        <th class="py-2.5 px-2 text-center">{{ __('Kitab') }}</th>
                        <th v-for="(a, i) in aspek" :key="a.kunci" class="py-2.5 px-2 text-center" :title="a.label">
                            <span v-text="i + 1"></span><span class="block text-[10px] font-normal text-slate-400" v-text="'/' + a.maks"></span>
                        </th>
                        <th class="py-2.5 pl-2 text-center">{{ __('Rata-rata') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(r, i) in rekap" :key="r.siswa_id" class="border-b border-slate-50 hover:bg-[#f8fbfa]">
                        <td class="py-2.5 pr-3 text-slate-400 tabular-nums" v-text="i + 1"></td>
                        <td class="py-2.5 pr-3 min-w-48">
                            <span class="block font-semibold text-slate-800" v-text="r.siswa"></span>
                            <x-nama-arab vue="r.arab" />
                            <span v-if="r.kelas" class="block text-xs text-slate-400" v-text="r.kelas"></span>
                        </td>
                        <td class="py-2.5 px-2 text-center tabular-nums text-slate-600" v-text="r.jumlah"></td>
                        <td v-for="a in aspek" :key="a.kunci" class="py-2.5 px-2 text-center tabular-nums text-slate-600" v-text="bulat(r.rataAspek[a.kunci])"></td>
                        <td class="py-2.5 pl-2 text-center">
                            <span class="inline-block min-w-[3rem] rounded-xl px-2.5 py-1 font-extrabold tabular-nums ring-1" :class="warnaTotal(r.rata)" v-text="bulat(r.rata)"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-3 text-[11px] text-slate-400">
                {{ __('Nomor kolom') }}: <span v-for="(a, i) in aspek" :key="a.kunci"><b v-text="i + 1"></b> = <span v-text="a.label"></span><span v-if="i < aspek.length - 1"> · </span></span>
            </p>
        </div>
    </section>

    {{-- ================= Semua nilai ================= --}}
    <section v-else class="{{ $kartu }} p-4 sm:p-6">
        <ul class="divide-y divide-slate-100">
            <li v-for="n in nilaiTersaring" :key="n.id" class="py-3 flex items-start gap-3">
                <span class="shrink-0 min-w-[3rem] rounded-xl px-2.5 py-1.5 text-center text-sm font-extrabold tabular-nums ring-1" :class="warnaTotal(n.total)" v-text="n.total"></span>
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold text-slate-800" v-text="n.siswa"></span>
                    <x-nama-arab vue="n.arab" />
                    <span class="block text-xs text-slate-400" v-text="[n.kelas, n.kitab, n.bulan, @js(__('terakhir dinilai')) + ' ' + n.tanggal, n.oleh ? @js(__('oleh')) + ' ' + n.oleh : null].filter(Boolean).join(' · ')"></span>
                    <x-nama-arab vue="n.oleh_arab" kelas="text-xs text-slate-400" />
                    <span class="mt-1.5 flex flex-wrap gap-1">
                        <span v-for="(a, i) in aspek" :key="a.kunci" :title="a.label"
                            class="rounded-lg bg-slate-50 px-2 py-0.5 text-[11px] text-slate-500 ring-1 ring-slate-200 tabular-nums"
                            v-text="(i + 1) + ': ' + n.nilai[a.kunci] + '/' + a.maks"></span>
                    </span>
                </span>
                <button type="button" @click="bukaUbahHarian(n)"
                    title="{{ __('Ubah nilai') }}" aria-label="{{ __('Ubah nilai') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </button>
                <button type="button" @click="hapus(n)" :disabled="menghapus === n.id"
                    title="{{ __('Hapus nilai') }}" aria-label="{{ __('Hapus nilai') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </li>
        </ul>
    </section>

    {{-- ===== Modal: Ubah Nilai Harian (6 penilaian, batas dari NilaiHarian::ASPEK) ===== --}}
    <div v-if="ubahHarian" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="ubahHarian = null">
        <form class="w-full max-w-lg rounded-3xl bg-white shadow-xl" @submit.prevent="simpanUbahHarian">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="min-w-0">
                    <h3 class="text-base font-extrabold text-slate-800">{{ __('Ubah Nilai Harian') }}</h3>
                    <p class="text-xs text-slate-500 truncate" v-text="[ubahHarian.siswa, ubahHarian.kitab, ubahHarian.bulan].filter(Boolean).join(' · ')"></p>
                </div>
                <button type="button" @click="ubahHarian = null" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-3">
                <label v-for="(a, i) in aspek" :key="a.kunci" class="flex items-center gap-3">
                    <span class="flex-1 text-sm text-slate-700"><b class="text-slate-400" v-text="(i + 1) + '.'"></b> <span v-text="a.label"></span></span>
                    <input type="number" min="0" :max="a.maks" step="1" required v-model.number="ubahHarian.nilai[a.kunci]"
                        class="w-20 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2 text-sm text-center tabular-nums text-slate-700 focus:ring-2 focus:ring-sky-500 outline-none">
                    <span class="w-10 text-xs text-slate-400 tabular-nums" v-text="'/ ' + a.maks"></span>
                </label>
                <p class="pt-2 border-t border-slate-100 flex justify-between text-sm font-bold text-slate-700">
                    <span>{{ __('Total') }}</span><span class="tabular-nums" v-text="totalUbahHarian + ' / 100'"></span>
                </p>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                <button type="button" @click="ubahHarian = null" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                    <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                    <template v-else>{{ __('Simpan Perubahan') }}</template>
                </button>
            </div>
        </form>
    </div>
</div>
