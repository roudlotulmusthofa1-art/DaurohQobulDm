{{--
Pengaturan > Profil: Akun admin yang sedang login: nama, email, ganti password.
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
@php $user = auth()->user(); @endphp
<h2 class="text-lg font-bold text-slate-800">{{ __('Profil Akun') }}</h2>
<p class="text-sm text-slate-500">{{ __('Data akun admin yang sedang login.') }}</p>

{{-- ===== Foto profil: pilih -> pratinjau -> Simpan (Vue: window.tabPengaturan di bawah) ===== --}}
<div class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 flex flex-col sm:flex-row sm:items-center gap-5">
    <div class="relative shrink-0 self-center sm:self-auto">
        <img :src="pratinjauFoto || fotoSekarang" alt="{{ __('Foto profil') }}" data-perbesar="{{ __('Foto profil') }} {{ $user->name }}"
            class="w-24 h-24 rounded-3xl object-cover ring-4 ring-emerald-50 bg-slate-100">
        <label title="{{ __('Pilih foto baru') }}"
            class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-[#0b332c] text-white flex items-center justify-center cursor-pointer shadow-lg hover:bg-[#135347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
            <input ref="inputFoto" form="form-foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="hidden" @change="pilihFoto">
        </label>
    </div>

    <div class="min-w-0 flex-1 text-center sm:text-left">
        <p class="font-bold text-slate-800 truncate">{{ $user->name }}</p>
        <x-nama-arab :teks="$user->nama_arab" />
        <p class="text-sm text-slate-500 truncate">{{ $user->email }}</p>
        <span class="mt-1 inline-block rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ ucfirst($user->role) }}</span>

        {{-- Foto baru dipilih: simpan / batal --}}
        <div v-if="pratinjauFoto" class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-2">
            <span class="text-xs text-slate-500 truncate max-w-[200px]" v-text="namaFotoBaru"></span>
            <button type="submit" form="form-foto" :disabled="fotoTerlaluBesar"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 disabled:opacity-40">{{ __('Simpan Foto') }}</button>
            <button type="button" @click="batalFoto" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">{{ __('Batal') }}</button>
            <p v-if="fotoTerlaluBesar" class="w-full text-xs text-rose-600">{{ __('Ukuran foto lebih dari 3 MB. Pilih foto yang lebih kecil.') }}</p>
        </div>
        {{-- Belum memilih: ganti / hapus --}}
        <div v-else class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-2">
            <button type="button" @click="$refs.inputFoto.click()"
                class="px-3.5 py-2 rounded-xl bg-[#0b332c]/[0.06] text-[#0b332c] text-xs font-bold hover:bg-[#0b332c]/[0.12]">{{ $user->foto ? __('Ganti foto') : __('Unggah foto') }}</button>
            @if ($user->foto)
            <form method="POST" action="{{ route('admin.pengaturan.foto.hapus') }}" @submit="konfirmasi" data-konfirmasi="{{ __('Hapus foto profil? Avatar kembali ke inisial nama.') }}">
                @csrf @method('DELETE')
                <button class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Hapus foto') }}</button>
            </form>
            @endif
            <span class="text-[11px] text-slate-400">{{ __('JPG, PNG, atau WEBP · maks. 3 MB') }}</span>
        </div>
    </div>
</div>
<form id="form-foto" method="POST" action="{{ route('admin.pengaturan.foto') }}" enctype="multipart/form-data" class="hidden">@csrf</form>

<form method="POST" action="{{ route('admin.pengaturan.profil') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 space-y-4" @input="formDiubah = true" @change="formDiubah = true">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-4">
        <label class="block"><span class="{{ $label }}">{{ __('Nama') }} <span class="text-rose-500">*</span></span>
            <input name="name" required maxlength="150" value="{{ old('name', $user->name) }}" class="{{ $input }}"></label>
        <label class="block"><span class="{{ $label }}">{{ __('Email login') }} <span class="text-rose-500">*</span></span>
            <input type="email" name="email" required maxlength="150" value="{{ old('email', $user->email) }}" class="{{ $input }}"></label>
    </div>

    <div class="border-t border-slate-100 pt-4">
        <p class="text-sm font-bold text-slate-800">{{ __('Ganti Password') }}</p>
        <p class="text-xs text-slate-400">{{ __('Kosongkan bila tidak ingin mengganti password.') }}</p>
        <div class="mt-3 grid sm:grid-cols-3 gap-4">
            <label class="block"><span class="{{ $label }}">{{ __('Password lama') }}</span>
                <input type="password" name="password_lama" autocomplete="current-password" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">{{ __('Password baru') }}</span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">{{ __('Ulangi password baru') }}</span>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></label>
        </div>
    </div>

    <div class="flex justify-end border-t border-slate-100 pt-4">
        <button class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" :disabled="!formDiubah">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            {{ __('Simpan Perubahan') }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Bagian Vue tab Profil (dipasang lewat mixins di admin/pengaturan/index.blade.php): pratinjau foto profil
    window.tabPengaturan = {
        data() {
            return {
                fotoSekarang: @json($user->avatarUrl()),
                pratinjauFoto: null, // gambar yang baru dipilih (belum disimpan)
                namaFotoBaru: '',
                fotoTerlaluBesar: false,
            };
        },
        methods: {
            pilihFoto(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.pratinjauFoto = URL.createObjectURL(file);
                this.namaFotoBaru = file.name;
                this.fotoTerlaluBesar = file.size > 3 * 1024 * 1024;
            },
            batalFoto() {
                this.pratinjauFoto = null;
                this.namaFotoBaru = '';
                this.fotoTerlaluBesar = false;
                this.$refs.inputFoto.value = '';
            },
        },
    };
</script>
@endpush
