{{--
    Dashboard Admin versi HP — tampil otomatis saat dashboard dibuka dari HP (DashboardController@index).
    Data sama persis dengan dashboard laptop (admin/dashboard.blade.php), tampilan dibuat seperti aplikasi HP:
    bilah atas, kartu angka yang bisa digeser, tombol pintas, daftar "Perlu tindakan", dan menu bawah.
    Bila dibuka di laptop (dipilih manual) tampil di dalam bingkai HP. Vue: tab bawah & lembar "Semua Menu".
--}}
@php
use App\Models\PendaftaranSiswa;
use Illuminate\Support\Facades\Route;

$rute = fn (string $nama, mixed $p = []) => Route::has($nama) ? route($nama, $p) : '#';
$jam = (int) $hariIni->format('H');
$salam = $jam < 11 ? __('Selamat pagi') : ($jam < 15 ? __('Selamat siang') : ($jam < 18 ? __('Selamat sore') : __('Selamat malam')));
$namaDepan = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();

// Kartu angka (bisa digeser ke samping)
$angkaKartu = [
    [__('Santri diterima'), $angka['diterima'], $angka['pendaftar'] ? __('dari :jumlah pendaftar', ['jumlah' => $angka['pendaftar']]) : __('belum ada pendaftar'), 'from-emerald-500 to-teal-600', $rute('admin.santri.index')],
    [__('Menunggu verifikasi'), $angka['menunggu'], __('+:jumlah minggu ini', ['jumlah' => $angka['pendaftar_minggu_ini']]), 'from-amber-400 to-orange-500', $rute('admin.data-masuk.index')],
    [__('Asatid aktif'), $angka['asatid'], $angka['asatid_menunggu'] ? __(':jumlah pendaftar baru', ['jumlah' => $angka['asatid_menunggu']]) : __('tidak ada pendaftar baru'), 'from-sky-500 to-blue-600', $rute('admin.asatid.index')],
    [__('Kehadiran 30 hari'), $angka['kehadiran'] !== null ? $angka['kehadiran'] . '%' : '–', __(':jumlah sesi absensi', ['jumlah' => $angka['absensi_30']]), 'from-violet-500 to-purple-600', $rute('admin.laporan-absensi.index')],
];

