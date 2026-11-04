{{--
    Kartu "fitur belum tersedia" untuk halaman asatid.
    Dipakai: @include('asatid.partials.segera-hadir', [
        'judul' => '...', 'teks' => '...', 'warna' => 'from-sky-400 to-blue-600', 'ikon' => 'path svg', 'poin' => ['...', '...'],
    ])
--}}
<div class="relative overflow-hidden rounded-[28px] bg-white ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)] px-6 py-10 sm:px-10 sm:py-14 text-center">
    <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-64 rounded-full bg-gradient-to-br {{ $warna }} opacity-10 blur-3xl"></div>

    <div class="relative">
        <span class="mx-auto w-20 h-20 rounded-[26px] bg-gradient-to-br {{ $warna }} text-white flex items-center justify-center shadow-xl shadow-black/10">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
        </span>
        <span class="mt-6 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 bg-slate-100 rounded-full px-3 py-1">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> {{ __('Segera hadir') }}
        </span>
        <h2 class="mt-3 text-xl sm:text-2xl font-extrabold text-slate-800">{{ $judul }}</h2>
        <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto leading-relaxed">{{ $teks }}</p>

        @if (!empty($poin))
        <ul class="mt-6 max-w-sm mx-auto space-y-2 text-left">
            @foreach ($poin as $p)
            <li class="flex items-center gap-3 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3 text-sm text-slate-600">
                <span class="w-6 h-6 shrink-0 rounded-full bg-white ring-1 ring-slate-200 text-slate-400 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
                {{ $p }}
            </li>
            @endforeach
        </ul>
        @endif

        <a href="{{ route('asatid.dashboard') }}"
            class="mt-8 inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('Kembali ke beranda') }}
        </a>
    </div>
</div>
