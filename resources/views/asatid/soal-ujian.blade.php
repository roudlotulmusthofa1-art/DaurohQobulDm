{{--
    Akun Asatid > Soal Ujian (menu di beranda; Asatid\UjianController@soal). Dua tab (alamat ?tab=buat|disetujui):
    ===== Tab 1 "Buat Soal" =====
    Urutan: 1) pilih halaqoh (boleh beberapa)  2) pilih ujian & kitab: tombolnya = kelompok kitab dari Pengaturan Ujian admin (per kitab / seluruh fan / kelompok)
            3) tulis pertanyaan, jawaban, dan nilai tiap pertanyaan -> Kirim ke admin.
    Di bawah: "Soal saya". Menunggu = boleh diubah / dihapus. Disetujui = tampil versi koreksi admin
    (butir yang diubah admin diberi tanda) + catatan admin.
    ===== Tab 2 "Soal Disetujui" =====
    Semua soal dari seluruh asatid yang sudah disetujui admin (hanya dibaca): saringan ujian + cari, "Lihat soal" = pertanyaan, jawaban, nilai.
    Vue: #buat-soal-ujian.
--}}
@extends('layouts.asatid')

@section('judul', __('Soal Ujian'))
@section('kembali', route('asatid.dashboard'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-amber-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
@endphp

<div id="buat-soal-ujian" v-cloak class="space-y-5 pb-28">
    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-amber-400/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Ujian') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Soal Ujian') }}</h2>
                <p class="text-sm text-white/70">{{ __('Buat soal untuk diperiksa admin, dan lihat semua soal yang sudah disetujui.') }}</p>
            </div>
        </div>
        <a href="{{ route('asatid.penilaian.ujian.nilai') }}" class="relative mt-4 inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/20 px-3.5 py-1.5 text-xs font-bold hover:bg-white/20">
            {{ __('Isi nilai ujian') }} &rsaquo;
        </a>
    </section>

    {{-- ===== Tombol tab ===== --}}
    <div class="grid grid-cols-2 gap-3">
        <button type="button" @click="pilihTab('buat')" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold ring-1 transition"
            :class="tab === 'buat' ? 'bg-[#0b332c] text-white ring-[#0b332c] shadow-md shadow-[#0b332c]/25' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            {{ __('Buat Soal') }}
        </button>
        <button type="button" @click="pilihTab('disetujui')" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold ring-1 transition"
            :class="tab === 'disetujui' ? 'bg-[#0b332c] text-white ring-[#0b332c] shadow-md shadow-[#0b332c]/25' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('Soal Disetujui') }}
            <span class="rounded-full px-2 py-0.5 text-[11px]" :class="tab === 'disetujui' ? 'bg-white/20' : 'bg-slate-100'" v-text="soalDisetujui.length"></span>
        </button>
    </div>

    {{-- ================= Tab 1: Buat Soal ================= --}}
    <div v-show="tab === 'buat'" @if ($tab !== 'buat') style="display: none" @endif class="space-y-5">
    @if (! count($ujian))
    <p class="p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada ujian yang aktif. Minta admin mengaturnya di Penilaian') }} &rsaquo; {{ __('Kelas & Pelajaran') }} &rsaquo; {{ __('Ujian') }}.</p>
    @endif

    <p v-if="form.id" class="p-3.5 rounded-2xl bg-sky-50 ring-1 ring-sky-200 text-sm text-sky-900 flex items-center gap-3">
        <span class="flex-1">{{ __('Anda sedang mengubah soal yang sudah dikirim. Setelah disimpan, soal dikirim ulang ke admin.') }}</span>
        <button type="button" @click="baru" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold text-sky-800 ring-1 ring-sky-300 hover:bg-sky-100">{{ __('Batal ubah') }}</button>
    </p>

    {{-- ===== 1. Halaqoh ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span>{{ __('Halaqoh yang diujikan') }} <span class="text-xs font-normal text-slate-400">({{ __('boleh lebih dari satu') }})</span></h3>
        <div class="mt-3 flex flex-wrap gap-2">
            <button v-for="k in kelas" :key="k.id" type="button" @click="pilihKelas(k.id)"
                class="px-3.5 py-2 rounded-2xl text-sm font-semibold ring-1 transition"
                :class="form.kelas_ids.includes(k.id) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-text="k.nama"></span> <span class="opacity-70 text-xs" v-text="'(' + k.santri_count + ')'"></span>
            </button>
        </div>
    </section>

    {{-- ===== 2. Ujian & kitab ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Ujian & kitab') }}</h3>
        <div class="mt-3 space-y-3">
            <div v-for="u in ujian" :key="u.id" class="rounded-2xl ring-1 p-3" :class="form.ujian_id === u.id ? 'ring-amber-300 bg-amber-50/50' : 'ring-slate-200'">
                <p class="text-sm font-bold text-slate-700" v-text="u.judul"></p>
                <p class="text-xs text-slate-400">{{ __('Nilai maksimal') }} <span v-text="u.nilai_maks"></span></p>
                {{-- Pilihan kitab mengikuti cara pembuatan soal dari admin (per kitab / seluruh fan / kelompok) --}}
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="g in u.kelompok" :key="g.kitab_ids.join('-')" type="button" @click="pilihKelompok(u.id, g)"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="terpilih(u.id, g) ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        v-text="g.nama"></button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 3. Pertanyaan, jawaban, nilai ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800">
            <span class="{{ $nomor }}">3</span>{{ __('Pertanyaan & jawaban') }}
            <span class="ml-auto rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-amber-200" v-text="teks.total + ' ' + total + (ujianDipilih ? ' / ' + ujianDipilih.nilai_maks : '')"></span>
        </h3>
        <p v-if="ujianDipilih && total !== ujianDipilih.nilai_maks" class="mt-2 text-xs text-amber-700" v-text="teks.beda.replace(':total', total).replace(':maks', ujianDipilih.nilai_maks)"></p>
        <div class="mt-3 space-y-3">
            <div v-for="(b, i) in form.butir" :key="b.kunci" class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 p-3 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 shrink-0 rounded-xl bg-[#0b332c] text-white text-sm font-extrabold flex items-center justify-center" v-text="i + 1"></span>
                    <label class="ml-auto flex items-center gap-1.5 text-xs font-semibold text-slate-500">{{ __('Nilai') }}
                        <input type="number" inputmode="numeric" min="1" max="{{ \App\Models\Ujian::MAKS_NILAI }}" v-model.number="b.nilai"
                            class="w-20 rounded-xl bg-white ring-1 ring-slate-200/80 border-0 px-2 py-2 text-center text-base font-extrabold text-slate-800 focus:ring-2 focus:ring-amber-500 outline-none"></label>
                    <button type="button" @click="hapusButir(i)" :disabled="form.butir.length === 1" aria-label="{{ __('Hapus pertanyaan') }}"
                        class="w-9 h-9 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <textarea v-model="b.pertanyaan" rows="2" maxlength="5000" dir="auto" placeholder="{{ __('Tulis pertanyaan…') }}" class="{{ $input }}"></textarea>
                <textarea v-model="b.jawaban" rows="3" maxlength="10000" dir="auto" placeholder="{{ __('Tulis jawabannya…') }}" class="{{ $input }}"></textarea>
            </div>
        </div>
        <button type="button" @click="tambahButir" class="mt-3 w-full py-3 rounded-2xl border-2 border-dashed border-amber-300 text-sm font-bold text-amber-700 hover:bg-amber-50">
            + {{ __('Tambah pertanyaan') }}
        </button>
    </section>

    <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

    {{-- Tombol kirim (menempel di bawah layar) --}}
    <div v-if="tab === 'buat'" class="fixed inset-x-0 bottom-0 z-30 bg-white/90 backdrop-blur border-t border-slate-200 px-4 py-3">
        <div class="mx-auto max-w-6xl flex items-center gap-3">
            <p class="flex-1 min-w-0 text-xs text-slate-500 truncate">
                <b class="text-slate-800" v-text="form.butir.length"></b> {{ __('pertanyaan') }} · {{ __('total') }} <b class="text-slate-800" v-text="total"></b>
            </p>
            <button type="button" @click="kirimSoal" :disabled="menyimpan"
                class="shrink-0 px-5 py-3 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-extrabold shadow-lg disabled:opacity-50">
                <span v-if="menyimpan">{{ __('Mengirim…') }}</span>
                <span v-else-if="form.id">{{ __('Simpan & kirim ulang') }}</span>
                <span v-else>{{ __('Kirim ke admin') }}</span>
            </button>
        </div>
    </div>

    {{-- ===== Soal saya ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="font-bold text-slate-800">{{ __('Soal saya') }}</h3>
        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="s in soalSaya" :key="s.id" class="py-3">
                <div class="flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800" v-text="s.kitab || '-'"></p>
                        <p class="text-xs text-slate-400" v-text="[s.nama_ujian, s.fan, s.kelas.join(', ')].filter(Boolean).join(' · ')"></p>
                        <p class="text-xs text-slate-400"><span v-text="s.butir.length"></span> {{ __('pertanyaan') }} · {{ __('total') }} <span v-text="s.total"></span> · <span v-text="s.dikirim"></span></p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                        :class="s.status === 'disetujui' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200'" v-text="s.label"></span>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" @click="buka = buka === s.id ? null : s.id" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
                        <template v-if="buka === s.id">{{ __('Tutup') }}</template><template v-else>{{ __('Lihat soal') }}</template>
                    </button>
                    <template v-if="s.status === 'menunggu'">
                        <button type="button" @click="ubah(s)" class="px-3 py-1.5 rounded-xl text-xs font-bold text-amber-700 ring-1 ring-amber-200 hover:bg-amber-50">{{ __('Ubah') }}</button>
                        <button type="button" @click="hapus(s)" class="px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Hapus') }}</button>
                    </template>
                </div>

                {{-- Isi soal (versi koreksi admin kalau sudah disetujui) --}}
                <div v-if="buka === s.id" class="mt-3 space-y-2">
                    <p v-if="s.catatan_admin" class="p-3 rounded-2xl bg-sky-50 ring-1 ring-sky-200 text-sm text-sky-900 whitespace-pre-line">
                        <b>{{ __('Catatan admin') }}:</b> <span v-text="s.catatan_admin"></span>
                    </p>
                    <p v-if="s.status === 'disetujui' && s.pemeriksa" class="text-xs text-emerald-700">
                        {{ __('Diperiksa oleh') }} <b v-text="s.pemeriksa"></b> · <span v-text="s.diperiksa"></span>
                        <template v-if="s.diubah.length"> · <span v-text="s.diubah.length + ' ' + teks.diubah"></span></template>
                    </p>
                    <div v-for="(b, i) in s.butir" :key="i" class="rounded-2xl ring-1 p-3" :class="s.diubah.includes(i) ? 'ring-sky-200 bg-sky-50/50' : 'ring-slate-200 bg-[#f8fbfa]'">
                        <div class="flex items-center gap-2">
                            <b class="text-sm text-slate-800" v-text="(i + 1) + '.'"></b>
                            <span v-if="s.diubah.includes(i)" class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700">{{ __('diubah admin') }}</span>
                            <span class="ml-auto text-xs font-bold text-amber-700" v-text="teks.nilai + ' ' + b.nilai"></span>
                        </div>
                        <p class="mt-1 text-sm text-slate-800 whitespace-pre-line" dir="auto" v-text="b.pertanyaan"></p>
                        <p class="mt-1.5 text-sm text-emerald-800 whitespace-pre-line" dir="auto"><b>{{ __('Jawaban') }}:</b> <span v-text="b.jawaban"></span></p>
                    </div>
                </div>
            </li>
            <li v-if="!soalSaya.length" class="py-6 text-center text-sm text-slate-400">{{ __('Belum ada soal yang Anda kirim.') }}</li>
        </ul>
    </section>
    </div>

    {{-- ================= Tab 2: Soal Disetujui (semua asatid, hanya dibaca) ================= --}}
    <div v-show="tab === 'disetujui'" @if ($tab !== 'disetujui') style="display: none" @endif class="space-y-5">
        <section class="{{ $kartu }} p-5 space-y-3">
            <select v-model="saring.ujian" class="{{ $input }}">
                <option value="">{{ __('Semua ujian') }}</option>
                <option v-for="u in ujianDisetujui" :key="u.kunci" :value="u.kunci" v-text="u.nama"></option>
            </select>
            <input type="search" v-model="saring.cari" placeholder="{{ __('Cari asatid / kitab / halaqoh…') }}" class="{{ $input }}">
        </section>

        <section v-for="s in disetujuiTampil" :key="s.id" class="{{ $kartu }} p-5">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-600" v-text="[s.nama_ujian, s.fan].filter(Boolean).join(' · ') || '-'"></p>
                    <h3 class="mt-0.5 font-extrabold text-slate-800" v-text="s.kitab || '-'"></h3>
                    <p class="text-xs text-slate-400"><span v-text="s.butir.length"></span> {{ __('pertanyaan') }} · {{ __('total') }} <span v-text="s.total"></span></p>
                </div>
                <span v-if="s.oleh_id === saya" class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200">{{ __('Soal Anda') }}</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-1">
                <span v-for="k in s.kelas" :key="k" class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600" v-text="k"></span>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ __('Dibuat oleh') }} <b class="text-slate-700" v-text="s.oleh || '-'"></b> · {{ __('disetujui') }} <span v-text="s.diperiksa"></span></p>
            <x-nama-arab vue="s.oleh_arab" kelas="text-xs text-slate-400" />
            <button type="button" @click="bukaSetuju = bukaSetuju === s.id ? null : s.id" class="mt-3 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">
                <template v-if="bukaSetuju === s.id">{{ __('Tutup') }}</template><template v-else>{{ __('Lihat soal') }}</template>
            </button>
            <div v-if="bukaSetuju === s.id" class="mt-3 space-y-2">
                <div v-for="(b, i) in s.butir" :key="i" class="rounded-2xl ring-1 ring-slate-200 bg-[#f8fbfa] p-3">
                    <div class="flex items-center gap-2">
                        <b class="text-sm text-slate-800" v-text="(i + 1) + '.'"></b>
                        <span class="ml-auto text-xs font-bold text-amber-700" v-text="teks.nilai + ' ' + b.nilai"></span>
                    </div>
                    <p class="mt-1 text-sm text-slate-800 whitespace-pre-line" dir="auto" v-text="b.pertanyaan"></p>
                    <p class="mt-1.5 text-sm text-emerald-800 whitespace-pre-line" dir="auto"><b>{{ __('Jawaban') }}:</b> <span v-text="b.jawaban"></span></p>
                </div>
            </div>
        </section>
        <p v-if="!disetujuiTampil.length" class="{{ $kartu }} py-10 text-center text-sm text-slate-400">{{ __('Belum ada soal yang disetujui admin.') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const RUTE = {
        simpan: @json(route('asatid.penilaian.ujian.soal.simpan')),
        hapus: @json(route('asatid.penilaian.ujian.soal.hapus', ['id' => '__ID__'])),
    };
    const CSRF = @json(csrf_token());
    let kunci = 0;
    const butirBaru = () => ({ pertanyaan: '', jawaban: '', nilai: 10, kunci: ++kunci });
    const formBaru = () => ({ id: null, kelas_ids: [], ujian_id: null, kitab_ids: [], butir: [butirBaru()] });

    async function kirim(url, method, body) {
        const res = await fetch(url, {
            method,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: body ? JSON.stringify(body) : undefined,
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || @js(__('Gagal mengirim. Coba lagi.')));
        return json;
    }

    Vue.createApp({
        data: () => ({
            ujian: @json($ujian),
            kelas: @json($kelasList),
            soalSaya: @json($soalSaya),
            soalDisetujui: @json($soalDisetujui),
            saya: @json(auth()->id()),
            tab: @json($tab),           // buat | disetujui
            saring: { ujian: '', cari: '' },
            bukaSetuju: null,
            teks: {
                total: @js(__('Total')),
                nilai: @js(__('nilai')),
                diubah: @js(__('butir diubah admin')),
                beda: @js(__('Jumlah nilai soal (:total) belum sama dengan nilai maksimal ujian (:maks). Boleh tetap dikirim.')),
            },
            form: formBaru(),
            buka: null,
            menyimpan: false,
            galat: '',
        }),
        computed: {
            ujianDipilih() { return this.ujian.find((u) => u.id === this.form.ujian_id) || null; },
            total() { return this.form.butir.reduce((n, b) => n + (Number(b.nilai) || 0), 0); },
            // Pilihan saringan ujian di tab Soal Disetujui (dari soal yang ada; nama ujian disalin di soal)
            ujianDisetujui() {
                const peta = {};
                this.soalDisetujui.forEach((s) => { peta[s.ujian_id ?? s.nama_ujian] ||= { kunci: String(s.ujian_id ?? s.nama_ujian), nama: [s.nama_ujian, s.fan].filter(Boolean).join(' · ') || '-' }; });
                return Object.values(peta);
            },
            disetujuiTampil() {
                const k = (this.saring.cari || '').trim().toLowerCase();
                return this.soalDisetujui.filter((s) => (!this.saring.ujian || String(s.ujian_id ?? s.nama_ujian) === this.saring.ujian)
                    && (!k || [s.oleh, s.kitab, s.fan, s.nama_ujian, ...s.kelas].some((t) => (t || '').toLowerCase().includes(k))));
            },
        },
        methods: {
            // Pindah tab; alamat ?tab=... ikut berubah (bisa dimuat ulang / dibagikan)
            pilihTab(t) {
                this.tab = t;
                const u = new URL(location.href);
                u.searchParams.set('tab', t);
                history.replaceState(null, '', u);
            },
            pilihKelas(id) {
                const i = this.form.kelas_ids.indexOf(id);
                i === -1 ? this.form.kelas_ids.push(id) : this.form.kelas_ids.splice(i, 1);
            },
            // Kelompok kitab dari pengaturan admin (satu kitab, beberapa kitab, atau seluruh fan)
            pilihKelompok(ujianId, g) { this.form.ujian_id = ujianId; this.form.kitab_ids = [...g.kitab_ids]; },
            terpilih(ujianId, g) { return this.form.ujian_id === ujianId && [...g.kitab_ids].sort().join() === [...this.form.kitab_ids].sort().join(); },
            tambahButir() { this.form.butir.push(butirBaru()); },
            hapusButir(i) {
                const b = this.form.butir[i];
                if (this.form.butir.length > 1 && (!b.pertanyaan && !b.jawaban || confirm(@js(__('Hapus pertanyaan nomor :n?')).replace(':n', i + 1)))) this.form.butir.splice(i, 1);
            },
            baru() { this.form = formBaru(); this.galat = ''; },
            ubah(s) {
                this.form = {
                    id: s.id, kelas_ids: [...s.kelas_ids], ujian_id: s.ujian_id, kitab_ids: [...s.kitab_ids],
                    butir: s.butir.map((b) => ({ ...b, kunci: ++kunci })),
                };
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            async kirimSoal() {
                const f = this.form;
                this.galat = '';
                if (!f.kelas_ids.length) { this.galat = @js(__('Pilih minimal satu halaqoh.')); return; }
                if (!f.ujian_id || !f.kitab_ids.length) { this.galat = @js(__('Pilih ujian & kitab dulu.')); return; }
                const kosong = f.butir.findIndex((b) => !b.pertanyaan.trim() || !b.jawaban.trim() || !(Number(b.nilai) > 0));
                if (kosong !== -1) { this.galat = @js(__('Pertanyaan nomor :n belum lengkap (pertanyaan, jawaban, dan nilai wajib diisi).')).replace(':n', kosong + 1); return; }
                this.menyimpan = true;
                try {
                    const json = await kirim(RUTE.simpan, 'POST', {
                        id: f.id, kelas_ids: f.kelas_ids, ujian_id: f.ujian_id, kitab_ids: f.kitab_ids,
                        butir: f.butir.map((b) => ({ pertanyaan: b.pertanyaan, jawaban: b.jawaban, nilai: b.nilai })),
                    });
                    this.soalSaya = [json.baris, ...this.soalSaya.filter((s) => s.id !== json.baris.id)];
                    this.form = formBaru();
                    this.buka = json.baris.id;
                    window.notifikasi?.sukses(json.message);
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.menyimpan = false;
                }
            },
            async hapus(s) {
                if (!confirm(@js(__('Hapus soal :kitab? Tidak bisa dibatalkan.')).replace(':kitab', s.kitab || '-'))) return;
                try {
                    const json = await kirim(RUTE.hapus.replace('__ID__', s.id), 'DELETE');
                    this.soalSaya = this.soalSaya.filter((x) => x.id !== s.id);
                    if (this.form.id === s.id) this.baru();
                    window.notifikasi?.sukses(json.message);
                } catch (e) {
                    window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                }
            },
        },
    }).mount('#buat-soal-ujian');
});
</script>
@endpush
