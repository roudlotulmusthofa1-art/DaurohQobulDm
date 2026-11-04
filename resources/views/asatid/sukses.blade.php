<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Pendaftaran Asatid Berhasil') }} · {{ __('Dauroh Qobul') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        @keyframes muncul { 0% { transform: scale(.4); opacity: 0; } 60% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(1); } }
        @keyframes gambar { to { stroke-dashoffset: 0; } }
        @keyframes naik { from { transform: translateY(16px); opacity: 0; } to { transform: none; opacity: 1; } }
        .anim-muncul { animation: muncul .6s cubic-bezier(.2, .9, .3, 1.2) both; }
        .anim-centang { stroke-dasharray: 30; stroke-dashoffset: 30; animation: gambar .45s .45s ease-out forwards; }
        .anim-naik { animation: naik .6s .1s ease-out both; }
    </style>
</head>

<body class="min-h-screen antialiased text-slate-800 bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] flex items-center justify-center px-4 py-10 relative overflow-x-hidden">
    <div class="pointer-events-none fixed inset-0 opacity-60"
        style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="pointer-events-none fixed -top-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-emerald-400/25 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-40 -left-24 w-[28rem] h-[28rem] rounded-full bg-amber-300/15 blur-3xl"></div>

    <div class="relative max-w-lg w-full anim-naik">
        <div class="bg-white rounded-[28px] shadow-[0_32px_64px_-24px_rgba(0,0,0,0.5)] overflow-hidden">
            <div class="relative px-8 pt-10 pb-8 text-center bg-gradient-to-b from-emerald-50 to-white">
                <div class="relative mx-auto w-24 h-24">
                    <span class="absolute inset-0 rounded-full bg-emerald-400/30 animate-ping [animation-iteration-count:2]"></span>
                    <div class="anim-muncul relative w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center shadow-xl shadow-emerald-500/40 ring-8 ring-emerald-100">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path class="anim-centang" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">{{ __('Alhamdulillah') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">{{ __('Pendaftaran Berhasil!') }}</h1>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    {{ __('Data atas nama') }} <strong class="text-slate-800">{{ $asatid->nama_lengkap }}</strong> {{ __('sudah masuk ke admin dan akan diverifikasi.') }}
                </p>
            </div>

            <div class="px-6 sm:px-8">
                <div class="relative rounded-2xl bg-gradient-to-br from-[#0b332c] to-[#135347] text-white p-5 text-center overflow-hidden">
                    <div class="pointer-events-none absolute -right-10 -top-10 w-32 h-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
                    <span class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white"></span>
                    <span class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white"></span>
                    <p class="relative text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-200/90">{{ __('Nomor Pendaftaran Asatid') }}</p>
                    <p class="relative mt-1.5 text-3xl font-extrabold tracking-[0.12em]">{{ $asatid->nomor_pendaftaran }}</p>
                    <p class="relative mt-2 text-[11px] text-white/55">{{ __('Mohon simpan atau foto nomor ini') }}</p>
                </div>
            </div>

            <div class="px-6 sm:px-8 pt-7 pb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Langkah selanjutnya') }}</p>
                <ol class="relative space-y-5 before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">{{ __('Data terkirim') }}</p>
                            <p class="text-xs text-slate-500">{{ __('Formulir dan pas foto Anda sudah kami terima.') }}</p>
                        </div>
                    </li>
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-amber-400 text-white flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">{{ __('Verifikasi oleh admin') }}</p>
                            <p class="text-xs text-slate-500">{{ __('Admin memeriksa data pendaftaran Anda.') }}</p>
                        </div>
                    </li>
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">{{ __('Info lewat WhatsApp') }}</p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                {{ __('Jika dinyatakan') }} <strong class="text-slate-700">{{ __('diterima') }}</strong>, {{ __('kami akan mengirim chat ke') }}
                                <strong class="text-emerald-700" dir="ltr">{{ $asatid->no_hp }}</strong>. {{ __('Setelah itu Anda bisa masuk memakai email') }}
                                <strong class="text-slate-700">{{ $asatid->email }}</strong>.
                            </p>
                        </div>
                    </li>
                </ol>
            </div>

            <div class="px-6 sm:px-8 py-5 bg-[#f5f9f7] border-t border-slate-100 text-center">
                <a href="{{ url('/login') }}"
                    class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                    {{ __('Sudah diterima? Masuk akun asatid') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-white/50">{{ __('Dauroh Qobul · Darul Musthofa Tarim') }}</p>
    </div>
</body>

</html>
