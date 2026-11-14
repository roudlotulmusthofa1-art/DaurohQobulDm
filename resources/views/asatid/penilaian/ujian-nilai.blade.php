{{--
    Akun Asatid > Penilaian > Nilai Ujian (Asatid\UjianController@nilai).
    Urutan: 1) pilih ujian & kitab: tombolnya = kelompok kitab dari Pengaturan Ujian admin (per kitab / seluruh fan / kelompok),
               SAMA dengan pilihan di Buat Soal  2) pilih halaqoh (daftar santri dimuat dari asatid.penilaian.ujian.nilai.peserta)
            3) tanggal ujian (otomatis hari ini WIB)  4) tulis nilai tiap santri (satu angka 0..nilai maksimal) -> Simpan.
    Satu santri + ujian + kelompok = satu nilai: yang sudah dinilai langsung muncul & tinggal diperbarui.
    Kotak yang dikosongkan tidak disimpan. Nilai langsung masuk ke Admin > Penilaian > Kelas & Pelajaran > Ujian.
    Vue: #nilai-ujian.
--}}
@extends('layouts.asatid')

@section('judul', __('Nilai Ujian'))
@section('kembali', route('asatid.penilaian'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-orange-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
@endphp

<div id="nilai-ujian" v-cloak class="space-y-5 pb-28">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-orange-400/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Ujian') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Nilai Ujian') }}</h2>
                <p class="text-sm text-white/70">{{ __('Pilih ujian & kitab, halaqoh, lalu tulis nilai tiap santri.') }}</p>
            </div>
        </div>
        <a href="{{ route('asatid.soal-ujian') }}" class="relative mt-4 inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/20 px-3.5 py-1.5 text-xs font-bold hover:bg-white/20">
            {{ __('Buat soal ujian') }} &rsaquo;
        </a>
    </section>

    @if (! count($ujian))
    <p class="p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada ujian yang aktif. Minta admin mengaturnya di Penilaian') }} &rsaquo; {{ __('Kelas & Pelajaran') }} &rsaquo; {{ __('Ujian') }}.</p>
    @endif

    {{-- ===== 1. Ujian & kitab ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span>{{ __('Ujian & kitab') }}</h3>
        <div class="mt-3 space-y-3">
            <div v-for="u in ujian" :key="u.id" class="rounded-2xl ring-1 p-3" :class="form.ujian_id === u.id ? 'ring-orange-300 bg-orange-50/50' : 'ring-slate-200'">
                <p class="text-sm font-bold text-slate-700" v-text="u.judul"></p>
                <p class="text-xs text-slate-400">{{ __('Nilai maksimal') }} <span v-text="u.nilai_maks"></span></p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="g in u.kelompok" :key="g.kunci" type="button" @click="pilihKelompok(u.id, g)"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="form.ujian_id === u.id && form.kelompok === g.kunci ? 'bg-orange-500 text-white ring-orange-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        v-text="g.nama"></button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 2. Halaqoh ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Halaqoh') }}</h3>
        <div class="mt-3 flex flex-wrap gap-2">
            <button v-for="k in kelas" :key="k.id" type="button" @click="pilihKelas(k.id)"
                class="px-3.5 py-2 rounded-2xl text-sm font-semibold ring-1 transition"
                :class="form.kelas_id === k.id ? 'bg-orange-500 text-white ring-orange-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-text="k.nama"></span> <span class="opacity-70 text-xs" v-text="'(' + k.santri_count + ')'"></span>
            </button>
        </div>
    </section>

    {{-- ===== 3. Tanggal ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">3</span>{{ __('Tanggal ujian') }}</h3>
        <input type="date" v-model="form.tanggal" class="mt-3 {{ $input }}">
    </section>

    {{-- ===== 4. Nilai tiap santri ===== --}}
    <section v-if="siap" class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800">
            <span class="{{ $nomor }}">4</span>{{ __('Nilai santri') }}
            <span class="ml-auto rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-bold text-orange-700 ring-1 ring-orange-200" v-text="teks.maks + ' ' + ujianDipilih.nilai_maks"></span>
        </h3>
        <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor…') }}" class="mt-3 {{ $input }}">
        <p v-if="memuat" class="py-8 text-center text-sm text-slate-400">{{ __('Memuat santri…') }}</p>
        <ul v-else class="mt-2 divide-y divide-slate-100">
            <li v-for="s in pesertaTampil" :key="s.id" class="py-2.5 flex items-center gap-3">
                <img v-if="s.foto" :src="s.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(s.nama)"></span>
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold text-slate-800 truncate" v-text="s.nama"></span>
                    <span v-if="s.arab" dir="rtl" class="block text-sm text-slate-500 truncate" v-text="s.arab"></span>
                    <span v-if="s.oleh" class="block text-[11px] text-emerald-700 truncate" v-text="teks.sudah.replace(':nilai', s.nilai).replace(':nama', s.oleh)"></span>
                </span>
                <input type="number" inputmode="numeric" min="0" :max="ujianDipilih.nilai_maks" :value="isian[s.id]" @input="atur(s.id, $event)" placeholder="–"
                    class="w-20 shrink-0 rounded-2xl bg-white ring-1 border-0 px-2 py-2.5 text-center text-lg font-extrabold text-slate-800 focus:ring-2 focus:ring-orange-500 outline-none tabular-nums"
                    :class="isian[s.id] !== '' && isian[s.id] !== s.nilai ? 'ring-orange-400 bg-orange-50' : 'ring-slate-200'">
            </li>
            <li v-if="!pesertaTampil.length" class="py-8 text-center text-sm text-slate-400">{{ __('Tidak ada santri di halaqoh ini.') }}</li>
        </ul>
    </section>
    <p v-else class="p-4 rounded-2xl bg-slate-50 ring-1 ring-slate-200 text-sm text-slate-500">{{ __('Pilih ujian & kitab serta halaqoh dulu, lalu daftar santri akan muncul.') }}</p>

    <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

    {{-- Tombol simpan (menempel di bawah layar) --}}
    <div v-if="siap" class="fixed inset-x-0 bottom-0 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 py-3">
        <div class="mx-auto max-w-6xl flex items-center gap-3">
            <p class="flex-1 min-w-0 text-xs text-slate-500 truncate">
                <b class="text-slate-800" v-text="jumlahTerisi"></b> {{ __('nilai terisi') }} · <b class="text-orange-700" v-text="jumlahBerubah"></b> {{ __('baru / berubah') }}
            </p>
            <button type="button" @click="simpan" :disabled="menyimpan || !jumlahBerubah"
                class="shrink-0 px-5 py-3 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-extrabold shadow-lg disabled:opacity-50">
                <span v-if="menyimpan">{{ __('Menyimpan…') }}</span>
                <span v-else>{{ __('Simpan nilai') }}</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const RUTE = {
        peserta: @json(route('asatid.penilaian.ujian.nilai.peserta')),
        simpan: @json(route('asatid.penilaian.ujian.nilai.simpan')),
    };
    const CSRF = @json(csrf_token());
    const kecil = (t) => (t || '').toString().toLowerCase();

    Vue.createApp({
        data: () => ({
            ujian: @json($ujian),
            kelas: @json($kelasList),
            teks: {
                maks: @js(__('Nilai maks')),
                sudah: @js(__('Sudah dinilai :nilai oleh :nama')),
            },
            form: { ujian_id: null, kitab_ids: [], kelompok: '', kelas_id: null, tanggal: @json($hariIni) },
            peserta: [],
            isian: {},       // siswa_id => angka atau ''
            cari: '',
            memuat: false,
            menyimpan: false,
            galat: '',
        }),
        computed: {
            ujianDipilih() { return this.ujian.find((u) => u.id === this.form.ujian_id) || null; },
            siap() { return !!(this.ujianDipilih && this.form.kitab_ids.length && this.form.kelas_id); },
            pesertaTampil() {
                const k = kecil(this.cari.trim());
                return k ? this.peserta.filter((s) => kecil(s.nama).includes(k) || kecil(s.nomor).includes(k) || kecil(s.arab).includes(k)) : this.peserta;
            },
            jumlahTerisi() { return Object.values(this.isian).filter((v) => v !== '').length; },
            // Yang baru diisi / berbeda dari nilai tersimpan
            berubah() { return this.peserta.filter((s) => this.isian[s.id] !== '' && this.isian[s.id] !== s.nilai); },
            jumlahBerubah() { return this.berubah.length; },
        },
        methods: {
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
            // Kelompok kitab dari pengaturan admin (sama seperti di Buat Soal)
            pilihKelompok(ujianId, g) {
                if (this.jumlahBerubah && !confirm(@js(__('Nilai yang belum disimpan akan hilang. Lanjutkan?')))) return;
                this.form.ujian_id = ujianId;
                this.form.kitab_ids = [...g.kitab_ids];
                this.form.kelompok = g.kunci;
                this.muat();
            },
            pilihKelas(id) {
                if (this.jumlahBerubah && !confirm(@js(__('Nilai yang belum disimpan akan hilang. Lanjutkan?')))) return;
                this.form.kelas_id = id;
                this.muat();
            },
            async muat() {
                if (!this.siap) return;
                this.memuat = true;
                this.galat = '';
                try {
                    const q = new URLSearchParams({ ujian_id: this.form.ujian_id, kelas_id: this.form.kelas_id });
                    this.form.kitab_ids.forEach((id) => q.append('kitab_ids[]', id));
                    const res = await fetch(RUTE.peserta + '?' + q, { headers: { Accept: 'application/json' } });
                    if (!res.ok) throw new Error(@js(__('Gagal memuat santri. Coba lagi.')));
                    this.peserta = (await res.json()).peserta;
                    this.isian = Object.fromEntries(this.peserta.map((s) => [s.id, s.nilai === null ? '' : s.nilai]));
                } catch (e) {
                    this.galat = e.message;
                    this.peserta = [];
                } finally {
                    this.memuat = false;
                }
            },
            // Angka di luar 0..maks langsung dikembalikan ke batasnya
            atur(id, e) {
                if (e.target.value === '') { this.isian[id] = ''; return; }
                let v = parseInt(e.target.value, 10);
                if (Number.isNaN(v)) v = 0;
                v = Math.max(0, Math.min(this.ujianDipilih.nilai_maks, v));
                this.isian[id] = v;
                if (String(e.target.value) !== String(v)) e.target.value = v;
            },
            async simpan() {
                this.galat = '';
                this.menyimpan = true;
                try {
                    const res = await fetch(RUTE.simpan, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ ...this.form, nilai: Object.fromEntries(this.berubah.map((s) => [s.id, this.isian[s.id]])) }),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || @js(__('Gagal menyimpan. Coba lagi.')));
                    window.notifikasi?.sukses(json.message);
                    await this.muat(); // tampilkan nilai tersimpan + "sudah dinilai oleh"
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.menyimpan = false;
                }
            },
        },
    }).mount('#nilai-ujian');
});
</script>
@endpush
