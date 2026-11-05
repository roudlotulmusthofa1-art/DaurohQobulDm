{{--
    Notifikasi modern di pojok kanan bawah (semua halaman). Menutup sendiri, bisa ditahan (arahkan kursor / sentuh),
    bisa ditutup dengan ✕. Paling banyak 4 bertumpuk.

    Otomatis dari server (tidak perlu ditulis di tiap halaman):
        ->with('success' / 'sukses' / 'status', '...')  -> hijau
        ->with('error', '...')                           -> merah
        ->with('warning', '...')                         -> kuning
        $errors (validasi formulir)                      -> merah: "Ada N isian yang belum benar" + pesan pertama

    Dari JavaScript di halaman mana pun:
        notifikasi.sukses('Tersimpan.')            notifikasi.gagal('Gagal menyimpan.', 'Judul opsional')
        notifikasi.peringatan('...')               notifikasi.info('...')
        notifikasi.tampil({ jenis: 'gagal', judul: '...', pesan: '...', lama: 10000 })

    Pemakaian: @include('partials.notifikasi')  atau  @include('partials.notifikasi', ['bawah' => 'bottom-36 lg:bottom-16'])
    $bawah = jarak dari bawah layar (di atas tombol koneksi / menu bawah HP).
    Tanpa Vue (JavaScript biasa) supaya jalan di semua halaman. Memakai "popover" agar tampil di atas pop-up <dialog>.
--}}
<style>
    /* Bawaan browser untuk popover: inset 0 + margin auto (ke tengah). Dibatalkan; posisi kanan/bawah dari class Tailwind */
    #notifikasi-wadah { top: auto; margin: 0; padding: 0; border: 0; background: transparent; overflow: visible; }
    .notif-kartu { animation: notif-masuk .35s cubic-bezier(.2, .9, .3, 1.2); }
    .notif-kartu.notif-keluar { animation: notif-keluar .25s ease-in forwards; }
    .notif-garis { transform-origin: left; animation: notif-hitung linear forwards; }
    .notif-kartu.notif-tahan .notif-garis { animation-play-state: paused; }
    [dir="rtl"] .notif-garis { transform-origin: right; }
    @keyframes notif-masuk { from { opacity: 0; transform: translateY(16px) scale(.96); } }
    @keyframes notif-keluar { to { opacity: 0; transform: translateX(24px) scale(.96); } }
    @keyframes notif-hitung { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    @media (prefers-reduced-motion: reduce) { .notif-kartu, .notif-kartu.notif-keluar { animation: none; } }
</style>

<div id="notifikasi-wadah" popover="manual" aria-live="polite"
    class="fixed z-[90] right-3 left-3 sm:left-auto sm:right-5 {{ $bawah ?? 'bottom-16' }} w-auto sm:w-[360px] h-auto flex flex-col gap-2.5 pointer-events-none print:hidden"></div>

<script>
    (function () {
        // Gaya per jenis (ditulis lengkap supaya Tailwind ikut membuat class-nya)
        const GAYA = {
            sukses:     { judul: @js(__('Berhasil')),   ikon: 'bg-emerald-50 text-emerald-600 ring-emerald-100', garis: 'bg-emerald-500', lama: 5000,
                          svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>' },
            gagal:      { judul: @js(__('Gagal')),      ikon: 'bg-rose-50 text-rose-600 ring-rose-100',          garis: 'bg-rose-500',    lama: 8000,
                          svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>' },
            peringatan: { judul: @js(__('Perhatian')),  ikon: 'bg-amber-50 text-amber-600 ring-amber-100',       garis: 'bg-amber-500',   lama: 7000,
                          svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>' },
            info:       { judul: @js(__('Info')),       ikon: 'bg-sky-50 text-sky-600 ring-sky-100',             garis: 'bg-sky-500',     lama: 5000,
                          svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>' },
        };
        const MAKS = 4;
        const teksAman = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        function wadah() {
            const w = document.getElementById('notifikasi-wadah');
            // Saat pop-up <dialog> terbuka, isi di luar pop-up tidak bisa diklik (tombol ✕ mati).
            // Jadi wadah dipindah ke dalam pop-up teratas; bila tidak ada pop-up, kembali ke <body>.
            const modal = [...document.querySelectorAll('dialog[open]')].reverse().find((d) => { try { return d.matches(':modal'); } catch (_) { return false; } });
            const tujuan = modal || document.body;
            const pindah = w.parentElement !== tujuan;
            if (pindah) tujuan.appendChild(w);
            // Popover = lapisan paling atas (di atas <dialog> yang sedang terbuka). Dibuka bila belum / baru dipindah.
            try { if (w.showPopover && (pindah || !w.matches(':popover-open'))) { if (w.matches(':popover-open')) w.hidePopover(); w.showPopover(); } } catch (_) {}
            return w;
        }

        function tutup(kartu) {
            if (!kartu || kartu.dataset.tutup) return;
            kartu.dataset.tutup = '1';
            kartu.classList.add('notif-keluar');
            setTimeout(() => {
                kartu.remove();
                const w = document.getElementById('notifikasi-wadah');
                if (w && !w.children.length) { try { w.hidePopover?.(); } catch (_) {} }
            }, 250);
        }

        function tampil({ jenis = 'info', judul = null, pesan = '', lama = null } = {}) {
            const g = GAYA[jenis] || GAYA.info;
            const durasi = lama ?? g.lama;
            const w = wadah();
            while (w.children.length >= MAKS) tutup(w.firstElementChild);

            const kartu = document.createElement('div');
            kartu.className = 'notif-kartu pointer-events-auto overflow-hidden rounded-2xl bg-white/95 backdrop-blur-md ring-1 ring-slate-200/80 shadow-[0_20px_40px_-16px_rgba(11,51,44,0.35)]';
            kartu.setAttribute('role', jenis === 'gagal' ? 'alert' : 'status');
            kartu.innerHTML = `
                <div class="flex items-start gap-3 px-4 py-3.5">
                    <span class="w-9 h-9 shrink-0 rounded-full ring-1 flex items-center justify-center ${g.ikon}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">${g.svg}</svg>
                    </span>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-bold text-slate-800">${teksAman(judul || g.judul)}</p>
                        ${pesan ? `<p class="mt-0.5 text-[13px] leading-snug text-slate-600 break-words whitespace-pre-line">${teksAman(pesan)}</p>` : ''}
                    </div>
                    <button type="button" aria-label="${teksAman(@js(__('Tutup')))}"
                        class="shrink-0 -mr-1 -mt-0.5 w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-1 bg-slate-100"><div class="notif-garis h-full ${g.garis}" style="animation-duration:${durasi}ms"></div></div>`;

            kartu.querySelector('button').addEventListener('click', () => tutup(kartu));

            // Tutup sendiri: penghitung waktu biasa (garis di bawah hanya tampilan).
            // Ditahan saat kursor di atas notifikasi / saat disentuh di HP, lalu lanjut dari sisa waktunya.
            let sisa = durasi, mulai = Date.now(), jeda = setTimeout(() => tutup(kartu), sisa);
            const tahan = () => {
                if (kartu.classList.contains('notif-tahan')) return;
                clearTimeout(jeda);
                sisa = Math.max(800, sisa - (Date.now() - mulai));
                kartu.classList.add('notif-tahan');
            };
            const lanjut = () => {
                if (!kartu.classList.contains('notif-tahan')) return;
                kartu.classList.remove('notif-tahan');
                mulai = Date.now();
                jeda = setTimeout(() => tutup(kartu), sisa);
            };
            kartu.addEventListener('mouseenter', tahan);
            kartu.addEventListener('mouseleave', lanjut);
            kartu.addEventListener('touchstart', tahan, { passive: true });
            kartu.addEventListener('touchend', () => setTimeout(lanjut, 1500), { passive: true });

            w.appendChild(kartu);
            return kartu;
        }

        window.notifikasi = {
            tampil,
            sukses: (pesan, judul) => tampil({ jenis: 'sukses', pesan, judul }),
            gagal: (pesan, judul) => tampil({ jenis: 'gagal', pesan, judul }),
            peringatan: (pesan, judul) => tampil({ jenis: 'peringatan', pesan, judul }),
            info: (pesan, judul) => tampil({ jenis: 'info', pesan, judul }),
        };

        // ===== Pesan dari server (setelah simpan / pindah halaman) =====
        @php
            $antrianNotif = [];
            foreach (['success' => 'sukses', 'sukses' => 'sukses', 'status' => 'sukses', 'error' => 'gagal', 'warning' => 'peringatan'] as $kunciSesi => $jenisNotif) {
                $isiSesi = session($kunciSesi);
                if (is_string($isiSesi) && filled($isiSesi)) {
                    $antrianNotif[] = ['jenis' => $jenisNotif, 'pesan' => $isiSesi];
                }
            }
            if (isset($errors) && $errors->any()) {
                $jumlahError = $errors->count();
                $antrianNotif[] = [
                    'jenis' => 'gagal',
                    'judul' => $jumlahError > 1 ? __('Ada :jumlah isian yang belum benar', ['jumlah' => $jumlahError]) : __('Isian belum benar'),
                    'pesan' => $errors->first(),
                ];
            }
        @endphp
        const dariServer = @json($antrianNotif);
        if (dariServer.length) {
            const mulai = () => dariServer.forEach((n, i) => setTimeout(() => tampil(n), 150 + i * 200));
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', mulai) : mulai();
        }
    })();
</script>
