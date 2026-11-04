{{--
    Pratinjau foto layar penuh (dipakai di layout admin, asatid, siswa).

    Cara pakai di halaman mana pun: tambahkan atribut  data-perbesar  pada <img>.
        <img src="..." data-perbesar>                       -> judul diambil dari alt
        <img src="..." data-perbesar="Nama Santri">         -> judul = teks itu
        <img :src="p.foto" :data-perbesar="p.nama">         -> juga bisa di template Vue
    Klik foto -> terbuka besar. Bisa diperbesar (tombol +/−, roda mouse, klik dua kali),
    digeser saat diperbesar, pindah foto dengan ← →, tutup dengan Esc / klik area gelap.
--}}
<style>
    img[data-perbesar] { cursor: zoom-in; }
</style>

<div id="pratinjau-foto">
    {{-- <dialog> supaya tampil paling atas, termasuk di atas pop-up lain yang sedang terbuka --}}
    <dialog ref="kotak" @close="saatTertutup" class="m-0 p-0 w-screen h-screen max-w-none max-h-none bg-transparent backdrop:bg-black/90">
    <div v-if="buka" class="w-full h-full flex flex-col select-none" @click.self="tutup" @wheel.prevent="roda">
        {{-- Bilah atas --}}
        <div class="flex items-center gap-2 px-3 sm:px-5 py-3 text-white" @click.self="tutup">
            <p class="flex-1 min-w-0 truncate text-sm font-semibold" v-text="sekarang.judul"></p>
            <span v-if="daftar.length > 1" class="text-xs text-white/60 tabular-nums" v-text="(indeks + 1) + ' / ' + daftar.length"></span>
            <button type="button" @click="ubahSkala(-0.5)" :disabled="skala <= 1" title="{{ __('Perkecil') }}"
                class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 text-lg font-bold">−</button>
            <button type="button" @click="aturUlang" title="{{ __('Ukuran awal') }}"
                class="h-9 px-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold tabular-nums" v-text="Math.round(skala * 100) + '%'"></button>
            <button type="button" @click="ubahSkala(0.5)" :disabled="skala >= 5" title="{{ __('Perbesar') }}"
                class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 text-lg font-bold">+</button>
            <a :href="sekarang.src" target="_blank" rel="noopener" title="{{ __('Buka foto asli di tab baru') }}"
                class="hidden sm:inline-flex h-9 px-3 items-center rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold">{{ __('Buka asli') }}</a>
            {{-- Unduh foto ke perangkat (nama file dari judul foto) --}}
            <button type="button" @click="unduh" :disabled="mengunduh" title="{{ __('Unduh foto') }}" aria-label="{{ __('Unduh foto') }}"
                class="inline-flex h-9 px-2.5 sm:px-3 items-center gap-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-[#0b332c] text-xs font-bold disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span class="hidden sm:inline">{{ __('Unduh') }}</span>
            </button>
            <button type="button" @click="tutup" title="{{ __('Tutup (Esc)') }}" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-lg">✕</button>
        </div>

        {{-- Foto --}}
        <div class="relative flex-1 min-h-0 overflow-hidden flex items-center justify-center" @click.self="tutup">
            <img :src="sekarang.src" :alt="sekarang.judul" draggable="false"
                class="w-full h-full max-w-[94vw] object-contain drop-shadow-2xl"
                :class="skala > 1 ? (geser.aktif ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-zoom-in'"
                :style="{ transform: 'translate(' + posisi.x + 'px,' + posisi.y + 'px) scale(' + skala + ')', transition: geser.aktif ? 'none' : 'transform .15s' }"
                @click="skala === 1 && ubahSkala(1.5)"
                @pointerdown="mulaiGeser" @pointermove="saatGeser" @pointerup="selesaiGeser" @pointercancel="selesaiGeser">

            <button v-if="daftar.length > 1" type="button" @click="pindah(-1)" title="{{ __('Sebelumnya (←)') }}"
                class="absolute left-2 sm:left-5 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white text-2xl">‹</button>
            <button v-if="daftar.length > 1" type="button" @click="pindah(1)" title="{{ __('Berikutnya (→)') }}"
                class="absolute right-2 sm:right-5 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white text-2xl">›</button>
        </div>
        <p class="py-2 text-center text-[11px] text-white/40">{{ __('Klik foto / roda mouse / tombol + untuk memperbesar · geser saat diperbesar · Esc untuk menutup') }}</p>
    </div>
    </dialog>
</div>

