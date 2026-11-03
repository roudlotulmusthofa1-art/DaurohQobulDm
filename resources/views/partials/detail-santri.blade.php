{{--
    Tampilan data lengkap santri/peserta, dipakai BERSAMA oleh:
    - admin/pendaftaran/show.blade.php  -> ['admin' => true,  'sisipan' => 'admin.pendaftaran._persyaratan']
    - siswa/profil.blade.php            -> ['admin' => false, 'sisipan' => 'siswa._persyaratan']
    $admin = false: tombol edit & Terima/Tolak tidak tampil (hanya melihat).
    $judul (opsional): label kecil di kartu atas, mis. 'Detail Peserta' untuk dasbor asatid.
    Dipakai juga di asatid/peserta-detail.blade.php -> ['admin' => false, 'judul' => 'Detail Peserta', 'rahasia' => true, 'sisipan' => 'siswa._persyaratan']
    $rahasia (opsional, bawaan false): sembunyikan email, catatan admin, dan berkas (untuk asatid).
    $sisipan: view tambahan yang ditaruh di akhir grid (form persyaratan admin / daftar persyaratan siswa).
--}}
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
'Pernah Mengikuti Dauroh Qobul' => $p::PERNAH_DAUROH[$p->pernah_dauroh] ?? null,
],
];

// Mode rahasia (dasbor asatid): email tidak ditampilkan
$rahasia = $rahasia ?? false;
if ($rahasia) {
    unset($seksi['Data Pribadi']['Email']);
}

// Label yang isinya panjang -> ditampilkan bertumpuk & memakai lebar penuh
$lebar = ['Alamat Lengkap', 'Tujuan Belajar', 'Rencana Setelah Lulus'];
// Label yang isinya nomor WhatsApp -> diberi ikon yang bisa diklik
$labelWa = ['No. WhatsApp', 'No. WhatsApp Wali', 'No. WhatsApp Muarrif', 'Kontak Darurat (WhatsApp)'];
// Bagian yang kartunya selebar halaman (isinya banyak)
$seksiPenuh = ['Wali & Muarrif', 'Data Tambahan'];

// riwayat_pondok & riwayat_kitab disimpan sebagai JSON (di model sudah di-cast ke array)
$pondok = $p->riwayat_pondok ?? [];
$kitab = $p->riwayat_kitab ?? [];

// Angka untuk baris statistik & kartu ringkasan
$totalSyarat = count($p::SYARAT);
$persenSyarat = $totalSyarat ? round($p->syarat_terpenuhi / $totalSyarat * 100) : 0;
$lokasi = collect([$p->kota, $p->provinsi])->filter()->implode(', ');
$diperbarui = optional($p->updated_at)->translatedFormat('d M Y, H:i');

// Warna badge status (di atas latar hijau tua)
$warnaStatus = [
'menunggu' => 'bg-amber-300/15 text-amber-200 ring-amber-300/30',
'diterima' => 'bg-emerald-300/15 text-emerald-200 ring-emerald-300/30',
'ditolak' => 'bg-rose-300/15 text-rose-200 ring-rose-300/30',
][$p->status] ?? 'bg-white/10 text-white/80 ring-white/20';
$titikStatus = [
'menunggu' => 'bg-amber-400',
'diterima' => 'bg-emerald-400',
'ditolak' => 'bg-rose-400',
][$p->status] ?? 'bg-slate-400';

// Ikon (path SVG gaya Heroicons outline)
$ikon = [
'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
'pin' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
'cap' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
'sparkles' => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z',
'home' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
'book' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
'file' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
'badge' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
'phone' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z',
'mail' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.5-9.75-6.5',
'pencil' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z',
'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
'check' => 'M4.5 12.75l6 6 9-13.5',
'x' => 'M6 18L18 6M6 6l12 12',
'open' => 'M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25',
'back' => 'M15.75 19.5L8.25 12l7.5-7.5',
'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
];
$svg = fn ($nama, $kelas = 'w-5 h-5') => '<svg class="' . $kelas . '" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . $ikon[$nama] . '"/></svg>';

