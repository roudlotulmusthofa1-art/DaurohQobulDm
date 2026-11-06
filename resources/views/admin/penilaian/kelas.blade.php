{{--
    Admin > Penilaian > Kelas & Pelajaran (PenilaianController@kelas).
    Tiga halaman dalam satu menu: Nilai Harian · Ujian Lisan · Ujian, pindah lewat tombol di atas (seperti Master Data).
    Alur penilaiannya menyusul; sementara tiap halaman berisi keterangan "alur menyusul".
    Vue: #halaman-penilaian-kelas (tab aktif + alamat ?tab=... ikut berubah).
--}}
@extends('layouts.admin')

@section('title', __('Kelas & Pelajaran'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$bagian = [
    'harian' => [
        'label' => __('Nilai Harian'),
        'ket'   => __('Nilai harian peserta per kelas & pelajaran yang diisi asatid setelah mengajar.'),
        'warna' => 'from-sky-400 to-blue-600',
        'aksen' => '#0284c7',
        'ikon'  => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
    ],
    'lisan' => [
        'label' => __('Ujian Lisan'),
        'ket'   => __('Nilai ujian lisan (setoran / musyafahah) peserta per pelajaran.'),
        'warna' => 'from-violet-400 to-purple-600',
        'aksen' => '#7c3aed',
        'ikon'  => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z',
    ],
    'ujian' => [
        'label' => __('Ujian'),
        'ket'   => __('Nilai ujian tulis peserta per kelas & pelajaran.'),
        'warna' => 'from-amber-400 to-orange-500',
        'aksen' => '#d97706',
        'ikon'  => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    ],
];
@endphp

<div id="halaman-penilaian-kelas" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="__('Kelas & Pelajaran')"
        :keterangan="__('Nilai harian, ujian lisan, dan ujian peserta yang diisi asatid.')" />

    {{-- ===== Tombol pindah halaman (seperti Master Data) ===== --}}
    <div class="-mx-1 overflow-x-auto px-1 pb-2">
        <div class="inline-flex gap-1.5 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5" role="tablist">
            @foreach ($bagian as $kunci => $b)
            <a href="?tab={{ $kunci }}" role="tab" style="--aksen: {{ $b['aksen'] }}"
                :aria-selected="tab === '{{ $kunci }}'"
                @click.prevent="pilih('{{ $kunci }}')"
                class="group flex shrink-0 items-center gap-2.5 whitespace-nowrap rounded-xl py-1.5 pl-1.5 pr-3 text-sm font-medium transition-all duration-200"
                :class="tab === '{{ $kunci }}'
                    ? 'bg-[linear-gradient(135deg,#0f4238,#0b332c)] text-white shadow-md shadow-[#0b332c]/25'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110"
                    :class="tab === '{{ $kunci }}'
                        ? 'bg-(--aksen) text-white shadow-[0_4px_12px_-2px_var(--aksen)]'
                        : 'bg-[color-mix(in_srgb,var(--aksen)_14%,white)] text-(--aksen)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $b['ikon'] }}"/></svg>
                </span>
                {{ $b['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- ===== Isi tiap halaman (alur menyusul) ===== --}}
    @foreach ($bagian as $kunci => $b)
    <section v-show="tab === '{{ $kunci }}'" @if ($tab !== $kunci) style="display: none" @endif
        class="relative overflow-hidden {{ $kartu }} px-6 py-12 sm:py-16 text-center">
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-64 rounded-full bg-gradient-to-br {{ $b['warna'] }} opacity-10 blur-3xl"></div>
        <div class="relative">
            <span class="mx-auto w-20 h-20 rounded-[26px] bg-gradient-to-br {{ $b['warna'] }} text-white flex items-center justify-center shadow-xl shadow-black/10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $b['ikon'] }}"/></svg>
            </span>
            <span class="mt-6 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 bg-slate-100 rounded-full px-3 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> {{ __('Alur menyusul') }}
            </span>
            <h2 class="mt-3 text-xl sm:text-2xl font-extrabold text-slate-800">{{ $b['label'] }}</h2>
            <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto leading-relaxed">{{ $b['ket'] }}</p>
            <p class="mt-1 text-sm text-slate-400 max-w-md mx-auto">{{ __('Halaman ini disiapkan; cara pengisian nilainya akan dibuat setelah alurnya ditentukan.') }}</p>
        </div>
    </section>
    @endforeach
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            data: () => ({ tab: @json($tab) }),
            methods: {
                // Pindah halaman tanpa memuat ulang; alamat ?tab=... ikut berubah (bisa dibagikan / dimuat ulang)
                pilih(kunci) {
                    this.tab = kunci;
                    const u = new URL(location.href);
                    u.searchParams.set('tab', kunci);
                    history.replaceState(null, '', u);
                },
            },
        }).mount('#halaman-penilaian-kelas');
    });
</script>
@endpush
