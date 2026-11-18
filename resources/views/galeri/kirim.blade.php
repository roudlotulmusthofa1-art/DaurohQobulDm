{{--
    Menu "Kirim Foto" untuk asatid & siswa (layout dari controller: layouts.asatid / layouts.siswa).
    Satu kiriman = satu album (beberapa foto + keterangan + tanggal kegiatan).
    Album masuk ke Admin > Landing Page > Galeri Foto > Kotak Masuk; tampil di /galeri setelah admin menekan Tampilkan.
    Variabel: $layout, $rute (asatid.foto | siswa.foto), $daftar, $maksFoto
    Foto diperkecil di browser lewat window.perkecilFoto (berita/partials/skrip-tulis).
--}}
@extends($layout)

@section('judul', __('Kirim Foto'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-base sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
$label = 'block text-xs font-semibold text-slate-600 mb-1.5';
@endphp

<div id="halaman-foto-saya" class="space-y-5" v-cloak>
    {{-- Penjelasan + tombol kirim --}}
    <div class="{{ $kartu }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
            <h2 class="font-bold text-slate-800">{{ __('Kirim foto untuk Galeri Foto') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Pilih foto kegiatan (boleh beberapa sekaligus), beri keterangan, lalu kirim. Admin akan memeriksa dulu; bila pantas, foto tampil di Galeri Foto dan bisa disukai, dikomentari, dan dibagikan.') }}</p>
        </div>
        <button v-if="!form" type="button" @click="bukaForm()"
            class="shrink-0 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0b332c] px-5 py-3 text-sm font-bold text-white hover:bg-[#135347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Kirim foto baru') }}
        </button>
    </div>

    {{-- Formulir --}}
    <form v-if="form" @submit.prevent="simpan" class="{{ $kartu }} p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-bold text-slate-800" v-text="form.id ? @js(__('Ubah album')) : @js(__('Album baru'))"></h2>
            <button type="button" @click="tutupForm" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Batal') }}</button>
        </div>
        <p v-if="form.status === 'ditolak'" class="rounded-xl bg-amber-50 ring-1 ring-amber-200 px-3 py-2 text-xs text-amber-800">{{ __('Setelah diperbaiki, foto dikirim ulang ke admin untuk diperiksa.') }}</p>

        <div>
            <span class="{{ $label }}" v-text="@js(__('Foto (boleh banyak sekaligus, maksimal :n)')).replace(':n', maksFoto)"></span>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                <div v-for="f in fotoTampil" :key="f.kunci" class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100">
                    <img :src="f.url" alt="" class="w-full h-full object-cover">
                    <span v-if="f.ukuran" class="absolute left-1 bottom-1 px-1.5 py-0.5 rounded bg-black/55 text-white text-[10px]" v-text="f.ukuran"></span>
                    <button type="button" @click="buangFoto(f)" aria-label="{{ __('Buang foto') }}" class="absolute right-1 top-1 w-6 h-6 rounded-full bg-black/55 text-white leading-none hover:bg-rose-600">&times;</button>
                </div>
                <label v-if="fotoTampil.length < maksFoto"
                    class="aspect-square rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center gap-1 text-slate-400 hover:border-emerald-500 hover:text-emerald-600 cursor-pointer">
                    <span class="text-2xl leading-none">+</span>
                    <span class="text-[11px] font-semibold text-center px-1" v-text="memproses ? @js(__('Menyiapkan…')) : @js(__('Pilih foto'))"></span>
                    <input type="file" accept="image/*" multiple class="hidden" @change="pilihFoto" :disabled="memproses">
                </label>
            </div>
            <p class="mt-1.5 text-[11px] text-slate-400">{{ __('Besar foto tidak dibatasi: foto diperkecil otomatis sebelum dikirim.') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="{{ $label }}">{{ __('Keterangan') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
                <textarea v-model="form.keterangan" rows="2" maxlength="500" class="{{ $isian }}" placeholder="{{ __('cth. Ziarah bersama santri ke makam Zanbal') }}"></textarea>
            </div>
            <div>
                <label class="{{ $label }}">{{ __('Tanggal kegiatan') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
                <input v-model="form.tanggal" type="date" class="{{ $isian }}">
            </div>
        </div>

        <p v-if="galat" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-sm text-rose-700" v-text="galat"></p>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" @click="tutupForm" class="px-5 py-3 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button type="submit" :disabled="sibuk || memproses || !fotoTampil.length"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                <span v-text="sibuk ? @js(__('Mengirim…')) : @js(__('Kirim ke admin'))"></span>
            </button>
        </div>
    </form>

    {{-- Foto saya --}}
    <section>
        <h2 class="font-bold text-slate-800 mb-3">{{ __('Foto Saya') }} <span class="text-slate-400 font-semibold" v-text="'(' + daftar.length + ')'"></span></h2>
        <p v-if="!daftar.length" class="{{ $kartu }} p-8 text-center text-sm text-slate-400">{{ __('Belum ada foto yang Anda kirim.') }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <article v-for="a in daftar" :key="a.id" class="{{ $kartu }} overflow-hidden flex flex-col">
                <div class="flex gap-3 p-4">
                    <div class="relative w-24 h-20 shrink-0 rounded-2xl overflow-hidden bg-slate-100">
                        <img v-if="a.foto.length" :src="a.foto[0].url" alt="" class="w-full h-full object-cover">
                        <span v-if="a.foto.length > 1" class="absolute right-1 bottom-1 px-1.5 py-0.5 rounded bg-black/55 text-white text-[10px] font-bold" v-text="a.foto.length + ' ' + @js(__('foto'))"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-full ring-1 text-[11px] font-bold" :class="infoStatus(a.status).kelas" v-text="infoStatus(a.status).teks"></span>
                        <p class="mt-1 font-semibold text-slate-800 leading-snug line-clamp-2" :class="a.keterangan ? '' : 'text-slate-400 font-normal'" v-text="a.keterangan || @js(__('(tanpa keterangan)'))"></p>
                        <p class="mt-0.5 text-xs text-slate-400" v-text="@js(__('Dikirim')) + ' ' + a.dibuat"></p>
                    </div>
                </div>
                <div class="px-4 pb-3 space-y-2 text-xs">
                    <div v-if="a.status === 'ditolak'" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-rose-700">
                        <b>{{ __('Catatan admin:') }}</b> <span v-text="a.catatan_admin || @js(__('(tanpa catatan)'))"></span>
                    </div>
                    <p v-if="a.status === 'tampil'" class="text-slate-500"
                        v-text="a.jumlah_dilihat + ' ' + @js(__('dilihat')) + ' · ' + a.jumlah_suka + ' ' + @js(__('suka')) + ' · ' + a.jumlah_komentar + ' ' + @js(__('komentar'))"></p>
                </div>
                <div class="mt-auto border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100 text-xs font-bold">
                    <a :href="a.url" target="_blank" rel="noopener" class="py-3 text-center text-slate-600 hover:bg-slate-50">{{ __('Lihat') }}</a>
                    <button type="button" @click="bukaForm(a)" :disabled="a.status === 'tampil'"
                        class="py-3 text-emerald-700 hover:bg-emerald-50 disabled:text-slate-300 disabled:hover:bg-transparent">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(a)" :disabled="sibuk" class="py-3 text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                </div>
            </article>
        </div>
    </section>
</div>
@endsection

@push('scripts')
@include('berita.partials.skrip-tulis')
@include('galeri.partials.skrip-album')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.TOKEN_BERITA = @json(csrf_token());
        const URL_DASAR = @json(route($rute));

        Vue.createApp({
            mixins: [window.MIXIN_ALBUM_FOTO],
            data: () => ({ daftar: @json($daftar), maksFoto: @json($maksFoto) }),
            methods: {
                ...metodeHalaman,
                async simpan() {
                    const ok = await this.kirimAlbum(this.form.id ? URL_DASAR + '/' + this.form.id : URL_DASAR);
                    if (ok) window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                hapus(a) {
                    if (!confirm(@js(__('Hapus album ini beserta semua fotonya?')))) return;
                    this.aksi('DELETE', URL_DASAR + '/' + a.id);
                },
            },
        }).mount('#halaman-foto-saya');
    });
</script>
@endpush
