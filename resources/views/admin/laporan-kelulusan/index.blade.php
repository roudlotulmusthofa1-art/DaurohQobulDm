{{--
    Laporan > Laporan Kelulusan (Admin\LaporanKelulusanController@index).
    Santri yang sudah diputuskan Lajnah di halaman Istimaroh. Dua tab (alamat ?tab=lulus|tidak):
    - tiap tab punya Pengaturan Pesan sendiri (teks bebas + isian {nama} dll., pratinjau ala WhatsApp, contoh bawaan)
    - daftar santri + tanda "Sudah / Belum diberitahu"
    - kirim satu per satu atau sekaligus: lewat Fonnte (otomatis) atau WhatsApp admin (wa.me, antrean satu per satu)
    - santri yang sudah diberitahu otomatis melihat hasil + pesannya di dasbor siswa > Pengumuman Kelulusan (Munaqosah::pengumumanUntuk)
    Nomor tujuan = nomor WhatsApp santri sendiri (no_hp).
    Vue: #halaman-kelulusan.
--}}
@extends('layouts.admin')

@section('title', __('Laporan Kelulusan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$tutup = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
$ikonWa = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.5h-.01a9.4 9.4 0 01-4.8-1.31l-.34-.2-3.57.93.95-3.48-.22-.36a9.43 9.43 0 01-1.45-5.03c0-5.2 4.24-9.44 9.45-9.44a9.4 9.4 0 016.68 2.77 9.38 9.38 0 012.76 6.68c0 5.21-4.24 9.44-9.45 9.44zm8.04-17.48A11.3 11.3 0 0012.05.7C5.79.7.7 5.79.7 12.04c0 2 .52 3.95 1.52 5.67L.6 23.3l5.72-1.5a11.3 11.3 0 005.42 1.38h.01c6.25 0 11.34-5.09 11.34-11.34 0-3.03-1.18-5.88-3.32-8.02z"/></svg>';
@endphp

<div id="halaman-kelulusan" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8 pb-6">
    <x-header-admin :label="__('Laporan')" :judul="__('Laporan Kelulusan')"
        :keterangan="__('Hasil keputusan Lajnah di halaman Istimaroh, beserta pemberitahuan lewat WhatsApp ke nomor santri.')" />

    {{-- ===== Tab ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 rounded-3xl bg-white ring-1 ring-slate-200/70 p-1.5">
        <button v-for="t in TAB" :key="t.kunci" type="button" @click="pilihTab(t.kunci)"
            class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl text-sm font-bold transition"
            :class="tab === t.kunci ? t.aktif : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'">
            <span v-text="t.label"></span>
            <span class="min-w-[1.75rem] rounded-full px-2 py-0.5 text-xs tabular-nums" :class="tab === t.kunci ? 'bg-white/20' : 'bg-slate-100'" v-text="perTab(t.kunci).length"></span>
        </button>
    </div>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Jumlah santri') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800 tabular-nums" v-text="daftarTab.length"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Sudah diberitahu') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 tabular-nums" v-text="daftarTab.filter((s) => s.sudah).length"></p>
        </div>
        <div class="{{ $kartu }} p-4">
            <p class="text-xs font-semibold text-slate-500">{{ __('Belum diberitahu') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 tabular-nums" v-text="daftarTab.filter((s) => !s.sudah).length"></p>
        </div>
    </div>

    {{-- ===== Pengaturan pesan tab ini ===== --}}
    <section class="{{ $kartu }} overflow-hidden">
        <button type="button" @click="aturBuka = !aturBuka" class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-slate-50/60">
            <span class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-emerald-500 to-[#0b332c] text-white flex items-center justify-center">{!! $ikonWa !!}</span>
            <span class="min-w-0 flex-1">
                <span class="block font-bold text-slate-800">
                    <span v-text="teks.pengaturanPesan + ' ' + tabIni.label"></span>
                    <span v-if="pesan[tab].diubah" class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ __('DIUBAH') }}</span>
                    <span v-else class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ __('CONTOH') }}</span>
                </span>
                <span class="block text-xs text-slate-400">{{ __('Tulis sendiri isi pesan WhatsApp untuk tab ini. Klik tombol isian untuk menyisipkan data santri.') }}</span>
            </span>
            <span class="text-slate-400 transition" :class="aturBuka ? 'rotate-180' : ''">&#9662;</span>
        </button>

        <div v-show="aturBuka" class="border-t border-slate-100">
            <div class="grid lg:grid-cols-2">
                <div class="p-4 sm:p-5 space-y-2 lg:border-r border-slate-100">
                    <div class="flex flex-wrap gap-1.5">
                        <button v-for="(ket, k) in isian" :key="k" type="button" @click="sisip(k)" :title="ket"
                            class="rounded-lg bg-emerald-50 px-2 py-1 font-mono text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100" v-text="'{' + k + '}'"></button>
                    </div>
                    <textarea ref="ta" rows="12" maxlength="3000" v-model="ubahan[tab]"
                        class="w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-[13px] leading-relaxed text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    <p class="text-[11px] text-slate-400">{{ __('Format WhatsApp:') }} <b>*{{ __('tebal') }}*</b>, <i>_{{ __('miring') }}_</i>, <s>~{{ __('coret') }}~</s>.
                        <span class="font-mono">{catatan}</span> {{ __('berisi catatan Lajnah beserta judulnya, otomatis hilang bila kosong.') }}</p>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="text-[11px] text-slate-400" v-text="(ubahan[tab] || '').length + ' / 3000'"></span>
                        <button type="button" @click="pakaiContoh" class="ml-auto text-xs font-semibold text-slate-500 hover:text-rose-600">{{ __('Kembalikan ke contoh') }}</button>
                        <button type="button" @click="simpanPesan" :disabled="menyimpan || !berubah"
                            class="px-4 py-2 rounded-xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-40"
                            v-text="menyimpan ? teks.menyimpan : teks.simpanPesan"></button>
                    </div>
                    <p v-if="berubah" class="text-xs font-semibold text-amber-600" v-text="teks.belumDisimpan"></p>
                </div>
                {{-- Pratinjau ala WhatsApp dengan data contoh --}}
                <div class="p-4 sm:p-5 bg-[#efeae2]">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Pratinjau (data contoh)') }}</p>
                    <div class="mt-2 ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#d9fdd3] px-3 py-2 text-[13px] leading-relaxed text-slate-800 shadow-sm whitespace-pre-wrap break-words"
                        v-html="htmlWa(rangkai(ubahan[tab], contoh))"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Saringan + kirim sekaligus ===== --}}
    <div class="{{ $kartu }} p-4 sm:p-5 space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-end gap-3">
            <label class="block flex-1 min-w-48"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
                <input type="search" v-model="cari" placeholder="{{ __('Cari nama / nomor santri…') }}" class="mt-1 {{ $input }}"></label>
            <div class="grid grid-cols-3 gap-1.5 rounded-2xl bg-slate-100 p-1.5 lg:w-80 lg:shrink-0">
                <button v-for="s in SARING" :key="s.kunci" type="button" @click="saring = s.kunci" class="py-2 rounded-xl text-xs sm:text-sm font-bold transition"
                    :class="saring === s.kunci ? 'bg-white text-[#0b332c] shadow-sm' : 'text-slate-500 hover:text-slate-700'" v-text="s.label"></button>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" @click="kirimSemuaFonnte" :disabled="!!massal || berubah || !antrean.length"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0b332c] px-4 py-3 text-sm font-bold text-white hover:bg-[#135347] disabled:opacity-40">
                {!! $ikonWa !!} <span v-text="teks.semuaFonnte + ' (' + antrean.length + ')'"></span>
            </button>
            <button type="button" @click="bukaAntreanWa" :disabled="!!massal || berubah || !antrean.length"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25d366] px-4 py-3 text-sm font-bold text-white hover:bg-[#1ebe5a] disabled:opacity-40">
                {!! $ikonWa !!} <span v-text="teks.semuaWa + ' (' + antrean.length + ')'"></span>
            </button>
        </div>
        <p class="text-[11px] text-slate-400">{{ __('Kirim sekaligus hanya untuk santri yang BELUM diberitahu dan punya nomor WhatsApp.') }}
            {{ __('Santri yang sudah diberitahu juga langsung bisa melihat hasilnya di dasbor siswa (Pengumuman Kelulusan).') }}
            <span v-if="berubah" class="font-semibold text-amber-600" v-text="teks.simpanDulu"></span></p>

        {{-- Kemajuan kirim sekaligus lewat Fonnte --}}
        <div v-if="massal" class="rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 p-4">
            <div class="flex items-center justify-between gap-3 text-sm font-bold text-emerald-800">
                <span v-text="teks.mengirim + ' ' + massal.selesai + ' / ' + massal.total"></span>
                <button type="button" @click="massal.berhenti = true" :disabled="massal.berhenti" class="text-xs font-bold text-rose-600 hover:underline disabled:opacity-40">{{ __('Berhenti') }}</button>
            </div>
            <div class="mt-2 h-2 rounded-full bg-white overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all" :style="{ width: (massal.selesai / massal.total * 100) + '%' }"></div>
            </div>
            <p class="mt-2 text-xs text-emerald-700" v-text="teks.berhasil + ': ' + massal.berhasil + ' · ' + teks.gagalLabel + ': ' + massal.gagal.length"></p>
        </div>
        {{-- Daftar yang gagal dari pengiriman terakhir --}}
        <div v-if="gagalTerakhir.length" class="rounded-2xl bg-rose-50 ring-1 ring-rose-100 p-4">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-bold text-rose-700" v-text="teks.daftarGagal"></p>
                <button type="button" @click="gagalTerakhir = []" class="text-rose-400 hover:text-rose-600">{!! $tutup !!}</button>
            </div>
            <p v-for="(g, i) in gagalTerakhir" :key="i" class="mt-1 text-xs text-rose-700" v-text="g"></p>
        </div>
    </div>

    <p v-if="!tampilList.length" class="{{ $kartu }} py-14 text-center text-sm text-slate-400" v-text="daftarTab.length ? teks.tidakCocok : teks.kosong"></p>

    {{-- ===== Daftar santri ===== --}}
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-4">
        <div v-for="s in tampilList" :key="s.id" class="{{ $kartu }} p-5 flex flex-col">
            <div class="flex items-center gap-3">
                <img v-if="s.foto" :src="s.foto" alt="" class="w-12 h-12 shrink-0 rounded-2xl object-cover bg-slate-100">
                <span v-else class="w-12 h-12 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-[#0b332c] text-white text-lg font-extrabold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-bold text-slate-800 truncate" v-text="s.siswa"></span>
                    <x-nama-arab vue="s.arab" />
                    <span class="block text-xs text-slate-400 truncate" v-text="[s.kelas, s.nomor].filter(Boolean).join(' · ')"></span>
                </span>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold ring-1" :class="s.keputusan === 'lulus' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200'"
                    v-text="s.keputusan === 'lulus' ? teks.lulus : teks.tidak"></span>
                <span v-if="s.sudah" class="rounded-full bg-emerald-500 px-2.5 py-1 text-[11px] font-bold text-white" v-text="teks.sudah + ' · ' + (s.lewat === 'fonnte' ? 'Fonnte' : teks.waSaya)"></span>
                <span v-else class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200" v-text="teks.belum"></span>
                <span v-if="s.sudah" class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-200" :title="teks.ketDasbor" v-text="teks.tampilDasbor"></span>
            </div>
            <p class="mt-2 text-[11px] text-slate-400">
                <span v-text="teks.diputuskan + ' ' + waktu(s.diputuskan)"></span>
                <span v-if="s.sudah" v-text="' · ' + teks.diberitahu + ' ' + waktu(s.diberitahu)"></span>
            </p>
            <p class="mt-1 text-xs font-semibold" :class="s.wa ? 'text-slate-600' : 'text-rose-500'" v-text="s.hp || teks.nomorKosong"></p>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" @click="kirimFonnte(s)" :disabled="!s.wa || !!mengirim[s.id] || !!massal || berubah"
                    class="rounded-xl bg-[#0b332c] px-3 py-2 text-xs font-bold text-white hover:bg-[#135347] disabled:opacity-40"
                    v-text="mengirim[s.id] ? teks.mengirim : (s.sudah ? teks.ulangFonnte : teks.kirimFonnte)"></button>
                <a v-if="s.wa && !berubah" :href="linkWa(s)" target="_blank" rel="noopener" @click="tandaiWa(s)"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#25d366] px-3 py-2 text-xs font-bold text-white hover:bg-[#1ebe5a]">{!! $ikonWa !!} <span v-text="teks.waSaya"></span></a>
                <span v-else class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#25d366] px-3 py-2 text-xs font-bold text-white opacity-40 cursor-not-allowed">{!! $ikonWa !!} <span v-text="teks.waSaya"></span></span>
            </div>
            <button type="button" @click="lihat = s" class="mt-2 text-xs font-semibold text-emerald-700 hover:underline">{{ __('Lihat pesan') }}</button>
        </div>
    </div>
</div>

{{-- ================= Lihat pesan satu santri ================= --}}
<div v-if="lihat" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="lihat = null">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="lihat = null" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Pesan untuk') }}</p>
            <h3 class="mt-1 pr-10 text-xl font-extrabold truncate" v-text="lihat.siswa"></h3>
            <p class="text-sm text-white/70" v-text="lihat.hp || teks.nomorKosong"></p>
        </div>
        <div class="p-5 bg-[#efeae2]">
            <div class="ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#d9fdd3] px-3 py-2 text-[13px] leading-relaxed text-slate-800 shadow-sm whitespace-pre-wrap break-words" v-html="htmlWa(pesanUntuk(lihat))"></div>
        </div>
    </div>
</div>

{{-- ================= Antrean kirim lewat WhatsApp admin (satu per satu) ================= --}}
<div v-if="antreanWa" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:py-10" @mousedown.self="tutupAntreanWa">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-xl overflow-hidden">
        <div class="relative bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white px-6 py-5">
            <button type="button" @click="tutupAntreanWa" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutup !!}</button>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80" v-text="teks.semuaWa + ' · ' + Math.min(antreanWa.ke + 1, antreanWa.daftar.length) + ' / ' + antreanWa.daftar.length"></p>
            <template v-if="santriAntrean">
                <h3 class="mt-1 pr-10 text-xl font-extrabold truncate" v-text="santriAntrean.siswa"></h3>
                <p class="text-sm text-white/70" v-text="santriAntrean.hp"></p>
            </template>
            <h3 v-else class="mt-1 text-xl font-extrabold" v-text="teks.antreanSelesai"></h3>
        </div>
        <template v-if="santriAntrean">
            <div class="p-5 bg-[#efeae2] max-h-[50vh] overflow-y-auto">
                <div class="ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#d9fdd3] px-3 py-2 text-[13px] leading-relaxed text-slate-800 shadow-sm whitespace-pre-wrap break-words" v-html="htmlWa(pesanUntuk(santriAntrean))"></div>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <p class="mr-auto text-[11px] text-slate-400">{{ __('Tekan Buka WhatsApp, kirim di WhatsApp, lalu kembali ke sini untuk santri berikutnya.') }}</p>
                <button type="button" @click="antreanWa.ke++" class="px-4 py-2.5 rounded-2xl ring-1 ring-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Lewati') }}</button>
                <a :href="linkWa(santriAntrean)" target="_blank" rel="noopener" @click="bukaDariAntrean"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-[#25d366] text-white text-sm font-bold hover:bg-[#1ebe5a]">{!! $ikonWa !!} {{ __('Buka WhatsApp') }}</a>
            </div>
        </template>
        <div v-else class="px-6 py-5 flex justify-end">
            <button type="button" @click="tutupAntreanWa" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Tutup') }}</button>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const CSRF = @json(csrf_token());
        const RUTE = {
            pesan: @js(route('admin.laporan-kelulusan.pesan', '__J__')),
            fonnte: @js(route('admin.laporan-kelulusan.fonnte', '__ID__')),
            wa: @js(route('admin.laporan-kelulusan.wa', '__ID__')),
        };
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
        const tunggu = (ms) => new Promise((r) => setTimeout(r, ms));
        const PESAN = @json($pesan);

        Vue.createApp({
            data: () => ({
                TAB: [
                    { kunci: 'lulus', label: @js(__('Lulus / Diterima')), aktif: 'bg-emerald-600 text-white shadow-sm' },
                    { kunci: 'tidak', label: @js(__('Tidak Lulus / Tidak Diterima')), aktif: 'bg-rose-600 text-white shadow-sm' },
                ],
                SARING: [
                    { kunci: '', label: @js(__('Semua')) },
                    { kunci: 'belum', label: @js(__('Belum')) },
                    { kunci: 'sudah', label: @js(__('Sudah')) },
                ],
                tab: @js($tab),
                santri: @json($santri),
                pesan: PESAN,                                          // { lulus: {teks, bawaan, diubah}, tidak: {...} } yang tersimpan
                ubahan: { lulus: PESAN.lulus.teks, tidak: PESAN.tidak.teks }, // yang sedang ditulis
                isian: @json($isian),
                contoh: @json($contoh),
                aturBuka: true,
                menyimpan: false,
                cari: '',
                saring: '',
                mengirim: {},       // { id_santri: true } yang sedang dikirim lewat Fonnte
                massal: null,       // { total, selesai, berhasil, gagal: [], berhenti }
                gagalTerakhir: [],
                antreanWa: null,    // { daftar: [santri], ke: 0 }
                lihat: null,
                teks: {
                    pengaturanPesan: @js(__('Pengaturan Pesan')), simpanPesan: @js(__('Simpan pesan')), menyimpan: @js(__('Menyimpan…')),
                    belumDisimpan: @js(__('Pesan diubah tetapi belum disimpan.')),
                    simpanDulu: @js(__('Simpan pesan dulu sebelum mengirim.')),
                    semuaFonnte: @js(__('Kirim semua lewat Fonnte')), semuaWa: @js(__('Kirim semua lewat WA saya')),
                    kirimFonnte: @js(__('Kirim Fonnte')), ulangFonnte: @js(__('Kirim ulang Fonnte')), waSaya: @js(__('WA saya')),
                    mengirim: @js(__('Mengirim…')), berhasil: @js(__('Berhasil')), gagalLabel: @js(__('Gagal')),
                    daftarGagal: @js(__('Tidak terkirim:')),
                    lulus: @js(__('Lulus / Diterima')), tidak: @js(__('Tidak Lulus / Tidak Diterima')),
                    sudah: @js(__('Sudah diberitahu')), belum: @js(__('Belum diberitahu')),
                    diputuskan: @js(__('Diputuskan')), diberitahu: @js(__('diberitahu')),
                    nomorKosong: @js(__('Nomor WhatsApp kosong')),
                    tampilDasbor: @js(__('Tampil di dasbor')),
                    ketDasbor: @js(__('Hasil dan pesan ini sudah bisa dilihat santri di dasbor siswa > Pengumuman Kelulusan.')),
                    kosong: @js(__('Belum ada santri dengan keputusan ini. Keputusan dibuat di halaman Istimaroh (tab Lajnah).')),
                    tidakCocok: @js(__('Tidak ada santri yang cocok dengan pencarian / saringan.')),
                    yakinFonnte: @js(__('Kirim pesan lewat Fonnte ke :jumlah santri yang belum diberitahu?')),
                    yakinContoh: @js(__('Ganti isi pesan dengan contoh bawaan? (Belum tersimpan sampai Anda menekan Simpan pesan.)')),
                    antreanSelesai: @js(__('Selesai. Semua santri di antrean sudah dibuka.')),
                    selesaiMassal: @js(__('Selesai: :berhasil terkirim, :gagal gagal.')),
                },
            }),
            computed: {
                tabIni() { return this.TAB.find((t) => t.kunci === this.tab); },
                daftarTab() { return this.perTab(this.tab); },
                berubah() { return (this.ubahan[this.tab] || '').trim() !== (this.pesan[this.tab].teks || '').trim(); },
                // Antrean kirim sekaligus: belum diberitahu & punya nomor
                antrean() { return this.daftarTab.filter((s) => !s.sudah && s.wa); },
                tampilList() {
                    const k = kecil(this.cari.trim());
                    return this.daftarTab
                        .filter((s) => !this.saring || (this.saring === 'sudah' ? s.sudah : !s.sudah))
                        .filter((s) => !k || kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || (s.arab || '').includes(this.cari.trim()));
                },
                santriAntrean() { return this.antreanWa ? this.antreanWa.daftar[this.antreanWa.ke] || null : null; },
            },
            mounted() {
                window.addEventListener('keydown', (e) => {
                    if (e.key !== 'Escape') return;
                    if (this.antreanWa) this.tutupAntreanWa();
                    else this.lihat = null;
                });
            },
            methods: {
                perTab(t) { return this.santri.filter((s) => s.keputusan === t); },
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                waktu: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}, ${t.slice(11, 16)}` : '–'),
                pilihTab(t) {
                    this.tab = t;
                    this.saring = '';
                    const url = new URL(location.href);
                    url.searchParams.set('tab', t);
                    history.replaceState(null, '', url);
                },
                // Teks pesan + data -> pesan jadi (sama dengan aturan di server)
                rangkai(teks, data) {
                    return (teks || '').replace(/\{(\w+)\}/g, (m, k) => (k in this.isian ? (data[k] ?? '') : m))
                        .replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
                },
                // Pesan untuk satu santri, memakai pesan TERSIMPAN tab santri itu (yang juga dipakai Fonnte)
                pesanUntuk(s) {
                    return this.rangkai(this.pesan[s.keputusan].teks, {
                        nama: s.siswa, nomor: s.nomor || '', kelas: s.kelas || '', catatan: s.catatan || '', pesantren: this.contoh.pesantren,
                    });
                },
                htmlWa(teks) {
                    return (teks || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/\*([^*\n]+)\*/g, '<b>$1</b>')
                        .replace(/(^|\W)_([^_\n]+)_(?=\W|$)/g, '$1<i>$2</i>')
                        .replace(/~([^~\n]+)~/g, '<s>$1</s>');
                },
                linkWa(s) { return 'https://wa.me/' + s.wa + '?text=' + encodeURIComponent(this.pesanUntuk(s)); },
                // ===== Pengaturan pesan =====
                sisip(k) {
                    const ta = this.$refs.ta;
                    const kata = '{' + k + '}';
                    const teks = this.ubahan[this.tab] || '';
                    const awal = ta.selectionStart ?? teks.length;
                    const akhir = ta.selectionEnd ?? awal;
                    this.ubahan[this.tab] = teks.slice(0, awal) + kata + teks.slice(akhir);
                    this.$nextTick(() => { ta.focus(); ta.selectionStart = ta.selectionEnd = awal + kata.length; });
                },
                pakaiContoh() {
                    if (confirm(this.teks.yakinContoh)) this.ubahan[this.tab] = this.pesan[this.tab].bawaan;
                },
                async simpanPesan() {
                    const t = this.tab;
                    this.menyimpan = true;
                    try {
                        const json = await kirimJson(RUTE.pesan.replace('__J__', t), 'PUT', { teks: this.ubahan[t] });
                        Object.assign(this.pesan[t], { teks: json.teks, diubah: json.diubah });
                        this.ubahan[t] = json.teks;
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
                // ===== Kirim =====
                perbarui(baris) {
                    const s = this.santri.find((x) => x.id === baris.id);
                    if (s) Object.assign(s, baris);
                },
                async kirimSatu(s) {
                    this.mengirim[s.id] = true;
                    try {
                        const json = await kirimJson(RUTE.fonnte.replace('__ID__', s.id), 'POST');
                        this.perbarui(json.baris);
                        return json;
                    } finally {
                        delete this.mengirim[s.id];
                    }
                },
                async kirimFonnte(s) {
                    try {
                        const json = await this.kirimSatu(s);
                        window.notifikasi?.sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    }
                },
                async kirimSemuaFonnte() {
                    const daftar = [...this.antrean];
                    if (!daftar.length || !confirm(this.teks.yakinFonnte.replace(':jumlah', daftar.length))) return;
                    this.gagalTerakhir = [];
                    this.massal = { total: daftar.length, selesai: 0, berhasil: 0, gagal: [], berhenti: false };
                    for (const s of daftar) {
                        if (this.massal.berhenti) break;
                        try {
                            await this.kirimSatu(s);
                            this.massal.berhasil++;
                        } catch (e) {
                            this.massal.gagal.push(e.message);
                        }
                        this.massal.selesai++;
                        // Jeda antar pesan supaya nomor pengirim tidak dianggap spam oleh WhatsApp
                        if (this.massal.selesai < daftar.length && !this.massal.berhenti) await tunggu(2000);
                    }
                    const m = this.massal;
                    this.gagalTerakhir = m.gagal;
                    this.massal = null;
                    const ringkas = this.teks.selesaiMassal.replace(':berhasil', m.berhasil).replace(':gagal', m.gagal.length);
                    m.gagal.length ? gagal(ringkas) : window.notifikasi?.sukses(ringkas);
                },
                // WhatsApp admin: tautan dibuka -> tandai sudah diberitahu
                async tandaiWa(s) {
                    try {
                        const json = await kirimJson(RUTE.wa.replace('__ID__', s.id), 'POST');
                        this.perbarui(json.baris);
                    } catch (e) {
                        gagal(e.message);
                    }
                },
                bukaAntreanWa() {
                    this.antreanWa = { daftar: [...this.antrean], ke: 0 };
                },
                bukaDariAntrean() {
                    this.tandaiWa(this.santriAntrean);
                    this.antreanWa.ke++;
                },
                tutupAntreanWa() { this.antreanWa = null; },
            },
        }).mount('#halaman-kelulusan');
    });
</script>
@endpush
