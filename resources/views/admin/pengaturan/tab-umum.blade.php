{{--
Pengaturan > Umum: Profil pesantren: logo, nama, alamat, telepon, email (Pengaturan > Umum).
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
<h2 class="text-lg font-bold text-slate-800">Profil Pesantren</h2>
<p class="text-sm text-slate-500">Informasi identitas pesantren yang akan digunakan di seluruh aplikasi.</p>

{{-- Logo --}}
<div class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 flex flex-col sm:flex-row gap-5">
    <div class="w-36 h-36 shrink-0 rounded-2xl ring-1 ring-slate-200 p-3 flex items-center justify-center"
        style="background-image: repeating-conic-gradient(#f1f5f9 0% 25%, #fff 0% 50%); background-size: 16px 16px;">
        <img data-perbesar :src="pratinjauLogo || logoSekarang" alt="Logo pesantren" class="max-w-full max-h-full object-contain">
    </div>
    <div class="min-w-0">
        <p class="text-sm font-bold text-slate-800">Logo Pesantren</p>
        <p class="mt-0.5 text-xs text-slate-500">Digunakan di seluruh aplikasi sebagai identitas visual pesantren. Bila tidak diunggah, logo bawaan aplikasi akan dipakai.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ $profil['logo_url'] }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Lihat ukuran besar
            </a>
            <label for="input-logo" class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Ganti
            </label>
            @if ($profil['logo'])
            <button type="submit" form="form-hapus-logo" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                Hapus
            </button>
            @endif
        </div>
        <p class="mt-2 text-[11px] text-slate-400" v-if="namaLogoBaru" v-text="'Logo baru: ' + namaLogoBaru + '. Klik &quot;Simpan Perubahan&quot; untuk menyimpan.'"></p>
        <p class="mt-2 text-[11px] text-slate-400" v-else>PNG, JPG, WEBP atau SVG, maks. 2 MB. Logo baru tersimpan setelah klik "Simpan Perubahan".</p>
    </div>
</div>
<form method="POST" action="{{ route('admin.pengaturan.logo.hapus') }}" id="form-hapus-logo" @submit="konfirmasi" data-konfirmasi="Hapus logo pesantren?">
    @csrf @method('DELETE')
</form>

{{-- Data pesantren --}}
<form method="POST" action="{{ route('admin.pengaturan.umum') }}" enctype="multipart/form-data" class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 space-y-4" @input="formDiubah = true" @change="formDiubah = true">
    @csrf @method('PUT')
    <input type="file" name="logo" id="input-logo" @change="pilihLogo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden">
    <label class="block"><span class="{{ $label }}">Nama Pesantren <span class="text-rose-500">*</span></span>
        <input name="nama" required maxlength="150" value="{{ old('nama', $profil['nama']) }}" class="{{ $input }}"></label>
    <label class="block"><span class="{{ $label }}">Alamat</span>
        <textarea name="alamat" rows="3" maxlength="500" class="{{ $input }}">{{ old('alamat', $profil['alamat']) }}</textarea>
        <span class="mt-1 block text-xs text-slate-400">Ditampilkan pada berbagai bagian aplikasi, termasuk header dokumen.</span></label>
    <div class="grid sm:grid-cols-2 gap-4">
        <label class="block"><span class="{{ $label }}">Nomor Telepon</span>
            <input name="telepon" maxlength="30" value="{{ old('telepon', $profil['telepon']) }}" placeholder="+62 ..." class="{{ $input }}"></label>
        <label class="block"><span class="{{ $label }}">Email</span>
            <input type="email" name="email" maxlength="150" value="{{ old('email', $profil['email']) }}" placeholder="info@..." class="{{ $input }}"></label>
    </div>
    <div class="flex justify-end border-t border-slate-100 pt-4">
        <button class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" :disabled="!formDiubah">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Simpan Perubahan
        </button>
    </div>
</form>


@push('scripts')
<script>
    // Bagian Vue tab Umum: pratinjau logo baru sebelum disimpan
    window.tabPengaturan = {
        data() {
            return {
                logoSekarang: @json($profil['logo_url']),
                pratinjauLogo: null, // alamat sementara gambar yang baru dipilih (belum disimpan)
                namaLogoBaru: '',
            };
        },
        methods: {
            pilihLogo(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.pratinjauLogo = URL.createObjectURL(file);
                this.namaLogoBaru = file.name;
            },
        },
    };
</script>
@endpush
