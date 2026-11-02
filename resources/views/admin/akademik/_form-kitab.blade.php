{{-- Form tambah/ubah kitab (di dalam <dialog>). Variabel: $aksi, $metode, $judul, $k (null = baru), $bidangList, $kelasList --}}
@php
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$kelasDipilih = $k ? $k->kelas->pluck('id')->all() : [];
$bulanDipilih = $k?->bulan_ke ?? [];
$pilih = 'cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold ring-1 ring-slate-200 bg-white text-slate-600 has-[:checked]:bg-emerald-600 has-[:checked]:text-white has-[:checked]:ring-emerald-600 transition-colors';
@endphp
<form method="POST" action="{{ $aksi }}" class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
    @csrf
    @if ($metode !== 'POST') @method($metode) @endif
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-slate-800">{{ $judul }}</h2>
        <button type="button" data-tutup class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">Nama kitab *</span>
            <input name="nama" required maxlength="150" value="{{ $k?->nama }}" placeholder="cth. Al-Ajurrumiyyah" class="{{ $input }}"></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">Nama Arab</span>
            <input name="nama_arab" maxlength="150" value="{{ $k?->nama_arab }}" dir="rtl" placeholder="الآجرومية" class="{{ $input }}"></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">Bidang ilmu</span>
            <select name="bidang_ilmu_id" class="{{ $input }}">
                <option value="">— Tanpa bidang —</option>
                @foreach ($bidangList as $b)<option value="{{ $b->id }}" @selected($k?->bidang_ilmu_id === $b->id)>{{ $b->nama }}</option>@endforeach
            </select></label>
        <label class="col-span-2 sm:col-span-1"><span class="{{ $label }}">Pengarang</span>
            <input name="pengarang" maxlength="150" value="{{ $k?->pengarang }}" class="{{ $input }}"></label>
        <div><span class="{{ $label }}">Jenis *</span>
            <div class="mt-1 flex gap-1.5">
                @foreach (\App\Models\Kitab::JENIS as $kode => $nama)
                <label class="{{ $pilih }}"><input type="radio" name="jenis" value="{{ $kode }}" class="sr-only" @checked(($k?->jenis ?? 'teori') === $kode)>{{ $nama }}</label>
                @endforeach
            </div></div>
        <label><span class="{{ $label }}">Frekuensi (x/minggu) *</span>
            <input type="number" name="frekuensi" min="1" max="30" required value="{{ $k?->frekuensi ?? 2 }}" class="{{ $input }}"></label>
    </div>

    <div><span class="{{ $label }}">Diajarkan di kelas</span>
        <div class="mt-1.5 flex flex-wrap gap-1.5">
            @forelse ($kelasList as $kelas)
            <label class="{{ $pilih }}"><input type="checkbox" name="kelas[]" value="{{ $kelas->id }}" class="sr-only" @checked(in_array($kelas->id, $kelasDipilih))>{{ $kelas->nama }}</label>
            @empty
            <p class="text-xs text-slate-400">Belum ada kelas di Master Data.</p>
            @endforelse
        </div></div>

    <div><span class="{{ $label }}">Bulan ajaran ke <span class="font-normal text-slate-400">(kosongkan = semua bulan)</span></span>
        <div class="mt-1.5 grid grid-cols-6 gap-1.5">
            @for ($n = 1; $n <= 12; $n++)
            <label class="{{ $pilih }} text-center"><input type="checkbox" name="bulan_ke[]" value="{{ $n }}" class="sr-only" @checked(in_array($n, $bulanDipilih))>{{ $n }}</label>
            @endfor
        </div></div>

    <label class="block"><span class="{{ $label }}">Deskripsi</span>
        <textarea name="deskripsi" rows="2" maxlength="1000" class="{{ $input }}">{{ $k?->deskripsi }}</textarea></label>

    <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-tutup class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
        <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Simpan</button>
    </div>
</form>
