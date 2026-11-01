{{-- Form edit data pendaftar. Konten saja; layout admin memuat @stack('scripts'). --}}
@extends('layouts.admin')

@section('content')
@php
/*
| Daftar isian per bagian. Satu isian = [nama_kolom, label, tipe, opsi (boleh kosong)]
| tipe: text | date | number | textarea | select
| Menambah isian baru cukup menambah satu baris di sini.
*/
$bagian = [
'Data Pribadi' => [
['nama_lengkap', 'Nama Lengkap (Indonesia)', 'text'],
['nama_lengkap_arab', 'Nama Lengkap (Arab)', 'text', ['dir' => 'rtl']],
['tempat_lahir', 'Tempat Lahir', 'text'],
['tanggal_lahir', 'Tanggal Lahir', 'date'],
['status_pernikahan', 'Status Pernikahan', 'select', ['options' => $p::STATUS_PERNIKAHAN]],
['no_hp', 'No. WhatsApp', 'text', ['hint' => 'Contoh: +6281234567890']],
],
'Alamat' => [
['provinsi', 'Provinsi', 'text'],
['kota', 'Kota/Kabupaten', 'text'],
['kecamatan', 'Kecamatan', 'text'],
['kelurahan', 'Kelurahan/Desa', 'text'],
['alamat', 'Alamat Lengkap', 'textarea'],
],
'Wali & Muarrif' => [
['nama_wali', 'Nama Wali', 'text'],
['pekerjaan_wali', 'Pekerjaan Wali', 'text'],
['hubungan_wali', 'Hubungan dengan Wali', 'text'],
['no_hp_wali', 'No. WhatsApp Wali', 'text', ['hint' => 'Nomor ini dipakai untuk chat penerimaan']],
['nama_muarrif', 'Nama Muarrif', 'text'],
['no_hp_muarrif', 'No. WhatsApp Muarrif', 'text'],
['nama_butuh', 'Kontak Darurat (Nama)', 'text'],
['no_hp_nya', 'Kontak Darurat (WhatsApp)', 'text'],
],
'Pendidikan Formal' => [
['pendidikan_terakhir', 'Pendidikan Terakhir', 'select', ['options' => $p::PENDIDIKAN_TERAKHIR]],
['nama_sekolah', 'Nama Sekolah / Lembaga', 'text'],
['nama_daerah', 'Daerah Lembaga', 'text'],
],
'Data Tambahan' => [
['marhalah', 'Marhalah yang Diinginkan', 'text'],
['lama_belajar', 'Lama Belajar yang Diinginkan', 'text'],
['kesehatan', 'Kesehatan', 'text'],
['status_ekonomi', 'Status Ekonomi', 'text'],
['persentasimuslim', 'Persentase Muslim di Daerah (%)', 'number', ['step' => '0.01']],
['alumni', 'Alumni di Daerah', 'text'],
['tujuan', 'Tujuan Belajar', 'textarea'],
['rencana', 'Rencana Setelah Lulus', 'textarea'],
],
];

// Nilai awal untuk daftar pondok & kitab (old() = isian terakhir kalau validasi gagal)
$barisPondok = ['asal_pondok' => '', 'nama_pengasuh' => '', 'nama_daerah' => '', 'tahun_belajar' => ''];
$barisKitab = ['nama_kitab' => '', 'fan_kitab' => '', 'sudah_diujikan' => ''];

$pondokAwal = collect(old('riwayat_pondok', $p->riwayat_pondok ?: [$barisPondok]))
->map(fn ($r) => array_merge($barisPondok, (array) $r))->values()->all();
$kitabAwal = collect(old('riwayat_kitab', $p->riwayat_kitab ?: [$barisKitab]))
->map(fn ($r) => array_merge($barisKitab, (array) $r))->values()->all();

// Gaya input (sama untuk semua isian)
$kelasInput = 'w-full bg-[#f5f9f7] border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20';

// Input di dalam kartu pondok/kitab memakai latar putih
$kelasInputPutih = str_replace('bg-[#f5f9f7]', 'bg-white', $kelasInput);

