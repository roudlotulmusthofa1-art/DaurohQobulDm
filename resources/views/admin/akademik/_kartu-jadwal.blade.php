{{-- Kartu satu jadwal. Variabel: $j (JadwalMengajar), $latar, $teks, $ustadz, $inisial, $cari, $lengkap (tampilkan hari & jam) --}}
{{-- data-jadwal/kelas/ustadz/asal dipakai skrip geser (drag & drop) di tampilan Grid --}}
<div data-cari="{{ $cari }}" data-jadwal="{{ $j->id }}" data-kelas="{{ $j->kelas_id }}" data-ustadz="{{ $j->user_id }}"
    data-asal="{{ $j->hari }}-{{ $j->waktu_pelajaran_id }}" data-label="{{ $j->kitab?->nama ?? 'Tanpa kitab' }} · {{ $j->kelas?->nama }}"
    class="group relative rounded-xl px-2.5 py-2 ring-1 {{ $latar }} transition hover:shadow-md">
    <div class="absolute right-1 top-1 flex gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
        <button type="button" title="Ubah jadwal" data-id="{{ $j->id }}"
            data-ubah-jadwal="{{ json_encode($j->only(['hari', 'waktu_pelajaran_id', 'kelas_id', 'kitab_id', 'user_id', 'catatan'])) }}"
            class="rounded-md bg-white/90 p-1 text-slate-500 ring-1 ring-slate-200 hover:text-sky-600">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
        </button>
        <form method="POST" action="{{ route('admin.jadwal.destroy', $j) }}" onsubmit="return confirm('Hapus jadwal ini?')">
            @csrf @method('DELETE')
            <button title="Hapus" class="rounded-md bg-white/90 p-1 text-slate-500 ring-1 ring-slate-200 hover:text-rose-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </form>
    </div>
    @if ($lengkap)
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $j->nama_hari }} · {{ $j->waktu?->nama }}</p>
    @endif
    <p class="pr-10 text-sm font-bold leading-tight {{ $teks }} mb-2">{{ $j->kitab?->nama ?? 'Tanpa kitab' }}</p>
    <p class="mt-0.5 text-sm leading-tight {{ $ustadz ? 'text-slate-600' : 'text-amber-600' }} mb-1 ">{{ $ustadz ?? 'Ustadz belum ditentukan' }}</p>
    <div class="mt-1 flex items-center justify-between gap-1">
        <span class="truncate text-[11px] font-semibold text-slate-700">{{ $j->kelas?->nama }}</span>
        @if ($inisial)<span class="shrink-0 text-[9px] font-bold text-slate-400">{{ $inisial }}</span>@endif
    </div>
    @if ($j->catatan)<p class="mt-0.5 text-[10px] italic text-slate-500">{{ $j->catatan }}</p>@endif
</div>
