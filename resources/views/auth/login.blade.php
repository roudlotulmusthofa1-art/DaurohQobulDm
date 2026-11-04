<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Masuk · Dauroh Qobul') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Vue 3 (global build) --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }

        /* ==========================================================
           GANTI FOTO BACKGROUND DI SINI
           Ganti url() di bawah dengan foto lain kalau ingin.
        ========================================================== */
        .hero-bg {
            background-image:
                linear-gradient(160deg, rgba(19, 83, 71, 0.82) 0%, rgba(11, 51, 44, 0.88) 55%, rgba(7, 31, 26, 0.96) 100%),
                url('/images/darul musthofa.jpg');
            background-size: cover;
            background-position: center;
        }

        @keyframes naik {
            from { transform: translateY(14px); opacity: 0; }
            to { transform: none; opacity: 1; }
        }

        .anim-naik { animation: naik .55s ease-out both; }
    </style>
</head>

<body class="min-h-screen bg-[#eef4f1] text-slate-800 antialiased">

    <div id="loginApp" class="min-h-screen flex flex-col lg:flex-row">

        <!-- ===================== PANEL FOTO (kiri di laptop, atas di HP) ===================== -->
        <div class="relative hero-bg overflow-hidden text-white lg:w-1/2 xl:w-[55%] shrink-0">
            <div class="pointer-events-none absolute inset-0 opacity-50"
                style="background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 18px 18px;"></div>
            <div class="pointer-events-none absolute -top-24 -right-24 w-96 h-96 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 w-96 h-96 rounded-full bg-amber-300/10 blur-3xl"></div>

            <div class="relative h-full flex flex-col px-6 sm:px-10 lg:px-14 pt-8 pb-20 lg:py-12">
                <!-- Logo + nama -->
                <a href="/" class="inline-flex items-center gap-3 self-start group">
                    <span class="w-11 h-11 rounded-full bg-[#eff5f0] ring-4 ring-white/10 flex items-center justify-center overflow-hidden shadow-lg">
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="{{ __('Logo') }}" class="w-full h-full object-contain">
                    </span>
                    <span class="font-bold text-lg tracking-tight group-hover:text-white/80 transition-colors">{{ __('Dauroh Qobul') }}</span>
                </a>

                <!-- Sambutan -->
                <div class="mt-10 lg:mt-auto lg:mb-auto max-w-md">
                    <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.22em] text-amber-200/90">{{ __('Darul Musthofa Tarim') }}</p>
                    <h2 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight">
                        {{ __('Selamat Datang') }}
                        <span class="block text-emerald-300">{{ __('di Dauroh Qobul') }}</span>
                    </h2>
                    <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed hidden sm:block">
                        {{ __('Masuk untuk mengelola data , atau melihat informasi akun Anda.') }}
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-white/80 hidden lg:block">
                        <li class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center text-emerald-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </span>
                            {{ __('Akun aman, khusus Admin, Asatid & Peserta terdaftar') }}
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center text-amber-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </span>
                            {{ __('Info akun siswa dikirim lewat Nomer WhatsApp terdaftar.') }}
                        </li>
                    </ul>
                </div>

                <p class="hidden lg:block text-xs text-white/40">&copy; {{ date('Y') }} {{ __('Dauroh Qobul · Darul Musthofa Tarim') }}</p>
            </div>
        </div>

        <!-- ===================== FORM SIGN IN ===================== -->
        <div class="flex-1 flex items-start lg:items-center justify-center px-4 sm:px-8 -mt-12 lg:mt-0 pb-10 lg:py-12 relative z-10">
            <div class="w-full max-w-md anim-naik">

                <div class="bg-white rounded-[28px] ring-1 ring-[#0b332c]/[0.06] shadow-[0_24px_56px_-24px_rgba(7,31,26,0.35)] px-6 sm:px-9 pt-8 pb-9">
                    <div class="text-center sm:text-left">
                        <span class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white items-center justify-center shadow-lg shadow-emerald-500/25">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </span>
                        <h1 class="mt-4 text-2xl sm:text-[28px] font-extrabold text-slate-800 tracking-tight">{{ __('Masuk ke Akun') }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Silakan masukkan email dan password Anda.') }}</p>
                    </div>

                    {{-- Notifikasi error umum (misal email/password salah) --}}
                    @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-700 text-sm px-4 py-3" v-pre>
                        <span class="w-6 h-6 shrink-0 rounded-full bg-rose-500 text-white flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg>
                        </span>
                        <div class="pt-0.5">
                            @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" @submit="onSubmit" class="mt-6">
                        @csrf
                        <!-- Email -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Email') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.5-9.75-6.5"/></svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                    v-model="form.email" autocomplete="email"
                                    class="w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl py-3 pl-11 pr-4 text-slate-800 placeholder:text-slate-400 outline-none transition focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15"
                                    placeholder="nama@email.com">
                            </div>
                            <p class="text-xs text-rose-600 mt-1.5" v-if="clientErrors.email">@{{ clientErrors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Password') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                                </span>
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                    v-model="form.password" autocomplete="current-password"
                                    class="w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl py-3 pl-11 pr-12 text-slate-800 placeholder:text-slate-400 outline-none transition focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15"
                                    placeholder="{{ __('Masukkan password') }}">
                                <button type="button" @click="showPassword = !showPassword"
                                    :title="showPassword ? @js(__('Sembunyikan password')) : @js(__('Tampilkan password'))"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-[#0b332c] hover:bg-slate-100 transition-colors">
                                    <!-- Password tertutup -->
                                    <svg v-show="!showPassword" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                        <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>

                                    <!-- Password terbuka -->
                                    <svg v-show="showPassword" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4-7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-rose-600 mt-1.5" v-if="clientErrors.password">@{{ clientErrors.password }}</p>
                        </div>

                        <!-- Forgot password -->
                        <div class="text-right mb-6">
                            <a href="#" class="text-xs font-medium text-slate-400 hover:text-emerald-700 transition-colors">
                                {{ __('Lupa password?') }}
                            </a>
                        </div>

                        <!-- Submit -->
                        <button type="submit" :disabled="loading"
                            class="w-full bg-[#0b332c] hover:bg-[#135347] disabled:opacity-60 text-white font-semibold py-3.5 rounded-2xl shadow-lg shadow-[#0b332c]/25 transition-colors flex items-center justify-center gap-2 cursor-pointer">

                            <!-- SVG loading -->
                            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            <!-- SVG masuk -->
                            <svg v-else class="w-5 h-5 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2" />
                            </svg>

                            <!-- Tulisan -->
                            <span v-text="loading ? @js(__('Proses...')) : @js(__('Masuk'))"></span>
                        </button>
                    </form>

                    <!-- Pemisah -->
                    <div class="my-7 flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex-1 h-px bg-slate-100"></span>
                        {{ __('Belum punya akun?') }}
                        <span class="flex-1 h-px bg-slate-100"></span>
                    </div>

                    <!-- Sign up link -->
                    <a href="{{ route('psb.daftar') }}"
                        class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-2xl ring-1 ring-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        {{ __('Daftar di sini') }}
                    </a>
                </div>

                <!-- Bantuan & kembali -->
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-slate-500">
                    <a href="/" class="inline-flex items-center gap-1.5 font-medium text-[#0b332c] hover:text-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        {{ __('Kembali ke Beranda') }}
                    </a>
                    <span class="hidden sm:inline text-slate-300">|</span>
                    <span>
                        {{ __('Ada masalah? Hubungi') }}
                        <a href="/.#kontak" class="text-amber-600 font-semibold hover:underline">{{ __('kami') }}</a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vue dari resources/js/app.js (Vite), dipasang setelah halaman siap
        document.addEventListener('DOMContentLoaded', () => Vue.createApp({
            data() {
                return {
                    form: {
                        email: @json(old('email', '')),
                        password: '',
                    },
                    showPassword: false,
                    loading: false,
                    clientErrors: {},
                };
            },
            methods: {
                onSubmit(e) {
                    this.clientErrors = {};
                    if (!this.form.email) {
                        this.clientErrors.email = @js(__('Email wajib diisi.'));
                    }
                    if (!this.form.password) {
                        this.clientErrors.password = @js(__('Password wajib diisi.'));
                    }
                    if (Object.keys(this.clientErrors).length > 0) {
                        e.preventDefault();
                        return;
                    }
                    // Biarkan form submit normal ke Laravel (server-side auth),
                    // cukup tampilkan status loading pada tombol.
                    this.loading = true;
                }
            },
            mounted() {
                lucide.createIcons();
            },
        }).mount('#loginApp'));
    </script>
{{-- Status koneksi internet + tombol muat ulang --}}
@include('partials.status-koneksi')
</body>

</html>
