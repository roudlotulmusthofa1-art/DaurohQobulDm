{{-- Detail pendaftar. Konten saja; layout admin memuat @stack('scripts'). --}}
@extends('layouts.admin')

@section('content')
@php
// Data yang ditampilkan per bagian: 'Label' => nilai.
// Kode singkat (K, L, M, o) diubah jadi tulisan lewat konstanta di model.
$seksi = [
'Data Pribadi' => [
'Nama Lengkap (Indonesia)' => $p->nama_lengkap,
'Nama Lengkap (Arab)' => $p->nama_lengkap_arab,
'Tempat Lahir' => $p->tempat_lahir,
'Tanggal Lahir' => optional($p->tanggal_lahir)->translatedFormat('d F Y'),
'Status Pernikahan' => $p::STATUS_PERNIKAHAN[$p->status_pernikahan] ?? null,
'No. WhatsApp' => $p->no_hp,
'Email' => $p->email,
],
'Alamat' => [
'Provinsi' => $p->provinsi,
'Kota/Kabupaten' => $p->kota,
'Kecamatan' => $p->kecamatan,
'Kelurahan/Desa' => $p->kelurahan,
'Alamat Lengkap' => $p->alamat,
],
'Wali & Muarrif' => [
'Nama Wali' => $p->nama_wali,
'Pekerjaan Wali' => $p->pekerjaan_wali,
'Hubungan dengan Wali' => $p->hubungan_wali,
'No. WhatsApp Wali' => $p->no_hp_wali,
'Nama Muarrif' => $p->nama_muarrif,
'No. WhatsApp Muarrif' => $p->no_hp_muarrif,
'Kontak Darurat (Nama)' => $p->nama_butuh,
'Kontak Darurat (WhatsApp)' => $p->no_hp_nya,
],
'Pendidikan Formal' => [
'Pendidikan Terakhir' => $p::PENDIDIKAN_TERAKHIR[$p->pendidikan_terakhir] ?? null,
'Nama Sekolah / Lembaga' => $p->nama_sekolah,
'Daerah Lembaga' => $p->nama_daerah,
],
'Data Tambahan' => [
'Marhalah yang Diinginkan' => $p->marhalah,
'Lama Belajar yang Diinginkan' => $p->lama_belajar,
'Kesehatan' => $p->kesehatan,
'Status Ekonomi' => $p->status_ekonomi,
'Persentase Muslim di Daerah' => $p->persentasimuslim !== null ? ((float) $p->persentasimuslim) . '%' : null,
'Alumni di Daerah' => $p->alumni,
'Tujuan Belajar' => $p->tujuan,
'Rencana Setelah Lulus' => $p->rencana,
],
];

// Label yang isinya panjang -> memakai lebar penuh
$lebar = ['Alamat Lengkap', 'Tujuan Belajar', 'Rencana Setelah Lulus'];
// Label yang isinya nomor WhatsApp -> diberi ikon yang bisa diklik
$labelWa = ['No. WhatsApp', 'No. WhatsApp Wali', 'No. WhatsApp Muarrif', 'Kontak Darurat (WhatsApp)'];

// riwayat_pondok & riwayat_kitab disimpan sebagai JSON (di model sudah di-cast ke array)
$pondok = $p->riwayat_pondok ?? [];
$kitab = $p->riwayat_kitab ?? [];

// Angka untuk baris statistik & kartu ringkasan
$totalSyarat = count($p::SYARAT);
$persenSyarat = $totalSyarat ? round($p->syarat_terpenuhi / $totalSyarat * 100) : 0;
$lokasi = collect([$p->kota, $p->provinsi])->filter()->implode(', ');
$diperbarui = optional($p->updated_at)->translatedFormat('d M Y, H:i');

// Warna badge status
$warnaStatus = [
'menunggu' => 'bg-amber-100 text-amber-700',
'diterima' => 'bg-emerald-100 text-emerald-700',
'ditolak' => 'bg-rose-100 text-rose-700',
][$p->status] ?? 'bg-slate-100 text-slate-600';

