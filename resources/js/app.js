import "../css/app.css";
import { createApp, reactive, ref, watch, computed } from "vue";

// Sediakan Vue untuk skrip yang masih berada di file Blade.
// Fungsi Vue yang dipakai di Blade (const { ... } = Vue) harus ada di sini, kalau tidak halamannya kosong.
window.Vue = {
    createApp,
    reactive,
    ref,
    watch,
    computed,
};

// Pemindai QR (absensi kegiatan). Dimuat hanya saat dibutuhkan supaya halaman lain tetap ringan.
window.muatPemindaiQr = () => import("html5-qrcode");

// Navbar dan animasi statistik tetap di bawah sini.

/**
 * Navbar: dropdown desktop (Profil/Informasi/Bantuan) + drawer menu mobile.
 * Vue di-mount langsung di atas markup Blade yang sudah ada (in-DOM template),
 * jadi HTML tetap ditulis di navbar.blade.php dan Vue hanya menambahkan perilaku.
 */
const navbarEl = document.getElementById("navbar-app");
if (navbarEl && createApp) {
    // Dibaca SEBELUM mount: Vue mengosongkan isi #navbar-app saat memasang template in-DOM
    const navbarTransparan = navbarEl.querySelector("header")?.dataset.transparan === "1";
    const app = createApp({
        data() {
            return {
                openMenu: null,
                mobileOpen: false,
                // Halaman sudah digulir? (navbar landing berubah dari transparan jadi kapsul)
                tergulir: window.scrollY > 24,
                // Navbar transparan di atas hero hanya di landing page (data-transparan="1")
                transparan: navbarTransparan,
            };
        },
        computed: {
            // Bentuk kapsul putih mengambang
            padat() {
                return !this.transparan || this.tergulir || this.mobileOpen;
            },
        },
        mounted() {
            window.addEventListener(
                "scroll",
                () => {
                    this.tergulir = window.scrollY > 24;
                },
                { passive: true },
            );
        },
        methods: {
            toggleMenu(name) {
                this.openMenu = this.openMenu === name ? null : name;
            },
        },
        watch: {
            // Kunci scroll body selagi drawer mobile terbuka.
            mobileOpen(isOpen) {
                document.body.classList.toggle("overflow-hidden", isOpen);
            },
        },
    });
    const vm = app.mount(navbarEl);

    // Tutup dropdown desktop kalau user klik di luar area navbar/dropdown.
    document.addEventListener("click", (event) => {
        if (
            !event.target.closest("[data-dropdown]") &&
            !event.target.closest("#navbar-app button")
        ) {
            vm.openMenu = null;
        }
    });
}

/**
 * Animasi count-up untuk statistik di hero ("200+ Santri Aktif", "15 Tahun Berpengalaman").
 */
const statEls = document.querySelectorAll(".stat-number");
if (statEls.length) {
    const animate = (el) => {
        const target = parseInt(el.dataset.target, 10) || 0;
        const duration = 1200;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(progress * target);
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.5 },
    );

    statEls.forEach((el) => observer.observe(el));
}
