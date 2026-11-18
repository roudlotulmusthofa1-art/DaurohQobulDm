{{--
    Landing page > Galeri Foto (di bawah Berita Terkini): 8 foto terbaru + tombol Lihat semua foto.
    Tidak tampil bila belum ada foto. Variabel: $foto (koleksi baris App\Support\Galeri)
--}}
@if ($foto->isNotEmpty())
<section id="galeri" class="bg-white py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#0b332c]">{{ __('Galeri Foto') }}</h2>
            @include('berita.partials.pemisah', ['kelas' => 'mt-4'])
        </div>

        <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ($foto as $f)
            <a href="{{ route('galeri.index') }}" class="group relative aspect-square rounded-xl overflow-hidden bg-slate-200 shadow-sm">
                <img src="{{ $f['url'] }}" alt="{{ $f['keterangan'] }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @if ($f['keterangan'])
                <span class="absolute inset-x-0 bottom-0 p-2.5 pt-8 bg-linear-to-t from-black/70 to-transparent text-xs font-semibold text-white line-clamp-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $f['keterangan'] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('galeri.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-teal-700 text-white text-sm font-bold hover:bg-teal-800 transition-colors">
                {{ __('Lihat semua foto') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
