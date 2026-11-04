{{-- Form tambah/ubah tahun ajaran (dipakai di dalam <dialog>). Variabel: $aksi, $metode, $judul, $t (null = baru) --}}
@php
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$kunci = $t ? 't' . $t->id : 'baru';
@endphp
<form method="POST" action="{{ $aksi }}" class="p-6 space-y-4">
    @csrf
    @if ($metode !== 'POST') @method($metode) @endif
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-slate-800">{{ $judul }}</h2>
        <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Tahun ajaran (Hijriyah) *') }}</span>
            <input name="nama" required maxlength="50" value="{{ $t?->nama }}" placeholder="{{ __('cth. 1448/1449') }}" class="{{ $input }}"></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Tahun ajaran (Masehi)') }}</span>
            <input name="nama_masehi" maxlength="50" value="{{ $t?->nama_masehi }}" placeholder="{{ __('cth. 2026/2027') }}" class="{{ $input }}"></label>
        <label><span class="{{ $label }}">{{ __('Tanggal mulai') }}</span>
            <input type="date" name="tanggal_mulai" value="{{ $t?->tanggal_mulai?->toDateString() }}" class="{{ $input }}"
                @input="tanggal['hijri-mulai-{{ $kunci }}'] = $event.target.value">
            {{-- Tanggal Hijriyah dihitung Vue (method hijriyah) dari isian di atas --}}
            <span class="mt-1 block text-[11px] text-emerald-700" v-text="hijriyah(tanggal['hijri-mulai-{{ $kunci }}'] ?? '{{ $t?->tanggal_mulai?->toDateString() }}')"></span></label>
        <label><span class="{{ $label }}">{{ __('Tanggal selesai') }}</span>
            <input type="date" name="tanggal_selesai" value="{{ $t?->tanggal_selesai?->toDateString() }}" class="{{ $input }}"
                @input="tanggal['hijri-selesai-{{ $kunci }}'] = $event.target.value">
            {{-- Tanggal Hijriyah dihitung Vue (method hijriyah) dari isian di atas --}}
            <span class="mt-1 block text-[11px] text-emerald-700" v-text="hijriyah(tanggal['hijri-selesai-{{ $kunci }}'] ?? '{{ $t?->tanggal_selesai?->toDateString() }}')"></span></label>
        <label class="col-span-2"><span class="{{ $label }}">{{ __('Keterangan') }}</span>
            <input name="keterangan" maxlength="255" value="{{ $t?->keterangan }}" class="{{ $input }}"></label>
        @unless ($t)
        <label class="col-span-2 flex items-start gap-2.5 rounded-xl bg-amber-50 p-3 ring-1 ring-amber-100 cursor-pointer">
            <input type="checkbox" name="isi_bulan" value="1" checked class="mt-0.5 h-4 w-4 accent-emerald-600">
            <span class="text-xs text-amber-900"><b>{{ __('Buat bulan ajaran otomatis') }}</b> {{ __('mengikuti bulan Hijriyah di antara tanggal mulai dan selesai.') }}</span>
        </label>
        @endunless
    </div>
    <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
        <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">{{ __('Simpan') }}</button>
    </div>
</form>
