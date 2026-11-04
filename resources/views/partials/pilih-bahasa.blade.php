{{--
    Tombol ganti bahasa tampilan (Indonesia <-> العربية). Bawaan: Indonesia.
    Pilihan disimpan di cookie "bahasa" (route 'bahasa'), lihat App\Http\Middleware\PilihBahasa.
--}}
@php $bahasaLain = app()->getLocale() === 'ar' ? 'id' : 'ar'; @endphp
<a href="{{ route('bahasa', $bahasaLain) }}" title="{{ $bahasaLain === 'ar' ? __('Tampilkan dalam bahasa Arab') : __('Tampilkan dalam bahasa Indonesia') }}"
    class="inline-flex items-center gap-1.5 h-10 shrink-0 rounded-xl {{ $kelas ?? 'bg-white' }} px-3 text-[13px] font-bold text-[#0b332c] ring-1 ring-slate-200/70 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802"/></svg>
    @if (! empty($ringkas))
    <span>{{ $bahasaLain === 'ar' ? 'ع' : 'ID' }}</span>
    @else
    <span>{{ $bahasaLain === 'ar' ? 'العربية' : 'Indonesia' }}</span>
    @endif
</a>
