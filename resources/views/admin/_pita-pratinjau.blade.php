{{--
    Pratinjau Pita Pengumuman di halaman admin (template Vue, di dalam #halaman-pita).
    Meniru landing page kecil: top bar + latar hero, pita di atas / di bawah top bar, penuh / ringkas
    sesuai `tampilan` (posisi & ukuran). Butuh `pita` dari aplikasi Vue pemakainya.
--}}
<div class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-linear-to-b from-teal-900 to-teal-800">
    {{-- Pita di ATAS top bar --}}
    <template v-if="tampilan.posisi === 'atas'">
        <div v-if="tampilan.ukuran === 'ringkas'" class="py-2">@include('partials.pita-pengumuman-ringkas')</div>
        <div v-else>@include('partials.pita-pengumuman-tampilan')</div>
    </template>

    {{-- Top bar tiruan --}}
    <div class="h-10 bg-white/95 border-b border-slate-100 flex items-center px-4 gap-3">
        <span class="w-14 h-3 rounded bg-slate-200"></span><span class="ml-auto w-10 h-2 rounded bg-slate-100"></span><span class="w-10 h-2 rounded bg-slate-100"></span><span class="w-10 h-2 rounded bg-slate-100"></span>
    </div>

    {{-- Pita di BAWAH top bar --}}
    <template v-if="tampilan.posisi === 'bawah'">
        <div v-if="tampilan.ukuran === 'ringkas'" class="py-2">@include('partials.pita-pengumuman-ringkas')</div>
        <div v-else>@include('partials.pita-pengumuman-tampilan')</div>
    </template>

    {{-- Latar hero tiruan --}}
    <div class="h-14 flex flex-col items-center justify-center gap-1.5">
        <span class="w-32 h-2.5 rounded bg-white/40"></span>
        <span class="w-20 h-2 rounded bg-amber-300/50"></span>
    </div>
</div>
