{{-- Admin > Data Admin > Detail --}}
@extends('layouts.admin')

@section('title', 'Detail Admin')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$warnaStatus = [
    'menunggu' => 'bg-amber-300/15 text-amber-200 ring-amber-300/30',
    'diterima' => 'bg-emerald-300/15 text-emerald-200 ring-emerald-300/30',
    'ditolak'  => 'bg-rose-300/15 text-rose-200 ring-rose-300/30',
][$a->status] ?? 'bg-white/10 text-white/80 ring-white/20';

$seksi = [
    ['Data Diri', 'emerald', 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', [
        'Nama Lengkap (Indonesia)' => $a->nama_lengkap,
        'Nama Lengkap (Arab)'      => $a->nama_lengkap_arab,
        'No. WhatsApp'             => $a->no_hp,
        'Alamat Lengkap'           => $a->alamat,
    ]],
    ['Akun Login', 'sky', 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', [
        'Email'       => $a->email,
        'Status Akun' => $a->user_id ? 'Aktif (role admin)' : 'Belum dibuat — dibuat saat diterima',
        'Diproses'    => optional($a->diproses_at)->translatedFormat('d M Y, H:i'),
    ]],
];
$nada = [
    'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
    'amber'   => 'bg-amber-50 text-amber-600 ring-amber-100',
    'sky'     => 'bg-sky-50 text-sky-600 ring-sky-100',
];
$lebar = ['Alamat Lengkap'];
@endphp

