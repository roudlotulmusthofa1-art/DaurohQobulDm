{{--
    Admin > Akademik > Kelulusan (kelulusan AKHIR dauroh).
    - Isi Lulus / Tidak Lulus, predikat (khusus lulus) & catatan per santri, atau sekaligus untuk santri yang dicentang.
    - Tombol Simpan hanya mengirim baris yang diubah.
    - Peserta baru bisa melihat hasil setelah tombol "Umumkan" ditekan (bisa ditarik lagi).
    Semua interaksi diatur Vue; data awal dari KelulusanController lewat @json.
--}}
@extends('layouts.admin')

@section('title', __('Kelulusan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div id="halaman-kelulusan" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-28">
    @foreach (['success' => 'bg-emerald-50 ring-emerald-200 text-emerald-900', 'error' => 'bg-rose-50 ring-rose-200 text-rose-800'] as $kunciFlash => $gayaFlash)
    @if (session($kunciFlash))
    <div class="p-3.5 rounded-2xl ring-1 text-sm {{ $gayaFlash }}">{{ session($kunciFlash) }}</div>
    @endif
    @endforeach
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl ring-1 ring-rose-200 bg-rose-50 text-rose-800 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ===== Judul + status pengumuman ===== --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-end gap-5">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Akademik') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Kelulusan Akhir Dauroh') }}</h1>
                <p class="mt-1.5 text-sm text-white/70 max-w-2xl">{{ __('Tentukan santri yang lulus beserta predikatnya. Hasil baru terlihat di dasbor peserta setelah Anda menekan') }} <b>{{ __('Umumkan') }}</b>.</p>
            </div>

            <div class="shrink-0 rounded-2xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[260px]">
                @if ($diumumkan)
                <p class="flex items-center gap-2 text-sm font-bold text-emerald-300"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ __('Sudah diumumkan') }}</p>
                <p class="mt-0.5 text-xs text-white/60">{{ $diumumkan->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y · H:i') }}</p>
                <form method="POST" action="{{ route('admin.kelulusan.tarik') }}" class="mt-3" @submit="konfirmasi" data-konfirmasi="{{ __('Tarik pengumuman? Peserta tidak lagi bisa melihat hasil kelulusan.') }}">
                    @csrf
                    @method('DELETE')
                    <button class="w-full px-4 py-2 rounded-xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20">{{ __('Tarik Pengumuman') }}</button>
                </form>
                @else
                <p class="flex items-center gap-2 text-sm font-bold text-amber-200"><span class="w-2.5 h-2.5 rounded-full bg-amber-300"></span> {{ __('Belum diumumkan') }}</p>
                <p class="mt-0.5 text-xs text-white/60">{{ __('Peserta melihat "Belum diumumkan".') }}</p>
                <form method="POST" action="{{ route('admin.kelulusan.umumkan') }}" class="mt-3" @submit="cekUmumkan">
                    @csrf
                    <button class="w-full px-4 py-2 rounded-xl bg-amber-300 text-[#0b332c] text-sm font-bold hover:bg-amber-200">{{ __('Umumkan Kelulusan') }}</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan (ikut berubah saat mengisi) ===== --}}
    <div class="grid grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-3xl p-4 sm:p-5 bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
            <p class="text-xs font-semibold text-white/80">{{ __('Lulus') }}</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold tabular-nums" v-text="hitung.lulus"></p>
        </div>
        <div class="rounded-3xl p-4 sm:p-5 bg-gradient-to-br from-rose-500 to-pink-600 text-white">
            <p class="text-xs font-semibold text-white/80">{{ __('Tidak Lulus') }}</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold tabular-nums" v-text="hitung.tidak"></p>
        </div>
        <div class="rounded-3xl p-4 sm:p-5 bg-gradient-to-br from-slate-400 to-slate-600 text-white">
            <p class="text-xs font-semibold text-white/80">{{ __('Belum diisi') }}</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold tabular-nums" v-text="hitung.belum"></p>
        </div>
    </div>

    {{-- ===== Daftar santri ===== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-wrap items-center gap-2 p-4 sm:p-5 border-b border-slate-100">
            <input type="search" v-model.trim="cari" placeholder="{{ __('Cari nama / nomor…') }}"
                class="flex-1 min-w-[180px] rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
            <select v-model="saringKelas" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <option value="">{{ __('Semua kelas') }}</option>
                <option value="-">{{ __('Belum ada kelas') }}</option>
                <option v-for="k in daftarKelas" :key="k.id" :value="String(k.id)" v-text="k.nama"></option>
            </select>
            <select v-model="saringStatus" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <option value="">{{ __('Semua status') }}</option>
                <option value="lulus">{{ __('Lulus') }}</option>
                <option value="tidak_lulus">{{ __('Tidak lulus') }}</option>
                <option value="-">{{ __('Belum diisi') }}</option>
            </select>
        </div>

        {{-- Isi sekaligus untuk santri yang dicentang --}}
        <div v-if="dipilih.length" class="flex flex-wrap items-center gap-2 px-4 sm:px-5 py-3 bg-emerald-50 border-b border-emerald-100 text-sm">
            <span class="font-semibold text-emerald-800" v-text="dipilih.length + ' ' + @js(__('santri dicentang:'))"></span>
            <button type="button" @click="isiMassal('lulus')" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">{{ __('Tandai Lulus') }}</button>
            <button type="button" @click="isiMassal('tidak_lulus')" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-semibold hover:bg-rose-700">{{ __('Tandai Tidak Lulus') }}</button>
            <button type="button" @click="isiMassal('')" class="px-3 py-1.5 rounded-lg bg-white ring-1 ring-slate-200 text-slate-600 font-semibold hover:bg-slate-50">{{ __('Kosongkan') }}</button>
            <select @change="predikatMassal($event)" class="rounded-lg border border-emerald-200 bg-white px-2.5 py-1.5 text-sm">
                <option value="">{{ __('Predikat untuk yang lulus…') }}</option>
                <option v-for="p in daftarPredikat" :key="p" :value="p" v-text="p"></option>
            </select>
            <button type="button" @click="dipilih = []" class="ml-auto text-xs font-semibold text-slate-500 hover:text-slate-700">{{ __('Batal centang') }}</button>
        </div>

        {{-- ===== HP (di bawah lg): satu santri = satu kartu. Memakai data Vue yang sama dengan tabel (tanpa name), yang dikirim tetap bilah simpan ===== --}}
        <div class="lg:hidden p-3 space-y-2.5 bg-slate-50/60">
            <label v-if="tampil.length" class="flex items-center gap-2 px-1 text-xs font-semibold text-slate-500">
                <input type="checkbox" :checked="semuaDicentang" @change="centangSemua($event.target.checked)" class="w-4 h-4 accent-emerald-600">
                {{ __('Centang semua yang tampil') }}
            </label>
            <article v-for="s in tampil" :key="s.id" class="rounded-2xl p-4 ring-1 shadow-[0_1px_2px_rgba(16,24,40,0.04)]"
                :class="sudahDiubah(s) ? 'bg-amber-50 ring-amber-200' : 'bg-white ring-slate-200/80'">
                <div class="flex items-center gap-3">
                    <input type="checkbox" :value="s.id" v-model="dipilih" class="w-4 h-4 shrink-0 accent-emerald-600">
                    <img v-if="s.foto" :src="s.foto" :data-perbesar="s.nama" alt="" class="w-11 h-11 shrink-0 rounded-xl object-cover ring-1 ring-slate-200">
                    <span v-else class="w-11 h-11 shrink-0 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center" v-text="s.nama.charAt(0).toUpperCase()"></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800 truncate" v-text="s.nama"></p>
                        <p class="text-[11px] text-slate-400 truncate" v-text="s.nomor + (s.kelas ? ' · ' + s.kelas : '')"></p>
                    </div>
                    <span v-if="sudahDiubah(s)" class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">{{ __('Diubah') }}</span>
                </div>
                {{-- Pilihan hasil: tombolnya sama dengan di tabel --}}
                <div class="mt-3 grid grid-cols-3 rounded-xl bg-slate-100 p-0.5 text-xs font-semibold">
                    <button type="button" @click="s.status = 'lulus'" class="py-2 rounded-lg transition"
                        :class="s.status === 'lulus' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500'">{{ __('Lulus') }}</button>
                    <button type="button" @click="s.status = 'tidak_lulus'; s.predikat = ''" class="py-2 rounded-lg transition"
                        :class="s.status === 'tidak_lulus' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-500'">{{ __('Tidak Lulus') }}</button>
                    <button type="button" @click="s.status = ''; s.predikat = ''" class="py-2 rounded-lg transition"
                        :class="s.status === '' ? 'bg-white text-slate-700 shadow-sm' : 'text-slate-400'">{{ __('Belum diisi') }}</button>
                </div>
                <div class="mt-2.5 grid grid-cols-[auto_1fr] items-center gap-x-3 gap-y-2">
                    <span class="text-xs font-semibold text-slate-500">{{ __('Predikat') }}</span>
                    <select v-model="s.predikat" :disabled="s.status !== 'lulus'"
                        class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-300">
                        <option value="">—</option>
                        <option v-for="p in daftarPredikat" :key="p" :value="p" v-text="p"></option>
                    </select>
                    <span class="text-xs font-semibold text-slate-500">{{ __('Catatan') }}</span>
                    <input type="text" v-model="s.catatan" maxlength="500" placeholder="{{ __('opsional') }}" dir="auto"
                        class="w-full min-w-0 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:border-emerald-400">
                </div>
            </article>
            <p v-if="!tampil.length" class="rounded-2xl bg-white py-10 text-center text-sm text-slate-400 ring-1 ring-slate-200/80"
                v-text="santri.length ? @js(__('Tidak ada santri yang cocok.')) : @js(__('Belum ada santri yang diterima di sesi ini.'))"></p>
        </div>

        {{-- ===== Laptop (lg ke atas): tabel ===== --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-left text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="pl-5 pr-2 py-3 w-10"><input type="checkbox" :checked="semuaDicentang" @change="centangSemua($event.target.checked)" class="w-4 h-4 accent-emerald-600" title="{{ __('Centang semua yang tampil') }}"></th>
                        <th class="px-3 py-3">{{ __('Santri') }}</th>
                        <th class="px-3 py-3">{{ __('Hasil') }}</th>
                        <th class="px-3 py-3">{{ __('Predikat') }}</th>
                        <th class="px-3 py-3 min-w-[200px]">{{ __('Catatan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="s in tampil" :key="s.id" :class="sudahDiubah(s) ? 'bg-amber-50/60' : 'hover:bg-slate-50/60'">
                        <td class="pl-5 pr-2 py-3"><input type="checkbox" :value="s.id" v-model="dipilih" class="w-4 h-4 accent-emerald-600"></td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-3 min-w-[200px]">
                                <img v-if="s.foto" :src="s.foto" :data-perbesar="s.nama" alt="" class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200">
                                <span v-else class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center" v-text="s.nama.charAt(0).toUpperCase()"></span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate" v-text="s.nama"></p>
                                    <p class="text-[11px] text-slate-400" v-text="s.nomor + (s.kelas ? ' · ' + s.kelas : '')"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="inline-flex rounded-xl bg-slate-100 p-0.5 text-xs font-semibold whitespace-nowrap">
                                <button type="button" @click="s.status = 'lulus'" class="px-2.5 py-1.5 rounded-lg transition"
                                    :class="s.status === 'lulus' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-emerald-700'">{{ __('Lulus') }}</button>
                                <button type="button" @click="s.status = 'tidak_lulus'; s.predikat = ''" class="px-2.5 py-1.5 rounded-lg transition"
                                    :class="s.status === 'tidak_lulus' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-500 hover:text-rose-700'">{{ __('Tidak') }}</button>
                                <button type="button" @click="s.status = ''; s.predikat = ''" class="px-2.5 py-1.5 rounded-lg transition" title="{{ __('Belum diisi') }}"
                                    :class="s.status === '' ? 'bg-white text-slate-700 shadow-sm' : 'text-slate-400 hover:text-slate-600'">—</button>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <select v-model="s.predikat" :disabled="s.status !== 'lulus'"
                                class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-300">
                                <option value="">—</option>
                                <option v-for="p in daftarPredikat" :key="p" :value="p" v-text="p"></option>
                            </select>
                        </td>
                        <td class="px-3 py-3">
                            <input type="text" v-model="s.catatan" maxlength="500" placeholder="{{ __('opsional') }}" dir="auto"
                                class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm focus:outline-none focus:border-emerald-400">
                        </td>
                    </tr>
                    <tr v-if="!tampil.length">
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400"
                            v-text="santri.length ? @js(__('Tidak ada santri yang cocok.')) : @js(__('Belum ada santri yang diterima di sesi ini.'))"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== Bilah simpan (muncul bila ada perubahan) ===== --}}
    <form v-if="diubah.length" method="POST" action="{{ route('admin.kelulusan.simpan') }}" @submit="menyimpan = true"
        class="fixed bottom-24 lg:bottom-5 inset-x-4 md:left-auto md:right-8 md:w-[460px] z-30 flex items-center gap-3 rounded-2xl bg-[#0b332c] text-white px-4 py-3 shadow-2xl">
        @csrf
        <template v-for="s in diubah" :key="s.id">
            <input type="hidden" :name="'hasil[' + s.id + '][status]'" :value="s.status">
            <input type="hidden" :name="'hasil[' + s.id + '][predikat]'" :value="s.status === 'lulus' ? s.predikat : ''">
            <input type="hidden" :name="'hasil[' + s.id + '][catatan]'" :value="s.catatan">
        </template>
        <span class="flex-1 text-sm"><b v-text="diubah.length"></b> {{ __('perubahan belum disimpan') }}</span>
        <button type="button" @click="batalkan" class="px-3 py-2 rounded-xl text-sm font-semibold text-white/80 hover:bg-white/10">{{ __('Batal') }}</button>
        <button type="submit" :disabled="menyimpan" class="px-4 py-2 rounded-xl bg-emerald-400 text-[#0b332c] text-sm font-bold hover:bg-emerald-300 disabled:opacity-60"
            v-text="menyimpan ? @js(__('Menyimpan…')) : @js(__('Simpan'))"></button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const AWAL = @json($santri);

        Vue.createApp({
            data() {
                return {
                    santri: AWAL.map((s) => ({ ...s })), // salinan yang bisa diubah; AWAL = data tersimpan
                    daftarKelas: @json($kelas),
                    daftarPredikat: @json($predikat),
                    cari: '',
                    saringKelas: '',
                    saringStatus: '',
                    dipilih: [],
                    menyimpan: false,
                };
            },
            computed: {
                tampil() {
                    const kata = this.cari.toLowerCase();
                    return this.santri.filter((s) =>
                        (!kata || s.nama.toLowerCase().includes(kata) || String(s.nomor).toLowerCase().includes(kata)) &&
                        (this.saringKelas === '' || (this.saringKelas === '-' ? !s.kelas_id : String(s.kelas_id) === this.saringKelas)) &&
                        (this.saringStatus === '' || (this.saringStatus === '-' ? s.status === '' : s.status === this.saringStatus)));
                },
                hitung() {
                    return {
                        lulus: this.santri.filter((s) => s.status === 'lulus').length,
                        tidak: this.santri.filter((s) => s.status === 'tidak_lulus').length,
                        belum: this.santri.filter((s) => s.status === '').length,
                    };
                },
                // Baris yang berbeda dari data tersimpan -> dikirim saat Simpan
                diubah() {
                    return this.santri.filter((s) => this.sudahDiubah(s));
                },
                semuaDicentang() {
                    return this.tampil.length > 0 && this.tampil.every((s) => this.dipilih.includes(s.id));
                },
            },
            methods: {
                ...metodeHalaman,
                sudahDiubah(s) {
                    const a = AWAL.find((x) => x.id === s.id);
                    return a.status !== s.status || a.predikat !== s.predikat || a.catatan !== s.catatan;
                },
                centangSemua(nyala) {
                    const id = this.tampil.map((s) => s.id);
                    this.dipilih = nyala ? [...new Set([...this.dipilih, ...id])] : this.dipilih.filter((x) => !id.includes(x));
                },
                isiMassal(status) {
                    this.santri.forEach((s) => {
                        if (!this.dipilih.includes(s.id)) return;
                        s.status = status;
                        if (status !== 'lulus') s.predikat = '';
                    });
                },
                predikatMassal(e) {
                    const p = e.target.value;
                    this.santri.forEach((s) => { if (this.dipilih.includes(s.id) && s.status === 'lulus') s.predikat = p; });
                    e.target.value = '';
                },
                batalkan() {
                    this.santri = AWAL.map((s) => ({ ...s }));
                },
                // Ingatkan bila masih ada perubahan belum disimpan / santri belum diisi sebelum mengumumkan
                cekUmumkan(e) {
                    let pesan = @js(__('Umumkan kelulusan sekarang? Peserta langsung bisa melihat hasilnya.'));
                    if (this.diubah.length) pesan = @js(__('Ada :jumlah perubahan BELUM disimpan dan tidak ikut diumumkan.')).replace(':jumlah', this.diubah.length) + '\n\n' + pesan;
                    if (this.hitung.belum) pesan = this.hitung.belum + ' ' + @js(__('santri belum diisi hasilnya (mereka akan melihat "hasil belum tersedia").')) + '\n\n' + pesan;
                    if (!confirm(pesan)) e.preventDefault();
                },
            },
            mounted() {
                window.addEventListener('beforeunload', (e) => { if (this.diubah.length && !this.menyimpan) e.preventDefault(); });
            },
        }).mount('#halaman-kelulusan');
    });
</script>
@endpush
