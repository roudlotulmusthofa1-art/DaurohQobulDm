{{--
    Header halaman admin (kartu hijau tua + pola titik), sama dengan Data Santri / Data Asatid / Data Admin.
    Ubah tampilan semua header admin di file ini saja.

    Pemakaian:
      <x-header-admin :label="__('Akademik')" :judul="__('Jadwal Mengajar')" :keterangan="__('...')">
          ...isi tambahan di bawah keterangan (opsional)...
          <x-slot:aksi> ...tombol-tombol di kanan (opsional)... </x-slot:aksi>
      </x-header-admin>

    Gaya tombol yang serasi (pakai variabel ini di halaman):
      putih  : bg-white text-[#0b332c] hover:bg-emerald-100
      hijau  : bg-emerald-400 text-[#0b332c] hover:bg-emerald-300
      biru   : bg-sky-400 text-[#0b332c] hover:bg-sky-300
      kuning : bg-amber-300 text-[#3b2a00] hover:bg-amber-200
      bening : bg-white/10 ring-1 ring-white/20 hover:bg-white/20
--}}
@props(['label' => null, 'judul', 'keterangan' => null])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7']) }}>
    <div class="pointer-events-none absolute inset-0 opacity-60"
        style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>

    <div class="relative flex flex-col lg:flex-row lg:items-end gap-5">
        <div class="flex-1 min-w-0">
            @if ($label)
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ $label }}</p>
            @endif
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $judul }}</h1>
            @if ($keterangan)
            <p class="mt-1.5 text-sm text-white/70">{{ $keterangan }}</p>
            @endif
            {{ $slot }}
        </div>

        @isset($aksi)
        <div class="flex flex-wrap gap-2">
            {{ $aksi }}
        </div>
        @endisset
    </div>
</div>
