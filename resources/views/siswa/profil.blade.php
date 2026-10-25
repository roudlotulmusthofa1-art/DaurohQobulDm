<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Siswa | Ribath</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
@php
    $seksi = [
        'Data Pribadi' => [
            'Nama Lengkap' => $siswa->nama_lengkap, 'NIK' => $siswa->nik, 'NISN' => $siswa->nisn,
            'Jenis Kelamin' => $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : null),
            'Tempat, Tanggal Lahir' => $siswa->tempat_lahir . ', ' . optional($siswa->tanggal_lahir)->translatedFormat('d F Y'),
            'Anak ke / Jumlah Saudara' => ($siswa->anak_ke ?? '-') . ' / ' . ($siswa->jumlah_saudara ?? '-'),
        ],
        'Alamat' => [
            'Alamat' => $siswa->alamat, 'Kelurahan/Desa' => $siswa->kelurahan, 'Kecamatan' => $siswa->kecamatan,
            'Kota/Kabupaten' => $siswa->kota, 'Provinsi' => $siswa->provinsi, 'Kode Pos' => $siswa->kode_pos,
        ],
        'Orang Tua / Wali' => [
            'Nama Ayah' => $siswa->nama_ayah, 'Pekerjaan Ayah' => $siswa->pekerjaan_ayah,
            'Nama Ibu' => $siswa->nama_ibu, 'Pekerjaan Ibu' => $siswa->pekerjaan_ibu,
            'Nama Wali' => $siswa->nama_wali, 'No. WhatsApp Wali' => $siswa->no_hp_wali,
        ],
        'Pendidikan & Program' => [
            'Asal Sekolah' => $siswa->asal_sekolah, 'Tahun Lulus' => $siswa->tahun_lulus,
            'Program Pilihan' => $siswa->program_pilihan, 'Hafalan' => ($siswa->hafalan_juz ?? 0) . ' juz',
        ],
    ];
@endphp

<header class="bg-teal-700 text-white">
    <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
        <span class="font-semibold">Ribath — Akun Siswa</span>
        <form method="POST" action="{{ route('siswa.logout') }}">
            @csrf
            <button class="text-sm bg-white/15 hover:bg-white/25 rounded-lg px-3 py-1.5">Keluar</button>
        </form>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 py-6 space-y-5">
    <!-- Kartu identitas -->
    <section class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col sm:flex-row gap-5 items-center sm:items-start">
        @if ($siswa->foto_url)
            <img src="{{ $siswa->foto_url }}" alt="Foto" class="w-28 h-36 object-cover rounded-xl border border-slate-200">
        @endif
        <div class="text-center sm:text-left">
            <h1 class="text-xl font-bold text-slate-800">{{ $siswa->nama_lengkap }}</h1>
            <p class="text-sm text-slate-500">No. Pendaftaran: <strong>{{ $siswa->nomor_pendaftaran }}</strong></p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-700">
                {{ $siswa->label_status }}
            </span>
            @if ($siswa->diproses_at)
                <p class="text-xs text-slate-400 mt-2">Diterima pada {{ $siswa->diproses_at->translatedFormat('d F Y') }}</p>
            @endif
            @if ($siswa->catatan_admin)
                <p class="text-sm text-slate-600 mt-2">Catatan: {{ $siswa->catatan_admin }}</p>
            @endif
        </div>
    </section>

    @foreach ($seksi as $judul => $baris)
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-teal-700 mb-3">{{ $judul }}</h2>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @foreach ($baris as $label => $nilai)
                    <div>
                        <dt class="text-slate-400 text-xs">{{ $label }}</dt>
                        <dd class="text-slate-800">{{ $nilai ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endforeach

    <section class="bg-white rounded-2xl border border-slate-200 p-5">
        <h2 class="font-semibold text-teal-700 mb-3">Berkas</h2>
        <div class="flex flex-wrap gap-3 text-sm">
            @foreach (['berkas_foto' => 'Pas Foto', 'berkas_kk' => 'Kartu Keluarga', 'berkas_akta' => 'Akta Kelahiran'] as $kolom => $nama)
                @if ($siswa->$kolom)
                    <a href="{{ asset('storage/' . $siswa->$kolom) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200">{{ $nama }}</a>
                @endif
            @endforeach
        </div>
    </section>
</main>
</body>
</html>
