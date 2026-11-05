{{-- Admin > Manajemen Data > Data Asatid --}}
@extends('layouts.admin')

@section('title', __('Data Asatid'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$warnaStatus = [
    'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'diterima' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'ditolak'  => 'bg-rose-50 text-rose-700 ring-rose-200',
];
$tile = [
    ''         => [__('Semua'), $stat['total'], 'from-[#135347] to-[#0b332c] text-white', 'text-white/70'],
    'menunggu' => [__('Menunggu'), $stat['menunggu'], 'from-amber-400 to-orange-500 text-white', 'text-white/80'],
    'diterima' => [__('Diterima'), $stat['diterima'], 'from-emerald-500 to-teal-600 text-white', 'text-white/80'],
    'ditolak'  => [__('Ditolak'), $stat['ditolak'], 'from-rose-500 to-pink-600 text-white', 'text-white/80'],
];
$pesanWa = "Assalamu'alaikum, berikut link pendaftaran asatid Dauroh Qobul Darul Musthofa:\n" . $linkDaftar;
@endphp

<div id="halaman-data-asatid">
    {{-- Pop-up Tambah Cepat (partials/tambah-cepat.blade.php) --}}
    @include('partials.tambah-cepat', ['jenis' => __('Asatid'), 'aksi' => route('admin.asatid.tambah-cepat')])
    {{-- Pop-up Kirim Pesan ke asatid (partials/kirim-pesan-asatid.blade.php) --}}
    @include('partials.kirim-pesan-asatid', ['penerimaPesan' => $penerimaPesan])
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    {{-- Pesan berhasil / gagal tampil sebagai notifikasi pojok (partials/notifikasi) --}}

    {{-- ===== Link pendaftaran asatid ===== --}}
    <div class="relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7 mx-4 md:mx-10 lg:mx-20">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-end gap-5">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Manajemen Data') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ __('Data Asatid') }}</h1>
                <p class="mt-1.5 text-sm text-white/70">{{ __('Bagikan link di bawah ini kepada calon asatid. Formulir ini tidak tampil di website umum.') }}</p>

                <div class="mt-4 flex items-center gap-2 rounded-2xl bg-white/10 ring-1 ring-white/15 pl-4 pr-1.5 py-1.5">
                    <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    <input type="text" readonly value="{{ $linkDaftar }}" @click="$event.target.select()"
                        class="flex-1 min-w-0 bg-transparent text-sm text-white/90 outline-none truncate">
                    <button type="button" @click="salinLink"
                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white text-[#0b332c] text-xs font-bold hover:bg-emerald-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/></svg>
                        <span v-text="tersalin ? @js(__('Tersalin!')) : @js(__('Salin'))"></span>
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" @click="bukaKirimPesan"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-sky-400 text-[#0b332c] text-sm font-bold hover:bg-sky-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    {{ __('Kirim Pesan') }}
                </button>
                <button type="button" @click="bukaTambahCepat"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Tambah Cepat') }}
                </button>
                <a href="https://wa.me/?text={{ rawurlencode($pesanWa) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-400 text-[#0b332c] text-sm font-bold hover:bg-emerald-300 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    {{ __('Kirim lewat WhatsApp') }}
                </a>
                <form method="POST" action="{{ route('admin.asatid.link-baru') }}"
                    @submit="konfirmasi" data-konfirmasi="{{ __('Buat link baru? Link lama yang sudah dibagikan tidak bisa dipakai lagi.') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 ring-1 ring-white/20 text-sm font-semibold hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        {{ __('Buat link baru') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan status (bisa diklik untuk menyaring) ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mx-4 md:mx-10 lg:mx-20">
        @foreach ($tile as $kunci => [$label, $angka, $warna, $warnaLabel])
        <a href="{{ route('admin.asatid.index', array_filter(['status' => $kunci, 'q' => request('q')])) }}"
            @class(['relative overflow-hidden rounded-3xl p-5 bg-gradient-to-br transition hover:-translate-y-0.5', $warna,
                'ring-4 ring-offset-2 ring-offset-[#eef4f1] ring-[#0b332c]/20' => ($status ?? '') === $kunci])>
            <p class="text-xs font-semibold {{ $warnaLabel }}">{{ $label }}</p>
            <p class="mt-1 text-3xl font-extrabold">{{ $angka }}</p>
        </a>
        @endforeach
    </div>

    {{-- ===== Daftar ===== --}}
    <div class="{{ $kartu }} overflow-hidden mx-4 md:mx-10 lg:mx-20">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 p-4 sm:p-5 border-b border-slate-100">
            @if ($status)
            <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Cari nama, nomor, email, atau WhatsApp...') }}"
                    class="w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl py-2.5 pl-10 pr-4 text-sm focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15">
            </div>
            <button class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] transition-colors">{{ __('Cari') }}</button>
        </form>

        <div class="divide-y divide-slate-100">
            @forelse ($asatid as $a)
            <div class="flex items-center hover:bg-[#f5f9f7] transition-colors">
            <a href="{{ route('admin.asatid.show', $a) }}" class="flex-1 min-w-0 flex items-center gap-4 pl-4 sm:pl-5 pr-2 py-3.5">
                @if ($a->foto_url)
                <img data-perbesar="{{ $a->nama_lengkap }}" src="{{ $a->foto_url }}" alt="" class="w-12 h-12 rounded-2xl object-cover ring-1 ring-slate-200 shrink-0">
                @else
                <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-300 to-lime-200 text-[#0b332c] font-extrabold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</span>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 truncate">{{ $a->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500 truncate">
                        <span class="font-semibold text-slate-600">{{ $a->nomor_pendaftaran }}</span>
                        · <span dir="ltr">{{ $a->no_hp }}</span>
                        <span class="hidden sm:inline">· {{ $a->email }}</span>
                    </p>
                </div>
                <div class="hidden md:block text-right text-xs text-slate-400 shrink-0">
                    {{ $a->created_at->locale('id')->diffForHumans() }}
                </div>
                <span class="shrink-0 text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 {{ $warnaStatus[$a->status] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ __($a->label_status) }}</span>
            </a>
            {{-- Tombol edit (di luar link baris supaya tidak bertumpuk) --}}
            <a href="{{ route('admin.asatid.edit', $a) }}" title="{{ __('Edit data :nama', ['nama' => $a->nama_lengkap]) }}"
                class="shrink-0 mr-3 sm:mr-4 w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
            </a>
            </div>
            @empty
            <div class="px-5 py-14 text-center">
                <span class="mx-auto w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </span>
                <p class="mt-3 font-semibold text-slate-700">{{ __('Belum ada data asatid') }}</p>
                <p class="mt-1 text-sm text-slate-400">{{ __('Bagikan link pendaftaran di atas kepada calon asatid.') }}</p>
            </div>
            @endforelse
        </div>

        @if ($asatid->hasPages())
        <div class="px-4 sm:px-5 py-4 border-t border-slate-100">{{ $asatid->links() }}</div>
        @endif
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            mixins: [window.mixinTambahCepat, window.mixinKirimPesan], // pop-up Tambah Cepat & Kirim Pesan
            data() {
                return {
                    linkDaftar: @json($linkDaftar),
                    tersalin: false, // tulisan tombol jadi "Tersalin!" selama 2 detik
                };
            },
            methods: {
                ...metodeHalaman, // konfirmasi (tombol "Buat link baru", hapus)
                async salinLink() {
                    await navigator.clipboard.writeText(this.linkDaftar);
                    this.tersalin = true;
                    setTimeout(() => (this.tersalin = false), 2000);
                },
            },
        }).mount('#halaman-data-asatid');
    });
</script>
@endpush
