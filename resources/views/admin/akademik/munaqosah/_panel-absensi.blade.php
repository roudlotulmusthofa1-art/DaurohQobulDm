{{--
    Munaqosah: Panel 6: Detail Absensi (hanya dibaca): jumlah Hadir / Terlambat / Sakit / Izin / Alfa + persen kehadiran,
    lalu daftar semua absensi kelas & kegiatan santri ini (terbaru dulu). Kotak jumlah = saringan daftar.
    Data Vue: absensi, saringAbsensi, STATUS_ABSENSI (mixin MUNAQOSAH_PANEL). Data dari MunaqosahController::absensiSantri.
    Dipakai di halaman detail (Lajnah) dan modal panel di halaman Istimaroh (tab Musyrifin & Lajnah).
    Butuh variabel Blade $kartu dan $nomorPanel.
--}}
<section class="{{ $kartu }} p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <h2 class="flex-1 flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel(6) !!} {{ __('Detail Absensi') }}</h2>
        <p class="text-sm text-slate-500">
            {{ __('Kehadiran') }}: <span class="font-extrabold text-[#0b332c]" v-text="persen(persenHadir)"></span>
            <span class="text-slate-400" v-text="'(' + absensi.length + ' ' + tp.kaliAbsen + ')'"></span>
        </p>
    </div>

    {{-- Jumlah per status (klik = saring daftar) --}}
    <div class="mt-4 grid grid-cols-3 sm:grid-cols-6 gap-2">
        <button type="button" @click="saringAbsensi = ''" class="rounded-2xl px-2 py-2.5 text-center ring-1 transition"
            :class="saringAbsensi === '' ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-slate-50 text-slate-600 ring-slate-200'">
            <span class="block text-[10px] font-semibold uppercase tracking-wider opacity-80">{{ __('Semua') }}</span>
            <span class="block text-lg font-extrabold tabular-nums" v-text="jumlahAbsensi('')"></span>
        </button>
        <button v-for="s in STATUS_ABSENSI" :key="s.kunci" type="button" @click="saringAbsensi = saringAbsensi === s.kunci ? '' : s.kunci"
            class="rounded-2xl px-2 py-2.5 text-center ring-1 transition" :class="saringAbsensi === s.kunci ? s.aktif : s.kotak">
            <span class="block text-[10px] font-semibold uppercase tracking-wider opacity-80" v-text="s.label"></span>
            <span class="block text-lg font-extrabold tabular-nums" v-text="jumlahAbsensi(s.kunci)"></span>
        </button>
    </div>

    {{-- Daftar absensi --}}
    <div v-if="absensiTampil.length" class="mt-4 space-y-2">
        <div v-for="a in absensiTampil" :key="a.id" class="flex items-start gap-3 rounded-2xl bg-white ring-1 ring-slate-200/80 px-4 py-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-slate-800" v-text="a.judul"></p>
                <p class="mt-0.5 text-xs text-slate-500" v-text="a.hari + ', ' + tanggal(a.tanggal) + (a.jam ? ' · ' + a.jam : '') + ' · ' + a.jenis"></p>
                <p v-if="a.catatan" class="mt-1 text-sm text-slate-700 whitespace-pre-line" v-text="a.catatan"></p>
                <p v-if="a.oleh" class="mt-0.5 text-[11px] text-slate-400" v-text="tp.dicatat + ' ' + a.oleh"></p>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1" :class="statusAbsensi(a.status).kotak" v-text="statusAbsensi(a.status).label"></span>
        </div>
    </div>
    <p v-else class="mt-4 text-sm text-slate-400">{{ __('Belum ada absensi.') }}</p>
</section>
