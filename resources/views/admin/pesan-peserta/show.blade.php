{{-- Admin > PSB > Pesan Peserta > percakapan dengan satu peserta --}}
@extends('layouts.admin')

@section('title', 'Pesan · ' . $peserta->nama_lengkap)

@section('content')
@php
use App\Models\PesanPeserta;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$waktu = fn ($t) => $t->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y · H:i');
@endphp

<div id="halaman-percakapan">
<div class="max-w-4xl space-y-5">
    @include('admin.akademik._flash')

    <a href="{{ route('admin.pesan-peserta.index') }}" @click="kembali" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-emerald-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </a>

    {{-- Peserta --}}
    <section class="{{ $kartu }} p-5 flex items-center gap-4">
        @if ($peserta->foto_url)
        <img data-perbesar="{{ $peserta->nama_lengkap }}" src="{{ $peserta->foto_url }}" alt="" class="w-14 h-14 rounded-2xl object-cover">
        @else
        <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white text-xl font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($peserta->nama_lengkap, 0, 1)) }}</span>
        @endif
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800">{{ $peserta->nama_lengkap }}</p>
            <p class="text-xs text-slate-500">{{ $peserta->nomor_pendaftaran }} · {{ $peserta->label_status }}{{ $peserta->kelasMaster ? ' · Kelas ' . $peserta->kelasMaster->nama : '' }}</p>
        </div>
        <a href="{{ route('admin.pendaftaran.show', $peserta) }}" class="shrink-0 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Detail peserta</a>
    </section>

    {{-- Percakapan --}}
    <section class="{{ $kartu }} p-5 sm:p-6 space-y-4">
        @forelse ($pesan as $p)
        @php $admin = $p->dari === PesanPeserta::DARI_ADMIN; @endphp
        <div class="flex {{ $admin ? 'justify-end' : 'justify-start' }}">
            <div class="group max-w-[85%] rounded-2xl px-4 py-3 {{ $admin ? 'rounded-tr-sm bg-emerald-50 ring-1 ring-emerald-100' : 'rounded-tl-sm bg-slate-50 ring-1 ring-slate-200' }}">
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="font-bold {{ $admin ? 'text-emerald-700' : 'text-slate-700' }}">{{ $admin ? 'Admin' . ($p->pengirim ? ' · ' . $p->pengirim->name : '') : $peserta->nama_lengkap }}</span>
                    <span class="text-slate-400">{{ $waktu($p->created_at) }}</span>
                    @if ($admin)<span class="text-slate-400">· {{ $p->dibaca_at ? 'dibaca' : 'belum dibaca' }}</span>@endif
                    <form method="POST" action="{{ route('admin.pesan-peserta.hapus', $p) }}" @submit="konfirmasi" data-konfirmasi="Hapus pesan ini?" class="ml-auto opacity-0 group-hover:opacity-100">
                        @csrf @method('DELETE')
                        <button class="text-rose-500 hover:text-rose-700" title="Hapus pesan">Hapus</button>
                    </form>
                </div>
                @if ($p->judul)<p class="mt-1 text-sm font-bold text-slate-800">{{ $p->judul }}</p>@endif
                <p class="mt-1 text-sm text-slate-700 whitespace-pre-line break-words">{{ $p->isi }}</p>
            </div>
        </div>
        @empty
        <p class="py-8 text-center text-sm text-slate-400">Belum ada pesan dengan peserta ini.</p>
        @endforelse
    </section>

    {{-- Balas --}}
    <form method="POST" action="{{ route('admin.pesan-peserta.balas', $peserta) }}" class="{{ $kartu }} p-5 space-y-3">
        @csrf
        <label class="block"><span class="text-xs font-semibold text-slate-600">Tulis balasan / pesan</span>
            <textarea name="isi" rows="4" required maxlength="3000" class="mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('isi') }}</textarea></label>
        <div class="flex justify-end">
            <button class="inline-flex items-center gap-2 rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                Kirim
            </button>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: konfirmasi hapus pesan (metode dari partials/vue-metode.blade.php)
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-percakapan'));
</script>
@endpush
