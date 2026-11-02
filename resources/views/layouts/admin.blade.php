<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Dauroh</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Class utama scrollbar */
        .scrollbar-custom {
            --scrollbar-size: 6px;
            --scrollbar-thumb: #4b5563;
            --scrollbar-thumb-hover: #9ca3af;
            --scrollbar-track: transparent;

            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
        }

        /* Chrome, Edge, dan Safari */
        .scrollbar-custom::-webkit-scrollbar {
            width: var(--scrollbar-size);
        }

        .scrollbar-custom::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
            border-radius: 9999px;
        }

        .scrollbar-custom::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 9999px;
        }

        .scrollbar-custom::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }

        /* Pilihan ukuran */
        .scrollbar-small {
            --scrollbar-size: 4px;
        }

        .scrollbar-medium {
            --scrollbar-size: 7px;
        }

        .scrollbar-large {
            --scrollbar-size: 11px;
        }

        /* Pilihan warna */
        .scrollbar-dark {
            --scrollbar-thumb: rgba(255, 255, 255, 0.12);
            --scrollbar-thumb-hover: rgba(255, 255, 255, 0.25);
            --scrollbar-track: transparent;
        }

        .scrollbar-light {
            --scrollbar-thumb: #9ca3af;
            --scrollbar-thumb-hover: #e5e7eb;
            --scrollbar-track: rgba(255, 255, 255, 0.05);
        }

        /* ===== Sidebar buka/tutup =====
           data-collapsed="true" di <aside id="sidebar"> = sidebar ditutup (ikon saja).
           .sb-teks  = bagian yang disembunyikan saat ditutup (tulisan, judul, submenu)
           .sb-item  = tombol menu yang ikonnya dibuat ke tengah saat ditutup */
        #sidebar[data-ready] { transition: width .2s ease, padding .2s ease; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) { width: 5rem; padding-left: .75rem; padding-right: .75rem; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) .sb-teks { display: none; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) .sb-logo { flex-direction: column; gap: .75rem; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) .sb-user { justify-content: center; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) #sidebar-toggle svg { transform: rotate(180deg); }
        /* .sb-ikon = daftar ikon submenu, hanya tampil saat sidebar ditutup */
        .sb-ikon { display: none; }
        :is(#sidebar[data-collapsed="true"], #sidebar-app[data-tertutup-awal="1"]:not([data-v-app]) #sidebar) .sb-ikon { display: flex; }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased text-gray-800 bg-[#0b312a]">

    {{-- Bingkai hijau gelap (seperti di desain) --}}
    <div
        class="flex h-screen min-h-0 overflow-hidden p-3 bg-[linear-gradient(180deg,#0f4238_0%,#0b332c_55%,#0a2b25_100%)]">

        {{-- ============================= SIDEBAR =============================
             Aplikasi Vue #sidebar-app (skrip di bawah halaman): buka/tutup sidebar + menu.
             Posisi buka/tutup disimpan di cookie "sidebar_tertutup" supaya server langsung
             menampilkan posisi yang benar (tidak berkedip saat pindah halaman). --}}
        @php $sidebarTertutup = request()->cookie('sidebar_tertutup') === '1'; @endphp
        {{-- data-tertutup-awal: posisi dari server, dipakai CSS hanya sebelum Vue terpasang (lihat <style> di atas) --}}
        <div id="sidebar-app" class="contents" data-tertutup-awal="{{ $sidebarTertutup ? '1' : '0' }}">
        <aside id="sidebar" :data-collapsed="tertutup ? 'true' : 'false'" :data-ready="siap ? '1' : null"
            class="w-65 h-full min-h-0 shrink-0 text-gray-100 flex flex-col pl-4 pr-5 pt-5 pb-4">

            {{-- Logo --}}
            <div class="sb-logo w-full flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5 px-1 group">
                    <div class="w-10 h-10 flex items-center justify-center bg-[#eff5f0d7] rounded-full">
                        {{-- Logo & nama dari Sistem > Pengaturan > Umum (bawaan: Logo dar4.png & "Dauroh Qobul") --}}
                        @php $profilPesantren = \App\Models\Pengaturan::profilPesantren(); @endphp
                        <img src="{{ $profilPesantren['logo_url'] }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="sb-teks text-white font-semibold text-[19px] tracking-tight group-hover:text-white/80 line-clamp-2 leading-tight" v-pre>{{ $profilPesantren['nama'] }}</span>
                </a>
                {{-- Tombol buka/tutup sidebar --}}
                <button type="button" id="sidebar-toggle" aria-controls="sidebar" @click="aturSidebar(!tertutup)"
                    :aria-expanded="tertutup ? 'false' : 'true'" :title="tertutup ? 'Buka sidebar' : 'Tutup sidebar'"
                    class="shrink-0 rounded-md p-1 text-white/40 hover:text-white/80 hover:bg-white/5 transition-colors">
                    <svg class="w-5 h-5 transition-transform duration-200"
                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path d="M13 21h6c1.1046 0 2-.8954 2-2V5c0-1.10457-.8954-2-2-2h-6v18Z" />
                        <path fill-rule="evenodd"
                            d="M11 3H5c-1.10457 0-2 .89543-2 2v14c0 1.1046.89543 2 2 2h6V3Zm-2.29295 7.7071c.39052-.3905.39052-1.02368 0-1.41421-.39053-.39052-1.02369-.39052-1.41421 0L5.29289 11.2928c-.39052.3906-.39052 1.0237 0 1.4142l1.99995 2c.39052.3905 1.02368.3905 1.41421 0 .39052-.3905.39052-1.0237 0-1.4142l-1.29284-1.2929 1.29284-1.2928Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            {{-- garis tipis --}}
            <div class="mt-5 mb-6 h-px bg-white/10"></div>

            {{-- Label NAVIGATION --}}
            <p class="sb-teks px-1 mb-3 text-[10px] font-medium uppercase tracking-[0.08em] text-white/40">Navigation</p>

            <div id="sidebar-nav"
                class="flex-1 min-h-0 overflow-y-auto pr-1 scrollbar-custom scrollbar-dark scrollbar-small">
                <nav class="space-y-2">
                    <template v-for="item in menuItems" :key="item.label">

                        <!-- Menu dengan submenu -->
                        <div v-if="item.submenu && item.submenu.length">
                            <button type="button" @click="toggle(item.label)" :title="item.label"
                                class="sb-item w-full flex items-center gap-3 pl-1.5 pr-3 py-1.5 rounded-full text-[13px] font-medium transition-colors"
                                :class="isActive(item) ?
                                        'bg-white/10 text-white' :
                                        'text-white/75 hover:bg-white/5 hover:text-white'">
                                <span
                                    class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center transition-colors"
                                    :class="isActive(item) ? 'bg-white text-[#0b332c]' : 'bg-white/10 text-white/70'">
                                    <i :data-lucide="item.icon" class="w-4 h-4"></i>
                                </span>
                                <span class="sb-teks flex-1 text-left" v-text="item.label"></span>
                                {{-- Panah: ke kanan saat tertutup, berputar ke bawah saat dibuka.
                                     Putarannya dipasang di <span> pembungkus, karena Lucide mengganti <i> dengan <svg>
                                     (binding :class di <i> ikut hilang). --}}
                                <span class="sb-teks shrink-0 inline-flex text-white/50 transition-transform duration-200"
                                    :class="{ 'rotate-90 text-white/80': openMenus.includes(item.label) }">
                                    <i data-lucide="chevron-right" class="w-4 h-4 shrink-0"></i>
                                </span>
                            </button>

                            <div v-show="openMenus.includes(item.label)"
                                class="sb-teks mt-1.5 ml-6.5 pl-4 border-l border-white/10 space-y-1">
                                <a v-for="sub in item.submenu" :key="sub.label" :href="sub.url"
                                    class="block px-3 py-1.5 rounded-full text-[12.5px] transition-colors" :class="sub.active ?
                                            'text-white font-semibold bg-white/10' :
                                            'text-white/55 hover:text-white hover:bg-white/5'" v-text="sub.label">
                                </a>
                            </div>

                            {{-- Saat sidebar DITUTUP: semua submenu tampil sebagai ikon (nama muncul saat kursor diarahkan) --}}
                            <div class="sb-ikon mt-1.5 flex-col items-center gap-1 rounded-2xl bg-white/5 py-1.5">
                                <a v-for="sub in item.submenu" :key="'ikon-' + sub.label" :href="sub.url"
                                    :title="sub.label" :aria-label="sub.label"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                                    :class="sub.active ? 'bg-white text-[#0b332c]' : 'text-white/60 hover:bg-white/10 hover:text-white'">
                                    <i :data-lucide="sub.icon || 'circle'" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Menu tanpa submenu -->
                        <a v-else :href="item.url" :title="item.label"
                            class="sb-item flex items-center gap-3 pl-1.5 pr-3 py-1.5 rounded-full text-[13px] font-medium transition-colors"
                            :class="isActive(item) ?
                                    'bg-white/10 text-white' :
                                    'text-white/75 hover:bg-white/5 hover:text-white'">
                            <span
                                class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center transition-colors"
                                :class="isActive(item) ? 'bg-white text-[#0b332c]' : 'bg-white/10 text-white/70'">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </span>
                            <span class="sb-teks" v-text="item.label"></span>
                        </a>

                    </template>
                </nav>
            </div>

            {{-- USER ACCOUNT --}}
            <div class="mt-6">
                <p class="sb-teks px-1 mb-3 text-[10px] font-medium uppercase tracking-[0.08em] text-white/40">User Account
                </p>
                <div class="sb-user flex items-center gap-3 px-1">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=4ade80&color=0b332c"
                        class="w-10 h-10 rounded-full object-cover ring-2 ring-[#4ade80]/60" alt="Avatar admin" />
                    <div class="sb-teks flex flex-col items-start justify-center min-w-0" v-pre>
                        <p class="text-white text-[15px] font-medium truncate">@yield('page-heading', '' .
                            (auth()->user()->name ?? 'Admin'))</p>
                        <p class="text-white/45 text-xs">Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>
        </div>

        {{-- ============================= KONTEN ============================= --}}
        <div
            class="flex-1 min-w-0 min-h-0 flex flex-col overflow-hidden rounded-[26px] bg-[#eef4f1]">

            {{-- Topbar: tetap di atas, di luar area scroll, jadi sudut kanan atas tertutup rapi --}}
            {{-- Aplikasi Vue #header-akun: menu lonceng & profil (skrip di bawah halaman).
                 Teks dari database di sini diberi v-pre agar tidak dibaca Vue sebagai template. --}}
            <header id="header-akun" class="shrink-0 z-30 flex items-center justify-between px-6 pt-6 pb-5 bg-[#0b312a4e] border-b border-[#0b312a99] backdrop-blur-md">
                <div>
                    <h1 class="text-[26px] font-bold tracking-tight text-[#0f1f1a]" v-pre>@yield('page-heading', 'Selamat
                        Datang, ' . (auth()->user()->name ?? 'Admin') . ' 👋')</h1>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Sesi aktif (Pengaturan > Sesi): semua data yang tampil milik sesi ini --}}
                    @php $sesiAktifHeader = \App\Support\SesiAplikasi::aktif(); @endphp
                    @if ($sesiAktifHeader)
                    <a href="{{ route('admin.pengaturan.index', ['tab' => 'sesi']) }}" title="Sesi aktif. Klik untuk mengganti."
                        class="hidden lg:inline-flex items-center gap-2 max-w-[14rem] rounded-xl bg-white/80 px-3 py-2.5 text-[12px] font-semibold text-[#0b332c] ring-1 ring-emerald-200 hover:bg-white">
                        <span class="w-2 h-2 shrink-0 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                        <span class="truncate" v-pre>{{ $sesiAktifHeader->nama }}</span>
                    </a>
                    @endif
                    <div class="relative hidden md:block">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" placeholder="Cari Santri, Ustadz..."
                            class="bg-white rounded-xl text-[13px] pl-10 pr-4 py-2.5 w-64 border border-transparent focus:outline-none focus:border-[#0b332c]/20 focus:ring-2 focus:ring-[#22c55e]/20" />
                    </div>

                    {{-- ===== Notifikasi (lonceng): pendaftar yang menunggu verifikasi ===== --}}
                    <div class="relative">
                        <button type="button" @click.stop="bukaTutup('notif')" aria-haspopup="true"
                            :aria-expanded="menuBuka === 'notif' ? 'true' : 'false'" title="Notifikasi"
                            class="relative w-10 h-10 rounded-xl bg-white flex items-center justify-center hover:bg-white/70 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if ($jumlahNotif > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white">{{ $jumlahNotif > 9 ? '9+' : $jumlahNotif }}</span>
                            @endif
                        </button>

                        <div v-show="menuBuka === 'notif'" @click.stop style="display: none"
                            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-bold text-slate-800">Notifikasi</p>
                                @if ($jumlahNotif > 0)
                                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-2 py-0.5">{{ $jumlahNotif }} perlu dicek</span>
                                @endif
                            </div>

                            <div class="max-h-[60vh] overflow-y-auto" v-pre>
                            @forelse ($notifItems as $n)
                                <a href="{{ $n['url'] ?? '#' }}"
                                    class="flex items-start gap-3 px-4 py-3 hover:bg-[#f5f9f7] transition-colors border-b border-slate-50 last:border-0">
                                    <span class="w-9 h-9 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna'] }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon'] }}"/></svg>
                                    </span>
                                    <span class="min-w-0">
                                        {{-- teks sudah di-escape di App\Support\NotifikasiAdmin --}}
                                        <span class="block text-[13px] text-slate-700 leading-snug [&_b]:font-semibold [&_b]:text-slate-900">{!! $n['teks'] !!}</span>
                                        <span class="block text-[11px] text-slate-400 mt-0.5">{{ collect([$n['keterangan'], $n['waktu']])->filter()->implode(' · ') }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <span class="mx-auto w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </span>
                                    <p class="mt-2 text-sm text-slate-500">Tidak ada notifikasi baru</p>
                                </div>
                            @endforelse
                            </div>

                            <a href="{{ route('admin.pengaturan.index') }}"
                                class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-emerald-700 bg-[#f5f9f7] hover:bg-emerald-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                                Atur notifikasi
                            </a>
                        </div>
                    </div>

                    {{-- ===== Profil: foto + nama + peran, klik untuk Keluar ===== --}}
                    <div class="relative">
                        <button type="button" @click.stop="bukaTutup('profil')" aria-haspopup="true"
                            :aria-expanded="menuBuka === 'profil' ? 'true' : 'false'"
                            class="flex items-center gap-2.5 rounded-xl pl-1 pr-2.5 py-1 hover:bg-white/60 transition-colors">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0b332c&color=fff"
                                class="w-10 h-10 rounded-xl object-cover" alt="Avatar admin" />
                            <span class="hidden sm:flex flex-col items-start leading-tight text-left" v-pre>
                                <span class="text-[13px] font-semibold text-[#0f1f1a] max-w-[10rem] truncate">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="text-[11px] text-gray-600">Super Admin</span>
                            </span>
                            <svg class="hidden sm:block w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>

                        <div v-show="menuBuka === 'profil'" @click.stop style="display: none"
                            class="absolute right-0 mt-2 w-60 bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100" v-pre>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a href="/" target="_blank" rel="noopener"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#f5f9f7] transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                                Lihat Website
                            </a>
                            <form method="POST" action="{{ route('admin.logout') }}"
                                @submit="konfirmasi($event, 'Keluar dari akun admin?')" class="border-t border-slate-100">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Di sinilah konten tiap halaman disuntikkan --}}
            {{-- Hanya bagian ini yang di-scroll, scrollbar mulai di bawah header --}}
            <main class="flex-1 min-h-0 overflow-y-auto px-6 pt-5 pb-6 space-y-4 scrollbar-custom scrollbar-small">
                {{-- Breadcrumb: Beranda > Halaman. Datanya ($breadcrumbs) dibuat otomatis di AppServiceProvider.
                     Halaman bisa mengganti langkah terakhir dengan @section('breadcrumb-akhir', 'Nama') --}}
                <nav aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-1.5 text-[13px] text-gray-500">
                        @foreach ($breadcrumbs as $crumb)
                            <li class="flex items-center gap-1.5">
                                @unless ($loop->first)
                                    <svg class="w-3.5 h-3.5 text-gray-400" aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                @endunless

                                @if ($crumb['url'])
                                    <a href="{{ $crumb['url'] }}" class="flex items-center gap-1.5 hover:text-[#0b332c] transition-colors">
                                        @if ($loop->first)
                                            <svg class="w-3.5 h-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            </svg>
                                        @endif
                                        {{ $crumb['label'] }}
                                    </a>
                                @else
                                    <span class="flex items-center gap-1.5 font-medium text-[#0f1f1a]" aria-current="page">
                                        @if ($loop->first)
                                            <svg class="w-3.5 h-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            </svg>
                                        @endif
                                        @if ($loop->last && trim($__env->yieldContent('breadcrumb-akhir')) !== '')
                                            @yield('breadcrumb-akhir')
                                        @else
                                            {{ $crumb['label'] }}
                                        @endif
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                @yield('content')
            </main>
        </div>
    </div>

    {{-- ===== Aplikasi Vue layout admin (window.Vue dari resources/js/app.js) ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = window.Vue;

            // 1) Menu lonceng & profil di header
            createApp({
                data() {
                    return { menuBuka: null }; // null | 'notif' | 'profil'
                },
                methods: {
                    bukaTutup(nama) {
                        this.menuBuka = this.menuBuka === nama ? null : nama;
                    },
                    tutupSemua() {
                        this.menuBuka = null;
                    },
                    tutupDenganEsc(e) {
                        if (e.key === 'Escape') this.tutupSemua();
                    },
                    // Tanya dulu sebelum form dikirim (dipakai tombol Keluar)
                    konfirmasi(e, pesan) {
                        if (!confirm(pesan)) e.preventDefault();
                    },
                },
                // Klik di luar menu / tekan Esc -> menu tertutup
                mounted() {
                    document.addEventListener('click', this.tutupSemua);
                    document.addEventListener('keydown', this.tutupDenganEsc);
                },
                unmounted() {
                    document.removeEventListener('click', this.tutupSemua);
                    document.removeEventListener('keydown', this.tutupDenganEsc);
                },
            }).mount('#header-akun');

            // 2) Sidebar: buka/tutup + menu bertingkat
            createApp({
                data() {
                    return {
                        tertutup: @json($sidebarTertutup), // true = sidebar ditutup (ikon saja)
                        siap: false,                        // animasi dinyalakan setelah tampilan pertama
                        menuItems: @json($menuItemsForVue),
                        openMenus: @json($initiallyOpenMenus),
                    };
                },
                methods: {
                    // Simpan posisi di cookie agar server menampilkan posisi yang sama di halaman berikutnya
                    aturSidebar(tutup) {
                        this.tertutup = tutup;
                        document.cookie = 'sidebar_tertutup=' + (tutup ? 1 : 0) + '; path=/; max-age=31536000; SameSite=Lax';
                    },
                    toggle(label) {
                        // Sidebar sedang ditutup: buka dulu, lalu tampilkan submenu yang diklik
                        if (this.tertutup) {
                            this.aturSidebar(false);
                            if (!this.openMenus.includes(label)) this.openMenus.push(label);
                            return;
                        }
                        const idx = this.openMenus.indexOf(label);
                        idx === -1 ? this.openMenus.push(label) : this.openMenus.splice(idx, 1);
                    },
                    isActive(item) {
                        if (item.active) return true;
                        if (item.submenu) return item.submenu.some(sub => sub.active);
                        return false;
                    },
                },
                mounted() {
                    lucide.createIcons();
                    // Animasi buka/tutup baru aktif setelah tampilan pertama (tidak "bergeser" saat halaman dibuka)
                    requestAnimationFrame(() => requestAnimationFrame(() => (this.siap = true)));
                },
                updated() {
                    lucide.createIcons();
                },
            }).mount('#sidebar-app');
        });
    </script>

    {{-- Metode Vue bersama untuk skrip halaman (window.metodeHalaman), lalu skrip tiap halaman --}}
    @include('partials.vue-metode')
    @stack('scripts')

</body>

</html>
