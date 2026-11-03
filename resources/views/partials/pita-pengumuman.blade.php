{{--
    Pita Pengumuman di landing page (diatur di Admin > Landing Page > Pita Pengumuman).
    - Hanya pengumuman yang sedang dijadwalkan tampil (lihat PitaPengumuman::tampilSekarang).
    - Tampilan (berlaku untuk semua pengumuman, PitaPengumuman::tampilan()):
        posisi : atas  = paling atas, navbar ikut turun  | bawah = tepat di bawah top bar (navbar tetap)
        ukuran : penuh = selebar layar                    | ringkas = kapsul kecil di tengah
    - Hitung mundur berjalan tiap detik; lebih dari satu pengumuman -> bergantian tiap 7 detik.
    - Tombol × menutup sampai browser ditutup (muncul lagi bila pengumumannya diubah admin).
    - Variabel CSS: --tinggi-pita-atas (geser navbar) & --tinggi-pita (geser isi halaman).
--}}
@php
$pitaPengumuman = rescue(
    fn () => \App\Models\PitaPengumuman::tampilSekarang('landing')->get()->map->untukPublik()->all(),
    [], false
);
$pitaTampilan = \App\Models\PitaPengumuman::tampilan();
$pitaDiAtas = $pitaTampilan['posisi'] === 'atas';
$pitaRingkas = $pitaTampilan['ukuran'] === 'ringkas';
@endphp

@if ($pitaPengumuman)
<style>[v-cloak] { display: none !important; }</style>
<div id="pita-pengumuman" v-cloak data-posisi="{{ $pitaTampilan['posisi'] }}"
    class="fixed inset-x-0 {{ $pitaDiAtas ? 'top-0 z-[55]' : 'z-[45]' }} {{ $pitaRingkas ? 'pointer-events-none py-2' : '' }}"
    @mouseenter="jeda = true" @mouseleave="jeda = false">
    <div v-if="pita" :key="pita.versi" class="pita-masuk">
        @if ($pitaRingkas)
            @include('partials.pita-pengumuman-ringkas')
        @else
            @include('partials.pita-pengumuman-tampilan')
        @endif
    </div>
</div>

@include('partials.pita-pengumuman-gaya')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('pita-pengumuman');
        if (!el || !window.Vue) return;

        const DI_ATAS = el.dataset.posisi === 'atas';
        const KUNCI = 'pita_ditutup';
        const bacaTertutup = () => {
            try { return JSON.parse(sessionStorage.getItem(KUNCI) || '[]'); } catch (e) { return []; }
        };

        // Atur letak: di atas -> navbar turun setinggi pita; di bawah -> pita menempel di bawah navbar.
        // Isi halaman turun setinggi pita pada keduanya (0 bila pita ditutup / habis).
        const aturTinggi = () => {
            const akar = document.documentElement.style;
            const tinggi = el.querySelector('.pita-masuk') ? el.offsetHeight : 0;
            if (DI_ATAS) {
                akar.setProperty('--tinggi-pita-atas', tinggi + 'px');
            } else {
                const header = document.querySelector('#navbar-app header');
                el.style.top = (header ? header.offsetHeight : 80) + 'px';
            }
            akar.setProperty('--tinggi-pita', tinggi + 'px');
        };

        window.Vue.createApp({
            mixins: [window.mixinPita],
            data() {
                return {
                    semua: @json($pitaPengumuman),
                    indeks: 0,
                    tertutup: bacaTertutup(),
                    jeda: false,      // berhenti bergantian saat kursor di atas pita
                    bisaTutup: true,
                };
            },
            computed: {
                // Yang belum selesai & belum ditutup pengunjung
                tampil() {
                    return this.semua.filter((p) => this.fasePita(p) !== 'selesai' && !this.tertutup.includes(p.versi));
                },
                banyakPita() {
                    return this.tampil.length;
                },
                pita() {
                    return this.banyakPita ? this.tampil[this.indeks % this.banyakPita] : null;
                },
            },
            mounted() {
                // v-cloak baru dilepas Vue sesudah mounted -> ukur setelah itu
                setTimeout(aturTinggi, 0);
                this.putar = setInterval(() => {
                    if (!this.jeda && this.banyakPita > 1) this.indeks++;
                }, 7000);
            },
            watch: {
                // Pita berganti / ditutup -> hitung ulang tingginya setelah tampil
                pita() {
                    this.$nextTick(aturTinggi);
                },
            },
            methods: {
                pitaBerikut() {
                    this.indeks++;
                },
                pitaSebelum() {
                    this.indeks = (this.indeks - 1 + this.banyakPita) % this.banyakPita;
                },
                tutupPita() {
                    this.tertutup.push(this.pita.versi);
                    try { sessionStorage.setItem(KUNCI, JSON.stringify(this.tertutup)); } catch (e) { /* abaikan */ }
                },
            },
        }).mount(el);

        // Tinggi pita juga bisa berubah karena lebar layar (teks turun baris)
        window.addEventListener('resize', aturTinggi);
        if (window.ResizeObserver) new ResizeObserver(aturTinggi).observe(el);
    });
</script>
<style>
    .pita-masuk { animation: pita-masuk .45s ease both; }
    @keyframes pita-masuk { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
</style>
@endif
