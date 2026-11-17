{{--
    Munaqosah: Panel 3: Nilai Harian, Ujian Lisan & Ujian (hanya dibaca). Data Vue: nilai, aspek, BAGIAN_NILAI, tp (mixin MUNAQOSAH_PANEL).
    Dipakai di halaman detail (Lajnah) dan modal panel di halaman Munaqosah (Musyrifin).
    Butuh variabel Blade $kartu dan $nomorPanel.
--}}
<section class="{{ $kartu }} p-5 sm:p-6">
    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel(3) !!} {{ __('Nilai Harian, Ujian Lisan & Ujian') }}</h2>
    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
        <div v-for="b in BAGIAN_NILAI" :key="b.kunci" class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2.5">
            <p class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-2 h-2 rounded-full" :class="b.titik"></span><span v-text="b.label"></span></p>
            <p class="text-xl font-extrabold tabular-nums text-slate-800" v-text="persen(nilai.persen[b.kunci])"></p>
        </div>
        <div class="rounded-2xl bg-[#0b332c] text-white px-3 py-2.5">
            <p class="text-[11px] font-semibold text-white/60">{{ __('Nilai Akhir') }}</p>
            <p class="text-xl font-extrabold tabular-nums" v-text="persen(nilai.akhir)"></p>
        </div>
    </div>

    {{-- Nilai Harian --}}
    <h3 class="mt-6 flex items-center gap-2 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>{{ __('Nilai Harian') }}</h3>
    <div v-if="nilai.rincian.harian.length" class="mt-2 overflow-x-auto rounded-2xl ring-1 ring-slate-100">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="py-2 px-3 text-left font-semibold">{{ __('Kitab') }}</th>
                    <th v-for="(a, i) in aspek" :key="a.kunci" class="py-2 px-1.5 text-center font-semibold" :title="a.label" v-text="'A' + (i + 1)"></th>
                    <th class="py-2 px-3 text-center font-semibold">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(h, i) in nilai.rincian.harian" :key="'h' + i" class="border-t border-slate-100">
                    <td class="py-2 px-3 min-w-36">
                        <span class="block font-semibold text-slate-700" v-text="h.kitab"></span>
                        <span v-if="h.bulan" class="block text-[10px] text-slate-400" v-text="h.bulan"></span>
                    </td>
                    <td v-for="(n, j) in h.nilai" :key="j" class="py-2 px-1.5 text-center tabular-nums text-slate-600" v-text="n"></td>
                    <td class="py-2 px-3 text-center font-extrabold tabular-nums text-slate-800" v-text="h.total"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <p v-if="nilai.rincian.harian.length" class="mt-1.5 text-[10px] text-slate-400 leading-relaxed">
        <span v-for="(a, i) in aspek" :key="'k' + a.kunci" class="mr-2 whitespace-nowrap" v-text="'A' + (i + 1) + ' = ' + a.label + ' (' + a.maks + ')'"></span>
    </p>
    <p v-else class="mt-2 text-sm text-slate-400">{{ __('Belum ada nilai harian.') }}</p>

    {{-- Ujian Lisan & Ujian --}}
    <template v-for="b in [{ kunci: 'lisan', label: tp.lisan, titik: 'bg-violet-500', kosong: tp.kosongLisan }, { kunci: 'ujian', label: tp.ujian, titik: 'bg-orange-500', kosong: tp.kosongUjian }]" :key="b.kunci">
        <h3 class="mt-6 flex items-center gap-2 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full" :class="b.titik"></span><span v-text="b.label"></span></h3>
        <div v-if="nilai.rincian[b.kunci].length" class="mt-2 overflow-x-auto rounded-2xl ring-1 ring-slate-100">
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
                    <tr v-for="(n, i) in nilai.rincian[b.kunci]" :key="b.kunci + i" class="border-t border-slate-100 align-top">
                        <td class="py-2 px-3 min-w-40">
                            <span class="block font-semibold text-slate-700" v-text="n.nama"></span>
                            <span v-if="n.catatan" class="mt-1 block rounded-lg bg-amber-50 px-2 py-1 text-[11px] text-amber-800 whitespace-pre-line" v-text="n.catatan"></span>
                        </td>
                        <td class="py-2 px-3 whitespace-nowrap text-slate-500" v-text="tanggal(n.tanggal)"></td>
                        <td class="py-2 px-3 text-center tabular-nums text-slate-600" v-text="n.nilai + ' / ' + n.maks"></td>
                        <td class="py-2 px-3 text-center font-extrabold tabular-nums text-slate-800" v-text="Math.round(n.persen) + '%'"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="mt-2 text-sm text-slate-400" v-text="b.kosong"></p>
    </template>
</section>
