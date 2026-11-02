{{--
    Admin > Akademik > Kurikulum & Kitab
    Kitab dikelompokkan per bidang ilmu (Master Data > Bidang Ilmu), untuk satu tahun ajaran.
    Pilihan "bulan ke" mengikuti jumlah bulan di tahun ajaran itu. Kelas dipilih di Jadwal Mengajar.
    Form tambah/ubah memakai <dialog> (lihat _form-kitab).
--}}
@extends('layouts.admin')

@section('title', 'Kurikulum & Kitab')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$namaBidang = $bidangList->keyBy('id');
$totalJam = $semua->sum('frekuensi');
$tautan = fn (array $ubah) => route('admin.kurikulum.index', array_filter(array_merge($filter, $ubah), fn ($v) => $v !== null && $v !== ''));
$ikonStat = [
    'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
    'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
];
$warnaStat = ['bg-sky-50 text-sky-600 ring-sky-100', 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'bg-violet-50 text-violet-600 ring-violet-100'];
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20">
    @include('admin.akademik._flash')

    {{-- ===== Judul ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-teal-600 via-[#0f6b5c] to-[#0b332c] text-white p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Akademik</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Manajemen Kurikulum &amp; Kitab</h1>
                <p class="mt-1.5 text-sm text-white/75">Total {{ $semua->count() }} kitab · {{ $semua->pluck('bidang_ilmu_id')->filter()->unique()->count() }} bidang ilmu</p>
            </div>
            <button type="button" data-buka="dialog-kitab-baru"
                class="inline-flex items-center gap-2 self-start sm:self-auto rounded-2xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-amber-300 shadow-lg shadow-amber-500/30 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Tambah Kitab
            </button>
        </div>
    </section>

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="{{ $kartu }} p-4">
            <span class="w-9 h-9 rounded-xl ring-1 flex items-center justify-center bg-teal-50 text-teal-600 ring-teal-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonStat[0] }}"/></svg>
            </span>
            <p class="mt-3 text-2xl font-extrabold text-slate-800">{{ $semua->count() }}</p>
            <p class="text-xs font-semibold text-slate-600">Total Kitab</p>
            <p class="text-[11px] text-slate-400">{{ $bidangList->count() }} bidang ilmu</p>
        </div>
        @php
        $ringkas = [
            [$tahunAktif ? $semua->where('tahun_ajaran_id', $tahunAktif->id)->count() : 0, 'Kitab tahun aktif', $tahunAktif ? 'TA ' . $tahunAktif->nama . ' · ' . $tahunAktif->bulan->count() . ' bulan' : 'Belum ada tahun aktif'],
            [$jumlahBulanIni, 'Kitab bulan ini', $bulanAktif ? 'Bulan ke-' . $bulanAktif->urutan . ' · ' . $bulanAktif->nama : 'Belum ada bulan aktif'],
            [$terjadwal->count(), 'Sudah dijadwalkan', 'di Jadwal Mengajar bulan ini'],
        ];
        @endphp
        @foreach ($ringkas as $i => [$angka, $judulStat, $ket])
        <div class="{{ $kartu }} p-4">
            <span class="w-9 h-9 rounded-xl ring-1 flex items-center justify-center {{ $warnaStat[$i % 3] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonStat[1] }}"/></svg>
            </span>
            <p class="mt-3 text-2xl font-extrabold text-slate-800">{{ $angka }}</p>
            <p class="text-xs font-semibold text-slate-600 truncate">{{ $judulStat }}</p>
            <p class="text-[11px] text-slate-400 truncate">{{ $ket }}</p>
        </div>
        @endforeach
        <div class="{{ $kartu }} p-4">
            <span class="w-9 h-9 rounded-xl ring-1 flex items-center justify-center bg-amber-50 text-amber-600 ring-amber-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonStat[2] }}"/></svg>
            </span>
            <p class="mt-3 text-2xl font-extrabold text-slate-800">{{ $totalJam }}</p>
            <p class="text-xs font-semibold text-slate-600">Total Jam/Minggu</p>
            <p class="text-[11px] text-slate-400">seluruh kitab</p>
        </div>
    </div>

    {{-- ===== Cari & saring ===== --}}
    <form method="GET" class="{{ $kartu }} p-4 flex flex-col sm:flex-row gap-2">
        @if ($filter['bidang'])<input type="hidden" name="bidang" value="{{ $filter['bidang'] }}">@endif
        <label class="flex-1 flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 focus-within:ring-2 focus-within:ring-emerald-500">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Cari kitab (nama, nama Arab, deskripsi)…" class="flex-1 bg-transparent border-0 py-2.5 text-sm outline-none focus:ring-0">
        </label>
        <select name="tahun" onchange="this.form.submit()" class="rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-700">
            <option value="">Semua tahun ajaran</option>
            @foreach ($tahunList as $t)<option value="{{ $t->id }}" @selected((string) $filter['tahun'] === (string) $t->id)>TA {{ $t->nama }}{{ $t->is_active ? ' (aktif)' : '' }}</option>@endforeach
        </select>
        <button class="rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Cari</button>
    </form>

    {{-- Chip bidang ilmu --}}
    <div class="{{ $kartu }} p-3">
        <div class="flex gap-2 overflow-x-auto pb-1">
            <a href="{{ $tautan(['bidang' => null]) }}"
                class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors {{ ! $filter['bidang'] ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">
                Semua <span class="rounded-full px-2 text-[11px] {{ ! $filter['bidang'] ? 'bg-white/20' : 'bg-slate-100' }}">{{ $semua->count() }}</span>
            </a>
            @foreach ($bidangList as $b)
            @php $aktif = (string) $filter['bidang'] === (string) $b->id; @endphp
            <a href="{{ $tautan(['bidang' => $b->id]) }}"
                class="shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 transition-colors {{ $aktif ? 'bg-[#0b332c] text-white ring-[#0b332c]' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' }}">
                <span class="w-2 h-2 rounded-full {{ $b->warna ?: 'bg-slate-200' }} ring-1 ring-black/10"></span>
                {{ $b->nama }} <span class="rounded-full px-2 text-[11px] {{ $aktif ? 'bg-white/20' : 'bg-slate-100' }}">{{ $jumlahPerBidang[$b->id] ?? 0 }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ===== Kitab per bidang ===== --}}
    @forelse ($grup as $bidangId => $kitabList)
    @php $bidang = $namaBidang[$bidangId] ?? null; @endphp
    <section class="{{ $kartu }} p-5 sm:p-6">
        <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
            <span class="w-3 h-3 rounded-full {{ $bidang?->warna ?: 'bg-slate-200' }} ring-1 ring-black/10"></span>
            {{ $bidang?->nama ?? 'Tanpa bidang ilmu' }}
            <span class="text-sm font-semibold text-slate-400">({{ $kitabList->count() }} kitab)</span>
        </h2>
        <div class="mt-4 grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($kitabList as $k)
            @php $nKelas = $terjadwal[$k->id] ?? 0; @endphp
            <article class="group flex flex-col rounded-2xl ring-1 ring-slate-200 bg-white overflow-hidden hover:ring-emerald-300 hover:shadow-[0_16px_36px_-20px_rgba(11,51,44,0.4)] transition-all">
                <div class="flex items-center gap-2 px-4 py-2.5 bg-[#f8fbfa] border-b border-slate-100 text-xs font-semibold text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonStat[0] }}"/></svg>
                    {{ $bidang?->nama ?? 'Tanpa bidang' }}
                </div>
                <div class="flex-1 p-4">
                    <h3 class="font-bold text-slate-800">{{ $k->nama }}</h3>
                    @if ($k->nama_arab)<p class="text-sm text-slate-500" dir="rtl">{{ $k->nama_arab }}</p>@endif
                    <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $k->tahunAjaran?->is_active ? 'bg-teal-50 text-teal-700 ring-teal-200' : 'bg-slate-50 text-slate-500 ring-slate-200' }}">
                        {{ $k->tahunAjaran ? 'TA ' . $k->tahunAjaran->nama : 'Tahun ajaran belum dipilih' }}
                    </span>

                    <p class="mt-3 text-[11px] font-semibold text-slate-400">Bulan ke:</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @forelse ($k->bulan_label as $b)
                            @php $bulanIni = $bulanAktif && $k->tahun_ajaran_id === $bulanAktif->tahun_ajaran_id && (int) $b === (int) $bulanAktif->urutan; @endphp
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $bulanIni ? 'bg-amber-400 text-[#0b332c] ring-amber-400' : 'bg-white text-slate-600 ring-slate-200' }}">{{ $b }}</span>
                        @empty
                            <span class="rounded-full bg-slate-50 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200">Semua bulan{{ $k->tahunAjaran ? ' (' . $k->tahunAjaran->bulan->count() . ' bulan)' : '' }}</span>
                        @endforelse
                    </div>

                    <dl class="mt-3 space-y-1 text-xs">
                        <div class="flex justify-between"><dt class="text-slate-500">Frekuensi:</dt><dd class="font-semibold text-slate-700">{{ $k->frekuensi }}x/minggu</dd></div>
                    </dl>

                    <p class="mt-3 inline-block rounded-lg px-2.5 py-1 text-[11px] font-semibold {{ $nKelas ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400' }}">
                        {{ $nKelas ? "Dijadwalkan di {$nKelas} kelas" : 'Belum dijadwalkan' }}{{ $bulanAktif ? ' · ' . $bulanAktif->nama : '' }}
                    </p>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 px-4 py-2.5">
                    <button type="button" data-buka="dialog-kitab-{{ $k->id }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-sky-600 hover:text-sky-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>Edit
                    </button>
                    <form method="POST" action="{{ route('admin.kurikulum.destroy', $k) }}" onsubmit="return confirm('Hapus kitab {{ addslashes($k->nama) }}?')">
                        @csrf @method('DELETE')
                        <button class="inline-flex items-center gap-1.5 text-sm font-semibold text-rose-600 hover:text-rose-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>Hapus
                        </button>
                    </form>
                </div>
            </article>

            <dialog id="dialog-kitab-{{ $k->id }}" class="dialog-akademik">
                @include('admin.akademik._form-kitab', ['aksi' => route('admin.kurikulum.update', $k), 'metode' => 'PUT', 'judul' => 'Ubah Kitab', 'k' => $k])
            </dialog>
            @endforeach
        </div>
    </section>
    @empty
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <p class="font-bold text-slate-700">{{ $semua->isEmpty() ? 'Belum ada kitab' : 'Kitab tidak ditemukan' }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ $semua->isEmpty() ? 'Klik "Tambah Kitab" untuk mengisi kurikulum.' : 'Coba kata kunci atau bidang ilmu lain.' }}</p>
    </div>
    @endforelse
</div>

<dialog id="dialog-kitab-baru" class="dialog-akademik">
    @include('admin.akademik._form-kitab', ['aksi' => route('admin.kurikulum.store'), 'metode' => 'POST', 'judul' => 'Tambah Kitab', 'k' => null])
</dialog>
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
@include('admin.akademik._dialog-script')
<script>
    // Pilihan "bulan ke" mengikuti tahun ajaran yang dipilih (bulan tahun lain disembunyikan dan tidak ikut terkirim)
    document.addEventListener('change', (e) => {
        const pilih = e.target.closest('[data-pilih-tahun]');
        if (!pilih) return;
        pilih.closest('[data-form-kitab]').querySelectorAll('[data-bulan-tahun]').forEach((grup) => {
            const tampil = grup.dataset.bulanTahun === pilih.value;
            grup.classList.toggle('hidden', !tampil);
            grup.querySelectorAll('input').forEach((i) => { i.disabled = !tampil; if (!tampil) i.checked = false; });
        });
    });
</script>
@endpush
