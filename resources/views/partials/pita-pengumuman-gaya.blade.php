{{--
    Pita Pengumuman: warna + hitung mundur (mixin Vue window.mixinPita).
    Dipakai di landing page (partials/pita-pengumuman) dan pratinjau di admin (admin/landing-pengumuman).
    Kelas Tailwind ditulis lengkap di sini supaya ikut dibuat oleh Tailwind (kunci warna ada di App\Models\PitaPengumuman::WARNA).
--}}
<script>
    window.PITA_GAYA = {
        hijau: { nama: @js(__('Hijau')), contoh: 'bg-emerald-700', latar: 'bg-linear-to-r from-emerald-900 via-teal-700 to-emerald-900 text-white', lencana: 'bg-white/15 ring-1 ring-white/25', kotak: 'bg-black/25', tombol: 'bg-amber-400 text-emerald-950 hover:bg-amber-300', titik: 'bg-amber-300' },
        emas:  { nama: @js(__('Emas')),  contoh: 'bg-amber-500',   latar: 'bg-linear-to-r from-amber-500 via-yellow-400 to-amber-500 text-amber-950', lencana: 'bg-amber-950/10 ring-1 ring-amber-950/20', kotak: 'bg-amber-950/15', tombol: 'bg-amber-950 text-amber-50 hover:bg-amber-900', titik: 'bg-amber-950' },
        biru:  { nama: @js(__('Biru')),  contoh: 'bg-blue-700',    latar: 'bg-linear-to-r from-sky-800 via-blue-700 to-indigo-800 text-white', lencana: 'bg-white/15 ring-1 ring-white/25', kotak: 'bg-black/25', tombol: 'bg-white text-blue-800 hover:bg-blue-50', titik: 'bg-sky-300' },
        merah: { nama: @js(__('Merah')), contoh: 'bg-rose-600',    latar: 'bg-linear-to-r from-rose-700 via-red-600 to-rose-700 text-white', lencana: 'bg-white/15 ring-1 ring-white/25', kotak: 'bg-black/25', tombol: 'bg-white text-rose-700 hover:bg-rose-50', titik: 'bg-yellow-300' },
        ungu:  { nama: @js(__('Ungu')),  contoh: 'bg-violet-700',  latar: 'bg-linear-to-r from-violet-800 via-purple-700 to-fuchsia-800 text-white', lencana: 'bg-white/15 ring-1 ring-white/25', kotak: 'bg-black/25', tombol: 'bg-white text-violet-800 hover:bg-violet-50', titik: 'bg-fuchsia-300' },
        gelap: { nama: @js(__('Gelap')), contoh: 'bg-slate-900',   latar: 'bg-linear-to-r from-slate-950 via-slate-800 to-slate-950 text-white', lencana: 'bg-white/10 ring-1 ring-white/20', kotak: 'bg-white/10', tombol: 'bg-emerald-400 text-slate-950 hover:bg-emerald-300', titik: 'bg-emerald-400' },
    };

    // Warna sendiri (latar dari kode warna admin): kelas tulisan & tombol mengikuti terang/gelapnya warna
    window.PITA_GAYA_SENDIRI = {
        gelap:  { nama: @js(__('Warna sendiri')), contoh: '', latar: 'text-white', lencana: 'bg-white/15 ring-1 ring-white/25', kotak: 'bg-black/25', tombol: 'bg-white text-slate-900 hover:bg-slate-100', titik: 'bg-white' },
        terang: { nama: @js(__('Warna sendiri')), contoh: '', latar: 'text-slate-900', lencana: 'bg-black/10 ring-1 ring-black/15', kotak: 'bg-black/10', tombol: 'bg-slate-900 text-white hover:bg-slate-800', titik: 'bg-slate-900' },
    };

    // Ikon per jenis (gaya Heroicons outline)
    window.PITA_IKON = {
        pendaftaran: 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46',
        dauroh: 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        info: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    };

    window.mixinPita = {
        data() {
            return { sekarangPita: Date.now(), detakPita: null };
        },
        mounted() {
            this.detakPita = setInterval(() => (this.sekarangPita = Date.now()), 1000);
        },
        beforeUnmount() {
            clearInterval(this.detakPita);
        },
        methods: {
            gayaPita(p) {
                if (p.warna === 'sendiri') return this.terangPita(p) ? window.PITA_GAYA_SENDIRI.terang : window.PITA_GAYA_SENDIRI.gelap;
                return window.PITA_GAYA[p.warna] || window.PITA_GAYA.hijau;
            },
            // Warna sendiri: apakah warnanya terang? (menentukan tulisan hitam / putih)
            terangPita(p) {
                const hex = /^#[0-9a-f]{6}$/i.test(p.warna_hex || '') ? p.warna_hex : '#0f766e';
                const [r, g, b] = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
                return (r * 299 + g * 587 + b * 114) / 1000 > 160;
            },
            // Latar warna sendiri (gradasi dari warna pilihan ke versi lebih gelap); warna bawaan pakai kelas Tailwind
            latarPita(p) {
                if (p.warna !== 'sendiri') return {};
                const hex = /^#[0-9a-f]{6}$/i.test(p.warna_hex || '') ? p.warna_hex : '#0f766e';
                return { background: 'linear-gradient(90deg, ' + hex + ', color-mix(in srgb, ' + hex + ' 72%, black))' };
            },
            ikonPita(p) {
                return window.PITA_IKON[p.jenis] || window.PITA_IKON.info;
            },
            // akan (belum mulai) | berlangsung | selesai
            fasePita(p) {
                const mulai = Date.parse(p.mulai);
                const selesai = Date.parse(p.selesai);
                if (!isNaN(selesai) && this.sekarangPita >= selesai) return 'selesai';
                if (!isNaN(mulai) && this.sekarangPita < mulai) return 'akan';
                return 'berlangsung';
            },
            labelSisaPita(p) {
                return this.fasePita(p) === 'akan' ? @js(__('Dimulai dalam')) : @js(__('Berakhir dalam'));
            },
            sisaPita(p) {
                const target = Date.parse(this.fasePita(p) === 'akan' ? p.mulai : p.selesai);
                const detik = isNaN(target) ? 0 : Math.max(0, Math.floor((target - this.sekarangPita) / 1000));
                const dua = (n) => String(n).padStart(2, '0');
                return {
                    hari: Math.floor(detik / 86400),
                    jam: dua(Math.floor((detik % 86400) / 3600)) + ':' + dua(Math.floor((detik % 3600) / 60)) + ':' + dua(detik % 60),
                };
            },
        },
    };
</script>
