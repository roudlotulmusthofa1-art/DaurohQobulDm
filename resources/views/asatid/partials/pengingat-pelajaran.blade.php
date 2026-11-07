{{--
    Pengingat ganti pelajaran untuk asatid (dimuat di layouts/asatid, tampil di semua halaman asatid).
    - Data: jadwal mengajar guru ini (AsatidDashboardController::dataPengingat), diperbarui tiap 30 menit.
    - Tiap 20 detik dicocokkan dengan jam di HP guru:
        * 5 menit sebelum jam mulai -> "Sebentar lagi ..."
        * saat jam mulai           -> "Pelajaran sudah dimulai" + tombol "Absen sekarang"
      Muncul jendela + bunyi alarm + getar HP (+ notifikasi browser kalau diizinkan). Tiap pengingat hanya sekali.
    - Tanda dari admin (dasbor admin > Pergantian Pelajaran) dicek tiap 20 detik -> jendela "Admin: sudah pergantian pelajaran";
      ditutup / Absen sekarang = admin melihat "Dilihat guru".
    - Hanya bekerja selama web asatid terbuka (boleh di tab belakang). Bunyi baru bisa keluar setelah halaman disentuh sekali.
    Vue: #pengingat-pelajaran.
--}}
@php
$pengingatData = \App\Http\Controllers\Asatid\AsatidDashboardController::dataPengingat();
@endphp

