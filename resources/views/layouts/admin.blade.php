{{--
resources/views/layouts/app.blade.php

Layout utama admin Ribath — berisi SIDEBAR + TOPBAR yang dipakai bersama
di semua halaman (dashboard, data santri, data ustadz, dst).
Halaman turunan tinggal:

@extends('layouts.app')

@section('title', 'Data Santri')

@section('content')
... konten halaman ...
@endsection

@push('scripts')
<script>
    ... script khusus halaman ini (mis. Vue app) ... 
</script>
@endpush


--}}
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

        {{-- Vue 3 tetap dari CDN — cukup library-nya, tiap halaman mount app-nya sendiri --}}
        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

        <script>
            window.createApp = Vue.createApp;
        </script>

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
    --scrollbar-thumb: #374151;
    --scrollbar-thumb-hover: #6b7280;
    --scrollbar-track: #111827;
    }
    
    .scrollbar-light {
    --scrollbar-thumb: #9ca3af;
    --scrollbar-thumb-hover: #e5e7eb;
    --scrollbar-track: rgba(255, 255, 255, 0.05);
    }
</style>
        @stack('styles')
    </head>

    <body class="font-sans bg-ribath-bg text-gray-800 antialiased">

        <div class="flex h-screen overflow-hidden">

            {{-- ============================= SIDEBAR ============================= --}}
            <aside class="w-72 shrink-0 bg-ribath-dark text-gray-100 flex flex-col px-5 py-6 ">

                {{-- Logo --}}
                <div class="w-full flex items-center justify-between ">
                    <a href="/" class="flex items-center gap-2 px-1 ">
                        <div
                            class="w-12 h-12 rounded-xl bg-ribath-green-light/90 hover:bg-ribath-green-light/70 flex items-center justify-center text-ribath-darker font-bold text-lg">
                            <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo">
                        </div>
                        <span class="text-white font-bold text-lg tracking-tight hover:text-gray-300">Dauroh Qobul
                        </span>
                    </a>
                    <svg class=" w-6 h-6 text-gray-800 dark:text-white hover:text-gray-300 cursor-pointer"
                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 21h6c1.1046 0 2-.8954 2-2V5c0-1.10457-.8954-2-2-2h-6v18Z" />
                        <path fill-rule="evenodd"
                            d="M11 3H5c-1.10457 0-2 .89543-2 2v14c0 1.1046.89543 2 2 2h6V3Zm-2.29295 7.7071c.39052-.3905.39052-1.02368 0-1.41421-.39053-.39052-1.02369-.39052-1.41421 0L5.29289 11.2928c-.39052.3906-.39052 1.0237 0 1.4142l1.99995 2c.39052.3905 1.02368.3905 1.41421 0 .39052-.3905.39052-1.0237 0-1.4142l-1.29284-1.2929 1.29284-1.2928Z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                {{-- garis putih --}}
                <div class="my-8 h-px bg-ribath-green-light/30"></div>

                <div id="sidebar-nav" class="flex-1 space-y-1 min-h-0 overflow-y-auto pr-2 scrollbar-custom::-webkit-scrollbar scrollbar-dark scrollbar-small">
                    <nav class="flex-1 space-y-5">
                        <template v-for="item in menuItems" :key="item.label">

                            <!-- Menu dengan submenu -->
                            <div v-if="item.submenu && item.submenu.length">
                                <button type="button" @click="toggle(item.label)"
                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
                                    :class="isActive(item) ?
                                        'bg-ribath-green-light text-ribath-darker font-semibold shadow-sm' :
                                        'text-gray-400 hover:bg-white/5 hover:text-gray-100'">
                                    <i :data-lucide="item.icon" class="w-5 h-5 shrink-0"></i>
                                    <span class="flex-1 text-left" v-text="item.label"></span>
                                    <i data-lucide="chevron-down"
                                        class="w-4 h-4 shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180': openMenus.includes(item.label) }"></i>
                                </button>

                                <div v-show="openMenus.includes(item.label)" class="mt-1 ml-8 space-y-1">
                                    <a v-for="sub in item.submenu" :key="sub.label" :href="sub.url"
                                        class="block px-3.5 py-2 rounded-lg text-sm transition-colors"
                                        :class="sub.active ?
                                            'text-ribath-darker font-semibold bg-ribath-green-light/60' :
                                            'text-gray-400 hover:text-gray-100 hover:bg-white/5'"
                                        v-text="sub.label">
                                    </a>
                                </div>
                            </div>

                            <!-- Menu tanpa submenu -->
                            <a v-else :href="item.url"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
                                :class="isActive(item) ?
                                    'bg-ribath-green-light text-ribath-darker font-semibold shadow-sm' :
                                    'text-gray-400 hover:bg-white/5 hover:text-gray-100'">
                                <i :data-lucide="item.icon" class="w-5 h-5 shrink-0"></i>
                                <span v-text="item.label"></span>
                            </a>

                        </template>
                    </nav>
                </div>

                {{-- Kartu bantuan --}}
                <div class="flex gap-2 mt-6 rounded-2xl bg-white/5 border border-white/10 px-4 py-2 text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0E1F17&color=fff"
                        class="w-10 h-10 rounded-full object-cover" alt="Avatar admin" />
                        <div class=" flex flex-col items-start justify-center">
                    <p class="text-white text-sm font-semibold">Super Admin</p>
                    <p class="text-gray-400 text-xs  leading-relaxed">@yield('page-heading', '' . (auth()->user()->name ?? 'Admin'))</p>
                    </div>
                </div>
            </aside>

            {{-- ============================= KONTEN ============================= --}}
            <div class="flex-1 overflow-y-auto">

                {{-- Topbar --}}
                <header
                    class="flex items-center justify-between px-8 pt-7 pb-9 mb-4 border border-b border-ribath-green-light/20">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">@yield('page-heading', 'Selamat Datang, ' . (auth()->user()->name ?? 'Admin') . ' 👋')</h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative hidden md:block">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <input type="text" placeholder="Cari Santri, Ustadz..."
                                class="bg-white border border-gray-200 rounded-full text-sm pl-10 pr-4 py-2.5 w-64 focus:outline-none focus:ring-2 focus:ring-ribath-green/30" />
                        </div>

                        <button
                            class="relative w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span class="absolute top-1.5 right-2 w-2 h-2 rounded-full bg-ribath-green"></span>
                        </button>

                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0E1F17&color=fff"
                            class="w-10 h-10 rounded-full object-cover" alt="Avatar admin" />
                    </div>
                </header>

                {{-- Di sinilah konten tiap halaman disuntikkan --}}
                <main class="px-8 pb-10 space-y-5">
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')

        <script>
            createApp({
                data() {
                    return {
                        menuItems: @json($menuItemsForVue),
                        openMenus: @json($initiallyOpenMenus),
                    };
                },
                methods: {
                    toggle(label) {
                        const idx = this.openMenus.indexOf(label);
                        idx === -1 ? this.openMenus.push(label) : this.openMenus.splice(idx, 1);
                        this.$nextTick(() => lucide.createIcons());
                    },
                    isActive(item) {
                        if (item.active) return true;
                        if (item.submenu) return item.submenu.some(sub => sub.active);
                        return false;
                    },
                },
                mounted() {
                    lucide.createIcons();
                },
                updated() {
                    lucide.createIcons();
                },
            }).mount('#sidebar-nav');
        </script>



    </body>

</html>
