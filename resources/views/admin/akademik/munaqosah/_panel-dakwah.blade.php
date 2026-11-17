{{--
    Munaqosah: Panel 4: Nilai Laporan Dakwah (hanya dibaca). Data Vue: dakwah, jenisDakwah, tp (mixin MUNAQOSAH_PANEL).
    Dipakai di halaman detail (Lajnah) dan modal panel di halaman Munaqosah (Musyrifin).
    Butuh variabel Blade $kartu dan $nomorPanel.
--}}
<section class="{{ $kartu }} p-5 sm:p-6">
    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel(4) !!} {{ __('Nilai Laporan Dakwah') }}</h2>
    <template v-for="(label, j) in jenisDakwah" :key="j">
        <h3 class="mt-5 flex items-center gap-2 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full" :class="j === 'khitobah' ? 'bg-teal-500' : 'bg-fuchsia-500'"></span><span v-text="label"></span>
            <span class="ml-auto text-xs font-semibold text-slate-400" v-text="dakwahPer(j).length + ' ' + tp.nilaiKecil"></span></h3>
        <div v-if="dakwahPer(j).length" class="mt-2 overflow-x-auto rounded-2xl ring-1 ring-slate-100">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="py-2 px-3 text-left font-semibold">{{ __('Tanggal') }}</th>
                        <th v-if="j === 'khitobah'" class="py-2 px-3 text-left font-semibold">{{ __('Waktu') }}</th>
                        <th class="py-2 px-3 text-left font-semibold">{{ __('Nilai') }}</th>
                        <th class="py-2 px-3 text-left font-semibold">{{ __('Catatan') }}</th>
                        <th class="py-2 px-3 text-left font-semibold">{{ __('Penilai') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="n in dakwahPer(j)" :key="n.id" class="border-t border-slate-100 align-top">
                        <td class="py-2 px-3 whitespace-nowrap text-slate-500"><span v-text="n.hari"></span>, <span v-text="tanggal(n.tanggal)"></span></td>
                        <td v-if="j === 'khitobah'" class="py-2 px-3 text-slate-600" v-text="n.waktu || '–'"></td>
                        <td class="py-2 px-3 font-bold text-slate-800" v-text="n.nilai"></td>
                        <td class="py-2 px-3 min-w-40 text-slate-600 whitespace-pre-line" v-text="n.catatan || '–'"></td>
                        <td class="py-2 px-3 min-w-28 text-slate-500">
                            <span class="block" v-text="n.oleh || '–'"></span>
                            <x-nama-arab vue="n.oleh_arab" kelas="text-xs text-slate-400" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="mt-2 text-sm text-slate-400">{{ __('Belum ada nilai.') }}</p>
    </template>
</section>