<div id="pengingat-pelajaran" v-cloak>
    <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-6"
        leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0 translate-y-6">
        <div v-if="tampil" class="fixed inset-x-0 bottom-0 z-[60] p-4 sm:p-6 flex justify-center pointer-events-none">
            <div role="alertdialog" aria-live="assertive"
                class="pointer-events-auto w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-[0_24px_64px_-16px_rgba(11,51,44,0.55)] ring-1 ring-black/5">
                <div class="relative p-5 text-white" :class="tampil.tipe === 'admin' ? 'bg-gradient-to-br from-[#135347] to-[#071f1a]' : (tampil.tipe === 'mulai' ? 'bg-gradient-to-br from-emerald-500 to-teal-700' : 'bg-gradient-to-br from-amber-400 to-orange-600')">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 shrink-0 rounded-2xl bg-white/20 flex items-center justify-center animate-bounce">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80">{{ __('Pengingat pelajaran') }}</p>
                            <p class="text-lg font-extrabold leading-tight" v-text="judul(tampil)"></p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-base font-bold text-slate-800" v-text="[tampil.kelas, tampil.kitab].filter(Boolean).join(' · ')"></p>
                    <p class="mt-0.5 text-sm text-slate-500" v-text="[tampil.jam, tampil.mulai ? tampil.mulai + (tampil.selesai ? '–' + tampil.selesai : '') : null].filter(Boolean).join(' · ')"></p>

                    <button v-if="izin === 'default'" type="button" @click="mintaIzin"
                        class="mt-3 w-full rounded-2xl bg-sky-50 px-4 py-2.5 text-xs font-semibold text-sky-700 ring-1 ring-sky-200 hover:bg-sky-100">
                        {{ __('Izinkan notifikasi supaya pengingat juga muncul di HP') }}
                    </button>

                    <div class="mt-4 flex gap-2">
                        <button type="button" @click="tutup" class="px-4 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Tutup') }}</button>
                        <a :href="alamatAbsen(tampil)" @click="tutup"
                            class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-extrabold hover:bg-[#135347]">
                            {{ __('Absen sekarang') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ALAMAT = @json(route('asatid.jadwal.pengingat'));
    const CEK_TANDA = @json(route('asatid.tanda.cek'));
    const LIHAT_TANDA = @json(route('asatid.tanda.lihat', ['id' => '__ID__']));
    const CSRF = @json(csrf_token());
    const SEBELUM = 5;   // menit sebelum jam mulai
    const JENDELA = 5;   // menit: pengingat masih dimunculkan kalau HP sempat "tidur" sebentar
    const dua = (n) => String(n).padStart(2, '0');
    const tanggalIni = (d) => `${d.getFullYear()}-${dua(d.getMonth() + 1)}-${dua(d.getDate())}`;
    const keMenit = (jam) => { const [h, m] = (jam || '0:0').split(':').map(Number); return h * 60 + m; };

    // Pengingat yang sudah tampil disimpan di HP (supaya tidak berulang walau halaman dibuka ulang / pindah halaman)
    const sudah = new Set();
    const pernah = (k) => { try { return sudah.has(k) || localStorage.getItem(k) === '1'; } catch (e) { return sudah.has(k); } };
    const tandai = (k) => { sudah.add(k); try { localStorage.setItem(k, '1'); } catch (e) { /* abaikan */ } };

    // Bunyi alarm pendek (dibuat sendiri, tanpa file suara)
    let audio = null;
    const siapkanAudio = () => {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            if (audio.state === 'suspended') audio.resume();
        } catch (e) { audio = null; }
    };
    ['click', 'touchstart', 'keydown'].forEach((ev) => document.addEventListener(ev, siapkanAudio, { once: true, passive: true }));
    const bunyi = () => {
        if (!audio || audio.state !== 'running') return;
        [0, 0.35, 0.7, 1.4, 1.75, 2.1].forEach((t) => {
            const o = audio.createOscillator();
            const g = audio.createGain();
            o.type = 'sine';
            o.frequency.value = 880;
            g.gain.setValueAtTime(0.0001, audio.currentTime + t);
            g.gain.exponentialRampToValueAtTime(0.35, audio.currentTime + t + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + t + 0.25);
            o.connect(g).connect(audio.destination);
            o.start(audio.currentTime + t);
            o.stop(audio.currentTime + t + 0.3);
        });
    };

    window.Vue.createApp({
        data: () => ({
            jadwal: @json($pengingatData),
            tampil: null,
            izin: 'Notification' in window ? Notification.permission : 'tidak-ada',
        }),
        mounted() {
            this.periksa();
            this.cekTanda();
            // Tiap 20 detik: cocokkan jadwal dengan jam HP + cek tanda dari admin
            setInterval(() => { this.periksa(); this.cekTanda(); }, 20000);
            setInterval(this.muatUlang, 30 * 60000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) { this.periksa(); this.cekTanda(); } });
        },
        methods: {
            judul(p) {
                if (p.tipe === 'admin') return @js(__('Admin: sudah pergantian pelajaran'));
                return p.tipe === 'mulai' ? @js(__('Pelajaran sudah dimulai')) : @js(__('Sebentar lagi pelajaran dimulai'));
            },
            // Tanda ganti pelajaran dari admin (dasbor admin > Pergantian Pelajaran)
            async cekTanda() {
                if (this.tampil?.tipe === 'admin') return;
                try {
                    const res = await fetch(CEK_TANDA, { headers: { Accept: 'application/json' } });
                    if (!res.ok) return;
                    const t = (await res.json()).tanda;
                    if (!t || sudah.has('tanda:' + t.kunci)) return;
                    sudah.add('tanda:' + t.kunci);
                    this.munculkan({ ...t, tipe: 'admin' });
                } catch (e) { /* offline: coba lagi nanti */ }
            },
            // Cocokkan jam di HP dengan jadwal hari ini
            periksa() {
                const now = new Date();
                const hari = now.getDay() || 7; // 1 = Senin ... 7 = Ahad
                const menit = now.getHours() * 60 + now.getMinutes();
                const tgl = tanggalIni(now);
                for (const j of this.jadwal) {
                    if (j.hari !== hari) continue;
                    const mulai = keMenit(j.mulai);
                    const tipe = menit >= mulai && menit < mulai + JENDELA ? 'mulai'
                        : (menit >= mulai - SEBELUM && menit < mulai ? 'segera' : null);
                    if (!tipe) continue;
                    const kunci = `pengingat-pelajaran:${tgl}:${j.id}:${tipe}`;
                    if (pernah(kunci)) continue;
                    tandai(kunci);
                    this.munculkan({ ...j, tipe });
                    break;
                }
            },
            munculkan(p) {
                this.tampil = p;
                bunyi();
                try { navigator.vibrate?.([400, 150, 400, 150, 400]); } catch (e) { /* abaikan */ }
                // Notifikasi browser (kalau diizinkan), berguna saat web ada di tab belakang
                if (this.izin === 'granted' && document.hidden) {
                    try {
                        const n = new Notification(this.judul(p), {
                            body: [p.jam, p.kelas, p.kitab, p.mulai].filter(Boolean).join(' · '),
                            tag: 'pengingat-pelajaran',
                        });
                        n.onclick = () => { window.focus(); n.close(); };
                    } catch (e) { /* sebagian HP hanya mengizinkan lewat service worker */ }
                }
            },
            tutup() {
                // Tanda dari admin: beri tahu admin bahwa guru sudah melihat
                if (this.tampil?.tipe === 'admin') {
                    fetch(LIHAT_TANDA.replace('__ID__', this.tampil.id), {
                        method: 'POST', keepalive: true,
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                    }).catch(() => {});
                }
                this.tampil = null;
            },
            alamatAbsen(p) {
                const url = new URL(p.absen);
                url.searchParams.set('tanggal', tanggalIni(new Date()));
                return url.toString();
            },
            async mintaIzin() {
                try { this.izin = await Notification.requestPermission(); } catch (e) { /* abaikan */ }
            },
            // Jadwal bisa diubah admin: ambil ulang tiap 30 menit
            async muatUlang() {
                try {
                    const res = await fetch(ALAMAT, { headers: { Accept: 'application/json' } });
                    if (res.ok) this.jadwal = (await res.json()).jadwal || [];
                } catch (e) { /* offline: pakai jadwal lama */ }
            },
        },
    }).mount('#pengingat-pelajaran');
});
</script>
