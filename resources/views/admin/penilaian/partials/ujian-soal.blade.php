{{--
    Admin > Penilaian > Kelas & Pelajaran > Ujian > panel "Soal Ujian" (dimuat dari partials/ujian.blade.php).
    Soal kiriman asatid (Asatid > Penilaian > Buat Soal Ujian), lengkap dengan jawaban & nilai tiap pertanyaan.
    Admin membuka "Periksa", mengedit pertanyaan / jawaban / nilai (boleh tambah / hapus butir), menulis catatan,
    lalu "Setujui & kirim ke asatid" (status jadi Disetujui; asatid melihat versi koreksi ini).
    Kiriman asli asatid tetap disimpan (butir_asli), jadi butir yang diubah admin diberi tanda "diubah".
    Data: utSoal (SoalUjian::baris, dari partials/ujian.blade.php).
    Vue: window.UJIAN_SOAL (bawah file ini), dipasang sebagai "mixins" di kelas.blade.php. Semua nama diawali "us".
--}}
@php
$inputUs = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
@endphp

<div v-show="utPanel === 'soal'" @if ($panelUjian !== 'soal') style="display: none" @endif class="space-y-5">
    {{-- ===== Saringan status + cari ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2">
            <button v-for="s in usStatusList" :key="s.kunci" type="button" @click="usStatus = s.kunci"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-bold ring-1"
                :class="usStatus === s.kunci ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'">
                <span v-text="s.label"></span>
                <span class="rounded-full px-2 py-0.5 text-[11px]" :class="usStatus === s.kunci ? 'bg-white/20' : 'bg-slate-100'" v-text="usJumlah(s.kunci)"></span>
            </button>
        </div>
        <input type="search" v-model="usCari" placeholder="{{ __('Cari asatid / kitab / ujian / halaqoh…') }}" class="flex-1 min-w-[14rem] {{ $inputUs }}">
    </section>

    {{-- ===== Daftar soal ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <section v-for="s in usSoalTampil" :key="s.id" class="{{ $kartu }} p-5 flex flex-col">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-600" v-text="[s.nama_ujian, s.fan].filter(Boolean).join(' · ') || '-'"></p>
                    <h3 class="mt-0.5 font-extrabold text-slate-800" v-text="s.kitab || '-'"></h3>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                    :class="s.status === 'disetujui' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200'" v-text="s.label"></span>
            </div>
            <div class="mt-2 flex flex-wrap gap-1">
                <span v-for="k in s.kelas" :key="k" class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600" v-text="k"></span>
            </div>
            <p class="mt-3 text-sm text-slate-600">
                <b v-text="s.butir.length"></b> {{ __('pertanyaan') }} · {{ __('total nilai') }} <b v-text="s.total"></b>
                <template v-if="s.diubah.length"> · <span class="text-sky-700 font-semibold" v-text="s.diubah.length + ' ' + usTeks.diubah"></span></template>
            </p>
            <p class="mt-1 text-xs text-slate-400">
                {{ __('Dari') }} <b class="text-slate-500" v-text="s.oleh || '-'"></b> · <span v-text="s.dikirim"></span>
                <template v-if="s.diperiksa"> · {{ __('disetujui') }} <span v-text="s.diperiksa"></span></template>
            </p>
            <x-nama-arab vue="s.oleh_arab" kelas="text-xs text-slate-400" />
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                <button type="button" @click="usBuka(s)" class="flex-1 px-4 py-2.5 rounded-2xl text-sm font-bold"
                    :class="s.status === 'menunggu' ? 'bg-[#0b332c] text-white hover:bg-[#135347]' : 'bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50'">
                    <template v-if="s.status === 'menunggu'">{{ __('Periksa') }}</template>
                    <template v-else>{{ __('Lihat & ubah') }}</template>
                </button>
                <button type="button" @click="usHapus(s)" :disabled="usMenghapus === s.id" title="{{ __('Hapus soal') }}" aria-label="{{ __('Hapus soal') }}"
                    class="w-10 h-10 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </div>
        </section>
    </div>
    <p v-if="!usSoalTampil.length" class="{{ $kartu }} py-12 text-center text-sm text-slate-400">{{ __('Belum ada soal di sini. Soal dikirim asatid lewat menu Penilaian > Buat Soal Ujian.') }}</p>

    {{-- ===== Modal: Periksa soal ===== --}}
    <div v-if="usEdit" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4" @mousedown.self="usTutup">
        <form class="w-full max-w-3xl my-4 rounded-3xl bg-white shadow-xl" @submit.prevent="usSimpan(true)">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-600" v-text="[usEdit.soal.nama_ujian, usEdit.soal.fan].filter(Boolean).join(' · ')"></p>
                    <h3 class="text-base font-extrabold text-slate-800" v-text="usEdit.soal.kitab || '-'"></h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Dari') }} <b v-text="usEdit.soal.oleh || '-'"></b> · {{ __('Halaqoh') }}: <span v-text="usEdit.soal.kelas.join(', ') || '-'"></span>
                    </p>
                </div>
                <button type="button" @click="usTutup" class="w-8 h-8 shrink-0 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div v-for="(b, i) in usEdit.butir" :key="b.kunci" class="rounded-2xl ring-1 p-4 space-y-2.5"
                    :class="usBeda(i) ? 'ring-sky-200 bg-sky-50/40' : 'ring-slate-200'">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-[#0b332c] text-white text-sm font-extrabold flex items-center justify-center" v-text="i + 1"></span>
                        <span v-if="usBeda(i)" class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-bold text-sky-700" v-text="usAsli(i) ? usTeks.diubahSatu : usTeks.tambahan"></span>
                        <button v-if="usBeda(i) && usAsli(i)" type="button" @click="b.lihatAsli = !b.lihatAsli" class="text-[11px] font-semibold text-sky-700 hover:underline">
                            <template v-if="b.lihatAsli">{{ __('Sembunyikan asli') }}</template><template v-else>{{ __('Lihat tulisan asatid') }}</template>
                        </button>
                        <label class="ml-auto flex items-center gap-1.5 text-xs font-semibold text-slate-500">{{ __('Nilai') }}
                            <input type="number" min="1" max="{{ \App\Models\Ujian::MAKS_NILAI }}" v-model.number="b.nilai" required
                                class="w-20 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-2 py-1.5 text-sm font-bold text-center text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none"></label>
                        <button type="button" @click="usHapusButir(i)" :disabled="usEdit.butir.length === 1" title="{{ __('Hapus pertanyaan') }}" aria-label="{{ __('Hapus pertanyaan') }}"
                            class="w-8 h-8 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center disabled:opacity-30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div v-if="b.lihatAsli && usAsli(i)" class="rounded-xl bg-white ring-1 ring-sky-100 p-3 text-xs text-slate-600 space-y-1">
                        <p><b>{{ __('Pertanyaan asli') }}:</b> <span class="whitespace-pre-line" v-text="usAsli(i).pertanyaan"></span></p>
                        <p><b>{{ __('Jawaban asli') }}:</b> <span class="whitespace-pre-line" v-text="usAsli(i).jawaban"></span></p>
                        <p><b>{{ __('Nilai asli') }}:</b> <span v-text="usAsli(i).nilai"></span></p>
                    </div>
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Pertanyaan') }}</span>
                        <textarea v-model="b.pertanyaan" rows="2" maxlength="5000" required dir="auto" class="mt-1 {{ $inputUs }}"></textarea></label>
                    <label class="block"><span class="text-[11px] font-semibold text-slate-500">{{ __('Jawaban') }}</span>
                        <textarea v-model="b.jawaban" rows="3" maxlength="10000" required dir="auto" class="mt-1 {{ $inputUs }}"></textarea></label>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" @click="usTambahButir" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('Tambah pertanyaan') }}
                    </button>
                    <p class="ml-auto text-sm text-slate-600">{{ __('Total nilai') }}: <b class="text-emerald-700" v-text="usTotalEdit"></b></p>
                </div>

                <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Catatan untuk asatid') }} <span class="font-normal text-slate-400">({{ __('boleh dikosongkan') }})</span></span>
                    <textarea v-model="usEdit.catatan_admin" rows="2" maxlength="5000" class="mt-1 {{ $inputUs }}"></textarea></label>
            </div>

            <div class="border-t border-slate-100 px-6 py-4 flex flex-wrap justify-end gap-2">
                <button type="button" @click="usTutup" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button v-if="usEdit.soal.status === 'menunggu'" type="button" @click="usSimpan(false)" :disabled="usMenyimpan" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-emerald-200 text-sm font-bold text-emerald-700 hover:bg-emerald-50 disabled:opacity-50">{{ __('Simpan dulu') }}</button>
                <button :disabled="usMenyimpan" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                    <template v-if="usMenyimpan">{{ __('Menyimpan...') }}</template>
                    <template v-else-if="usEdit.soal.status === 'menunggu'">{{ __('Setujui & kirim ke asatid') }}</template>
                    <template v-else>{{ __('Simpan perubahan') }}</template>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Panel Soal Ujian: dipasang sebagai mixins di aplikasi Vue halaman Kelas & Pelajaran (kelas.blade.php)
    window.UJIAN_SOAL = (function () {
        const RUTE = {
            simpan: @json(route('admin.penilaian.soal-ujian.simpan', ['soal' => '__ID__'])),
            hapus: @json(route('admin.penilaian.soal-ujian.hapus', ['soal' => '__ID__'])),
        };
        const kecil = (t) => (t || '').toString().toLowerCase();
        let kunci = 0;
        async function kirim(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || @js(__('Gagal menyimpan. Coba lagi.')));
            return json;
        }
        const gagal = (e) => (window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message));

        return {
            data: () => ({
                usStatusList: [
                    { kunci: 'menunggu', label: @js(__('Menunggu')) },
                    { kunci: 'disetujui', label: @js(__('Disetujui')) },
                    { kunci: 'semua', label: @js(__('Semua')) },
                ],
                usTeks: { diubah: @js(__('diubah admin')), diubahSatu: @js(__('diubah')), tambahan: @js(__('tambahan admin')) },
                usStatus: 'menunggu',
                usCari: '',
                usEdit: null,      // { soal, asli, butir: [...salinan], catatan_admin }
                usMenyimpan: false,
                usMenghapus: null,
            }),
            computed: {
                usSoalTampil() {
                    const k = kecil(this.usCari.trim());
                    return this.utSoal.filter((s) => (this.usStatus === 'semua' || s.status === this.usStatus)
                        && (!this.utSaring.bulan || this.utIdUjianBulan.has(s.ujian_id))   // saringan bulan di atas
                        && (!k || [s.oleh, s.kitab, s.fan, s.nama_ujian, ...s.kelas].some((t) => kecil(t).includes(k))));
                },
                usTotalEdit() { return this.usEdit ? this.usEdit.butir.reduce((n, b) => n + (Number(b.nilai) || 0), 0) : 0; },
            },
            methods: {
                // Jumlah per status, mengikuti saringan bulan
                usJumlah(st) {
                    const d = this.utSoal.filter((s) => !this.utSaring.bulan || this.utIdUjianBulan.has(s.ujian_id));
                    return st === 'semua' ? d.length : d.filter((s) => s.status === st).length;
                },
                usBuka(s) {
                    this.usEdit = {
                        soal: s,
                        // pembanding "diubah": kiriman asli asatid (kalau sudah pernah diedit) atau soal apa adanya
                        asli: s.butir_asli || s.butir,
                        butir: s.butir.map((b) => ({ ...b, kunci: ++kunci, lihatAsli: false })),
                        catatan_admin: s.catatan_admin || '',
                    };
                },
                usTutup() { this.usEdit = null; },
                usAsli(i) { return this.usEdit.asli[i] || null; },
                usBeda(i) {
                    const a = this.usEdit.asli[i];
                    const b = this.usEdit.butir[i];
                    return !a || a.pertanyaan !== b.pertanyaan.trim() || a.jawaban !== b.jawaban.trim() || Number(a.nilai) !== Number(b.nilai);
                },
                usTambahButir() { this.usEdit.butir.push({ pertanyaan: '', jawaban: '', nilai: 10, kunci: ++kunci, lihatAsli: false }); },
                usHapusButir(i) {
                    if (this.usEdit.butir.length > 1 && confirm(@js(__('Hapus pertanyaan nomor :n?')).replace(':n', i + 1))) this.usEdit.butir.splice(i, 1);
                },
                async usSimpan(setujui) {
                    const e = this.usEdit;
                    this.usMenyimpan = true;
                    try {
                        const json = await kirim(RUTE.simpan.replace('__ID__', e.soal.id), 'PUT', {
                            butir: e.butir.map((b) => ({ pertanyaan: b.pertanyaan, jawaban: b.jawaban, nilai: b.nilai })),
                            catatan_admin: e.catatan_admin,
                            setujui: setujui || e.soal.status === 'disetujui',
                        });
                        this.utSoal = this.utSoal.map((s) => (s.id === json.baris.id ? json.baris : s));
                        this.usEdit = null;
                        window.notifikasi?.sukses(json.message);
                    } catch (err) { gagal(err); } finally { this.usMenyimpan = false; }
                },
                async usHapus(s) {
                    if (!confirm(@js(__('Hapus soal :kitab dari :nama? Tidak bisa dibatalkan.')).replace(':kitab', s.kitab || '-').replace(':nama', s.oleh || '-'))) return;
                    this.usMenghapus = s.id;
                    try {
                        const json = await kirim(RUTE.hapus.replace('__ID__', s.id), 'DELETE');
                        this.utSoal = this.utSoal.filter((x) => x.id !== s.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (err) { gagal(err); } finally { this.usMenghapus = null; }
                },
            },
        };
    })();
</script>
@endpush
