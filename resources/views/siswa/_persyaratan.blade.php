{{-- Profil siswa: daftar persyaratan (hanya dilihat). Disisipkan lewat partials/detail-santri; centangnya diisi admin. --}}
<section class="{{ $kartu }} p-5 sm:p-6 xl:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        {!! $judulKartu(__('Persyaratan'), 'badge', 'emerald', __('Diperiksa oleh admin. Hubungi admin bila ada yang belum terpenuhi.')) !!}
        <span @class(['text-xs font-semibold px-3 py-1 rounded-full ring-1',
            'bg-emerald-50 text-emerald-700 ring-emerald-100' => $p->syarat_terpenuhi === $totalSyarat,
            'bg-amber-50 text-amber-700 ring-amber-100' => $p->syarat_terpenuhi !== $totalSyarat])>
            {{ $p->syarat_terpenuhi }}/{{ $totalSyarat }} {{ __('terpenuhi') }}
        </span>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($p::SYARAT as $kolom => $nama)
        <div @class(['flex items-center gap-3 rounded-2xl p-3.5 ring-1',
            'bg-emerald-50 ring-emerald-200' => $p->$kolom,
            'bg-[#f5f9f7] ring-slate-100' => ! $p->$kolom])>
            @if ($p->$kolom)
            <span class="w-6 h-6 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center">{!! $svg('check', 'w-3.5 h-3.5') !!}</span>
            @else
            <span class="w-6 h-6 shrink-0 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center">{!! $svg('clock', 'w-3.5 h-3.5') !!}</span>
            @endif
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-slate-700">{{ __($nama) }}</span>
                <span @class(['block text-xs', 'text-emerald-700' => $p->$kolom, 'text-slate-400' => ! $p->$kolom])>{{ $p->$kolom ? __('Sudah terpenuhi') : __('Belum terpenuhi') }}</span>
            </span>
        </div>
        @endforeach
    </div>
</section>