// Warna titik judul tiap bagian
$warnaSeksi = ['bg-teal-400', 'bg-sky-400', 'bg-violet-400', 'bg-lime-400', 'bg-rose-400'];
@endphp

<div class="space-y-6" id="psb-detail">
    <a href="{{ route('admin.pendaftaran.index') }}"
        class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-teal-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke daftar
    </a>

    @if (session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm" v-pre>{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="p-3 rounded-xl bg-red-50 text-red-700 text-sm" v-pre>{{ session('error') }}</div>
    @endif

    {{-- ================= KARTU PROFIL ================= --}}
    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row gap-6">
            {{-- Foto --}}
            <div class="shrink-0 self-center sm:self-start">
                @if ($p->foto_url)
                <img src="{{ $p->foto_url }}" alt="Foto" class=" w-56 h-60 object-cover rounded-2xl shadow-md">
                @else
                <div class="w-36 h-44 rounded-2xl bg-gradient-to-br from-teal-100 to-lime-100 flex items-center justify-center text-4xl font-bold text-teal-700"
                    v-pre>
                    {{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}
                </div>
                @endif
            </div>

            {{-- Identitas --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0" v-pre>
                        <h1 class="text-xl sm:text-3xl font-bold text-slate-800 truncate">{{ $p->nama_lengkap }}</h1>
                        @if ($p->nama_lengkap_arab)
                        <p class="text-xl text-slate-600" dir="rtl">{{ $p->nama_lengkap_arab }}</p>
                        @endif
                        <p class="text-sm text-slate-500 mt-0.5">
                            <span class="font-medium text-slate-700">{{ $p->nomor_pendaftaran }}</span>
                            · mendaftar {{ $p->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Tombol ikon kanan atas --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <span
                            class="hidden sm:inline-flex text-xs font-medium px-3 py-1.5 rounded-full {{ $warnaStatus }}"
                            v-pre>{{ $p->label_status }}</span>
                        <a href="{{ route('admin.pendaftaran.edit', $p) }}" title="Edit Data"
                            class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-teal-50 hover:text-teal-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <span class="sm:hidden inline-flex mt-2 text-xs font-medium px-3 py-1 rounded-full {{ $warnaStatus }}"
                    v-pre>{{ $p->label_status }}</span>

                {{-- Kontak dengan ikon --}}
                <div class="mt-4 grid sm:grid-cols-2 gap-x-6 gap-y-2.5 text-sm text-slate-600" v-pre>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        @if ($p->no_hp)
                        <a href="{{ $p::linkWa($p->no_hp) }}" target="_blank" rel="noopener"
                            class="hover:text-emerald-600" dir="ltr">{{ $p->no_hp }}</a>
                        @else - @endif
                    </div>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.5-9.75-6.5" />
                        </svg>
                        <span class="truncate">{{ $p->email ?: '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span class="truncate">{{ $lokasi ?: '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                        <span class="truncate">{{ $p->nama_sekolah ?: '-' }}</span>
                    </div>
                </div>

                @if ($p->catatan_admin)
                <p class="mt-3 text-sm text-slate-600 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2" v-pre>
                    <span class="font-medium text-amber-700">Catatan:</span> {{ $p->catatan_admin }}
                </p>
                @endif

                @if ($p->status === 'menunggu')
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="submit" form="form-syarat" formaction="{{ route('admin.pendaftaran.terima', $p) }}"
                        onclick="return confirm('Terima pendaftar ini? Persyaratan dan catatan di bawah ikut terkirim ke wali lewat WhatsApp.')"
                        class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm">
                        Terima &amp; Kirim Chat
                    </button>
                    <button type="button" @click="tolakBuka = true"
                        class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-medium hover:bg-rose-700 shadow-sm">Tolak</button>
                </div>
                @endif
            </div>

            {{-- QR --}}
            @if ($p->status === 'diterima' && $p->kode_qr)
            <div class="text-center sm:border-l sm:border-slate-100 sm:pl-6 self-center">
                <div class="inline-block p-2 bg-white ring-1 ring-slate-200 rounded-2xl">
                    {!! $p->qr_svg !!}
                </div>
                <p class="text-[11px] text-slate-400 mt-1" v-pre>Kode: {{ $p->kode_qr }}</p>
            </div>
            @endif
        </div>

        {{-- Baris statistik dengan garis warna --}}
        <div class="grid grid-cols-3 border-t border-slate-100">
            <div class="px-5 pt-4">
                <p class="text-xs text-slate-400">Syarat Terpenuhi</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-800">{{ $p->syarat_terpenuhi }}<span
                        class="text-sm font-medium text-slate-400">/{{ $totalSyarat }}</span></p>
                <div class="mt-3 h-1.5 rounded-full bg-rose-100 overflow-hidden">
                    <div class="h-full bg-rose-300 rounded-full" style="width: {{ $persenSyarat }}%"></div>
                </div>
            </div>
            <div class="px-5 pt-4">
                <p class="text-xs text-slate-400">Riwayat Pondok</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-800">{{ count($pondok) }}</p>
                <div class="mt-3 h-1.5 rounded-full bg-teal-300"></div>
            </div>
            <div class="px-5 pt-4 pb-5">
                <p class="text-xs text-slate-400">Kitab Dipelajari</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-800">{{ count($kitab) }}</p>
                <div class="mt-3 h-1.5 rounded-full bg-lime-300"></div>
            </div>
        </div>
    </div>

    {{-- ================= RINGKASAN (kartu pastel) ================= --}}
    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-800">Ringkasan</h2>
            <span class="text-xs text-slate-500 bg-slate-100 rounded-lg px-3 py-1.5" v-pre>Diperbarui {{ $diperbarui
                }}</span>
        </div>

        <div class="grid sm:grid-cols-3 gap-4" v-pre>
            {{-- Status --}}
            <div class="relative rounded-2xl bg-lime-200/80 p-4 min-h-34 flex flex-col">
                <p class="font-semibold text-slate-800 leading-tight">Status<br>Pendaftaran</p>
                <p class="text-sm text-slate-700 mt-1">{{ $p->label_status }}</p>
                <div class="mt-auto flex items-end justify-between">
                    <p class="text-[11px] text-slate-600">Daftar:<br>{{ $p->created_at->translatedFormat('d M Y, H:i')
                        }}</p>
                    <span
                        class="w-11 h-11 rounded-full bg-white/60 flex items-center justify-center text-sm font-semibold text-slate-800">
                        {{ $persenSyarat }}%
                    </span>
                </div>
            </div>

            {{-- Kelas --}}
            <div class="relative rounded-2xl bg-teal-200/80 p-4 min-h-[8.5rem] flex flex-col">
                <p class="font-semibold text-slate-800 leading-tight">Kelas<br>Ditempatkan</p>
                {{-- Nama dari Master Data > Kelas --}}
                <p class="text-sm text-slate-700 mt-1">{{ $p->kelasMaster?->nama ?? 'Belum dipilih' }}</p>
                <div class="mt-auto flex items-end justify-between">
                    <p class="text-[11px] text-slate-600">Update:<br>{{ $diperbarui }}</p>
                    <span
                        class="w-11 h-11 rounded-full bg-white/60 flex items-center justify-center text-xs font-semibold text-slate-800">
                        {{ $p->kelasMaster?->kode ?: '-' }}
                    </span>
                </div>
            </div>

            {{-- Kamar --}}
            <div class="relative rounded-2xl bg-rose-200/80 p-4 min-h-[8.5rem] flex flex-col">
                <p class="font-semibold text-slate-800 leading-tight">Kamar<br>Ditempatkan</p>
                {{-- Nama dari Master Data > Kamar --}}
                <p class="text-sm text-slate-700 mt-1" dir="auto">{{ $p->kamarMaster?->nama ?? 'Belum dipilih' }}</p>
                <div class="mt-auto flex items-end justify-between">
                    <p class="text-[11px] text-slate-600">Update:<br>{{ $diperbarui }}</p>
                    <span
                        class="w-11 h-11 rounded-full bg-white/60 flex items-center justify-center text-xs font-semibold text-slate-800">
                        {{ $p->kamarMaster?->kode ?: '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= BAGIAN-BAGIAN DATA ================= --}}
    <div class="space-y-6" v-pre>
        @foreach ($seksi as $judul => $baris)

        {{-- Riwayat pondok tampil SEBELUM "Pendidikan Formal" --}}
        @if ($judul === 'Pendidikan Formal')
        <section class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
            <h2 class="flex items-center gap-2 font-semibold text-slate-800 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Riwayat Pondok Pesantren
            </h2>
            <div class="grid sm:grid-cols-2 gap-3">
                @forelse ($pondok as $i => $r)
                <div class="rounded-2xl bg-slate-50 p-4 text-lg">
                    <p
                        class="inline-block text-[11px] font-medium text-amber-700 bg-amber-100 rounded-full px-2 py-0.5 mb-3">
                        Pondok {{ $i + 1 }}</p>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                        <div>
                            <dt class="text-xs text-slate-400">Asal Pondok</dt>
                            <dd class="font-medium text-slate-700" dir="auto">{{ $r['asal_pondok'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Nama Pengasuh</dt>
                            <dd class="font-medium text-slate-700" dir="auto">{{ $r['nama_pengasuh'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Daerah / Kota Pondok</dt>
                            <dd class="font-medium text-slate-700" dir="auto">{{ $r['nama_daerah'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Lama Belajar</dt>
                            <dd class="font-medium text-slate-700">{{ $r['tahun_belajar'] ?? '-' }} tahun</dd>
                        </div>
                    </dl>
                </div>
                @empty
                <p class="text-sm text-slate-400">Tidak ada riwayat pondok.</p>
                @endforelse
            </div>
        </section>
        @endif

        <section class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
            <h2 class="flex items-center gap-2 font-semibold text-slate-800 mb-4">
                <span class="w-2.5 h-2.5 rounded-full {{ $warnaSeksi[$loop->index % count($warnaSeksi)] }}"></span> {{
                $judul }}
            </h2>
            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                @foreach ($baris as $label => $nilai)
                <div @class(['rounded-xl bg-slate-50 px-4 py-3', 'sm:col-span-2'=> in_array($label, $lebar)])>
                    <dt class="text-sm text-slate-400 text-right">{{ $label }}</dt>
                    {{-- dir="auto": teks Arab otomatis rata kanan, teks Latin tetap rata kiri --}}
                    <dd dir="rtl" class="whitespace-pre-line font-medium text-lg text-slate-700 mt-0.5 text-right">
                        @if ($nilai && in_array($label, $labelWa))
                        <span class="inline-flex items-center gap-2">
                            <span dir="ltr">{{ $nilai }}</span>
                            <a href="{{ $p::linkWa($nilai) }}" target="_blank" rel="noopener"
                                title="Chat lewat WhatsApp" aria-label="Chat {{ $nilai }} lewat WhatsApp"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200 hover:text-emerald-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                            </a>
                        </span>
                        @else
                        {{ $nilai ?: '-' }}
                        @endif
                    </dd>
                </div>
                @endforeach
            </dl>
        </section>

        {{-- Riwayat kitab tampil SETELAH "Pendidikan Formal" --}}
        @if ($judul === 'Pendidikan Formal')
        <section class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
            <h2 class="flex items-center gap-2 font-semibold text-slate-800 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Kitab yang Pernah Dipelajari
            </h2>
            @if (count($kitab))
            <div class="overflow-x-auto rounded-2xl ring-1 ring-slate-100">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 text-xs bg-slate-50">
                        <tr>
                            <th class="py-2.5 px-4 w-12">No</th>
                            <th class="py-2.5 px-4">Nama Kitab</th>
                            <th class="py-2.5 px-4">Fan</th>
                            <th class="py-2.5 px-4">Sudah Diujikan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($kitab as $i => $k)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2.5 px-4 text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-2.5 px-4 font-medium text-slate-700" dir="auto">{{ $k['nama_kitab'] ?? '-' }}
                            </td>
                            <td class="py-2.5 px-4" dir="auto">{{ $k['fan_kitab'] ?? '-' }}</td>
                            <td class="py-2.5 px-4" dir="auto">
                                @if (($k['sudah_diujikan'] ?? '') === 'sudah')
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">أختبر</span>
                                @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">لا</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-400">Tidak ada riwayat kitab.</p>
            @endif
        </section>
        @endif
        @endforeach

        {{-- Berkas --}}
        <section class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
            <h2 class="flex items-center gap-2 font-semibold text-slate-800 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span> Berkas
            </h2>
            <div class="flex flex-wrap gap-2.5 text-sm">
                @foreach ($p::BERKAS as $kolom => $nama)
                @if ($p->$kolom)
                <a href="{{ asset('storage/' . $p->$kolom) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-50 text-teal-700 hover:bg-teal-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    {{ $nama }}
                </a>
                @else
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 text-slate-400 border border-dashed border-slate-200">{{
                    $nama }} (belum ada)</span>
                @endif
                @endforeach
            </div>
        </section>

        {{-- Persyaratan & catatan admin --}}
        <section class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-100 p-5 sm:p-6">
            {{-- Form ini TANPA @method: tombol Terima di atas ikut memakainya lewat atribut form="form-syarat" --}}
            <form id="form-syarat" method="POST" action="{{ route('admin.pendaftaran.syarat', $p) }}">
                @csrf

                <div class="flex items-center justify-between mb-4">
                    <h2 class="flex items-center gap-2 font-semibold text-slate-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Persyaratan &amp; Ketentuan Admin
                    </h2>
                    <span @class([ 'text-xs font-medium px-3 py-1 rounded-full' , 'bg-emerald-100 text-emerald-700'=>
                        $p->syarat_terpenuhi === $totalSyarat,
                        'bg-amber-100 text-amber-700' => $p->syarat_terpenuhi !== $totalSyarat,
                        ])>
                        {{ $p->syarat_terpenuhi }}/{{ $totalSyarat }} terpenuhi
                    </span>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ($p::SYARAT as $kolom => $nama)
                    @php $kolomBerkas = $p::SYARAT_BERKAS[$kolom] ?? null; @endphp

                    <div
                        class="rounded-2xl bg-slate-50 ring-1 ring-transparent p-3.5 transition hover:ring-teal-300 has-[:checked]:bg-teal-50 has-[:checked]:ring-teal-200">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="{{ $kolom }}" value="1" @checked($p->$kolom)
                            class="h-5 w-5 shrink-0 accent-teal-600 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700">{{ $nama }}</span>
                        </label>

                        @if ($kolomBerkas && $p->$kolomBerkas)
                        <div class="ml-8 mt-1">
                            <a href="{{ asset('storage/' . $p->$kolomBerkas) }}" target="_blank" rel="noopener"
                                class="text-xs text-teal-700 hover:underline">Lihat berkas yang diunggah</a>
                        </div>
                        @elseif ($kolomBerkas)
                        <p class="ml-8 mt-1 text-xs text-slate-400">Belum ada berkas unggahan</p>
                        @endif
                    </div>
                    @endforeach

                    {{-- No paspor --}}
                    <div class="rounded-2xl bg-slate-50 p-3.5">
                        <label for="syarat_no_paspor" class="block text-sm font-medium text-slate-700">No Paspor</label>
                        <input type="number" min="1" max="200" id="syarat_no_paspor" name="syarat_no_paspor"
                            value="{{ old('syarat_no_paspor', $p->syarat_no_paspor) }}"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                        @error('syarat_no_paspor')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kelas: pilihan dari Master Data > Kelas --}}
                    <div class="rounded-2xl bg-slate-50 p-3.5">
                        <label for="kelas_id" class="block text-sm font-medium text-slate-700">Kelas</label>
                        <select id="kelas_id" name="kelas_id"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                            <option value="">Pilih kelas...</option>
                            @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected((string) old('kelas_id', $p->kelas_id) === (string) $kelas->id)>
                                {{ $kelas->nama }}{{ $kelas->kode ? ' (' . $kelas->kode . ')' : '' }}{{ $kelas->is_active ? '' : ' — nonaktif' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($kelasList->isEmpty())
                        <p class="text-xs text-slate-400 mt-1">Belum ada kelas. Tambahkan di
                            <a href="{{ route('admin.master-data.index', ['tab' => 'kelas']) }}" class="text-teal-700 hover:underline">Master Data &rsaquo; Kelas</a>.</p>
                        @endif
                        @error('kelas_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kamar: pilihan dari Master Data > Kamar, dengan jumlah isi / kapasitas --}}
                    <div class="rounded-2xl bg-slate-50 p-3.5 text-right" dir="rtl">
                        <label for="kamar_id" class="block text-sm font-medium text-slate-700">Kamar</label>
                        <select id="kamar_id" name="kamar_id"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                            <option value="">Pilih kamar...</option>
                            @foreach ($kamarList as $kamar)
                            <option value="{{ $kamar->id }}" @selected((string) old('kamar_id', $p->kamar_id) === (string) $kamar->id)>
                                {{ $kamar->label_isi }}{{ $kamar->is_active ? '' : ' — nonaktif' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($kamarList->isEmpty())
                        <p class="text-xs text-slate-400 mt-1">Belum ada kamar. Tambahkan di
                            <a href="{{ route('admin.master-data.index', ['tab' => 'kamar']) }}" class="text-teal-700 hover:underline">Master Data &rsaquo; Kamar</a>.</p>
                        @endif
                        @error('kamar_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="catatan_admin" class="block text-xs font-medium text-slate-500 mb-1">Catatan Admin untuk
                        Pendaftar</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3" dir="auto"
                        placeholder="Contoh: uang pendaftaran masih kurang, mohon dilunasi sebelum berangkat"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/40">{{ old('catatan_admin', $p->catatan_admin) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Catatan ini ikut terkirim ke wali lewat WhatsApp saat siswa
                        diterima atau ditolak.</p>
                    @error('catatan_admin')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 shadow-sm">
                        Simpan Persyaratan &amp; Ketentuan Admin
                    </button>
                </div>
            </form>
        </section>
    </div>{{-- /v-pre --}}

    {{-- Modal tolak (Vue) --}}
    <div v-if="tolakBuka" v-cloak
        class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <form method="POST" action="{{ route('admin.pendaftaran.tolak', $p) }}"
            class="bg-white rounded-3xl shadow-xl p-6 w-full max-w-md space-y-3">
            @csrf
            <h3 class="font-semibold text-slate-800">Tolak pendaftar</h3>
            <textarea name="catatan_admin" v-model="alasan" rows="4" required
                placeholder="Alasan penolakan (akan dikirim ke wali)"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400/40"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" @click="tolakBuka = false"
                    class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm hover:bg-slate-200">Batal</button>
                <button :disabled="!alasan.trim()"
                    class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm hover:bg-rose-700 disabled:opacity-50">Tolak
                    &amp; Kirim Chat</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3.4.38/dist/vue.global.prod.js"></script>
<script>
    Vue.createApp({
        data: () => ({ tolakBuka: false, alasan: '' }),
    }).mount('#psb-detail');
</script>
@endpush
