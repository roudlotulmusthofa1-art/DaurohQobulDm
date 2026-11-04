{{--
    Form "Reset Password": admin mengetik password baru (+ ulangi), lalu Simpan.
    Dipakai di kotak "Akun login" detail asatid/admin (partials/reset-password-akun) dan detail santri.
    Variabel: $aksi = alamat form, $konfirmasi = pertanyaan sebelum disimpan.
    Pemeriksaan isian: App\Support\ResetPasswordAkun::validasi() (error bag "resetPassword").
--}}
@php $kolom = 'w-full sm:w-44 rounded-xl bg-white/10 ring-1 ring-white/15 border-0 px-3 py-2 text-sm text-white placeholder-white/40 focus:ring-2 focus:ring-sky-300 outline-none'; @endphp
<form method="POST" action="{{ $aksi }}" @submit="konfirmasi" data-konfirmasi="{{ $konfirmasi }}"
    class="mt-3 flex flex-col sm:flex-row sm:items-end gap-2">
    @csrf
    <label class="block">
        <span class="text-[10px] uppercase tracking-wider text-white/40">{{ __('Password baru') }}</span>
        <input type="password" name="password_baru" required minlength="8" autocomplete="new-password" class="{{ $kolom }}"
            placeholder="{{ __('Minimal 8 karakter') }}">
    </label>
    <label class="block">
        <span class="text-[10px] uppercase tracking-wider text-white/40">{{ __('Ulangi password baru') }}</span>
        <input type="password" name="password_baru_confirmation" required minlength="8" autocomplete="new-password" class="{{ $kolom }}">
    </label>
    <button type="submit"
        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-sky-400 text-[#06283a] text-sm font-bold hover:bg-sky-300 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
        {{ __('Simpan Password Baru') }}
    </button>
</form>
@if ($errors->resetPassword->any())
<p class="mt-2 text-xs font-semibold text-rose-200">{{ $errors->resetPassword->first() }}</p>
@endif
