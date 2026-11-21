{{--
    Kotak ringkas Kalender Pendidikan di Dasbor admin (laptop & HP).
    Data: $kalender = KalenderPendidikan::ringkasDasbor() -> ['berjalan' => [...], 'akanDatang' => [...]].
    Variabel: $kelasKartu (gaya kartu), $ringkas (true = tampilan HP).
    Isinya Blade biasa tanpa perintah Vue; v-pre supaya judul kegiatan yang berisi "{{" tidak dibaca Vue.
--}}
@php
$ringkas ??= false;
$bolehBuka = \App\Support\MenuAdmin::boleh(auth()->user(), 'admin.kalender.index');
$urlKalender = $bolehBuka && \Illuminate\Support\Facades\Route::has('admin.kalender.index') ? route('admin.kalender.index') : null;
$teksKemajuan = function (array $m) {
    if ($m['status'] === 'belum') {
        return $m['lagi'] === 1 ? __('Dimulai besok') : __('Dimulai :lagi hari lagi', ['lagi' => $m['lagi']]);
    }
    if ($m['sisa'] === 0) {
        return __('Hari terakhir') . ' (' . $m['hari_ke'] . '/' . $m['lama'] . ')';
    }

    return __('Hari ke-:ke dari :lama · sisa :sisa hari', ['ke' => $m['hari_ke'], 'lama' => $m['lama'], 'sisa' => $m['sisa']]);
};
$rentang = fn (array $k, string $a, string $b) => $k['mulai'] === $k['selesai'] ? $k[$a] : $k[$a] . ' – ' . $k[$b];
@endphp

<section v-pre class="{{ $kelasKartu }} {{ $ringkas ? 'p-4' : 'p-5 sm:p-6' }}">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-9 h-9 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </span>
            <h2 class="{{ $ringkas ? 'font-extrabold' : 'font-bold' }} text-slate-800">{{ __('Kalender Pendidikan') }}</h2>
        </div>
        @if ($urlKalender)
        <a href="{{ $urlKalender }}" class="shrink-0 text-xs font-semibold text-emerald-700 hover:underline">{{ __('Lihat kalender') }}</a>
        @endif
    </div>

    @if (! $kalender['berjalan'] && ! $kalender['akanDatang'])
    <p class="mt-4 rounded-2xl bg-[#f8fbfa] px-4 py-5 text-center text-sm text-slate-400">{{ __('Belum ada kegiatan yang berjalan atau akan datang dalam 30 hari.') }}</p>
    @else
    <div class="mt-4 grid gap-5">
        {{-- Sedang berjalan --}}
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">{{ __('Sedang berjalan') }}</p>
            @forelse ($kalender['berjalan'] as $k)
            <div class="mt-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3.5 py-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $k['kelas']['titik'] }}"></span>
                    <p class="font-bold text-sm text-slate-800 truncate">{{ $k['judul'] }}</p>
                    <span class="ml-auto shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $k['kelas']['pita'] }}">{{ $k['label_jenis'] }}</span>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">{{ $rentang($k, 'mulai_teks', 'selesai_teks') }}</p>
                <p class="text-[11px] text-amber-700">{{ $rentang($k, 'mulai_hijriyah', 'selesai_hijriyah') }}</p>
                <div class="mt-2 flex items-center justify-between text-[11px] font-semibold">
                    <span class="text-emerald-700">{{ $teksKemajuan($k['kemajuan']) }}</span>
                    <span class="text-slate-400 tabular-nums">{{ $k['kemajuan']['persen'] }}%</span>
                </div>
                <div class="mt-1 h-1.5 rounded-full bg-slate-200/70 overflow-hidden"><div class="h-full rounded-full {{ $k['kelas']['garis'] }}" style="width: {{ $k['kemajuan']['persen'] }}%"></div></div>
            </div>
            @empty
            <p class="mt-2 text-sm text-slate-400">{{ __('Tidak ada kegiatan hari ini.') }}</p>
            @endforelse
        </div>

        {{-- Akan datang --}}
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-sky-700">{{ __('Akan datang') }}</p>
            @forelse ($kalender['akanDatang'] as $k)
            <div class="mt-2 flex items-start gap-3 rounded-2xl px-1 py-1.5">
                <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-full {{ $k['kelas']['titik'] }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-sm text-slate-800 truncate">{{ $k['judul'] }}</p>
                    <p class="text-[11px] text-slate-500">{{ $rentang($k, 'mulai_teks', 'selesai_teks') }} · {{ $k['kemajuan']['lama'] }} {{ __('hari') }}</p>
                    <p class="text-[11px] text-amber-700">{{ $rentang($k, 'mulai_hijriyah', 'selesai_hijriyah') }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700">{{ $teksKemajuan($k['kemajuan']) }}</span>
            </div>
            @empty
            <p class="mt-2 text-sm text-slate-400">{{ __('Belum ada kegiatan dalam 30 hari ke depan.') }}</p>
            @endforelse
        </div>
    </div>
    @endif
</section>
