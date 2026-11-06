{{--
    Akun Asatid > Absensi Kegiatan (langkah 2: mencatat kehadiran)
    Tab "Pindai QR": kamera HP memindai kartu QR peserta, status otomatis menurut jam.
    Tab "Cari Nama": cari peserta lalu pilih keterangan.
    Setiap peserta langsung tersimpan ke server.
--}}
@extends('layouts.asatid')

@section('judul', __('Absensi') . ' ' . ($absensi->kegiatan?->nama ?? __('Kegiatan')))

@push('styles')
<style>
    [v-cloak] { display: none; }
    #pembaca video { border-radius: 1.25rem; object-fit: cover; }
    #pembaca { border: 0 !important; }
    @keyframes muncul { from { transform: translateY(16px) scale(.96); opacity: 0; } to { transform: none; opacity: 1; } }
    .anim-muncul { animation: muncul .25s cubic-bezier(.2,.8,.2,1) both; }
    @keyframes pindai { 0%, 100% { top: 12%; } 50% { top: 84%; } }
    .garis-pindai { animation: pindai 2.4s ease-in-out infinite; }
</style>
@endpush

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$info = [
    'nama' => $absensi->kegiatan?->nama ?? __('Kegiatan'),
    'tanggal' => $absensi->tanggal->locale('id')->translatedFormat('l, d F Y'),
    'hijriyah' => $absensi->tanggal_hijriyah,
    'jam_mulai' => substr((string) $absensi->jam_mulai, 0, 5),
    'batas_terlambat' => substr((string) $absensi->batas_terlambat, 0, 5),
    'batas_alfa' => substr((string) $absensi->batas_alfa, 0, 5),
    'metode' => $absensi->metode,
    'bersama' => (bool) $absensi->dari_admin, // dibuat admin: dicatat bersama semua asatid
];
@endphp

