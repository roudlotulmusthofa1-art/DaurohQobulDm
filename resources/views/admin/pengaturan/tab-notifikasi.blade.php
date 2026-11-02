{{--
Pengaturan > Notifikasi: Saklar jenis notifikasi lonceng admin.
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
<h2 class="text-lg font-bold text-slate-800">Notifikasi</h2>
<p class="text-sm text-slate-500">Pilih jenis notifikasi yang muncul di ikon lonceng (kanan atas).</p>

<form method="POST" action="{{ route('admin.pengaturan.update') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 overflow-hidden">
    @csrf @method('PUT')
    <div class="divide-y divide-slate-100">
        @foreach ($notifikasi as $n)
        <label class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-[#f5f9f7] transition-colors">
            <span class="w-10 h-10 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna_kelas'] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon_path'] }}"/></svg>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-sm font-semibold text-slate-800">{{ $n['judul'] }}</span>
                <span class="block text-xs text-slate-500 mt-0.5">{{ $n['keterangan'] }}</span>
            </span>
            {{-- Saklar hidup/mati --}}
            <span class="relative shrink-0">
                <input type="checkbox" name="notif[{{ $n['kunci'] }}]" value="1" @checked($n['aktif']) class="peer sr-only">
                <span class="block w-11 h-6 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20"></span>
                <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
            </span>
        </label>
        @endforeach
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-[#f5f9f7] border-t border-slate-100">
        <p class="text-xs text-slate-500">Notifikasi hilang sendiri begitu masalahnya sudah dibereskan.</p>
        <button type="submit" class="{{ $tombol }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Simpan
        </button>
    </div>
</form>
