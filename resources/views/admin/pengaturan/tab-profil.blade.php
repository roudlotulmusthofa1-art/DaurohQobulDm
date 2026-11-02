{{--
Pengaturan > Profil: Akun admin yang sedang login: nama, email, ganti password.
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
@php $user = auth()->user(); @endphp
<h2 class="text-lg font-bold text-slate-800">Profil Akun</h2>
<p class="text-sm text-slate-500">Data akun admin yang sedang login.</p>

<div class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 flex items-center gap-4">
    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0b332c&color=fff&size=128" alt="Avatar" class="w-16 h-16 rounded-2xl">
    <div class="min-w-0">
        <p class="font-bold text-slate-800 truncate">{{ $user->name }}</p>
        <p class="text-sm text-slate-500 truncate">{{ $user->email }}</p>
        <span class="mt-1 inline-block rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ ucfirst($user->role) }}</span>
    </div>
</div>

<form method="POST" action="{{ route('admin.pengaturan.profil') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 space-y-4" @input="formDiubah = true" @change="formDiubah = true">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-4">
        <label class="block"><span class="{{ $label }}">Nama <span class="text-rose-500">*</span></span>
            <input name="name" required maxlength="150" value="{{ old('name', $user->name) }}" class="{{ $input }}"></label>
        <label class="block"><span class="{{ $label }}">Email login <span class="text-rose-500">*</span></span>
            <input type="email" name="email" required maxlength="150" value="{{ old('email', $user->email) }}" class="{{ $input }}"></label>
    </div>

    <div class="border-t border-slate-100 pt-4">
        <p class="text-sm font-bold text-slate-800">Ganti Password</p>
        <p class="text-xs text-slate-400">Kosongkan bila tidak ingin mengganti password.</p>
        <div class="mt-3 grid sm:grid-cols-3 gap-4">
            <label class="block"><span class="{{ $label }}">Password lama</span>
                <input type="password" name="password_lama" autocomplete="current-password" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">Password baru</span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">Ulangi password baru</span>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></label>
        </div>
    </div>

    <div class="flex justify-end border-t border-slate-100 pt-4">
        <button class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" :disabled="!formDiubah">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Simpan Perubahan
        </button>
    </div>
</form>
