{{-- Form buat/ubah absensi kegiatan (di dalam <dialog>). Variabel: $aksi, $metode, $judul, $a (null = baru), $kegiatanList --}}
@php
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$baru = ! $a;
// Isian lama dipakai lagi hanya untuk form yang tadi dikirim
$nilai = fn ($kolom, $bawaan) => $baru && old('_form') === 'baru' ? old($kolom, $bawaan) : $bawaan;
$jam = fn ($v) => $v ? substr((string) $v, 0, 5) : null;
// Formulir baru: jam mulai = jam sekarang (WIB), terlambat +15 menit, alfa +45 menit (paling lambat 23:59)
$sekarang = now('Asia/Jakarta');
$bawaan = fn ($menit) => $sekarang->copy()->addMinutes($menit)->isSameDay($sekarang) ? $sekarang->copy()->addMinutes($menit)->format('H:i') : '23:59';
$pilih = 'cursor-pointer rounded-xl px-3 py-2.5 text-sm font-semibold ring-1 ring-slate-200 bg-white text-slate-600 text-center has-[:checked]:bg-emerald-600 has-[:checked]:text-white has-[:checked]:ring-emerald-600 transition-colors';
@endphp
<form method="POST" action="{{ $aksi }}" class="p-5 sm:p-6 space-y-4 max-h-[85vh] overflow-y-auto">
    @csrf
    @if ($metode !== 'POST') @method($metode) @endif
    <input type="hidden" name="_form" value="{{ $baru ? 'baru' : $a->id }}">

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-slate-800">{{ $judul }}</h2>
        <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
    </div>

    <label class="block"><span class="{{ $label }}">{{ __('Nama kegiatan *') }}</span>
        <select name="jenis_kegiatan_id" required class="{{ $input }}">
            <option value="">{{ __('Pilih kegiatan…') }}</option>
            @foreach ($kegiatanList as $k)
            <option value="{{ $k->id }}" @selected((string) $nilai('jenis_kegiatan_id', $a?->jenis_kegiatan_id) === (string) $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <span class="mt-1 block text-[11px] text-slate-400">{{ __('Daftar nama diatur di Master Data › Jenis Kegiatan.') }}</span>
    </label>

    <div class="grid grid-cols-2 gap-3">
        <label class="col-span-2"><span class="{{ $label }}">{{ __('Tanggal *') }}</span>
            <input type="date" name="tanggal" required value="{{ $nilai('tanggal', $a?->tanggal?->toDateString() ?? now('Asia/Jakarta')->toDateString()) }}" class="{{ $input }}"></label>
        <label><span class="{{ $label }}">{{ __('Jam mulai *') }}</span>
            <input type="time" name="jam_mulai" required value="{{ $nilai('jam_mulai', $jam($a?->jam_mulai) ?? $bawaan(0)) }}" @change="geserBatas" class="{{ $input }}"></label>
        <label><span class="{{ $label }}">{{ __('Batas terlambat *') }}</span>
            <input type="time" name="batas_terlambat" required value="{{ $nilai('batas_terlambat', $jam($a?->batas_terlambat) ?? $bawaan(15)) }}" class="{{ $input }}"></label>
        <label class="col-span-2"><span class="{{ $label }}">{{ __('Batas alfa *') }}</span>
            <input type="time" name="batas_alfa" required value="{{ $nilai('batas_alfa', $jam($a?->batas_alfa) ?? $bawaan(45)) }}" class="{{ $input }}">
            <span class="mt-1 block text-[11px] text-slate-400">{{ __('Sebelum batas terlambat = Hadir · sampai batas alfa = Terlambat · setelahnya = Alfa.') }}</span>
            <span class="mt-0.5 block text-[11px] text-slate-400">{{ __('Saat jam mulai diganti, batas terlambat (+15 menit) dan batas alfa (+45 menit) ikut menyesuaikan. Absensi ditutup otomatis saat batas alfa lewat.') }}</span></label>
    </div>

    <div><span class="{{ $label }}">{{ __('Cara absen *') }}</span>
        <div class="mt-1.5 grid grid-cols-2 gap-2">
            @foreach (['qr' => __('Pindai kartu QR'), 'manual' => __('Manual (cari nama)')] as $kode => $nama)
            <label class="{{ $pilih }}">
                <input type="radio" name="metode" value="{{ $kode }}" class="sr-only" @checked($nilai('metode', $a?->metode ?? 'qr') === $kode)>
                {{ $nama }}
            </label>
            @endforeach
        </div>
    </div>

    @if ($baru)
    <label class="flex items-start gap-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 px-3.5 py-3 cursor-pointer">
        <input type="checkbox" name="aktif" value="1" @checked($nilai('aktif', true)) class="mt-0.5 w-5 h-5 accent-emerald-600">
        <span class="text-sm">
            <span class="block font-semibold text-emerald-800">{{ __('Langsung aktifkan') }}</span>
            <span class="block text-xs text-emerald-700/80">{{ __('Tampil di dasbor semua asatid, sehingga bisa langsung diabsen bersama.') }}</span>
        </span>
    </label>
    @endif

    <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
        <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">{{ __('Simpan') }}</button>
    </div>
</form>
