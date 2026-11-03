{{--
    Layout semua halaman akun siswa/peserta (disalin dari layout asatid agar tampilannya sama).
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
    <title>@hasSection('judul')@yield('judul') · @endif Peserta Dauroh Qobul</title>
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
$namaPeserta = $siswa?->nama_lengkap ?? $user->name;
$fotoPeserta = $siswa?->foto_url;
$inisialPeserta = mb_strtoupper(mb_substr($namaPeserta, 0, 1));
// Lonceng: status dibaca/dihapus per akun (App\Support\StatusNotifikasi)
$lonceng = \App\Support\StatusNotifikasi::terapkan($notif ?? []);
@endphp

<body class="min-h-screen bg-[#eef4f1] text-slate-800 antialiased">
    {{-- Pita "Anda melihat sebagai ..." saat Super Admin Masuk sebagai (hanya tampil bila aktif) --}}
    @include('partials.pita-masuk-sebagai')

    {{-- ================= HEADER ================= --}}
    {{-- Aplikasi Vue #header-akun: menu lonceng & profil + tombol kembali (skrip di bawah).
         Teks dari database di sini diberi v-pre agar tidak dibaca Vue sebagai template. --}}
    <header id="header-akun" class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-[#0b332c]/[0.06]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">

            @hasSection('judul')
                {{-- Selalu kembali ke halaman sebelumnya; href = cadangan (section 'kembali' atau beranda) --}}
                <a href="@hasSection('kembali')@yield('kembali')@else{{ route('siswa.dashboard') }}@endif" title="Kembali ke halaman sebelumnya" @click="kembali"
                    class="w-10 h-10 shrink-0 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-600 leading-none">Akun Peserta</p>
                    <h1 class="mt-1 text-base sm:text-lg font-extrabold text-slate-800 truncate leading-tight" v-pre>@yield('judul')</h1>
                </div>
            @else
                <a href="/" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span class="w-10 h-10 shrink-0 rounded-full bg-[#eff5f0] ring-1 ring-[#0b332c]/10 overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </span>
                    <span class="min-w-0">
                        <span class="block font-extrabold text-[#0b332c] leading-tight truncate">Dauroh Qobul</span>
                        <span class="block text-[11px] text-slate-400 leading-tight">Akun Peserta</span>
                    </span>
                </a>
            @endif

            {{-- Notifikasi --}}
            <div class="relative">
                <button type="button" @click.stop="bukaTutup('notif')" aria-haspopup="true"
                    :aria-expanded="menuBuka === 'notif' ? 'true' : 'false'" title="Notifikasi"
                    class="relative w-10 h-10 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    <span v-if="notifTotal > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white" v-text="notifLencana"></span>
                </button>
                <div v-show="menuBuka === 'notif'" @click.stop style="display: none"
                    class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-sm bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50 -mr-14 sm:mr-0">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <p class="text-sm font-bold text-slate-800">Notifikasi</p>
                        <span v-if="notifTotal > 0" class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-100 rounded-full px-2 py-0.5" v-text="notifTotal + ' baru'"></span>
                    </div>
                    @include('partials.lonceng-notifikasi')
                </div>
            </div>

            {{-- Profil + Keluar --}}
            <div class="relative">
                <button type="button" @click.stop="bukaTutup('profil')" aria-haspopup="true"
                    :aria-expanded="menuBuka === 'profil' ? 'true' : 'false'"
                    class="flex items-center gap-2.5 rounded-2xl p-1 sm:pr-3 hover:bg-[#f5f9f7] transition-colors">
                    @if ($fotoPeserta)
                    <img src="{{ $fotoPeserta }}" alt="Foto profil" class="w-9 h-9 rounded-xl object-cover ring-2 ring-emerald-100">
                    @else
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white font-bold flex items-center justify-center">{{ $inisialPeserta }}</span>
                    @endif
                    <span class="hidden sm:flex flex-col items-start leading-tight text-left" v-pre>
                        <span class="text-[13px] font-semibold text-slate-800 max-w-[10rem] truncate">{{ $namaPeserta }}</span>
                        <span class="text-[11px] text-slate-400">{{ $siswa?->nomor_pendaftaran ?? 'Peserta' }}</span>
                    </span>
                    <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div v-show="menuBuka === 'profil'" @click.stop style="display: none"
                    class="absolute right-0 mt-2 w-60 bg-white rounded-2xl ring-1 ring-[#0b332c]/10 shadow-[0_24px_48px_-16px_rgba(11,51,44,0.35)] overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-slate-100" v-pre>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $namaPeserta }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#f5f9f7] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        Beranda
                    </a>
                    <a href="{{ route('siswa.profil') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#f5f9f7] transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('siswa.logout') }}" @submit="konfirmasi($event, 'Keluar dari akun peserta?')" class="border-t border-slate-100">
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
        @if (session('success'))
        <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-900 text-sm">
            <span class="w-7 h-7 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </span>
            <span class="pt-0.5">{{ session('success') }}</span>
        </div>
        @endif
        @if (session('error'))
        <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-amber-900 text-sm">
            <span class="w-7 h-7 shrink-0 rounded-full bg-amber-400 text-white flex items-center justify-center font-bold">!</span>
            <span class="pt-0.5">{{ session('error') }}</span>
        </div>
        @endif
        @yield('content')
    </main>

    <footer class="pb-8 text-center text-xs text-slate-400">&copy; {{ date('Y') }} Dauroh Qobul · Darul Musthofa Tarim</footer>

    {{-- Aplikasi Vue header (window.Vue dari resources/js/app.js): menu lonceng & profil + tombol kembali --}}
    @include('partials.lonceng-notifikasi-skrip')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.Vue.createApp({
                mixins: [window.mixinNotifikasi], // isi lonceng: partials/lonceng-notifikasi-skrip
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
                    // Kembali ke halaman sebelumnya. Bila tidak ada (mis. dibuka langsung / tab baru): pindah ke href.
                    // Halaman sebelumnya dilewati (pakai href) bila: halaman yang sama, halaman login,
                    // atau cocok dengan data-lewati="potongan-alamat,..." (mis. halaman yang otomatis melempar balik ke sini).
                    kembali(e) {
                        e.preventDefault();
                        const tautan = e.currentTarget.getAttribute('href');
                        const lewati = (e.currentTarget.dataset.lewati || '').split(',').map((x) => x.trim()).filter(Boolean);
                        let asal = null;
                        try { asal = document.referrer ? new URL(document.referrer) : null; } catch (err) { asal = null; }
                        const layak = asal && asal.origin === location.origin
                            && asal.pathname !== location.pathname
                            && !asal.pathname.startsWith('/login')
                            && !lewati.some((p) => asal.pathname.includes(p));
                        if (history.length > 1 && layak) {
                            history.back();
                            return;
                        }
                        // Tidak ada halaman sebelumnya yang layak -> buka alamat cadangan (tab tidak ditutup)
                        location.href = tautan;
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
        });
    </script>
    @include('partials.pratinjau-foto') {{-- klik foto bertanda data-perbesar -> tampil besar --}}
    @include('partials.vue-metode')
    @stack('scripts')
</body>

</html>
