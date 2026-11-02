{{--
    Akun Asatid > Absensi > Absensi Kelas
    Tahap 1 (atur)    : pilih kelas, pelajaran, tanggal, jam ke
    Tahap 2 (absen)   : satu kartu per peserta; klik keterangan -> otomatis ke peserta berikutnya
    Tahap 3 (ringkas) : cek ulang semua peserta lalu Simpan
    Data dari database hanya ditampilkan lewat v-text / binding Vue (tidak di-echo ke template).
--}}
@extends('layouts.asatid')

@section('judul', 'Absensi Kelas')

@push('styles')
<style>
    [v-cloak] { display: none; }
    .maju-enter-active, .maju-leave-active, .mundur-enter-active, .mundur-leave-active { transition: all .28s cubic-bezier(.2,.8,.2,1); }
    .maju-enter-from  { opacity: 0; transform: translateX(48px) scale(.98); }
    .maju-leave-to    { opacity: 0; transform: translateX(-48px) scale(.98); }
    .mundur-enter-from { opacity: 0; transform: translateX(-48px) scale(.98); }
    .mundur-leave-to   { opacity: 0; transform: translateX(48px) scale(.98); }
    .maju-leave-active, .mundur-leave-active { position: absolute; inset: 0; }
    .gulir-tipis::-webkit-scrollbar { height: 6px; }
    .gulir-tipis::-webkit-scrollbar-thumb { background: rgba(11,51,44,.15); border-radius: 999px; }
