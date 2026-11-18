{{--
    Landing page > Galeri Foto (setelah Berita Terkini, di atas Kontak).
    Slider satu baris: 4 foto di laptop, 2 di HP. Tombol ‹ ›, titik halaman, geser sendiri tiap 4,5 detik
    (berhenti saat disentuh / kursor di atasnya). Klik foto = diperbesar. Tidak tampil bila belum ada foto.
    Variabel: $foto (koleksi baris App\Support\Galeri). Vue #galeri-landing; teks dari database lewat v-text.
--}}
@if ($foto->isNotEmpty())
<section id="galeri" class="relative isolate overflow-hidden bg-linear-to-b from-white via-[#f3fbf7] to-[#e3f6ee] py-16 sm:py-20">
    {{-- Hiasan latar: garis kotak tipis memudar + cahaya hijau & emas + garis emas tipis di atas --}}
    <div aria-hidden="true" class="absolute -z-10 inset-0 bg-[linear-gradient(to_right,#0f766e14_1px,transparent_1px),linear-gradient(to_bottom,#0f766e14_1px,transparent_1px)] [background-size:44px_44px] [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]"></div>
    <div aria-hidden="true" class="absolute -z-10 -top-24 right-0 w-[26rem] h-[26rem] rounded-full bg-emerald-300/30 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -z-10 -bottom-32 -left-24 w-[28rem] h-[28rem] rounded-full bg-amber-200/40 blur-3xl"></div>
    <div aria-hidden="true" class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-amber-400/60 to-transparent"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center">
            <p class="text-sm font-bold tracking-widest uppercase text-teal-700">{{ __('Galeri Peserta') }}</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-[#0b332c]">{{ __('Kegiatan Peserta Dauroh Qobul') }}</h2>
            @include('berita.partials.pemisah', ['kelas' => 'mt-4'])
        </div>

        <div id="galeri-landing" class="mt-10" v-cloak>
            <div class="relative" @mouseenter="jeda = true" @mouseleave="jeda = false" @touchstart.passive="sentuh">
                {{-- Jalur foto (bisa digeser jari) --}}
                <div ref="jalur" @scroll.passive="saatGulir"
                    class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <button v-for="(f, i) in foto" :key="f.kunci" type="button" @click="aktif = i"
                        class="group relative shrink-0 w-[calc(50%-6px)] sm:w-[calc(25%-9px)] aspect-[4/3] snap-start rounded-2xl overflow-hidden bg-slate-200 shadow-sm hover:shadow-xl transition-shadow">
                        <img :src="f.url" :alt="f.keterangan || ''" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <span class="absolute inset-0 bg-linear-to-t from-black/70 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        <span v-if="f.keterangan" class="absolute inset-x-0 bottom-0 p-3 text-left text-xs sm:text-sm font-semibold text-white line-clamp-2 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all" v-text="f.keterangan"></span>
                    </button>
                </div>

                {{-- Tombol ‹ › --}}
                <template v-if="jumlahHalaman > 1">
                    <button type="button" @click="keHalaman(halaman - 1)" aria-label="{{ __('Foto sebelumnya') }}"
                        class="absolute left-2 sm:-left-5 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/90 text-[#0b332c] shadow-lg ring-1 ring-slate-200 flex items-center justify-center hover:bg-white hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" @click="keHalaman(halaman + 1)" aria-label="{{ __('Foto berikutnya') }}"
                        class="absolute right-2 sm:-right-5 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/90 text-[#0b332c] shadow-lg ring-1 ring-slate-200 flex items-center justify-center hover:bg-white hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </template>
            </div>

            {{-- Titik halaman --}}
            <div v-if="jumlahHalaman > 1" class="mt-5 flex justify-center gap-2">
                <button v-for="n in jumlahHalaman" :key="n" type="button" @click="keHalaman(n - 1)" :aria-label="@js(__('Halaman')) + ' ' + n"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="n - 1 === halaman ? 'w-6 bg-teal-700' : 'w-2 bg-slate-300 hover:bg-slate-400'"></button>
            </div>

            {{-- Foto diperbesar --}}
            <div v-if="aktif !== null" @click.self="aktif = null" class="fixed inset-0 z-[80] bg-black/90 flex flex-col items-center justify-center p-4">
                <img :src="foto[aktif].url" alt="" class="max-w-full max-h-[78vh] object-contain rounded-lg">
                <div class="mt-3 max-w-2xl text-center text-white">
                    <p class="text-sm font-semibold" v-text="foto[aktif].keterangan"></p>
                    <p class="text-xs text-white/60" v-text="foto[aktif].tanggal_teks"></p>
                    <a v-if="foto[aktif].berita_url" :href="foto[aktif].berita_url"
                        class="mt-2 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/15 text-xs font-bold hover:bg-white/25">{{ __('Baca berita') }} &rarr;</a>
                </div>
                <button type="button" @click="aktif = null" aria-label="{{ __('Tutup') }}" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/15 text-white text-2xl leading-none hover:bg-white/25">&times;</button>
                <template v-if="foto.length > 1">
                    <button type="button" @click="geserBesar(-1)" aria-label="{{ __('Foto sebelumnya') }}" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&lsaquo;</button>
                    <button type="button" @click="geserBesar(1)" aria-label="{{ __('Foto berikutnya') }}" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&rsaquo;</button>
                </template>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('galeri.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-teal-700 text-white text-sm font-bold hover:bg-teal-800 transition-colors">
                {{ __('Lihat semua foto') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>

@php
    $fotoSlider = $foto->map(fn ($f) => \Illuminate\Support\Arr::only($f, ['kunci', 'url', 'keterangan', 'tanggal_teks', 'berita_url']))->values();
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const JARAK = 12; // gap-3

        Vue.createApp({
            data: () => ({
                foto: @json($fotoSlider),
                aktif: null,      // foto yang sedang diperbesar
                halaman: 0,
                perLayar: 4,      // jumlah foto yang terlihat sekaligus
                jeda: false,      // berhenti geser sendiri
            }),
            computed: {
                jumlahHalaman() { return Math.max(1, Math.ceil(this.foto.length / this.perLayar)); },
            },
            watch: {
                aktif(v) { document.body.style.overflow = v !== null ? 'hidden' : ''; },
            },
            methods: {
                lebarHalaman() {
                    const el = this.$refs.jalur;
                    const kartu = el?.firstElementChild;
                    return kartu ? (kartu.offsetWidth + JARAK) * this.perLayar : 1;
                },
                ukur() {
                    const el = this.$refs.jalur;
                    const kartu = el?.firstElementChild;
                    if (!kartu) return;
                    this.perLayar = Math.max(1, Math.round((el.clientWidth + JARAK) / (kartu.offsetWidth + JARAK)));
                    this.saatGulir();
                },
                keHalaman(n) {
                    const total = this.jumlahHalaman;
                    this.halaman = (n + total) % total;
                    this.$refs.jalur.scrollTo({ left: this.halaman * this.lebarHalaman(), behavior: 'smooth' });
                },
                saatGulir() {
                    const el = this.$refs.jalur;
                    if (!el) return;
                    const ujung = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
                    this.halaman = ujung ? this.jumlahHalaman - 1 : Math.round(el.scrollLeft / this.lebarHalaman());
                },
                // Disentuh di HP: berhenti sebentar, lalu jalan lagi
                sentuh() {
                    this.jeda = true;
                    clearTimeout(this._lanjut);
                    this._lanjut = setTimeout(() => { this.jeda = false; }, 6000);
                },
                geserBesar(arah) { this.aktif = (this.aktif + arah + this.foto.length) % this.foto.length; },
            },
            mounted() {
                this.$nextTick(this.ukur);
                window.addEventListener('resize', () => this.ukur());
                setInterval(() => {
                    if (!this.jeda && this.aktif === null && this.jumlahHalaman > 1 && !document.hidden) this.keHalaman(this.halaman + 1);
                }, 4500);
                document.addEventListener('keydown', (e) => {
                    if (this.aktif === null) return;
                    if (e.key === 'Escape') this.aktif = null;
                    if (e.key === 'ArrowLeft') this.geserBesar(-1);
                    if (e.key === 'ArrowRight') this.geserBesar(1);
                });
            },
        }).mount('#galeri-landing');
    });
</script>
@endif