// ===== Gaya yang sama dengan halaman detail (show.blade.php) =====
$warnaStatus = [
'menunggu' => 'bg-amber-300/15 text-amber-200 ring-amber-300/30',
'diterima' => 'bg-emerald-300/15 text-emerald-200 ring-emerald-300/30',
'ditolak' => 'bg-rose-300/15 text-rose-200 ring-rose-300/30',
][$p->status] ?? 'bg-white/10 text-white/80 ring-white/20';

$ikon = [
'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
'pin' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
'cap' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
'sparkles' => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z',
'home' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
'book' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
'file' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
'pencil' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z',
'check' => 'M4.5 12.75l6 6 9-13.5',
'x' => 'M6 18L18 6M6 6l12 12',
'open' => 'M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25',
'back' => 'M15.75 19.5L8.25 12l7.5-7.5',
'eye' => 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
'plus' => 'M12 4.5v15m7.5-7.5h-15',
'trash' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
'warn' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
];
$svg = fn ($nama, $kelas = 'w-5 h-5') => '<svg class="' . $kelas . '" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . $ikon[$nama] . '"/></svg>';

$nada = [
'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
'teal' => 'bg-teal-50 text-teal-600 ring-teal-100',
'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
'cyan' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
'lime' => 'bg-lime-50 text-lime-700 ring-lime-100',
'sky' => 'bg-sky-50 text-sky-600 ring-sky-100',
];
$gayaSeksi = [
'Data Pribadi' => ['user', 'emerald', 'Identitas diri santri'],
'Alamat' => ['pin', 'teal', 'Domisili sesuai data pendaftaran'],
'Wali & Muarrif' => ['users', 'amber', 'Kontak keluarga & pemberi rekomendasi'],
'Pendidikan Formal' => ['cap', 'sky', 'Jenjang pendidikan terakhir'],
'Data Tambahan' => ['sparkles', 'lime', 'Minat, kondisi & rencana belajar'],
];
// Bagian yang kartunya selebar halaman (isinya banyak)
$seksiPenuh = ['Wali & Muarrif', 'Pendidikan Formal', 'Data Tambahan'];

$judulKartu = fn ($judul, $namaIkon, $warna, $ket = null) =>
'<div class="flex items-center gap-3">'
. '<span class="w-10 h-10 shrink-0 rounded-2xl ring-1 flex items-center justify-center ' . $nada[$warna] . '">' . $svg($namaIkon) . '</span>'
. '<div class="min-w-0"><h2 class="text-[15px] font-bold text-slate-800 leading-tight">' . e($judul) . '</h2>'
. ($ket ? '<p class="text-xs text-slate-400 mt-0.5">' . e($ket) . '</p>' : '')
. '</div></div>';

