{{--
    Widget kecil mengambang (pojok kanan bawah) di semua halaman:
    - Titik status koneksi internet: hijau = Online, kuning = Lambat, merah = Offline (+ waktu respons server).
      Diperiksa saat browser online/offline, dan tiap 20 detik ke /up (rute kesehatan Laravel).
    - Tombol muat ulang halaman.
    Klik titik untuk menampilkan/menyembunyikan keterangan. Saat offline / baru tersambung lagi, keterangan muncul sendiri.
    Pemakaian: @include('partials.status-koneksi')  atau  @include('partials.status-koneksi', ['posisi' => 'bottom-24 right-5'])
--}}
<style>[v-cloak] { display: none !important; }</style>
<div id="status-koneksi" v-cloak class="fixed z-[60] {{ $posisi ?? 'bottom-5 right-5' }} print:hidden">
    <div class="flex items-center gap-0.5 rounded-full bg-white/90 backdrop-blur-md p-1 shadow-[0_8px_24px_-8px_rgba(11,51,44,0.35)] ring-1 transition-colors"
        :class="gaya.cincin">
        {{-- Status koneksi --}}
        <button type="button" @click="buka = !buka" :title="judul" :aria-label="judul"
            class="flex items-center gap-2 h-8 rounded-full pl-2.5 transition-all" :class="tampilLabel ? 'pr-3' : 'pr-2.5'">
            <span class="relative flex w-2.5 h-2.5">
                <span v-if="status !== 'online'" class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" :class="gaya.titik"></span>
                <span class="relative inline-flex w-2.5 h-2.5 rounded-full" :class="gaya.titik"></span>
            </span>
            <span v-if="tampilLabel" class="text-xs font-semibold whitespace-nowrap" :class="gaya.teks" v-text="label"></span>
        </button>

        {{-- Muat ulang --}}
        <button type="button" @click="muatUlang" title="{{ __('Muat ulang halaman') }}" aria-label="{{ __('Muat ulang halaman') }}"
            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
            <svg class="w-4 h-4" :class="memuat ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('status-koneksi');
        if (!el || !window.Vue) return;

        const ALAMAT_CEK = @json(url('/up'));
        const BATAS_LAMBAT = 1500; // ms

        window.Vue.createApp({
            data() {
                return {
                    status: navigator.onLine ? 'online' : 'offline', // online | lambat | offline
                    ms: null,         // waktu respons server terakhir
                    buka: false,      // keterangan ditampilkan (klik titik)
                    baruPulih: false, // sebentar menampilkan "Tersambung kembali"
                    memuat: false,
                };
            },
            computed: {
                gaya() {
                    return {
                        online: { titik: 'bg-emerald-500', teks: 'text-emerald-700', cincin: 'ring-slate-200' },
                        lambat: { titik: 'bg-amber-500', teks: 'text-amber-700', cincin: 'ring-amber-200' },
                        offline: { titik: 'bg-rose-500', teks: 'text-rose-700', cincin: 'ring-rose-300' },
                    }[this.status];
                },
                label() {
                    if (this.status === 'offline') return @js(__('Offline · tidak ada internet'));
                    if (this.baruPulih) return @js(__('Tersambung kembali'));
                    const waktu = this.ms === null ? '' : ' · ' + (this.ms >= 1000 ? (this.ms / 1000).toFixed(1) + ' ' + @js(__('dtk')) : this.ms + ' ms');
                    return (this.status === 'lambat' ? @js(__('Koneksi lambat')) : @js(__('Online'))) + waktu;
                },
                judul() {
                    return this.label + ' ' + (this.buka ? @js(__('(klik untuk menyembunyikan keterangan)')) : @js(__('(klik untuk menampilkan keterangan)')));
                },
                tampilLabel() {
                    return this.buka || this.status !== 'online' || this.baruPulih;
                },
            },
            mounted() {
                window.addEventListener('online', this.saatOnline);
                window.addEventListener('offline', this.saatOffline);
                document.addEventListener('visibilitychange', () => { if (!document.hidden) this.cek(); });
                this.cek();
                this.jadwal = setInterval(() => { if (!document.hidden) this.cek(); }, 20000);
            },
            methods: {
                // Ukur koneksi ke server (permintaan kecil, tidak disimpan cache)
                async cek() {
                    if (!navigator.onLine) return this.saatOffline();
                    const henti = new AbortController();
                    const batas = setTimeout(() => henti.abort(), 8000);
                    const mulai = performance.now();
                    try {
                        const res = await fetch(ALAMAT_CEK, { method: 'HEAD', cache: 'no-store', signal: henti.signal });
                        this.ms = Math.round(performance.now() - mulai);
                        const tadiOffline = this.status === 'offline';
                        this.status = !res.ok && res.status >= 500 ? 'lambat' : (this.ms > BATAS_LAMBAT ? 'lambat' : 'online');
                        if (tadiOffline) this.tandaiPulih();
                    } catch (e) {
                        this.ms = null;
                        this.status = navigator.onLine && e.name === 'AbortError' ? 'lambat' : 'offline';
                    } finally {
                        clearTimeout(batas);
                    }
                },
                saatOnline() {
                    this.tandaiPulih();
                    this.cek();
                },
                saatOffline() {
                    this.status = 'offline';
                    this.ms = null;
                },
                tandaiPulih() {
                    this.baruPulih = true;
                    clearTimeout(this.jedaPulih);
                    this.jedaPulih = setTimeout(() => (this.baruPulih = false), 4000);
                },
                muatUlang() {
                    this.memuat = true;
                    location.reload();
                },
            },
        }).mount(el);
    });
</script>
