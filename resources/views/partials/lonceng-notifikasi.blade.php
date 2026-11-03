{{--
    Isi menu lonceng (dipakai layout admin, asatid & siswa) — dirender oleh Vue header (mixin di lonceng-notifikasi-skrip).
    - Klik notifikasi  : ditandai sudah dibaca, angka merah langsung berkurang.
    - Tombol ×         : hapus notifikasi (muncul lagi hanya bila ada perubahan baru).
    - Tandai semua dibaca / Hapus semua di bagian bawah.
    Teks notifikasi sudah di-escape di server (TeksAman::e), jadi aman ditampilkan dengan v-html.
--}}
<div class="max-h-[60vh] overflow-y-auto">
    <div v-for="n in notif" :key="n.kunci"
        class="relative flex items-start border-b border-slate-50 last:border-0 transition-colors"
        :class="n.dibaca ? 'bg-white' : 'bg-emerald-50/50'">
        {{-- Titik hijau = belum dibaca --}}
        <span v-if="!n.dibaca" class="absolute left-1.5 top-[26px] w-1.5 h-1.5 rounded-full bg-emerald-500" title="Belum dibaca"></span>
        <a :href="n.url || '#'" @click="bukaNotif(n, $event)"
            class="flex-1 min-w-0 flex items-start gap-3 pl-4 pr-1 py-3 hover:bg-[#f5f9f7] transition-colors">
            <span class="w-9 h-9 shrink-0 rounded-xl ring-1 flex items-center justify-center" :class="[n.warna, n.dibaca ? 'opacity-50' : '']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="n.ikon"></path></svg>
            </span>
            <span class="min-w-0">
                <span class="block text-[13px] leading-snug [&_b]:font-semibold"
                    :class="n.dibaca ? 'text-slate-400 [&_b]:text-slate-500' : 'text-slate-700 [&_b]:text-slate-900'" v-html="n.teks"></span>
                <span v-if="n.ket" class="block text-[11px] text-slate-400 mt-0.5" v-text="n.ket"></span>
            </span>
        </a>
        <button type="button" @click="hapusNotif(n)" title="Hapus notifikasi" aria-label="Hapus notifikasi"
            class="shrink-0 mt-3 mr-2 w-8 h-8 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <div v-if="!notif.length" class="px-4 py-8 text-center">
        <span class="mx-auto w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
        </span>
        <p class="mt-2 text-sm text-slate-500">Tidak ada notifikasi</p>
    </div>
</div>

<div v-if="notif.length" class="flex items-center justify-between gap-2 px-4 py-2 border-t border-slate-100 text-xs font-semibold">
    <button v-if="notifTotal > 0" type="button" @click="bacaSemuaNotif" class="text-emerald-700 hover:underline">Tandai semua dibaca</button>
    <span v-else class="text-slate-400">Semua sudah dibaca</span>
    <button type="button" @click="hapusSemuaNotif" class="text-rose-600 hover:underline">Hapus semua</button>
</div>
