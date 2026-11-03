{{--
    Pita Pengumuman di dasbor asatid / peserta (Admin > Landing Page > Pita Pengumuman, pilih tujuan).
    Tampil di atas isi halaman (di bawah header), tidak mengambang. Tampilan sama dengan pita landing page
    (partials/pita-pengumuman-tampilan): hitung mundur, bergantian bila lebih dari satu, tombol × menutup
    sampai browser ditutup (muncul lagi bila pengumumannya diubah admin).
    Pemakaian: @include('partials.pita-dasbor', ['tujuan' => 'asatid' | 'siswa'])
--}}
@php
$pitaDasbor = rescue(
    fn () => \App\Models\PitaPengumuman::tampilSekarang($tujuan)->get()->map->untukPublik()->all(),
    [], false
);
@endphp

@if ($pitaDasbor)
<style>[v-cloak] { display: none !important; }</style>
<div id="pita-dasbor" v-cloak @mouseenter="jeda = true" @mouseleave="jeda = false">
    <div v-if="pita" :key="pita.versi" class="mb-5 rounded-2xl overflow-hidden shadow-[0_10px_30px_-14px_rgba(11,51,44,0.45)] ring-1 ring-black/5">
        @include('partials.pita-pengumuman-tampilan')
    </div>
</div>

@include('partials.pita-pengumuman-gaya')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('pita-dasbor');
        if (!el || !window.Vue) return;

        const KUNCI = 'pita_ditutup';
        const bacaTertutup = () => {
            try { return JSON.parse(sessionStorage.getItem(KUNCI) || '[]'); } catch (e) { return []; }
        };

        window.Vue.createApp({
            mixins: [window.mixinPita],
            data() {
                return { semua: @json($pitaDasbor), indeks: 0, tertutup: bacaTertutup(), jeda: false, bisaTutup: true };
            },
            computed: {
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
                this.putar = setInterval(() => {
                    if (!this.jeda && this.banyakPita > 1) this.indeks++;
                }, 7000);
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
    });
</script>
@endif
