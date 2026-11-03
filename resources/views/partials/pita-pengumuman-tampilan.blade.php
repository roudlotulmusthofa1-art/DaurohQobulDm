{{--
    Tampilan satu Pita Pengumuman (template Vue). Dipakai landing page & pratinjau admin.
    Aplikasi Vue pemakainya harus punya: mixinPita, `pita` (data pengumuman), `banyakPita`, `bisaTutup`
    dan (landing) method pitaSebelum / pitaBerikut / tutupPita.
--}}
<div class="relative overflow-hidden" :class="gayaPita(pita).latar" :style="latarPita(pita)">
    {{-- kilau lembut --}}
    <div class="pointer-events-none absolute inset-0 opacity-25 bg-[radial-gradient(circle_at_12%_50%,rgba(255,255,255,0.35),transparent_35%)]"></div>

    <div class="relative max-w-7xl mx-auto pl-4 sm:pl-6 lg:pl-8 py-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[13px]"
        :class="bisaTutup || banyakPita > 1 ? 'pr-20 sm:pr-24' : 'pr-4 sm:pr-6'">

        {{-- Lencana jenis --}}
        <span class="inline-flex items-center gap-1.5 shrink-0 rounded-full pl-1.5 pr-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide" :class="gayaPita(pita).lencana">
            <span class="relative flex w-2 h-2">
                <span v-if="fasePita(pita) === 'berlangsung'" class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" :class="gayaPita(pita).titik"></span>
                <span class="relative inline-flex w-2 h-2 rounded-full" :class="gayaPita(pita).titik"></span>
            </span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="ikonPita(pita)"></path></svg>
            <span v-text="pita.label"></span>
        </span>

        {{-- Judul + keterangan + tanggal --}}
        <span class="min-w-0 flex-1 basis-64 leading-snug">
            <span class="font-bold" v-text="pita.judul || 'Judul pengumuman'"></span>
            <span v-if="pita.keterangan" class="opacity-90" v-text="' — ' + pita.keterangan"></span>
            <span class="flex items-center gap-1 text-[12px] opacity-80 mt-0.5">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg>
                <span v-text="pita.rentang"></span>
            </span>
        </span>

        {{-- Hitung mundur --}}
        <span v-if="pita.hitung_mundur" class="inline-flex items-center gap-1.5 shrink-0">
            <span class="text-[11px] opacity-80" v-text="labelSisaPita(pita)"></span>
            <span v-if="sisaPita(pita).hari > 0" class="rounded-md px-1.5 py-0.5 font-bold tabular-nums" :class="gayaPita(pita).kotak" v-text="sisaPita(pita).hari + ' hari'"></span>
            <span class="rounded-md px-1.5 py-0.5 font-bold font-mono tabular-nums" :class="gayaPita(pita).kotak" v-text="sisaPita(pita).jam"></span>
        </span>

        {{-- Tombol --}}
        <a v-if="pita.tombol_teks && pita.tombol_url" :href="pita.tombol_url"
            class="inline-flex items-center gap-1 shrink-0 rounded-full px-3.5 py-1 text-xs font-bold shadow-sm transition-colors" :class="gayaPita(pita).tombol">
            <span v-text="pita.tombol_teks"></span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
        </a>
    </div>

    {{-- Kanan: pindah pengumuman (bila lebih dari satu) + tutup --}}
    <div v-if="bisaTutup || banyakPita > 1" class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 flex items-center gap-0.5">
        <template v-if="banyakPita > 1">
            <button type="button" @click="pitaSebelum" class="w-7 h-7 rounded-full flex items-center justify-center opacity-80 hover:opacity-100 hover:bg-white/15" title="Pengumuman sebelumnya" aria-label="Pengumuman sebelumnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path></svg>
            </button>
            <button type="button" @click="pitaBerikut" class="w-7 h-7 rounded-full flex items-center justify-center opacity-80 hover:opacity-100 hover:bg-white/15" title="Pengumuman berikutnya" aria-label="Pengumuman berikutnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
            </button>
        </template>
        <button v-if="bisaTutup" type="button" @click="tutupPita" class="w-7 h-7 rounded-full flex items-center justify-center opacity-80 hover:opacity-100 hover:bg-white/15" title="Tutup pengumuman" aria-label="Tutup pengumuman">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>
