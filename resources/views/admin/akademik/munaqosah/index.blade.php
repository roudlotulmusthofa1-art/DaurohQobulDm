{{--
    Admin > Akademik > Munaqosah (Admin\MunaqosahController@index).
    Musyawarah para asatid: santri lulus / diterima atau tidak. Dua tab (alamat ?tab=musyrifin|lajnah):
    1. Musyrifin Dauroh : 3 tombol pengaturan kolom (Nilai Ilmu, Suluk, Catatan Mandub) + kartu santri yang belum dikirim.
       Tiap kartu punya 8 tombol panel -> MODAL di halaman ini (data dari admin.munaqosah.data / .profil);
       Nilai Ilmu / Suluk / Mandub disimpan per panel (admin.munaqosah.bagian), lalu tombol "Kirim ke Lajnah" (admin.munaqosah.kirim).
    2. Lajnah Qobul Darul Musthofa : kartu santri yang sudah dikirim + tanda keputusan.
       Kartu dibuka di TAB BARU (admin/munaqosah/{santri}); saat kembali ke tab ini halaman dimuat ulang supaya kartu pindah.
    Tampilan panel bersama: _panel-isian, _panel-nilai, _panel-dakwah, _panel-absensi, _panel-catatan + _skrip-panel (mixin MUNAQOSAH_PANEL).
    Vue: #halaman-munaqosah.
--}}
@extends('layouts.admin')

@section('title', __('Istimaroh'))

@section('content')
@include('admin.akademik.munaqosah._skrip-panel')
@php
$nomorPanel = fn ($n) => '<span class="w-7 h-7 shrink-0 rounded-lg bg-[#0b332c] text-white text-xs font-extrabold flex items-center justify-center">' . $n . '</span>';
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$tutup = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

