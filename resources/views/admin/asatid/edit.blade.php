{{--
    Admin > Data Asatid > Edit
    Admin mengubah data asatid. Bila akun login sudah ada, nama/email/password akun ikut diperbarui.
    Vue: pratinjau pas foto baru, tampil/sembunyikan password, tanda "belum disimpan".
--}}
@extends('layouts.admin')

@section('title', __('Edit Asatid'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$isian = 'mt-1.5 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15';
$label = 'text-sm font-semibold text-slate-700';
$salah = fn ($kolom) => $errors->has($kolom) ? 'border-rose-300 bg-rose-50/40' : '';
@endphp

<div id="halaman-edit-asatid" class="space-y-6 pb-6 mx-4 md:mx-10 lg:mx-20">
    <a href="{{ route('admin.asatid.show', $a) }}" @click="kembali"
        class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0b332c] transition-colors">
        <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center group-hover:-translate-x-0.5 transition-transform">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </span>
        {{ __('Kembali') }}
    </a>

    @if ($errors->any())
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
        <p class="font-semibold">{{ __('Periksa lagi isian yang ditandai merah:') }}</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.asatid.update', $a) }}" enctype="multipart/form-data" class="space-y-6"
        @input="diubah = true" @change="diubah = true" @submit="diubah = false">
        @csrf
        @method('PUT')

        {{-- ===== Judul + pas foto ===== --}}
        <div class="{{ $kartu }} p-5 sm:p-6 flex flex-col sm:flex-row gap-5 items-start">
            <div class="shrink-0">
                <img data-perbesar="{{ __('Pas foto :nama', ['nama' => $a->nama_lengkap]) }}" v-if="pratinjauFoto" :src="pratinjauFoto" alt="{{ __('Pas foto') }}" class="w-28 h-36 object-cover rounded-2xl ring-1 ring-slate-200">
                <div v-else class="w-28 h-36 rounded-2xl bg-gradient-to-br from-emerald-300 to-lime-200 flex items-center justify-center text-4xl font-extrabold text-[#0b332c]">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</div>
                <label class="mt-2 block text-center text-xs font-semibold text-emerald-700 hover:underline cursor-pointer">
                    {{ __('Ganti foto') }}
                    <input type="file" name="berkas_foto" accept="image/*" class="hidden" @change="pilihFoto">
                </label>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700/70">{{ __('Edit Data Asatid') }}</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-800 break-words">{{ $a->nama_lengkap }}</h1>
                <p class="mt-1 text-sm text-slate-500"># {{ $a->nomor_pendaftaran }} · {{ __($a->label_status) }}</p>
                <p v-if="namaFoto" class="mt-3 inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    {{ __('Foto baru:') }} <b v-text="namaFoto"></b>
                </p>
                <p class="mt-3 text-xs text-slate-400">
                    @if ($a->user_id)
                    {{ __('Akun login sudah aktif: perubahan nama, email, dan password langsung berlaku untuk login asatid ini.') }}
                    @else
                    {{ __('Akun login belum dibuat (dibuat saat pendaftar diterima).') }}
                    @endif
                </p>
            </div>
        </div>

        {{-- ===== Data diri ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h2 class="text-[15px] font-bold text-slate-800 mb-4">{{ __('Data Diri') }}</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="{{ $label }}">{{ __('Nama Lengkap (Indonesia)') }}</span>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $a->nama_lengkap) }}" required maxlength="150" class="{{ $isian }} {{ $salah('nama_lengkap') }}">
                </label>
                <label class="block">
                    <span class="{{ $label }}">{{ __('Nama Lengkap (Arab)') }}</span>
                    <input type="text" name="nama_lengkap_arab" value="{{ old('nama_lengkap_arab', $a->nama_lengkap_arab) }}" maxlength="150" dir="rtl" class="{{ $isian }} text-lg {{ $salah('nama_lengkap_arab') }}">
                </label>
                <label class="block">
                    <span class="{{ $label }}">{{ __('Tempat Lahir') }} <span class="font-normal text-slate-400">{{ __('(huruf Arab)') }}</span></span>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $a->tempat_lahir) }}" maxlength="100" dir="rtl" class="{{ $isian }} text-lg {{ $salah('tempat_lahir') }}">
                </label>
                <label class="block">
                    <span class="{{ $label }}">{{ __('Tanggal Lahir') }}</span>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($a->tanggal_lahir)->format('Y-m-d')) }}" class="{{ $isian }} {{ $salah('tanggal_lahir') }}">
                </label>
                <label class="block">
                    <span class="{{ $label }}">{{ __('No. WhatsApp') }}</span>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $a->no_hp) }}" required maxlength="20" placeholder="+6281234567890" dir="ltr" class="{{ $isian }} {{ $salah('no_hp') }}">
                </label>
                <div class="hidden md:block"></div>
                <label class="block md:col-span-2">
                    <span class="{{ $label }}">{{ __('Alamat Lengkap') }} <span class="font-normal text-slate-400">{{ __('(huruf Arab)') }}</span></span>
                    <textarea name="alamat" rows="3" maxlength="500" dir="rtl" class="{{ $isian }} text-lg {{ $salah('alamat') }}">{{ old('alamat', $a->alamat) }}</textarea>
                </label>
            </div>
        </section>

        <div class="grid xl:grid-cols-2 gap-6 items-start">
            {{-- ===== Riwayat ===== --}}
            <section class="{{ $kartu }} p-5 sm:p-6">
                <h2 class="text-[15px] font-bold text-slate-800 mb-4">{{ __('Riwayat di Darul Musthofa') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="{{ $label }}">{{ __('Lama Belajar') }}</span>
                        <input type="text" name="lama_belajar" value="{{ old('lama_belajar', $a->lama_belajar) }}" maxlength="100" placeholder="{{ __('mis. 5 tahun') }}" class="{{ $isian }} {{ $salah('lama_belajar') }}">
                    </label>
                    <label class="block">
                        <span class="{{ $label }}">{{ __('Tahun Selesai') }}</span>
                        <input type="text" name="tahun_selesai" value="{{ old('tahun_selesai', $a->tahun_selesai) }}" inputmode="numeric" maxlength="4" placeholder="2020" class="{{ $isian }} {{ $salah('tahun_selesai') }}">
                    </label>
                </div>
            </section>

            {{-- ===== Akun login ===== --}}
            <section class="{{ $kartu }} p-5 sm:p-6">
                <h2 class="text-[15px] font-bold text-slate-800 mb-4">{{ __('Akun Login') }}</h2>
                <div class="space-y-4">
                    <label class="block">
                        <span class="{{ $label }}">{{ __('Email') }}</span>
                        <input type="email" name="email" value="{{ old('email', $a->email) }}" required maxlength="255" class="{{ $isian }} {{ $salah('email') }}">
                    </label>
                    <label class="block">
                        <span class="{{ $label }}">{{ __('Password Baru') }} <span class="font-normal text-slate-400">{{ __('(kosongkan bila tidak diganti)') }}</span></span>
                        <span class="relative block">
                            <input :type="lihatPassword ? 'text' : 'password'" name="password_baru" v-model="passwordBaru" minlength="8" maxlength="100" autocomplete="new-password"
                                placeholder="{{ __('Minimal 8 karakter') }}" class="{{ $isian }} pr-20 {{ $salah('password_baru') }}">
                            <button type="button" @click="lihatPassword = !lihatPassword"
                                class="absolute right-2 top-1/2 -translate-y-1/2 mt-0.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100"
                                v-text="lihatPassword ? @js(__('Sembunyi')) : @js(__('Lihat'))"></button>
                        </span>
                        <span v-if="passwordBaru && passwordBaru.length < 8" class="mt-1 block text-xs text-amber-600">{{ __('Kurang') }} <span v-text="8 - passwordBaru.length"></span> {{ __('karakter lagi.') }}</span>
                    </label>
                </div>
            </section>
        </div>

        {{-- ===== Catatan admin ===== --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h2 class="text-[15px] font-bold text-slate-800">{{ __('Catatan Admin') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('Tampil di notifikasi dasbor asatid.') }}</p>
            <textarea name="catatan_admin" rows="3" maxlength="1000" dir="auto" placeholder="{{ __('Opsional') }}" class="{{ $isian }}">{{ old('catatan_admin', $a->catatan_admin) }}</textarea>
        </section>

        {{-- ===== Tombol ===== --}}
        <div class="sticky bottom-4 z-10 flex flex-wrap items-center gap-3 p-3 rounded-2xl bg-white/90 backdrop-blur ring-1 ring-slate-200 shadow-lg">
            <span v-if="diubah" class="text-xs px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200 font-semibold">{{ __('Ada perubahan belum disimpan') }}</span>
            <span class="flex-1"></span>
            <a href="{{ route('admin.asatid.show', $a) }}" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                {{ __('Simpan Perubahan') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            data() {
                return {
                    pratinjauFoto: @json($a->foto_url),
                    namaFoto: '',
                    passwordBaru: '',
                    lihatPassword: false,
                    diubah: @json($errors->any()), // gagal simpan -> isian lama masih belum tersimpan
                };
            },
            methods: {
                ...metodeHalaman, // kembali ke halaman sebelumnya
                // Pas foto baru: tampilkan langsung sebelum disimpan
                pilihFoto(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.namaFoto = file.name;
                    this.pratinjauFoto = URL.createObjectURL(file);
                },
            },
            mounted() {
                // Peringatan bila meninggalkan halaman sebelum menyimpan
                window.addEventListener('beforeunload', (e) => { if (this.diubah) e.preventDefault(); });
            },
        }).mount('#halaman-edit-asatid');
    });
</script>
@endpush
