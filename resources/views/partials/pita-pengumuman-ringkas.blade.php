{{--
    Tampilan RINGKAS satu Pita Pengumuman (template Vue): kapsul kaca putih di tengah, senada dengan navbar.
    Warna pilihan admin dipakai sebagai aksen (lencana, tombol, garis sisa waktu).
    HP: kartu 2 baris (judul + tutup | hitung mundur + tombol). Layar lebar: satu baris.
    Dipakai landing page (bila ukuran = ringkas) & pratinjau admin. Kebutuhan sama dengan pita-pengumuman-tampilan.
--}}
<div class="pointer-events-auto relative mx-3 sm:mx-auto sm:w-fit sm:max-w-[min(60rem,calc(100%-2rem))] overflow-hidden rounded-2xl sm:rounded-full bg-white/90 backdrop-blur-xl text-slate-800 ring-1 ring-slate-900/5 shadow-[0_12px_40px_-14px_rgba(11,51,44,0.5)]">

    <div class="relative flex flex-wrap sm:flex-nowrap items-center gap-x-3 gap-y-2 p-2 sm:pr-2.5">

        {{-- Lencana jenis (warna aksen) + titik berkedip --}}
        <span class="relative shrink-0 inline-flex items-center gap-1.5 h-9 rounded-full px-2.5 sm:pl-2.5 sm:pr-3.5 text-[11px] font-bold uppercase tracking-wide"
            :class="gayaPita(pita).aksen" :style="aksenPita(pita)" :title="pita.label">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="ikonPita(pita)"></path></svg>
            <span class="hidden sm:inline" v-text="pita.label"></span>
            <span v-if="fasePita(pita) === 'berlangsung'" class="absolute -top-0.5 -right-0.5 flex w-2.5 h-2.5">
                <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
            </span>
        </span>

        {{-- Judul + tanggal --}}
        <span class="min-w-0 flex-1 sm:flex-none sm:max-w-[22rem] leading-tight">
            <span class="block text-[13px] sm:text-sm font-bold text-slate-900 truncate" v-text="pita.judul || @js(__('Judul pengumuman'))"></span>
            <span class="flex items-center gap-1 text-[11px] text-slate-500 truncate mt-0.5">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg>
                <span class="truncate" v-text="pita.rentang"></span>
            </span>
        </span>

        {{-- Tutup (HP: di baris pertama) --}}
        <button v-if="bisaTutup" type="button" @click="tutupPita" class="sm:hidden shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100" title="{{ __('Tutup pengumuman') }}" aria-label="{{ __('Tutup pengumuman') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        {{-- Baris kedua di HP: hitung mundur + tombol --}}
        <span class="flex items-center gap-2 w-full sm:w-auto">
            <span v-if="pita.hitung_mundur" class="inline-flex items-center gap-1 shrink-0">
                <span class="text-[10px] leading-tight text-slate-500 max-w-[3.6rem] mr-0.5" v-text="labelSisaPita(pita)"></span>
                <span v-if="kotakPita(pita).hari > 0" class="flex flex-col items-center min-w-[2.15rem] rounded-lg bg-slate-100 px-1 py-1">
                    <span class="text-[13px] font-bold font-mono tabular-nums leading-none text-slate-900" v-text="kotakPita(pita).hari"></span>
                    <span class="text-[9px] uppercase tracking-wide leading-none mt-0.5 text-slate-500">{{ __('hari') }}</span>
                </span>
                <span class="flex flex-col items-center min-w-[2.15rem] rounded-lg bg-slate-100 px-1 py-1">
                    <span class="text-[13px] font-bold font-mono tabular-nums leading-none text-slate-900" v-text="kotakPita(pita).jam"></span>
                    <span class="text-[9px] uppercase tracking-wide leading-none mt-0.5 text-slate-500">{{ __('jam') }}</span>
                </span>
                <span class="flex flex-col items-center min-w-[2.15rem] rounded-lg bg-slate-100 px-1 py-1">
                    <span class="text-[13px] font-bold font-mono tabular-nums leading-none text-slate-900" v-text="kotakPita(pita).menit"></span>
                    <span class="text-[9px] uppercase tracking-wide leading-none mt-0.5 text-slate-500">{{ __('mnt') }}</span>
                </span>
                <span class="flex flex-col items-center min-w-[2.15rem] rounded-lg bg-slate-100 px-1 py-1">
                    <span class="text-[13px] font-bold font-mono tabular-nums leading-none text-slate-900" v-text="kotakPita(pita).detik"></span>
                    <span class="text-[9px] uppercase tracking-wide leading-none mt-0.5 text-slate-500">{{ __('dtk') }}</span>
                </span>
            </span>

            <a v-if="pita.tombol_teks && pita.tombol_url" :href="pita.tombol_url"
                class="group ml-auto sm:ml-1 shrink-0 inline-flex items-center gap-1.5 h-9 rounded-full px-4 text-xs font-bold shadow-md transition-all hover:-translate-y-px"
                :class="gayaPita(pita).aksenTombol" :style="aksenPita(pita)">
                <span v-text="pita.tombol_teks"></span>
                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
            </a>

            {{-- Pindah (lebih dari satu) + tutup (layar lebar) --}}
            <span class="inline-flex items-center shrink-0" :class="pita.tombol_teks && pita.tombol_url ? '' : 'ml-auto sm:ml-0'">
                <button v-if="banyakPita > 1" type="button" @click="pitaBerikut" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100" title="{{ __('Pengumuman berikutnya') }}" aria-label="{{ __('Pengumuman berikutnya') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                </button>
                <button v-if="bisaTutup" type="button" @click="tutupPita" class="hidden sm:flex w-8 h-8 rounded-full items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100" title="{{ __('Tutup pengumuman') }}" aria-label="{{ __('Tutup pengumuman') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </span>
        </span>
    </div>

    {{-- Garis sisa waktu (hanya saat acara berlangsung) --}}
    <span v-if="persenPita(pita) !== null" class="absolute left-0 bottom-0 h-[3px] bg-slate-900/5 w-full">
        <span class="block h-full transition-[width] duration-1000 ease-linear" :class="gayaPita(pita).garis" :style="[aksenPita(pita), { width: persenPita(pita) + '%' }]"></span>
    </span>
</div>
