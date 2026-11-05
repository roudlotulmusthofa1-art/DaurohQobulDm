{{-- Detail pendaftar. Konten saja; layout admin memuat @stack('scripts'). --}}
@extends('layouts.admin')

@section('content')
@php
// Ikon kecil untuk tombol kembali, pesan, dan pop-up tolak di halaman ini
// (ikon bagian data ada di partials/detail-santri.blade.php)
$ikonHalaman = [
    'back'  => 'M15.75 19.5L8.25 12l7.5-7.5',
    'check' => 'M4.5 12.75l6 6 9-13.5',
    'x'     => 'M6 18L18 6M6 6l12 12',
];
$svg = fn ($nama, $kelas = 'w-5 h-5') => '<svg class="' . $kelas . '" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . $ikonHalaman[$nama] . '"/></svg>';
@endphp
{{-- Tombol kembali berada di luar #psb-detail -> aplikasi Vue kecil sendiri (#kembali-detail) --}}
<div id="kembali-detail">
<a href="{{ route('admin.pendaftaran.index') }}" @click="kembali"
    class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0b332c] transition-colors mt-4">
    <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center group-hover:-translate-x-0.5 transition-transform">
        {!! $svg('back', 'w-4 h-4') !!}
    </span>
    {{ __('Kembali') }}
</a>
</div>
<div class="space-y-6 pb-4 mx-4 md:mx-10 lg:mx-20 mt-3" id="psb-detail">

    {{-- Data santri: tampilan yang sama dipakai di profil siswa (partials/detail-santri) --}}
    @include('partials.detail-santri', ['admin' => true, 'sisipan' => 'admin.pendaftaran._persyaratan'])

    {{-- Modal tolak (Vue) --}}
    <div v-if="tolakBuka" v-cloak
        class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <form method="POST" action="{{ route('admin.pendaftaran.tolak', $p) }}"
            class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-md space-y-4">
            @csrf
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-rose-100 flex items-center justify-center">{!! $svg('x') !!}</span>
                <div>
                    <h3 class="font-bold text-slate-800">{{ __('Tolak pendaftar') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Alasan akan dikirim ke WhatsApp peserta') }}</p>
                </div>
            </div>
            <textarea name="catatan_admin" v-model="alasan" rows="4" required
                placeholder="{{ __('Tulis alasan penolakan...') }}"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-sm focus:bg-white focus:outline-none focus:border-rose-300 focus:ring-2 focus:ring-rose-400/20"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" @click="tolakBuka = false"
                    class="px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">{{ __('Batal') }}</button>
                <button :disabled="!alasan.trim()"
                    class="px-4 py-2.5 rounded-2xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 disabled:opacity-50">{{ __('Tolak & Kirim Chat') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman detail pendaftar (Vue dari resources/js/app.js).
    // Data pendaftar di halaman ini ditampilkan Blade dan sebagian diberi v-pre.
    document.addEventListener('DOMContentLoaded', function () {
        const KASUR = @json($kasurPerKamar); // { idKamar: { kapasitas, terisi: { nomor: 'nama penghuni' } } }

        Vue.createApp({
            data() {
                return {
                    tolakBuka: false, // pop-up alasan penolakan
                    alatBuka: @json(session()->has('password_baru') || $errors->any()), // ⋮ alat lainnya; terbuka sendiri setelah reset password
                    alasan: '',
                    kamarId: @json((string) old('kamar_id', $p->kamar_id ?? '')),
                    nomorKasur: @json((string) old('nomor_kasur', $p->nomor_kasur ?? '')),
                };
            },
            computed: {
                kamarTerpilih() {
                    return KASUR[this.kamarId] ?? null;
                },
                // Kasur 1..kapasitas kamar yang dipilih, beserta penghuninya (bila ada)
                pilihanKasur() {
                    const k = this.kamarTerpilih;
                    if (!k || k.kapasitas < 1) return [];
                    return Array.from({ length: k.kapasitas }, (_, i) => ({ nomor: i + 1, penghuni: k.terisi[i + 1] ?? null }));
                },
                keteranganKasur() {
                    if (!this.kamarTerpilih) return @js(__('Pilih kamar dulu.'));
                    if (this.kamarTerpilih.kapasitas < 1) return @js(__('Kapasitas kamar ini belum diatur di Master Data › Kamar.'));
                    const kosong = this.pilihanKasur.filter((k) => !k.penghuni).length;
                    return @js(__(':kosong dari :kapasitas kasur masih kosong.')).replace(':kosong', kosong).replace(':kapasitas', this.kamarTerpilih.kapasitas);
                },
            },
            watch: {
                // Ganti kamar -> nomor kasur dipilih ulang
                kamarId() {
                    this.nomorKasur = '';
                },
            },
            created() {
                // Nomor kasur lama tidak berlaku bila kasurnya sudah ditempati santri lain
                const k = this.pilihanKasur.find((x) => String(x.nomor) === this.nomorKasur);
                if (!k || k.penghuni) this.nomorKasur = '';
            },
            methods: { ...metodeHalaman }, // konfirmasi tombol Terima
        }).mount('#psb-detail');
        // Tombol kembali di atas: kembali ke halaman sebelumnya
        Vue.createApp({ methods: { ...metodeHalaman } }).mount('#kembali-detail');
    });
</script>
@endpush
