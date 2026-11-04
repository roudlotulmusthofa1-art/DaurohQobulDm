{{--
    Kotak "Akun login" + tombol Reset Password di halaman detail Data Asatid & Data Admin
    (diletakkan di dalam kartu profil yang berwarna gelap).
    Variabel: $a = PendaftaranAsatid / PendaftaranAdmin, $routeReset = alamat form reset.
    Aturan (siapa boleh mereset) & isi pesan WhatsApp: App\Support\ResetPasswordAkun.
--}}
@if ($a->user_id)
@php $alasanTidakBoleh = \App\Support\ResetPasswordAkun::alasanTidakBoleh($a); @endphp
<div class="mt-4 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 p-3.5">
    <div class="flex flex-wrap items-center gap-3">
        <span class="w-9 h-9 shrink-0 rounded-xl bg-sky-300/15 text-sky-200 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
        </span>
        <div class="min-w-0 flex-1" v-pre>
            <p class="text-[10px] uppercase tracking-wider text-white/40">{{ __('Akun login') }}</p>
            <p class="text-sm font-medium truncate">{{ $a->email }}</p>
            <p class="text-[11px] text-white/45">
                {{ $alasanTidakBoleh ?? __('Password tersimpan terenkripsi (tidak bisa dilihat). Bila lupa: ketik password baru di bawah.') }}
            </p>
        </div>
    </div>

    {{-- Reset Password: admin mengetik password baru sendiri --}}
    @unless ($alasanTidakBoleh)
    @include('partials.form-password-baru', [
        'aksi' => $routeReset,
        'konfirmasi' => __('Simpan password BARU untuk :nama? Password lama tidak bisa dipakai lagi. Password baru dikirim ke WhatsApp :nama.', ['nama' => $a->nama_lengkap]),
    ])
    @endunless

    {{-- Password baru: tampil SEKALI setelah reset (tidak disimpan dalam bentuk yang bisa dibaca) --}}
    @if ($baruReset = session('password_baru'))
    <div class="mt-3 rounded-2xl bg-amber-300/10 ring-1 ring-amber-300/30 p-3.5" v-pre>
        <p class="text-xs font-semibold text-amber-100">{{ __('Password baru sudah disimpan (hanya tampil sekali, segera kirim):') }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <code class="rounded-xl bg-black/30 px-3 py-1.5 font-mono text-lg font-bold tracking-wider text-white select-all" dir="ltr">{{ $baruReset['password'] }}</code>
            <button type="button" data-teks="{{ $baruReset['password'] }}"
                onclick="navigator.clipboard.writeText(this.dataset.teks).then(() => { this.textContent = @js(__('Tersalin ✓')); })"
                class="px-3 py-1.5 rounded-xl bg-white/10 ring-1 ring-white/20 text-xs font-semibold hover:bg-white/20">{{ __('Salin') }}</button>
        </div>
        @if ($a->no_hp)
        <a href="https://wa.me/{{ \App\Services\WhatsAppService::nomorWa($a->no_hp) }}?text={{ rawurlencode($baruReset['pesan']) }}" target="_blank" rel="noopener"
            title="{{ $a->nama_lengkap }} · {{ $a->no_hp }}"
            class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[#25D366] text-[#073b1d] text-sm font-bold hover:bg-[#3ae07a] transition-colors">
            {{ __('Kirim lewat WhatsApp saya') }}
        </a>
        @endif
    </div>
    @endif
</div>
@endif
