{{--
    Navbar halaman publik (landing, berita, galeri). Perilaku Vue ada di resources/js/app.js (#navbar-app):
    - openMenu   : menu turun yang terbuka (desktop: arahkan kursor / klik; HP: klik)
    - mobileOpen : lembar menu HP
    - padat      : bentuk kapsul putih mengambang. Di landing page navbar transparan di atas hero
                   dan baru jadi kapsul setelah digulir; di halaman lain langsung kapsul.
    Daftar menu ditulis SATU kali di $menuNavbar, dipakai desktop & HP.
--}}
@php
$transparan = request()->routeIs('landing');
$menuNavbar = [
    'profil' => ['label' => __('Profil'), 'ikon' => 'info', 'isi' => [
        ['title' => __('Tentang Kami'), 'ket' => __('Mengenal Dauroh Qobul'), 'ikon' => 'landmark', 'url' => route('landing') . '#tentang'],
        ['title' => __('Visi & Misi'), 'ket' => __('Tujuan dan arah program'), 'ikon' => 'target', 'url' => route('landing') . '#tentang'],
        ['title' => __('Kurikulum'), 'ket' => __('Kitab dan materi yang dipelajari'), 'ikon' => 'book-open', 'url' => '/kurikulum'],
    ]],
    'informasi' => ['label' => __('Informasi'), 'ikon' => 'newspaper', 'isi' => [
        ['title' => __('Berita & Artikel'), 'ket' => __('Kabar kegiatan terbaru'), 'ikon' => 'newspaper', 'url' => route('berita.index')],
        ['title' => __('Galeri Foto'), 'ket' => __('Dokumentasi kegiatan'), 'ikon' => 'images', 'url' => route('galeri.index')],
        ['title' => __('Pembukaan Dauroh'), 'ket' => __('Jadwal dan info pendaftaran'), 'ikon' => 'calendar-days', 'url' => '/pembukaandauroh'],
    ]],
    'bantuan' => ['label' => __('Bantuan'), 'ikon' => 'circle-help', 'isi' => [
        ['title' => __('Program Dauroh'), 'ket' => __('Rangkaian program yang diikuti'), 'ikon' => 'graduation-cap', 'url' => route('landing') . '#program-dauroh'],
        ['title' => __('Persiapan Dauroh'), 'ket' => __('Berkas dan bekal yang perlu disiapkan'), 'ikon' => 'clipboard-list', 'url' => route('landing') . '#persiapan-dauroh'],
        ['title' => __('Hubungi Kami'), 'ket' => __('Alamat, WhatsApp, dan email'), 'ikon' => 'message-circle', 'url' => route('landing') . '#kontak'],
    ]],
];
@endphp
<div id="navbar-app">

    {{-- top mengikuti tinggi Pita Pengumuman bila pita berada DI ATAS top bar (0 bila tidak ada / di bawah) --}}
    <header data-transparan="{{ $transparan ? 1 : 0 }}" class="fixed left-0 right-0 z-50 transition-all duration-500"
        :class="padat ? 'pt-2 sm:pt-3' : ''" style="top: var(--tinggi-pita-atas, 0px)">

        <div class="mx-auto transition-all duration-500" :class="padat ? 'max-w-6xl px-3' : 'max-w-7xl px-4 sm:px-6 lg:px-8'">

            <div class="flex items-center justify-between gap-4 transition-all duration-500"
                :class="padat ? 'h-16 pl-2 pr-2 sm:pl-3 rounded-2xl bg-white/85 backdrop-blur-xl ring-1 ring-slate-900/5 shadow-[0_10px_40px_-12px_rgba(11,51,44,0.35)]' : 'h-20'">

                {{-- ===== Logo + nama ===== --}}
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 shrink-0 min-w-0">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/95 ring-1 ring-slate-900/5 shadow-sm flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="{{ __('Logo') }}" class="w-full h-full object-contain p-0.5">
                    </span>
                    <span class="hidden sm:block leading-tight min-w-0">
                        <span class="block text-[15px] font-extrabold tracking-tight transition-colors" :class="padat ? 'text-[#0b332c]' : 'text-white'">{{ __('Dauroh Qobul') }}</span>
                        <span class="block text-[11px] font-medium transition-colors" :class="padat ? 'text-teal-700/80' : 'text-emerald-100/80'">{{ __('Darul Musthofa Tarim') }}</span>
                    </span>
                </a>

                {{-- ===== Menu desktop ===== --}}
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('landing') }}"
                        class="px-4 py-2 rounded-full text-[15px] font-semibold transition-colors"
                        :class="padat ? 'text-slate-600 hover:text-teal-800 hover:bg-teal-50' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        {{ __('Beranda') }}
                    </a>

                    @foreach ($menuNavbar as $kunci => $menu)
                    <div class="relative" data-dropdown @mouseenter="openMenu = '{{ $kunci }}'" @mouseleave="openMenu = null">
                        <button type="button" @click="toggleMenu('{{ $kunci }}')" :aria-expanded="openMenu === '{{ $kunci }}' ? 'true' : 'false'"
                            class="flex items-center gap-1 px-4 py-2 rounded-full text-[15px] font-semibold transition-colors"
                            :class="padat
                                ? (openMenu === '{{ $kunci }}' ? 'text-teal-800 bg-teal-50' : 'text-slate-600 hover:text-teal-800 hover:bg-teal-50')
                                : (openMenu === '{{ $kunci }}' ? 'text-white bg-white/15' : 'text-white/90 hover:text-white hover:bg-white/10')">
                            {{ $menu['label'] }}
                            <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': openMenu === '{{ $kunci }}' }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.084l3.71-3.855a.75.75 0 011.08 1.04l-4.24 4.41a.75.75 0 01-1.08 0l-4.24-4.41a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Menu turun: ikon + judul + keterangan --}}
                        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2"
                            leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 translate-y-1">
                            <div v-show="openMenu === '{{ $kunci }}'" v-cloak class="absolute left-1/2 -translate-x-1/2 top-full pt-3 w-80">
                                <div class="rounded-2xl bg-white p-2 shadow-2xl shadow-slate-900/15 ring-1 ring-slate-900/5">
                                    @foreach ($menu['isi'] as $item)
                                    <a href="{{ $item['url'] }}" @click="openMenu = null"
                                        class="group flex items-start gap-3 rounded-xl p-3 hover:bg-teal-50/70 transition-colors">
                                        <span class="w-10 h-10 shrink-0 rounded-xl bg-teal-50 text-teal-700 ring-1 ring-teal-100 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white group-hover:ring-teal-600 transition-colors">
                                            <i data-lucide="{{ $item['ikon'] }}" class="w-5 h-5"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold text-slate-800 group-hover:text-teal-800">{{ $item['title'] }}</span>
                                            <span class="block text-xs text-slate-500 mt-0.5">{{ $item['ket'] }}</span>
                                        </span>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </transition>
                    </div>
                    @endforeach
                </nav>

                {{-- ===== Tombol desktop ===== --}}
                <div class="hidden lg:flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold ring-1 transition-colors"
                        :class="padat ? 'text-teal-800 ring-teal-700/30 hover:bg-teal-50' : 'text-white ring-white/50 hover:bg-white/10'">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        {{ __('Masuk') }}
                    </a>
                    <a href="/psb/daftar"
                        class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all duration-300 hover:-translate-y-0.5"
                        :class="padat ? 'bg-teal-700 text-white shadow-lg shadow-teal-700/25 hover:bg-teal-800' : 'bg-white text-teal-800 shadow-lg shadow-black/10 hover:bg-emerald-50'">
                        {{ __('Daftar Sekarang') }}
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>

                {{-- ===== Tombol menu HP (tiga garis -> X) ===== --}}
                <button type="button" @click="mobileOpen = !mobileOpen" aria-label="{{ __('Buka menu') }}"
                    class="lg:hidden w-11 h-11 shrink-0 rounded-xl flex flex-col items-center justify-center gap-[5px] transition-colors"
                    :class="padat ? 'text-slate-700 bg-slate-100/80 hover:bg-slate-200/80' : 'text-white bg-white/10 hover:bg-white/20'">
                    <span class="block h-0.5 w-5 rounded-full bg-current transition-transform duration-300" :class="mobileOpen ? 'translate-y-[7px] rotate-45' : ''"></span>
                    <span class="block h-0.5 w-5 rounded-full bg-current transition-opacity duration-300" :class="mobileOpen ? 'opacity-0' : ''"></span>
                    <span class="block h-0.5 w-5 rounded-full bg-current transition-transform duration-300" :class="mobileOpen ? '-translate-y-[7px] -rotate-45' : ''"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- ===== Latar gelap HP ===== --}}
    <transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
        <div v-show="mobileOpen" v-cloak @click="mobileOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-60 lg:hidden"></div>
    </transition>

    {{-- ===== Lembar menu HP (muncul dari bawah) ===== --}}
    <transition name="slide-up">
        <div v-show="mobileOpen" v-cloak
            class="fixed bottom-0 left-0 right-0 z-70 lg:hidden bg-white rounded-t-[28px] shadow-2xl px-4 pt-3 pb-[max(1.25rem,env(safe-area-inset-bottom))]">

            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>

            <div class="flex items-center justify-between px-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-xl bg-white ring-1 ring-slate-900/5 shadow-sm flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="" class="w-full h-full object-contain p-0.5">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-[15px] font-extrabold text-[#0b332c]">{{ __('Dauroh Qobul') }}</span>
                        <span class="block text-[11px] text-teal-700/80">{{ __('Darul Musthofa Tarim') }}</span>
                    </span>
                </div>
                <button type="button" @click="mobileOpen = false" aria-label="{{ __('Tutup menu') }}"
                    class="w-9 h-9 flex items-center justify-center rounded-full text-slate-500 bg-slate-100 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="py-3 max-h-[62vh] overflow-y-auto space-y-1">
                <a href="{{ route('landing') }}" @click="mobileOpen = false"
                    class="flex items-center gap-3 px-2 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-50 transition">
                    <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center"><i data-lucide="house" class="w-5 h-5"></i></span>
                    <span class="text-[15px] font-semibold">{{ __('Beranda') }}</span>
                </a>

                @foreach ($menuNavbar as $kunci => $menu)
                <div>
                    <button type="button" @click="toggleMenu('{{ $kunci }}')"
                        class="w-full flex items-center justify-between px-2 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-50 transition"
                        :class="openMenu === '{{ $kunci }}' ? 'bg-slate-50' : ''">
                        <span class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center"><i data-lucide="{{ $menu['ikon'] }}" class="w-5 h-5"></i></span>
                            <span class="text-[15px] font-semibold">{{ $menu['label'] }}</span>
                        </span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': openMenu === '{{ $kunci }}' }" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.084l3.71-3.855a.75.75 0 011.08 1.04l-4.24 4.41a.75.75 0 01-1.08 0l-4.24-4.41a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div v-show="openMenu === '{{ $kunci }}'" v-cloak class="ml-[3.25rem] mr-1 mt-1 mb-2 space-y-0.5">
                        @foreach ($menu['isi'] as $item)
                        <a href="{{ $item['url'] }}" @click="mobileOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-teal-50 hover:text-teal-800 transition">
                            <i data-lucide="{{ $item['ikon'] }}" class="w-4 h-4 text-teal-600 shrink-0"></i>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold">{{ $item['title'] }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $item['ket'] }}</span>
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100">
                <a href="{{ route('login') }}" @click="mobileOpen = false"
                    class="flex items-center justify-center gap-2 px-4 py-3 rounded-full ring-1 ring-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    {{ __('Masuk') }}
                </a>
                <a href="/psb/daftar" @click="mobileOpen = false"
                    class="flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-teal-700 text-white text-sm font-bold shadow-lg shadow-teal-700/25 hover:bg-teal-800 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    {{ __('Daftar') }}
                </a>
            </div>
        </div>
    </transition>

</div>