<script>
    /**
     * Unduh QR (SVG) sebagai gambar PNG besar berlatar putih — supaya tajam & mudah dicetak.
     * Pemakaian: <div data-qr> ...svg QR... <button onclick="unduhQr(this)" data-nama="QR-nomor-nama"> </div>
     */
    window.unduhQr = function (tombol) {
        const svg = tombol.closest('[data-qr]')?.querySelector('svg');
        if (!svg) return;
        const UKURAN = 1024, TEPI = 64;
        const salinan = svg.cloneNode(true);
        salinan.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        salinan.setAttribute('width', UKURAN - TEPI * 2);
        salinan.setAttribute('height', UKURAN - TEPI * 2);
        const url = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(salinan)], { type: 'image/svg+xml' }));
        const gambar = new Image();
        gambar.onload = () => {
            const kanvas = Object.assign(document.createElement('canvas'), { width: UKURAN, height: UKURAN });
            const ctx = kanvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, UKURAN, UKURAN);
            ctx.drawImage(gambar, TEPI, TEPI, UKURAN - TEPI * 2, UKURAN - TEPI * 2);
            URL.revokeObjectURL(url);
            kanvas.toBlob((blob) => {
                const nama = (tombol.dataset.nama || 'QR').replace(/[\\/:*?"<>|]+/g, ' ').trim();
                const link = URL.createObjectURL(blob);
                const a = Object.assign(document.createElement('a'), { href: link, download: nama + '.png' });
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(link), 1000);
            }, 'image/png');
        };
        gambar.src = url;
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.pratinjauFoto = Vue.createApp({
            data() {
                return {
                    buka: false,
                    daftar: [],        // [{ src, judul }] semua foto bertanda data-perbesar di halaman
                    indeks: 0,
                    skala: 1,
                    posisi: { x: 0, y: 0 },
                    geser: { aktif: false, x: 0, y: 0 },
                    mengunduh: false,
                };
            },
            computed: {
                sekarang() {
                    return this.daftar[this.indeks] ?? { src: '', judul: '' };
                },
            },
            methods: {
                bukaDari(img) {
                    // Kumpulkan foto yang sedang tampil di halaman (src sama tidak diulang)
                    const semua = [], sudah = new Set();
                    document.querySelectorAll('img[data-perbesar]').forEach((el) => {
                        if (!el.currentSrc || sudah.has(el.currentSrc) || !el.offsetParent) return;
                        sudah.add(el.currentSrc);
                        semua.push({ src: el.currentSrc, judul: el.dataset.perbesar || el.alt || @js(__('Foto')) });
                    });
                    if (!sudah.has(img.currentSrc)) semua.push({ src: img.currentSrc, judul: img.dataset.perbesar || img.alt || @js(__('Foto')) });

                    this.daftar = semua;
                    this.indeks = Math.max(0, semua.findIndex((f) => f.src === img.currentSrc));
                    this.aturUlang();
                    this.buka = true;
                    if (!this.$refs.kotak.open) this.$refs.kotak.showModal();
                    document.documentElement.style.overflow = 'hidden'; // halaman di belakang tidak ikut tergulir
                },
                tutup() {
                    this.$refs.kotak.close(); // -> saatTertutup
                },
                // Tertutup lewat tombol X, klik area gelap, atau tombol Esc (bawaan <dialog>)
                saatTertutup() {
                    this.buka = false;
                    document.documentElement.style.overflow = '';
                },
                pindah(arah) {
                    this.indeks = (this.indeks + arah + this.daftar.length) % this.daftar.length;
                    this.aturUlang();
                },
                // Simpan foto yang sedang tampil ke perangkat. Nama file = judul foto + ekstensi aslinya.
                // Bila gagal diambil (mis. foto dari situs lain), foto dibuka di tab baru saja.
                async unduh() {
                    const { src, judul } = this.sekarang;
                    if (!src) return;
                    this.mengunduh = true;
                    try {
                        const res = await fetch(src);
                        if (!res.ok) throw new Error(res.status);
                        const blob = await res.blob();
                        const ext = (blob.type.split('/')[1] || src.split('?')[0].split('.').pop() || 'jpg').replace('jpeg', 'jpg');
                        const nama = (judul || 'foto').replace(/[\\/:*?"<>|]+/g, ' ').trim().slice(0, 80) || 'foto';
                        const url = URL.createObjectURL(blob);
                        const a = Object.assign(document.createElement('a'), { href: url, download: nama + '.' + ext });
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        setTimeout(() => URL.revokeObjectURL(url), 1000);
                    } catch (e) {
                        window.open(src, '_blank', 'noopener');
                    } finally {
                        this.mengunduh = false;
                    }
                },
                aturUlang() {
                    this.skala = 1;
                    this.posisi = { x: 0, y: 0 };
                },
                ubahSkala(tambah) {
                    this.skala = Math.min(5, Math.max(1, this.skala + tambah));
                    if (this.skala === 1) this.posisi = { x: 0, y: 0 };
                },
                roda(e) {
                    this.ubahSkala(e.deltaY < 0 ? 0.25 : -0.25);
                },
                // Geser foto yang sedang diperbesar (mouse atau jari)
                mulaiGeser(e) {
                    if (this.skala === 1) return;
                    this.geser = { aktif: true, x: e.clientX - this.posisi.x, y: e.clientY - this.posisi.y };
                    e.target.setPointerCapture(e.pointerId);
                },
                saatGeser(e) {
                    if (this.geser.aktif) this.posisi = { x: e.clientX - this.geser.x, y: e.clientY - this.geser.y };
                },
                selesaiGeser() {
                    this.geser.aktif = false;
                },
                tombol(e) {
                    if (!this.buka) return;
                    if (e.key === 'ArrowLeft' && this.daftar.length > 1) this.pindah(-1);
                    else if (e.key === 'ArrowRight' && this.daftar.length > 1) this.pindah(1);
                    else if (e.key === '+' || e.key === '=') this.ubahSkala(0.5);
                    else if (e.key === '-') this.ubahSkala(-0.5);
                },
                // Klik foto bertanda data-perbesar di mana pun -> buka pratinjau.
                // Dipasang di fase "capture" supaya didahulukan dari link/tombol di sekitar foto.
                klikDokumen(e) {
                    const img = e.target.closest?.('img[data-perbesar]');
                    if (!img || !img.currentSrc || img.closest('#pratinjau-foto')) return;
                    e.preventDefault();
                    e.stopPropagation();
                    this.bukaDari(img);
                },
            },
            mounted() {
                document.addEventListener('click', this.klikDokumen, true);
                document.addEventListener('keydown', this.tombol);
            },
        }).mount('#pratinjau-foto');
    });
</script>
