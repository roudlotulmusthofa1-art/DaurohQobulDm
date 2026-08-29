<div id="navbar-app">

    <!-- ========================================= -->
    <!-- NAVBAR -->
    <!-- ========================================= -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur border-b border-slate-100">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between h-20">

                <!-- LOGO -->
                <a href="{{ route('landing') }}" class="flex items-center shrink-0">
                    <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo" class="h-12 w-20">
                </a>


                <!-- ========================================= -->
                <!-- MENU DESKTOP -->
                <!-- ========================================= -->
                <nav class="hidden lg:flex items-center gap-8">

                    <!-- Beranda -->
                    <a href="{{ route('landing') }}"
                        class="text-lg font-medium text-slate-700 hover:text-teal-700 transition-colors">
                        Beranda
                    </a>


                    <!-- Dropdown -->
                    @foreach ([
                    'profil' => [
                    [
                    'title' => 'Tentang Kami',
                    'url' => '#tentang',
                    ],
                    [
                    'title' => 'Visi & Misi',
                    'url' => '#tentang',
                    ],
                    [
                    'title' => 'Kurikulum',
                    'url' => '/kurikulum',
                    ],
                    ],

                    'informasi' => [
                    [
                    'title' => 'Berita & Artikel',
                    'url' => '/berita',
                    ],
                    [
                    'title' => 'Galeri Foto',
                    'url' => '/galeri',
                    ],
                    [
                    'title' => 'Pembukaan Dauroh',
                    'url' => '/pembukaandauroh',
                    ],
                    ],

                    'bantuan' => [
                    [
                    'title' => 'Program Dauroh',
                    'url' => '#program-dauroh',
                    ],
                    [
                    'title' => 'Persiapan Dauroh',
                    'url' => '#persiapan-dauroh',
                    ],
                    [
                    'title' => 'Hubungi Kami',
                    'url' => '#kontak',
                    ],
                    ],
                    ] as $menu => $items)
                    <div class="relative" data-dropdown v-cloak @mouseleave="openMenu = null">

                        <button type="button" @click="toggleMenu('{{ $menu }}')"
                            class="flex items-center gap-1 text-lg font-medium text-slate-700 hover:text-teal-700 transition-colors">

                            {{ ucfirst($menu) }}

                            <svg class="w-4 h-4 transition-transform" :class="{
                                        'rotate-180': openMenu === '{{ $menu }}'
                                    }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.084l3.71-3.855a.75.75 0 011.08 1.04l-4.24 4.41a.75.75 0 01-1.08 0l-4.24-4.41a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>

                        </button>


                        <!-- SUB MENU -->
                        <div v-show="openMenu === '{{ $menu }}'"
                            class="absolute left-1/2 -translate-x-1/2 top-full w-56 rounded-xl bg-white shadow-xl ring-1 ring-slate-100 py-2">

                            @foreach ($items as $item)
                            <a href="{{ $item['url'] }}"
                                class="block px-4 py-2.5 text-sm text-slate-600 hover:bg-teal-100/60 hover:text-teal-700 transition-colors">
                                {{ $item['title'] }}
                            </a>
                            @endforeach

                        </div>

                    </div>
                    @endforeach

                </nav>


                <!-- ========================================= -->
                <!-- TOMBOL DESKTOP -->
                <!-- ========================================= -->
                <div class="hidden lg:flex items-center gap-3">

                    <a href="{{ route('login') }}"
                        class="px-5 py-2.5 rounded-lg border border-teal-700 text-teal-700 text-sm font-semibold hover:bg-teal-50 transition-colors">
                        Masuk
                    </a>

                    <a href="#"
                        class="px-5 py-2.5 rounded-lg bg-teal-700 text-white text-sm font-semibold hover:bg-teal-800 transition-colors shadow-sm">
                        Daftar Sekarang
                    </a>

                </div>


                <!-- ========================================= -->
                <!-- HAMBURGER MOBILE -->
                <!-- ========================================= -->
                <button type="button" @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-slate-700">

                    <span v-if="!mobileOpen" class="text-2xl">
                        ☰
                    </span>

                    <span v-else class="text-2xl">
                        ✕
                    </span>

                </button>

            </div>

        </div>

    </header>


    <!-- ========================================= -->
    <!-- OVERLAY -->
    <!-- ========================================= -->
    <transition name="fade">

        <div v-show="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-black/60 z-60 lg:hidden"></div>

    </transition>


    <!-- ========================================= -->
    <!-- MENU MOBILE / BOTTOM SHEET -->
    <!-- ========================================= -->
    <transition name="slide-up">

        <div v-show="mobileOpen" {{-- agar nempel ke bawah --}}
            class="fixed bottom-0 left-0 right-0 z-70 lg:hidden bg-white rounded-t-3xl shadow-2xl px-5 pt-5 pb-6">

            <!-- Garis kecil -->
            <div class="w-12 h-1.5 bg-slate-300 rounded-full mx-auto mb-5"></div>

            <!-- ================================= -->
            <!-- HEADER -->
            <!-- ================================= -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">

                <h2 class="text-lg font-semibold text-slate-800">
                    Menu Navigasi
                </h2>

                <button type="button" @click="mobileOpen = false" class="w-9 h-9 flex items-center justify-center rounded-full
                       text-slate-600 hover:bg-slate-100 transition" aria-label="Tutup menu">

                    <i data-lucide="x" class="w-5 h-5"></i>

                </button>

            </div>

            <!-- ================================= -->
            <!-- ISI MENU -->
            <!-- ================================= -->
            <div class="px-4 py-3 max-h-[70vh] overflow-y-auto">

                <!-- ============================= -->
                <!-- BERANDA -->
                <!-- ============================= -->
                <a href="/" @click="mobileOpen = false" class="flex items-center gap-4 px-3 py-3.5 rounded-xl
                       text-slate-700 hover:bg-slate-50 transition">

                    <i data-lucide="house" class="w-5 h-5 text-teal-600"></i>

                    <span class="text-[15px] font-medium">
                        Beranda
                    </span>

                </a>

                <!-- ============================= -->
                <!-- PROFIL -->
                <!-- ============================= -->
                <div class="border-b border-transparent">
                    <button type="button" @click="toggleMenu('profil')" class="w-full flex items-center justify-between
                               px-3 py-3.5 rounded-xl
                               text-slate-700 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-4">
                            <i data-lucide="info" class="w-5 h-5 text-teal-600"></i>
                            <span class="text-sm font-medium">Profil</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 transition-transform duration-200" :class="{
                                'rotate-90': openMenu === 'profil'
                            }"></i>
                    </button>
                    <div v-show="openMenu === 'profil'" v-cloak
                        class="ml-12 mr-2 mb-2 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden">
                        <a href="#tentang" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Tentang
                            Kami</a>
                        <a href="#tentang" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Visi
                            & Misi</a>
                        <a href="#kurikulum" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Kurikulum</a>
                    </div>
                </div>

                {{-- informasi --}}

                <div class="border-b border-transparent">
                    <button type="button" @click="toggleMenu('informasi')" class="w-full flex items-center justify-between
                               px-3 py-3.5 rounded-xl
                               text-slate-700 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-4">
                            <i data-lucide="newspaper" class="w-5 h-5 text-teal-600"></i>
                            <span class="text-sm font-medium">Informasi</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 transition-transform duration-200" :class="{
                                'rotate-90': openMenu === 'informasi'
                            }"></i>
                    </button>
                    <div v-show="openMenu === 'informasi'" v-cloak
                        class="ml-12 mr-2 mb-2 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden">
                        <a href="#tentang" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Berita
                            & Artikel</a>
                        <a href="#tentang" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Galeri
                            Foto</a>
                        <a href="#kurikulum" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Pembukaan
                            Dauroh</a>
                    </div>
                </div>

                {{-- bantuan --}}

                <div class="border-b border-transparent">
                    <button type="button" @click="toggleMenu('bantuan')" class="w-full flex items-center justify-between
                               px-3 py-3.5 rounded-xl
                               text-slate-700 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-4">
                            <i data-lucide="circle-help" class="w-5 h-5 text-teal-600"></i>
                            <span class="text-sm font-medium">Bantuan</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 transition-transform duration-200" :class="{
                                'rotate-90': openMenu === 'bantuan'
                            }"></i>
                    </button>
                    <div v-show="openMenu === 'bantuan'" v-cloak
                        class="ml-12 mr-2 mb-2 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden transition">
                        <a href="#program-dauroh" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">
                            Program Daurohh
                        </a>
                        <a href="#persiapan-dauroh" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">
                            Persiapan Dauroh
                        </a>
                        <a href="#kontak" @click="mobileOpen = false"
                            class="block px-4 py-3 text-sm text text-slate-600 hover:bg-teal-100 hover:text-teal-800">Pembukaan
                            Hubungi Kami
                        </a>
                    </div>
                </div>
                {{-- garis --}}
                <div class="w-full h-0.5 bg-slate-100 my-5"></div>
                {{-- tombol masuk --}}
                <div class="space-y-4">
                    <a href="#" @click="mobilOpen = false"
                        class="flex items-center justify-center gap-3 w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-700 text-sm font-medium hover:bg-teal-100 hover:text-teal-700 transition">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Masuk</a>
                    <a href="#" @click="mobilOpen = false"
                        class="flex items-center justify-center gap-3 w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-700 text-sm font-medium hover:bg-teal-100 hover:text-teal-700 transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Daftar Sekarang</a>
                </div>
            </div>
        </div>

    </transition>

</div>