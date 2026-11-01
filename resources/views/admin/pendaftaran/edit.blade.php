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

$kelasInput = 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-teal-500';
@endphp

<div class="space-y-5">
    <a href="{{ route('admin.pendaftaran.show', $p) }}" class="text-sm text-teal-700 hover:underline">&larr; Kembali ke
        detail</a>

    <div class="rounded-2xl bg-teal-700 text-white p-5">
        <h1 class="text-xl font-bold">Edit Data Pendaftar</h1>
        <p class="text-sm text-teal-100">{{ $p->nomor_pendaftaran }} · {{ $p->nama_lengkap }}</p>
    </div>

    {{-- Daftar semua kesalahan validasi --}}
    @if ($errors->any())
    <div class="p-4 rounded-lg bg-red-50 text-red-700 text-sm">
        <p class="font-medium mb-1">Data belum bisa disimpan:</p>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- enctype multipart wajib ada supaya file bisa diunggah; @method('PUT') memberi tahu Laravel ini permintaan
    update --}}
    <form method="POST" action="{{ route('admin.pendaftaran.update', $p) }}" enctype="multipart/form-data"
        class="space-y-5">
        @csrf
        @method('PUT')

        @foreach ($bagian as $judul => $isian)

        {{-- Riwayat pondok & kitab (Vue) tampil SEBELUM "Data Tambahan" --}}
        @if ($judul === 'Data Tambahan')
        <div id="riwayat-app" class="space-y-5">
            {{-- Pondok --}}
            <section class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-teal-700">Riwayat Pondok Pesantren</h2>
                    <span class="text-xs text-slate-400" v-text="pondok.length + '/5'"></span>
                </div>

                <div v-for="(row, i) in pondok" :key="row.key" class="rounded-xl border border-slate-200 p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium" v-text="'Pondok ' + (i + 1)"></span>
                        <button type="button" v-if="pondok.length > 1" @click="hapusPondok(i)"
                            class="text-sm text-red-600 hover:text-red-800">Hapus</button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input :name="'riwayat_pondok[' + i + '][asal_pondok]'" v-model="row.asal_pondok"
                            placeholder="Asal pondok pesantren" dir="auto" class="{{ $kelasInput }}">
                        <input :name="'riwayat_pondok[' + i + '][nama_pengasuh]'" v-model="row.nama_pengasuh"
                            placeholder="Nama pengasuh" dir="auto" class="{{ $kelasInput }}">
                        <input :name="'riwayat_pondok[' + i + '][nama_daerah]'" v-model="row.nama_daerah"
                            placeholder="Daerah / kota pondok" dir="auto" class="{{ $kelasInput }}">
                        <input :name="'riwayat_pondok[' + i + '][tahun_belajar]'" v-model="row.tahun_belajar"
                            type="number" min="0" max="30" placeholder="Lama belajar (tahun)" class="{{ $kelasInput }}">
                    </div>
                </div>

                <button type="button" @click="tambahPondok" :disabled="pondok.length >= 5"
                    class="px-4 py-2 rounded-lg border border-emerald-600 text-emerald-700 text-sm hover:bg-emerald-50 disabled:opacity-50">
                    + Tambah Pondok
                </button>
            </section>

            {{-- Kitab --}}
            <section class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-teal-700">Kitab yang Pernah Dipelajari</h2>
                    <span class="text-xs text-slate-400" v-text="kitab.length + '/20'"></span>
                </div>

                <div v-for="(row, i) in kitab" :key="row.key" class="rounded-xl border border-slate-200 p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium" v-text="'Kitab ' + (i + 1)"></span>
                        <button type="button" v-if="kitab.length > 1" @click="hapusKitab(i)"
                            class="text-sm text-red-600 hover:text-red-800">Hapus</button>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-3">
                        <input :name="'riwayat_kitab[' + i + '][nama_kitab]'" v-model="row.nama_kitab"
                            placeholder="Nama kitab" dir="auto" class="{{ $kelasInput }}">
                        <select :name="'riwayat_kitab[' + i + '][fan_kitab]'" v-model="row.fan_kitab"
                            class="{{ $kelasInput }}">
                            <option value="">Pilih fan...</option>
                            <option v-for="f in fan" :key="f" :value="f" v-text="f"></option>
                        </select>
                        <select :name="'riwayat_kitab[' + i + '][sudah_diujikan]'" v-model="row.sudah_diujikan"
                            class="{{ $kelasInput }}">
                            <option value="">Sudah diujikan?</option>
                            <option value="sudah">أختبر</option>
                            <option value="belum">لا</option>
                        </select>
                    </div>
                </div>

                <button type="button" @click="tambahKitab" :disabled="kitab.length >= 20"
                    class="px-4 py-2 rounded-lg border border-emerald-600 text-emerald-700 text-sm hover:bg-emerald-50 disabled:opacity-50">
                    + Tambah Kitab
                </button>
            </section>
        </div>
        @endif

        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-teal-700 mb-3">{{ $judul }}</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($isian as $k)
                @php
                [$nama, $label, $tipe] = $k;
                $opsi = $k[3] ?? [];
                // old() = isian terakhir kalau validasi gagal; kalau tidak ada, pakai data dari database
                $nilai = old($nama, $tipe === 'date' ? optional($p->$nama)->format('Y-m-d') : $p->$nama);
                @endphp

                <div @class(['sm:col-span-2'=> $tipe === 'textarea'])>
                    <label for="{{ $nama }}" class="block text-xs font-medium text-slate-500 mb-1">{{ $label }}</label>

                    @if ($tipe === 'select')
                    <select id="{{ $nama }}" name="{{ $nama }}" class="{{ $kelasInput }}">
                        <option value="">Pilih...</option>
                        @foreach ($opsi['options'] as $kode => $teks)
                        <option value="{{ $kode }}" @selected((string) $nilai===(string) $kode)>{{ $teks }}</option>
                        @endforeach
                    </select>
                    @elseif ($tipe === 'textarea')
                    <textarea id="{{ $nama }}" name="{{ $nama }}" rows="3" dir="auto"
                        class="{{ $kelasInput }}">{{ $nilai }}</textarea>
                    @else
                    <input id="{{ $nama }}" name="{{ $nama }}" type="{{ $tipe }}" value="{{ $nilai }}" @if ($tipe==='text' )
                        dir="{{ $opsi['dir'] ?? 'auto' }}" @endif @if (isset($opsi['step'])) step="{{ $opsi['step'] }}" min="0" max="100"
                        @endif class="{{ $kelasInput }}">
                    @endif

                    @isset($opsi['hint'])
                    <p class="text-xs text-slate-400 mt-1">{{ $opsi['hint'] }}</p>
                    @endisset
                    @error($nama)
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>
        </section>
        @endforeach

        {{-- Berkas --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-teal-700 mb-1">Berkas</h2>
            <p class="text-xs text-slate-400 mb-3">Pilih file hanya kalau ingin menggantinya. Kosongkan kalau berkas
                tidak berubah.</p>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($p::BERKAS as $kolom => $nama)
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">{{ $nama }}</label>
                    @if ($p->$kolom)
                    <a href="{{ asset('storage/' . $p->$kolom) }}" target="_blank"
                        class="text-sm text-teal-700 hover:underline">Lihat berkas saat ini</a>
                    @else
                    <span class="text-sm text-slate-400">Belum ada berkas</span>
                    @endif
                    <input type="file" name="{{ $kolom }}"
                        accept="{{ $kolom === 'berkas_foto' ? 'image/*' : '.jpg,.jpeg,.png,.pdf' }}"
                        class="block w-full text-sm mt-1 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    @error($kolom)
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.pendaftaran.show', $p) }}"
                class="px-5 py-2 rounded-lg border border-slate-300 text-sm">Batal</a>
            <button class="px-5 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">Simpan
                Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
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
