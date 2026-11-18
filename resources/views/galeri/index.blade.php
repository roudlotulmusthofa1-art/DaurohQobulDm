{{--
    Galeri Foto (/galeri): foto unggahan admin + foto berita yang sudah tampil (App\Support\Galeri).
    Saringan Semua · Unggahan admin · Dari berita (?asal=). Foto tampil utuh (tidak dipotong), klik = diperbesar + geser.
    Vue #galeri-foto; teks dari database ditampilkan lewat v-text.
--}}
@extends('layouts.app')

@section('title', __('Galeri Foto') . ' - ' . __('Dauroh Qobul Darul Musthofa Tarim'))

@section('content')
<div class="bg-[#f7f5f0] min-h-screen pt-28 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('landing') }}" class="font-semibold text-teal-700 hover:underline">{{ __('Beranda') }}</a>
            <span class="mx-1.5">/</span>{{ __('Galeri Foto') }}
        </nav>
        <div class="mt-4 text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0b332c]">{{ __('Galeri Foto') }}</h1>
            @include('berita.partials.pemisah', ['kelas' => 'mt-4'])
        </div>

        {{-- Saringan --}}
        <div class="mt-8 flex justify-center gap-2 flex-wrap">
            @foreach (['' => __('Semua'), 'admin' => __('Unggahan admin'), 'berita' => __('Dari berita')] as $kunci => $nama)
            <a href="{{ route('galeri.index', array_filter(['asal' => $kunci])) }}"
                class="px-4 py-2 rounded-full text-sm font-bold ring-1 transition-colors {{ (string) $asal === (string) $kunci ? 'bg-teal-700 text-white ring-teal-700' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">{{ $nama }}</a>
            @endforeach
        </div>

        @if ($foto->isEmpty())
        <p class="mt-16 text-center text-slate-500">{{ __('Belum ada foto.') }}</p>
        @else
        <div id="galeri-foto" class="mt-8" v-cloak>
            {{-- Kolom bertumpuk: tiap foto tampil utuh sesuai bentuk aslinya --}}
            <div class="columns-2 sm:columns-3 lg:columns-4 gap-3">
                <button v-for="(f, i) in foto" :key="f.kunci" type="button" @click="buka(i)"
                    class="group relative block w-full mb-3 break-inside-avoid rounded-xl overflow-hidden bg-slate-200 shadow-sm hover:shadow-lg transition-shadow">
                    <img :src="f.url" :alt="f.keterangan || ''" loading="lazy" class="block w-full h-auto group-hover:scale-105 transition-transform duration-500">
                    <span v-if="f.keterangan || f.tanggal_teks" class="absolute inset-x-0 bottom-0 p-2.5 pt-8 bg-linear-to-t from-black/70 to-transparent text-left opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="block text-xs font-semibold text-white line-clamp-2" v-text="f.keterangan"></span>
                        <span class="block text-[10px] text-white/70" v-text="f.tanggal_teks"></span>
                    </span>
                </button>
            </div>

            {{-- Foto diperbesar --}}
            <div v-if="aktif !== null" @click.self="aktif = null" class="fixed inset-0 z-[80] bg-black/90 flex flex-col items-center justify-center p-4">
                <img :src="foto[aktif].url" alt="" class="max-w-full max-h-[78vh] object-contain rounded-lg">
                <div class="mt-3 max-w-2xl text-center text-white">
                    <p class="text-sm font-semibold" v-text="foto[aktif].keterangan"></p>
                    <p class="text-xs text-white/60" v-text="foto[aktif].tanggal_teks"></p>
                    <a v-if="foto[aktif].berita_url" :href="foto[aktif].berita_url"
                        class="mt-2 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/15 text-xs font-bold hover:bg-white/25">{{ __('Baca berita') }} &rarr;</a>
                </div>
                <button type="button" @click="aktif = null" aria-label="{{ __('Tutup') }}" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/15 text-white text-2xl leading-none hover:bg-white/25">&times;</button>
                <template v-if="foto.length > 1">
                    <button type="button" @click="geser(-1)" aria-label="{{ __('Foto sebelumnya') }}" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&lsaquo;</button>
                    <button type="button" @click="geser(1)" aria-label="{{ __('Foto berikutnya') }}" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/15 text-white text-2xl hover:bg-white/25">&rsaquo;</button>
                </template>
            </div>
        </div>
        <div class="mt-10">{{ $foto->links() }}</div>
        @endif
    </div>
</div>

@if ($foto->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Vue.createApp({
            data: () => ({ foto: @json($foto->items()), aktif: null }),
            methods: {
                buka(i) { this.aktif = i; },
                geser(arah) { this.aktif = (this.aktif + arah + this.foto.length) % this.foto.length; },
            },
            mounted() {
                document.addEventListener('keydown', (e) => {
                    if (this.aktif === null) return;
                    if (e.key === 'Escape') this.aktif = null;
                    if (e.key === 'ArrowLeft') this.geser(-1);
                    if (e.key === 'ArrowRight') this.geser(1);
                });
            },
        }).mount('#galeri-foto');
    });
</script>
@endif
@endsection