<div id="halaman-munaqosah" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8 pb-6">
    <x-header-admin :label="__('Akademik')" :judul="__('Istimaroh')"
        :keterangan="__('Musyawarah para asatid untuk menentukan santri lulus / diterima atau tidak.')" />

    {{-- ===== Tab ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 rounded-3xl bg-white ring-1 ring-slate-200/70 p-1.5">
        <button v-for="t in TAB" :key="t.kunci" type="button" @click="pilihTab(t.kunci)"
            class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl text-sm font-bold transition"
            :class="tab === t.kunci ? 'bg-[#0b332c] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'">
            <span v-text="t.label"></span>
            <span class="min-w-[1.75rem] rounded-full px-2 py-0.5 text-xs tabular-nums" :class="tab === t.kunci ? 'bg-white/15' : 'bg-slate-100'" v-text="jumlah[t.kunci]"></span>
        </button>
    </div>

    {{-- ===== Saringan (+ tab Musyrifin: tombol kecil pengaturan kolom Nilai Ilmu / Suluk / Catatan Mandub) ===== --}}
    <div class="{{ $kartu }} p-4 sm:p-5 flex flex-col lg:flex-row lg:items-end gap-3">
        <label class="block flex-1 min-w-48"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
            <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor santri…') }}" class="mt-1 {{ $input }}"></label>
        <div v-if="tab === 'musyrifin'" class="lg:shrink-0">
            <span class="text-xs font-semibold text-slate-500">{{ __('Pengaturan kolom penilaian') }}</span>
            <div class="mt-1 flex flex-wrap gap-1.5">
                <button v-for="(label, b) in bagian" :key="b" type="button" @click="bukaPengaturan(b)" :title="teks.pengaturan + ' ' + label"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold text-slate-600 hover:ring-emerald-400 hover:text-[#0b332c] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    <span v-text="label + ' (' + kolom[b].length + ')'"></span>
                </button>
            </div>
        </div>
        <div v-if="tab === 'lajnah'" class="grid grid-cols-4 gap-1.5 rounded-2xl bg-slate-100 p-1.5 lg:w-96 lg:shrink-0">
            <button v-for="s in SARING" :key="s.kunci" type="button" @click="saring = s.kunci" class="py-2 rounded-xl text-xs sm:text-sm font-bold transition"
                :class="saring === s.kunci ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'" v-text="s.label"></button>
        </div>
        {{-- Pilihan tampilan kartu tab Lajnah: Ringkas (kartu biasa) | Panel (8 tombol seperti Musyrifin) --}}
        <div v-if="tab === 'lajnah'" class="lg:shrink-0">
            <span class="text-xs font-semibold text-slate-500">{{ __('Tampilan') }}</span>
            <div class="mt-1 grid grid-cols-2 gap-1.5 rounded-2xl bg-slate-100 p-1.5 lg:w-56">
                <button v-for="t in TAMPILAN" :key="t.kunci" type="button" @click="pilihTampilan(t.kunci)" class="py-2 rounded-xl text-xs sm:text-sm font-bold transition"
                    :class="tampilan === t.kunci ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'" v-text="t.label"></button>
            </div>
        </div>
    </div>

    <p v-if="!tampilList.length" class="{{ $kartu }} py-14 text-center text-sm text-slate-400"
        v-text="tab === 'musyrifin' ? teks.kosongMusyrifin : teks.kosongLajnah"></p>

    {{-- ===== Kartu santri ===== --}}
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
        <div v-for="s in tampilList" :key="s.id" class="relative overflow-hidden {{ $kartu }} p-5 flex flex-col">
            <span class="pointer-events-none absolute -top-16 -right-16 w-40 h-40 rounded-full opacity-60 blur-2xl" :class="tanda(s).cahaya"></span>

            <div class="relative flex items-center gap-3">
                <img v-if="s.foto" :src="s.foto" alt="" class="w-12 h-12 shrink-0 rounded-2xl object-cover bg-slate-100">
                <span v-else class="w-12 h-12 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-[#0b332c] text-white text-lg font-extrabold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                <span class="min-w-0">
                    <span class="block font-bold text-slate-800 truncate" v-text="s.siswa"></span>
                    <x-nama-arab vue="s.arab" />
                    <span class="block text-xs text-slate-400 truncate" v-text="[s.kelas, s.nomor].filter(Boolean).join(' · ')"></span>
                </span>
            </div>

            <div class="relative mt-4 flex items-center justify-between gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold ring-1" :class="tanda(s).lencana" v-text="tanda(s).nama"></span>
                <a v-if="tab === 'lajnah' && tampilan === 'ringkas'" :href="s.url" target="_blank" rel="noopener" @click="dibuka = true"
                    class="text-xs font-semibold text-emerald-700 hover:underline whitespace-nowrap">{{ __('Buka') }} &#8599;</a>
            </div>
            <p v-if="tab === 'lajnah'" class="relative mt-2 text-[11px] text-slate-400"
                v-text="s.keputusan ? teks.diputuskan + ' ' + waktu(s.diputuskan) : teks.dikirim + ' ' + waktu(s.dikirim)"></p>

            {{-- Tab Musyrifin, atau tab Lajnah tampilan Panel: 8 tombol panel (dibuka di modal).
                 Musyrifin: + Kirim ke Lajnah. Lajnah: isian hanya dibaca + Buka & putuskan (tab baru). --}}
            <template v-if="tab === 'musyrifin' || tampilan === 'panel'">
                <div class="relative mt-4 grid grid-cols-2 gap-1.5">
                    <button v-for="(p, i) in PANEL" :key="p.kunci" type="button" @click="bukaPanel(s, p.kunci)"
                        class="flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 px-2.5 py-2 text-left text-xs font-semibold text-slate-600 hover:ring-emerald-400 hover:bg-emerald-50/60 hover:text-[#0b332c] transition"
                        :class="PANEL.length % 2 && i === PANEL.length - 1 ? 'col-span-2' : ''">
                        <span class="w-5 h-5 shrink-0 rounded-md bg-[#0b332c] text-white text-[10px] font-extrabold flex items-center justify-center" v-text="i + 1"></span>
                        <span class="min-w-0 flex-1 leading-tight" v-text="p.label"></span>
                        <span v-if="p.isian && s.isi[p.kunci]" class="w-4 h-4 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center" :title="teks.sudahDiisi">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                    </button>
                </div>
                <button v-if="tab === 'musyrifin'" type="button" @click="kirim(s)" :disabled="mengirim === s.id"
                    class="relative mt-3 w-full rounded-2xl bg-[#0b332c] py-2.5 text-sm font-bold text-white hover:bg-[#135347] disabled:opacity-50"
                    v-text="mengirim === s.id ? teks.mengirim : teks.kirim"></button>
                <a v-else :href="s.url" target="_blank" rel="noopener" @click="dibuka = true"
                    class="relative mt-3 block w-full rounded-2xl bg-[#0b332c] py-2.5 text-center text-sm font-bold text-white hover:bg-[#135347]">{{ __('Buka & putuskan') }} &#8599;</a>
            </template>
        </div>
    </div>
</div>

{{-- ================= Modal panel santri (tab Musyrifin) ================= --}}
<div v-if="modal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-2 sm:p-4 sm:py-8" @mousedown.self="tutupModal">
    <div class="w-full max-w-5xl rounded-3xl bg-[#f4f7f6] shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-5 sm:px-6 pt-5 pb-3">
            <button type="button" @click="tutupModal" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80" v-text="lajnah ? teks.judulLajnah : teks.judulMusyrifin"></p>
            <h3 class="mt-1 pr-10 text-xl font-extrabold truncate" v-text="modal.santri.siswa"></h3>
            <x-nama-arab vue="modal.santri.arab" kelas="text-sm text-white/70" />
            {{-- Pindah panel tanpa menutup modal --}}
            <div class="mt-4 -mx-1 flex gap-1.5 overflow-x-auto pb-1">
                <button v-for="(p, i) in PANEL" :key="p.kunci" type="button" @click="gantiPanel(p.kunci)"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition"
                    :class="modal.panel === p.kunci ? 'bg-white text-[#0b332c]' : 'bg-white/10 text-white/80 hover:bg-white/20'">
                    <span v-text="(i + 1) + '. ' + p.label"></span>
                    <span v-if="p.isian && modal.santri.isi[p.kunci]" class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                </button>
            </div>
        </div>

        <div class="p-3 sm:p-5">
            <p v-if="memuat" class="py-16 text-center text-sm text-slate-400" v-text="teks.memuat"></p>
            <template v-else>
                <div v-if="modal.panel === 'santri'">
                    <p v-if="!profilHtml" class="py-16 text-center text-sm text-slate-400" v-text="teks.memuat"></p>
                    <div v-else v-html="profilHtml"></div>
                </div>
                <div v-else-if="modal.panel === 'ilmu'">@include('admin.akademik.munaqosah._panel-isian', ['nomor' => 2, 'b' => 'ilmu'])</div>
                <div v-else-if="modal.panel === 'nilai'">@include('admin.akademik.munaqosah._panel-nilai')</div>
                <div v-else-if="modal.panel === 'dakwah'">@include('admin.akademik.munaqosah._panel-dakwah')</div>
                <div v-else-if="modal.panel === 'suluk'">@include('admin.akademik.munaqosah._panel-isian', ['nomor' => 5, 'b' => 'suluk'])</div>
                <div v-else-if="modal.panel === 'absensi'">@include('admin.akademik.munaqosah._panel-absensi')</div>
                <div v-else-if="modal.panel === 'catatan'">@include('admin.akademik.munaqosah._panel-catatan')</div>
                <div v-else-if="modal.panel === 'mandub'">@include('admin.akademik.munaqosah._panel-isian', ['nomor' => 8, 'b' => 'mandub'])</div>
            </template>
        </div>

        <div class="border-t border-slate-200 bg-white px-5 sm:px-6 py-4 flex flex-wrap items-center justify-end gap-2">
            <p v-if="panelIsian && !lajnah && berubah(modal.panel)" class="mr-auto text-xs font-semibold text-amber-600" v-text="teks.belumDisimpan"></p>
            <button type="button" @click="tutupModal" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Tutup') }}</button>
            <button v-if="panelIsian && !lajnah && !memuat" type="button" @click="simpanPanel" :disabled="menyimpanPanel || !kolom[modal.panel].length"
                class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                v-text="menyimpanPanel ? teks.menyimpan : teks.simpanPanel + ' ' + bagian[modal.panel]"></button>
        </div>
    </div>
</div>

{{-- ================= Jendela pengaturan kolom (Nilai Ilmu / Suluk / Catatan Mandub) ================= --}}
<div v-if="atur" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="tutupPengaturan">
    <div class="w-full max-w-3xl rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="tutupPengaturan" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Pengaturan') }}</p>
            <h3 class="mt-1 text-xl font-extrabold" v-text="bagian[atur.bagian]"></h3>
            <p class="mt-1 text-sm text-white/70">{{ __('Tulis nama label, pilih jenis isiannya. Dropdown dan centang bisa diberi pilihan sendiri.') }}</p>
        </div>

        <div class="p-5 sm:p-6 space-y-3">
            <p v-if="!atur.kolom.length" class="rounded-2xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-400">{{ __('Belum ada kolom. Tekan "Tambah kolom".') }}</p>

            <div v-for="(k, i) in atur.kolom" :key="k.kunci" class="rounded-2xl ring-1 ring-slate-200 bg-[#f8fbfa] p-4">
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <label class="block flex-1"><span class="text-xs font-semibold text-slate-500">{{ __('Nama label') }}</span>
                        <input type="text" v-model="k.label" maxlength="255" placeholder="{{ __('mis. Hafalan Alfiyah') }}" class="mt-1 {{ $input }} bg-white"></label>
                    <label class="block md:w-56"><span class="text-xs font-semibold text-slate-500">{{ __('Jenis isian') }}</span>
                        <select v-model="k.jenis" class="mt-1 {{ $input }} bg-white">
                            <option v-for="(l, j) in jenis" :key="j" :value="j" v-text="l"></option>
                        </select></label>
                    <div class="flex gap-1.5">
                        <button type="button" @click="geser(i, -1)" :disabled="i === 0" class="w-9 h-10 rounded-xl bg-white ring-1 ring-slate-200 text-slate-500 hover:text-[#0b332c] disabled:opacity-30" title="{{ __('Naik') }}">&uarr;</button>
                        <button type="button" @click="geser(i, 1)" :disabled="i === atur.kolom.length - 1" class="w-9 h-10 rounded-xl bg-white ring-1 ring-slate-200 text-slate-500 hover:text-[#0b332c] disabled:opacity-30" title="{{ __('Turun') }}">&darr;</button>
                        <button type="button" @click="atur.kolom.splice(i, 1)" class="w-9 h-10 rounded-xl bg-white ring-1 ring-rose-200 text-rose-500 hover:bg-rose-50 flex items-center justify-center" title="{{ __('Hapus kolom') }}">{!! $tutup !!}</button>
                    </div>
                </div>

                {{-- Pratinjau / pilihan --}}
                <div v-if="berpilihan(k.jenis)" class="mt-3">
                    <p class="text-xs font-semibold text-slate-500">{{ __('Pilihan') }}</p>
                    <div class="mt-1.5 space-y-1.5">
                        <div v-for="(p, pi) in k.pilihan" :key="pi" class="flex items-center gap-2">
                            <span class="w-4 h-4 shrink-0 ring-1 ring-slate-300 bg-white" :class="k.jenis === 'centang' ? 'rounded' : 'rounded-full'"></span>
                            <input type="text" v-model="k.pilihan[pi]" maxlength="255" placeholder="{{ __('Tulis pilihan…') }}" class="{{ $input }} bg-white py-2">
                            <button type="button" @click="k.pilihan.splice(pi, 1)" class="w-8 h-8 shrink-0 rounded-lg text-rose-500 hover:bg-rose-50 flex items-center justify-center" title="{{ __('Hapus pilihan') }}">{!! $tutup !!}</button>
                        </div>
                    </div>
                    <button type="button" @click="k.pilihan.push('')" class="mt-2 text-xs font-bold text-emerald-700 hover:underline">+ {{ __('Tambah pilihan') }}</button>
                </div>
                <p v-else class="mt-2 text-[11px] text-slate-400" v-text="k.jenis === 'teks' ? teks.contohPendek : teks.contohPanjang"></p>
            </div>

            <button type="button" @click="tambahKolom" class="w-full rounded-2xl border-2 border-dashed border-emerald-300 py-3 text-sm font-bold text-emerald-700 hover:bg-emerald-50">+ {{ __('Tambah kolom') }}</button>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap justify-end gap-2">
            <button type="button" @click="tutupPengaturan" class="px-5 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button type="button" @click="simpanPengaturan" :disabled="menyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                v-text="menyimpan ? teks.menyimpan : teks.simpan"></button>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const CSRF = @json(csrf_token());
        const RUTE_KOLOM = @js(route('admin.munaqosah.kolom', '__B__'));
        // Alamat per santri: __ID__ diganti id santri
        const RUTE = {
            data: @js(route('admin.munaqosah.data', '__ID__')),
            profil: @js(route('admin.munaqosah.profil', '__ID__')),
            bagian: @js(route('admin.munaqosah.bagian', ['__ID__', '__B__'])),
            kirim: @js(route('admin.munaqosah.kirim', '__ID__')),
        };
        const alamat = (r, id, b) => r.replace('__ID__', id).replace('__B__', b || '');
        async function kirimJson(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.errors ? Object.values(json.errors)[0][0] : (json.message || @js(__('Gagal menyimpan. Coba lagi.'))));
            return json;
        }
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const kecil = (t) => (t || '').toString().toLowerCase();
        const gagal = (pesan) => (window.notifikasi ? window.notifikasi.gagal(pesan) : alert(pesan));
        let nomor = 0;

        Vue.createApp({
            mixins: [window.MUNAQOSAH_PANEL], // data & fungsi panel bersama (_skrip-panel)
            data: () => ({
                // 8 tombol di kartu tab Musyrifin (& Lajnah tampilan Panel); isian = panel yang diisi & punya tombol Simpan
                PANEL: [
                    { kunci: 'santri', label: @js(__('Detail Santri')) },
                    { kunci: 'ilmu', label: @js(__('Nilai Ilmu')), isian: true },
                    { kunci: 'nilai', label: @js(__('Nilai Pelajaran')) },
                    { kunci: 'dakwah', label: @js(__('Nilai Dakwah')) },
                    { kunci: 'suluk', label: @js(__('Nilai Suluk')), isian: true },
                    { kunci: 'absensi', label: @js(__('Detail Absensi')) },
                    { kunci: 'catatan', label: @js(__('Catatan Santri')) },
                    { kunci: 'mandub', label: @js(__('Catatan Mandub')), isian: true },
                ],
                modal: null,          // { santri, panel } yang sedang dibuka
                memuat: false,
                profilHtml: '',
                asli: {},             // isian per bagian saat terakhir disimpan (JSON) -> tanda "belum disimpan"
                menyimpanPanel: false,
                mengirim: null,       // id santri yang sedang dikirim ke Lajnah
                TAB: [
                    { kunci: 'musyrifin', label: @js(__('Musyrifin Dauroh')) },
                    { kunci: 'lajnah', label: @js(__('Lajnah Qobul Darul Musthofa')) },
                ],
                SARING: [
                    { kunci: '', label: @js(__('Semua')) },
                    { kunci: 'menunggu', label: @js(__('Menunggu')) },
                    { kunci: 'lulus', label: @js(__('Lulus')) },
                    { kunci: 'tidak', label: @js(__('Tidak')) },
                ],
                tab: @js($tab),
                santri: @json($santri),
                kolom: @json($kolom),
                bagian: @json($bagian),
                jenis: @json($jenis),
                cari: '',
                saring: '',
                atur: null,       // { bagian, kolom: [...] } salinan yang sedang diubah
                menyimpan: false,
                dibuka: false,    // kartu sudah dibuka di tab baru -> muat ulang saat kembali
                teks: {
                    pengaturan: @js(__('Pengaturan')), kolom: @js(__('kolom')),
                    simpan: @js(__('Simpan pengaturan')), menyimpan: @js(__('Menyimpan…')),
                    kosongMusyrifin: @js(__('Tidak ada santri di tab ini. Santri yang sudah disimpan ada di tab Lajnah.')),
                    kosongLajnah: @js(__('Belum ada santri yang dikirim ke Lajnah.')),
                    dikirim: @js(__('Dikirim')), diputuskan: @js(__('Diputuskan')),
                    contohPendek: @js(__('Kotak isian pendek (satu baris).')),
                    contohPanjang: @js(__('Kotak isian panjang (bisa banyak baris).')),
                    labelKosong: @js(__('Nama label tidak boleh kosong.')),
                    pilihanKosong: @js(__('Kolom dropdown / centang perlu minimal satu pilihan:')),
                    gagal: @js(__('Gagal menyimpan. Coba lagi.')),
                    memuat: @js(__('Memuat…')), sudahDiisi: @js(__('Sudah diisi')),
                    kirim: @js(__('Kirim ke Lajnah')), mengirim: @js(__('Mengirim…')),
                    simpanPanel: @js(__('Simpan')),
                    belumDisimpan: @js(__('Ada isian yang belum disimpan.')),
                    tutupBelum: @js(__('Ada isian yang belum disimpan. Tetap tutup?')),
                    yakinKirim: @js(__('Kirim :nama ke Lajnah Qobul Darul Musthofa?')),
                    masihKosong: @js(__('Masih kosong:')),
                    gagalMuat: @js(__('Gagal memuat data. Coba lagi.')),
                    judulMusyrifin: @js(__('Musyrifin Dauroh')),
                    judulLajnah: @js(__('Lajnah Qobul Darul Musthofa')),
                },
                TAMPILAN: [
                    { kunci: 'ringkas', label: @js(__('Ringkas')) },
                    { kunci: 'panel', label: @js(__('Panel')) },
                ],
                tampilan: 'ringkas', // tampilan kartu tab Lajnah, diingat per perangkat (localStorage)
                TANDA: {
                    belum: { nama: @js(__('Belum diisi')), lencana: 'bg-slate-50 text-slate-500 ring-slate-200', cahaya: 'bg-slate-100' },
                    kembali: { nama: @js(__('Dikembalikan Lajnah')), lencana: 'bg-sky-50 text-sky-700 ring-sky-200', cahaya: 'bg-sky-200' },
                    menunggu: { nama: @js(__('Menunggu keputusan')), lencana: 'bg-amber-50 text-amber-700 ring-amber-200', cahaya: 'bg-amber-200' },
                    lulus: { nama: @js(__('Lulus / Diterima')), lencana: 'bg-emerald-50 text-emerald-700 ring-emerald-200', cahaya: 'bg-emerald-200' },
                    tidak: { nama: @js(__('Tidak')), lencana: 'bg-rose-50 text-rose-700 ring-rose-200', cahaya: 'bg-rose-200' },
                },
            }),
            computed: {
                jumlah() {
                    return {
                        musyrifin: this.santri.filter((s) => s.status === 'musyrifin').length,
                        lajnah: this.santri.filter((s) => s.status === 'lajnah').length,
                    };
                },
                // Panel yang sedang dibuka termasuk panel isian (Nilai Ilmu / Suluk / Catatan Mandub)?
                panelIsian() {
                    return !!this.modal && !!this.PANEL.find((p) => p.kunci === this.modal.panel)?.isian;
                },
                tampilList() {
                    const k = kecil(this.cari.trim());
                    return this.santri
                        .filter((s) => s.status === this.tab)
                        .filter((s) => this.tab !== 'lajnah' || !this.saring || (this.saring === 'menunggu' ? !s.keputusan : s.keputusan === this.saring))
                        .filter((s) => !k || kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || (s.arab || '').includes(this.cari.trim()));
                },
            },
            mounted() {
                try { if (localStorage.getItem('tampilan_lajnah') === 'panel') this.tampilan = 'panel'; } catch (e) {}
                // Kembali dari tab detail -> muat ulang supaya kartu yang sudah disimpan / diputuskan pindah
                window.addEventListener('focus', () => { if (this.dibuka && !this.atur && !this.modal) location.reload(); });
                window.addEventListener('keydown', (e) => {
                    if (e.key !== 'Escape') return;
                    if (this.atur) this.tutupPengaturan();
                    else if (this.modal) this.tutupModal();
                });
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                waktu: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}, ${t.slice(11, 16)}` : '–'),
                berpilihan: (j) => j === 'dropdown' || j === 'centang',
                tanda(s) {
                    if (s.status === 'lajnah') return this.TANDA[s.keputusan || 'menunggu'];
                    return s.terisi ? this.TANDA.kembali : this.TANDA.belum;
                },
                pilihTab(t) {
                    this.tab = t;
                    const url = new URL(location.href);
                    url.searchParams.set('tab', t);
                    history.replaceState(null, '', url);
                },
                pilihTampilan(t) {
                    this.tampilan = t;
                    try { localStorage.setItem('tampilan_lajnah', t); } catch (e) {}
                },
                // ===== Modal 8 panel (tab Musyrifin, dan tab Lajnah tampilan Panel = hanya dibaca) =====
                async bukaPanel(s, panel) {
                    const santriBaru = !this.modal || this.modal.santri.id !== s.id;
                    this.lajnah = s.status === 'lajnah'; // panel isian jadi hanya dibaca (_panel-isian)
                    this.modal = { santri: s, panel };
                    if (santriBaru) await this.muatSantri(s);
                    if (panel === 'santri') this.muatProfil();
                },
                async muatSantri(s) {
                    this.memuat = true;
                    this.profilHtml = '';
                    this.saringCatatan = '';
                    this.saringAbsensi = '';
                    try {
                        const json = await kirimJson(alamat(RUTE.data, s.id), 'GET');
                        if (!this.modal || this.modal.santri.id !== s.id) return;
                        this.kolom = json.kolom;
                        Object.assign(this, {
                            nilai: json.nilai, aspek: json.aspek, dakwah: json.dakwah,
                            jenisDakwah: json.jenisDakwah, catatan: json.catatan, absensi: json.absensi || [], tersimpan: json.tersimpan || {},
                        });
                        this.isian = this.isianDari(this.kolom, this.tersimpan);
                        this.asli = Object.fromEntries(Object.keys(this.isian).map((b) => [b, JSON.stringify(this.isian[b])]));
                    } catch (e) {
                        gagal(this.teks.gagalMuat);
                        this.modal = null;
                    } finally {
                        this.memuat = false;
                    }
                },
                async muatProfil() {
                    if (this.profilHtml || !this.modal) return;
                    const id = this.modal.santri.id;
                    try {
                        const res = await fetch(alamat(RUTE.profil, id), { headers: { Accept: 'text/html' } });
                        if (!res.ok) throw new Error();
                        const html = await res.text();
                        if (this.modal && this.modal.santri.id === id) this.profilHtml = html;
                    } catch (e) {
                        gagal(this.teks.gagalMuat);
                    }
                },
                gantiPanel(panel) {
                    this.modal.panel = panel;
                    if (panel === 'santri') this.muatProfil();
                },
                berubah(b) {
                    return this.isian[b] !== undefined && JSON.stringify(this.isian[b]) !== this.asli[b];
                },
                tutupModal() {
                    const ada = this.PANEL.some((p) => p.isian && this.berubah(p.kunci));
                    if (ada && !confirm(this.teks.tutupBelum)) return;
                    this.modal = null;
                },
                async simpanPanel() {
                    const b = this.modal.panel;
                    const s = this.modal.santri;
                    this.menyimpanPanel = true;
                    try {
                        const json = await kirimJson(alamat(RUTE.bagian, s.id, b), 'POST', { isian: this.isian[b] });
                        this.tersimpan = json.tersimpan;
                        this.asli[b] = JSON.stringify(this.isian[b]);
                        s.isi = json.isi;
                        s.terisi = Object.values(json.isi).reduce((n, v) => n + v, 0);
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpanPanel = false;
                    }
                },
                async kirim(s) {
                    const kosong = this.PANEL.filter((p) => p.isian && !s.isi[p.kunci]).map((p) => p.label);
                    let tanya = this.teks.yakinKirim.replace(':nama', s.siswa);
                    if (kosong.length) tanya += '\n\n' + this.teks.masihKosong + ' ' + kosong.join(', ');
                    if (!confirm(tanya)) return;
                    this.mengirim = s.id;
                    try {
                        const json = await kirimJson(alamat(RUTE.kirim, s.id), 'POST');
                        Object.assign(s, { status: 'lajnah', keputusan: null, dikirim: json.dikirim, diputuskan: null });
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.mengirim = null;
                    }
                },
                // ===== Pengaturan kolom =====
                bukaPengaturan(b) {
                    this.atur = {
                        bagian: b,
                        kolom: this.kolom[b].map((k) => ({ ...k, pilihan: [...k.pilihan], kunci: ++nomor })),
                    };
                },
                tutupPengaturan() { this.atur = null; },
                tambahKolom() {
                    this.atur.kolom.push({ id: null, label: '', jenis: 'teks', pilihan: [''], kunci: ++nomor });
                },
                geser(i, arah) {
                    const list = this.atur.kolom;
                    const j = i + arah;
                    if (j < 0 || j >= list.length) return;
                    [list[i], list[j]] = [list[j], list[i]];
                },
                async simpanPengaturan() {
                    const kolom = this.atur.kolom.map((k) => ({
                        id: k.id, label: (k.label || '').trim(), jenis: k.jenis,
                        pilihan: this.berpilihan(k.jenis) ? k.pilihan.map((p) => (p || '').trim()).filter(Boolean) : [],
                    }));
                    if (kolom.some((k) => !k.label)) return gagal(this.teks.labelKosong);
                    const tanpaPilihan = kolom.find((k) => this.berpilihan(k.jenis) && !k.pilihan.length);
                    if (tanpaPilihan) return gagal(this.teks.pilihanKosong + ' ' + tanpaPilihan.label);

                    this.menyimpan = true;
                    try {
                        const res = await fetch(RUTE_KOLOM.replace('__B__', this.atur.bagian), {
                            method: 'PUT',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify({ kolom }),
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(json.errors ? Object.values(json.errors)[0][0] : (json.message || this.teks.gagal));
                        this.kolom[this.atur.bagian] = json.kolom;
                        window.notifikasi?.sukses(json.pesan);
                        this.atur = null;
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
            },
        }).mount('#halaman-munaqosah');
    });
</script>
@endpush
