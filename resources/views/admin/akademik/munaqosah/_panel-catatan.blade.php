{{--
    Munaqosah: Panel 7: Catatan Pencapaian & Pelanggaran (hanya dibaca). Data Vue: catatan, saringCatatan, tp (mixin MUNAQOSAH_PANEL).
    Dipakai di halaman detail (Lajnah) dan modal panel di halaman Munaqosah (Musyrifin).
    Butuh variabel Blade $kartu dan $nomorPanel.
--}}
<section class="{{ $kartu }} p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <h2 class="flex-1 flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel(7) !!} {{ __('Catatan Pencapaian & Pelanggaran') }}</h2>
        <div class="grid grid-cols-3 gap-1 rounded-2xl bg-slate-100 p-1 sm:w-96">
            <button v-for="s in SARING_CATATAN" :key="s.kunci" type="button" @click="saringCatatan = s.kunci" class="py-1.5 rounded-xl text-xs font-bold transition"
                :class="saringCatatan === s.kunci ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                v-text="s.label + ' (' + jumlahCatatan(s.kunci) + ')'"></button>
        </div>
    </div>
    <div v-if="catatanTampil.length" class="mt-4 space-y-2">
        <div v-for="c in catatanTampil" :key="c.id" class="flex gap-3 rounded-2xl ring-1 px-4 py-3"
            :class="c.jenis === 'pencapaian' ? 'bg-emerald-50/50 ring-emerald-100' : 'bg-rose-50/50 ring-rose-100'">
            <span class="mt-1 w-2.5 h-2.5 shrink-0 rounded-full" :class="c.jenis === 'pencapaian' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
            <div class="min-w-0 flex-1">
                <p class="flex flex-wrap items-center gap-x-2 text-xs">
                    <span class="font-bold" :class="c.jenis === 'pencapaian' ? 'text-emerald-700' : 'text-rose-700'" v-text="c.label"></span>
                    <span class="text-slate-400" v-text="c.hari + ', ' + tanggal(c.tanggal) + (c.jam ? ' · ' + c.jam : '')"></span>
                </p>
                <p v-if="c.catatan" class="mt-1 text-sm text-slate-700 whitespace-pre-line" v-text="c.catatan"></p>
                <p v-if="c.keterangan" class="mt-0.5 text-[11px] text-slate-400" v-text="c.keterangan"></p>
                <p v-if="c.oleh" class="mt-0.5 text-[11px] text-slate-400"><span v-text="tp.oleh + ' ' + c.oleh"></span></p>
            </div>
        </div>
    </div>
    <p v-else class="mt-4 text-sm text-slate-400">{{ __('Belum ada catatan.') }}</p>
</section>