<div id="halaman-detail-admin">
<div class="space-y-6 pb-4">
    <a href="{{ route('admin.data-admin.index') }}"
        class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0b332c] transition-colors">
        <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center group-hover:-translate-x-0.5 transition-transform">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </span>
        Kembali ke Data Admin
    </a>

    @if (session('success'))
    <div class="p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ===== Kartu profil ===== --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white shadow-[0_24px_48px_-24px_rgba(7,31,26,0.6)]">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-28 -right-20 w-80 h-80 rounded-full bg-emerald-400/25 blur-3xl"></div>

        <div class="relative p-5 sm:p-8 flex flex-col md:flex-row gap-6">
            <div class="shrink-0 self-center md:self-start">
                @if ($a->foto_url)
                <img src="{{ $a->foto_url }}" alt="Foto {{ $a->nama_lengkap }}" class="w-36 h-44 sm:w-40 sm:h-48 object-cover rounded-3xl ring-4 ring-white/10 shadow-2xl shadow-black/30">
                @else
                <div class="w-36 h-44 sm:w-40 sm:h-48 rounded-3xl ring-4 ring-white/10 bg-gradient-to-br from-emerald-300 to-lime-200 flex items-center justify-center text-5xl font-extrabold text-[#0b332c]">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Detail Admin</p>
                        <h1 class="mt-1 text-2xl sm:text-[32px] leading-tight font-extrabold tracking-tight break-words">{{ $a->nama_lengkap }}</h1>
                        <p class="mt-1 text-2xl text-emerald-100/90" dir="rtl">{{ $a->nama_lengkap_arab }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 font-semibold tracking-wide"># {{ $a->nomor_pendaftaran }}</span>
                            <span class="rounded-full bg-white/5 px-3 py-1 text-white/60">mendaftar {{ $a->created_at->locale('id')->diffForHumans() }}</span>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ring-1 {{ $warnaStatus }}">{{ $a->label_status }}</span>
                </div>

                <div class="mt-5 flex flex-wrap gap-2.5">
                    <a href="{{ \App\Models\PendaftaranSiswa::linkWa($a->no_hp) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-2 text-sm hover:bg-white/10 transition-colors">
                        <svg class="w-4 h-4 text-emerald-300" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        <span dir="ltr">{{ $a->no_hp }}</span>
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-2xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-2 text-sm">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.5-9.75-6.5"/></svg>
                        {{ $a->email }}
                    </span>
                </div>

                @if ($a->catatan_admin)
                <div class="mt-3 flex gap-2.5 text-sm rounded-2xl bg-amber-300/10 ring-1 ring-amber-300/25 px-3.5 py-2.5 text-amber-50">
                    <span class="font-semibold text-amber-200 shrink-0">Catatan admin:</span>
                    <span class="whitespace-pre-line">{{ $a->catatan_admin }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Catatan dari peserta ===== --}}
    @if ($a->catatan_peserta)
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-50 via-white to-white ring-1 ring-sky-200/70 p-5 sm:p-6">
        <span class="absolute left-0 top-5 bottom-5 w-1 rounded-r-full bg-gradient-to-b from-sky-400 to-cyan-500"></span>
        <h2 class="text-[15px] font-bold text-slate-800">Catatan dari Pendaftar</h2>
        <p dir="auto" class="mt-3 whitespace-pre-line text-[15px] leading-relaxed text-slate-700 bg-white rounded-2xl ring-1 ring-sky-100 px-4 py-3">{{ $a->catatan_peserta }}</p>
    </div>
    @endif

    {{-- ===== Data ===== --}}
    <div class="grid xl:grid-cols-2 gap-6 items-start">
        @foreach ($seksi as [$judul, $warna, $ikon, $baris])
        <section @class([$kartu, 'p-5 sm:p-6', 'xl:col-span-2' => $loop->first])>
            <div class="flex items-center gap-3 mb-5">
                <span class="w-10 h-10 rounded-2xl ring-1 flex items-center justify-center {{ $nada[$warna] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </span>
                <h2 class="text-[15px] font-bold text-slate-800">{{ $judul }}</h2>
            </div>
            <dl @class(['grid gap-2.5 text-sm', 'md:grid-cols-2' => $loop->first])>
                @foreach ($baris as $label => $nilai)
                @if (in_array($label, $lebar))
                <div class="md:col-span-2 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3">
                    <dt class="text-xs font-medium text-slate-400">{{ $label }}</dt>
                    <dd dir="auto" class="mt-1 whitespace-pre-line text-[15px] font-semibold text-slate-700">{{ $nilai ?: '-' }}</dd>
                </div>
                @else
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 px-4 py-3">
                    <dt class="text-xs font-medium text-slate-400 shrink-0 max-w-[45%]">{{ $label }}</dt>
                    <dd dir="auto" class="min-w-0 text-right text-[15px] font-semibold text-slate-800 break-words">{{ $nilai ?: '-' }}</dd>
                </div>
                @endif
                @endforeach
            </dl>
        </section>
        @endforeach
    </div>

    {{-- ===== Keputusan admin ===== --}}
    @if ($a->status === 'menunggu')
    <div class="grid md:grid-cols-2 gap-6">
        <form method="POST" action="{{ route('admin.data-admin.terima', $a) }}" class="{{ $kartu }} p-5 sm:p-6 flex flex-col"
            @submit="konfirmasi" data-konfirmasi="Terima {{ $a->nama_lengkap }} sebagai admin? Akun admin dibuat (bisa membuka seluruh panel admin) dan chat WhatsApp dikirim.">
            @csrf
            <h2 class="text-[15px] font-bold text-emerald-700">Terima sebagai Admin</h2>
            <p class="text-xs text-slate-400 mt-0.5">Akun login (role admin) dibuat, lalu info dikirim lewat WhatsApp.</p>
            <textarea name="catatan_admin" rows="3" dir="auto" placeholder="Catatan untuk calon admin (opsional)"
                class="mt-4 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15">{{ old('catatan_admin') }}</textarea>
            <button class="mt-4 self-end inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-500 text-white text-sm font-bold hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Terima &amp; Kirim Chat
            </button>
        </form>

        <form method="POST" action="{{ route('admin.data-admin.tolak', $a) }}" class="{{ $kartu }} p-5 sm:p-6 flex flex-col">
            @csrf
            <h2 class="text-[15px] font-bold text-rose-600">Tolak Pendaftaran</h2>
            <p class="text-xs text-slate-400 mt-0.5">Alasan wajib diisi dan akan dikirim lewat WhatsApp.</p>
            <textarea name="catatan_admin" rows="3" dir="auto" required placeholder="Tulis alasan penolakan..."
                class="mt-4 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-400/15"></textarea>
            <button class="mt-4 self-end inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Tolak &amp; Kirim Chat
            </button>
        </form>
    </div>
    @endif

    {{-- Hapus --}}
    <div class="flex justify-end">
        <form method="POST" action="{{ route('admin.data-admin.destroy', $a) }}"
            @submit="konfirmasi" data-konfirmasi="Hapus data admin ini? Pas foto dan akun login-nya ikut terhapus.">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                Hapus data
            </button>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: konfirmasi hapus (metode dari partials/vue-metode.blade.php)
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-detail-admin'));
</script>
@endpush
