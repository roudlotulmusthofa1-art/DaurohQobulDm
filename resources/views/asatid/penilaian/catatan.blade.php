{{--
    Akun Asatid > Penilaian > Catatan (Asatid\PenilaianController@catatan).
    Urutan mengisi: 1) pilih santri (boleh beberapa)  2) jenis: Pencapaian / Pelanggaran
                    3) tulis catatan  4) tanggal & jam (otomatis hari ini & jam sekarang dari HP, bisa diubah).
    Catatan langsung masuk ke Admin > Penilaian > Catatan. Di bawahnya: catatan milik asatid ini (bisa dihapus).
    Vue: #isi-catatan.
--}}
@extends('layouts.asatid')

@section('judul', __('Catatan'))
@section('kembali', route('asatid.penilaian'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
@endphp

<div id="isi-catatan" v-cloak class="space-y-5 pb-24">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-rose-400 via-amber-400 to-emerald-500 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Penilaian') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Tulis Catatan Santri') }}</h2>
                <p class="text-sm text-white/70">{{ __('Pencapaian atau pelanggaran. Langsung masuk ke admin.') }}</p>
            </div>
        </div>
    </section>

    {{-- ===== 1. Santri ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800">
            <span class="{{ $nomor }}">1</span>
            {{ __('Pilih santri') }}
            <span v-if="dipilih.length" class="ml-auto rounded-full bg-[#0b332c] px-2.5 py-0.5 text-xs font-semibold text-white"><span v-text="dipilih.length"></span> {{ __('dipilih') }}</span>
        </h3>

        <div v-if="dipilih.length" class="mt-3 flex flex-wrap gap-1.5">
            <button v-for="s in siswaDipilih" :key="s.siswa_id" type="button" @click="pilihSiswa(s)"
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 pl-3 pr-1.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-emerald-200">
                <span v-text="s.siswa"></span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor / kelas…') }}" class="mt-3 {{ $input }}">

        <ul class="mt-2 max-h-80 overflow-y-auto divide-y divide-slate-100 -mx-1 px-1">
            <li v-for="s in siswaTampil" :key="s.siswa_id">
                <button type="button" @click="pilihSiswa(s)" class="w-full flex items-center gap-3 py-2.5 text-left">
                    <img v-if="s.foto" :src="s.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                    <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="s.siswa"></span>
                        <span class="block text-xs text-slate-400 truncate" v-text="[s.nomor, s.kelas].filter(Boolean).join(' · ')"></span>
                    </span>
                    <span class="w-6 h-6 shrink-0 rounded-full ring-2 flex items-center justify-center"
                        :class="dipilih.includes(s.siswa_id) ? 'bg-emerald-600 ring-emerald-600 text-white' : 'ring-slate-200 text-transparent'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                </button>
            </li>
        </ul>
        <p v-if="!siswaTersaring.length" class="py-4 text-center text-sm text-slate-400">{{ __('Santri tidak ditemukan.') }}</p>
        <p v-else-if="siswaTersaring.length > siswaTampil.length" class="pt-2 text-center text-xs text-slate-400">{{ __('Ketik nama untuk mencari santri lainnya.') }}</p>
    </section>

    {{-- ===== 2. Jenis ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Jenis catatan') }}</h3>
        <div class="mt-3 grid grid-cols-2 gap-3">
            <button v-for="j in JENIS" :key="j.kunci" type="button" @click="form.jenis = j.kunci"
                class="flex flex-col items-center gap-2 rounded-2xl px-4 py-4 text-center ring-2 transition-all"
                :class="form.jenis === j.kunci ? j.aktif : 'bg-white ring-slate-200 text-slate-600 hover:bg-slate-50'">
                <span class="w-11 h-11 rounded-2xl flex items-center justify-center" :class="form.jenis === j.kunci ? 'bg-white/20' : j.ikonLembut">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="j.ikon"/></svg>
                </span>
                <span class="font-extrabold" v-text="j.label"></span>
            </button>
        </div>
    </section>

    {{-- ===== 3. Catatan ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">3</span>{{ __('Isi catatan') }}</h3>
        <textarea v-model="form.catatan" rows="4" maxlength="2000"
            :placeholder="form.jenis === 'pelanggaran' ? @js(__('cth. Tidak ikut sholat Subuh berjamaah tanpa izin.')) : @js(__('cth. Hafal surat Al-Mulk dengan lancar.'))"
            class="mt-3 {{ $input }}"></textarea>
        <p class="mt-1 text-right text-[11px] text-slate-400"><span v-text="form.catatan.length"></span>/2000</p>
    </section>

    {{-- ===== 4. Tanggal & jam ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">4</span>{{ __('Hari, tanggal & jam') }}</h3>
        <div class="mt-3 grid grid-cols-2 gap-3">
            <label class="block col-span-2 sm:col-span-1">
                <span class="text-xs font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                <input type="date" v-model="form.tanggal" class="mt-1 {{ $input }}">
                <span class="mt-1 block text-xs font-semibold text-slate-500">{{ __('Hari') }}: <b class="text-slate-800" v-text="hari"></b></span>
            </label>
            <label class="block col-span-2 sm:col-span-1">
                <span class="text-xs font-semibold text-slate-500">{{ __('Jam') }}</span>
                <input type="time" v-model="form.jam" class="mt-1 {{ $input }}">
                <button type="button" @click="isiSekarang" class="mt-1 text-xs font-semibold text-emerald-600 hover:underline">{{ __('Pakai hari & jam sekarang') }}</button>
            </label>
        </div>
    </section>

    {{-- ===== Tombol simpan (menempel di bawah layar) ===== --}}
    <div class="fixed inset-x-0 bottom-0 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 py-3">
        <div class="mx-auto max-w-6xl flex items-center gap-3">
            <p class="flex-1 min-w-0 text-xs text-slate-500 truncate">
                <template v-if="dipilih.length"><b class="text-slate-800" v-text="dipilih.length"></b> {{ __('santri') }} · </template>
                <b v-if="form.jenis" :class="form.jenis === 'pelanggaran' ? 'text-rose-600' : 'text-emerald-600'" v-text="labelJenis"></b>
                <template v-if="form.jenis"> · </template>
                <span v-text="hari"></span> <span v-text="form.jam"></span>
            </p>
            <button type="button" @click="simpan" :disabled="menyimpan"
                class="shrink-0 px-5 py-3 rounded-2xl text-white text-sm font-extrabold shadow-lg disabled:opacity-50 transition-colors"
                :class="form.jenis === 'pelanggaran' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                <span v-if="menyimpan">{{ __('Menyimpan…') }}</span>
                <span v-else>{{ __('Simpan catatan') }}</span>
            </button>
        </div>
    </div>

    {{-- ===== Catatan saya ===== --}}
    <section class="{{ $kartu }} p-5">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800">{{ __('Catatan Anda') }}</h3>
            <span class="text-xs text-slate-400">{{ __('50 terakhir') }}</span>
        </div>
        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="b in riwayat" :key="b.id" class="py-3 flex items-start gap-3">
                <span class="mt-1 w-2.5 h-2.5 shrink-0 rounded-full" :class="b.jenis === 'pencapaian' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                <span class="flex-1 min-w-0">
                    <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-semibold text-slate-800" v-text="b.siswa"></span>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1"
                            :class="b.jenis === 'pencapaian' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200'" v-text="b.label"></span>
                    </span>
                    <span class="block mt-0.5 text-xs text-slate-400">
                        <span v-text="b.hari"></span>, <span v-text="b.tanggal"></span>
                        <template v-if="b.jam"> · {{ __('jam') }} <span v-text="b.jam"></span></template>
                    </span>
                    <span class="block mt-1 text-sm text-slate-600 whitespace-pre-line" v-text="b.catatan"></span>
                </span>
                <button type="button" @click="hapus(b)" :disabled="menghapus === b.id"
                    title="{{ __('Hapus catatan') }}" aria-label="{{ __('Hapus catatan') }}"
                    class="shrink-0 w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </li>
        </ul>
        <p v-if="!riwayat.length" class="py-8 text-center text-sm text-slate-400">{{ __('Belum ada catatan dari Anda.') }}</p>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const SIMPAN = @json(route('asatid.penilaian.catatan.simpan'));
    const HAPUS = @json(route('asatid.penilaian.catatan.hapus', ['id' => '__ID__']));
    const CSRF = @json(csrf_token());
    const JENIS = [
        { kunci: 'pencapaian', label: @js(__('Pencapaian')), aktif: 'bg-emerald-600 ring-emerald-600 text-white shadow-lg shadow-emerald-600/25', ikonLembut: 'bg-emerald-50 text-emerald-600',
          ikon: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z' },
        { kunci: 'pelanggaran', label: @js(__('Pelanggaran')), aktif: 'bg-rose-600 ring-rose-600 text-white shadow-lg shadow-rose-600/25', ikonLembut: 'bg-rose-50 text-rose-600',
          ikon: 'M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.25-8.25-3.286zm0 13.036h.008v.008H12v-.008z' },
    ];
    const dua = (n) => String(n).padStart(2, '0');
    const kecil = (t) => (t || '').toString().toLowerCase();
    // Tanggal & jam dari HP guru (waktu setempat)
    const sekarang = () => {
        const d = new Date();
        return { tanggal: `${d.getFullYear()}-${dua(d.getMonth() + 1)}-${dua(d.getDate())}`, jam: `${dua(d.getHours())}:${dua(d.getMinutes())}` };
    };

    Vue.createApp({
        data: () => ({
            JENIS,
            siswa: @json($siswa),
            riwayat: @json($riwayat),
            cari: '',
            dipilih: [],
            form: { jenis: null, catatan: '', ...sekarang() },
            menyimpan: false,
            menghapus: null,
        }),
        computed: {
            siswaTersaring() {
                const k = kecil(this.cari.trim());
                return k ? this.siswa.filter((s) => kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || kecil(s.kelas).includes(k) || (s.arab || '').includes(this.cari.trim())) : this.siswa;
            },
            siswaTampil() { return this.siswaTersaring.slice(0, 40); },
            siswaDipilih() { return this.dipilih.map((id) => this.siswa.find((s) => s.siswa_id === id)).filter(Boolean); },
            labelJenis() { return JENIS.find((j) => j.kunci === this.form.jenis)?.label || ''; },
            // Nama hari mengikuti tanggal yang dipilih
            hari() {
                if (!this.form.tanggal) return '–';
                return new Date(this.form.tanggal + 'T12:00:00').toLocaleDateString('id-ID', { weekday: 'long' });
            },
        },
        methods: {
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
            pilihSiswa(s) {
                const i = this.dipilih.indexOf(s.siswa_id);
                i === -1 ? this.dipilih.push(s.siswa_id) : this.dipilih.splice(i, 1);
            },
            isiSekarang() { Object.assign(this.form, sekarang()); },

            async simpan() {
                if (!this.dipilih.length) return window.notifikasi?.peringatan(@js(__('Pilih minimal satu santri.')));
                if (!this.form.jenis) return window.notifikasi?.peringatan(@js(__('Pilih jenisnya: Pencapaian atau Pelanggaran.')));
                if (!this.form.catatan.trim()) return window.notifikasi?.peringatan(@js(__('Catatan wajib diisi.')));
                if (!this.form.tanggal) return window.notifikasi?.peringatan(@js(__('Tanggal wajib diisi.')));
                this.menyimpan = true;
                try {
                    const res = await fetch(SIMPAN, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ siswa: this.dipilih, jenis: this.form.jenis, catatan: this.form.catatan, tanggal: this.form.tanggal, jam: this.form.jam || null }),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const galat = json.errors ? Object.values(json.errors)[0][0] : null;
                        throw new Error(galat || json.message || @js(__('Gagal menyimpan.')));
                    }
                    this.riwayat = [...json.baris, ...this.riwayat].slice(0, 50);
                    window.notifikasi?.sukses(json.message);
                    // Siap untuk catatan berikutnya: santri & isi dikosongkan, jenis & waktu tetap
                    this.dipilih = [];
                    this.form.catatan = '';
                    this.cari = '';
                } catch (e) {
                    window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                } finally {
                    this.menyimpan = false;
                }
            },

            async hapus(b) {
                const tanya = @js(__('Hapus catatan :jenis untuk :nama?'));
                if (!confirm(tanya.replace(':jenis', b.label).replace(':nama', b.siswa))) return;
                this.menghapus = b.id;
                try {
                    const res = await fetch(HAPUS.replace('__ID__', b.nomor_id), {
                        method: 'DELETE',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || @js(__('Gagal menghapus.')));
                    this.riwayat = this.riwayat.filter((x) => x.id !== b.id);
                    window.notifikasi?.sukses(json.message);
                } catch (e) {
                    window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                } finally {
                    this.menghapus = null;
                }
            },
        },
    }).mount('#isi-catatan');
});
</script>
@endpush
