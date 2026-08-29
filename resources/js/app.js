import "../css/app.css";

// Vue dimuat lewat file lokal public/js/vue.global.prod.js (lihat layouts/app.blade.php),
// diakses via window.Vue — bukan "import { createApp } from 'vue'" (tidak butuh npm install vue).
if (!window.Vue) {
    console.error(
        '[Ribath] window.Vue tidak ditemukan. Pastikan <script src=".../js/vue.global.prod.js"> ' +
            "ada di <head> SEBELUM @vite(...), dan file public/js/vue.global.prod.js benar-benar ada.",
    );
}
const { createApp } = window.Vue || {};

/**
 * Navbar: dropdown desktop (Profil/Informasi/Bantuan) + drawer menu mobile.
 * Vue di-mount langsung di atas markup Blade yang sudah ada (in-DOM template),
 * jadi HTML tetap ditulis di navbar.blade.php dan Vue hanya menambahkan perilaku.
 */
const navbarEl = document.getElementById("navbar-app");
if (navbarEl && createApp) {
    const app = createApp({
        data() {
            return {
                openMenu: null,
                mobileOpen: false,
            };
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
