{{-- Admin > PSB > Pesan Peserta: kotak masuk (satu baris per peserta) + kirim pesan baru --}}
@extends('layouts.admin')

@section('title', 'Pesan Peserta')

@section('content')
@php
use App\Models\PendaftaranSiswa;
use App\Models\PesanPeserta;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$warnaStatus = [PendaftaranSiswa::DITERIMA => 'bg-emerald-50 text-emerald-700', PendaftaranSiswa::DITOLAK => 'bg-rose-50 text-rose-600', PendaftaranSiswa::MENUNGGU => 'bg-amber-50 text-amber-700'];
@endphp

<div id="halaman-pesan-peserta">
<div class="space-y-6">
    @include('admin.akademik._flash')

    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-teal-600 via-[#0f6b5c] to-[#0b332c] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">PSB</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Pesan Peserta</h1>
                <p class="mt-1.5 text-sm text-white/75">Pesan dua arah dengan dasbor siswa. {{ $jumlahBelum ? $jumlahBelum . ' pesan belum dibaca.' : 'Semua pesan sudah dibaca.' }}</p>
            </div>
            <button type="button" @click="bukaDialog('dialog-kirim')" class="inline-flex items-center gap-2 self-start sm:self-auto rounded-2xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-amber-300 shadow-lg shadow-amber-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Kirim Pesan
            </button>
        </div>
    </section>

    {{-- Saring --}}
    <form method="GET" class="{{ $kartu }} p-4 flex flex-col sm:flex-row gap-2">
        <label class="flex-1 flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 focus-within:ring-2 focus-within:ring-emerald-500">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / nomor pendaftaran…" class="flex-1 bg-transparent border-0 py-2.5 text-sm outline-none focus:ring-0">
        </label>
        <div class="flex gap-1.5">
            @foreach (['semua' => 'Semua', 'belum' => 'Belum dibaca'] as $kode => $nama)
            <a href="{{ request()->fullUrlWithQuery(['saring' => $kode, 'page' => null]) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold {{ $saring === $kode ? 'bg-[#0b332c] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $nama }}</a>
            @endforeach
        </div>
        <button class="rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Cari</button>
    </form>

    {{-- Daftar percakapan --}}
    <section class="{{ $kartu }} overflow-hidden">
        @forelse ($ringkas as $r)
        @php $p = $peserta[$r->pendaftaran_siswa_id] ?? null; $akhir = $terakhir[$r->terakhir_id] ?? null; @endphp
        @continue(! $p)
        <a href="{{ route('admin.pesan-peserta.show', $p) }}" class="flex items-center gap-4 px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-[#f8fbfa] transition-colors {{ $r->belum ? 'bg-emerald-50/40' : '' }}">
            @if ($p->foto_url)
            <img data-perbesar="{{ $p->nama_lengkap }}" src="{{ $p->foto_url }}" alt="" class="w-11 h-11 shrink-0 rounded-2xl object-cover">
            @else
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}</span>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-bold text-slate-800 truncate">{{ $p->nama_lengkap }}</p>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $warnaStatus[$p->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $p->label_status }}</span>
                    <span class="text-[11px] text-slate-400">{{ $p->nomor_pendaftaran }}</span>
                </div>
                <p class="mt-0.5 text-sm text-slate-500 truncate">
                    @if ($akhir)<span class="font-semibold {{ $akhir->dari === PesanPeserta::DARI_ADMIN ? 'text-sky-600' : 'text-slate-700' }}">{{ $akhir->dari === PesanPeserta::DARI_ADMIN ? 'Admin:' : 'Peserta:' }}</span> {{ $akhir->judul ? $akhir->judul . ' — ' : '' }}{{ \Illuminate\Support\Str::limit($akhir->isi, 90) }}@endif
                </p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-[11px] text-slate-400">{{ $akhir?->created_at->diffForHumans() }}</p>
                @if ($r->belum)<span class="mt-1 inline-flex min-w-[22px] h-[22px] px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold items-center justify-center">{{ $r->belum }}</span>@endif
            </div>
        </a>
        @empty
        <div class="py-16 px-6 text-center">
            <p class="font-bold text-slate-700">{{ $saring === 'belum' ? 'Tidak ada pesan yang belum dibaca' : 'Belum ada pesan' }}</p>
            <p class="mt-1 text-sm text-slate-500">Pesan dari peserta (menu "Pesan untuk Admin" di dasbor siswa) muncul di sini.</p>
        </div>
        @endforelse
    </section>
    {{ $ringkas->links() }}
</div>

{{-- ===== Kirim pesan baru ===== --}}
<dialog id="dialog-kirim" ref="dialog-kirim" class="dialog-akademik" @click.self="tutupDialog">
    <form method="POST" action="{{ route('admin.pesan-peserta.kirim') }}" class="p-6 space-y-4">
        @csrf
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800">Kirim Pesan ke Peserta</h2>
            <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-xs text-slate-500">Pesan muncul di menu <b>Pesan dari Admin</b> pada dasbor siswa.</p>
        <label class="block"><span class="{{ $label }}">Penerima *</span>
            <select name="tujuan" v-model="tujuan" class="{{ $input }}">
                @foreach ($tujuanList as $kode => $nama)
                @php $n = $kode === 'semua' ? $jumlahTujuan->sum() : ($jumlahTujuan[$kode] ?? null); @endphp
                <option value="{{ $kode }}" @selected(old('tujuan', 'satu') === $kode)>{{ $nama }}{{ $kode !== 'satu' ? ' (' . ($n ?? 0) . ' peserta)' : '' }}</option>
                @endforeach
            </select></label>
        <label class="block" v-show="tujuan === 'satu'"><span class="{{ $label }}">Peserta *</span>
            <select name="pendaftaran_siswa_id" class="{{ $input }}">
                <option value="">Pilih peserta…</option>
                @foreach ($pilihanPeserta as $ps)<option value="{{ $ps->id }}" @selected((string) old('pendaftaran_siswa_id') === (string) $ps->id)>{{ $ps->nama_lengkap }} · {{ $ps->nomor_pendaftaran }} · {{ $ps->label_status }}</option>@endforeach
            </select></label>
        <label class="block"><span class="{{ $label }}">Judul *</span>
            <input name="judul" required maxlength="150" value="{{ old('judul') }}" placeholder="cth. Jadwal kedatangan peserta" class="{{ $input }}"></label>
        <label class="block"><span class="{{ $label }}">Pesan *</span>
            <textarea name="isi" rows="6" required maxlength="3000" class="{{ $input }}">{{ old('isi') }}</textarea></label>
        <div class="flex justify-end gap-2">
            <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button>
            <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Kirim</button>
        </div>
    </form>
</dialog>
</div>
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            data() {
                return {
                    tujuan: @json(old('tujuan', 'satu')), // penerima pesan baru; kotak "Peserta" hanya untuk "satu"
                };
            },
            methods: { ...metodeHalaman }, // bukaDialog, tutupDialog
            mounted() {
                // Gagal validasi saat mengirim -> buka lagi dialognya
                @if ($errors->any() || session('error'))
                this.bukaDialog('dialog-kirim');
                @endif
            },
        }).mount('#halaman-pesan-peserta');
    });
</script>
@endpush
