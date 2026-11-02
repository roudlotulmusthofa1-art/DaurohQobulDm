{{--
    Admin > Sistem > Pengaturan
    Menu kiri: Umum, Profil, Notifikasi, Database (?tab=...). Isi tab ada di bawah, satu @if per tab.
--}}
@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1.5 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-[13px] font-semibold text-slate-700';
$tombol = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors';
$ukuran = fn ($b) => $b >= 1073741824 ? number_format($b / 1073741824, 1, ',', '.') . ' GB'
    : ($b >= 1048576 ? number_format($b / 1048576, 1, ',', '.') . ' MB' : number_format($b / 1024, 0, ',', '.') . ' KB');
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    {{-- ===== Judul ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-teal-600 via-[#0f6b5c] to-[#0b332c] text-white p-6 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="relative">
            <h1 class="text-2xl font-extrabold tracking-tight">Pengaturan</h1>
            <p class="mt-1 text-sm text-white/80">Konfigurasi aplikasi dan preferensi</p>
        </div>
    </section>

    @if (session('success'))
    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
        <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
        <p class="font-semibold">Periksa kembali isian Anda:</p>
        <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="grid lg:grid-cols-[220px_1fr] gap-6 items-start">
        {{-- ===== Menu kiri ===== --}}
        <nav class="{{ $kartu }} p-3 flex lg:flex-col gap-1 overflow-x-auto lg:sticky lg:top-4">
            @foreach ($tabList as $kode => $t)
            <a href="{{ route('admin.pengaturan.index', ['tab' => $kode]) }}"
                class="shrink-0 flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-colors {{ $tab === $kode ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'text-slate-600 hover:bg-slate-50' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['ikon'] }}"/></svg>
                {{ $t['label'] }}
            </a>
            @endforeach
        </nav>

        <div class="{{ $kartu }} p-5 sm:p-7">
        {{-- ================= UMUM ================= --}}
        @if ($tab === 'umum')
            <h2 class="text-lg font-bold text-slate-800">Profil Pesantren</h2>
            <p class="text-sm text-slate-500">Informasi identitas pesantren yang akan digunakan di seluruh aplikasi.</p>

            {{-- Logo --}}
            <div class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 flex flex-col sm:flex-row gap-5">
                <div class="w-36 h-36 shrink-0 rounded-2xl ring-1 ring-slate-200 p-3 flex items-center justify-center"
                    style="background-image: repeating-conic-gradient(#f1f5f9 0% 25%, #fff 0% 50%); background-size: 16px 16px;">
                    <img src="{{ $profil['logo_url'] }}" alt="Logo pesantren" class="max-w-full max-h-full object-contain" id="pratinjau-logo">
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
                    <p class="mt-2 text-[11px] text-slate-400" id="nama-logo-baru">PNG, JPG, WEBP atau SVG, maks. 2 MB. Logo baru tersimpan setelah klik "Simpan Perubahan".</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.pengaturan.logo.hapus') }}" id="form-hapus-logo" onsubmit="return confirm('Hapus logo pesantren?')">
                @csrf @method('DELETE')
            </form>

            {{-- Data pesantren --}}
            <form method="POST" action="{{ route('admin.pengaturan.umum') }}" enctype="multipart/form-data" class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 space-y-4" data-form-simpan>
                @csrf @method('PUT')
                <input type="file" name="logo" id="input-logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden">
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
                    <button class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" data-tombol-simpan disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        {{-- ================= PROFIL ================= --}}
        @elseif ($tab === 'profil')
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

            <form method="POST" action="{{ route('admin.pengaturan.profil') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5 space-y-4" data-form-simpan>
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
                    <button class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" data-tombol-simpan disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        {{-- ================= NOTIFIKASI ================= --}}
        @elseif ($tab === 'notifikasi')
            <h2 class="text-lg font-bold text-slate-800">Notifikasi</h2>
            <p class="text-sm text-slate-500">Pilih jenis notifikasi yang muncul di ikon lonceng (kanan atas).</p>

            <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 overflow-hidden">
                @csrf @method('PUT')
                <div class="divide-y divide-slate-100">
                    @foreach ($notifikasi as $n)
                    <label class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-[#f5f9f7] transition-colors">
                        <span class="w-10 h-10 shrink-0 rounded-xl ring-1 flex items-center justify-center {{ $n['warna_kelas'] }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $n['ikon_path'] }}"/></svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-semibold text-slate-800">{{ $n['judul'] }}</span>
                            <span class="block text-xs text-slate-500 mt-0.5">{{ $n['keterangan'] }}</span>
                        </span>
                        {{-- Saklar hidup/mati --}}
                        <span class="relative shrink-0">
                            <input type="checkbox" name="notif[{{ $n['kunci'] }}]" value="1" @checked($n['aktif']) class="peer sr-only">
                            <span class="block w-11 h-6 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20"></span>
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-[#f5f9f7] border-t border-slate-100">
                    <p class="text-xs text-slate-500">Notifikasi hilang sendiri begitu masalahnya sudah dibereskan.</p>
                    <button type="submit" class="{{ $tombol }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan
                    </button>
                </div>
            </form>

        {{-- ================= PESAN (template WhatsApp) ================= --}}
        @elseif ($tab === 'pesan')
            <h2 class="text-lg font-bold text-slate-800">Pesan WhatsApp</h2>
            <p class="text-sm text-slate-500">Atur isi pesan WhatsApp yang dikirim otomatis. Berlaku untuk semua sesi. Klik tombol isian untuk menyisipkan data (nama, nomor, dll.) di posisi kursor.</p>
            <div class="mt-3 rounded-2xl bg-sky-50 ring-1 ring-sky-100 p-3 text-xs text-sky-900">
                Format WhatsApp: <b>*tebal*</b>, <i>_miring_</i>, <s>~coret~</s>. Isian <span class="font-mono">{catatan}</span> dan <span class="font-mono">{penempatan}</span> sudah membawa judulnya sendiri dan otomatis hilang bila datanya kosong.
            </div>

            <form method="POST" action="{{ route('admin.pengaturan.pesan') }}" class="mt-5 space-y-6" data-form-pesan>
                @csrf @method('PUT')
                @foreach ($pesanList as $grup => $daftar)
                <section>
                    <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wider text-emerald-700">{{ \App\Support\PesanWa::GRUP[$grup] }}</h3>
                    <div class="space-y-4">
                        @foreach ($daftar as $p)
                        <article id="{{ $p['kunci'] }}" class="rounded-2xl ring-1 ring-slate-200 overflow-hidden" data-pesan="{{ $p['kunci'] }}">
                            <div class="flex flex-wrap items-center gap-3 px-4 py-3 bg-[#f8fbfa] border-b border-slate-100">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800">{{ $p['judul'] }}
                                        @if ($p['diubah'])<span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">DIUBAH</span>@endif
                                    </p>
                                    <p class="text-[11px] text-slate-500">Kepada: {{ $p['penerima'] }} · {{ $p['kapan'] }}</p>
                                </div>
                                {{-- Saklar kirim / tidak --}}
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                                    <span class="relative">
                                        <input type="checkbox" name="aktif[{{ $p['kunci'] }}]" value="1" @checked($p['aktif']) class="peer sr-only">
                                        <span class="block w-10 h-[22px] rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></span>
                                        <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] rounded-full bg-white shadow transition-transform peer-checked:translate-x-[18px]"></span>
                                    </span>
                                    Kirim
                                </label>
                            </div>
                            <div class="grid lg:grid-cols-2">
                                <div class="p-4 space-y-2 lg:border-r border-slate-100">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($p['isian'] as $isian)
                                        <button type="button" data-sisip="{{ '{' . $isian . '}' }}" title="{{ \App\Support\PesanWa::ISIAN[$isian] }}"
                                            class="rounded-lg bg-emerald-50 px-2 py-1 font-mono text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100">{{ '{' . $isian . '}' }}</button>
                                        @endforeach
                                    </div>
                                    <textarea name="teks[{{ $p['kunci'] }}]" rows="9" maxlength="3000" data-bawaan="{{ $p['bawaan'] }}"
                                        class="w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-[13px] leading-relaxed text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">{{ $p['teks'] }}</textarea>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-[11px] text-slate-400" data-hitung></span>
                                        <button type="submit" name="kembalikan" value="{{ $p['kunci'] }}" formnovalidate @disabled(! $p['diubah'])
                                            onclick="return confirm('Kembalikan pesan ini ke teks bawaan?')"
                                            class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 disabled:opacity-40 disabled:cursor-not-allowed">Kembalikan ke bawaan</button>
                                    </div>
                                </div>
                                {{-- Pratinjau ala WhatsApp dengan data contoh --}}
                                <div class="p-4 bg-[#efeae2]">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Pratinjau (data contoh)</p>
                                    <div class="mt-2 ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#d9fdd3] px-3 py-2 text-[13px] leading-relaxed text-slate-800 shadow-sm whitespace-pre-wrap break-words" data-pratinjau></div>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </section>
                @endforeach

                <div class="sticky bottom-3 z-10 flex justify-end">
                    <button class="{{ $tombol }} shadow-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Semua Pesan
                    </button>
                </div>
            </form>

        {{-- ================= DATABASE ================= --}}
        @elseif ($tab === 'database')
            @php
            $kotak = 'rounded-2xl ring-1 ring-slate-200 p-5';
            $kecil = 'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 transition-colors';
            @endphp
            <h2 class="text-lg font-bold text-slate-800">Database</h2>
            <p class="text-sm text-slate-500">Backup, restore, dan hapus data untuk sesi yang sedang aktif.</p>

            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 px-4 py-3 text-sm">
                <span class="w-2.5 h-2.5 shrink-0 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                <p class="min-w-0 text-emerald-900">Sesi aktif: <b>{{ $sesiAktif?->nama ?? '-' }}</b> <span class="text-emerald-700/80">· database <span class="font-mono">{{ $db['nama'] }}</span></span></p>
                <a href="{{ route('admin.pengaturan.index', ['tab' => 'sesi']) }}" class="ml-auto shrink-0 text-xs font-semibold text-emerald-700 hover:underline">Ganti sesi</a>
            </div>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ([
                    ['Database', $db['nama']],
                    ['Jenis', strtoupper($db['driver']) . ' ' . \Illuminate\Support\Str::before((string) $db['versi'], '-')],
                    ['Jumlah tabel', number_format($db['tabel']->count(), 0, ',', '.')],
                    ['Ukuran', $ukuran($db['ukuran'])],
                ] as [$judulInfo, $nilaiInfo])
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 p-4 min-w-0">
                    <p class="text-[11px] font-semibold text-slate-400">{{ $judulInfo }}</p>
                    <p class="mt-1 font-bold text-slate-800 truncate" title="{{ $nilaiInfo }}">{{ $nilaiInfo }}</p>
                </div>
                @endforeach
            </div>

            {{-- ----- Memori server ----- --}}
            @php
            $meter = function (string $judul, ?array $m, string $warna) use ($ukuran) {
                if (! $m || ! $m['total']) {
                    return ['judul' => $judul, 'ada' => false];
                }
                $persen = round($m['terpakai'] / $m['total'] * 100);
                return ['judul' => $judul, 'ada' => true, 'persen' => $persen, 'warna' => $persen >= 90 ? 'bg-rose-500' : ($persen >= 75 ? 'bg-amber-500' : $warna),
                    'terpakai' => $ukuran($m['terpakai']), 'sisa' => $ukuran($m['sisa']), 'total' => $ukuran($m['total'])];
            };
            $meterList = [$meter('Penyimpanan (disk) server', $server['disk'], 'bg-emerald-500'), $meter('Memori (RAM) server', $server['ram'], 'bg-sky-500')];
            @endphp
            <section class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5">
                <p class="font-bold text-slate-800">Memori Server</p>
                <div class="mt-3 grid sm:grid-cols-2 gap-4">
                    @foreach ($meterList as $m)
                    <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold text-slate-600">{{ $m['judul'] }}</p>
                            @if ($m['ada'])<p class="text-xs font-bold tabular-nums text-slate-700">{{ $m['persen'] }}%</p>@endif
                        </div>
                        @if ($m['ada'])
                        <div class="mt-2 h-2.5 rounded-full bg-slate-200 overflow-hidden"><div class="h-full rounded-full {{ $m['warna'] }}" style="width: {{ $m['persen'] }}%"></div></div>
                        <dl class="mt-3 grid grid-cols-3 gap-2 text-[11px]">
                            <div><dt class="text-slate-400">Terpakai</dt><dd class="font-bold text-slate-700 tabular-nums">{{ $m['terpakai'] }}</dd></div>
                            <div><dt class="text-slate-400">Sisa</dt><dd class="font-bold text-emerald-700 tabular-nums">{{ $m['sisa'] }}</dd></div>
                            <div><dt class="text-slate-400">Total</dt><dd class="font-bold text-slate-700 tabular-nums">{{ $m['total'] }}</dd></div>
                        </dl>
                        @else
                        <p class="mt-2 text-xs text-slate-400">Tidak tersedia di komputer ini (terbaca di server Linux).</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs font-semibold text-slate-600">Dipakai aplikasi ini</p>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    @foreach (['Database (semua sesi)' => $server['aplikasi']['database'], 'File upload' => $server['aplikasi']['file'], 'Backup' => $server['aplikasi']['backup'], 'Log' => $server['aplikasi']['log']] as $judulPakai => $nilaiPakai)
                    <div class="rounded-xl ring-1 ring-slate-100 px-3 py-2"><p class="text-slate-400">{{ $judulPakai }}</p><p class="font-bold text-slate-700 tabular-nums">{{ $ukuran($nilaiPakai) }}</p></div>
                    @endforeach
                </div>
            </section>

            @if (! $db['bisa_backup'])
            <p class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Backup, restore, dan hapus data hanya tersedia untuk database MySQL/MariaDB.</p>
            @else
            {{-- ----- Backup ----- --}}
            <section class="mt-5 {{ $kotak }}">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <span class="w-11 h-11 shrink-0 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800">Backup Data</p>
                        <p class="text-xs text-slate-500">Salinan seluruh data sesi ini ({{ number_format($db['baris'], 0, ',', '.') }} baris) dalam file .sql. File berisi data pribadi, simpan di tempat aman. Berkas upload (foto, dokumen) tidak ikut.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <form method="POST" action="{{ route('admin.pengaturan.backup.simpan') }}">@csrf
                            <button class="{{ $kecil }} text-slate-700 ring-slate-200 hover:bg-slate-50 py-2.5">Simpan di server</button>
                        </form>
                        <a href="{{ route('admin.pengaturan.backup') }}" class="{{ $tombol }} justify-center">Download Backup</a>
                    </div>
                </div>

                <div class="mt-4 rounded-xl ring-1 ring-slate-100 overflow-hidden">
                    <p class="px-4 py-2.5 bg-[#f8fbfa] text-xs font-bold text-slate-600">Riwayat backup di server <span class="font-normal text-slate-400">(dibuat otomatis sebelum restore & hapus data)</span></p>
                    @forelse ($cadangan as $c)
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 px-4 py-2.5 border-t border-slate-100 text-sm">
                        <span class="min-w-0 flex-1 font-mono text-[12px] text-slate-700 truncate" title="{{ $c['nama'] }}">{{ $c['nama'] }}</span>
                        <span class="text-xs text-slate-400 tabular-nums">{{ $ukuran($c['ukuran']) }} · {{ $c['waktu']->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                        <a href="{{ route('admin.pengaturan.backup.unduh', $c['nama']) }}" class="{{ $kecil }} text-sky-700 ring-sky-200 hover:bg-sky-50">Unduh</a>
                        <button type="button" data-pulihkan="{{ $c['nama'] }}" class="{{ $kecil }} text-amber-700 ring-amber-200 hover:bg-amber-50">Restore</button>
                        <form method="POST" action="{{ route('admin.pengaturan.backup.hapus', $c['nama']) }}" onsubmit="return confirm('Hapus file backup ini?')">@csrf @method('DELETE')
                            <button class="{{ $kecil }} text-rose-600 ring-rose-200 hover:bg-rose-50">Hapus</button>
                        </form>
                    </div>
                    @empty
                    <p class="px-4 py-4 border-t border-slate-100 text-xs text-slate-400">Belum ada backup tersimpan di server.</p>
                    @endforelse
                </div>
            </section>

            {{-- ----- Restore ----- --}}
            <form method="POST" action="{{ route('admin.pengaturan.restore') }}" enctype="multipart/form-data" class="mt-5 {{ $kotak }} ring-amber-200 bg-amber-50/40" id="form-restore">
                @csrf
                <div class="flex items-start gap-4">
                    <span class="w-11 h-11 shrink-0 rounded-2xl bg-amber-400 text-[#0b332c] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800">Restore Data</p>
                        <p class="text-xs text-slate-600">Mengganti SELURUH data sesi <b>{{ $sesiAktif?->nama }}</b> dengan isi file backup. Data sekarang otomatis di-backup dulu. Akun admin & asatid disamakan kembali setelahnya.</p>
                    </div>
                </div>
                <div class="mt-4 grid sm:grid-cols-2 gap-4">
                    <div>
                        <span class="{{ $label }}">Sumber backup</span>
                        <div class="mt-1.5 flex gap-2 text-sm">
                            <label class="flex items-center gap-2"><input type="radio" name="sumber" value="upload" @checked(old('sumber', 'upload') === 'upload') class="text-amber-500"> Upload file .sql</label>
                            <label class="flex items-center gap-2"><input type="radio" name="sumber" value="server" @checked(old('sumber') === 'server') class="text-amber-500" @disabled(! $cadangan)> Dari server</label>
                        </div>
                        <input type="file" name="file" accept=".sql" data-sumber="upload" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 file:ring-1 file:ring-slate-200">
                        <select name="nama" data-sumber="server" class="{{ $input }} hidden">
                            @foreach ($cadangan as $c)<option value="{{ $c['nama'] }}">{{ $c['nama'] }}</option>@endforeach
                        </select>
                    </div>
                    <label class="block"><span class="{{ $label }}">Password Anda</span>
                        <input type="password" name="password_konfirmasi" required autocomplete="current-password" class="{{ $input }}"></label>
                </div>
                <label class="mt-4 flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="yakin" value="1" required class="mt-0.5 rounded text-amber-500">
                    Saya mengerti seluruh data sesi ini akan diganti dengan isi file backup.
                </label>
                <div class="mt-4 flex justify-end">
                    <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600" onclick="return confirm('Yakin restore? Semua data sesi ini akan diganti.')">Restore Sekarang</button>
                </div>
            </form>

            {{-- ----- Hapus data ----- --}}
            <form method="POST" action="{{ route('admin.pengaturan.hapus-data') }}" class="mt-5 {{ $kotak }} ring-rose-200 bg-rose-50/30">
                @csrf
                <div class="flex items-start gap-4">
                    <span class="w-11 h-11 shrink-0 rounded-2xl bg-rose-500 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800">Hapus Data</p>
                        <p class="text-xs text-slate-600">Pilih kelompok data yang dihapus dari sesi <b>{{ $sesiAktif?->nama }}</b>. Backup otomatis dibuat dulu. Akun admin & asatid tidak ikut terhapus.</p>
                    </div>
                </div>
                <div class="mt-4 grid sm:grid-cols-2 gap-2">
                    @foreach ($kelompok as $kode => $k)
                    <label class="flex items-start gap-3 rounded-xl bg-white ring-1 ring-slate-200 p-3 cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-rose-400">
                        <input type="checkbox" name="kelompok[]" value="{{ $kode }}" @checked(in_array($kode, old('kelompok', []))) class="mt-0.5 rounded text-rose-500">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-800">{{ $k['label'] }}</span>
                            <span class="block text-[11px] text-slate-500">{{ $k['keterangan'] }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
                <div class="mt-4 grid sm:grid-cols-2 gap-4">
                    <label class="block"><span class="{{ $label }}">Ketik <span class="font-mono text-rose-600">HAPUS</span> untuk konfirmasi</span>
                        <input name="ketik" required autocomplete="off" class="{{ $input }}"></label>
                    <label class="block"><span class="{{ $label }}">Password Anda</span>
                        <input type="password" name="password_konfirmasi" required autocomplete="current-password" class="{{ $input }}"></label>
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700" onclick="return confirm('Yakin menghapus data yang dipilih?')">Hapus Data Terpilih</button>
                </div>
            </form>
            @endif

            <details class="mt-5 rounded-2xl ring-1 ring-slate-200 overflow-hidden">
                <summary class="cursor-pointer px-5 py-3 bg-[#f8fbfa] text-sm font-bold text-slate-700">Isi tabel ({{ $db['tabel']->count() }})</summary>
                <div class="max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white text-xs text-slate-400">
                            <tr><th class="px-5 py-2 text-left font-semibold">Tabel</th><th class="px-5 py-2 text-right font-semibold">Jumlah data</th><th class="px-5 py-2 text-right font-semibold">Ukuran</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($db['tabel'] as $t)
                            <tr class="hover:bg-[#f8fbfa]">
                                <td class="px-5 py-2 font-mono text-[13px] text-slate-700">{{ $t['nama'] }}</td>
                                <td class="px-5 py-2 text-right tabular-nums text-slate-700">{{ number_format($t['baris'], 0, ',', '.') }}</td>
                                <td class="px-5 py-2 text-right tabular-nums text-slate-400">{{ $ukuran($t['ukuran']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

        {{-- ================= SESI ================= --}}
        @elseif ($tab === 'sesi')
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-slate-800">Sesi Dauroh</h2>
                    <p class="text-sm text-slate-500">Tiap sesi (angkatan/gelombang) punya database dan folder file sendiri. Semua menu menampilkan data <b>sesi aktif</b>. Akun admin & asatid dipakai bersama semua sesi.</p>
                </div>
                <button type="button" data-buka-dialog="dialog-sesi-baru" class="{{ $tombol }} shrink-0 self-start">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Sesi Baru
                </button>
            </div>

            <div class="mt-5 grid xl:grid-cols-2 gap-4">
                @foreach ($sesiList as $s)
                @php $r = $ringkasSesi[$s->id]; @endphp
                <article class="relative rounded-2xl p-5 ring-1 {{ $s->is_active ? 'ring-2 ring-emerald-500 bg-emerald-50/40' : 'ring-slate-200 bg-white' }}">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center text-sm font-extrabold {{ $s->is_active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }}">{{ mb_strtoupper(mb_substr($s->nama, 0, 2)) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="font-bold text-slate-800 truncate">{{ $s->nama }}</h3>
                                @if ($s->is_active)<span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">AKTIF</span>@endif
                                @if ($s->utama)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">UTAMA</span>@endif
                            </div>
                            @if ($s->periode)<p class="text-xs text-slate-500">{{ $s->periode }}</p>@endif
                            @if ($s->keterangan)<p class="mt-1 text-xs text-slate-500">{{ $s->keterangan }}</p>@endif
                        </div>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2"><dt class="text-slate-400">Peserta</dt><dd class="font-bold text-slate-700">{{ $r['ada'] ? number_format($r['peserta'], 0, ',', '.') : 'Database hilang' }}</dd></div>
                        <div class="rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2"><dt class="text-slate-400">File</dt><dd class="font-bold text-slate-700">{{ $s->utama ? 'Folder utama' : $ukuran($r['ukuran_file']) }}</dd></div>
                        <div class="col-span-2 rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2 min-w-0"><dt class="text-slate-400">Database · folder</dt><dd class="font-mono text-[11px] text-slate-600 truncate">{{ $s->nama_database }} · storage/{{ $s->folder ?: '(utama)' }}</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @unless ($s->is_active)
                        <form method="POST" action="{{ route('admin.pengaturan.sesi.aktifkan', $s) }}" onsubmit="return confirm('Jadikan {{ addslashes($s->nama) }} sesi aktif? Semua menu akan menampilkan data sesi ini, dan peserta yang sedang login akan dikeluarkan.')">@csrf
                            <button class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Aktifkan</button>
                        </form>
                        @endunless
                        <button type="button" data-buka-dialog="dialog-sesi-{{ $s->id }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Ubah</button>
                        @unless ($s->utama || $s->is_active)
                        <button type="button" data-buka-dialog="dialog-hapus-sesi-{{ $s->id }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">Hapus</button>
                        @endunless
                    </div>
                </article>

                {{-- Ubah sesi --}}
                <dialog id="dialog-sesi-{{ $s->id }}" class="dialog-pengaturan">
                    <form method="POST" action="{{ route('admin.pengaturan.sesi.ubah', $s) }}" class="p-6 space-y-4">
                        @csrf @method('PUT')
                        <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-slate-800">Ubah Sesi</h3><button type="button" data-tutup-dialog class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
                        <label class="block"><span class="{{ $label }}">Nama sesi *</span><input name="nama" required maxlength="150" value="{{ $s->nama }}" class="{{ $input }}"></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block"><span class="{{ $label }}">Tanggal mulai</span><input type="date" name="tanggal_mulai" value="{{ $s->tanggal_mulai?->toDateString() }}" class="{{ $input }}"></label>
                            <label class="block"><span class="{{ $label }}">Tanggal selesai</span><input type="date" name="tanggal_selesai" value="{{ $s->tanggal_selesai?->toDateString() }}" class="{{ $input }}"></label>
                        </div>
                        <label class="block"><span class="{{ $label }}">Keterangan</span><textarea name="keterangan" rows="2" maxlength="1000" class="{{ $input }}">{{ $s->keterangan }}</textarea></label>
                        <div class="flex justify-end gap-2"><button type="button" data-tutup-dialog class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="{{ $tombol }}">Simpan</button></div>
                    </form>
                </dialog>

                {{-- Hapus sesi --}}
                @unless ($s->utama || $s->is_active)
                <dialog id="dialog-hapus-sesi-{{ $s->id }}" class="dialog-pengaturan">
                    <form method="POST" action="{{ route('admin.pengaturan.sesi.hapus', $s) }}" class="p-6 space-y-4">
                        @csrf @method('DELETE')
                        <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-rose-700">Hapus Sesi</h3><button type="button" data-tutup-dialog class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
                        <p class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800">Database <span class="font-mono">{{ $s->database }}</span>, semua file, dan riwayat backup sesi <b>{{ $s->nama }}</b> akan dihapus permanen. Unduh backup dulu bila masih diperlukan.</p>
                        <label class="block"><span class="{{ $label }}">Ketik nama sesi: <span class="font-mono text-rose-600">{{ $s->nama }}</span></span><input name="ketik" required autocomplete="off" class="{{ $input }}"></label>
                        <label class="block"><span class="{{ $label }}">Password Anda</span><input type="password" name="password_konfirmasi" required autocomplete="current-password" class="{{ $input }}"></label>
                        <div class="flex justify-end gap-2"><button type="button" data-tutup-dialog class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Hapus Permanen</button></div>
                    </form>
                </dialog>
                @endunless
                @endforeach
            </div>

            {{-- Sesi baru --}}
            <dialog id="dialog-sesi-baru" class="dialog-pengaturan">
                <form method="POST" action="{{ route('admin.pengaturan.sesi.simpan') }}" class="p-6 space-y-4" data-form-proses>
                    @csrf
                    <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-slate-800">Sesi Baru</h3><button type="button" data-tutup-dialog class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
                    <p class="text-xs text-slate-500">Database dan folder file baru dibuat otomatis. Akun admin & asatid langsung bisa dipakai di sesi ini.</p>
                    <label class="block"><span class="{{ $label }}">Nama sesi *</span><input name="nama" required maxlength="150" value="{{ old('nama') }}" placeholder="cth. Dauroh 1448 Gelombang 1" class="{{ $input }}"></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block"><span class="{{ $label }}">Tanggal mulai</span><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="{{ $input }}"></label>
                        <label class="block"><span class="{{ $label }}">Tanggal selesai</span><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="{{ $input }}"></label>
                    </div>
                    <label class="block"><span class="{{ $label }}">Keterangan</span><textarea name="keterangan" rows="2" maxlength="1000" class="{{ $input }}">{{ old('keterangan') }}</textarea></label>
                    <div class="rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 p-3 space-y-2">
                        <label class="block"><span class="{{ $label }}">Salin data awal dari</span>
                            <select name="salin_dari" class="{{ $input }}">
                                <option value="">— Mulai kosong —</option>
                                @foreach ($sesiList as $s)<option value="{{ $s->id }}" @selected($s->is_active)>{{ $s->nama }}</option>@endforeach
                            </select></label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="salin_master" value="1" checked class="rounded text-emerald-600"> Master data (kelas, kamar, waktu pelajaran, dll.)</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="salin_pengaturan" value="1" checked class="rounded text-emerald-600"> Pengaturan (profil pesantren, notifikasi)</label>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="aktifkan" value="1" class="rounded text-emerald-600"> Langsung jadikan sesi aktif</label>
                    <div class="flex justify-end gap-2"><button type="button" data-tutup-dialog class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="{{ $tombol }}" data-tombol-proses>Buat Sesi</button></div>
                </form>
            </dialog>
        @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Pop-up di tengah layar (Tailwind mereset margin bawaan <dialog>) */
    .dialog-pengaturan { margin: auto; inset: 0; max-height: calc(100vh - 2rem); width: min(520px, calc(100vw - 2rem)); border-radius: 1.5rem; padding: 0; border: 0; box-shadow: 0 30px 80px -20px rgba(11, 51, 44, .45); }
    .dialog-pengaturan::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(3px); }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        // Buka/tutup pop-up
        document.addEventListener('click', (e) => {
            const buka = e.target.closest('[data-buka-dialog]');
            if (buka) { document.getElementById(buka.dataset.bukaDialog)?.showModal(); return; }
            if (e.target.closest('[data-tutup-dialog]')) { e.target.closest('dialog')?.close(); return; }
            if (e.target.tagName === 'DIALOG') e.target.close();
        });
        @if ($tab === 'sesi' && $errors->any() && old('nama') !== null && ! request()->old('_method'))
        document.getElementById('dialog-sesi-baru')?.showModal(); // gagal validasi saat membuat sesi
        @endif

        // Tombol yang memproses lama (buat sesi): tampilkan "Memproses…"
        document.querySelectorAll('[data-form-proses]').forEach((f) => f.addEventListener('submit', () => {
            const b = f.querySelector('[data-tombol-proses]');
            b.disabled = true;
            b.textContent = 'Memproses… (bisa ±1 menit)';
        }));

        // ===== Tab Pesan: sisipkan isian, hitung karakter, pratinjau ala WhatsApp =====
        const CONTOH = @json(\App\Support\PesanWa::CONTOH);
        const KENAL = @json(array_keys(\App\Support\PesanWa::ISIAN));
        const aman = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        function pratinjau(teks) {
            const data = { ...CONTOH, catatan: '*Catatan admin:*\n' + CONTOH.catatan, penempatan: '*Penempatan:*\n' + CONTOH.penempatan };
            let hasil = teks.replace(/\{(\w+)\}/g, (m, k) => KENAL.includes(k) ? (data[k] ?? '') : m)
                .replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
            return aman(hasil)
                .replace(/\*([^*\n]+)\*/g, '<b>$1</b>')
                .replace(/(^|\W)_([^_\n]+)_(?=\W|$)/g, '$1<i>$2</i>')
                .replace(/~([^~\n]+)~/g, '<s>$1</s>');
        }
        document.querySelectorAll('[data-pesan]').forEach((kartu) => {
            const ta = kartu.querySelector('textarea');
            const lihat = kartu.querySelector('[data-pratinjau]');
            const hitung = kartu.querySelector('[data-hitung]');
            const segarkan = () => {
                lihat.innerHTML = pratinjau(ta.value) || '<span class="text-slate-400">(kosong = memakai teks bawaan)</span>';
                hitung.textContent = `${ta.value.length} / 3000 karakter`;
            };
            ta.addEventListener('input', segarkan);
            kartu.querySelectorAll('[data-sisip]').forEach((b) => b.addEventListener('click', () => {
                const awal = ta.selectionStart ?? ta.value.length, akhir = ta.selectionEnd ?? awal;
                ta.setRangeText(b.dataset.sisip, awal, akhir, 'end');
                ta.focus();
                segarkan();
            }));
            segarkan();
        });

        // Restore: pilih sumber upload / file di server
        const formRestore = document.getElementById('form-restore');
        const aturSumber = () => {
            const sumber = formRestore.querySelector('[name=sumber]:checked')?.value;
            formRestore.querySelectorAll('[data-sumber]').forEach((el) => {
                el.classList.toggle('hidden', el.dataset.sumber !== sumber);
                el.disabled = el.dataset.sumber !== sumber;
            });
        };
        if (formRestore) {
            formRestore.addEventListener('change', aturSumber);
            aturSumber();
        }
        // Tombol "Restore" di riwayat backup -> isi form restore
        document.addEventListener('click', (e) => {
            const t = e.target.closest('[data-pulihkan]');
            if (!t || !formRestore) return;
            formRestore.querySelector('[name=sumber][value=server]').checked = true;
            formRestore.querySelector('[name=nama]').value = t.dataset.pulihkan;
            aturSumber();
            formRestore.scrollIntoView({ behavior: 'smooth', block: 'center' });
            formRestore.querySelector('[name=password_konfirmasi]').focus({ preventScroll: true });
        });

        // Tombol "Simpan Perubahan" baru aktif setelah ada isian yang diubah
        document.querySelectorAll('[data-form-simpan]').forEach((form) => {
            const tombol = form.querySelector('[data-tombol-simpan]');
            const aktifkan = () => { tombol.disabled = false; };
            form.addEventListener('input', aktifkan);
            form.addEventListener('change', aktifkan);
            if (form.closest('body').querySelector('.bg-rose-50')) aktifkan(); // setelah gagal validasi
        });

        // Pratinjau logo baru sebelum disimpan
        const inputLogo = document.getElementById('input-logo');
        inputLogo?.addEventListener('change', () => {
            const file = inputLogo.files[0];
            if (!file) return;
            document.getElementById('pratinjau-logo').src = URL.createObjectURL(file);
            document.getElementById('nama-logo-baru').textContent = `Logo baru: ${file.name}. Klik "Simpan Perubahan" untuk menyimpan.`;
        });
    })();
</script>
@endpush
