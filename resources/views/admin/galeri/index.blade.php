{{--
    Admin > Landing Page > Galeri Foto. Semua foto berbentuk album (satu album = beberapa foto + keterangan).
    - Tab Kotak Masuk: kiriman asatid & siswa (menu Kirim Foto) yang menunggu -> Tampilkan / Tolak (+ catatan)
    - Tab Album: album admin & kiriman yang sudah diperiksa; Unggah Foto (langsung tampil), ubah, hapus
    - Tab Dari Berita: foto berita yang sudah ditampilkan otomatis masuk galeri; Sembunyikan / Tampilkan lagi
    Suka & komentar ada di halaman album (/galeri/{id}); admin yang login bisa menghapus komentar di sana.
    Vue #halaman-galeri + mixin galeri/partials/skrip-album; JSON {pesan, kotakMasuk, album, fotoBerita}.
--}}
@extends('layouts.admin')

@section('title', __('Galeri Foto'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
$label = 'block text-xs font-semibold text-slate-600 mb-1.5';
@endphp

<div id="halaman-galeri" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10" v-cloak>

    <x-header-admin :label="__('Landing Page')" :judul="__('Galeri Foto')"
        :keterangan="__('Foto untuk halaman Galeri Foto: unggahan admin, kiriman asatid & siswa yang sudah disetujui, dan foto berita yang sudah ditampilkan.')">
        <x-slot:aksi>
        <a href="{{ route('galeri.index') }}" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            {{ __('Lihat galeri') }}
        </a>
        <button type="button" @click="bukaUnggah"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Unggah Foto') }}
        </button>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Tab ===== --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        <button v-for="t in tabDaftar" :key="t.kunci" type="button" @click="tab = t.kunci"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold ring-1 transition-colors"
            :class="tab === t.kunci ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            <span v-text="t.nama"></span>
            <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px]"
                :class="tab === t.kunci ? 'bg-white/20' : (t.kunci === 'masuk' && t.jumlah ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500')" v-text="t.jumlah"></span>
        </button>
    </div>

    {{-- ===== Kotak Masuk: kiriman asatid & siswa ===== --}}
    <template v-if="tab === 'masuk'">
        <p class="text-sm text-slate-500">{{ __('Foto kiriman asatid & siswa yang menunggu diperiksa. Tekan Tampilkan agar masuk Galeri Foto, atau Tolak dengan catatan untuk pengirim.') }}</p>
        <p v-if="!kotakMasuk.length" class="{{ $kartu }} p-10 text-center text-sm text-slate-400">{{ __('Tidak ada foto yang menunggu diperiksa.') }}</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <article v-for="a in kotakMasuk" :key="a.id" class="{{ $kartu }} overflow-hidden flex flex-col">
                <div class="p-4 flex items-center gap-3">
                    <img v-if="a.penulis_foto" :src="a.penulis_foto" alt="" class="w-10 h-10 rounded-full object-cover">
                    <span v-else class="w-10 h-10 rounded-full bg-teal-600 text-white font-bold flex items-center justify-center" v-text="(a.penulis_nama || '?').charAt(0).toUpperCase()"></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800 truncate">
                            <span v-text="a.penulis_nama"></span>
                            <span class="ml-1 px-1.5 py-0.5 rounded bg-teal-600 text-white text-[10px] font-bold align-middle" v-text="a.penulis_label"></span>
                        </p>
                        <p class="text-[11px] text-slate-400" v-text="@js(__('Dikirim')) + ' ' + a.dibuat + ' · ' + a.foto.length + ' ' + @js(__('foto'))"></p>
                    </div>
                </div>
                <div class="px-4 grid grid-cols-4 gap-1.5">
                    <a v-for="(f, i) in a.foto.slice(0, 8)" :key="f.id" :href="f.url" target="_blank" rel="noopener" class="relative block aspect-square rounded-lg overflow-hidden bg-slate-100">
                        <img :src="f.url" alt="" loading="lazy" class="w-full h-full object-cover">
                        <span v-if="i === 7 && a.foto.length > 8" class="absolute inset-0 bg-black/55 text-white text-sm font-bold flex items-center justify-center" v-text="'+' + (a.foto.length - 8)"></span>
                    </a>
                </div>
                <div class="p-4 flex-1 text-sm">
                    <p class="whitespace-pre-line break-words" :class="a.keterangan ? 'text-slate-700' : 'text-slate-400'" v-text="a.keterangan || @js(__('(tanpa keterangan)'))"></p>
                    <p class="mt-1 text-[11px] text-slate-400" v-text="@js(__('Tanggal kegiatan:')) + ' ' + a.tanggal_teks"></p>
                </div>
                <div class="border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100 text-xs font-bold">
                    <a :href="a.url" target="_blank" rel="noopener" class="py-3 text-center text-slate-600 hover:bg-slate-50">{{ __('Pratinjau') }}</a>
                    <button type="button" @click="bukaTolak(a)" :disabled="sibuk" class="py-3 text-rose-600 hover:bg-rose-50">{{ __('Tolak') }}</button>
                    <button type="button" @click="tampilkan(a)" :disabled="sibuk" class="py-3 text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50">{{ __('Tampilkan') }}</button>
                </div>
            </article>
        </div>
    </template>

    {{-- ===== Album (admin + kiriman yang sudah diperiksa) ===== --}}
    <template v-if="tab === 'album'">
        <p v-if="!album.length" class="{{ $kartu }} p-10 text-center text-sm text-slate-400">{{ __('Belum ada album. Tekan "Unggah Foto" untuk memulai.') }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <article v-for="a in album" :key="a.id" class="{{ $kartu }} overflow-hidden flex flex-col" :class="a.status === 'ditolak' ? 'opacity-70' : ''">
                <a :href="a.url" target="_blank" rel="noopener" class="relative block aspect-square bg-slate-100">
                    <img v-if="a.foto.length" :src="a.foto[0].url" alt="" loading="lazy" class="w-full h-full object-cover">
                    <span class="absolute left-2 top-2 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1" :class="infoStatus(a.status).kelas" v-text="infoStatus(a.status).teks"></span>
                    <span v-if="a.foto.length > 1" class="absolute right-2 top-2 px-2 py-0.5 rounded-full bg-black/55 text-white text-[10px] font-bold" v-text="a.foto.length + ' ' + @js(__('foto'))"></span>
                </a>
                <div class="p-3 flex-1">
                    <p class="text-sm line-clamp-2" :class="a.keterangan ? 'font-semibold text-slate-700' : 'text-slate-400'" v-text="a.keterangan || @js(__('(tanpa keterangan)'))"></p>
                    <p class="mt-0.5 text-[11px] text-slate-400" v-text="a.penulis_nama + ' (' + a.penulis_label + ') · ' + a.tanggal_teks"></p>
                    <p v-if="a.status === 'tampil'" class="mt-1 text-[11px] text-slate-500"
                        v-text="'♥ ' + a.jumlah_suka + ' · ' + a.jumlah_komentar + ' ' + @js(__('komentar')) + ' · ' + a.jumlah_dilihat + ' ' + @js(__('dilihat'))"></p>
                    <p v-if="a.status === 'ditolak' && a.catatan_admin" class="mt-1 text-[11px] text-rose-600 line-clamp-2" v-text="@js(__('Catatan:')) + ' ' + a.catatan_admin"></p>
                </div>
                <div class="border-t border-slate-100 grid divide-x divide-slate-100 text-xs font-bold" :class="a.status === 'ditolak' ? 'grid-cols-3' : 'grid-cols-2'">
                    <button v-if="a.status === 'ditolak'" type="button" @click="tampilkan(a)" :disabled="sibuk" class="py-2.5 text-emerald-700 hover:bg-emerald-50">{{ __('Tampilkan') }}</button>
                    <button type="button" @click="bukaForm(a)" class="py-2.5 text-emerald-700 hover:bg-emerald-50">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(a)" :disabled="sibuk" class="py-2.5 text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                </div>
            </article>
        </div>
    </template>

    {{-- ===== Dari berita ===== --}}
    <template v-if="tab === 'berita'">
        <p class="text-sm text-slate-500">{{ __('Foto dari berita yang sudah Anda tampilkan. Sembunyikan foto yang tidak ingin masuk galeri; beritanya tidak berubah.') }}</p>
        <p v-if="!fotoBerita.length" class="{{ $kartu }} p-10 text-center text-sm text-slate-400">{{ __('Belum ada foto dari berita yang ditampilkan.') }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <article v-for="f in fotoBerita" :key="f.kunci" class="{{ $kartu }} overflow-hidden flex flex-col" :class="f.sembunyi ? 'opacity-60' : ''">
                <a :href="f.url" target="_blank" rel="noopener" class="relative block aspect-square bg-slate-100">
                    <img :src="f.url" alt="" loading="lazy" class="w-full h-full object-cover">
                    <span class="absolute left-2 top-2 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1"
                        :class="f.sembunyi ? 'bg-slate-100 text-slate-600 ring-slate-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200'"
                        v-text="f.sembunyi ? @js(__('Disembunyikan')) : @js(__('Tampil di galeri'))"></span>
                </a>
                <div class="p-3 flex-1">
                    <a :href="f.berita_url" target="_blank" rel="noopener" class="text-sm font-semibold text-slate-700 hover:text-emerald-700 line-clamp-2" v-text="f.berita_judul"></a>
                    <p class="mt-0.5 text-[11px] text-slate-400" v-text="f.tanggal_teks"></p>
                </div>
                <button type="button" @click="sembunyikan(f)" :disabled="sibuk"
                    class="border-t border-slate-100 py-2.5 text-xs font-bold hover:bg-slate-50"
                    :class="f.sembunyi ? 'text-emerald-700' : 'text-slate-600'"
                    v-text="f.sembunyi ? @js(__('Tampilkan di galeri')) : @js(__('Sembunyikan dari galeri'))"></button>
            </article>
        </div>
    </template>

    {{-- ===== Jendela: Unggah / Ubah album ===== --}}
    <div v-if="form" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="tutupForm">
        <form @submit.prevent="simpanAlbum" class="bg-white w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-800" v-text="form.id ? @js(__('Ubah album')) : @js(__('Unggah Foto'))"></h2>
                <button type="button" @click="tutupForm" class="w-9 h-9 rounded-full hover:bg-slate-100 text-2xl leading-none text-slate-500">&times;</button>
            </div>
            <div>
                <span class="{{ $label }}" v-text="@js(__('Foto satu album (boleh banyak sekaligus, maksimal :n)')).replace(':n', maksFoto)"></span>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    <div v-for="f in fotoTampil" :key="f.kunci" class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100">
                        <img :src="f.url" alt="" class="w-full h-full object-cover">
                        <span v-if="f.ukuran" class="absolute left-1 bottom-1 px-1.5 py-0.5 rounded bg-black/55 text-white text-[10px]" v-text="f.ukuran"></span>
                        <button type="button" @click="buangFoto(f)" class="absolute right-1 top-1 w-6 h-6 rounded-full bg-black/55 text-white leading-none hover:bg-rose-600">&times;</button>
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
                    <textarea v-model="form.keterangan" rows="2" maxlength="500" class="{{ $isian }}" placeholder="{{ __('cth. Ziarah bersama santri') }}"></textarea>
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('Tanggal kegiatan') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
                    <input v-model="form.tanggal" type="date" class="{{ $isian }}">
                </div>
            </div>
            <p v-if="galat" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-sm text-rose-700" v-text="galat"></p>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" @click="tutupForm" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="sibuk || memproses || !fotoTampil.length" class="px-6 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-50"
                    v-text="sibuk ? @js(__('Menyimpan…')) : (form.id ? @js(__('Simpan')) : @js(__('Unggah :n foto')).replace(':n', fotoTampil.length))"></button>
            </div>
        </form>
    </div>

    {{-- ===== Jendela: Tolak ===== --}}
    <div v-if="tolak" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="tolak = null">
        <form @submit.prevent="kirimTolak" class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">{{ __('Tolak foto') }}</h2>
            <p class="text-sm text-slate-500" v-text="@js(__('Kiriman dari :nama tidak ditampilkan. Pengirim bisa memperbaiki lalu mengirim ulang.')).replace(':nama', tolak.penulis_nama)"></p>
            <div>
                <label class="{{ $label }}">{{ __('Catatan untuk pengirim') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
                <textarea v-model="tolak.catatan" rows="3" maxlength="1000" class="{{ $isian }}" placeholder="{{ __('cth. Foto kurang jelas, mohon kirim yang lebih terang') }}"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="tolak = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="sibuk" class="px-6 py-2.5 rounded-2xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700 disabled:opacity-50">{{ __('Tolak') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('berita.partials.skrip-tulis')
@include('galeri.partials.skrip-album')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.TOKEN_BERITA = @json(csrf_token());
        const URL_DASAR = @json(route('admin.galeri.index'));
        const tabAwal = new URLSearchParams(location.search).get('tab');

        Vue.createApp({
            mixins: [window.MIXIN_ALBUM_FOTO],
            data: () => ({
                kotakMasuk: @json($kotakMasuk),
                album: @json($album),
                fotoBerita: @json($fotoBerita),
                maksFoto: @json($maksFoto),
                tab: ['masuk', 'album', 'berita'].includes(tabAwal) ? tabAwal : (@json($kotakMasuk->isNotEmpty()) ? 'masuk' : 'album'),
                tolak: null,
            }),
            computed: {
                tabDaftar() {
                    return [
                        { kunci: 'masuk', nama: @js(__('Kotak Masuk')), jumlah: this.kotakMasuk.length },
                        { kunci: 'album', nama: @js(__('Album')), jumlah: this.album.length },
                        { kunci: 'berita', nama: @js(__('Dari berita')), jumlah: this.fotoBerita.length },
                    ];
                },
            },
            methods: {
                ...metodeHalaman,
                // Balasan {pesan, kotakMasuk, album, fotoBerita}
                terimaData(data) {
                    if (data.kotakMasuk) this.kotakMasuk = data.kotakMasuk;
                    if (data.album) this.album = data.album;
                    if (data.fotoBerita) this.fotoBerita = data.fotoBerita;
                },
                bukaUnggah() {
                    this.tab = 'album';
                    this.bukaForm();
                },
                simpanAlbum() {
                    this.kirimAlbum(this.form.id ? URL_DASAR + '/' + this.form.id : URL_DASAR);
                },
                tampilkan(a) {
                    this.aksi('POST', URL_DASAR + '/' + a.id + '/tampilkan');
                },
                bukaTolak(a) {
                    this.tolak = { id: a.id, penulis_nama: a.penulis_nama, catatan: '' };
                },
                async kirimTolak() {
                    if (await this.aksi('POST', URL_DASAR + '/' + this.tolak.id + '/tolak', { catatan: this.tolak.catatan })) this.tolak = null;
                },
                hapus(a) {
                    if (!confirm(@js(__('Hapus album ini beserta semua fotonya, suka, dan komentarnya? Tidak bisa dibatalkan.')))) return;
                    this.aksi('DELETE', URL_DASAR + '/' + a.id);
                },
                sembunyikan(f) {
                    this.aksi('PATCH', URL_DASAR + '/berita/' + f.id);
                },
            },
        }).mount('#halaman-galeri');
    });
</script>
@endpush
