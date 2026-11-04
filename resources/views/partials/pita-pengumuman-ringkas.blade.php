{{--
    Tampilan RINGKAS satu Pita Pengumuman (template Vue): kapsul kecil di tengah, tidak selebar layar.
    Dipakai landing page (bila ukuran = ringkas) & pratinjau admin. Kebutuhan sama dengan pita-pengumuman-tampilan.
--}}
<div class="pointer-events-auto relative mx-3 sm:mx-auto sm:w-fit sm:max-w-[min(48rem,calc(100%-1.5rem))] overflow-hidden rounded-2xl sm:rounded-full shadow-[0_10px_30px_-10px_rgba(0,0,0,0.45)] ring-1 ring-black/5"
    :class="gayaPita(pita).latar" :style="latarPita(pita)">
    <div class="pointer-events-none absolute inset-0 opacity-25 bg-[radial-gradient(circle_at_10%_50%,rgba(255,255,255,0.35),transparent_40%)]"></div>

    <div class="relative flex items-center gap-2 sm:gap-3 pl-2 pr-1.5 py-1.5 text-[12px] sm:text-[13px]">
        {{-- Ikon jenis + titik berkedip --}}
        <span class="relative shrink-0 w-8 h-8 rounded-full flex items-center justify-center" :class="gayaPita(pita).lencana" :title="pita.label">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="ikonPita(pita)"></path></svg>
            <span v-if="fasePita(pita) === 'berlangsung'" class="absolute -top-0.5 -right-0.5 flex w-2.5 h-2.5">
                <span class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" :class="gayaPita(pita).titik"></span>
                <span class="relative inline-flex w-2.5 h-2.5 rounded-full" :class="gayaPita(pita).titik"></span>
            </span>
        </span>

        {{-- Judul + tanggal --}}
        <span class="min-w-0 flex-1 leading-tight">
            <span class="block font-bold truncate" v-text="pita.judul || @js(__('Judul pengumuman'))"></span>
            <span class="block text-[11px] opacity-80 truncate" v-text="pita.rentang"></span>
        </span>

        {{-- Hitung mundur singkat --}}
        <span v-if="pita.hitung_mundur" class="shrink-0 flex flex-col items-end leading-tight">
            <span class="hidden sm:block text-[10px] opacity-75" v-text="labelSisaPita(pita)"></span>
            <span class="rounded-full px-2 py-0.5 font-mono font-bold tabular-nums text-[11px]" :class="gayaPita(pita).kotak"
                v-text="(sisaPita(pita).hari > 0 ? sisaPita(pita).hari + ' ' + @js(__('hari')) + ' ' : '') + sisaPita(pita).jam"></span>
        </span>

        {{-- Tombol --}}
        <a v-if="pita.tombol_teks && pita.tombol_url" :href="pita.tombol_url"
            class="shrink-0 inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-[11px] sm:text-xs font-bold shadow-sm transition-colors" :class="gayaPita(pita).tombol">
            <span v-text="pita.tombol_teks"></span>
            <svg class="hidden sm:block w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
        </a>

        {{-- Pindah (lebih dari satu) + tutup --}}
        <button v-if="banyakPita > 1" type="button" @click="pitaBerikut" class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center opacity-80 hover:opacity-100" title="{{ __('Pengumuman berikutnya') }}" aria-label="{{ __('Pengumuman berikutnya') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
        </button>
        <button v-if="bisaTutup" type="button" @click="tutupPita" class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center opacity-80 hover:opacity-100" title="{{ __('Tutup pengumuman') }}" aria-label="{{ __('Tutup pengumuman') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>
