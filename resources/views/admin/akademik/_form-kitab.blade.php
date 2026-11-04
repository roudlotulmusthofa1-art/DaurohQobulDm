{{-- Form tambah/ubah kitab (di dalam <dialog>). Variabel: $aksi, $metode, $judul, $k (null = baru), $bidangList, $tahunList, $tahunAktif --}}
@php
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$bulanDipilih = $k?->bulan_ke ?? [];
$tahunDipilih = $k?->tahun_ajaran_id ?? $tahunAktif?->id ?? $tahunList->first()?->id;
// Kunci form untuk data Vue "tahunForm" di halaman kurikulum (satu nilai per form)
$kunciForm = $k ? 'k' . $k->id : 'baru';
$pilih = 'cursor-pointer rounded-xl px-3 py-2 text-xs font-semibold ring-1 ring-slate-200 bg-white text-slate-600 has-[:checked]:bg-emerald-600 has-[:checked]:text-white has-[:checked]:ring-emerald-600 transition-colors';
@endphp
<form method="POST" action="{{ $aksi }}" class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
    @csrf
    @if ($metode !== 'POST') @method($metode) @endif
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-slate-800">{{ $judul }}</h2>
        <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Nama kitab *') }}</span>
            <input name="nama" required maxlength="150" value="{{ $k?->nama }}" placeholder="{{ __('cth. Al-Ajurrumiyyah') }}" class="{{ $input }}"></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Nama Arab') }}</span>
            <input name="nama_arab" maxlength="150" value="{{ $k?->nama_arab }}" dir="rtl" placeholder="الآجرومية" class="{{ $input }}"></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Bidang ilmu') }}</span>
            <select name="bidang_ilmu_id" class="{{ $input }}">
                <option value="">{{ __('— Tanpa bidang —') }}</option>
                @foreach ($bidangList as $b)<option value="{{ $b->id }}" @selected($k?->bidang_ilmu_id === $b->id)>{{ $b->nama }}</option>@endforeach
            </select></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">{{ __('Frekuensi (x/minggu) *') }}</span>
            <input type="number" name="frekuensi" min="1" max="30" required value="{{ $k?->frekuensi ?? 2 }}" class="{{ $input }}"></label>
        <label class="col-span-2"><span class="{{ $label }}">{{ __('Tahun ajaran *') }}</span>
            <select name="tahun_ajaran_id" required class="{{ $input }}" @change="tahunForm['{{ $kunciForm }}'] = $event.target.value">
                @forelse ($tahunList as $t)
                <option value="{{ $t->id }}" @selected($tahunDipilih === $t->id)>{{ $t->nama }}{{ $t->nama_masehi ? ' (' . $t->nama_masehi . ')' : '' }} · {{ __(':jumlah bulan', ['jumlah' => $t->bulan->count()]) }}{{ $t->is_active ? ' · ' . __('aktif') : '' }}</option>
                @empty
                <option value="">{{ __('Belum ada tahun ajaran') }}</option>
                @endforelse
            </select></label>
    </div>

    <div><span class="{{ $label }}">{{ __('Diajarkan di bulan ke') }} <span class="font-normal text-slate-400">{{ __('(kosongkan = semua bulan)') }}</span></span>
        @forelse ($tahunList as $t)
        {{-- Hanya bulan milik tahun ajaran yang dipilih yang tampil & ikut terkirim (Vue: tahunTerpilih) --}}
        <div class="mt-1.5 grid grid-cols-2 sm:grid-cols-3 gap-1.5"
            :class="{ hidden: tahunTerpilih('{{ $kunciForm }}', '{{ $tahunDipilih }}') != '{{ $t->id }}' }">
            @forelse ($t->bulan as $b)
            <label class="{{ $pilih }}">
                <input type="checkbox" name="bulan_ke[]" value="{{ $b->urutan }}" class="sr-only"
                    :disabled="tahunTerpilih('{{ $kunciForm }}', '{{ $tahunDipilih }}') != '{{ $t->id }}'" @checked($k?->tahun_ajaran_id === $t->id && in_array($b->urutan, $bulanDipilih))>
                <span class="block">{{ __('Bulan ke-:urutan', ['urutan' => $b->urutan]) }}</span>
                <span class="block text-[10px] font-normal opacity-75 truncate">{{ $b->nama }}</span>
            </label>
            @empty
            <p class="col-span-full text-xs text-slate-400">{{ __('Tahun ajaran ini belum punya bulan. Isi bulannya di menu Tahun & Bulan Ajaran.') }}</p>
            @endforelse
        </div>
        @empty
        <p class="mt-1.5 text-xs text-slate-400">{{ __('Buat tahun ajaran dulu di') }} <a href="{{ route('admin.tahun-ajaran.index') }}" class="font-semibold text-emerald-600 underline">{{ __('Tahun & Bulan Ajaran') }}</a>.</p>
        @endforelse
    </div>

    <label class="block"><span class="{{ $label }}">{{ __('Deskripsi') }}</span>
        <textarea name="deskripsi" rows="2" maxlength="1000" class="{{ $input }}">{{ $k?->deskripsi }}</textarea></label>

    <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
        <button @disabled($tahunList->isEmpty()) class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347] disabled:opacity-50">{{ __('Simpan') }}</button>
    </div>
</form>
