{{--
    Akun Asatid > Penilaian > Ujian Lisan (Asatid\PenilaianController@lisan).
    Urutan: 1) pilih santri  2) pilih fan yang diujikan (ujian aktif dari Admin > Penilaian > Ujian Lisan > Pengaturan)
            3) tanggal (otomatis hari ini WIB, bisa diubah)  4) isi nilai tiap pertanyaan (batasnya dari pengaturan admin) -> Simpan.
    Satu santri + ujian + tanggal = satu nilai: kalau sudah pernah dinilai, nilainya langsung muncul & tinggal diperbarui.
    Nilai langsung masuk ke Admin > Penilaian > Kelas & Pelajaran > Ujian Lisan. Di bawah: nilai terakhir yang diisi asatid ini.
    Vue: #isi-ujian-lisan.
--}}
@extends('layouts.asatid')

@section('judul', __('Ujian Lisan'))
@section('kembali', route('asatid.penilaian'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-violet-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
@endphp

<div id="isi-ujian-lisan" v-cloak class="space-y-5 pb-28">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-violet-400/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Penilaian') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Ujian Lisan') }}</h2>
                <p class="text-sm text-white/70">{{ __('Pilih santri, fan yang diujikan, tanggal, lalu isi nilai tiap pertanyaan.') }}</p>
            </div>
        </div>
    </section>

    @if (! count($ujian))
    <p class="p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada ujian lisan yang aktif. Minta admin mengaturnya di Penilaian') }} &rsaquo; {{ __('Kelas & Pelajaran') }} &rsaquo; {{ __('Ujian Lisan') }}.</p>
    @endif

    {{-- ===== 1. Santri ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span>{{ __('Pilih santri') }}</h3>
        <div v-if="santri" class="mt-3 flex items-center gap-3 rounded-2xl bg-violet-50 ring-1 ring-violet-200 p-3">
            <img v-if="santri.foto" :src="santri.foto" alt="" class="w-12 h-12 shrink-0 rounded-xl object-cover bg-slate-100">
            <span v-else class="w-12 h-12 shrink-0 rounded-xl bg-violet-600 text-white font-bold flex items-center justify-center" v-text="inisial(santri.siswa)"></span>
            <span class="flex-1 min-w-0">
                <span class="block font-bold text-slate-800 truncate" v-text="santri.siswa"></span>
                <span v-if="santri.arab" dir="rtl" class="block text-slate-600 truncate" v-text="santri.arab"></span>
                <span class="block text-xs text-slate-500 truncate"><span v-text="santri.nomor"></span><template v-if="santri.kelas"> · <span v-text="santri.kelas"></span></template></span>
            </span>
            <button type="button" @click="form.siswa_id = null" class="shrink-0 px-3 py-2 rounded-xl text-xs font-bold text-violet-700 hover:bg-violet-100">{{ __('Ganti') }}</button>
        </div>
        <template v-else>
            <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor / kelas…') }}" class="mt-3 {{ $input }}">
            <ul class="mt-2 max-h-80 overflow-y-auto divide-y divide-slate-100 -mx-1 px-1">
                <li v-for="s in siswaTampil" :key="s.siswa_id">
                    <button type="button" @click="pilihSiswa(s.siswa_id)" class="w-full flex items-center gap-3 py-2.5 text-left">
                        <img v-if="s.foto" :src="s.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                        <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-semibold text-slate-800 truncate" v-text="s.siswa"></span>
                            <span class="block text-xs text-slate-400 truncate"><span v-text="s.nomor"></span><template v-if="s.kelas"> · <span v-text="s.kelas"></span></template></span>
                        </span>
                    </button>
                </li>
                <li v-if="!siswaTampil.length" class="py-6 text-center text-sm text-slate-400">{{ __('Santri tidak ditemukan.') }}</li>
            </ul>
        </template>
    </section>

    {{-- ===== 2. Fan yang diujikan ===== --}}
    <section v-if="form.siswa_id" class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Pilih fan yang diujikan') }}</h3>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button v-for="u in ujian" :key="u.id" type="button" @click="pilihUjian(u.id)"
                class="w-full min-w-0 flex items-center gap-3 rounded-2xl px-4 py-3 text-left ring-1 transition-all"
                :class="form.ujian_lisan_id === u.id ? 'bg-violet-50 ring-2 ring-violet-500' : 'bg-[#f8fbfa] ring-slate-200 hover:bg-white'">
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold text-slate-800 break-words" v-text="u.fan || '-'"></span>
                    <span v-if="u.kitab" class="block text-sm text-slate-600 break-words" v-text="u.kitab"></span>
                    <span v-if="u.kitab_arab" dir="rtl" class="block text-slate-500 break-words" v-text="u.kitab_arab"></span>
                </span>
                <span class="shrink-0 text-right text-[11px] text-slate-400">
                    <span class="block" v-text="u.maks.length + ' ' + teksPertanyaan"></span>
                    <span class="block font-bold text-violet-700" v-text="teksMaks + ' ' + u.total_maks"></span>
                </span>
            </button>
        </div>
    </section>

    {{-- ===== 3. Tanggal ===== --}}
    <section v-if="form.siswa_id && ujianDipilih" class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">3</span>{{ __('Tanggal ujian') }}</h3>
        <input type="date" v-model="form.tanggal" @change="isiNilaiLama" class="mt-3 {{ $input }}">
        <p v-if="lama" class="mt-2 p-3 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-xs text-amber-900">
            {{ __('Santri ini sudah dinilai untuk ujian ini pada tanggal tersebut') }}<template v-if="lama.oleh"> ({{ __('oleh') }} <span v-text="lama.oleh"></span>)</template>.
            {{ __('Menyimpan akan memperbarui nilainya.') }}
        </p>
    </section>

    {{-- ===== 4. Nilai tiap pertanyaan ===== --}}
    <template v-if="form.siswa_id && ujianDipilih">
        <section class="{{ $kartu }} p-5">
            <h3 class="flex items-center gap-2 font-bold text-slate-800">
                <span class="{{ $nomor }}">4</span>{{ __('Nilai tiap pertanyaan') }}
                <span class="ml-auto rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-bold text-violet-700 ring-1 ring-violet-200" v-text="total + ' / ' + ujianDipilih.total_maks"></span>
            </h3>
            <div class="mt-3 space-y-3">
                <div v-for="(m, i) in ujianDipilih.maks" :key="i" class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 p-3">
                    <div class="flex items-center gap-3">
                        <span class="flex-1 font-semibold text-slate-700"><span v-text="teksPertanyaanKe + ' ' + (i + 1)"></span></span>
                        <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-bold text-violet-700 ring-1 ring-violet-200" v-text="teksMaks + ' ' + m"></span>
                        <input type="number" inputmode="numeric" min="0" :max="m" :value="form.nilai[i]" @input="atur(i, m, $event)" placeholder="0"
                            class="w-20 rounded-2xl bg-white ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-center text-lg font-extrabold text-slate-800 focus:ring-2 focus:ring-violet-500 outline-none tabular-nums">
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" @click="form.nilai[i] = 0" class="flex-1 rounded-xl py-1.5 text-xs font-bold ring-1"
                            :class="form.nilai[i] === 0 ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">0</button>
                        <button type="button" @click="form.nilai[i] = Math.round(m / 2)" class="flex-1 rounded-xl py-1.5 text-xs font-bold ring-1"
                            :class="form.nilai[i] === Math.round(m / 2) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">{{ __('Setengah') }} (<span v-text="Math.round(m / 2)"></span>)</button>
                        <button type="button" @click="form.nilai[i] = m" class="flex-1 rounded-xl py-1.5 text-xs font-bold ring-1"
                            :class="form.nilai[i] === m ? 'bg-emerald-500 text-white ring-emerald-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">{{ __('Nilai penuh') }} (<span v-text="m"></span>)</button>
                    </div>
                </div>
            </div>
        </section>

        <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

        {{-- Tombol simpan (menempel di bawah layar) --}}
        <div class="fixed inset-x-0 bottom-0 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 py-3">
            <div class="mx-auto max-w-6xl flex items-center gap-3">
                <p class="flex-1 min-w-0 text-xs text-slate-500 truncate">{{ __('Total') }}: <b class="text-slate-800" v-text="total + ' / ' + ujianDipilih.total_maks"></b></p>
                <button type="button" @click="simpan" :disabled="menyimpan"
                    class="shrink-0 px-5 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-extrabold shadow-lg disabled:opacity-50">
                    <span v-if="menyimpan">{{ __('Menyimpan…') }}</span>
                    <span v-else>{{ __('Simpan nilai') }}</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ===== Nilai yang sudah diisi asatid ini ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="font-bold text-slate-800">{{ __('Nilai yang Anda isi') }}</h3>
        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="n in riwayat" :key="n.id" class="py-2.5 flex items-center gap-3">
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold text-slate-800 truncate" v-text="n.siswa"></span>
                    <span class="block text-xs text-slate-400 truncate"><span v-text="n.fan || '-'"></span><template v-if="n.kitab"> · <span v-text="n.kitab"></span></template> · <span v-text="tglPendek(n.tanggal)"></span></span>
                </span>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="warna(n.persen)" v-text="n.total + '/' + n.total_maks"></span>
            </li>
            <li v-if="!riwayat.length" class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada nilai ujian lisan dari Anda.') }}</li>
        </ul>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const SIMPAN = @json(route('asatid.penilaian.lisan.simpan'));
    const CSRF = @json(csrf_token());
    const HARI_INI = @json($hariIni);
    const kecil = (t) => (t || '').toString().toLowerCase();

    Vue.createApp({
        data: () => ({
            siswa: @json($siswa),
            ujian: @json($ujian),
            sudah: @json($sudah),       // nilai yang sudah ada {siswa_id, ujian_id, tanggal, nilai, oleh}
            riwayat: @json($riwayat),
            teksPertanyaan: @js(__('pertanyaan')),
            teksPertanyaanKe: @js(__('Pertanyaan')),
            teksMaks: @js(__('maks')),
            form: { siswa_id: null, ujian_lisan_id: null, tanggal: HARI_INI, nilai: [] },
            cari: '',
            lama: null,
            menyimpan: false,
            galat: '',
        }),
        computed: {
            santri() { return this.siswa.find((s) => s.siswa_id === this.form.siswa_id) || null; },
            ujianDipilih() { return this.ujian.find((u) => u.id === this.form.ujian_lisan_id) || null; },
            siswaTampil() {
                const k = kecil(this.cari.trim());
                const hasil = k ? this.siswa.filter((s) => kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || kecil(s.kelas).includes(k)) : this.siswa;
                return hasil.slice(0, 80);
            },
            total() { return this.form.nilai.reduce((n, v) => n + (Number(v) || 0), 0); },
        },
        methods: {
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
            tglPendek: (t) => (t ? new Date(t + 'T12:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '–'),
            warna(p) {
                if (p >= 80) return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                if (p >= 60) return 'bg-amber-50 text-amber-700 ring-amber-200';
                return 'bg-rose-50 text-rose-700 ring-rose-200';
            },
            pilihSiswa(id) { this.form.siswa_id = id; this.cari = ''; this.isiNilaiLama(); },
            pilihUjian(id) { this.form.ujian_lisan_id = id; this.isiNilaiLama(); },
            // Santri + ujian + tanggal ini sudah dinilai? Nilainya dimunculkan, tinggal diperbarui
            isiNilaiLama() {
                this.galat = '';
                const u = this.ujianDipilih;
                if (!u) { this.form.nilai = []; this.lama = null; return; }
                this.lama = this.sudah.find((n) => n.siswa_id === this.form.siswa_id && n.ujian_id === u.id && n.tanggal === this.form.tanggal) || null;
                this.form.nilai = u.maks.map((m, i) => (this.lama && this.lama.nilai[i] !== undefined ? Math.min(this.lama.nilai[i], m) : ''));
            },
            // Angka di luar 0..maks langsung dikembalikan ke batasnya
            atur(i, m, e) {
                if (e.target.value === '') { this.form.nilai[i] = ''; return; }
                let v = parseInt(e.target.value, 10);
                if (Number.isNaN(v)) v = 0;
                v = Math.max(0, Math.min(m, v));
                this.form.nilai[i] = v;
                if (String(e.target.value) !== String(v)) e.target.value = v;
            },
            async simpan() {
                this.galat = '';
                const kosong = this.form.nilai.findIndex((v) => v === '' || v === null);
                if (kosong >= 0) {
                    this.galat = @js(__('Nilai pertanyaan :n belum diisi.')).replace(':n', kosong + 1);
                    return;
                }
                this.menyimpan = true;
                try {
                    const res = await fetch(SIMPAN, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(this.form),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const galat = json.errors ? Object.values(json.errors)[0][0] : null;
                        throw new Error(galat || json.message || @js(__('Gagal menyimpan.')));
                    }
                    const b = json.baris;
                    this.sudah = [...this.sudah.filter((n) => !(n.siswa_id === b.siswa_id && n.ujian_id === b.ujian_id && n.tanggal === b.tanggal)),
                        { siswa_id: b.siswa_id, ujian_id: b.ujian_id, tanggal: b.tanggal, nilai: b.nilai, oleh: b.oleh }];
                    this.riwayat = [b, ...this.riwayat.filter((n) => n.id !== b.id)];
                    window.notifikasi?.sukses(json.message);
                    // Siap untuk santri berikutnya (fan & tanggal tetap)
                    this.form.siswa_id = null;
                    this.isiNilaiLama();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.menyimpan = false;
                }
            },
        },
    }).mount('#isi-ujian-lisan');
});
</script>
@endpush
