{{--
    Admin > Akademik > Tahun & Bulan Ajaran
    Tahun ajaran dibagi menjadi bulan ajaran (pengganti semester).
    Form tambah/ubah memakai <dialog> bawaan browser (lihat skrip di bawah).
--}}
@extends('layouts.admin')

@section('title', 'Tahun & Bulan Ajaran')

@section('content')
@php
use App\Models\Absensi;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$hijri = fn ($tgl) => $tgl ? Absensi::hijriyah($tgl->toDateString()) : null;
$tgl = fn ($t) => $t?->locale('id')->translatedFormat('d M Y');
$tombol = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold ring-1 transition-colors';
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20">
    @include('admin.akademik._flash')

    {{-- ===== Judul ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Akademik</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Tahun &amp; Bulan Ajaran</h1>
                <p class="mt-1.5 text-sm text-white/70">Kelola tahun ajaran dan pembagiannya per bulan. Jadwal mengajar memakai bulan yang aktif.</p>
                @if ($bulanAktif)
                <p class="mt-3 inline-flex flex-wrap items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1.5 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Aktif: {{ $bulanAktif->label }}
                </p>
                @endif
            </div>
            <button type="button" data-buka="dialog-tahun-baru"
                class="inline-flex items-center gap-2 self-start sm:self-auto rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-emerald-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Tambah Tahun Ajaran
            </button>
        </div>
    </section>

    {{-- ===== Daftar tahun ajaran ===== --}}
    @forelse ($tahunList as $t)
    <details class="{{ $kartu }} group overflow-hidden" @if ($t->is_active || session('buka') == $t->id) open @endif>
        <summary class="list-none cursor-pointer p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center gap-4 [&::-webkit-details-marker]:hidden">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <span class="w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center {{ $t->is_active ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-[#f5f9f7] text-emerald-700 ring-1 ring-emerald-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="text-xl font-extrabold text-slate-800">{{ $t->nama }}</span>
                        @if ($t->nama_masehi)<span class="text-sm font-semibold text-slate-400">/ {{ $t->nama_masehi }} M</span>@endif
                        @if ($t->is_active)<span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">Aktif</span>@endif
                    </p>
                    <p class="mt-0.5 text-sm text-slate-500">
                        {{ $t->bulan->count() }} bulan
                        @if ($t->tanggal_mulai) · {{ $tgl($t->tanggal_mulai) }} – {{ $tgl($t->tanggal_selesai) }} @endif
                    </p>
                    @if ($t->tanggal_mulai)
                    <p class="text-xs text-emerald-700/80">{{ $hijri($t->tanggal_mulai) }} – {{ $hijri($t->tanggal_selesai) }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2" onclick="event.stopPropagation()">
                @if ($t->is_active)
                    <span class="{{ $tombol }} bg-emerald-600 text-white ring-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Aktif
                    </span>
                @else
                    <form method="POST" action="{{ route('admin.tahun-ajaran.aktifkan', $t) }}">@csrf
                        <button class="{{ $tombol }} bg-white text-slate-600 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Nonaktif
                        </button>
                    </form>
                @endif
                <button type="button" class="{{ $tombol }} bg-white text-slate-600 ring-slate-200 hover:bg-slate-50" onclick="this.closest('details').toggleAttribute('open')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    Bulan
                    <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <button type="button" data-buka="dialog-tahun-{{ $t->id }}" title="Ubah" class="{{ $tombol }} bg-white text-slate-600 ring-slate-200 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $t) }}"
                    onsubmit="return confirm('Hapus tahun ajaran {{ addslashes($t->nama) }}? Semua bulan dan jadwal di dalamnya ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button title="Hapus" class="{{ $tombol }} bg-rose-500 text-white ring-rose-500 hover:bg-rose-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </form>
            </div>
        </summary>

        {{-- ----- Bulan ajaran ----- --}}
        <div class="border-t border-slate-100 bg-[#fafcfb] p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="font-bold text-slate-800">Bulan ajaran <span class="text-slate-400 font-semibold">({{ $t->bulan->count() }})</span></h3>
                <div class="flex flex-wrap gap-2">
                    @if ($t->bulan->isEmpty() && $t->tanggal_mulai && $t->tanggal_selesai)
                    <form method="POST" action="{{ route('admin.tahun-ajaran.isi-bulan', $t) }}">@csrf
                        <button class="{{ $tombol }} bg-amber-50 text-amber-800 ring-amber-200 hover:bg-amber-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            Isi otomatis per bulan Hijriyah
                        </button>
                    </form>
                    @endif
                    <button type="button" data-buka="dialog-bulan-baru-{{ $t->id }}" class="{{ $tombol }} bg-[#0b332c] text-white ring-[#0b332c] hover:bg-[#135347]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        Tambah Bulan
                    </button>
                </div>
            </div>

            @if ($t->bulan->isNotEmpty())
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($t->bulan as $b)
                <div class="rounded-2xl bg-white p-4 ring-1 {{ $b->is_active ? 'ring-2 ring-emerald-400 shadow-[0_10px_24px_-14px_rgba(16,185,129,0.6)]' : 'ring-slate-200' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bulan ke-{{ $b->urutan }}</p>
                            <p class="font-bold text-slate-800 truncate">{{ $b->nama }}</p>
                        </div>
                        @if ($b->is_active)
                            <span class="shrink-0 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[11px] font-bold text-white">Aktif</span>
                        @endif
                    </div>
                    @if ($b->tanggal_mulai)
                    <p class="mt-2 text-xs text-slate-500">{{ $b->periode }}</p>
                    <p class="text-[11px] text-emerald-700/80">{{ $hijri($b->tanggal_mulai) }} – {{ $hijri($b->tanggal_selesai) }}</p>
                    @endif
                    <div class="mt-3 flex items-center gap-1.5">
                        <a href="{{ route('admin.jadwal.index', ['bulan' => $b->id]) }}" class="text-[11px] font-semibold rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100 px-2.5 py-1 hover:bg-sky-100">{{ $b->jadwal_count }} jadwal</a>
                        <span class="flex-1"></span>
                        @unless ($b->is_active)
                        <form method="POST" action="{{ route('admin.bulan-ajaran.aktifkan', $b) }}">@csrf
                            <button class="rounded-lg px-2 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50">Aktifkan</button>
                        </form>
                        @endunless
                        <button type="button" data-buka="dialog-bulan-{{ $b->id }}" title="Ubah" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.bulan-ajaran.destroy', $b) }}"
                            onsubmit="return confirm('Hapus bulan {{ addslashes($b->nama) }}?{{ $b->jadwal_count ? ' ' . $b->jadwal_count . ' jadwal di bulan ini ikut terhapus.' : '' }}')">
                            @csrf @method('DELETE')
                            <button title="Hapus" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Dialog ubah bulan --}}
                <dialog id="dialog-bulan-{{ $b->id }}" class="dialog-akademik">
                    @include('admin.akademik._form-bulan', ['aksi' => route('admin.bulan-ajaran.update', $b), 'metode' => 'PUT', 'judul' => 'Ubah Bulan', 'b' => $b])
                </dialog>
                @endforeach
            </div>
            @else
            <p class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500 ring-1 ring-dashed ring-slate-200">
                Belum ada bulan ajaran. {{ $t->tanggal_mulai && $t->tanggal_selesai ? 'Klik "Isi otomatis per bulan Hijriyah" atau tambah satu per satu.' : 'Tambahkan bulan satu per satu, atau isi tanggal tahun ajaran agar bisa diisi otomatis.' }}
            </p>
            @endif
        </div>

        {{-- Dialog ubah tahun & tambah bulan --}}
        <dialog id="dialog-tahun-{{ $t->id }}" class="dialog-akademik">
            @include('admin.akademik._form-tahun', ['aksi' => route('admin.tahun-ajaran.update', $t), 'metode' => 'PUT', 'judul' => 'Ubah Tahun Ajaran', 't' => $t])
        </dialog>
        <dialog id="dialog-bulan-baru-{{ $t->id }}" class="dialog-akademik">
            @include('admin.akademik._form-bulan', ['aksi' => route('admin.bulan-ajaran.store', $t), 'metode' => 'POST', 'judul' => 'Tambah Bulan · ' . $t->nama, 'b' => null, 'urutanBaru' => $t->bulan->max('urutan') + 1])
        </dialog>
    </details>
    @empty
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <p class="font-bold text-slate-700">Belum ada tahun ajaran</p>
        <p class="mt-1 text-sm text-slate-500">Klik "Tambah Tahun Ajaran" untuk memulai. Bulan-bulannya bisa dibuat otomatis mengikuti kalender Hijriyah.</p>
    </div>
    @endforelse
</div>

{{-- Dialog tambah tahun ajaran --}}
<dialog id="dialog-tahun-baru" class="dialog-akademik">
    @include('admin.akademik._form-tahun', ['aksi' => route('admin.tahun-ajaran.store'), 'metode' => 'POST', 'judul' => 'Tambah Tahun Ajaran', 't' => null])
</dialog>

<datalist id="nama-bulan-hijriyah">
    @foreach ($namaHijriyah as $n)<option value="{{ $n }}">@endforeach
</datalist>
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
@include('admin.akademik._dialog-script')
<script>
    // Tanggal Hijriyah di bawah isian tanggal (form tahun & bulan)
    (function () {
        const FMT = new Intl.DateTimeFormat('id-u-ca-islamic-umalqura', { day: 'numeric', month: 'long', year: 'numeric' });
        document.querySelectorAll('input[type=date][data-hijri]').forEach((el) => {
            const ket = document.getElementById(el.dataset.hijri);
            const isi = () => { ket.textContent = el.value ? FMT.format(new Date(el.value + 'T12:00:00')) : ''; };
            el.addEventListener('input', isi);
            isi();
        });
    })();
</script>
@endpush
