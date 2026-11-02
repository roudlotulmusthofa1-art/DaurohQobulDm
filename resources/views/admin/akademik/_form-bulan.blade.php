{{-- Form tambah/ubah bulan ajaran (dipakai di dalam <dialog>). Variabel: $aksi, $metode, $judul, $b (null = baru), $urutanBaru --}}
@php
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$kunci = $b ? 'b' . $b->id : 'baru' . ($urutanBaru ?? '') . uniqid();
@endphp
<form method="POST" action="{{ $aksi }}" class="p-6 space-y-4">
    @csrf
    @if ($metode !== 'POST') @method($metode) @endif
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-slate-800">{{ $judul }}</h2>
        <button type="button" data-tutup class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
    </div>
    <div class="grid grid-cols-3 gap-3">
        <label class="col-span-2"><span class="{{ $label }}">Nama bulan *</span>
            <input name="nama" required maxlength="50" list="nama-bulan-hijriyah" value="{{ $b?->nama }}" placeholder="cth. Muharram 1448" class="{{ $input }}"></label>
        <label><span class="{{ $label }}">Bulan ke-</span>
            <input type="number" name="urutan" min="1" max="24" value="{{ $b?->urutan ?? ($urutanBaru ?? 1) }}" class="{{ $input }}"></label>
        <label class="col-span-3 sm:col-span-1"><span class="{{ $label }}">Tanggal mulai</span>
            <input type="date" name="tanggal_mulai" value="{{ $b?->tanggal_mulai?->toDateString() }}" data-hijri="hijri-bm-{{ $kunci }}" class="{{ $input }}">
            <span id="hijri-bm-{{ $kunci }}" class="mt-1 block text-[11px] text-emerald-700"></span></label>
        <label class="col-span-3 sm:col-span-2"><span class="{{ $label }}">Tanggal selesai</span>
            <input type="date" name="tanggal_selesai" value="{{ $b?->tanggal_selesai?->toDateString() }}" data-hijri="hijri-bs-{{ $kunci }}" class="{{ $input }}">
            <span id="hijri-bs-{{ $kunci }}" class="mt-1 block text-[11px] text-emerald-700"></span></label>
    </div>
    <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-tutup class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
        <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Simpan</button>
    </div>
</form>