</style>
@endpush

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div id="absen-kelas" v-cloak>
@verbatim
    <!-- ================= TAHAP 1: ATUR ================= -->
    <div v-if="tahap === 'atur'" class="space-y-5">
        <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 text-white p-6 sm:p-8">
            <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px); background-size: 18px 18px;"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="relative">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-100/80">Langkah 1 dari 2</p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Atur absensi kelas</h2>
                <p class="mt-1.5 text-sm text-white/75">Pilih kelas, pelajaran, tanggal, dan jam ke. Setelah itu absen peserta satu per satu.</p>
            </div>
        </section>

        <!-- Kelas -->
        <section :class="kartu" class="p-5 sm:p-6">
            <h3 class="flex items-center gap-2 font-bold text-slate-800">
                <span class="w-7 h-7 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center text-xs font-extrabold">1</span>
                Pilih kelas
            </h3>
            <div v-if="kelasList.length" class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <button v-for="k in kelasList" :key="k.id" type="button" @click="form.kelas_id = k.id"
                    class="relative text-left p-4 rounded-2xl ring-1 transition-all"
                    :class="form.kelas_id === k.id ? 'bg-sky-50 ring-2 ring-sky-500 shadow-[0_8px_24px_-12px_rgba(14,165,233,0.6)]' : 'bg-[#f8fbfa] ring-slate-200/80 hover:ring-sky-300 hover:bg-white'">
                    <span v-if="form.kelas_id === k.id" class="absolute top-3 right-3 w-5 h-5 rounded-full bg-sky-500 text-white flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <span class="block font-bold text-slate-800 pr-6" v-text="k.nama"></span>
                    <span class="mt-1 block text-xs text-slate-500" v-text="(k.santri_count || 0) + ' peserta'"></span>
                </button>
            </div>
            <p v-else class="mt-4 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">Belum ada kelas aktif. Minta admin mengisinya di Master Data &rsaquo; Kelas.</p>
        </section>

        <!-- Pelajaran -->
        <section :class="kartu" class="p-5 sm:p-6">
            <h3 class="flex items-center gap-2 font-bold text-slate-800">
                <span class="w-7 h-7 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center text-xs font-extrabold">2</span>
                Pilih pelajaran
            </h3>
            <div v-if="pelajaranList.length" class="mt-4 flex flex-wrap gap-2">
                <button v-for="p in pelajaranList" :key="p.id" type="button" @click="form.bidang_ilmu_id = p.id"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-semibold ring-1 transition-all"
                    :class="form.bidang_ilmu_id === p.id ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-[#f8fbfa] text-slate-700 ring-slate-200/80 hover:ring-slate-300 hover:bg-white'">
                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: warnaHex(p.warna) }"></span>
                    <span v-text="p.nama"></span>
                </button>
            </div>
            <p v-else class="mt-4 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">Belum ada pelajaran. Minta admin mengisinya di Master Data &rsaquo; Bidang Ilmu.</p>
        </section>

        <!-- Tanggal & jam ke -->
        <section :class="kartu" class="p-5 sm:p-6 grid lg:grid-cols-2 gap-6">
            <div>
                <h3 class="flex items-center gap-2 font-bold text-slate-800">
                    <span class="w-7 h-7 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center text-xs font-extrabold">3</span>
                    Tanggal
                </h3>
                <input type="date" v-model="form.tanggal"
                    class="mt-4 w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-sky-500 outline-none">
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 px-3 py-2.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-sky-600">Masehi</span>
                        <span class="mt-0.5 block font-semibold text-slate-700" v-text="masehi(form.tanggal)"></span>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 px-3 py-2.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600">Hijriyah</span>
                        <span class="mt-0.5 block font-semibold text-slate-700" v-text="hijriyah(form.tanggal)"></span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="flex items-center gap-2 font-bold text-slate-800">
                    <span class="w-7 h-7 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 flex items-center justify-center text-xs font-extrabold">4</span>
                    Jam ke / waktu pelajaran
                </h3>
                <div v-if="jamList.length" class="mt-4 grid grid-cols-2 gap-2">
                    <button v-for="j in jamList" :key="j.id" type="button" @click="form.waktu_pelajaran_id = j.id"
                        class="text-left px-4 py-3 rounded-2xl ring-1 transition-all"
                        :class="form.waktu_pelajaran_id === j.id ? 'bg-amber-50 ring-2 ring-amber-400' : 'bg-[#f8fbfa] ring-slate-200/80 hover:ring-amber-300 hover:bg-white'">
                        <span class="block text-sm font-bold text-slate-800 truncate" v-text="j.nama"></span>
                        <span class="block text-[11px] text-slate-500" v-text="jamRentang(j)"></span>
                    </button>
                </div>
                <p v-else class="mt-4 p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">Belum ada waktu pelajaran. Minta admin mengisinya di Master Data &rsaquo; Waktu Pelajaran.</p>
            </div>
        </section>

        <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

        <div class="sticky bottom-4 z-10">
            <button type="button" @click="mulai" :disabled="!siapMulai || memuat"
                class="w-full flex items-center justify-center gap-2 py-4 rounded-2xl text-base font-extrabold transition-all"
                :class="siapMulai ? 'bg-[#0b332c] text-white hover:bg-[#135347] shadow-[0_16px_32px_-12px_rgba(11,51,44,0.6)]' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                <svg v-if="memuat" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                <span v-text="memuat ? 'Memuat peserta…' : (siapMulai ? 'Mulai Absen' : 'Lengkapi pilihan di atas')"></span>
                <svg v-if="!memuat && siapMulai" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
        </div>
    </div>

    <!-- ================= TAHAP 2: ABSEN PER PESERTA ================= -->
    <div v-else-if="tahap === 'absen'" class="max-w-xl mx-auto space-y-4">
        <!-- Info & progres -->
        <div :class="kartu" class="p-4">
            <div class="flex items-center gap-3">
                <button type="button" @click="tahap = 'atur'" title="Ubah pilihan"
                    class="w-9 h-9 shrink-0 rounded-xl bg-[#f5f9f7] ring-1 ring-slate-200/70 flex items-center justify-center text-slate-500 hover:text-sky-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate" v-text="judulSesi"></p>
                    <p class="text-[11px] text-slate-400 truncate" v-text="masehi(form.tanggal) + ' · ' + hijriyah(form.tanggal)"></p>
                </div>
                <span class="shrink-0 text-sm font-extrabold text-slate-800"><span v-text="idx + 1"></span><span class="text-slate-300 font-semibold"> / </span><span v-text="peserta.length"></span></span>
            </div>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-blue-600 transition-all duration-500" :style="{ width: persenTerisi + '%' }"></div>
            </div>
            <p v-if="sudahAda" class="mt-2 text-[11px] text-amber-700">Absensi ini sudah pernah diisi. Keterangan lama ditampilkan, Anda bisa mengubahnya.</p>
        </div>

        <!-- Kartu peserta -->
        <div class="relative min-h-[480px]">
            <transition :name="arah">
                <div :key="sekarang.id" :class="kartu" class="overflow-hidden">
                    <div class="relative h-24 bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700">
                        <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 16px 16px;"></div>
                        <span v-if="sekarang.status" class="absolute top-3 left-3 text-[11px] font-bold rounded-full px-2.5 py-1 bg-white/90"
                            :class="gaya(sekarang.status).teks" v-text="label(sekarang.status)"></span>
                        <!-- Detail lengkap peserta (hanya lihat), dibuka di tab baru supaya absensi tidak hilang -->
                        <a :href="sekarang.detail" target="_blank" rel="noopener" title="Lihat detail lengkap peserta"
                            class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 hover:bg-white/25 ring-1 ring-white/30 backdrop-blur px-3 py-1.5 text-[11px] font-bold text-white transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Detail
                        </a>
                    </div>
                    <div class="relative px-5 pb-5 -mt-12 text-center">
                        <img v-if="sekarang.foto" :src="sekarang.foto" alt="" class="mx-auto w-24 h-24 rounded-3xl object-cover ring-4 ring-white shadow-lg bg-white">
                        <span v-else class="mx-auto w-24 h-24 rounded-3xl ring-4 ring-white shadow-lg bg-gradient-to-br from-emerald-400 to-teal-600 text-white text-3xl font-extrabold flex items-center justify-center" v-text="inisial(sekarang.nama)"></span>
                        <h3 class="mt-3 text-xl font-extrabold text-slate-800" v-text="sekarang.nama"></h3>
                        <p v-if="sekarang.arab" dir="rtl" class="mt-0.5 text-lg text-slate-600" v-text="sekarang.arab"></p>

                        <div class="mt-4 grid sm:grid-cols-2 gap-2 text-left">
                            <div class="flex items-start gap-2.5 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2.5">
                                <span class="w-7 h-7 shrink-0 rounded-lg bg-rose-50 text-rose-500 ring-1 ring-rose-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat</span>
                                    <span class="block text-[13px] font-medium text-slate-700 leading-snug break-words" dir="auto" v-text="sekarang.alamat || '-'"></span>
                                </span>
                            </div>
                            <div class="flex items-start gap-2.5 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-3 py-2.5">
                                <span class="w-7 h-7 shrink-0 rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Pondok terakhir</span>
                                    <span class="block text-[13px] font-medium text-slate-700 leading-snug break-words" dir="auto" v-text="sekarang.pondok || '-'"></span>
                                </span>
                            </div>
                        </div>

                        <label class="mt-5 block text-left">
                            <span class="text-xs font-semibold text-slate-500">Catatan mudarris <span class="font-normal text-slate-400">(opsional, isi sebelum memilih keterangan)</span></span>
                            <textarea v-model="sekarang.catatan" rows="2" maxlength="500" placeholder="cth. Belum hafal muqaddimah"
                                class="mt-1.5 w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-sky-500 outline-none resize-none"></textarea>
                        </label>

                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <button v-for="(s, i) in status" :key="s.kunci" type="button" @click="pilih(s.kunci)"
                                class="group relative flex flex-col items-center justify-center gap-1 py-3.5 rounded-2xl font-bold text-sm ring-1 transition-all active:scale-95"
                                :class="[sekarang.status === s.kunci ? s.aktif : s.lembut, i === 0 ? 'col-span-2 sm:col-span-1' : '']">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold"
                                    :class="sekarang.status === s.kunci ? 'bg-white/25' : s.bulat" v-text="s.pendek"></span>
                                <span v-text="s.label"></span>
                            </button>
                        </div>
                        <p class="mt-3 hidden sm:block text-[11px] text-slate-400">Tombol cepat keyboard: H, T, S, I, A · panah kiri/kanan untuk pindah peserta</p>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Navigasi -->
        <div class="flex items-center gap-2">
            <button type="button" @click="ke(idx - 1)" :disabled="idx === 0"
                class="flex-1 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 disabled:opacity-40 hover:bg-slate-50">&larr; Sebelumnya</button>
            <button type="button" @click="tahap = 'ringkas'"
                class="flex-1 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">Ringkasan</button>
            <button type="button" @click="lanjut" :disabled="idx === peserta.length - 1"
                class="flex-1 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 disabled:opacity-40 hover:bg-slate-50">Lewati &rarr;</button>
        </div>

        <!-- Deretan peserta -->
        <div class="gulir-tipis flex gap-2 overflow-x-auto pb-2">
            <button v-for="(p, i) in peserta" :key="p.id" type="button" @click="ke(i)" :title="p.nama"
                class="relative shrink-0 w-11 h-11 rounded-2xl ring-2 transition-all text-xs font-bold flex items-center justify-center overflow-hidden"
                :class="[p.status ? gaya(p.status).cincin : 'ring-slate-200', i === idx ? 'scale-110 shadow-lg' : 'opacity-80 hover:opacity-100']">
                <img v-if="p.foto" :src="p.foto" alt="" class="w-full h-full object-cover">
                <span v-else class="w-full h-full bg-slate-100 text-slate-500 flex items-center justify-center" v-text="inisial(p.nama)"></span>
                <span v-if="p.status" class="absolute bottom-0 inset-x-0 text-[9px] leading-3 text-white" :class="gaya(p.status).titik" v-text="pendek(p.status)"></span>
            </button>
        </div>
    </div>

    <!-- ================= TAHAP 3: RINGKASAN & SIMPAN ================= -->
    <div v-else class="max-w-3xl mx-auto space-y-5 pb-24">
        <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
            <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-emerald-400/25 blur-3xl"></div>
            <div class="relative">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Langkah 2 dari 2 · Ringkasan</p>
                <h2 class="mt-1 text-xl sm:text-2xl font-extrabold" v-text="judulSesi"></h2>
                <p class="mt-1 text-sm text-white/70" v-text="masehi(form.tanggal) + ' · ' + hijriyah(form.tanggal)"></p>
                <div class="mt-5 grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <div v-for="s in status" :key="s.kunci" class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-3 py-2.5 text-center">
                        <span class="block text-2xl font-extrabold" v-text="hitung[s.kunci]"></span>
                        <span class="block text-[11px] text-white/70" v-text="s.label"></span>
                    </div>
                    <div class="rounded-2xl px-3 py-2.5 text-center ring-1" :class="belum ? 'bg-amber-400/20 ring-amber-300/40' : 'bg-white/10 ring-white/15'">
                        <span class="block text-2xl font-extrabold" v-text="belum"></span>
                        <span class="block text-[11px] text-white/70">Belum</span>
                    </div>
                </div>
            </div>
        </section>

        <section :class="kartu" class="divide-y divide-slate-100">
            <div v-for="(p, i) in peserta" :key="p.id" class="p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                <button type="button" @click="ke(i); tahap = 'absen'" class="flex items-center gap-3 flex-1 min-w-0 text-left">
                    <img v-if="p.foto" :src="p.foto" alt="" class="w-11 h-11 rounded-2xl object-cover ring-1 ring-slate-200">
                    <span v-else class="w-11 h-11 shrink-0 rounded-2xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(p.nama)"></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-800 truncate" v-text="p.nama"></span>
                        <span class="block text-xs truncate" :class="p.catatan ? 'text-slate-500' : 'text-slate-300'" v-text="p.catatan ? '“' + p.catatan + '”' : 'Tanpa catatan'"></span>
                    </span>
                </button>
                <div class="flex gap-1.5">
                    <button v-for="s in status" :key="s.kunci" type="button" @click="p.status = s.kunci" :title="s.label"
                        class="w-9 h-9 rounded-xl text-xs font-extrabold ring-1 transition-all"
                        :class="p.status === s.kunci ? s.aktif : 'bg-white text-slate-400 ring-slate-200 hover:ring-slate-300'" v-text="s.pendek"></button>
                </div>
            </div>
        </section>

        <p v-if="galat" class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-sm text-rose-700" v-text="galat"></p>

        <div class="fixed bottom-0 inset-x-0 z-30 bg-white/85 backdrop-blur-xl border-t border-slate-200/70">
            <div class="max-w-3xl mx-auto px-4 py-3 flex items-center gap-3">
                <button type="button" @click="tahap = 'absen'" class="px-4 py-3 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Kembali</button>
                <button type="button" @click="simpan" :disabled="menyimpan"
                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-extrabold hover:bg-[#135347] shadow-[0_12px_28px_-12px_rgba(11,51,44,0.6)] disabled:opacity-60">
                    <svg v-if="menyimpan" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span v-text="menyimpan ? 'Menyimpan…' : (belum ? 'Simpan (' + belum + ' belum diisi)' : 'Simpan Absensi')"></span>
                </button>
            </div>
        </div>
    </div>
@endverbatim
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { createApp } = window.Vue;

    // Warna tiap keterangan (class Tailwind ditulis lengkap supaya ikut ter-build)
    const STATUS = [
        { kunci: 'hadir',     label: 'Hadir',       pendek: 'H', aktif: 'bg-emerald-500 text-white ring-emerald-500 shadow-[0_10px_24px_-10px_rgba(16,185,129,0.8)]', lembut: 'bg-emerald-50 text-emerald-700 ring-emerald-200 hover:bg-emerald-100', bulat: 'bg-emerald-500 text-white', teks: 'text-emerald-700', cincin: 'ring-emerald-500', titik: 'bg-emerald-500' },
        { kunci: 'terlambat', label: 'Terlambat',   pendek: 'T', aktif: 'bg-amber-500 text-white ring-amber-500 shadow-[0_10px_24px_-10px_rgba(245,158,11,0.8)]',   lembut: 'bg-amber-50 text-amber-700 ring-amber-200 hover:bg-amber-100',       bulat: 'bg-amber-500 text-white',   teks: 'text-amber-700',   cincin: 'ring-amber-500',   titik: 'bg-amber-500' },
        { kunci: 'sakit',     label: 'Sakit',       pendek: 'S', aktif: 'bg-sky-500 text-white ring-sky-500 shadow-[0_10px_24px_-10px_rgba(14,165,233,0.8)]',       lembut: 'bg-sky-50 text-sky-700 ring-sky-200 hover:bg-sky-100',               bulat: 'bg-sky-500 text-white',     teks: 'text-sky-700',     cincin: 'ring-sky-500',     titik: 'bg-sky-500' },
        { kunci: 'izin',      label: 'Izin',        pendek: 'I', aktif: 'bg-violet-500 text-white ring-violet-500 shadow-[0_10px_24px_-10px_rgba(139,92,246,0.8)]', lembut: 'bg-violet-50 text-violet-700 ring-violet-200 hover:bg-violet-100',   bulat: 'bg-violet-500 text-white',  teks: 'text-violet-700',  cincin: 'ring-violet-500',  titik: 'bg-violet-500' },
        { kunci: 'alfa',      label: 'Tidak Hadir', pendek: 'A', aktif: 'bg-rose-500 text-white ring-rose-500 shadow-[0_10px_24px_-10px_rgba(244,63,94,0.8)]',     lembut: 'bg-rose-50 text-rose-700 ring-rose-200 hover:bg-rose-100',           bulat: 'bg-rose-500 text-white',    teks: 'text-rose-700',    cincin: 'ring-rose-500',    titik: 'bg-rose-500' },
    ];
    const TOMBOL = { h: 'hadir', t: 'terlambat', s: 'sakit', i: 'izin', a: 'alfa' };
    // Warna titik pelajaran (dari pilihan warna Bidang Ilmu di Master Data)
    const WARNA = {
        'bg-slate-100': '#94a3b8', 'bg-red-100': '#f87171', 'bg-orange-100': '#fb923c', 'bg-amber-100': '#fbbf24',
        'bg-yellow-100': '#facc15', 'bg-lime-100': '#a3e635', 'bg-green-100': '#4ade80', 'bg-emerald-100': '#34d399',
        'bg-teal-100': '#2dd4bf', 'bg-cyan-100': '#22d3ee', 'bg-sky-100': '#38bdf8', 'bg-blue-100': '#60a5fa',
        'bg-indigo-100': '#818cf8', 'bg-violet-100': '#a78bfa', 'bg-purple-100': '#c084fc', 'bg-pink-100': '#f472b6', 'bg-rose-100': '#fb7185',
    };

    const keTanggal = (iso) => new Date(iso + 'T12:00:00');
    const FMT_M = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const FMT_H = new Intl.DateTimeFormat('id-u-ca-islamic-umalqura', { day: 'numeric', month: 'long', year: 'numeric' });
    const hariIni = () => { const d = new Date(); return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10); };

    const awal = @json($awal);
    const angka = (v) => (v ? Number(v) : null);

    createApp({
        data: () => ({
            kartu: @json($kartu),
            kelasList: @json($kelasList),
            pelajaranList: @json($pelajaranList),
            jamList: @json($jamList),
            status: STATUS,
            tahap: 'atur',
            form: {
                kelas_id: angka(awal.kelas_id),
                bidang_ilmu_id: angka(awal.bidang_ilmu_id),
                waktu_pelajaran_id: angka(awal.waktu_pelajaran_id),
                tanggal: awal.tanggal || hariIni(),
            },
            peserta: [],
            idx: 0,
            arah: 'maju',
            sudahAda: false,
            memuat: false,
            menyimpan: false,
            galat: '',
        }),
        computed: {
            siapMulai() { return this.form.kelas_id && this.form.bidang_ilmu_id && this.form.waktu_pelajaran_id && this.form.tanggal; },
            sekarang() { return this.peserta[this.idx] || {}; },
            hitung() {
                const h = {};
                STATUS.forEach((s) => { h[s.kunci] = this.peserta.filter((p) => p.status === s.kunci).length; });
                return h;
            },
            belum() { return this.peserta.filter((p) => !p.status).length; },
            persenTerisi() { return this.peserta.length ? Math.round(((this.peserta.length - this.belum) / this.peserta.length) * 100) : 0; },
            judulSesi() {
                const cari = (list, id) => (list.find((x) => x.id === id) || {}).nama || '-';
                return `${cari(this.kelasList, this.form.kelas_id)} · ${cari(this.pelajaranList, this.form.bidang_ilmu_id)} · ${cari(this.jamList, this.form.waktu_pelajaran_id)}`;
            },
        },
        mounted() {
            document.addEventListener('keydown', this.tombolCepat);
            if (this.siapMulai && awal.kelas_id) this.mulai();
        },
        methods: {
            masehi: (iso) => (iso ? FMT_M.format(keTanggal(iso)) : '-'),
            hijriyah: (iso) => (iso ? FMT_H.format(keTanggal(iso)) : '-'),
            jamRentang: (j) => (j.jam_mulai ? `${j.jam_mulai}${j.jam_selesai ? ' – ' + j.jam_selesai : ''}` : 'Jam belum diatur'),
            warnaHex: (w) => WARNA[w] || '#cbd5e1',
            inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
            gaya: (k) => STATUS.find((s) => s.kunci === k) || {},
            label(k) { return this.gaya(k).label; },
            pendek(k) { return this.gaya(k).pendek; },

            async mulai() {
                this.galat = '';
                this.memuat = true;
                try {
                    const url = new URL(@json(route('asatid.absensi.kelas.peserta')));
                    Object.entries(this.form).forEach(([k, v]) => url.searchParams.set(k, v));
                    const res = await fetch(url, { headers: { Accept: 'application/json' } });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Gagal memuat peserta.');
                    if (!json.peserta.length) throw new Error('Belum ada peserta di kelas ini. Minta admin menempatkan peserta ke kelas tersebut.');

                    this.peserta = json.peserta.map((p) => ({ ...p, catatan: p.catatan || '' }));
                    this.sudahAda = json.sudah_ada;
                    const belumIdx = this.peserta.findIndex((p) => !p.status);
                    this.idx = belumIdx === -1 ? 0 : belumIdx;
                    this.tahap = belumIdx === -1 ? 'ringkas' : 'absen';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },
            ke(i) {
                if (i < 0 || i >= this.peserta.length || i === this.idx) return;
                this.arah = i > this.idx ? 'maju' : 'mundur';
                this.idx = i;
            },
            lanjut() { this.ke(this.idx + 1); },
            pilih(kunci) {
                this.sekarang.status = kunci;
                // Ke peserta berikutnya yang belum diisi; kalau semua sudah, ke ringkasan
                setTimeout(() => {
                    const sisa = this.peserta.findIndex((p, i) => i > this.idx && !p.status);
                    const dariAwal = this.peserta.findIndex((p) => !p.status);
                    const tujuan = sisa !== -1 ? sisa : dariAwal;
                    if (tujuan === -1) { this.tahap = 'ringkas'; window.scrollTo({ top: 0, behavior: 'smooth' }); }
                    else this.ke(tujuan);
                }, 180);
            },
            tombolCepat(e) {
                if (this.tahap !== 'absen' || e.ctrlKey || e.metaKey || e.altKey) return;
                if (['TEXTAREA', 'INPUT'].includes(document.activeElement?.tagName)) return;
                const k = e.key.toLowerCase();
                if (TOMBOL[k]) { e.preventDefault(); this.pilih(TOMBOL[k]); }
                else if (e.key === 'ArrowRight') this.lanjut();
                else if (e.key === 'ArrowLeft') this.ke(this.idx - 1);
            },
            async simpan() {
                this.galat = '';
                if (this.belum) {
                    this.galat = `Masih ada ${this.belum} peserta yang belum diberi keterangan.`;
                    const i = this.peserta.findIndex((p) => !p.status);
                    this.idx = i; this.tahap = 'absen';
                    return;
                }
                this.menyimpan = true;
                try {
                    const res = await fetch(@json(route('asatid.absensi.kelas.simpan')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        body: JSON.stringify({ ...this.form, data: this.peserta.map((p) => ({ id: p.id, status: p.status, catatan: p.catatan })) }),
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Gagal menyimpan absensi.');
                    window.location = json.redirect;
                } catch (e) {
                    this.galat = e.message;
                    this.menyimpan = false;
                }
            },
        },
    }).mount('#absen-kelas');
});
</script>
@endpush
