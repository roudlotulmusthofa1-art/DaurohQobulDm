{{--
    Admin > Sistem > Daftar User
    Semua akun login beserta role-nya. Pencarian, saring role, urutan & halaman diatur Vue (tanpa muat ulang).
    Super Admin bisa mengganti role pengurus akun admin lewat pop-up.
--}}
@extends('layouts.admin')

@section('title', __('Daftar User'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div id="halaman-user" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10">
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl ring-1 ring-rose-200 bg-rose-50 text-rose-800 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ===== Judul + kartu jumlah per role (klik = saring) ===== --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Sistem') }}</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Daftar User') }}</h1>
            <p class="mt-1.5 text-sm text-white/70">{{ __('Semua akun yang bisa login. Akun admin & asatid berlaku di semua sesi; akun peserta dari sesi yang sedang aktif.') }}</p>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <button v-for="t in tab" :key="t.kunci" type="button" @click="saring = t.kunci; halaman = 1"
                    class="text-left rounded-2xl px-4 py-3 ring-1 transition-colors"
                    :class="saring === t.kunci ? 'bg-white text-[#0b332c] ring-white' : 'bg-white/10 ring-white/15 hover:bg-white/15'">
                    <span class="block text-2xl font-extrabold tabular-nums" v-text="jumlah[t.kunci]"></span>
                    <span class="block text-xs font-semibold" :class="saring === t.kunci ? 'text-[#0b332c]/70' : 'text-white/70'" v-text="t.label"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-wrap items-center gap-3 p-4 sm:p-5 border-b border-slate-100">
            <label class="relative flex-1 min-w-[220px]">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="search" v-model.trim="cari" @input="halaman = 1" placeholder="{{ __('Cari nama, email, atau role…') }}"
                    class="w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
            </label>
            <select v-model="urut" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <option value="nama">{{ __('Urut nama A–Z') }}</option>
                <option value="baru">{{ __('Terbaru dulu') }}</option>
                <option value="lama">{{ __('Terlama dulu') }}</option>
            </select>
        </div>

        {{-- ===== HP (di bawah lg): satu user = satu kartu ===== --}}
        <div class="lg:hidden space-y-2.5 p-3 bg-slate-50/60">
            <article v-for="u in tampil" :key="u.id" class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-[0_1px_2px_rgba(16,24,40,0.04)]">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center text-sm font-bold ring-1" :class="gaya(u.warna)" v-text="inisial(u.nama)"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-1 min-w-0">
                            <span class="font-bold text-slate-800 truncate" v-text="u.nama"></span>
                            <span v-if="u.saya" class="shrink-0 text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold">{{ __('Anda') }}</span>
                        </span>
                        <span class="block text-[11px] text-slate-400 truncate" v-text="u.email"></span>
                    </span>
                    <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold ring-1" :class="gaya(u.warna)">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        <span v-text="u.role"></span>
                    </span>
                </div>
                <div v-if="u.daftar" class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        <span v-text="u.daftar"></span>
                    </span>
                </div>
                {{-- Tombol aksi: sama dengan kolom Aksi di tabel --}}
                <div v-if="(bisaUbah && u.dasar === 'admin' && !u.saya) || (bisaLihat && (u.dasar === 'asatid' || u.dasar === 'siswa')) || u.detail" class="mt-3 flex gap-2">
                    <button v-if="bisaUbah && u.dasar === 'admin' && !u.saya" type="button" @click="bukaUbah(u)"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-sky-50 py-2 text-sm font-semibold text-sky-700 active:scale-95 transition">{{ __('Ganti Role') }}</button>
                    <button v-if="bisaLihat && (u.dasar === 'asatid' || u.dasar === 'siswa')" type="button" @click="masukSebagai(u)"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-50 py-2 text-sm font-semibold text-amber-800 active:scale-95 transition">{{ __('Masuk sebagai') }}</button>
                    <a v-if="u.detail" :href="u.detail" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-50 py-2 text-sm font-semibold text-teal-700 active:scale-95 transition">{{ __('Lihat Data') }}</a>
                </div>
            </article>
            <p v-if="!tampil.length" class="rounded-2xl bg-white py-10 text-center text-sm text-slate-400 ring-1 ring-slate-200/80">{{ __('Tidak ada user yang cocok.') }}</p>
        </div>

        {{-- ===== Laptop (lg ke atas): tabel ===== --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('Nama') }}</th>
                        <th class="px-5 py-3">{{ __('Role') }}</th>
                        <th class="px-5 py-3 hidden md:table-cell">{{ __('Terdaftar') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="u in tampil" :key="u.id" class="hover:bg-slate-50/60">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold ring-1 shrink-0" :class="gaya(u.warna)" v-text="inisial(u.nama)"></span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate">
                                        <span v-text="u.nama"></span>
                                        <span v-if="u.saya" class="ml-1 text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold">{{ __('Anda') }}</span>
                                    </p>
                                    <p class="text-xs text-slate-500 truncate" v-text="u.email"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1" :class="gaya(u.warna)">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span v-text="u.role"></span>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 hidden md:table-cell" v-text="u.daftar ?? '–'"></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button v-if="bisaUbah && u.dasar === 'admin' && !u.saya" type="button" @click="bukaUbah(u)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-[#0b332c] bg-[#0b332c]/[0.06] hover:bg-[#0b332c]/[0.12]">{{ __('Ganti Role') }}</button>
                                <button v-if="bisaLihat && (u.dasar === 'asatid' || u.dasar === 'siswa')" type="button" @click="masukSebagai(u)"
                                    title="{{ __('Buka dasbor akun ini (mode lihat)') }}"
                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200">{{ __('Masuk sebagai') }}</button>
                                <a v-if="u.detail" :href="u.detail" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">{{ __('Lihat Data') }}</a>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!tampil.length">
                        <td colspan="4" class="px-5 py-12 text-center text-slate-400">{{ __('Tidak ada user yang cocok.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Halaman --}}
        <div v-if="jumlahHalaman > 1" class="flex flex-wrap items-center gap-2 px-5 py-4 border-t border-slate-100 text-sm">
            <span class="text-slate-500 flex-1" v-text="@js(__('Menampilkan :awal–:akhir dari :total user')).replace(':awal', awal + 1).replace(':akhir', Math.min(awal + PER_HALAMAN, tersaring.length)).replace(':total', tersaring.length)"></span>
            <button type="button" @click="halaman--" :disabled="halaman === 1" class="px-3 py-1.5 rounded-xl ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40">{{ __('Sebelumnya') }}</button>
            <span class="px-2 font-semibold text-slate-700" v-text="halaman + ' / ' + jumlahHalaman"></span>
            <button type="button" @click="halaman++" :disabled="halaman === jumlahHalaman" class="px-3 py-1.5 rounded-xl ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40">{{ __('Berikutnya') }}</button>
        </div>
    </div>

    {{-- Form "Masuk sebagai" (action diisi Vue) --}}
    <form ref="formMasuk" method="POST" class="hidden">@csrf</form>

    {{-- ===== Pop-up ganti role akun admin ===== --}}
    <dialog ref="dialogRole" @click.self="tutupDialog" class="m-auto w-[min(440px,calc(100%-2rem))] rounded-3xl p-0 backdrop:bg-black/40">
        <form v-if="dipilih" method="POST" :action="URL_USER + '/' + dipilih.id + '/role'" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div>
                <h2 class="text-lg font-bold text-slate-800">{{ __('Ganti Role') }}</h2>
                <p class="text-sm text-slate-500"><span v-text="dipilih.nama"></span> · <span v-text="dipilih.email"></span></p>
            </div>
            <div class="space-y-2">
                <label v-for="r in roleAdmin" :key="r.id ?? 'super'"
                    class="flex items-center gap-3 p-3 rounded-2xl ring-1 cursor-pointer transition-colors"
                    :class="pilihanRole === r.id ? 'ring-emerald-500 bg-emerald-50/60' : 'ring-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="role_id" :value="r.id ?? ''" :checked="pilihanRole === r.id" @change="pilihanRole = r.id" class="accent-emerald-600">
                    <span class="w-2.5 h-2.5 rounded-full" :class="titik(r.warna)"></span>
                    <span class="font-semibold text-slate-800" v-text="r.nama"></span>
                    <span v-if="r.id === null" class="text-xs text-slate-500">{{ __('semua menu') }}</span>
                </label>
            </div>
            <p class="text-xs text-slate-500">{{ __('Menu yang boleh dibuka tiap role diatur di') }} <a href="{{ route('admin.role.index') }}" class="font-semibold text-emerald-700 hover:underline">{{ __('Role & Permission') }}</a>.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="tutupDialog" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ __('Batal') }}</button>
                <button type="submit" :disabled="pilihanRole === dipilih.role_id"
                    class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-40 disabled:cursor-not-allowed">{{ __('Simpan') }}</button>
            </div>
        </form>
    </dialog>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Kelas Tailwind ditulis lengkap supaya ikut dibuat oleh Tailwind
        const WARNA = {
            emerald: ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
            sky:     ['bg-sky-50 text-sky-700 ring-sky-200',             'bg-sky-500'],
            amber:   ['bg-amber-50 text-amber-700 ring-amber-200',       'bg-amber-500'],
            violet:  ['bg-violet-50 text-violet-700 ring-violet-200',    'bg-violet-500'],
            rose:    ['bg-rose-50 text-rose-700 ring-rose-200',          'bg-rose-500'],
            indigo:  ['bg-indigo-50 text-indigo-700 ring-indigo-200',    'bg-indigo-500'],
            teal:    ['bg-teal-50 text-teal-700 ring-teal-200',          'bg-teal-500'],
            slate:   ['bg-slate-100 text-slate-700 ring-slate-200',      'bg-slate-500'],
        };

        Vue.createApp({
            data() {
                return {
                    users: @json($users),
                    roleAdmin: @json($roleAdmin),
                    bisaUbah: @json($bisaUbah),
                    bisaLihat: @json($bisaLihat),
                    URL_MASUK: @json(url('admin/masuk-sebagai')),
                    URL_USER: @json(url('admin/user')),
                    PER_HALAMAN: 15,
                    tab: [
                        { kunci: 'semua', label: @js(__('Semua User')) },
                        { kunci: 'admin', label: @js(__('Admin')) },
                        { kunci: 'asatid', label: @js(__('Asatid')) },
                        { kunci: 'siswa', label: @js(__('Peserta')) },
                    ],
                    saring: 'semua',
                    cari: '',
                    urut: 'nama',
                    halaman: 1,
                    dipilih: null,       // akun yang sedang diganti role-nya
                    pilihanRole: null,
                };
            },
            computed: {
                // Jumlah akun per tab: { semua: 40, admin: 3, ... }
                jumlah() {
                    const n = { semua: this.users.length, admin: 0, asatid: 0, siswa: 0 };
                    this.users.forEach(u => { if (u.dasar in n) n[u.dasar]++; });
                    return n;
                },
                tersaring() {
                    const kata = this.cari.toLowerCase();
                    const hasil = this.users.filter(u =>
                        (this.saring === 'semua' || u.dasar === this.saring) &&
                        (!kata || [u.nama, u.email, u.role].some(t => (t ?? '').toLowerCase().includes(kata))));

                    const banding = {
                        nama: (a, b) => a.nama.localeCompare(b.nama, 'id'),
                        baru: (a, b) => b.urut - a.urut,
                        lama: (a, b) => a.urut - b.urut,
                    }[this.urut];

                    return hasil.sort(banding);
                },
                jumlahHalaman() {
                    return Math.max(1, Math.ceil(this.tersaring.length / this.PER_HALAMAN));
                },
                awal() {
                    return (this.halaman - 1) * this.PER_HALAMAN;
                },
                tampil() {
                    return this.tersaring.slice(this.awal, this.awal + this.PER_HALAMAN);
                },
            },
            methods: {
                ...metodeHalaman,
                gaya(warna) {
                    return (WARNA[warna] ?? WARNA.slate)[0];
                },
                titik(warna) {
                    return (WARNA[warna] ?? WARNA.slate)[1];
                },
                inisial(nama) {
                    return (nama ?? '?').split(/\s+/).filter(Boolean).slice(0, 2).map(k => k[0]).join('').toUpperCase();
                },
                // Super Admin: buka dasbor akun asatid/siswa (mode lihat)
                masukSebagai(u) {
                    if (!confirm(@js(__('Buka dasbor :nama? (mode lihat, perubahan tidak disimpan)')).replace(':nama', u.nama))) return;
                    const f = this.$refs.formMasuk;
                    f.action = this.URL_MASUK + '/' + u.id;
                    f.submit();
                },
                bukaUbah(u) {
                    this.dipilih = u;
                    this.pilihanRole = u.role_id;
                    this.$refs.dialogRole.showModal();
                },
            },
            watch: {
                // Saringan berganti -> kembali ke halaman 1
                saring() { this.halaman = 1; },
                urut() { this.halaman = 1; },
            },
        }).mount('#halaman-user');
    });
</script>
@endpush
