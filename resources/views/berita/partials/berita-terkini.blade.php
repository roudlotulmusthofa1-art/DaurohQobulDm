{{--
    Landing page > Berita Terkini (sebelum bagian Kontak).
    1 kartu besar (kiri) + sampai 3 kartu kecil (kanan). Tidak tampil bila belum ada berita yang tampil.
    Variabel: $berita (koleksi Berita, sudah memuat foto)
--}}
@if ($berita->isNotEmpty())
@php $utama = $berita->first(); $lain = $berita->slice(1); @endphp
<section id="berita" class="relative isolate overflow-hidden bg-linear-to-b from-[#f7f5f0] via-[#fbfaf6] to-white py-20">
    {{-- Hiasan latar: cahaya hijau & emas + pola titik halus --}}
    <div aria-hidden="true" class="absolute -z-10 -top-32 -left-32 w-[30rem] h-[30rem] rounded-full bg-teal-300/30 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -z-10 top-1/3 -right-40 w-[28rem] h-[28rem] rounded-full bg-amber-200/40 blur-3xl"></div>
    <div aria-hidden="true" class="absolute -z-10 inset-0 bg-[radial-gradient(#0f766e22_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#0b332c]">{{ __('Berita Terkini') }}</h2>
            @include('berita.partials.pemisah', ['kelas' => 'mt-4'])
        </div>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- ===== Kartu besar ===== --}}
            <a href="{{ $utama->url }}" class="group {{ $lain->isEmpty() ? 'lg:col-span-12 w-full max-w-3xl mx-auto' : 'lg:col-span-7' }} flex flex-col bg-white rounded-2xl overflow-hidden ring-1 ring-slate-200/70 shadow-sm hover:shadow-xl transition-shadow">
                <div class="relative aspect-video overflow-hidden border-b-4 border-teal-600">
                    @if ($utama->foto_utama_url)
                    <img src="{{ $utama->foto_utama_url }}" alt="{{ $utama->judul }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    @include('berita.partials.foto-kosong')
                    @endif
                </div>
                <div class="flex-1 flex flex-col p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($utama->lokasi)
                        <span class="px-2.5 py-1 rounded bg-teal-50 text-teal-700 text-[11px] font-bold tracking-widest">{{ $utama->lokasi }}</span>
                        @endif
                        @if ($utama->kategori)
                        <span class="px-2.5 py-1 rounded ring-1 text-[11px] font-bold tracking-wide uppercase {{ $utama->kelas_kategori }}">{{ $utama->kategori }}</span>
                        @endif
                    </div>
                    <h3 class="mt-3 text-xl sm:text-2xl font-extrabold text-[#0b332c] leading-snug group-hover:text-teal-700 transition-colors">{{ $utama->judul }}</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed line-clamp-3">{{ $utama->cuplikan }}</p>
                    <div class="mt-auto pt-6">@include('berita.partials.kartu-penulis', ['b' => $utama])</div>
                </div>
            </a>

            {{-- ===== Kartu kecil ===== --}}
            @if ($lain->isNotEmpty())
            <div class="lg:col-span-5 flex flex-col gap-5">
                @foreach ($lain as $b)
                <a href="{{ $b->url }}" class="group flex bg-white rounded-2xl overflow-hidden ring-1 ring-slate-200/70 shadow-sm hover:shadow-xl transition-shadow min-h-44">
                    <div class="w-32 sm:w-44 shrink-0 overflow-hidden">
                        @if ($b->foto_utama_url)
                        <img src="{{ $b->foto_utama_url }}" alt="{{ $b->judul }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                        @include('berita.partials.foto-kosong')
                        @endif
                    </div>
                    <div class="flex-1 min-w-0 flex flex-col p-4 sm:p-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if ($b->lokasi)
                            <span class="px-2 py-0.5 rounded bg-teal-50 text-teal-700 text-[10px] font-bold tracking-widest">{{ $b->lokasi }}</span>
                            @elseif ($b->kategori)
                            <span class="px-2 py-0.5 rounded ring-1 text-[10px] font-bold tracking-wide uppercase {{ $b->kelas_kategori }}">{{ $b->kategori }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-bold text-[#0b332c] leading-snug line-clamp-2 group-hover:text-teal-700 transition-colors">{{ $b->judul }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 line-clamp-2">{{ $b->cuplikan }}</p>
                        <div class="mt-auto pt-3">@include('berita.partials.kartu-penulis', ['b' => $b, 'kecil' => true])</div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('berita.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-teal-700 text-white text-sm font-bold hover:bg-teal-800 transition-colors">
                {{ __('Lihat semua berita') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
