<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Berhasil | Ribath</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }

        /* Animasi tanda centang */
        @keyframes muncul { 0% { transform: scale(.4); opacity: 0; } 60% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(1); } }
        @keyframes gambar { to { stroke-dashoffset: 0; } }
        @keyframes naik { from { transform: translateY(16px); opacity: 0; } to { transform: none; opacity: 1; } }
        .anim-muncul { animation: muncul .6s cubic-bezier(.2, .9, .3, 1.2) both; }
        .anim-centang { stroke-dasharray: 30; stroke-dashoffset: 30; animation: gambar .45s .45s ease-out forwards; }
        .anim-naik { animation: naik .6s .1s ease-out both; }
    </style>
</head>

<body class="min-h-screen antialiased text-slate-800 bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] flex items-center justify-center px-4 py-10 relative overflow-x-hidden">
    {{-- Hiasan latar --}}
    <div class="pointer-events-none fixed inset-0 opacity-60"
        style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="pointer-events-none fixed -top-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-emerald-400/25 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-40 -left-24 w-[28rem] h-[28rem] rounded-full bg-amber-300/15 blur-3xl"></div>

    <div class="relative max-w-lg w-full anim-naik">
        <div class="bg-white rounded-[28px] shadow-[0_32px_64px_-24px_rgba(0,0,0,0.5)] overflow-hidden">
            {{-- Bagian atas: centang --}}
            <div class="relative px-8 pt-10 pb-8 text-center bg-gradient-to-b from-emerald-50 to-white">
                <div class="relative mx-auto w-24 h-24">
                    <span class="absolute inset-0 rounded-full bg-emerald-400/30 animate-ping [animation-iteration-count:2]"></span>
                    <div class="anim-muncul relative w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center shadow-xl shadow-emerald-500/40 ring-8 ring-emerald-100">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path class="anim-centang" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Alhamdulillah</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Pendaftaran Berhasil!</h1>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    Data atas nama <strong class="text-slate-800">{{ $pendaftar->nama_lengkap }}</strong> sudah masuk ke admin dan akan diverifikasi.
                </p>
            </div>

            {{-- Nomor pendaftaran (model tiket) --}}
            <div class="px-6 sm:px-8">
                <div class="relative rounded-2xl bg-gradient-to-br from-[#0b332c] to-[#135347] text-white p-5 text-center overflow-hidden">
                    <div class="pointer-events-none absolute -right-10 -top-10 w-32 h-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
                    {{-- lubang tiket --}}
                    <span class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white"></span>
                    <span class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white"></span>
                    <p class="relative text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-200/90">Nomor Pendaftaran</p>
                    <p class="relative mt-1.5 text-3xl font-extrabold tracking-[0.12em]">{{ $pendaftar->nomor_pendaftaran }}</p>
                    <p class="relative mt-2 text-[11px] text-white/55">Mohon simpan atau foto nomor ini</p>
                </div>
            </div>

            {{-- Langkah selanjutnya --}}
            <div class="px-6 sm:px-8 pt-7 pb-8">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Langkah selanjutnya</p>
                <ol class="relative space-y-5 before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">Data terkirim</p>
                            <p class="text-xs text-slate-500">Formulir dan berkas Anda sudah kami terima.</p>
                        </div>
                    </li>
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-amber-400 text-white flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">Verifikasi oleh admin</p>
                            <p class="text-xs text-slate-500">Admin memeriksa data dan berkas pendaftaran Anda.</p>
                        </div>
                    </li>
                    <li class="relative flex gap-4">
                        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center ring-4 ring-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                        </span>
                        <div class="pt-1">
                            <p class="text-sm font-bold text-slate-800">Info lewat WhatsApp</p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Jika dinyatakan <strong class="text-slate-700">diterima</strong>, kami akan mengirim chat WhatsApp ke
                                <strong class="text-emerald-700" dir="ltr">{{ $pendaftar->no_hp_wali }}</strong> berisi email dan kode akses akun siswa.
                            </p>
                        </div>
                    </li>
                </ol>
            </div>

            {{-- Tombol masuk --}}
            <div class="px-6 sm:px-8 py-5 bg-[#f5f9f7] border-t border-slate-100 text-center">
                <a href="{{ url('/login') }}"
                    class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                    Sudah diterima? Masuk akun siswa
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-white/50">Dauroh Qobul · Darul Musthofa Tarim</p>
    </div>
</body>

</html>
