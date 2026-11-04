{{--
    Pita di atas dasbor asatid/siswa saat Super Admin sedang "Masuk sebagai" (lihat App\Support\MasukSebagai).
    Dipasang di layouts/asatid & layouts/siswa, tepat setelah <body>. Tidak tampil bila mode ini tidak aktif.
--}}
@if (\App\Support\MasukSebagai::aktif())
@php $bolehUbah = \App\Support\MasukSebagai::bolehUbah(); @endphp
{{-- Kuning = mode lihat, oranye-merah = perubahan diizinkan --}}
<div class="relative z-[60] shadow-md {{ $bolehUbah ? 'bg-orange-500 text-white' : 'bg-amber-400 text-[#3b2a00]' }}">
    <div class="max-w-6xl mx-auto px-4 py-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <p class="flex-1 min-w-0">
            {{ __('Anda melihat sebagai') }} <b>{{ auth()->user()->name }}</b>
            ({{ auth()->user()->role === 'asatid' ? __('asatid') : __('peserta') }}) ·
            @if ($bolehUbah)
            <b>{{ __('perubahan DIIZINKAN') }}</b> <span class="opacity-90">— {{ __('absensi dll. yang Anda simpan tercatat atas nama akun ini') }}</span>
            @else
            <span class="opacity-80">{{ __('mode lihat, perubahan tidak disimpan') }}</span>
            @endif
        </p>
        <form method="POST" action="{{ route('masuk-sebagai.mode') }}"
            onsubmit="return {{ $bolehUbah ? 'true' : 'confirm(' . \Illuminate\Support\Js::from(__('Izinkan perubahan? Absensi dan data lain yang Anda simpan akan tercatat atas nama akun ini.')) . ')' }}">
            @csrf
            <input type="hidden" name="ubah" value="{{ $bolehUbah ? 0 : 1 }}">
            <button class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold {{ $bolehUbah ? 'bg-white/20 hover:bg-white/30' : 'bg-white/60 hover:bg-white' }}">
                {{ $bolehUbah ? __('Kembali ke mode lihat') : __('Izinkan perubahan') }}
            </button>
        </form>
        <form method="POST" action="{{ route('masuk-sebagai.selesai') }}">
            @csrf
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-[#0b332c] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#135347]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                {{ __('Kembali ke Admin') }}
            </button>
        </form>
    </div>
</div>
@endif
