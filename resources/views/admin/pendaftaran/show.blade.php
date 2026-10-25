{{-- Konten saja. Layout admin memuat @stack('scripts'). --}}
@extends('layouts.admin')

@section('content')
@php
    $seksi = [
        'Data Pribadi' => [
            'Nama Lengkap' => $p->nama_lengkap, 'NIK' => $p->nik, 'NISN' => $p->nisn,
            'Jenis Kelamin' => $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'Tempat, Tgl Lahir' => $p->tempat_lahir . ', ' . optional($p->tanggal_lahir)->translatedFormat('d F Y'),
            'Anak ke / Jml Saudara' => ($p->anak_ke ?? '-') . ' / ' . ($p->jumlah_saudara ?? '-'),
        ],
        'Alamat' => [
            'Alamat' => $p->alamat, 'Kelurahan' => $p->kelurahan, 'Kecamatan' => $p->kecamatan,
            'Kota/Kab.' => $p->kota, 'Provinsi' => $p->provinsi, 'Kode Pos' => $p->kode_pos,
        ],
        'Orang Tua / Wali' => [
            'Ayah' => $p->nama_ayah, 'Pekerjaan Ayah' => $p->pekerjaan_ayah,
            'Ibu' => $p->nama_ibu, 'Pekerjaan Ibu' => $p->pekerjaan_ibu,
            'Wali' => $p->nama_wali, 'WhatsApp Wali' => $p->no_hp_wali,
        ],
        'Pendidikan & Program' => [
            'Asal Sekolah' => $p->asal_sekolah, 'Tahun Lulus' => $p->tahun_lulus,
            'Program' => $p->program_pilihan, 'Hafalan' => ($p->hafalan_juz ?? 0) . ' juz',
        ],
    ];
@endphp

<div class="space-y-5" id="psb-detail">
    <a href="{{ route('admin.pendaftaran.index') }}" class="text-sm text-teal-700 hover:underline">&larr; Kembali</a>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col sm:flex-row gap-5">
        @if ($p->foto_url)
            <img src="{{ $p->foto_url }}" class="w-28 h-36 object-cover rounded-xl border" alt="Foto">
        @endif
        <div class="flex-1">
            <h1 class="text-xl font-bold">{{ $p->nama_lengkap }}</h1>
            <p class="text-sm text-slate-500">{{ $p->nomor_pendaftaran }} · Daftar {{ $p->created_at->translatedFormat('d F Y H:i') }}</p>
            <p class="mt-2 text-sm">Status: <strong>{{ $p->label_status }}</strong></p>
            @if ($p->catatan_admin)
                <p class="text-sm text-slate-600 mt-1">Catatan: {{ $p->catatan_admin }}</p>
            @endif

            @if ($p->status === 'menunggu')
                <div class="mt-4 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.pendaftaran.terima', $p) }}"
                          onsubmit="return confirm('Terima pendaftar ini dan kirim chat WhatsApp ke wali?')">
                        @csrf
                        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">Terima &amp; Kirim Chat</button>
                    </form>
                    <button type="button" @click="tolakBuka = true"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">Tolak</button>
                </div>
            @endif
        </div>
    </div>

    @foreach ($seksi as $judul => $baris)
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-teal-700 mb-3">{{ $judul }}</h2>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @foreach ($baris as $label => $nilai)
                    <div><dt class="text-xs text-slate-400">{{ $label }}</dt><dd>{{ $nilai ?: '-' }}</dd></div>
                @endforeach
            </dl>
        </section>
    @endforeach

    <section class="bg-white rounded-2xl border border-slate-200 p-5">
        <h2 class="font-semibold text-teal-700 mb-3">Berkas</h2>
        <div class="flex flex-wrap gap-3 text-sm">
            @foreach (['berkas_foto' => 'Pas Foto', 'berkas_kk' => 'Kartu Keluarga', 'berkas_akta' => 'Akta Kelahiran'] as $kolom => $nama)
                @if ($p->$kolom)
                    <a href="{{ asset('storage/' . $p->$kolom) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200">{{ $nama }}</a>
                @endif
            @endforeach
        </div>
    </section>

    {{-- Modal tolak (Vue) --}}
    <div v-if="tolakBuka" v-cloak class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
        <form method="POST" action="{{ route('admin.pendaftaran.tolak', $p) }}"
              class="bg-white rounded-2xl p-6 w-full max-w-md space-y-3">
            @csrf
            <h3 class="font-semibold">Tolak pendaftar</h3>
            <textarea name="catatan_admin" v-model="alasan" rows="4" required placeholder="Alasan penolakan (akan dikirim ke wali)"
                      class="w-full border border-slate-300 rounded-lg p-2 text-sm"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" @click="tolakBuka = false" class="px-4 py-2 rounded-lg border text-sm">Batal</button>
                <button :disabled="!alasan.trim()" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm disabled:opacity-50">Tolak &amp; Kirim Chat</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
    Vue.createApp({
        data: () => ({ tolakBuka: false, alasan: '' }),
    }).mount('#psb-detail');
</script>
@endpush
