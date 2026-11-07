{{--
    Akun Asatid > Penilaian > Nilai Harian (Asatid\PenilaianController@harian).
    Urutan: 1) pilih kelas  2) pilih kitab (+ tanggal)  3) pilih santri  4) isi 6 penilaian -> Simpan.
    Batas tiap penilaian dari App\Models\NilaiHarian::ASPEK (total 100); angka di atas batas otomatis diturunkan ke batasnya
    (server juga menolak nilai di atas batas). Satu santri + kitab + tanggal = satu nilai (dinilai lagi = diperbarui).
    Bisa dibuka dari tombol "Nilai Harian" di absensi kelas: ?kelas_id=&siswa_id=&kitab_id=&tanggal=
    Vue: #isi-nilai-harian.
--}}
@extends('layouts.asatid')

@section('judul', __('Nilai Harian'))
@section('kembali', route('asatid.penilaian'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-sky-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
@endphp

<div id="isi-nilai-harian" v-cloak class="space-y-5 pb-28">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-sky-400/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Penilaian') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Nilai Harian') }}</h2>
                <p class="text-sm text-white/70">{{ __('Pilih kelas, kitab, lalu santri. Total nilai maksimal 100.') }}</p>
            </div>
        </div>
    </section>

    {{-- ================= TAHAP PILIH (kelas, kitab, santri) ================= --}}
    <template v-if="!santri">
        {{-- ===== 1. Kelas ===== --}}
        <section class="{{ $kartu }} p-5">
            <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span>{{ __('Pilih kelas') }}</h3>
            <div v-if="kelasList.length" class="mt-3 flex flex-wrap gap-2">
                <button v-for="k in kelasList" :key="k.id" type="button" @click="pilihKelas(k.id)"
                    class="rounded-2xl px-4 py-2.5 text-sm font-semibold ring-1 transition-all"
                    :class="form.kelas_id === k.id ? 'bg-[#0b332c] text-white ring-[#0b332c] shadow-md' : 'bg-[#f8fbfa] text-slate-700 ring-slate-200 hover:bg-white'">
                    <span v-text="k.nama"></span>
                    <span class="ml-1 text-xs opacity-60" v-text="'(' + k.santri_count + ')'"></span>
                </button>
            </div>
            <p v-else class="mt-3 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada kelas. Minta admin mengisinya di Master Data') }} &rsaquo; {{ __('Kelas.') }}</p>
        </section>

        {{-- ===== 2. Kitab & tanggal ===== --}}
        <section v-if="form.kelas_id" class="{{ $kartu }} p-5">
            <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Pilih kitab') }}</h3>
            <div v-if="kitabUrut.length" class="mt-3 grid sm:grid-cols-2 gap-2">
                <button v-for="k in kitabUrut" :key="k.id" type="button" @click="pilihKitab(k.id)"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-left ring-1 transition-all"
                    :class="form.kitab_id === k.id ? 'bg-sky-50 ring-2 ring-sky-500' : 'bg-[#f8fbfa] ring-slate-200 hover:bg-white'">
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="k.nama"></span>
                        <span v-if="k.arab" dir="rtl" class="block text-slate-600 truncate" v-text="k.arab"></span>
                        <span v-if="k.bidang" class="block text-xs text-slate-400 truncate" v-text="k.bidang"></span>
                    </span>
                    <span v-if="dijadwal(k.id)" class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200">{{ __('Terjadwal') }}</span>
                </button>
            </div>
            <p v-else class="mt-3 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada kitab untuk bulan ini. Minta admin mengisinya di Kurikulum & Kitab.') }}</p>

            <label class="mt-4 block">
                <span class="text-xs font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                <input type="date" v-model="form.tanggal" @change="muatPeserta" class="mt-1 {{ $input }}">
                <span class="mt-1 block text-xs font-semibold text-slate-500">{{ __('Hari') }}: <b class="text-slate-800" v-text="hari"></b></span>
            </label>
        </section>

        {{-- ===== 3. Santri ===== --}}
        <section v-if="form.kelas_id && form.kitab_id" class="{{ $kartu }} p-5">
            <h3 class="flex items-center gap-2 font-bold text-slate-800">
                <span class="{{ $nomor }}">3</span>{{ __('Pilih santri') }}
                <span v-if="peserta.length" class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                    <span v-text="sudahDinilai"></span>/<span v-text="peserta.length"></span> {{ __('dinilai') }}
                </span>
            </h3>
            <p v-if="memuat" class="py-6 text-center text-sm text-slate-400">{{ __('Memuat santri…') }}</p>
            <template v-else>
                <ul class="mt-2 divide-y divide-slate-100">
                    <li v-for="p in peserta" :key="p.id">
                        <button type="button" @click="buka(p)" class="w-full flex items-center gap-3 py-2.5 text-left">
                            <img v-if="p.foto" :src="p.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                            <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(p.nama)"></span>
                            <span class="flex-1 min-w-0">
                                <span class="block font-semibold text-slate-800 truncate" v-text="p.nama"></span>
                                <span class="block text-xs text-slate-400 truncate" v-text="p.oleh ? @js(__('Dinilai')) + ' · ' + p.oleh : p.nomor"></span>
                            </span>
                            <span v-if="p.total !== null && p.total !== undefined" class="shrink-0 min-w-[3rem] rounded-xl px-2.5 py-1.5 text-center text-sm font-extrabold tabular-nums ring-1" :class="warnaTotal(p.total)" v-text="p.total"></span>
                            <span v-else class="shrink-0 rounded-xl bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-400 ring-1 ring-slate-200">{{ __('Belum') }}</span>
                        </button>
                    </li>
                </ul>
                <p v-if="!peserta.length" class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada santri di kelas ini.') }}</p>
            </template>
            <p v-if="galat" class="mt-3 p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>
        </section>
    </template>

    {{-- ================= TAHAP NILAI (6 penilaian untuk satu santri) ================= --}}
    <template v-else>
        <section class="{{ $kartu }} p-5 flex items-center gap-4">
            <img v-if="santri.foto" :src="santri.foto" alt="" class="w-16 h-16 shrink-0 rounded-2xl object-cover bg-slate-100">
            <span v-else class="w-16 h-16 shrink-0 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white text-2xl font-extrabold flex items-center justify-center" v-text="inisial(santri.nama)"></span>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-extrabold text-slate-800 truncate" v-text="santri.nama"></h3>
                <p v-if="santri.arab" dir="rtl" class="text-slate-600 truncate" v-text="santri.arab"></p>
                <p class="text-xs text-slate-400 truncate" v-text="judulSesi"></p>
            </div>
            <div class="shrink-0 text-center">
                <span class="block text-3xl font-extrabold tabular-nums" :class="warnaTotal(total).split(' ')[1]" v-text="total"></span>
                <span class="block text-[10px] text-slate-400">{{ __('dari 100') }}</span>
            </div>
        </section>

        <section v-for="(a, i) in aspek" :key="a.kunci" class="{{ $kartu }} p-5">
            <div class="flex items-start gap-3">
                <span class="{{ $nomor }} mt-0.5 shrink-0" v-text="i + 1"></span>
                <h4 class="flex-1 font-bold text-slate-800 leading-snug" v-text="a.label"></h4>
                <span class="shrink-0 rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-bold text-sky-700 ring-1 ring-sky-200">{{ __('maks.') }} <span v-text="a.maks"></span></span>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <input type="range" min="0" :max="a.maks" step="1" :value="nilai[a.kunci]" @input="atur(a, $event)"
                    class="flex-1 accent-sky-600 h-2">
                <input type="number" inputmode="numeric" min="0" :max="a.maks" :value="nilai[a.kunci]" @input="atur(a, $event)"
                    class="w-20 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-center text-lg font-extrabold text-slate-800 focus:ring-2 focus:ring-sky-500 outline-none tabular-nums">
            </div>
            <div class="mt-3 flex gap-2">
                <button type="button" @click="nilai[a.kunci] = 0" class="flex-1 rounded-xl py-2 text-xs font-bold ring-1"
                    :class="nilai[a.kunci] === 0 ? 'bg-rose-500 text-white ring-rose-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">0</button>
                <button type="button" @click="nilai[a.kunci] = Math.round(a.maks / 2)" class="flex-1 rounded-xl py-2 text-xs font-bold ring-1"
                    :class="nilai[a.kunci] === Math.round(a.maks / 2) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">{{ __('Setengah') }} (<span v-text="Math.round(a.maks / 2)"></span>)</button>
                <button type="button" @click="nilai[a.kunci] = a.maks" class="flex-1 rounded-xl py-2 text-xs font-bold ring-1"
                    :class="nilai[a.kunci] === a.maks ? 'bg-emerald-500 text-white ring-emerald-500' : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50'">{{ __('Nilai penuh') }} (<span v-text="a.maks"></span>)</button>
            </div>
        </section>

        <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

        {{-- Tombol simpan (menempel di bawah layar) --}}
        <div class="fixed inset-x-0 bottom-0 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 py-3">
            <div class="mx-auto max-w-6xl flex items-center gap-3">
                <button type="button" @click="tutup" class="shrink-0 px-4 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Kembali') }}</button>
                <p class="flex-1 min-w-0 text-xs text-slate-500 truncate">{{ __('Total') }}: <b class="text-slate-800" v-text="total + ' / 100'"></b></p>
                <button type="button" @click="simpan" :disabled="menyimpan"
                    class="shrink-0 px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-extrabold shadow-lg disabled:opacity-50">
                    <span v-if="menyimpan">{{ __('Menyimpan…') }}</span>
                    <span v-else>{{ __('Simpan nilai') }}</span>
                </button>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const PESERTA = @json(route('asatid.penilaian.harian.peserta'));
    const SIMPAN = @json(route('asatid.penilaian.harian.simpan'));
    const CSRF = @json(csrf_token());
    const ASPEK = @json($aspek);
    const awal = @json($awal);
    const angka = (v) => (v ? Number(v) : null);
    // Tanggal hari ini dari HP guru (waktu setempat)
    const hariIni = () => { const d = new Date(); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10); };
    const kosong = () => Object.fromEntries(ASPEK.map((a) => [a.kunci, 0]));

    Vue.createApp({
        data: () => ({
            aspek: ASPEK,
            kelasList: @json($kelasList),
            kitabList: @json($kitabList),
            terjadwal: @json($terjadwal),
            form: { kelas_id: angka(awal.kelas_id), kitab_id: null, tanggal: awal.tanggal || hariIni() },
            peserta: [],
            santri: null,          // santri yang sedang dinilai
            nilai: kosong(),
            bukaSiswa: angka(awal.siswa_id), // dari absensi: langsung buka santri ini
            memuat: false,
            menyimpan: false,
            galat: '',
        }),
        computed: {
            // Kitab yang terjadwal di kelas ini (dan sesuai pelajaran dari absensi) ditaruh paling atas
            kitabUrut() {
                const bidang = angka(awal.bidang_ilmu_id);
                const skor = (k) => (this.dijadwal(k.id) ? 2 : 0) + (bidang && k.bidang_ilmu_id === bidang ? 1 : 0);
                return [...this.kitabList].sort((a, b) => skor(b) - skor(a));
            },
            total() { return ASPEK.reduce((n, a) => n + (Number(this.nilai[a.kunci]) || 0), 0); },
            sudahDinilai() { return this.peserta.filter((p) => p.total !== null && p.total !== undefined).length; },
            hari() {
                if (!this.form.tanggal) return '–';
                return new Date(this.form.tanggal + 'T12:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            },
            judulSesi() {
                const cari = (list, id) => (list.find((x) => x.id === id) || {}).nama || '-';
                return `${cari(this.kelasList, this.form.kelas_id)} · ${cari(this.kitabList, this.form.kitab_id)} · ${this.hari}`;
            },
        },
        mounted() {
            // Dari absensi kelas: kitabnya ikut dibawa, langsung dipilih
            if (this.form.kelas_id && angka(awal.kitab_id)) {
                this.pilihKitab(angka(awal.kitab_id));
            } else if (this.form.kelas_id && awal.bidang_ilmu_id) {
                // (tautan lama: hanya pelajaran) kalau hanya ada satu kitab untuk pelajaran itu, langsung dipilih
                const cocok = this.kitabList.filter((k) => k.bidang_ilmu_id === angka(awal.bidang_ilmu_id));
                if (cocok.length === 1) this.pilihKitab(cocok[0].id);
            }
        },
        methods: {
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
            dijadwal(id) { return (this.terjadwal[this.form.kelas_id] || []).includes(id); },
            warnaTotal(t) {
                if (t >= 80) return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                if (t >= 60) return 'bg-amber-50 text-amber-700 ring-amber-200';
                return 'bg-rose-50 text-rose-700 ring-rose-200';
            },
            pilihKelas(id) {
                if (this.form.kelas_id === id) return;
                this.form.kelas_id = id;
                this.form.kitab_id = null;
                this.peserta = [];
            },
            pilihKitab(id) {
                this.form.kitab_id = id;
                this.muatPeserta();
            },
            async muatPeserta() {
                if (!this.form.kelas_id || !this.form.kitab_id || !this.form.tanggal) return;
                this.galat = '';
                this.memuat = true;
                try {
                    const url = new URL(PESERTA);
                    Object.entries(this.form).forEach(([k, v]) => url.searchParams.set(k, v));
                    const res = await fetch(url, { headers: { Accept: 'application/json' } });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || @js(__('Gagal memuat santri.')));
                    this.peserta = json.peserta;
                    // Dari absensi: langsung buka santri yang dipilih (sekali saja)
                    if (this.bukaSiswa) {
                        const p = this.peserta.find((x) => x.id === this.bukaSiswa);
                        this.bukaSiswa = null;
                        if (p) this.buka(p);
                    }
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },
            buka(p) {
                this.galat = '';
                this.santri = p;
                this.nilai = p.nilai ? { ...p.nilai } : kosong();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            tutup() {
                this.santri = null;
                this.galat = '';
            },
            // Angka di luar 0..maks langsung dikembalikan ke batasnya
            atur(a, e) {
                let v = parseInt(e.target.value, 10);
                if (Number.isNaN(v)) v = 0;
                v = Math.max(0, Math.min(a.maks, v));
                this.nilai[a.kunci] = v;
                if (String(e.target.value) !== String(v) && e.target.value !== '') e.target.value = v;
            },
            async simpan() {
                this.galat = '';
                this.menyimpan = true;
                try {
                    const res = await fetch(SIMPAN, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ ...this.form, siswa_id: this.santri.id, ...this.nilai }),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const galat = json.errors ? Object.values(json.errors)[0][0] : null;
                        throw new Error(galat || json.message || @js(__('Gagal menyimpan.')));
                    }
                    Object.assign(this.santri, { nilai: { ...this.nilai }, total: json.total, oleh: json.oleh });
                    window.notifikasi?.sukses(json.message);
                    this.tutup();
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.menyimpan = false;
                }
            },
        },
    }).mount('#isi-nilai-harian');
});
</script>
@endpush
