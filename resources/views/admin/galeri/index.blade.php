{{--
    Admin > Landing Page > Galeri Foto (hanya admin yang mengunggah).
    - Tab Unggahan admin: Unggah Foto (banyak sekaligus, diperkecil di browser lewat window.perkecilFoto) + keterangan & tanggal; ubah, hapus
    - Tab Dari Berita: foto berita yang sudah ditampilkan otomatis masuk galeri; Sembunyikan / Tampilkan lagi
    Vue #halaman-galeri; simpan lewat Admin\GaleriController (JSON {pesan, fotoAdmin, fotoBerita}).
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
        :keterangan="__('Foto untuk halaman Galeri Foto. Hanya admin yang mengunggah; foto berita yang sudah ditampilkan otomatis ikut masuk.')">
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
            <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px]" :class="tab === t.kunci ? 'bg-white/20' : 'bg-slate-100 text-slate-500'" v-text="t.jumlah"></span>
        </button>
    </div>

    {{-- ===== Unggahan admin ===== --}}
    <template v-if="tab === 'admin'">
        <p v-if="!fotoAdmin.length" class="{{ $kartu }} p-10 text-center text-sm text-slate-400">{{ __('Belum ada foto unggahan admin. Tekan "Unggah Foto" untuk memulai.') }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <article v-for="f in fotoAdmin" :key="f.kunci" class="{{ $kartu }} overflow-hidden flex flex-col">
                <a :href="f.url" target="_blank" rel="noopener" class="block aspect-square bg-slate-100">
                    <img :src="f.url" alt="" loading="lazy" class="w-full h-full object-cover">
                </a>
                <div class="p-3 flex-1">
                    <p class="text-sm font-semibold text-slate-700 line-clamp-2" v-text="f.keterangan || @js(__('(tanpa keterangan)'))" :class="f.keterangan ? '' : 'text-slate-400 font-normal'"></p>
                    <p class="mt-0.5 text-[11px] text-slate-400" v-text="f.tanggal_teks"></p>
                </div>
                <div class="border-t border-slate-100 grid grid-cols-2 divide-x divide-slate-100 text-xs font-bold">
                    <button type="button" @click="bukaUbah(f)" class="py-2.5 text-emerald-700 hover:bg-emerald-50">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapus(f)" :disabled="sibuk" class="py-2.5 text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
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

    {{-- ===== Jendela: Unggah Foto ===== --}}
    <div v-if="unggah" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="tutupUnggah">
        <form @submit.prevent="kirimUnggah" class="bg-white w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-800">{{ __('Unggah Foto') }}</h2>
                <button type="button" @click="tutupUnggah" class="w-9 h-9 rounded-full hover:bg-slate-100 text-2xl leading-none text-slate-500">&times;</button>
            </div>
            <div>
                <span class="{{ $label }}" v-text="@js(__('Foto (boleh banyak sekaligus, maksimal :n)')).replace(':n', maksUnggah)"></span>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    <div v-for="f in unggah.foto" :key="f.kunci" class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100">
                        <img :src="f.url" alt="" class="w-full h-full object-cover">
                        <span class="absolute left-1 bottom-1 px-1.5 py-0.5 rounded bg-black/55 text-white text-[10px]" v-text="f.ukuran"></span>
                        <button type="button" @click="buangFoto(f)" class="absolute right-1 top-1 w-6 h-6 rounded-full bg-black/55 text-white leading-none hover:bg-rose-600">&times;</button>
                    </div>
                    <label v-if="unggah.foto.length < maksUnggah"
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
                    <label class="{{ $label }}">{{ __('Keterangan') }} <span class="font-normal text-slate-400">{{ __('(opsional, berlaku untuk semua foto di atas)') }}</span></label>
                    <input v-model="unggah.keterangan" type="text" maxlength="200" class="{{ $isian }}" placeholder="{{ __('cth. Ziarah bersama santri') }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('Tanggal kegiatan') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
                    <input v-model="unggah.tanggal" type="date" class="{{ $isian }}">
                </div>
            </div>
            <p v-if="galat" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-sm text-rose-700" v-text="galat"></p>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" @click="tutupUnggah" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="sibuk || memproses || !unggah.foto.length" class="px-6 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-50"
                    v-text="sibuk ? @js(__('Mengunggah…')) : @js(__('Unggah :n foto')).replace(':n', unggah.foto.length)"></button>
            </div>
        </form>
    </div>

    {{-- ===== Jendela: Ubah keterangan ===== --}}
    <div v-if="ubah" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="ubah = null">
        <form @submit.prevent="simpanUbah" class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">{{ __('Ubah foto') }}</h2>
            <img :src="ubah.url" alt="" class="w-full max-h-56 object-contain rounded-xl bg-slate-100">
            <div>
                <label class="{{ $label }}">{{ __('Keterangan') }}</label>
                <input v-model="ubah.keterangan" type="text" maxlength="200" class="{{ $isian }}">
            </div>
            <div>
                <label class="{{ $label }}">{{ __('Tanggal kegiatan') }}</label>
                <input v-model="ubah.tanggal" type="date" class="{{ $isian }}">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="ubah = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="sibuk" class="px-6 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-50">{{ __('Simpan') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('berita.partials.skrip-tulis')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const TOKEN = @json(csrf_token());
        const URL_DASAR = @json(route('admin.galeri.index'));
        const ukuranTeks = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');

        Vue.createApp({
            data: () => ({
                fotoAdmin: @json($fotoAdmin),
                fotoBerita: @json($fotoBerita),
                maksUnggah: @json($maksUnggah),
                tab: 'admin',
                unggah: null,
                ubah: null,
                galat: '',
                sibuk: false,
                memproses: false,
            }),
            computed: {
                tabDaftar() {
                    return [
                        { kunci: 'admin', nama: @js(__('Unggahan admin')), jumlah: this.fotoAdmin.length },
                        { kunci: 'berita', nama: @js(__('Dari berita')), jumlah: this.fotoBerita.length },
                    ];
                },
            },
            methods: {
                ...metodeHalaman,
                // Kirim JSON / FormData; balasan {pesan, fotoAdmin, fotoBerita}
                async aksiGaleri(metode, url, isi = null) {
                    this.sibuk = true;
                    this.galat = '';
                    try {
                        const formData = isi instanceof FormData;
                        const res = await fetch(url, {
                            method: metode,
                            headers: Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }, formData || !isi ? {} : { 'Content-Type': 'application/json' }),
                            body: isi ? (formData ? isi : JSON.stringify(isi)) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            if (res.status === 413) throw new Error(@js(__('Foto terlalu besar untuk dikirim sekaligus. Kurangi jumlah foto lalu coba lagi.')));
                            if (res.status === 419) throw new Error(@js(__('Sesi halaman habis. Muat ulang halaman lalu coba lagi.')));
                            throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan. Coba lagi.'))));
                        }
                        this.fotoAdmin = data.fotoAdmin;
                        this.fotoBerita = data.fotoBerita;
                        window.notifikasi?.sukses(data.pesan);
                        return true;
                    } catch (e) {
                        const pesan = e.message === 'Failed to fetch' ? @js(__('Koneksi terputus. Periksa internet lalu coba lagi.')) : e.message;
                        if (this.unggah) this.galat = pesan; else window.notifikasi?.gagal(pesan);
                        return false;
                    } finally {
                        this.sibuk = false;
                    }
                },
                bukaUnggah() {
                    this.tab = 'admin';
                    this.galat = '';
                    this.unggah = { foto: [], keterangan: '', tanggal: '' };
                },
                tutupUnggah() {
                    this.unggah?.foto.forEach((f) => URL.revokeObjectURL(f.url));
                    this.unggah = null;
                },
                async pilihFoto(e) {
                    const file = Array.from(e.target.files || []);
                    e.target.value = '';
                    const sisa = this.maksUnggah - this.unggah.foto.length;
                    if (file.length > sisa) window.notifikasi?.peringatan(@js(__('Sekali unggah paling banyak :n foto. Sebagian tidak ditambahkan.')).replace(':n', this.maksUnggah));
                    this.memproses = true;
                    try {
                        for (const f of file.slice(0, Math.max(0, sisa))) {
                            const kecil = await window.perkecilFoto(f);
                            if (!/image\/(jpeg|png|webp|gif)/.test(kecil.type)) {
                                window.notifikasi?.gagal(@js(__('Foto ":nama" tidak bisa dibaca. Coba foto lain atau ubah ke JPG.')).replace(':nama', f.name));
                                continue;
                            }
                            this.unggah.foto.push({ kunci: Math.random().toString(36).slice(2), file: kecil, url: URL.createObjectURL(kecil), ukuran: ukuranTeks(kecil.size) });
                        }
                    } finally {
                        this.memproses = false;
                    }
                },
                buangFoto(f) {
                    URL.revokeObjectURL(f.url);
                    this.unggah.foto = this.unggah.foto.filter((x) => x !== f);
                },
                async kirimUnggah() {
                    const fd = new FormData();
                    this.unggah.foto.forEach((f) => fd.append('foto[]', f.file, f.file.name));
                    fd.append('keterangan', this.unggah.keterangan || '');
                    fd.append('tanggal', this.unggah.tanggal || '');
                    if (await this.aksiGaleri('POST', URL_DASAR, fd)) this.tutupUnggah();
                },
                bukaUbah(f) {
                    this.ubah = { id: f.id, url: f.url, keterangan: f.keterangan || '', tanggal: f.tanggal || '' };
                },
                async simpanUbah() {
                    if (await this.aksiGaleri('PUT', URL_DASAR + '/' + this.ubah.id, { keterangan: this.ubah.keterangan, tanggal: this.ubah.tanggal || null })) this.ubah = null;
                },
                hapus(f) {
                    if (!confirm(@js(__('Hapus foto ini dari galeri? Tidak bisa dibatalkan.')))) return;
                    this.aksiGaleri('DELETE', URL_DASAR + '/' + f.id);
                },
                sembunyikan(f) {
                    this.aksiGaleri('PATCH', URL_DASAR + '/berita/' + f.id);
                },
            },
        }).mount('#halaman-galeri');
    });
</script>
@endpush
