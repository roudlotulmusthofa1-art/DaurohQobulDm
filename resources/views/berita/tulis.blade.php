{{--
    Menu "Tulis Berita" untuk asatid & siswa (layout dari controller: layouts.asatid / layouts.siswa).
    Tulisan masuk ke Admin > Landing Page > Berita > Kotak Masuk; tampil setelah admin menekan Tampilkan.
    Variabel: $layout, $rute (asatid.berita | siswa.berita), $daftar, $kategori, $maksFoto
--}}
@extends($layout)

@section('judul', __('Tulis Berita'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div id="halaman-berita-saya" class="space-y-5" v-cloak>
    {{-- Penjelasan + tombol tulis --}}
    <div class="{{ $kartu }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
            <h2 class="font-bold text-slate-800">{{ __('Kirim berita untuk landing page') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Tulis berita kegiatan, lalu kirim. Admin akan memeriksa dulu; bila pantas, berita tampil di bagian Berita Terkini di halaman depan.') }}</p>
        </div>
        <button v-if="!form" type="button" @click="bukaForm()"
            class="shrink-0 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0b332c] px-5 py-3 text-sm font-bold text-white hover:bg-[#135347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Tulis berita baru') }}
        </button>
    </div>

    {{-- Formulir --}}
    <form v-if="form" @submit.prevent="simpan" class="{{ $kartu }} p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="font-bold text-slate-800" v-text="form.id ? @js(__('Ubah berita')) : @js(__('Berita baru'))"></h2>
            <button type="button" @click="form = null" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Batal') }}</button>
        </div>
        <p v-if="form.status === 'ditolak'" class="mb-4 rounded-xl bg-amber-50 ring-1 ring-amber-200 px-3 py-2 text-xs text-amber-800">{{ __('Setelah diperbaiki, berita dikirim ulang ke admin untuk diperiksa.') }}</p>
        @include('berita.partials.form-tulis', ['admin' => false])
        <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" @click="form = null" class="px-5 py-3 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button type="submit" :disabled="sibuk || memproses"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                <span v-text="sibuk ? @js(__('Mengirim…')) : @js(__('Kirim ke admin'))"></span>
            </button>
        </div>
    </form>

    {{-- Berita saya --}}
    <section>
        <h2 class="font-bold text-slate-800 mb-3">{{ __('Berita Saya') }} <span class="text-slate-400 font-semibold" v-text="'(' + daftar.length + ')'"></span></h2>
        <p v-if="!daftar.length" class="{{ $kartu }} p-8 text-center text-sm text-slate-400">{{ __('Belum ada berita yang Anda tulis.') }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <article v-for="b in daftar" :key="b.id" class="{{ $kartu }} overflow-hidden flex flex-col">
                <div class="flex gap-3 p-4">
                    <div class="w-24 h-20 shrink-0 rounded-2xl overflow-hidden bg-slate-100">
                        <img v-if="b.foto.length" :src="b.foto[0].url" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-full ring-1 text-[11px] font-bold" :class="infoStatus(b.status).kelas" v-text="infoStatus(b.status).teks"></span>
                        <h3 class="mt-1 font-bold text-slate-800 leading-snug line-clamp-2" v-text="b.judul"></h3>
                        <p class="mt-0.5 text-xs text-slate-400" v-text="@js(__('Dikirim')) + ' ' + b.dibuat"></p>
                    </div>
                </div>
                <div class="px-4 pb-3 space-y-2 text-xs">
                    <p v-if="b.status === 'tampil'" class="text-emerald-700" v-text="b.tampil_sampai ? @js(__('Tampil sampai')) + ' ' + b.tampil_sampai + ' WIB' : @js(__('Tampil tanpa batas waktu'))"></p>
                    <p v-if="b.status === 'selesai'" class="text-slate-500" v-text="@js(__('Selesai tayang')) + ' ' + b.tampil_sampai + ' WIB'"></p>
                    <div v-if="b.status === 'ditolak'" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-rose-700">
                        <b>{{ __('Catatan admin:') }}</b> <span v-text="b.catatan_admin || @js(__('(tanpa catatan)'))"></span>
                    </div>
                    <p v-if="b.status === 'tampil' || b.status === 'selesai'" class="text-slate-500"
                        v-text="b.jumlah_dilihat + ' ' + @js(__('dilihat')) + ' · ' + b.jumlah_suka + ' ' + @js(__('suka')) + ' · ' + b.jumlah_komentar + ' ' + @js(__('komentar'))"></p>
                </div>
                <div class="mt-auto border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100 text-xs font-bold">
                    <a :href="b.url" target="_blank" rel="noopener" class="py-3 text-center text-slate-600 hover:bg-slate-50">{{ __('Lihat') }}</a>
                    <button type="button" @click="bukaForm(b)" :disabled="b.status === 'tampil' || b.status === 'selesai'"
                        class="py-3 text-emerald-700 hover:bg-emerald-50 disabled:text-slate-300 disabled:hover:bg-transparent">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(b)" class="py-3 text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                </div>
            </article>
        </div>
    </section>
</div>
@endsection

@push('scripts')
@include('berita.partials.skrip-tulis')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.TOKEN_BERITA = @json(csrf_token());
        const URL_DASAR = @json(route($rute));

        Vue.createApp({
            mixins: [window.MIXIN_TULIS_BERITA],
            data: () => ({ daftar: @json($daftar), kategori: @json($kategori), maksFoto: @json($maksFoto) }),
            methods: {
                ...metodeHalaman,
                async simpan() {
                    const ok = await this.kirimBerita(this.form.id ? URL_DASAR + '/' + this.form.id : URL_DASAR);
                    if (ok) window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                hapus(b) {
                    if (!confirm(@js(__('Hapus berita ":judul"?')).replace(':judul', b.judul))) return;
                    this.aksi('DELETE', URL_DASAR + '/' + b.id);
                },
            },
        }).mount('#halaman-berita-saya');
    });
</script>
@endpush