// Warna ikon judul tiap bagian (ditulis lengkap supaya Tailwind ikut membuat kelasnya)
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

// Judul kartu: ikon berwarna + judul + keterangan kecil
$judulKartu = fn ($judul, $namaIkon, $warna, $ket = null) =>
'<div class="flex items-center gap-3">'
. '<span class="w-10 h-10 shrink-0 rounded-2xl ring-1 flex items-center justify-center ' . $nada[$warna] . '">' . $svg($namaIkon) . '</span>'
. '<div class="min-w-0"><h2 class="text-[15px] font-bold text-slate-800 leading-tight">' . e($judul) . '</h2>'
. ($ket ? '<p class="text-xs text-slate-400 mt-0.5">' . e($ket) . '</p>' : '')
. '</div></div>';

// Gaya kartu putih yang dipakai berulang
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

    {{-- ================= KARTU PROFIL (hijau tua) ================= --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white shadow-[0_24px_48px_-24px_rgba(7,31,26,0.6)]">
        {{-- Hiasan latar: pola titik + cahaya lembut --}}
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-28 -right-20 w-80 h-80 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/4 w-80 h-80 rounded-full bg-amber-300/10 blur-3xl"></div>

        <div class="relative p-5 sm:p-8 flex flex-col lg:flex-row gap-6 lg:gap-8">
            {{-- Foto --}}
            <div class="shrink-0 self-center lg:self-start">
                <div class="relative">
                    @if ($p->foto_url)
                    <img data-perbesar src="{{ $p->foto_url }}" alt="Foto {{ $p->nama_lengkap }}"
                        class="w-40 h-48 sm:w-48 sm:h-56 object-cover rounded-3xl ring-4 ring-white/10 shadow-2xl shadow-black/30">
                    @else
                    <div class="w-40 h-48 sm:w-48 sm:h-56 rounded-3xl ring-4 ring-white/10 bg-gradient-to-br from-emerald-300 to-lime-200 flex items-center justify-center text-6xl font-extrabold text-[#0b332c] shadow-2xl shadow-black/30"
                        v-pre>
                        {{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}
                    </div>
                    @endif
                    <span class="absolute -bottom-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-[#0b332c] ring-1 ring-white/15 px-3 py-1 text-[11px] font-semibold shadow-lg" v-pre>
                        <span class="relative flex w-2 h-2">
                            @if ($p->status === 'menunggu')
                            <span class="absolute inline-flex h-full w-full rounded-full {{ $titikStatus }} opacity-75 animate-ping"></span>
                            @endif
                            <span class="relative inline-flex w-2 h-2 rounded-full {{ $titikStatus }}"></span>
                        </span>
                        {{ $p->label_status }}
                    </span>
                </div>
            </div>

            {{-- Identitas --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0" v-pre>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ $judul ?? ($admin ? 'Detail Santri' : 'Profil Saya') }}</p>
                        <h1 class="mt-1 text-2xl sm:text-[34px] leading-tight font-extrabold tracking-tight break-words">{{ $p->nama_lengkap }}</h1>
                        @if ($p->nama_lengkap_arab)
                        <p class="mt-1 text-2xl text-emerald-100/90" dir="rtl">{{ $p->nama_lengkap_arab }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 font-semibold tracking-wide">
                                # {{ $p->nomor_pendaftaran }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/5 px-3 py-1 text-white/60">
                                {!! $svg('clock', 'w-3.5 h-3.5') !!} mendaftar {{ $p->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    {{-- Status + tombol edit --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="hidden sm:inline-flex text-xs font-semibold px-3 py-1.5 rounded-full ring-1 {{ $warnaStatus }}"
                            v-pre>{{ $p->label_status }}</span>
                        @if ($admin && $p->user_id && \App\Support\MasukSebagai::bolehDipakai(auth()->user()))
                        {{-- Super Admin: buka dasbor siswa ini (mode lihat) --}}
                        <form method="POST" action="{{ route('admin.masuk-sebagai', $p->user_id) }}" @submit="konfirmasi" data-konfirmasi="Buka dasbor siswa {{ $p->nama_lengkap }}? (mode lihat, perubahan tidak disimpan)">
                            @csrf
                            <button title="Lihat dasbor siswa (mode lihat)" aria-label="Lihat dasbor siswa"
                                class="w-10 h-10 rounded-2xl bg-amber-300/90 text-[#3b2a00] hover:bg-amber-200 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                        </form>
                        @endif
                        @if ($admin)
                        <a href="{{ route('admin.pendaftaran.edit', $p) }}" title="Edit Data" aria-label="Edit data"
                            class="w-10 h-10 rounded-2xl bg-white/10 ring-1 ring-white/15 text-white hover:bg-white hover:text-[#0b332c] flex items-center justify-center transition-colors">
                            {!! $svg('pencil', 'w-4 h-4') !!}
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="mt-5 grid sm:grid-cols-2 gap-2.5 text-sm" v-pre>
                    <div class="flex items-center gap-3 min-w-0 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3 py-2.5">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-emerald-400/15 text-emerald-300 flex items-center justify-center">{!! $svg('phone', 'w-4 h-4') !!}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-white/40">WhatsApp</p>
                            @if ($p->no_hp)
                            <a href="{{ $p::linkWa($p->no_hp) }}" target="_blank" rel="noopener"
                                class="font-medium text-white hover:text-emerald-300 transition-colors" dir="ltr">{{ $p->no_hp }}</a>
                            @else <p class="text-white/50">-</p> @endif
                        </div>
                    </div>
                    @if ($rahasia)
                    <div class="flex items-center gap-3 min-w-0 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3 py-2.5">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-cyan-400/15 text-cyan-300 flex items-center justify-center">{!! $svg('cap', 'w-4 h-4') !!}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-white/40">Kelas</p>
                            <p class="font-medium truncate">{{ $p->kelasMaster?->nama ?? 'Belum dipilih' }}</p>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-3 min-w-0 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3 py-2.5">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-cyan-400/15 text-cyan-300 flex items-center justify-center">{!! $svg('mail', 'w-4 h-4') !!}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-white/40">Email</p>
                            <p class="font-medium truncate">{{ $p->email ?: '-' }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center gap-3 min-w-0 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3 py-2.5">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-amber-300/15 text-amber-200 flex items-center justify-center">{!! $svg('pin', 'w-4 h-4') !!}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-white/40">Domisili</p>
                            <p class="font-medium truncate">{{ $lokasi ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 min-w-0 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3 py-2.5">
                        <span class="w-8 h-8 shrink-0 rounded-xl bg-lime-300/15 text-lime-200 flex items-center justify-center">{!! $svg('cap', 'w-4 h-4') !!}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-white/40">Sekolah / Lembaga</p>
                            <p class="font-medium truncate">{{ $p->nama_sekolah ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                @if ($p->catatan_admin && ! $rahasia)
                <div class="mt-3 flex gap-2.5 text-sm rounded-2xl bg-amber-300/10 ring-1 ring-amber-300/25 px-3.5 py-2.5 text-amber-50" v-pre>
                    <span class="font-semibold text-amber-200 shrink-0">Catatan:</span>
                    <span class="whitespace-pre-line">{{ $p->catatan_admin }}</span>
                </div>
                @endif

                @if ($admin && $p->status === 'menunggu')
                <div class="mt-5 flex flex-wrap gap-2.5">
                    <button type="submit" form="form-syarat" formaction="{{ route('admin.pendaftaran.terima', $p) }}"
                        @click="konfirmasi" data-konfirmasi="Terima pendaftar ini? Persyaratan dan catatan di bawah ikut terkirim ke wali lewat WhatsApp."
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-400 text-[#0b332c] text-sm font-bold hover:bg-emerald-300 shadow-lg shadow-emerald-500/20 transition-colors">
                        {!! $svg('check', 'w-4 h-4') !!} Terima &amp; Kirim Chat
                    </button>
                    <button type="button" @click="tolakBuka = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white/5 ring-1 ring-rose-300/40 text-rose-100 text-sm font-semibold hover:bg-rose-500 hover:ring-rose-500 hover:text-white transition-colors">
                        {!! $svg('x', 'w-4 h-4') !!} Tolak
                    </button>
                </div>
                @endif

                {{-- Sudah diterima / ditolak: tombol kirim ulang chat konfirmasi selalu ada (bila chat gagal / perlu dikirim lagi) --}}
                @if ($admin && in_array($p->status, ['diterima', 'ditolak'], true))
                <form method="POST" action="{{ route('admin.pendaftaran.kirim-ulang-chat', $p) }}" class="mt-5 flex flex-wrap items-center gap-3">
                    @csrf
                    <button type="submit" @click="konfirmasi"
                        data-konfirmasi="Kirim ulang chat konfirmasi {{ $p->status === 'diterima' ? 'PENERIMAAN' : 'PENOLAKAN' }} ke WhatsApp {{ $p->status === 'diterima' ? 'wali' : 'wali & pendaftar' }}?"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/25 text-white text-sm font-semibold hover:bg-emerald-400 hover:text-[#0b332c] hover:ring-emerald-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Kirim Ulang Chat Konfirmasi
                    </button>
                    <span class="text-xs text-white/50">Pakai bila chat {{ $p->status === 'diterima' ? 'penerimaan' : 'penolakan' }} sebelumnya gagal terkirim.</span>
                </form>
                @endif
            </div>

            {{-- QR --}}
            @if ($p->status === 'diterima' && $p->kode_qr)
            <div class="text-center self-center lg:pl-8 lg:border-l lg:border-white/10">
                <div class="inline-block p-3 bg-white rounded-3xl shadow-xl shadow-black/20 [&_svg]:block [&_svg]:w-32 [&_svg]:h-32 sm:[&_svg]:w-36 sm:[&_svg]:h-36">
                    {!! $p->qr_svg !!}
                </div>
                <p class="text-[11px] text-white/50 mt-2 tracking-wide" v-pre>Kode: <span class="font-semibold text-white/80">{{ $p->kode_qr }}</span></p>
            </div>
            @endif
        </div>

        {{-- Baris statistik --}}
        <div class="relative grid grid-cols-3 border-t border-white/10 bg-black/10 divide-x divide-white/10">
            <div class="px-4 sm:px-6 py-4">
                <p class="text-[11px] uppercase tracking-wider text-white/45">Syarat Terpenuhi</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold">{{ $p->syarat_terpenuhi }}<span class="text-sm font-medium text-white/40">/{{ $totalSyarat }}</span></p>
                <div class="mt-2.5 h-1.5 rounded-full bg-white/10 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-lime-300" style="width: {{ $persenSyarat }}%"></div>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4 flex items-center gap-3">
                <span class="hidden sm:flex w-11 h-11 shrink-0 rounded-2xl bg-amber-300/15 text-amber-200 items-center justify-center">{!! $svg('home') !!}</span>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-white/45">Riwayat Pondok</p>
                    <p class="mt-1 text-xl sm:text-2xl font-extrabold">{{ count($pondok) }}</p>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4 flex items-center gap-3">
                <span class="hidden sm:flex w-11 h-11 shrink-0 rounded-2xl bg-cyan-300/15 text-cyan-200 items-center justify-center">{!! $svg('book') !!}</span>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-white/45">Kitab Dipelajari</p>
                    <p class="mt-1 text-xl sm:text-2xl font-extrabold">{{ count($kitab) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RINGKASAN PENEMPATAN ================= --}}
    <div class="grid sm:grid-cols-3 gap-4" v-pre>
        {{-- Status + cincin persentase syarat --}}
        <div class="{{ $kartu }} p-5 flex items-center gap-4">
            <div class="relative w-20 h-20 shrink-0 rounded-full"
                style="background: conic-gradient(#10b981 {{ $persenSyarat * 3.6 }}deg, #e7efeb 0deg);">
                <div class="absolute inset-[7px] rounded-full bg-white flex flex-col items-center justify-center">
                    <span class="text-lg font-extrabold text-slate-800 leading-none">{{ $persenSyarat }}%</span>
                    <span class="text-[9px] text-slate-400 mt-0.5">syarat</span>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-400">Status Pendaftaran</p>
                <p class="mt-0.5 text-lg font-bold text-slate-800">{{ $p->label_status }}</p>
                <p class="mt-1 inline-flex items-center gap-1 text-[11px] text-slate-400">
                    {!! $svg('calendar', 'w-3.5 h-3.5') !!} {{ $p->created_at->translatedFormat('d M Y, H:i') }}
                </p>
            </div>
        </div>

        {{-- Kelas (nama dari Master Data > Kelas) --}}
        <div class="relative overflow-hidden rounded-3xl p-5 bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-[0_12px_32px_-16px_rgba(5,150,105,0.7)]">
            <div class="pointer-events-none absolute -right-6 -bottom-8 text-white/10">{!! $svg('cap', 'w-32 h-32') !!}</div>
            <div class="relative flex items-start justify-between gap-3">
                <span class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center">{!! $svg('cap') !!}</span>
                
            </div>
            <p class="relative mt-4 text-xs font-medium text-white/70">Kelas Ditempatkan</p>
            <p class="relative text-lg font-bold">{{ $p->kelasMaster?->nama ?? 'Belum dipilih' }}</p>

        </div>

        {{-- Kamar (nama dari Master Data > Kamar) --}}
        <div class="relative overflow-hidden rounded-3xl p-5 bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-[0_12px_32px_-16px_rgba(234,88,12,0.7)]">
            <div class="pointer-events-none absolute -right-6 -bottom-8 text-white/15">{!! $svg('home', 'w-32 h-32') !!}</div>
            <div class="relative flex items-start justify-between gap-3">
                <span class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center">{!! $svg('home') !!}</span>

            </div>
            <p class="relative mt-4 text-xs font-medium text-white/75">Kamar Ditempatkan</p>
            <p class="relative text-lg font-bold" dir="auto">{{ $p->kamarMaster?->nama ?? 'Belum dipilih' }}</p>
            <p class="relative mt-1 text-[11px] text-white/70">Kasur: {{ $p->nomor_kasur ? 'No. ' . $p->nomor_kasur : 'Belum dipilih' }}</p>
        </div>
    </div>

    {{-- ================= CATATAN DARI PESERTA ================= --}}
    @if ($p->catatan_peserta)
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-50 via-white to-white ring-1 ring-sky-200/70 p-5 sm:p-6 shadow-[0_12px_32px_-16px_rgba(2,132,199,0.25)]" v-pre>
        <span class="absolute left-0 top-5 bottom-5 w-1 rounded-r-full bg-gradient-to-b from-sky-400 to-cyan-500"></span>
        <div class="flex items-center gap-3 mb-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-sky-500 text-white flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
            </span>
            <div>
                <h2 class="text-[15px] font-bold text-slate-800 leading-tight">Catatan dari Peserta</h2>
                <p class="text-xs text-slate-400 mt-0.5">Ditulis peserta saat mendaftar</p>
            </div>
        </div>
        <p dir="auto" class="whitespace-pre-line text-[15px] leading-relaxed text-slate-700 bg-white rounded-2xl ring-1 ring-sky-100 px-4 py-3">{{ $p->catatan_peserta }}</p>
    </div>
    @endif

    {{-- ================= BAGIAN-BAGIAN DATA =================
         Tidak memakai v-pre: form Persyaratan di bawah memakai Vue (kamar & nomor kasur).
         Teks data aman karena {{ }} Blade sudah dilindungi App\Support\TeksAman. --}}
    <div class="grid xl:grid-cols-2 gap-6 items-start">
        @foreach ($seksi as $judul => $baris)
        @php [$namaIkon, $warna, $ket] = $gayaSeksi[$judul]; $penuh = in_array($judul, $seksiPenuh); @endphp

        {{-- Riwayat pondok tampil SEBELUM "Pendidikan Formal" --}}
        @if ($judul === 'Pendidikan Formal')
        <section class="{{ $kartu }} p-5 sm:p-6 xl:col-span-2">
            <div class="flex items-center justify-between gap-3 mb-5">
                {!! $judulKartu('Riwayat Pondok Pesantren', 'home', 'amber', 'Pondok yang pernah ditempati') !!}
                <span class="text-xs font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-3 py-1">{{ count($pondok) }} pondok</span>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                @forelse ($pondok as $i => $r)
                <div class="relative rounded-2xl bg-gradient-to-br from-amber-50/80 via-white to-white ring-1 ring-amber-100 p-4 pl-5 overflow-hidden">
                    <span class="absolute left-0 top-4 bottom-4 w-1 rounded-r-full bg-gradient-to-b from-amber-400 to-orange-400"></span>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                        <p class="font-bold text-slate-800 truncate" dir="auto">{{ $r['asal_pondok'] ?? '-' }}</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Nama Pengasuh</dt>
                            <dd class="mt-0.5 font-semibold text-slate-700" dir="auto">{{ $r['nama_pengasuh'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Daerah / Kota</dt>
                            <dd class="mt-0.5 font-semibold text-slate-700" dir="auto">{{ $r['nama_daerah'] ?? '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Lama Belajar</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-100/70 rounded-full px-2.5 py-1">
                                    {!! $svg('clock', 'w-3.5 h-3.5') !!} {{ $r['tahun_belajar'] ?? '-' }} tahun
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
                @empty
                <div class="md:col-span-2 rounded-2xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-400">Tidak ada riwayat pondok.</div>
                @endforelse
            </div>
        </section>
        @endif

        <section @class([$kartu, 'p-5 sm:p-6', 'xl:col-span-2' => $penuh])>
            <div class="mb-5">{!! $judulKartu($judul, $namaIkon, $warna, $ket) !!}</div>
            <dl @class(['grid gap-2.5 text-sm', 'md:grid-cols-2' => $penuh])>
                @foreach ($baris as $label => $nilai)
                @if (in_array($label, $lebar))
                {{-- Isi panjang: label di atas, isi di bawah --}}
                <div @class(['rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3', 'md:col-span-2' => $penuh])>
                    <dt class="text-xs font-medium text-slate-400">{{ $label }}</dt>
                    <dd dir="auto" class="mt-1 whitespace-pre-line text-[15px] font-semibold text-slate-700 leading-relaxed">{{ $nilai ?: '-' }}</dd>
                </div>
                @else
                {{-- Isi pendek: label kiri, isi kanan --}}
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3 hover:bg-white hover:ring-emerald-100 transition-colors">
                    <dt class="text-xs font-medium text-slate-400 shrink-0 max-w-[45%]">{{ $label }}</dt>
                    <dd dir="auto" class="min-w-0 text-right text-[15px] font-semibold text-slate-800 break-words">
                        @if ($nilai && in_array($label, $labelWa))
                        <span class="inline-flex items-center gap-2">
                            <span dir="ltr">{{ $nilai }}</span>
                            <a href="{{ $p::linkWa($nilai) }}" target="_blank" rel="noopener"
                                title="Chat lewat WhatsApp" aria-label="Chat {{ $nilai }} lewat WhatsApp"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 hover:bg-emerald-600 hover:scale-105 transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
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
                @endif
                @endforeach
            </dl>
        </section>

        {{-- Riwayat kitab tampil SETELAH "Pendidikan Formal" --}}
        @if ($judul === 'Pendidikan Formal')
        <section class="{{ $kartu }} p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-5">
                {!! $judulKartu('Kitab yang Pernah Dipelajari', 'book', 'cyan', 'Riwayat kajian kitab') !!}
                <span class="text-xs font-semibold text-cyan-700 bg-cyan-50 ring-1 ring-cyan-100 rounded-full px-3 py-1">{{ count($kitab) }} kitab</span>
            </div>
            @if (count($kitab))
            <div class="overflow-x-auto rounded-2xl ring-1 ring-slate-100">
                <table class="w-full text-sm">
                    <thead class="text-left text-[11px] uppercase tracking-wider text-[#0b332c]/50 bg-[#f1f7f4]">
                        <tr>
                            <th class="py-3 px-4 w-12 font-semibold">No</th>
                            <th class="py-3 px-4 font-semibold">Nama Kitab</th>
                            <th class="py-3 px-4 font-semibold">Fan</th>
                            <th class="py-3 px-4 font-semibold">Diujikan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($kitab as $i => $k)
                        <tr class="hover:bg-emerald-50/40 transition-colors">
                            <td class="py-3 px-4">
                                <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 text-xs font-semibold flex items-center justify-center">{{ $i + 1 }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800" dir="auto">{{ $k['nama_kitab'] ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600" dir="auto">{{ $k['fan_kitab'] ?? '-' }}</td>
                            <td class="py-3 px-4" dir="auto">
                                @if (($k['sudah_diujikan'] ?? '') === 'sudah')
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">{!! $svg('check', 'w-3 h-3') !!} أختبر</span>
                                @else
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">لا</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-400">Tidak ada riwayat kitab.</div>
            @endif
        </section>
        @endif
        @endforeach

        {{-- Berkas (disembunyikan dalam mode rahasia) --}}
        @unless ($rahasia)
        <section class="{{ $kartu }} p-5 sm:p-6 xl:col-span-2">
            <div class="mb-5">{!! $judulKartu('Berkas', 'file', 'teal', 'Dokumen yang diunggah pendaftar') !!}</div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 text-sm">
                @foreach ($p::BERKAS as $kolom => $nama)
                @if ($p->$kolom)
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->$kolom) }}" target="_blank" rel="noopener"
                    class="group flex items-center gap-3 rounded-2xl bg-white ring-1 ring-teal-100 px-3.5 py-3 hover:ring-teal-300 hover:shadow-md hover:shadow-teal-900/5 hover:-translate-y-0.5 transition">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 text-white flex items-center justify-center shadow-sm">{!! $svg('file', 'w-5 h-5') !!}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-slate-800 truncate">{{ $nama }}</span>
                        <span class="block text-xs text-teal-600">Buka berkas</span>
                    </span>
                    <span class="text-slate-300 group-hover:text-teal-600 transition-colors">{!! $svg('open', 'w-4 h-4') !!}</span>
                </a>
                @else
                <div class="flex items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-3.5 py-3">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center">{!! $svg('file', 'w-5 h-5') !!}</span>
                    <span class="min-w-0">
                        <span class="block font-medium text-slate-500 truncate">{{ $nama }}</span>
                        <span class="block text-xs text-slate-400">Belum diunggah</span>
                    </span>
                </div>
                @endif
                @endforeach
            </div>
        </section>
        @endunless

        @if (!empty($sisipan))
        @include($sisipan)
        @endif
    </div>