$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div class="space-y-6">
    <a href="{{ route('admin.pendaftaran.show', $p) }}"
        class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0b332c] transition-colors">
        <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center group-hover:-translate-x-0.5 transition-transform">
            {!! $svg('back', 'w-4 h-4') !!}
        </span>
        Kembali ke detail
    </a>

    {{-- ================= HEADER (hijau tua, senada dengan halaman detail) ================= --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white shadow-[0_24px_48px_-24px_rgba(7,31,26,0.6)]">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-28 -right-20 w-80 h-80 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/3 w-72 h-72 rounded-full bg-amber-300/10 blur-3xl"></div>

        <div class="relative p-5 sm:p-7 flex flex-col sm:flex-row sm:items-center gap-5">
            @if ($p->foto_url)
            <img src="{{ $p->foto_url }}" alt="Foto {{ $p->nama_lengkap }}"
                class="w-20 h-20 shrink-0 object-cover rounded-2xl ring-4 ring-white/10 shadow-xl shadow-black/30">
            @else
            <div class="w-20 h-20 shrink-0 rounded-2xl ring-4 ring-white/10 bg-gradient-to-br from-emerald-300 to-lime-200 flex items-center justify-center text-3xl font-extrabold text-[#0b332c] shadow-xl shadow-black/30">
                {{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}
            </div>
            @endif

            <div class="flex-1 min-w-0">
                <p class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">
                    {!! $svg('pencil', 'w-3.5 h-3.5') !!} Edit Data Santri
                </p>
                <h1 class="mt-1 text-2xl sm:text-3xl leading-tight font-extrabold tracking-tight break-words">{{ $p->nama_lengkap }}</h1>
                <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 font-semibold tracking-wide"># {{ $p->nomor_pendaftaran }}</span>
                    <span class="inline-flex items-center rounded-full ring-1 px-3 py-1 font-semibold {{ $warnaStatus }}">{{ $p->label_status }}</span>
                </div>
            </div>

            <a href="{{ route('admin.pendaftaran.show', $p) }}"
                class="self-start sm:self-center inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/15 text-sm font-semibold hover:bg-white hover:text-[#0b332c] transition-colors">
                {!! $svg('eye', 'w-4 h-4') !!} Lihat Detail
            </a>
        </div>
    </div>

    {{-- Daftar semua kesalahan validasi --}}
    @if ($errors->any())
    <div class="flex gap-3 p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
        <span class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">{!! $svg('warn', 'w-4 h-4') !!}</span>
        <div>
            <p class="font-semibold mb-1">Data belum bisa disimpan:</p>
            <ul class="list-disc pl-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- enctype multipart wajib ada supaya file bisa diunggah; @method('PUT') memberi tahu Laravel ini permintaan update --}}
    <form method="POST" action="{{ route('admin.pendaftaran.update', $p) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid xl:grid-cols-2 gap-6 items-start">
            @foreach ($bagian as $judul => $isian)
            @php [$namaIkon, $warna, $ket] = $gayaSeksi[$judul]; $penuh = in_array($judul, $seksiPenuh); @endphp

            {{-- Riwayat pondok & kitab (Vue) tampil SEBELUM "Data Tambahan" --}}
            @if ($judul === 'Data Tambahan')
            <div id="riwayat-app" class="xl:col-span-2 grid xl:grid-cols-2 gap-6 items-start">
                {{-- Pondok --}}
                <section class="{{ $kartu }} p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        {!! $judulKartu('Riwayat Pondok Pesantren', 'home', 'amber', 'Maksimal 5 pondok') !!}
                        <span class="text-xs font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-3 py-1" v-text="pondok.length + '/5'"></span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(row, i) in pondok" :key="row.key"
                            class="relative rounded-2xl bg-gradient-to-br from-amber-50/80 via-white to-white ring-1 ring-amber-100 p-4 pl-5 overflow-hidden">
                            <span class="absolute left-0 top-4 bottom-4 w-1 rounded-r-full bg-gradient-to-b from-amber-400 to-orange-400"></span>
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center" v-text="i + 1"></span>
                                    <span v-text="'Pondok ' + (i + 1)"></span>
                                </span>
                                <button type="button" v-if="pondok.length > 1" @click="hapusPondok(i)" title="Hapus pondok"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 rounded-xl px-2.5 py-1.5 hover:bg-rose-50 transition-colors">
                                    {!! $svg('trash', 'w-4 h-4') !!} Hapus
                                </button>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <input :name="'riwayat_pondok[' + i + '][asal_pondok]'" v-model="row.asal_pondok"
                                    placeholder="Asal pondok pesantren" dir="auto" class="{{ $kelasInputPutih }}">
                                <input :name="'riwayat_pondok[' + i + '][nama_pengasuh]'" v-model="row.nama_pengasuh"
                                    placeholder="Nama pengasuh" dir="auto" class="{{ $kelasInputPutih }}">
                                <input :name="'riwayat_pondok[' + i + '][nama_daerah]'" v-model="row.nama_daerah"
                                    placeholder="Daerah / kota pondok" dir="auto" class="{{ $kelasInputPutih }}">
                                <input :name="'riwayat_pondok[' + i + '][tahun_belajar]'" v-model="row.tahun_belajar"
                                    type="number" min="0" max="30" placeholder="Lama belajar (tahun)" class="{{ $kelasInputPutih }}">
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="tambahPondok" :disabled="pondok.length >= 5"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-dashed border-amber-200 text-amber-700 text-sm font-semibold hover:bg-amber-50 hover:border-amber-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        {!! $svg('plus', 'w-4 h-4') !!} Tambah Pondok
                    </button>
                </section>

                {{-- Kitab --}}
                <section class="{{ $kartu }} p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        {!! $judulKartu('Kitab yang Pernah Dipelajari', 'book', 'cyan', 'Maksimal 20 kitab') !!}
                        <span class="text-xs font-semibold text-cyan-700 bg-cyan-50 ring-1 ring-cyan-100 rounded-full px-3 py-1" v-text="kitab.length + '/20'"></span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(row, i) in kitab" :key="row.key"
                            class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <span class="w-7 h-7 rounded-lg bg-cyan-500 text-white text-xs font-bold flex items-center justify-center" v-text="i + 1"></span>
                                    <span v-text="'Kitab ' + (i + 1)"></span>
                                </span>
                                <button type="button" v-if="kitab.length > 1" @click="hapusKitab(i)" title="Hapus kitab"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 rounded-xl px-2.5 py-1.5 hover:bg-rose-50 transition-colors">
                                    {!! $svg('trash', 'w-4 h-4') !!} Hapus
                                </button>
                            </div>
                            <div class="grid sm:grid-cols-3 gap-3">
                                <input :name="'riwayat_kitab[' + i + '][nama_kitab]'" v-model="row.nama_kitab"
                                    placeholder="Nama kitab" dir="auto" class="{{ $kelasInputPutih }}">
                                <select :name="'riwayat_kitab[' + i + '][fan_kitab]'" v-model="row.fan_kitab"
                                    class="{{ $kelasInputPutih }}">
                                    <option value="">Pilih fan...</option>
                                    <option v-for="f in fan" :key="f" :value="f" v-text="f"></option>
                                </select>
                                <select :name="'riwayat_kitab[' + i + '][sudah_diujikan]'" v-model="row.sudah_diujikan"
                                    class="{{ $kelasInputPutih }}">
                                    <option value="">Sudah diujikan?</option>
                                    <option value="sudah">أختبر</option>
                                    <option value="belum">لا</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="tambahKitab" :disabled="kitab.length >= 20"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-dashed border-cyan-200 text-cyan-700 text-sm font-semibold hover:bg-cyan-50 hover:border-cyan-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        {!! $svg('plus', 'w-4 h-4') !!} Tambah Kitab
                    </button>
                </section>
            </div>
            @endif

            <section @class([$kartu, 'p-5 sm:p-6', 'xl:col-span-2' => $penuh])>
                <div class="mb-5">{!! $judulKartu($judul, $namaIkon, $warna, $ket) !!}</div>
                <div @class(['grid sm:grid-cols-2 gap-4', 'xl:grid-cols-3' => $judul === 'Pendidikan Formal', 'xl:grid-cols-4' => $judul === 'Wali & Muarrif'])>
                    @foreach ($isian as $k)
                    @php
                    [$nama, $label, $tipe] = $k;
                    $opsi = $k[3] ?? [];
                    // old() = isian terakhir kalau validasi gagal; kalau tidak ada, pakai data dari database
                    $nilai = old($nama, $tipe === 'date' ? optional($p->$nama)->format('Y-m-d') : $p->$nama);
                    @endphp

                    <div @class(['sm:col-span-2 xl:col-span-full' => $tipe === 'textarea'])>
                        <label for="{{ $nama }}" class="block text-xs font-semibold text-slate-500 mb-1.5">{{ $label }}</label>

                        @if ($tipe === 'select')
                        <select id="{{ $nama }}" name="{{ $nama }}" @class([$kelasInput, 'border-rose-300 ring-2 ring-rose-400/20' => $errors->has($nama)])>
                            <option value="">Pilih...</option>
                            @foreach ($opsi['options'] as $kode => $teks)
                            <option value="{{ $kode }}" @selected((string) $nilai===(string) $kode)>{{ $teks }}</option>
                            @endforeach
                        </select>
                        @elseif ($tipe === 'textarea')
                        <textarea id="{{ $nama }}" name="{{ $nama }}" rows="3" dir="auto"
                            @class([$kelasInput, 'leading-relaxed', 'border-rose-300 ring-2 ring-rose-400/20' => $errors->has($nama)])>{{ $nilai }}</textarea>
                        @else
                        <input id="{{ $nama }}" name="{{ $nama }}" type="{{ $tipe }}" value="{{ $nilai }}" @if ($tipe==='text' )
                            dir="{{ $opsi['dir'] ?? 'auto' }}" @endif @if (isset($opsi['step'])) step="{{ $opsi['step'] }}" min="0" max="100"
                            @endif @class([$kelasInput, 'border-rose-300 ring-2 ring-rose-400/20' => $errors->has($nama)])>
                        @endif

                        @isset($opsi['hint'])
                        <p class="text-xs text-slate-400 mt-1.5">{{ $opsi['hint'] }}</p>
                        @endisset
                        @error($nama)
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </section>
            @endforeach

            {{-- Berkas --}}
            <section class="{{ $kartu }} p-5 sm:p-6 xl:col-span-2">
                <div class="mb-5">{!! $judulKartu('Berkas', 'file', 'teal', 'Pilih file hanya kalau ingin menggantinya. Kosongkan kalau berkas tidak berubah.') !!}</div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($p::BERKAS as $kolom => $nama)
                    <div @class(['rounded-2xl p-3.5 ring-1', 'bg-white ring-teal-100' => $p->$kolom, 'bg-slate-50/60 ring-slate-100' => ! $p->$kolom, 'ring-rose-300' => $errors->has($kolom)])>
                        <div class="flex items-center gap-3">
                            <span @class(['w-10 h-10 shrink-0 rounded-xl flex items-center justify-center',
                                'bg-gradient-to-br from-teal-400 to-emerald-500 text-white shadow-sm' => $p->$kolom,
                                'bg-slate-100 text-slate-300' => ! $p->$kolom])>{!! $svg('file', 'w-5 h-5') !!}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $nama }}</p>
                                @if ($p->$kolom)
                                <a href="{{ asset('storage/' . $p->$kolom) }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-teal-600 hover:underline">{!! $svg('open', 'w-3 h-3') !!} Lihat berkas saat ini</a>
                                @else
                                <p class="text-xs text-slate-400">Belum ada berkas</p>
                                @endif
                            </div>
                        </div>
                        <input type="file" name="{{ $kolom }}"
                            accept="{{ $kolom === 'berkas_foto' ? 'image/*' : '.jpg,.jpeg,.png,.pdf' }}"
                            class="mt-3 block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-semibold hover:file:bg-emerald-100 file:cursor-pointer cursor-pointer">
                        @error($kolom)
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Tombol simpan: menempel di bawah layar saat halaman digulir --}}
        <div class="sticky bottom-0 z-20 mt-6 -mx-1 px-1 pb-1">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white/85 backdrop-blur-md ring-1 ring-[#0b332c]/10 shadow-[0_-8px_24px_-12px_rgba(11,51,44,0.25)] px-4 py-3">
                <p class="hidden sm:block text-xs text-slate-500">Periksa kembali data sebelum menyimpan.</p>
                <div class="flex gap-2 ml-auto">
                    <a href="{{ route('admin.pendaftaran.show', $p) }}"
                        class="px-5 py-2.5 rounded-2xl bg-slate-100 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition-colors">Batal</a>
                    <button
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                        {!! $svg('check', 'w-4 h-4') !!} Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3.4.38/dist/vue.global.prod.js"></script>
<script>
    // Data awal dikirim dari PHP ke JavaScript dalam format JSON
    const awalPondok = @json($pondokAwal);
    const awalKitab  = @json($kitabAwal);
    const daftarFan  = @json($p::FAN_KITAB);

    // Setiap baris diberi nomor unik (key) supaya Vue tidak tertukar saat baris dihapus
    let kunci = 0;
    const beriKunci = (baris) => baris.map((b) => ({ key: ++kunci, ...b }));

    Vue.createApp({
        data: () => ({
            pondok: beriKunci(awalPondok),
            kitab: beriKunci(awalKitab),
            fan: daftarFan,
        }),
        methods: {
            tambahPondok() {
                if (this.pondok.length < 5) {
                    this.pondok.push({ key: ++kunci, asal_pondok: '', nama_pengasuh: '', nama_daerah: '', tahun_belajar: '' });
                }
            },
            hapusPondok(i) {
                if (this.pondok.length > 1) this.pondok.splice(i, 1);
            },
            tambahKitab() {
                if (this.kitab.length < 20) {
                    this.kitab.push({ key: ++kunci, nama_kitab: '', fan_kitab: '', sudah_diujikan: '' });
                }
            },
            hapusKitab(i) {
                if (this.kitab.length > 1) this.kitab.splice(i, 1);
            },
        },
    }).mount('#riwayat-app');
</script>
@endpush
