<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="theme-color" content="#0F766E">
    <meta name="description" content="{{ __('Platform digital untuk pengelolaan Pondok Pesantren Ribath Masjid Riyadh Solo. Monitoring santri, akademik, dan keuangan secara real-time.') }}">
    <title>@yield('title', __('Beranda - Dauroh Qobul Darul Musthofa Tarim'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    {{-- Vue dimuat via CDN (build global yang sudah menyertakan compiler),
         jadi bisa langsung membaca directive Vue (v-cloak, @click, dst) dari HTML Blade tanpa build step Vue. --}}
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="antialiased font-sans text-slate-800 bg-white">

    {{-- Pita Pengumuman (Admin > Landing Page > Pita Pengumuman) — paling atas, di atas navbar --}}
    @include('partials.pita-pengumuman')

    @include('partials.navbar')

    {{-- isi halaman ikut turun setinggi Pita Pengumuman --}}
    <main style="padding-top: var(--tinggi-pita, 0px)">
        @yield('content')
    </main>

    @include('partials.footer')

    @include('partials.floating-widgets')

    {{-- Status koneksi internet + tombol muat ulang (pojok kanan bawah) --}}
    @include('partials.status-koneksi')
    {{-- Notifikasi pojok kanan bawah (pesan sukses/error, menutup sendiri) --}}
    @include('partials.notifikasi')



    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
