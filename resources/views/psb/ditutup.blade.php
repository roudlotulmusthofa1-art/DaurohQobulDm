{{--
    Halaman /psb/daftar saat pendaftaran DINONAKTIFKAN admin (PSB > Data Masuk Peserta > tombol Aktif/Nonaktif).
    Gaya sama dengan halaman sukses pendaftaran.
--}}
@php
$profil = \App\Models\Pengaturan::profilPesantren();
$nomorWa = $profil['telepon'] ? preg_replace('/\D+/', '', preg_replace('/^0/', '62', $profil['telepon'])) : null;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Ditutup | {{ $profil['nama'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        @keyframes naik { from { transform: translateY(16px); opacity: 0; } to { transform: none; opacity: 1; } }
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
            <div class="px-8 pt-10 pb-8 text-center bg-gradient-to-b from-amber-50 to-white">
                <div class="mx-auto w-24 h-24 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center shadow-xl shadow-amber-500/30 ring-8 ring-amber-100">
                    <svg class="w-11 h-11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </div>
                <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-600">Mohon maaf</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Pendaftaran Sedang Ditutup</h1>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                    Saat ini formulir pendaftaran Dauroh Qobul belum dibuka atau sudah ditutup.
                    Silakan pantau pengumuman di halaman utama untuk jadwal pendaftaran berikutnya.
                </p>
            </div>

            <div class="px-6 sm:px-8 pb-8 pt-2 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('landing') }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0f6b5c] px-5 py-3 text-sm font-bold text-white hover:bg-[#0b5a4d] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                    Ke halaman utama
                </a>
                @if ($nomorWa)
                <a href="https://wa.me/{{ $nomorWa }}" target="_blank" rel="noopener"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl ring-1 ring-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM2.25 12c0 4.556 4.03 8.25 9 8.25a9.764 9.764 0 002.555-.337A5.972 5.972 0 0015 21a5.969 5.969 0 003.536-1.159c.23-.17.333-.47.236-.739a5.97 5.97 0 01-.39-1.477A8.24 8.24 0 0021 12c0-4.556-4.03-8.25-9-8.25s-9 3.694-9 8.25z"/></svg>
                    Tanya admin
                </a>
                @endif
            </div>
        </div>
        <p class="mt-6 text-center text-xs text-white/50">{{ $profil['nama'] }}</p>
    </div>
    {{-- Status koneksi internet + tombol muat ulang --}}
    @include('partials.status-koneksi')
</body>

</html>