<div id="absen-kegiatan" v-cloak class="space-y-5">
    <!-- ===== Kepala: kegiatan, jam, status sekarang ===== -->
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 via-teal-600 to-[#0b332c] text-white p-5 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-10 w-72 h-72 rounded-full bg-lime-300/25 blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-end gap-5">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-100/80"
                    v-text="info.bersama ? @js(__('Absensi bersama · dari admin')) : @js(__('Absensi kegiatan berjalan'))"></p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight truncate" v-text="info.nama"></h2>
                <p class="mt-1 text-sm text-white/75" v-text="info.tanggal + ' · ' + info.hijriyah"></p>
                <p v-if="info.bersama" class="mt-1 text-xs text-emerald-100/90">{{ __('Catatan dari asatid lain ikut muncul otomatis setiap 10 detik.') }}</p>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5">{{ __('Mulai') }} <b v-text="info.jam_mulai"></b></span>
                    <span class="rounded-full bg-amber-300/20 ring-1 ring-amber-200/30 px-3 py-1.5 text-amber-50">{{ __('Terlambat') }} <b v-text="info.batas_terlambat"></b></span>
                    <span class="rounded-full bg-rose-300/20 ring-1 ring-rose-200/30 px-3 py-1.5 text-rose-50">{{ __('Alfa') }} <b v-text="info.batas_alfa"></b></span>
                </div>
            </div>
            <div class="shrink-0 rounded-3xl bg-white/10 ring-1 ring-white/15 backdrop-blur px-5 py-4 text-center">
                <span class="block text-3xl font-extrabold tabular-nums tracking-tight" v-text="jam"></span>
                <span class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-bold rounded-full px-2.5 py-1 bg-white" :class="gaya(statusSekarang).teks">
                    <span class="w-1.5 h-1.5 rounded-full" :class="gaya(statusSekarang).titik"></span>
                    <span v-text="@js(__('Datang sekarang:')) + ' ' + gaya(statusSekarang).label"></span>
                </span>
            </div>
        </div>
    </section>

    <!-- ===== Hitungan ===== -->
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
        <div :class="kartu" class="p-3 text-center">
            <span class="block text-2xl font-extrabold text-slate-800" v-text="tercatat + '/' + peserta.length"></span>
            <span class="block text-[11px] text-slate-500">{{ __('Tercatat') }}</span>
        </div>
        <div v-for="s in status" :key="s.kunci" :class="kartu" class="p-3 text-center">
            <span class="block text-2xl font-extrabold" :class="s.teks" v-text="hitung[s.kunci]"></span>
            <span class="block text-[11px] text-slate-500" v-text="s.label"></span>
        </div>
    </div>

    <!-- ===== Tab ===== -->
    <div class="p-1.5 rounded-2xl bg-white ring-1 ring-slate-200/70 grid grid-cols-2 gap-1.5">
        <button type="button" @click="gantiTab('qr')" class="py-2.5 rounded-xl text-sm font-bold transition-all"
            :class="tab === 'qr' ? 'bg-[#0b332c] text-white shadow' : 'text-slate-500 hover:bg-slate-50'">{{ __('Pindai QR') }}</button>
        <button type="button" @click="gantiTab('manual')" class="py-2.5 rounded-xl text-sm font-bold transition-all"
            :class="tab === 'manual' ? 'bg-[#0b332c] text-white shadow' : 'text-slate-500 hover:bg-slate-50'">{{ __('Cari Nama') }}</button>
    </div>

    <!-- ===== Tab Pindai QR ===== -->
    <div v-show="tab === 'qr'" class="grid lg:grid-cols-5 gap-5">
        <section :class="kartu" class="lg:col-span-3 p-4 sm:p-5">
            <div class="relative aspect-square sm:aspect-[4/3] rounded-[1.25rem] bg-[#071f1a] overflow-hidden">
                <div id="pembaca" class="absolute inset-0"></div>

                <!-- Bingkai pindai -->
                <div v-if="kameraNyala" class="pointer-events-none absolute inset-[12%] rounded-3xl ring-2 ring-white/70">
                    <div class="garis-pindai absolute left-3 right-3 h-0.5 bg-emerald-400 shadow-[0_0_16px_4px_rgba(52,211,153,0.7)]"></div>
                </div>

                <!-- Kamera mati -->
                <div v-if="!kameraNyala" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 text-white">
                    <span class="w-16 h-16 rounded-3xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center">
                        <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                    </span>
                    <p class="mt-3 font-bold">{{ __('Kamera belum menyala') }}</p>
                    <p class="mt-1 text-xs text-white/60 max-w-xs" v-text="galatKamera || @js(__('Arahkan kamera ke kartu QR peserta. Status otomatis sesuai jam datang.'))"></p>
                    <button type="button" @click="nyalakan" :disabled="memuatKamera"
                        class="mt-4 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-400 text-[#071f1a] text-sm font-extrabold hover:bg-emerald-300 disabled:opacity-60">
                        <svg v-if="memuatKamera" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        <span v-text="memuatKamera ? @js(__('Menyalakan…')) : @js(__('Nyalakan Kamera'))"></span>
                    </button>
                </div>

                <!-- Hasil pindai -->
                <div v-if="hasil" :key="hasil.kunci" class="anim-muncul absolute inset-x-3 bottom-3 rounded-2xl bg-white p-3 flex items-center gap-3 shadow-2xl">
                    <img :data-perbesar="hasil.siswa.nama" v-if="hasil.siswa.foto" :src="hasil.siswa.foto" alt="" class="w-14 h-14 rounded-2xl object-cover">
                    <span v-else class="w-14 h-14 shrink-0 rounded-2xl bg-slate-100 text-slate-500 text-xl font-extrabold flex items-center justify-center" v-text="inisial(hasil.siswa.nama)"></span>
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-slate-800 truncate" v-text="hasil.siswa.nama"></p>
                        <p class="text-xs text-slate-500 truncate" v-text="hasil.ulang ? @js(__('Sudah tercatat sebelumnya')) : @js(__('Tercatat pukul')) + ' ' + hasil.siswa.waktu"></p>
                    </div>
                    <span class="shrink-0 px-3 py-2 rounded-xl text-sm font-extrabold" :class="gaya(hasil.siswa.status).aktif" v-text="gaya(hasil.siswa.status).label"></span>
                </div>
                <div v-if="galatPindai" class="anim-muncul absolute inset-x-3 bottom-3 rounded-2xl bg-rose-600 text-white p-3 text-sm font-semibold" v-text="galatPindai"></div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <form @submit.prevent="kirimKode" class="flex-1 flex items-center gap-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 pl-4 pr-1.5 py-1.5">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v8.25A2.25 2.25 0 006 16.5h2.25m8.25-8.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-7.5A2.25 2.25 0 018.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 00-2.25 2.25v6"/></svg>
                    <input v-model="kodeManual" type="text" placeholder="{{ __('Ketik kode / nomor pendaftaran, atau pakai alat scanner') }}"
                        class="flex-1 min-w-0 bg-transparent border-0 p-0 text-sm outline-none focus:ring-0">
                    <button type="submit" class="shrink-0 px-3.5 py-2 rounded-xl bg-[#0b332c] text-white text-xs font-bold">{{ __('Catat') }}</button>
                </form>
                <button v-if="kameraNyala" type="button" @click="matikan" class="px-4 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Matikan kamera') }}</button>
            </div>
        </section>

        <!-- Baru tercatat -->
        <section :class="kartu" class="lg:col-span-2 p-4 sm:p-5">
            <h3 class="font-bold text-slate-800">{{ __('Baru tercatat') }}</h3>
            <div v-if="terakhir.length" class="mt-3 space-y-2">
                <div v-for="p in terakhir" :key="p.id" class="flex items-center gap-3 p-2.5 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100">
                    <img :data-perbesar="p.nama" v-if="p.foto" :src="p.foto" alt="" class="w-10 h-10 rounded-xl object-cover">
                    <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(p.nama)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-semibold text-slate-800 truncate" v-text="p.nama"></span>
                        <span class="block text-[11px] text-slate-400" v-text="[p.waktu, p.oleh ? @js(__('oleh')) + ' ' + p.oleh : ''].filter(Boolean).join(' · ')"></span>
                    </span>
                    <span class="shrink-0 text-[11px] font-bold rounded-full px-2.5 py-1 ring-1" :class="gaya(p.status).lembut" v-text="gaya(p.status).label"></span>
                </div>
            </div>
            <p v-else class="mt-6 mb-4 text-center text-sm text-slate-400">{{ __('Belum ada peserta yang tercatat.') }}</p>
        </section>
    </div>

    <!-- ===== Tab Cari Nama ===== -->
    <section v-show="tab === 'manual'" :class="kartu" class="p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row gap-2">
            <label class="flex-1 flex items-center gap-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 px-4 py-3 focus-within:ring-2 focus-within:ring-emerald-500">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input v-model="cari" type="search" placeholder="{{ __('Cari nama atau nomor peserta…') }}" class="flex-1 min-w-0 bg-transparent border-0 p-0 text-sm outline-none focus:ring-0">
            </label>
            <div class="flex gap-1.5">
                <button v-for="f in [['belum',@js(__('Belum'))],['sudah',@js(__('Sudah'))],['semua',@js(__('Semua'))]]" :key="f[0]" type="button" @click="saring = f[0]"
                    class="px-4 py-2.5 rounded-2xl text-sm font-semibold ring-1 transition-all"
                    :class="saring === f[0] ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'" v-text="f[1]"></button>
            </div>
        </div>

        <div class="mt-4 divide-y divide-slate-100">
            <div v-for="p in daftar" :key="p.id" class="py-3">
                <button type="button" @click="buka = buka === p.id ? null : p.id" class="w-full flex items-center gap-3 text-left">
                    <img v-if="p.foto" :src="p.foto" alt="" class="w-11 h-11 rounded-2xl object-cover ring-1 ring-slate-200">
                    <span v-else class="w-11 h-11 shrink-0 rounded-2xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(p.nama)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-semibold text-slate-800 truncate" v-text="p.nama"></span>
                        <span class="block text-[11px] text-slate-400 truncate" v-text="[p.nomor, p.kelas, p.oleh ? @js(__('dicatat')) + ' ' + p.oleh : ''].filter(Boolean).join(' · ')"></span>
                    </span>
                    <span v-if="p.status" class="shrink-0 text-[11px] font-bold rounded-full px-2.5 py-1 ring-1" :class="gaya(p.status).lembut" v-text="gaya(p.status).label + (p.waktu ? ' · ' + p.waktu : '')"></span>
                    <span v-else class="shrink-0 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-slate-50 text-slate-400 ring-1 ring-slate-200">{{ __('Belum') }}</span>
                </button>
                <div v-if="buka === p.id" class="anim-muncul mt-3 grid grid-cols-5 gap-1.5">
                    <button v-for="s in status" :key="s.kunci" type="button" @click="kirim({ siswa_id: p.id, status: s.kunci })" :disabled="mengirim"
                        class="relative py-3 rounded-2xl text-xs font-bold ring-1 transition-all active:scale-95"
                        :class="p.status === s.kunci ? s.aktif : s.lembut">
                        <span v-if="s.kunci === statusSekarang && !p.status" class="absolute -top-2 left-1/2 -translate-x-1/2 text-[9px] font-extrabold uppercase tracking-wider bg-[#0b332c] text-white rounded-full px-1.5 py-0.5">{{ __('Saran') }}</span>
                        <span v-text="s.label"></span>
                    </button>
                </div>
            </div>
            <p v-if="!daftar.length" class="py-10 text-center text-sm text-slate-400" v-text="saring === 'belum' && !cari ? @js(__('Semua peserta sudah tercatat.')) : @js(__('Peserta tidak ditemukan.'))"></p>
            <p v-if="lebih" class="pt-3 text-center text-xs text-slate-400" v-text="@js(__('Menampilkan 60 dari :jumlah peserta. Ketik nama untuk mempersempit.')).replace(':jumlah', jumlahSaring)"></p>
        </div>
    </section>

    {{-- ===== Tutup absensi ===== --}}
    @if ($absensi->dari_admin)
    <div class="{{ $kartu }} p-4 sm:p-5 flex items-start gap-3">
        <span class="w-9 h-9 shrink-0 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        </span>
        <p class="text-sm text-slate-500">{{ __('Setiap catatan langsung tersimpan. Absensi ini ditutup oleh admin, atau otomatis saat batas alfa lewat; saat ditutup, peserta yang belum tercatat') }} (<b class="text-slate-800" v-text="belum"></b>) {{ __('dihitung') }} <b class="text-rose-600">{{ __('Alfa') }}</b>.</p>
    </div>
    @else
    <form method="POST" action="{{ route('asatid.absensi.kegiatan.selesai', $absensi) }}"
        @submit="konfirmasiTutup"
        class="{{ $kartu }} p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-3">
        @csrf
        <p class="flex-1 text-sm text-slate-500">{{ __('Sudah selesai? Tutup absensi. Peserta yang belum tercatat') }} (<b class="text-slate-800" v-text="belum"></b>) {{ __('otomatis ditandai') }} <b class="text-rose-600">{{ __('Alfa') }}</b>.</p>
        <button type="submit" class="px-5 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-extrabold hover:bg-[#135347] shadow-[0_12px_28px_-12px_rgba(11,51,44,0.6)]">{{ __('Tutup & Simpan') }}</button>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { createApp } = window.Vue;

    const STATUS = [
        { kunci: 'hadir',     label: @js(__('Hadir')),     aktif: 'bg-emerald-500 text-white ring-emerald-500', lembut: 'bg-emerald-50 text-emerald-700 ring-emerald-200 hover:bg-emerald-100', teks: 'text-emerald-600', titik: 'bg-emerald-500' },
        { kunci: 'terlambat', label: @js(__('Terlambat')), aktif: 'bg-amber-500 text-white ring-amber-500',     lembut: 'bg-amber-50 text-amber-700 ring-amber-200 hover:bg-amber-100',       teks: 'text-amber-600',   titik: 'bg-amber-500' },
        { kunci: 'sakit',     label: @js(__('Sakit')),     aktif: 'bg-sky-500 text-white ring-sky-500',         lembut: 'bg-sky-50 text-sky-700 ring-sky-200 hover:bg-sky-100',               teks: 'text-sky-600',     titik: 'bg-sky-500' },
        { kunci: 'izin',      label: @js(__('Izin')),      aktif: 'bg-violet-500 text-white ring-violet-500',   lembut: 'bg-violet-50 text-violet-700 ring-violet-200 hover:bg-violet-100',   teks: 'text-violet-600',  titik: 'bg-violet-500' },
        { kunci: 'alfa',      label: @js(__('Alfa')),      aktif: 'bg-rose-500 text-white ring-rose-500',       lembut: 'bg-rose-50 text-rose-700 ring-rose-200 hover:bg-rose-100',           teks: 'text-rose-600',    titik: 'bg-rose-500' },
    ];
    const dua = (n) => String(n).padStart(2, '0');
    const jamSekarang = () => { const d = new Date(); return `${dua(d.getHours())}:${dua(d.getMinutes())}`; };

    // Bunyi "bip" singkat setelah pindai berhasil
    let audio;
    const bip = (frek = 880) => {
        try {
            audio ??= new (window.AudioContext || window.webkitAudioContext)();
            const o = audio.createOscillator(), g = audio.createGain();
            o.frequency.value = frek; g.gain.value = 0.08;
            o.connect(g); g.connect(audio.destination);
            o.start(); o.stop(audio.currentTime + 0.12);
        } catch (e) {}
        navigator.vibrate?.(60);
    };

    createApp({
        data: () => ({
            kartu: @json($kartu),
            info: @json($info),
            peserta: @json($peserta),
            status: STATUS,
            tab: @json($info['metode'] === 'manual' ? 'manual' : 'qr'),
            jam: '',
            cari: '',
            saring: 'belum',
            buka: null,
            mengirim: false,
            terakhir: [],
            kodeManual: '',
            hasil: null,
            galatPindai: '',
            kameraNyala: false,
            memuatKamera: false,
            galatKamera: '',
        }),
        computed: {
            statusSekarang() {
                const j = this.jam.slice(0, 5);
                if (j < this.info.batas_terlambat) return 'hadir';
                if (j >= this.info.batas_alfa) return 'alfa';
                return 'terlambat';
            },
            hitung() {
                const h = {};
                STATUS.forEach((s) => { h[s.kunci] = this.peserta.filter((p) => p.status === s.kunci).length; });
                return h;
            },
            tercatat() { return this.peserta.filter((p) => p.status).length; },
            belum() { return this.peserta.length - this.tercatat; },
            tersaring() {
                const kata = this.cari.trim().toLowerCase();
                return this.peserta.filter((p) =>
                    (this.saring === 'semua' || (this.saring === 'belum' ? !p.status : !!p.status)) &&
                    (!kata || p.nama.toLowerCase().includes(kata) || (p.nomor || '').toLowerCase().includes(kata) || (p.arab || '').includes(kata)));
            },
            jumlahSaring() { return this.tersaring.length; },
            daftar() { return this.tersaring.slice(0, 60); },
            lebih() { return this.tersaring.length > 60; },
        },
        mounted() {
            const detak = () => {
                const d = new Date();
                this.jam = `${dua(d.getHours())}:${dua(d.getMinutes())}:${dua(d.getSeconds())}`;
                // Tepat saat batas alfa tiba: tanya server, absensi akan ditutup otomatis
                if (!this._cekBatas && this.info.batas_alfa && this.jam.slice(0, 5) >= this.info.batas_alfa) {
                    this._cekBatas = true;
                    this.segarkan();
                }
            };
            detak();
            setInterval(detak, 1000);
            window.addEventListener('pagehide', this.matikan);
            // Ambil catatan terbaru (dari asatid lain juga) tiap 10 detik, saat halaman sedang dilihat
            this._segar = setInterval(() => { if (!document.hidden) this.segarkan(); }, 10000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.segarkan(); });
        },
        methods: {
            gaya: (k) => STATUS.find((s) => s.kunci === k) || {},
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),

            konfirmasiTutup(e) {
                const tanya = this.belum
                    ? @js(__(':jumlah peserta yang belum tercatat akan ditandai Alfa. Tutup absensi sekarang?')).replace(':jumlah', this.belum)
                    : @js(__('Tutup absensi sekarang?'));
                if (!confirm(tanya)) e.preventDefault();
                else this.matikan();
            },

            /** Samakan status peserta dengan server (catatan asatid lain ikut tampil) */
            async segarkan() {
                try {
                    const res = await fetch(@json(route('asatid.absensi.kegiatan.status', $absensi)), { headers: { Accept: 'application/json' }, cache: 'no-store' });
                    if (!res.ok) return;
                    const json = await res.json();
                    let baru = 0;
                    this.peserta.forEach((p) => {
                        const d = json.tercatat[p.id];
                        if (!d) return;
                        if (!p.status) baru++;
                        p.status = d.status; p.waktu = d.waktu; p.oleh = d.oleh;
                    });
                    if (json.ditutup) {
                        this.selesaiDitutup(json.lewat_batas);
                    } else if (!json.aktif && !this._infoNonaktif) {
                        this._infoNonaktif = true;
                        window.notifikasi?.peringatan(@js(__('Admin menonaktifkan absensi ini sementara. Catatan baru belum bisa disimpan.')));
                    }
                } catch (e) {}
            },

            /** Absensi sudah ditutup (oleh admin atau karena lewat batas alfa): buka halaman hasil */
            selesaiDitutup(lewatBatas) {
                if (this._ditutup) return;
                this._ditutup = true;
                clearInterval(this._segar);
                this.matikan();
                window.notifikasi?.info(lewatBatas
                    ? @js(__('Waktu absensi habis (lewat batas alfa). Peserta yang belum tercatat dihitung Alfa. Membuka hasilnya…'))
                    : @js(__('Absensi ini sudah ditutup admin. Membuka hasilnya…')));
                setTimeout(() => location.reload(), 1500);
            },

            gantiTab(t) {
                this.tab = t;
                if (t !== 'qr') this.matikan();
            },

            /** Kirim satu catatan ke server: { kode } atau { siswa_id, status } */
            async kirim(isi) {
                this.mengirim = true;
                this.galatPindai = '';
                try {
                    const res = await fetch(@json(route('asatid.absensi.kegiatan.simpan', $absensi)), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        body: JSON.stringify({ ...isi, jam: jamSekarang() }),
                    });
                    const json = await res.json();
                    if (json.ditutup) this.selesaiDitutup(true);
                    if (!res.ok) throw new Error(json.message || @js(__('Gagal mencatat.')));

                    const s = json.siswa;
                    const p = this.peserta.find((x) => x.id === s.id);
                    if (p) { p.status = s.status; p.waktu = s.waktu; }
                    this.terakhir = [s, ...this.terakhir.filter((x) => x.id !== s.id)].slice(0, 8);
                    this.buka = null;
                    return json;
                } catch (e) {
                    this.galatPindai = e.message;
                    setTimeout(() => (this.galatPindai = ''), 3500);
                    bip(220);
                    return null;
                } finally {
                    this.mengirim = false;
                }
            },

            async tampilkanHasil(json) {
                if (!json) return;
                this.hasil = { ...json, kunci: Date.now() };
                bip(json.ulang ? 520 : 880);
                clearTimeout(this._tHasil);
                this._tHasil = setTimeout(() => (this.hasil = null), 3000);
            },

            async kirimKode() {
                const kode = this.kodeManual.trim();
                if (!kode) return;
                this.kodeManual = '';
                this.tampilkanHasil(await this.kirim({ kode }));
            },

            async nyalakan() {
                this.galatKamera = '';
                this.memuatKamera = true;
                try {
                    if (!window.isSecureContext) throw new Error(@js(__('Kamera hanya bisa dipakai di alamat https://.')));
                    const { Html5Qrcode, Html5QrcodeSupportedFormats } = await window.muatPemindaiQr();
                    this._pemindai ??= new Html5Qrcode('pembaca', { formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], verbose: false });
                    await this._pemindai.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.72); return { width: s, height: s }; } },
                        (teks) => this.saatTerbaca(teks),
                        () => {},
                    );
                    this.kameraNyala = true;
                } catch (e) {
                    const pesan = String(e?.message || e);
                    this.galatKamera = /Permission|NotAllowed/i.test(pesan)
                        ? @js(__('Izin kamera ditolak. Izinkan kamera di pengaturan browser, lalu coba lagi.'))
                        : (/NotFound|Requested device/i.test(pesan) ? @js(__('Kamera tidak ditemukan di perangkat ini.')) : pesan);
                } finally {
                    this.memuatKamera = false;
                }
            },

            async matikan() {
                if (this._pemindai && this.kameraNyala) {
                    try { await this._pemindai.stop(); } catch (e) {}
                }
                this.kameraNyala = false;
            },

            /** Kode yang sama tidak dikirim ulang selama 4 detik */
            async saatTerbaca(teks) {
                const sekarang = Date.now();
                if (this.mengirim || (teks === this._kodeTerakhir && sekarang - this._waktuTerakhir < 4000)) return;
                this._kodeTerakhir = teks;
                this._waktuTerakhir = sekarang;
                this.tampilkanHasil(await this.kirim({ kode: teks }));
            },
        },
    }).mount('#absen-kegiatan');
});
</script>
@endpush
