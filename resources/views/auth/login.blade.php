<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>


  
    {{-- Vue 3 (global build) --}}
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        
        /* Font senada dengan desain: bulat & tegas */
        body {
            font-family: 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
        }

        /* ==========================================================
           GANTI FOTO BACKGROUND DI SINI
           Ganti url() di bawah dengan foto custom Anda,
           atau hapus baris background-image agar hanya pakai gradient.
        ========================================================== */
        .hero-bg {
            background-image:
                linear-gradient(160deg, rgba(15, 118, 110, 0.85) 0%, rgba(45, 212, 191, 0.55) 20%, rgba(209, 250, 229, 0.25) 100%);
            background-image: linear-gradient(160deg, rgba(15, 118, 110, 0.85)), url('/images/darul musthofa.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-[#f4f7f7] scale-105">

    <div id="loginApp" class="min-h-screen flex flex-col ">

        <!-- ===================== HERO / HEADER ===================== -->
        <div class="relative hero-bg h-64 shrink-0">

            <!-- Wave putih di bagian bawah hero -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 375 60" preserveAspectRatio="none"
                style="height:60px;">
                <path fill="#f4f7f7"
                    d="M0,32 C60,60 100,0 160,18 C210,33 230,60 280,45 C320,33 350,18 375,28 L375,60 L0,60 Z">
                </path>
            </svg>


            <div class="absolute left-1/2 -translate-x-1/2 -bottom-12 z-10">
                <div
                    class="w-36 h-36 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden ring-4 ring-white">
                    <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo" class="w-full h-full object-cover p-4">
                </div>
            </div>
        </div>

        <!-- ===================== FORM SIGN IN ===================== -->
        <div class="flex-1 px-8 pt-16 pb-8 max-w-md mx-auto w-full">

            <h1 class="text-3xl font-bold text-slate-800 mb-6 text-center">Masuk Ke Akun</h1>

            {{-- Notifikasi error umum (misal email/password salah) --}}
            @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm px-3 py-2">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" @submit="onSubmit">
                @csrf
                <div class="bg-white border border-slate-400 shadow-2xl rounded-xl px-5 md:px-10 pt-8 pb-10 w-full max-w-sm lg:max-w-250">
                    <!-- Email -->
                    <div class="mb-5">
                        <label for="email" class="block text-sm text-slate-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            v-model="form.email"
                            class="w-full border border-slate-300 rounded-lg focus:border-teal-600 outline-none py-2 px-3 mt-2 text-slate-800 transition-colors"
                            placeholder="">
                        <p class="text-xs text-red-500 mt-1" v-if="clientErrors.email">@{{ clientErrors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-2">
                        <label for="password" class="block text-sm text-slate-500 mb-1">Password</label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                v-model="form.password"
                                class="w-full border border-slate-300 rounded-lg focus:border-teal-600 outline-none py-2 px-3 mt-2 text-slate-800 transition-colors"
                                placeholder="">
                           <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-7 -translate-y-1/2 text-slate-400 hover:text-slate-600">
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
                        <p class="text-xs text-red-500 mt-1" v-if="clientErrors.password">@{{ clientErrors.password }}
                        </p>
                    </div>

                    <!-- Forgot password -->
                    <div class="text-right mb-6">
                        <a href="#" class="text-xs text-slate-400 hover:text-teal-600">
                            Lupa password?
                        </a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="loading"
                        class="w-full bg-slate-800 hover:bg-slate-900 disabled:opacity-60 text-white font-medium py-3 rounded-4xl transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    
                        <!-- SVG loading -->
                        <svg v-if="loading" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                    
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                            </path>
                        </svg>
                    
                        <!-- SVG masuk -->
                        <svg v-else class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2" />
                        </svg>
                    
                        <!-- Tulisan -->
                        <span>
                            @{{ loading ? 'Proses...' : 'Masuk' }}
                        </span>
                    
                    </button>
            </form>

            

            <!-- Sign up link -->
            <p class="text-center text-sm text-slate-500 mt-8">
                Belum Punya Akun?
                <a href="#" class="text-teal-600 font-medium hover:underline">Datar di sini</a>
            </p>

            <!-- ada maslah -->

            <p class="text-center text-sm text-slate-500 mt-2">
                Ada masalah? Hubungi
                <a href="/.#kontak" class=" text-amber-600 font-medium hover:underline"> kami</a>
            </p>
            <p class="text-center text-sm text-slate-500 mt-2">
                Kembali Ke
                <a href="/" class=" text-teal-800 font-medium hover:underline">Beranda</a>
            </p>
        </div>
    </div>
    </div>

    <script>
        const { createApp,ref } = Vue;
        createApp({
            setup() {
            const showPassword = ref(false);
            
            return {
            showPassword
            };
            },
            data() {
                return {
                    form: {
                        email: '{{ old('email') }}',
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
                        this.clientErrors.email = 'Email wajib diisi.';
                    }
                    if (!this.form.password) {
                        this.clientErrors.password = 'Password wajib diisi.';
                    }
                    if (Object.keys(this.clientErrors).length > 0) {
                        e.preventDefault();
                        return;
                    }
                    // Biarkan form submit normal ke Laravel (server-side auth),
                    // cukup tampilkan status loading pada tombol.
                    this.loading = true;
                }
            }
        }).mount('#loginApp');

        lucide.createIcons();
    </script>
</body>

</html>