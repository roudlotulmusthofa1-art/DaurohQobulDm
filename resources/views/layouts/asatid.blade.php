{{--
    Layout semua halaman akun asatid.
    Halaman mengisi:
      @section('judul', 'Nama Halaman')   -> judul di header (kosongkan untuk beranda)
      @section('content')                  -> isi halaman
      @section('kembali', url(...))         -> (opsional) panah kiri atas kembali ke HALAMAN SEBELUMNYA,
                                              url ini dipakai kalau tidak ada halaman sebelumnya
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b332c">
    <title>@hasSection('judul')@yield('judul') · @endif Asatid Dauroh Qobul</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        @keyframes naik { from { transform: translateY(12px); opacity: 0; } to { transform: none; opacity: 1; } }
        .anim-naik { animation: naik .5s ease-out both; }
    </style>
    @stack('styles')
</head>

@php
$namaAsatid = $asatid?->nama_lengkap ?? $user->name;
$fotoAsatid = $asatid?->foto_url;
$inisialAsatid = mb_strtoupper(mb_substr($namaAsatid, 0, 1));
$jumlahNotif = count($notif ?? []);
@endphp

<body class="min-h-screen bg-[#eef4f1] text-slate-800 antialiased">

    {{-- ================= HEADER ================= --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-[#0b332c]/[0.06]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">

            @hasSection('judul')
                @hasSection('kembali')
                <a href="@yield('kembali')" title="Kembali ke halaman sebelumnya" data-kembali-sebelumnya
                @else
                <a href="{{ route('asatid.dashboard') }}" title="Kembali ke beranda"
                @endif
                    class="w-10 h-10 shrink-0 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-600 leading-none">Akun Asatid</p>
                    <h1 class="mt-1 text-base sm:text-lg font-extrabold text-slate-800 truncate leading-tight">@yield('judul')</h1>
                </div>
            @else
                <a href="/" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span class="w-10 h-10 shrink-0 rounded-full bg-[#eff5f0] ring-1 ring-[#0b332c]/10 overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </span>
                    <span class="min-w-0">
                        <span class="block font-extrabold text-[#0b332c] leading-tight truncate">Dauroh Qobul</span>
                        <span class="block text-[11px] text-slate-400 leading-tight">Akun Asatid</span>
                    </span>
                </a>
            @endif

            {{-- Notifikasi --}}
            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-tombol aria-haspopup="true" aria-expanded="false" title="Notifikasi"
                    class="relative w-10 h-10 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    @if ($jumlahNotif > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white">{{ $jumlahNotif }}</span>
                    @endif
                </button>
                <div data-dropdown-isi hidden
                    class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-sm bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50 -mr-14 sm:mr-0">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-800">Notifikasi</p>
                        @if ($jumlahNotif > 0)
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-100 rounded-full px-2 py-0.5">{{ $jumlahNotif }} baru</span>
                        @endif
                    </div>
                    <div class="max-h-[60vh] overflow-y-auto">
                        @forelse ($notif ?? [] as $n)
                        <a href="{{ $n['url'] }}" class="flex items-start gap-3 px-4 py-3 hover:bg-[#f5f9f7] transition-colors border-b border-slate-50 last:border-0">
                            <span class="w-9 h-9 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna'] }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon'] }}"/></svg>
                            </span>
                            <span class="min-w-0">
                                {{-- teks sudah di-escape di AsatidDashboardController --}}
                                <span class="block text-[13px] text-slate-700 leading-snug [&_b]:font-semibold [&_b]:text-slate-900">{!! $n['teks'] !!}</span>
                                @if ($n['ket'])
                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $n['ket'] }}</span>
                                @endif
                            </span>
                        </a>
                        @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada notifikasi baru</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Profil + Keluar --}}
            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-tombol aria-haspopup="true" aria-expanded="false"
                    class="flex items-center gap-2.5 rounded-2xl p-1 sm:pr-3 hover:bg-[#f5f9f7] transition-colors">
                    @if ($fotoAsatid)
                    <img src="{{ $fotoAsatid }}" alt="Foto profil" class="w-9 h-9 rounded-xl object-cover ring-2 ring-emerald-100">
                    @else
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white font-bold flex items-center justify-center">{{ $inisialAsatid }}</span>
                    @endif
                    <span class="hidden sm:flex flex-col items-start leading-tight text-left">
                        <span class="text-[13px] font-semibold text-slate-800 max-w-[10rem] truncate">{{ $namaAsatid }}</span>
                        <span class="text-[11px] text-slate-400">Asatid</span>
                    </span>
                    <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div data-dropdown-isi hidden
                    class="absolute right-0 mt-2 w-60 bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $namaAsatid }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('asatid.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#f5f9f7] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        Beranda
                    </a>
                    <a href="{{ route('asatid.profil') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#f5f9f7] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('asatid.logout') }}" onsubmit="return confirm('Keluar dari akun asatid?')" class="border-t border-slate-100">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 sm:pt-8 pb-12 anim-naik">
        @if (session('error'))
        <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-amber-900 text-sm">
            <span class="w-7 h-7 shrink-0 rounded-full bg-amber-400 text-white flex items-center justify-center font-bold">!</span>
            <span class="pt-0.5">{{ session('error') }}</span>
        </div>
        @endif
        @yield('content')
    </main>

    <footer class="pb-8 text-center text-xs text-slate-400">&copy; {{ date('Y') }} Dauroh Qobul · Darul Musthofa Tarim</footer>

    {{-- Menu kecil di header: klik untuk buka, klik di luar / Esc untuk tutup --}}
    <script>
        (function () {
            const semua = () => document.querySelectorAll('[data-dropdown]');
            const tutup = (kecuali) => semua().forEach((d) => {
                if (d === kecuali) return;
                d.querySelector('[data-dropdown-isi]').hidden = true;
                d.querySelector('[data-dropdown-tombol]').setAttribute('aria-expanded', 'false');
            });
            semua().forEach((d) => {
                const tombol = d.querySelector('[data-dropdown-tombol]');
                const isi = d.querySelector('[data-dropdown-isi]');
                tombol.addEventListener('click', (e) => {
                    e.stopPropagation();
                    tutup(d);
                    isi.hidden = !isi.hidden;
                    tombol.setAttribute('aria-expanded', isi.hidden ? 'false' : 'true');
                });
                isi.addEventListener('click', (e) => e.stopPropagation());
            });
            document.addEventListener('click', () => tutup());
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
        })();
    </script>
    {{--
        Tombol [data-kembali-sebelumnya]: kembali ke halaman sebelumnya.
        Kalau halaman ini dibuka di tab baru (mis. Detail dari absensi), tab ditutup
        sehingga kembali ke tab absensi. Kalau tidak bisa, pindah ke alamat di href.
    --}}
    <script>
        document.querySelectorAll('[data-kembali-sebelumnya]').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                const dariSitusIni = document.referrer && new URL(document.referrer).origin === location.origin;
                if (history.length > 1 && dariSitusIni) {
                    history.back();
                    return;
                }
                window.close();
                setTimeout(() => { location.href = el.getAttribute('href'); }, 250);
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
