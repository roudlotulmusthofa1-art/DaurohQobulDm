{{--
    Font Arab untuk semua dasbor (admin, asatid, peserta): Amiri — gaya Naskh seperti kitab cetak.
    Font-nya didaftarkan di resources/css/app.css (nama "Amiri Kitab", ukuran & daftar huruf Arab diatur di sana).
    Huruf latin tetap Plus Jakarta Sans; setiap huruf Arab (nama Arab, data Arab, terjemahan) otomatis memakai Amiri.
    Saat bahasa Arab: tata letak kanan ke kiri (dir="rtl" di <html>), lihat blok "TAMPILAN KANAN-KE-KIRI" di bawah.
--}}
<style>
    :root { --font-sans: 'Plus Jakarta Sans', 'Amiri Kitab', ui-sans-serif, system-ui, sans-serif; }
    body, input, button, select, textarea { font-family: var(--font-sans); }
    /* Teks Arab lebih lega supaya harakat Amiri (tinggi) tidak terpotong */
    [dir="rtl"], [lang="ar"] { line-height: 1.8; }

    /* ===== TAMPILAN KANAN-KE-KIRI (aktif hanya saat bahasa Arab) ===== */

    /* 1. Teks: yang tadinya rata kiri jadi rata kanan, dan sebaliknya */
    [dir="rtl"] .text-left  { text-align: right; }
    [dir="rtl"] .text-right { text-align: left; }

    /* 2. Menu turun (lonceng & profil): menempel ke kiri, bukan ke kanan */
    [dir="rtl"] .absolute.right-0 { right: auto; left: 0; }

    /* 3. "ml-auto" (dorong ke ujung): ujungnya sekarang di kiri */
    [dir="rtl"] .ml-auto { margin-left: 0; margin-right: auto; }

    /* 4. Garis tepi submenu sidebar: pindah dari kiri ke kanan */
    [dir="rtl"] .border-l { border-left-width: 0; border-right-width: 1px; }
    [dir="rtl"] #sidebar .border-l { margin-left: 0; margin-right: 1.625rem; padding-left: 0; padding-right: 1rem; }

    /* 5. Panah: dicerminkan supaya menunjuk ke arah yang benar */
    [dir="rtl"] #sidebar-toggle svg { transform: scaleX(-1); }
    [dir="rtl"] #sidebar[data-collapsed="true"] #sidebar-toggle svg { transform: rotate(180deg) scaleX(-1); }
    [dir="rtl"] a[data-kembali] svg { transform: scaleX(-1); }

    /* 6. Garis hiasan tipis di kiri kartu: pindah ke kanan */
    [dir="rtl"] .absolute.left-0.rounded-r-full { left: auto; right: 0; border-radius: 9999px 0 0 9999px; }
</style>
