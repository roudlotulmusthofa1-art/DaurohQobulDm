{{--
    Formulir "Ganti Password" untuk asatid & peserta (dipakai asatid/ganti-password dan siswa/ganti-password).
    Variabel: $aksi = alamat simpan (route ...ganti-password.simpan).
    Penyimpanan & aturannya: App\Support\GantiPasswordSendiri.
--}}
@php
$inputGanti = 'mt-1.5 w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-4 py-3 text-[15px] text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
@endphp
<div class="max-w-lg mx-auto space-y-5">
    @if (session('password_diganti'))
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-900 text-sm">
        <span class="w-7 h-7 shrink-0 rounded-full bg-emerald-500 text-white flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        <span class="pt-0.5">{{ __('Password berhasil diganti. Gunakan password baru saat masuk berikutnya.') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-200 text-rose-800 text-sm">
        <span class="w-7 h-7 shrink-0 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold">!</span>
        <span class="pt-0.5">{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ $aksi }}"
        class="rounded-3xl bg-white ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)] p-5 sm:p-7 space-y-4">
        @csrf
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-lg font-extrabold text-slate-800 leading-tight">{{ __('Ganti Password') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Password baru dipakai untuk masuk berikutnya.') }}</p>
            </div>
        </div>

        <label class="block">
            <span class="text-[13px] font-semibold text-slate-700">{{ __('Password lama') }}</span>
            <input type="password" name="password_lama" required autocomplete="current-password" class="{{ $inputGanti }}">
        </label>
        <label class="block">
            <span class="text-[13px] font-semibold text-slate-700">{{ __('Password baru') }}</span>
            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="{{ $inputGanti }}">
            <span class="mt-1 block text-[11px] text-slate-400">{{ __('Minimal 8 karakter.') }}</span>
        </label>
        <label class="block">
            <span class="text-[13px] font-semibold text-slate-700">{{ __('Ulangi password baru') }}</span>
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="{{ $inputGanti }}">
        </label>

        <button type="submit"
            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            {{ __('Simpan Password Baru') }}
        </button>
    </form>

    <p class="text-center text-xs text-slate-400">{{ __('Lupa password lama? Minta admin untuk mereset password Anda.') }}</p>
</div>
