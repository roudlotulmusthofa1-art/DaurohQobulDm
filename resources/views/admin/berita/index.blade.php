{{--
    Admin > Landing Page > Berita
    - Kotak Masuk: tulisan asatid & siswa menunggu diperiksa -> Tampilkan (+ batas waktu) / Tolak (+ catatan)
    - Sedang Tampil (ubah batas, turunkan) · Selesai tayang (tampilkan lagi) · Ditolak · Semua
    - Tulis Berita: tulisan admin langsung tampil
    - Komentar: lihat & hapus
    Vue (#halaman-berita) + mixin window.MIXIN_TULIS_BERITA (berita/partials/skrip-tulis). Simpan lewat Admin\BeritaController (JSON).
--}}
@extends('layouts.admin')

@section('title', __('Berita'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
$tombol = 'inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors disabled:opacity-40';
@endphp

<div id="halaman-berita" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10" v-cloak>

    <x-header-admin :label="__('Landing Page')" :judul="__('Berita')"
        :keterangan="__('Berita dari admin, asatid, dan siswa. Tulisan asatid & siswa masuk Kotak Masuk; tampil di landing page setelah Anda tekan Tampilkan.')">
        <x-slot:aksi>
        <a href="{{ route('landing') }}#berita" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            {{ __('Lihat landing page') }}
        </a>
        <button type="button" @click="bukaForm()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('Tulis Berita') }}
        </button>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Tab ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
            <button v-for="t in tab" :key="t.kunci" type="button" @click="pilihTab(t.kunci)"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold ring-1 transition-colors"
                :class="tabAktif === t.kunci ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-text="t.nama"></span>
                <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px]"
                    :class="tabAktif === t.kunci ? 'bg-white/20' : (t.kunci === 'menunggu' && t.jumlah ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-500')" v-text="t.jumlah"></span>
            </button>
        </div>
        <input v-model="cari" type="search" placeholder="{{ __('Cari judul / penulis…') }}" class="{{ $isian }} lg:max-w-xs lg:ml-auto">
    </div>

    {{-- ===== Daftar ===== --}}
    <p v-if="!daftarTampil.length" class="{{ $kartu }} p-10 text-center text-sm text-slate-400" v-text="tabAktif === 'menunggu' ? @js(__('Kotak masuk kosong. Tidak ada berita yang menunggu diperiksa.')) : @js(__('Belum ada berita di sini.'))"></p>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <article v-for="b in daftarTampil" :key="b.id" class="{{ $kartu }} overflow-hidden flex flex-col">
            <div class="flex gap-4 p-4 sm:p-5">
                <a :href="b.url" target="_blank" rel="noopener" class="w-28 h-24 sm:w-36 sm:h-28 shrink-0 rounded-2xl overflow-hidden bg-slate-100 block">
                    <img v-if="b.foto.length" :src="b.foto[0].url" alt="" class="w-full h-full object-cover">
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-full ring-1 text-[11px] font-bold" :class="infoStatus(b.status).kelas" v-text="infoStatus(b.status).teks"></span>
                        <span v-if="b.kategori" class="px-2 py-0.5 rounded ring-1 text-[10px] font-bold uppercase" :class="b.kelas_kategori" v-text="b.kategori"></span>
                        <span v-if="b.foto.length > 1" class="text-[11px] text-slate-400" v-text="b.foto.length + ' ' + @js(__('foto'))"></span>
                    </div>
                    <h3 class="mt-1.5 font-bold text-slate-800 leading-snug line-clamp-2" v-text="b.judul"></h3>
                    <div class="mt-2 flex items-center gap-2 min-w-0">
                        <img v-if="b.penulis_foto" :src="b.penulis_foto" alt="" class="w-6 h-6 rounded-full object-cover">
                        <span class="text-xs font-semibold text-slate-600 truncate" v-text="b.penulis_nama"></span>
                        <span class="shrink-0 px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-bold text-slate-500" v-text="b.penulis_label"></span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400" v-text="@js(__('Dikirim')) + ' ' + b.dibuat + ' WIB'"></p>
                </div>
            </div>

            <div class="px-4 sm:px-5 pb-3 space-y-2 text-xs">
                <p v-if="b.ringkasan || b.isi" class="text-slate-500 line-clamp-2" v-text="b.ringkasan || b.isi"></p>
                <p v-if="b.status === 'tampil'" class="font-semibold text-emerald-700"
                    v-text="b.tampil_sampai ? @js(__('Tampil sampai')) + ' ' + b.tampil_sampai + ' WIB' : @js(__('Tampil tanpa batas waktu'))"></p>
                <p v-if="b.status === 'selesai'" class="font-semibold text-slate-500" v-text="@js(__('Selesai tayang')) + ' ' + b.tampil_sampai + ' WIB'"></p>
                <p v-if="b.status === 'ditolak'" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-rose-700">
                    <b>{{ __('Catatan:') }}</b> <span v-text="b.catatan_admin || @js(__('(tanpa catatan)'))"></span>
                </p>
                <p v-if="b.diperiksa_oleh && b.status !== 'menunggu'" class="text-slate-400" v-text="@js(__('Diperiksa')) + ' ' + b.diperiksa_oleh + ' · ' + b.diperiksa_at"></p>
                <p v-if="b.status === 'tampil' || b.status === 'selesai'" class="text-slate-500"
                    v-text="b.jumlah_dilihat + ' ' + @js(__('dilihat')) + ' · ' + b.jumlah_suka + ' ' + @js(__('suka')) + ' · ' + b.jumlah_komentar + ' ' + @js(__('komentar'))"></p>
            </div>

            <div class="mt-auto border-t border-slate-100 p-3 flex flex-wrap gap-2">
                <button v-if="b.status !== 'tampil'" type="button" @click="bukaTampilkan(b)" :disabled="sibuk" class="{{ $tombol }} bg-emerald-600 text-white hover:bg-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span v-text="b.status === 'selesai' ? @js(__('Tampilkan lagi')) : @js(__('Tampilkan'))"></span>
                </button>
                <button v-if="b.status === 'tampil'" type="button" @click="bukaTampilkan(b)" :disabled="sibuk" class="{{ $tombol }} bg-emerald-50 text-emerald-700 hover:bg-emerald-100">{{ __('Ubah batas waktu') }}</button>
                <button v-if="b.status === 'tampil'" type="button" @click="turunkan(b)" :disabled="sibuk" class="{{ $tombol }} bg-amber-50 text-amber-700 hover:bg-amber-100">{{ __('Turunkan') }}</button>
                <button v-if="b.status === 'menunggu'" type="button" @click="bukaTolak(b)" :disabled="sibuk" class="{{ $tombol }} bg-rose-50 text-rose-700 hover:bg-rose-100">{{ __('Tolak') }}</button>
                <a :href="b.url" target="_blank" rel="noopener" class="{{ $tombol }} bg-slate-100 text-slate-600 hover:bg-slate-200">{{ __('Lihat') }}</a>
                <button type="button" @click="bukaForm(b)" :disabled="sibuk" class="{{ $tombol }} bg-slate-100 text-slate-600 hover:bg-slate-200">{{ __('Ubah') }}</button>
                <button v-if="b.jumlah_komentar" type="button" @click="bukaKomentar(b)" class="{{ $tombol }} bg-sky-50 text-sky-700 hover:bg-sky-100" v-text="@js(__('Komentar')) + ' (' + b.jumlah_komentar + ')'"></button>
                <button type="button" @click="hapus(b)" :disabled="sibuk" class="{{ $tombol }} text-rose-600 hover:bg-rose-50 ml-auto">{{ __('Hapus') }}</button>
            </div>
        </article>
    </div>

    {{-- ===== Jendela: Tulis / Ubah berita ===== --}}
    <div v-if="form" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="form = null">
        <form @submit.prevent="simpan" class="bg-white w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-bold text-slate-800" v-text="form.id ? @js(__('Ubah berita')) : @js(__('Tulis berita'))"></h2>
                <button type="button" @click="form = null" class="w-9 h-9 rounded-full hover:bg-slate-100 text-2xl leading-none text-slate-500">&times;</button>
            </div>
            <p v-if="!form.id" class="mb-4 rounded-xl bg-emerald-50 ring-1 ring-emerald-100 px-3 py-2 text-xs text-emerald-800">{{ __('Berita tulisan admin langsung tampil di landing page setelah disimpan.') }}</p>
            @include('berita.partials.form-tulis', ['admin' => true])
            <p v-if="form.id && form.status !== 'tampil'" class="mt-2 text-[11px] text-slate-400">{{ __('"Tampil sampai" hanya dipakai untuk berita yang sedang tampil. Untuk menampilkan, pakai tombol Tampilkan.') }}</p>
            <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" @click="form = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit" :disabled="sibuk || memproses" class="px-6 py-2.5 rounded-2xl bg-[#0f6b5c] text-white text-sm font-bold hover:bg-[#0b5a4d] disabled:opacity-50"
                    v-text="sibuk ? @js(__('Menyimpan…')) : (form.id ? @js(__('Simpan perubahan')) : @js(__('Terbitkan')))"></button>
            </div>
        </form>
    </div>

    {{-- ===== Jendela: Tampilkan (batas waktu) ===== --}}
    <div v-if="panel && panel.jenis === 'tampilkan'" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="panel = null">
        <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-800">{{ __('Tampilkan di landing page') }}</h2>
            <p class="mt-1 text-sm text-slate-500 line-clamp-2" v-text="panel.b.judul"></p>
            <label class="block mt-4 text-xs font-semibold text-slate-600 mb-1.5">{{ __('Tampil sampai tanggal (WIB, sampai pukul 23.59)') }}</label>
            <input v-model="panel.sampai" type="date" :min="hariIni" class="{{ $isian }}">
            <div class="mt-2 flex flex-wrap gap-2">
                <button v-for="h in [3, 7, 14, 30]" :key="h" type="button" @click="panel.sampai = tambahHari(h)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700" v-text="h + ' ' + @js(__('hari'))"></button>
                <button type="button" @click="panel.sampai = ''" class="px-3 py-1.5 rounded-xl text-xs font-bold"
                    :class="panel.sampai ? 'bg-slate-100 text-slate-600 hover:bg-emerald-50' : 'bg-emerald-600 text-white'">{{ __('Tanpa batas') }}</button>
            </div>
            <p class="mt-3 text-xs text-slate-500" v-text="panel.sampai ? @js(__('Setelah tanggal itu berita turun sendiri dan pindah ke Selesai tayang. Bisa ditampilkan lagi kapan saja.')) : @js(__('Berita tampil terus sampai Anda turunkan.'))"></p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="panel = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="button" @click="tampilkan" :disabled="sibuk" class="px-6 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 disabled:opacity-50">{{ __('Tampilkan') }}</button>
            </div>
        </div>
    </div>

    {{-- ===== Jendela: Tolak ===== --}}
    <div v-if="panel && panel.jenis === 'tolak'" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="panel = null">
        <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-800">{{ __('Tolak berita') }}</h2>
            <p class="mt-1 text-sm text-slate-500 line-clamp-2" v-text="panel.b.judul"></p>
            <label class="block mt-4 text-xs font-semibold text-slate-600 mb-1.5">{{ __('Catatan untuk penulis (opsional)') }}</label>
            <textarea v-model="panel.catatan" rows="4" maxlength="1000" class="{{ $isian }}" placeholder="{{ __('cth. Foto kurang jelas, mohon ganti foto.') }}"></textarea>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="panel = null" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="button" @click="tolak" :disabled="sibuk" class="px-6 py-2.5 rounded-2xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700 disabled:opacity-50">{{ __('Tolak') }}</button>
            </div>
        </div>
    </div>

    {{-- ===== Jendela: Komentar ===== --}}
    <div v-if="panel && panel.jenis === 'komentar'" class="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center sm:p-4" @click.self="panel = null">
        <div class="bg-white w-full sm:max-w-lg max-h-[85vh] flex flex-col rounded-t-3xl sm:rounded-3xl">
            <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-800">{{ __('Komentar') }}</h2>
                    <p class="text-sm text-slate-500 truncate" v-text="panel.b.judul"></p>
                </div>
                <button type="button" @click="panel = null" class="w-9 h-9 shrink-0 rounded-full hover:bg-slate-100 text-2xl leading-none text-slate-500">&times;</button>
            </div>
            <div class="p-5 overflow-y-auto space-y-3">
                <p v-if="panel.memuat" class="text-sm text-slate-400 text-center">{{ __('Memuat…') }}</p>
                <p v-else-if="!panel.komentar.length" class="text-sm text-slate-400 text-center">{{ __('Belum ada komentar.') }}</p>
                <div v-for="k in panel.komentar" :key="k.id" class="rounded-2xl bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-bold text-slate-800 min-w-0 truncate">
                            <span v-text="k.nama"></span>
                            <span v-if="k.peran" class="ml-1 px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[10px]" v-text="k.peran"></span>
                        </p>
                        <button type="button" @click="hapusKomentar(k)" class="shrink-0 text-xs font-bold text-rose-600 hover:underline">{{ __('Hapus') }}</button>
                    </div>
                    <p class="mt-1 text-sm text-slate-600 whitespace-pre-line break-words" v-text="k.isi"></p>
                    <p class="mt-1 text-[11px] text-slate-400" v-text="k.waktu"></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('berita.partials.skrip-tulis')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.TOKEN_BERITA = @json(csrf_token());
        const URL_DASAR = @json(route('admin.berita.index'));
        const TAB = ['menunggu', 'tampil', 'selesai', 'ditolak', 'semua'];

        Vue.createApp({
            mixins: [window.MIXIN_TULIS_BERITA],
            data() {
                const daftar = @json($daftar);
                const dariUrl = new URLSearchParams(location.search).get('tab');
                return {
                    daftar,
                    kategori: @json($kategori),
                    maksFoto: @json($maksFoto),
                    tabAktif: TAB.includes(dariUrl) ? dariUrl : (daftar.some((b) => b.status === 'menunggu') ? 'menunggu' : 'tampil'),
                    cari: '',
                    panel: null,
                };
            },
            computed: {
                tab() {
                    const n = (s) => this.daftar.filter((b) => b.status === s).length;
                    return [
                        { kunci: 'menunggu', nama: @js(__('Kotak Masuk')), jumlah: n('menunggu') },
                        { kunci: 'tampil', nama: @js(__('Sedang Tampil')), jumlah: n('tampil') },
                        { kunci: 'selesai', nama: @js(__('Selesai tayang')), jumlah: n('selesai') },
                        { kunci: 'ditolak', nama: @js(__('Ditolak')), jumlah: n('ditolak') },
                        { kunci: 'semua', nama: @js(__('Semua')), jumlah: this.daftar.length },
                    ];
                },
                daftarTampil() {
                    const k = this.cari.trim().toLowerCase();
                    return this.daftar
                        .filter((b) => this.tabAktif === 'semua' || b.status === this.tabAktif)
                        .filter((b) => !k || [b.judul, b.penulis_nama, b.lokasi, b.kategori].some((t) => (t || '').toLowerCase().includes(k)));
                },
            },
            methods: {
                ...metodeHalaman,
                pilihTab(t) {
                    this.tabAktif = t;
                    const u = new URL(location.href);
                    u.searchParams.set('tab', t);
                    history.replaceState(null, '', u);
                },
                tambahHari(h) {
                    const d = new Date(this.hariIni + 'T00:00:00');
                    d.setDate(d.getDate() + h);
                    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                },
                async simpan() {
                    const baru = !this.form.id;
                    const ok = await this.kirimBerita(baru ? URL_DASAR : URL_DASAR + '/' + this.form.id);
                    if (ok && baru) this.pilihTab('tampil');
                },
                bukaTampilkan(b) {
                    this.panel = { jenis: 'tampilkan', b, sampai: b.status === 'tampil' ? (b.tampil_sampai_tanggal || '') : this.tambahHari(7) };
                },
                async tampilkan() {
                    if (await this.aksi('POST', URL_DASAR + '/' + this.panel.b.id + '/tampilkan', { tampil_sampai: this.panel.sampai || null })) this.panel = null;
                },
                bukaTolak(b) {
                    this.panel = { jenis: 'tolak', b, catatan: '' };
                },
                async tolak() {
                    if (await this.aksi('POST', URL_DASAR + '/' + this.panel.b.id + '/tolak', { catatan: this.panel.catatan })) this.panel = null;
                },
                turunkan(b) {
                    if (!confirm(@js(__('Turunkan ":judul" dari landing page sekarang?')).replace(':judul', b.judul))) return;
                    this.aksi('POST', URL_DASAR + '/' + b.id + '/turunkan');
                },
                hapus(b) {
                    if (!confirm(@js(__('Hapus berita ":judul" beserta foto dan komentarnya? Tidak bisa dibatalkan.')).replace(':judul', b.judul))) return;
                    this.aksi('DELETE', URL_DASAR + '/' + b.id);
                },
                async bukaKomentar(b) {
                    this.panel = { jenis: 'komentar', b, komentar: [], memuat: true };
                    try {
                        const res = await fetch(URL_DASAR + '/' + b.id + '/komentar', { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (this.panel?.b === b) this.panel.komentar = data.komentar || [];
                    } catch (e) {
                        window.notifikasi?.gagal(@js(__('Komentar gagal dimuat.')));
                    } finally {
                        if (this.panel?.b === b) this.panel.memuat = false;
                    }
                },
                async hapusKomentar(k) {
                    if (!confirm(@js(__('Hapus komentar ini?')))) return;
                    const data = await this.aksi('DELETE', URL_DASAR + '/komentar/' + k.id);
                    if (data && this.panel) this.panel.komentar = data.komentar;
                },
            },
        }).mount('#halaman-berita');
    });
</script>
@endpush
