{{-- Kartu berita tegak (halaman Semua Berita & Berita Lainnya). Variabel: $b (Berita) --}}
<a href="{{ $b->url }}" class="group flex flex-col bg-white rounded-2xl overflow-hidden ring-1 ring-slate-200/70 shadow-sm hover:shadow-xl transition-shadow">
    <div class="aspect-video overflow-hidden border-b-4 border-teal-600">
        @if ($b->foto_utama_url)
        <img src="{{ $b->foto_utama_url }}" alt="{{ $b->judul }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
        @include('berita.partials.foto-kosong')
        @endif
    </div>
    <div class="flex-1 flex flex-col p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-1.5">
            @if ($b->lokasi)
            <span class="px-2 py-0.5 rounded bg-teal-50 text-teal-700 text-[10px] font-bold tracking-widest">{{ $b->lokasi }}</span>
            @endif
            @if ($b->kategori)
            <span class="px-2 py-0.5 rounded ring-1 text-[10px] font-bold tracking-wide uppercase {{ $b->kelas_kategori }}">{{ $b->kategori }}</span>
            @endif
        </div>
        <h3 class="mt-2 font-bold text-[#0b332c] leading-snug line-clamp-2 group-hover:text-teal-700 transition-colors">{{ $b->judul }}</h3>
        <p class="mt-1.5 text-sm text-slate-600 line-clamp-3">{{ $b->cuplikan }}</p>
        <div class="mt-auto pt-4">@include('berita.partials.kartu-penulis', ['b' => $b, 'kecil' => true])</div>
    </div>
</a>