// Tombol pintas (ikon heroicons)
$pintas = [
    [__('Data Masuk'), 'admin.data-masuk.index', 'bg-amber-50 text-amber-600', 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z'],
    [__('Santri'), 'admin.santri.index', 'bg-emerald-50 text-emerald-600', 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342'],
    [__('Asatid'), 'admin.asatid.index', 'bg-sky-50 text-sky-600', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
    [__('Pesan'), 'admin.pesan-peserta.index', 'bg-violet-50 text-violet-600', 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
    [__('Buku Kas'), 'admin.buku-kas.index', 'bg-teal-50 text-teal-600', 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3'],
    [__('Kelulusan'), 'admin.kelulusan.index', 'bg-rose-50 text-rose-600', 'M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0'],
    [__('Absensi'), 'admin.laporan-absensi.index', 'bg-indigo-50 text-indigo-600', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    [__('Pengaturan'), 'admin.pengaturan.index', 'bg-slate-100 text-slate-600', 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
];

// Semua menu (untuk lembar "Menu"), sudah disaring sesuai izin role
$semuaMenu = collect(\App\Support\MenuAdmin::untukAkun($user))->filter(fn ($m) => ! empty($m['submenu']));
$warnaStatus = [PendaftaranSiswa::DITERIMA => 'bg-emerald-50 text-emerald-700', PendaftaranSiswa::DITOLAK => 'bg-rose-50 text-rose-600', PendaftaranSiswa::MENUNGGU => 'bg-amber-50 text-amber-700'];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b332c">
    <title>{{ __('Dashboard') }} - {{ __('Dauroh') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .tanpa-scrollbar::-webkit-scrollbar { display: none; }
        .tanpa-scrollbar { scrollbar-width: none; }
        .lembar[open] { animation: lembar-naik .28s cubic-bezier(.2, .8, .2, 1); }
        .lembar[open]::backdrop { animation: redup .28s ease; }
        @keyframes lembar-naik { from { transform: translateY(100%); } }
        @keyframes redup { from { opacity: 0; } }
    </style>
    {{-- Font Arab modern (huruf Arab otomatis memakai font ini) --}}
    @include('partials.font-arab')
</head>
{{-- Di laptop: latar abu-abu + "bingkai HP" di tengah. Di HP: penuh layar. --}}
<body class="bg-slate-200 sm:py-6 antialiased text-slate-800">
<div id="dashboard-hp" class="relative mx-auto w-full sm:max-w-[420px] min-h-screen sm:min-h-[820px] sm:rounded-[40px] sm:ring-8 sm:ring-slate-900 sm:shadow-2xl bg-[#f3f7f5] overflow-hidden">

    {{-- ===== Bilah atas ===== --}}
    <header class="relative z-10 bg-[#0b332c] text-white px-4 pt-4 pb-5 rounded-b-[28px] shadow-lg">
        <div class="flex items-center gap-3">
            <img src="{{ $user->avatarUrl('4ade80', '0b332c') }}" alt="" class="w-11 h-11 rounded-2xl object-cover ring-2 ring-white/20">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] text-emerald-200/80">{{ $salam }},</p>
                <p class="font-extrabold truncate">{{ $namaDepan }}</p>
            </div>
            <a href="{{ $rute('admin.pengaturan.index', ['tab' => 'sesi']) }}" class="max-w-[110px] truncate rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold ring-1 ring-white/15">
                {{ $sesi?->nama ?? __('Sesi') }}
            </a>
            {{-- Tombol ganti bahasa tampilan (versi kecil) --}}
            @include('partials.pilih-bahasa', ['kelas' => 'bg-white/90 !px-2.5', 'ringkas' => true])
            <button type="button" @click="bukaLembar('tindakan')" class="relative w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center" aria-label="{{ __('Perlu tindakan') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                @if ($tindakan['total'])
                <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-rose-500 text-[10px] font-bold flex items-center justify-center ring-2 ring-[#0b332c]">{{ $tindakan['total'] > 99 ? '99+' : $tindakan['total'] }}</span>
                @endif
            </button>
        </div>
        <div class="mt-4 rounded-2xl bg-white/10 ring-1 ring-white/10 px-4 py-3">
            <p class="text-[11px] text-white/60">{{ $hariIni->locale('id')->translatedFormat('l, d F Y') }} · {{ $hijriyah }}</p>
            <p class="mt-0.5 text-sm">
                @if ($angka['menunggu'])
                {{ __('Ada') }} <b class="text-amber-300">{{ __(':jumlah pendaftar', ['jumlah' => $angka['menunggu']]) }}</b> {{ __('menunggu verifikasi.') }}
                @else
                {{ __('Semua pendaftar sudah diverifikasi. 👍') }}
                @endif
            </p>
        </div>
    </header>

    <main class="px-4 pb-28 space-y-6 -mt-1 pt-5">
        {{-- ===== Kartu angka (geser) ===== --}}
        <section class="-mx-4 px-4 flex gap-3 overflow-x-auto snap-x snap-mandatory tanpa-scrollbar">
            @foreach ($angkaKartu as [$judul, $nilai, $ket, $warna, $url])
            <a href="{{ $url }}" class="snap-start shrink-0 w-[150px] rounded-3xl bg-gradient-to-br {{ $warna }} text-white p-4 shadow-md active:scale-95 transition">
                <p class="text-[11px] font-semibold text-white/80">{{ $judul }}</p>
                <p class="mt-1 text-3xl font-extrabold tabular-nums">{{ $nilai }}</p>
                <p class="mt-1 text-[10px] text-white/75 leading-tight">{{ $ket }}</p>
            </a>
            @endforeach
        </section>

        {{-- ===== Tombol pintas ===== --}}
        <section class="rounded-3xl bg-white p-4 shadow-sm">
            <div class="grid grid-cols-4 gap-y-4">
                @foreach ($pintas as [$label, $nama, $warna, $ikon])
                @continue(! \App\Support\MenuAdmin::boleh($user, $nama)) {{-- sembunyikan menu yang tidak diizinkan role ini --}}
                <a href="{{ $rute($nama) }}" class="flex flex-col items-center gap-1.5 active:scale-95 transition">
                    <span class="relative w-12 h-12 rounded-2xl flex items-center justify-center {{ $warna }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                        @if ($label === __('Pesan') && $pesanBelum)
                        <span class="absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center">{{ $pesanBelum }}</span>
                        @endif
                        @if ($label === __('Data Masuk') && $angka['menunggu'])
                        <span class="absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center">{{ $angka['menunggu'] }}</span>
                        @endif
                    </span>
                    <span class="text-[11px] font-semibold text-slate-600 text-center leading-tight">{{ $label }}</span>
                </a>
                @endforeach
            </div>
        </section>

        {{-- ===== Perlu tindakan (3 teratas) ===== --}}
        <section>
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-extrabold text-slate-800">{{ __('Perlu Tindakan') }}</h2>
                @if (count($tindakan['items']) > 3)
                <button type="button" @click="bukaLembar('tindakan')" class="text-xs font-semibold text-emerald-700">{{ __('Lihat semua (:jumlah)', ['jumlah' => $tindakan['total']]) }}</button>
                @endif
            </div>
            <div class="space-y-2">
                @forelse (array_slice($tindakan['items'], 0, 3) as $n)
                <a href="{{ $n['url'] ?? '#' }}" class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm active:scale-[.98] transition">
                    <span class="w-10 h-10 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon'] }}"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[13px] text-slate-700 leading-snug [&_b]:font-semibold [&_b]:text-slate-900">{!! $n['teks'] !!}</span>
                        <span class="block text-[11px] text-slate-400">{{ collect([$n['keterangan'], $n['waktu']])->filter()->implode(' · ') }}</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                @empty
                <p class="rounded-2xl bg-white py-6 text-center text-sm text-slate-400 shadow-sm">{{ __('Tidak ada yang perlu ditindaklanjuti. 🎉') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Jadwal hari ini ===== --}}
        <section>
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-extrabold text-slate-800">{{ __('Jadwal Hari Ini') }}</h2>
                <span class="text-xs text-slate-400">{{ __(':jumlah jam', ['jumlah' => $jadwalHariIni->count()]) }}</span>
            </div>
            <div class="-mx-4 px-4 flex gap-3 overflow-x-auto tanpa-scrollbar">
                @forelse ($jadwalHariIni as $j)
                <div class="shrink-0 w-[200px] rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[11px] font-semibold text-emerald-700">{{ $j->waktu?->nama ?? __('Jam') }}{{ $j->waktu?->jam_mulai ? ' · ' . $j->waktu->jam_mulai : '' }}</p>
                    <p class="mt-1 font-bold text-slate-800 truncate">{{ $j->kitab?->nama ?? __('Kitab belum diatur') }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $j->kelas?->nama }} · {{ $namaUstadz[$j->user_id] ?? $j->ustadz?->name ?? __('Belum ada ustadz') }}</p>
                </div>
                @empty
                <p class="w-full rounded-2xl bg-white py-6 text-center text-sm text-slate-400 shadow-sm">{{ __('Tidak ada jadwal mengajar hari ini.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Pendaftar terbaru ===== --}}
        <section>
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-extrabold text-slate-800">{{ __('Pendaftar Terbaru') }}</h2>
                <a href="{{ $rute('admin.data-masuk.index') }}" class="text-xs font-semibold text-emerald-700">{{ __('Semua') }}</a>
            </div>
            <div class="rounded-3xl bg-white shadow-sm divide-y divide-slate-100 overflow-hidden">
                @forelse ($pendaftarBaru as $p)
                <a href="{{ $rute('admin.pendaftaran.show', $p) }}" class="flex items-center gap-3 px-4 py-3 active:bg-slate-50">
                    @if ($p->foto_url)
                    <img src="{{ $p->foto_url }}" alt="" class="w-10 h-10 rounded-xl object-cover">
                    @else
                    <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-slate-800 truncate">{{ $p->nama_lengkap }}</span>
                        <span class="block text-[11px] text-slate-400">{{ $p->nomor_pendaftaran }} · {{ $p->created_at->locale('id')->diffForHumans() }}</span>
                    </span>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $warnaStatus[$p->status] ?? 'bg-slate-100 text-slate-500' }}">{{ __($p->label_status) }}</span>
                </a>
                @empty
                <p class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada pendaftar.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- ===== Pesan peserta ===== --}}
        <section>
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-extrabold text-slate-800">{{ __('Pesan Peserta') }}</h2>
                <a href="{{ $rute('admin.pesan-peserta.index') }}" class="text-xs font-semibold text-emerald-700">{{ __('Semua') }}</a>
            </div>
            <div class="rounded-3xl bg-white shadow-sm divide-y divide-slate-100 overflow-hidden">
                @forelse ($percakapan->take(4) as $c)
                <a href="{{ $rute('admin.pesan-peserta.show', $c->peserta) }}" class="flex items-center gap-3 px-4 py-3 active:bg-slate-50">
                    <span class="w-10 h-10 rounded-full bg-violet-100 text-violet-700 font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($c->peserta->nama_lengkap, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-slate-800 truncate">{{ $c->peserta->nama_lengkap }}</span>
                        <span class="block text-xs text-slate-500 truncate">{{ \Illuminate\Support\Str::limit($c->terakhir?->isi, 60) }}</span>
                    </span>
                    @if ($c->belum)
                    <span class="shrink-0 min-w-5 h-5 px-1.5 rounded-full bg-emerald-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $c->belum }}</span>
                    @endif
                </a>
                @empty
                <p class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada pesan dari peserta.') }}</p>
                @endforelse
            </div>
        </section>
    </main>

    {{-- ===== Menu bawah ===== --}}
    <nav class="fixed sm:absolute bottom-0 inset-x-0 z-30 mx-auto w-full sm:max-w-[420px] bg-white/95 backdrop-blur border-t border-slate-200 px-2 pt-2 pb-[max(.5rem,env(safe-area-inset-bottom))] sm:rounded-b-[32px]">
        <div class="grid grid-cols-5 text-[10px] font-semibold">
            <a href="#" @click.prevent="keAtas" class="flex flex-col items-center gap-1 py-1 text-emerald-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"/><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"/></svg>
                {{ __('Beranda') }}
            </a>
            <a href="{{ $rute('admin.data-masuk.index') }}" class="relative flex flex-col items-center gap-1 py-1 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                {{ __('PSB') }}
                @if ($angka['menunggu'])<span class="absolute top-0 right-3 w-2 h-2 rounded-full bg-rose-500"></span>@endif
            </a>
            <a href="{{ $rute('admin.santri.index') }}" class="flex flex-col items-center gap-1 py-1 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                {{ __('Santri') }}
            </a>
            <a href="{{ $rute('admin.pesan-peserta.index') }}" class="relative flex flex-col items-center gap-1 py-1 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                {{ __('Pesan') }}
                @if ($pesanBelum)<span class="absolute top-0 right-3 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] flex items-center justify-center">{{ $pesanBelum }}</span>@endif
            </a>
            <button type="button" @click="bukaLembar('menu')" class="flex flex-col items-center gap-1 py-1 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                {{ __('Menu') }}
            </button>
        </div>
    </nav>

    {{-- ===== Lembar: Semua Menu ===== --}}
    <dialog ref="lembarMenu" @click.self="tutupLembar" class="lembar m-0 mt-auto sm:m-auto w-full max-w-none sm:max-w-[420px] max-h-[85vh] overflow-y-auto p-0 rounded-t-[28px] sm:rounded-[28px] bg-white backdrop:bg-black/40">
        <div class="sticky top-0 bg-white px-5 pt-3 pb-2 border-b border-slate-100">
            <span class="mx-auto block w-12 h-1.5 rounded-full bg-slate-200"></span>
            <div class="mt-3 flex items-center justify-between">
                <h2 class="font-extrabold text-slate-800">{{ __('Semua Menu') }}</h2>
                <button type="button" @click="tutupLembar" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100">✕</button>
            </div>
        </div>
        <div class="p-5 space-y-5">
            @foreach ($semuaMenu as $grup)
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $grup['label'] }}</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach ($grup['submenu'] as $sub)
                    <a href="{{ $rute($sub['route']) }}" class="flex flex-col items-center gap-1.5 rounded-2xl bg-[#f3f7f5] px-2 py-3 text-center active:scale-95 transition {{ Route::has($sub['route']) ? '' : 'opacity-40 pointer-events-none' }}">
                        <i data-lucide="{{ $sub['icon'] ?? 'circle' }}" class="w-5 h-5 text-emerald-700"></i>
                        <span class="text-[11px] font-semibold text-slate-700 leading-tight">{{ $sub['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endforeach
            {{-- Pindah ke tampilan laptop (diingat di perangkat ini) --}}
            <a href="{{ route('admin.dashboard.tampilan', 'laptop') }}"
                class="flex items-center justify-center gap-2 w-full rounded-2xl bg-slate-100 py-3 text-sm font-bold text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                {{ __('Ganti ke tampilan laptop') }}
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="w-full rounded-2xl bg-rose-50 py-3 text-sm font-bold text-rose-600">{{ __('Keluar') }}</button>
            </form>
        </div>
    </dialog>

    {{-- ===== Lembar: Perlu Tindakan (semua) ===== --}}
    <dialog ref="lembarTindakan" @click.self="tutupLembar" class="lembar m-0 mt-auto sm:m-auto w-full max-w-none sm:max-w-[420px] max-h-[85vh] overflow-y-auto p-0 rounded-t-[28px] sm:rounded-[28px] bg-white backdrop:bg-black/40">
        <div class="sticky top-0 bg-white px-5 pt-3 pb-2 border-b border-slate-100">
            <span class="mx-auto block w-12 h-1.5 rounded-full bg-slate-200"></span>
            <div class="mt-3 flex items-center justify-between">
                <h2 class="font-extrabold text-slate-800">{{ __('Perlu Tindakan (:jumlah)', ['jumlah' => $tindakan['total']]) }}</h2>
                <button type="button" @click="tutupLembar" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100">✕</button>
            </div>
        </div>
        <div class="p-4 space-y-1">
            @forelse ($tindakan['items'] as $n)
            <a href="{{ $n['url'] ?? '#' }}" class="flex items-start gap-3 rounded-2xl px-2 py-2.5 active:bg-slate-50">
                <span class="w-9 h-9 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna'] }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon'] }}"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-[13px] text-slate-700 leading-snug [&_b]:font-semibold [&_b]:text-slate-900">{!! $n['teks'] !!}</span>
                    <span class="block text-[11px] text-slate-400">{{ collect([$n['keterangan'], $n['waktu']])->filter()->implode(' · ') }}</span>
                </span>
            </a>
            @empty
            <p class="py-8 text-center text-sm text-slate-400">{{ __('Tidak ada yang perlu ditindaklanjuti. 🎉') }}</p>
            @endforelse
        </div>
    </dialog>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            methods: {
                bukaLembar(nama) {
                    (nama === 'menu' ? this.$refs.lembarMenu : this.$refs.lembarTindakan).showModal();
                },
                tutupLembar() {
                    this.$refs.lembarMenu.close();
                    this.$refs.lembarTindakan.close();
                },
                keAtas() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
            },
            mounted() {
                window.lucide?.createIcons(); // ikon menu di lembar "Semua Menu"
            },
        }).mount('#dashboard-hp');
    });
</script>
{{-- Status koneksi internet + tombol muat ulang --}}
@include('partials.status-koneksi', ['posisi' => 'bottom-24 right-4'])
</body>
</html>
