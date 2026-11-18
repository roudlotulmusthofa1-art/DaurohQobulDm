{{-- Foto + nama penulis + tanggal (kartu berita). Variabel: $b (Berita), $kecil (opsional) --}}
<div class="flex items-center gap-2.5 min-w-0">
    @if ($b->penulis_foto)
    <img src="{{ $b->penulis_foto }}" alt="" loading="lazy" class="{{ ($kecil ?? false) ? 'w-7 h-7' : 'w-8 h-8' }} shrink-0 rounded-full object-cover ring-2 ring-white shadow">
    @else
    <span class="{{ ($kecil ?? false) ? 'w-7 h-7' : 'w-8 h-8' }} shrink-0 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($b->penulis_nama, 0, 1)) }}</span>
    @endif
    <span class="text-sm font-semibold text-slate-700 truncate">{{ $b->penulis_nama }}</span>
    <span class="w-1 h-1 shrink-0 rounded-full bg-teal-500"></span>
    <span class="text-xs text-slate-400 shrink-0">{{ $b->tanggal->locale('id')->translatedFormat('d M Y') }}</span>
</div>
