{{--
    Admin > Akademik > Jadwal Mengajar
    Tampilan: grid (jam × hari), per ustadz, per kelas. Satu <dialog> form dipakai untuk tambah & ubah
    (diisi oleh skrip di bawah dari atribut data-* tiap kartu jadwal).
--}}
@extends('layouts.admin')

@section('title', 'Jadwal Mengajar')

@section('content')
@php
use App\Models\Absensi;
use App\Models\JadwalMengajar;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$hari = JadwalMengajar::HARI;
$warnaKelas = [
    ['bg-sky-50 ring-sky-200', 'text-sky-800', 'bg-sky-500'],
    ['bg-emerald-50 ring-emerald-200', 'text-emerald-800', 'bg-emerald-500'],
    ['bg-violet-50 ring-violet-200', 'text-violet-800', 'bg-violet-500'],
    ['bg-amber-50 ring-amber-200', 'text-amber-800', 'bg-amber-500'],
    ['bg-rose-50 ring-rose-200', 'text-rose-800', 'bg-rose-500'],
    ['bg-teal-50 ring-teal-200', 'text-teal-800', 'bg-teal-500'],
];
$indeksKelas = $kelasList->pluck('id')->flip();
$gayaKelas = fn ($id) => $warnaKelas[($indeksKelas[$id] ?? 0) % count($warnaKelas)];
$tautan = fn (array $ubah) => route('admin.jadwal.index', array_filter(array_merge(['bulan' => $bulan?->id, 'kelas' => $kelasFilter, 'tampilan' => $tampilan], $ubah)));
$inisial = fn ($nama) => collect(preg_split('/\s+/', trim(preg_replace('/^(Ustadz|Ust\.?|Syekh|KH\.?)\s+/i', '', (string) $nama))))->filter()->take(3)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
// Data kitab untuk pilihan di form: yang tampil hanya kitab kurikulum bulan ini (tahun ajaran & bulan ke cocok)
$dataKitab = $kitabList->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama, 'cocok' => $kitabBulanIni->contains($k->id)])->values();
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20">
    @include('admin.akademik._flash')

    @if (! $bulan)
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <p class="text-lg font-bold text-slate-700">Belum ada bulan ajaran</p>
        <p class="mt-1 text-sm text-slate-500">Jadwal mengajar dibuat per bulan ajaran. Buat tahun ajaran dan bulannya dulu.</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="mt-4 inline-flex rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Buka Tahun &amp; Bulan Ajaran</a>
    </div>
    @else

    {{-- ===== Judul & tombol ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-50 via-white to-amber-50 ring-1 ring-emerald-200 p-5 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold text-[#0b332c]">Jadwal Mengajar</h1>
                <p class="mt-0.5 text-sm font-semibold text-emerald-700">Bulan ke-{{ $bulan->urutan }} · {{ $bulan->nama }} · TA {{ $bulan->tahunAjaran?->nama }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-buka="dialog-salin" class="inline-flex items-center gap-2 rounded-xl bg-violet-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-600 shadow-lg shadow-violet-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/></svg>
                    Salin Jadwal
                </button>
                <button type="button" data-buka="dialog-ganti" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600 shadow-lg shadow-amber-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    Ganti Ustadz
                </button>
                <button type="button" data-tambah class="inline-flex items-center gap-2 rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#135347] shadow-lg shadow-[#0b332c]/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Tambah Jadwal
                </button>
            </div>
        </div>
    </section>

    {{-- ===== Periode ===== --}}
    <section class="{{ $kartu }} p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="flex items-center gap-2 font-bold text-slate-800">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Periode
            </h2>
            <a href="{{ route('admin.tahun-ajaran.index') }}" class="rounded-xl bg-[#0b332c] px-3 py-2 text-xs font-bold text-white hover:bg-[#135347]">Kelola Tahun &amp; Bulan</a>
        </div>
        <form method="GET" class="mt-4 grid sm:grid-cols-2 gap-3">
            <input type="hidden" name="tampilan" value="{{ $tampilan }}">
            <label><span class="{{ $label }}">Tahun ajaran</span>
                <select class="{{ $input }}" onchange="const b = this.selectedOptions[0].dataset.bulan; if (b) location.href = '{{ route('admin.jadwal.index') }}?bulan=' + b + '&tampilan={{ $tampilan }}'">
                    @foreach ($tahunList as $t)
                    <option data-bulan="{{ $t->bulan->first()?->id }}" @selected($bulan->tahun_ajaran_id === $t->id)>{{ $t->nama }}{{ $t->is_active ? ' ✓ aktif' : '' }}{{ $t->bulan->isEmpty() ? ' (belum ada bulan)' : '' }}</option>
                    @endforeach
                </select></label>
            <label><span class="{{ $label }}">Bulan ajaran</span>
                <select name="bulan" onchange="this.form.submit()" class="{{ $input }}">
                    @foreach ($tahunList->firstWhere('id', $bulan->tahun_ajaran_id)?->bulan ?? [] as $b)
                    <option value="{{ $b->id }}" @selected($b->id === $bulan->id)>Bulan ke-{{ $b->urutan }} · {{ $b->nama }}{{ $b->is_active ? ' ✓ aktif' : '' }}</option>
                    @endforeach
                </select></label>
        </form>
        <div class="mt-3 rounded-2xl bg-[#f8fbfa] px-4 py-3 text-sm">
            <p>Status: <b class="{{ $bulan->is_active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $bulan->is_active ? 'Bulan aktif' : 'Tidak aktif' }}</b>
                @unless ($bulan->is_active)
                <span class="text-xs text-slate-400">· jadwal yang tampil di dasbor asatid adalah jadwal bulan aktif</span>
                @endunless
            </p>
            @if ($bulan->tanggal_mulai)
            <p class="text-slate-600">Periode: {{ $bulan->periode }} <span class="text-emerald-700">({{ Absensi::hijriyah($bulan->tanggal_mulai->toDateString()) }} – {{ Absensi::hijriyah($bulan->tanggal_selesai->toDateString()) }})</span></p>
            @endif
        </div>
    </section>

    {{-- ===== Cari, kelas, tampilan ===== --}}
    <section class="{{ $kartu }} p-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <label class="flex-1 flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 focus-within:ring-2 focus-within:ring-emerald-500">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="search" id="cari-jadwal" placeholder="Cari jadwal (kitab, ustadz, kelas)…" class="flex-1 bg-transparent border-0 py-2.5 text-sm outline-none focus:ring-0">
        </label>
        <label class="flex items-center gap-2 text-sm text-slate-600">Kelas:
            <select onchange="location.href = this.value" class="rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm">
                <option value="{{ $tautan(['kelas' => null]) }}">Semua Kelas</option>
                @foreach ($kelasList as $k)<option value="{{ $tautan(['kelas' => $k->id]) }}" @selected((string) $kelasFilter === (string) $k->id)>{{ $k->nama }}</option>@endforeach
            </select></label>
        <div class="flex gap-1.5">
            @foreach (['grid' => 'Grid', 'kelas' => 'Per Kelas', 'ustadz' => 'Per Ustadz'] as $kode => $nama)
            <a href="{{ $tautan(['tampilan' => $kode]) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors {{ $tampilan === $kode ? 'bg-[#0b332c] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $nama }}</a>
            @endforeach
        </div>
    </section>

    @if ($waktuList->isEmpty())
    <div class="{{ $kartu }} p-6 text-center text-sm text-slate-500">Belum ada jam pelajaran. Tambahkan di <a href="{{ route('admin.master-data.index', ['tab' => 'waktu-pelajaran']) }}" class="font-semibold text-emerald-700 hover:underline">Master Data › Waktu Pelajaran</a>.</div>
    @endif

    {{-- ===== Kartu satu jadwal (dipakai semua tampilan) ===== --}}
    @php
    $kartuJadwal = function ($j, $lengkap = false) use ($gayaKelas, $namaUstadz, $inisial, $hari) {
        [$latar, $teks] = $gayaKelas($j->kelas_id);
        $ustadz = $namaUstadz[$j->user_id] ?? null;
        $cari = mb_strtolower(implode(' ', [$j->kitab?->nama, $ustadz, $j->kelas?->nama, $j->waktu?->nama, $hari[$j->hari] ?? '']));
        return view('admin.akademik._kartu-jadwal', compact('j', 'latar', 'teks', 'ustadz', 'cari', 'lengkap') + ['inisial' => $inisial($ustadz)])->render();
    };
    @endphp

    {{-- ===== Tampilan GRID ===== --}}
    @if ($tampilan === 'grid' && $waktuList->isNotEmpty())
    <p class="mb-5 flex items-center gap-2 px-1 text-xs text-slate-500">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/></svg>
        <span>Geser kartu ke hari/jam lain untuk memindahkan jadwal, lalu klik <b>Simpan</b>. Klik ikon pensil untuk mengubah isi jadwal.</span>
    </p>
    <section class="{{ $kartu }} overflow-x-auto " id="grid-jadwal">
        {{-- table-fixed: semua kolom hari sama lebar, sel berbentuk agak kotak --}}
        <table class="w-full min-w-[1040px] table-fixed border-collapse text-sm">
            <thead>
                <tr class="bg-[#f8fbfa]">
                    <th class="w-32 px-3 py-3 text-left font-bold text-slate-700">Waktu</th>
                    @foreach ($hari as $h)<th class="px-2 py-3 font-bold text-slate-700">{{ $h }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($waktuList as $w)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-3 py-3">
                        <p class="font-bold text-slate-700">{{ $w->nama }}</p>
                        @if ($w->jam_mulai)<p class="text-[11px] text-slate-400 tabular-nums">{{ $w->jam_mulai }}–{{ $w->jam_selesai }}</p>@endif
                    </td>
                    @foreach ($hari as $nomorHari => $h)
                    @php $isi = ($slot[$w->id . '-' . $nomorHari] ?? collect())->sortBy(fn ($j) => $indeksKelas[$j->kelas_id] ?? 0); @endphp
                    <td class="p-2 border-l border-slate-100 transition-colors" data-slot="{{ $nomorHari }}-{{ $w->id }}" data-hari="{{ $nomorHari }}" data-waktu="{{ $w->id }}">
                        {{-- Tinggi minimum 112px; tombol "+" mengisi sisa ruang (sel kosong = kotak penuh) --}}
                        <div class="flex min-h-28 flex-col gap-3 " data-isi-slot>
                            @foreach ($isi as $j){!! $kartuJadwal($j) !!}@endforeach
                            <button type="button" data-tambah data-hari="{{ $nomorHari }}" data-waktu="{{ $w->id }}" data-kelas="{{ $kelasFilter }}"
                                class="w-full flex-1 min-h-7 rounded-xl border border-dashed border-slate-200 text-[11px] text-slate-300 hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-600 transition-colors">
                                {{ $isi->isEmpty() ? 'Kosong' : '+' }}
                            </button>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Bar simpan: muncul setelah ada kartu yang digeser --}}
    <form method="POST" action="{{ route('admin.jadwal.pindah') }}" id="form-pindah"
        class="hidden fixed bottom-5 left-1/2 z-40 -translate-x-1/2 w-[min(640px,calc(100vw-2rem))] items-center gap-3 rounded-2xl bg-[#0b332c] px-4 py-3 text-white shadow-2xl shadow-[#0b332c]/40 ring-1 ring-white/10">
        @csrf
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <div data-isian-pindah></div>
        <span class="w-9 h-9 shrink-0 rounded-xl bg-amber-400 text-[#0b332c] flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold" data-teks-pindah>0 jadwal dipindah</p>
            <p class="text-[11px] text-white/70 truncate" data-teks-bentrok>Belum disimpan</p>
        </div>
        <button type="button" data-batal-pindah class="rounded-xl px-3 py-2 text-sm font-semibold text-white/80 ring-1 ring-white/20 hover:bg-white/10">Batal</button>
        <button data-simpan-pindah class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-[#0b332c] hover:bg-amber-300 disabled:opacity-40 disabled:cursor-not-allowed">Simpan</button>
    </form>
    @endif

    {{-- ===== Tampilan PER KELAS / PER USTADZ ===== --}}
    @if ($tampilan !== 'grid')
    @php
    $kunciGrup = $tampilan === 'kelas' ? 'kelas_id' : 'user_id';
    $kelompok = $jadwal->sortBy(fn ($j) => [$j->hari, $j->waktu?->urutan ?? 0])->groupBy(fn ($j) => $j->{$kunciGrup} ?? 0);
    $judulGrup = fn ($id) => $tampilan === 'kelas' ? ($kelasList->firstWhere('id', $id)?->nama ?? '-') : ($namaUstadz[$id] ?? 'Belum ada ustadz');
    @endphp
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($kelompok as $id => $daftar)
        <section class="{{ $kartu }} p-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-extrabold text-white {{ $tampilan === 'kelas' ? $gayaKelas($id)[2] : ($id ? 'bg-[#0b332c]' : 'bg-slate-300') }}">
                    {{ $tampilan === 'kelas' ? mb_strtoupper(mb_substr($judulGrup($id), 0, 2)) : ($id ? $inisial($judulGrup($id)) : '?') }}
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-slate-800 truncate">{{ $judulGrup($id) }}</h3>
                    <p class="text-xs text-slate-400">{{ $daftar->count() }} jam / minggu</p>
                </div>
            </div>
            <div class="mt-4 space-y-1.5">
                @foreach ($daftar as $j){!! $kartuJadwal($j, true) !!}@endforeach
            </div>
        </section>
        @empty
        <div class="{{ $kartu }} md:col-span-2 xl:col-span-3 py-12 text-center text-sm text-slate-500">Belum ada jadwal di bulan ini.</div>
        @endforelse
    </div>
    @endif
    @endif
</div>

@if ($bulan)
{{-- ===== Dialog tambah / ubah jadwal ===== --}}
<dialog id="dialog-jadwal" class="dialog-akademik">
    <form method="POST" id="form-jadwal" action="{{ route('admin.jadwal.store') }}" class="p-6 space-y-4"
        data-simpan="{{ route('admin.jadwal.store') }}" data-ubah="{{ route('admin.jadwal.update', '__ID__') }}">
        @csrf
        <input type="hidden" name="_method" value="POST" id="metode-jadwal">
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800" id="judul-jadwal">Tambah Jadwal</h2>
            <button type="button" data-tutup class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-xs text-slate-500">Bulan ke-{{ $bulan->urutan }} · {{ $bulan->nama }} · TA {{ $bulan->tahunAjaran?->nama }}</p>
        <div class="grid grid-cols-2 gap-3">
            <label><span class="{{ $label }}">Hari *</span>
                <select name="hari" required class="{{ $input }}">@foreach ($hari as $n => $h)<option value="{{ $n }}">{{ $h }}</option>@endforeach</select></label>
            <label><span class="{{ $label }}">Jam pelajaran *</span>
                <select name="waktu_pelajaran_id" required class="{{ $input }}">@foreach ($waktuList as $w)<option value="{{ $w->id }}">{{ $w->nama }}{{ $w->jam_mulai ? ' (' . $w->jam_mulai . ')' : '' }}</option>@endforeach</select></label>
            <label class="col-span-2"><span class="{{ $label }}">Kelas *</span>
                <select name="kelas_id" required class="{{ $input }}"><option value="">Pilih kelas…</option>@foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach</select></label>
            <label class="col-span-2"><span class="{{ $label }}">Kitab <span class="font-normal text-slate-400">(dari Kurikulum bulan ini)</span></span>
                <select name="kitab_id" class="{{ $input }}"></select></label>
            <label class="col-span-2"><span class="{{ $label }}">Ustadz <span class="font-normal text-slate-400">(akun asatid yang diterima)</span></span>
                <select name="user_id" class="{{ $input }}">
                    <option value="">— Belum ditentukan —</option>
                    @foreach ($ustadzList as $u)<option value="{{ $u->id }}">{{ $u->nama }}</option>@endforeach
                </select>
                @if ($ustadzList->isEmpty())<span class="mt-1 block text-[11px] text-amber-700">Belum ada asatid yang diterima di Manajemen Data › Data Asatid.</span>@endif
            </label>
            <label class="col-span-2"><span class="{{ $label }}">Catatan</span>
                <input name="catatan" maxlength="255" class="{{ $input }}"></label>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-tutup class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
            <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">Simpan</button>
        </div>
    </form>
</dialog>

{{-- ===== Dialog salin jadwal ===== --}}
<dialog id="dialog-salin" class="dialog-akademik">
    <form method="POST" action="{{ route('admin.jadwal.salin') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="ke" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800">Salin Jadwal</h2>
            <button type="button" data-tutup class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-sm text-slate-500">Salin seluruh jadwal dari bulan lain ke <b>{{ $bulan->nama }}</b>.</p>
        @if ($bulanLain->isEmpty())
            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800 ring-1 ring-amber-100">Belum ada bulan lain yang punya jadwal.</p>
        @else
        <label class="block"><span class="{{ $label }}">Salin dari</span>
            <select name="dari" required class="{{ $input }}">
                @foreach ($bulanLain as $b)<option value="{{ $b->id }}">{{ $b->nama }} · TA {{ $b->tahunAjaran?->nama }} ({{ $b->jadwal_count }} jadwal)</option>@endforeach
            </select></label>
        <label class="flex items-start gap-2.5 rounded-xl bg-rose-50 p-3 ring-1 ring-rose-100 cursor-pointer">
            <input type="checkbox" name="timpa" value="1" class="mt-0.5 h-4 w-4 accent-rose-600">
            <span class="text-xs text-rose-900"><b>Hapus dulu jadwal yang sudah ada</b> di {{ $bulan->nama }}. Tanpa centang, slot yang sudah terisi dilewati.</span>
        </label>
        @endif
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-tutup class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
            @if ($bulanLain->isNotEmpty())<button class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-700">Salin</button>@endif
        </div>
    </form>
</dialog>

{{-- ===== Dialog ganti ustadz ===== --}}
<dialog id="dialog-ganti" class="dialog-akademik">
    <form method="POST" action="{{ route('admin.jadwal.ganti-ustadz') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800">Ganti Ustadz</h2>
            <button type="button" data-tutup class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-sm text-slate-500">Semua jadwal ustadz lama di <b>{{ $bulan->nama }}</b> dipindahkan ke ustadz pengganti.</p>
        @php $ustadzBerjadwal = $jadwal->pluck('user_id')->filter()->unique(); @endphp
        <label class="block"><span class="{{ $label }}">Ustadz lama</span>
            <select name="dari" required class="{{ $input }}">
                @forelse ($ustadzList->whereIn('id', $ustadzBerjadwal) as $u)<option value="{{ $u->id }}">{{ $u->nama }} ({{ $jadwal->where('user_id', $u->id)->count() }} jadwal)</option>
                @empty<option value="">Belum ada ustadz yang dijadwalkan</option>@endforelse
            </select></label>
        <label class="block"><span class="{{ $label }}">Ustadz pengganti</span>
            <select name="ke" required class="{{ $input }}">@foreach ($ustadzList as $u)<option value="{{ $u->id }}">{{ $u->nama }}</option>@endforeach</select></label>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" data-tutup class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Batal</button>
            <button class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-600">Ganti</button>
        </div>
    </form>
</dialog>
@endif
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
@include('admin.akademik._dialog-script')
@if ($bulan)
<script>
    (function () {
        const KITAB = @json($dataKitab);
        const form = document.getElementById('form-jadwal');
        const dialog = document.getElementById('dialog-jadwal');
        const pilih = (n) => form.querySelector(`[name="${n}"]`);

        // Pilihan kitab: kitab kurikulum bulan ini (tahun ajaran & bulan ke cocok)
        function isiKitab(terpilih) {
            const el = pilih('kitab_id');
            el.innerHTML = '<option value="">— Tanpa kitab —</option>';
            KITAB.filter((k) => k.cocok).forEach((k) => el.add(new Option(k.nama, k.id)));
            if (terpilih && ![...el.options].some((o) => o.value === String(terpilih))) {
                const k = KITAB.find((x) => x.id === Number(terpilih));
                if (k) el.add(new Option(k.nama + ' (bukan kurikulum bulan ini)', k.id));
            }
            el.value = terpilih || '';
        }

        function buka(data, ubahId) {
            form.reset();
            form.action = ubahId ? form.dataset.ubah.replace('__ID__', ubahId) : form.dataset.simpan;
            document.getElementById('metode-jadwal').value = ubahId ? 'PUT' : 'POST';
            document.getElementById('judul-jadwal').textContent = ubahId ? 'Ubah Jadwal' : 'Tambah Jadwal';
            ['hari', 'waktu_pelajaran_id', 'kelas_id', 'user_id', 'catatan'].forEach((n) => { if (data[n] !== undefined && data[n] !== '') pilih(n).value = data[n]; });
            isiKitab(data.kitab_id);
            dialog.showModal();
        }

        document.addEventListener('click', (e) => {
            const tambah = e.target.closest('[data-tambah]');
            if (tambah) {
                buka({ hari: tambah.dataset.hari, waktu_pelajaran_id: tambah.dataset.waktu, kelas_id: tambah.dataset.kelas });
                return;
            }
            const ubah = e.target.closest('[data-ubah-jadwal]');
            if (ubah) buka(JSON.parse(ubah.dataset.ubahJadwal), ubah.dataset.id);
        });

        // ===== Geser kartu di tampilan Grid (drag & drop), simpan sekaligus lewat bar Simpan =====
        const grid = document.getElementById('grid-jadwal');
        const formPindah = document.getElementById('form-pindah');
        if (grid && formPindah) {
            let diseret = null;
            const pindahan = new Map(); // id jadwal -> "hari-waktu" tujuan
            const kartuSemua = () => grid.querySelectorAll('[data-jadwal]');
            const tandaBentrok = ['ring-2', 'ring-rose-500', 'animate-pulse'];

            kartuSemua().forEach((k) => {
                k.draggable = true;
                k.classList.add('cursor-grab', 'active:cursor-grabbing');
            });

            // Teks tombol "Kosong" / "+" di tiap slot
            const rapikanSlot = (td) => {
                const tombol = td.querySelector('[data-tambah]');
                if (tombol) tombol.textContent = td.querySelector('[data-jadwal]') ? '+' : 'Kosong';
            };

            // Tandai bentrok: kelas yang sama / ustadz yang sama di satu slot
            function cekBentrok() {
                let bentrok = 0;
                kartuSemua().forEach((k) => { k.classList.remove(...tandaBentrok); k.removeAttribute('title'); });
                grid.querySelectorAll('[data-slot]').forEach((td) => {
                    const isi = [...td.querySelectorAll('[data-jadwal]')];
                    isi.forEach((k) => {
                        const kelasSama = isi.filter((x) => x.dataset.kelas === k.dataset.kelas).length > 1;
                        const ustadzSama = k.dataset.ustadz && isi.filter((x) => x.dataset.ustadz === k.dataset.ustadz).length > 1;
                        if (kelasSama || ustadzSama) {
                            k.classList.add(...tandaBentrok);
                            k.title = kelasSama ? 'Bentrok: kelas ini sudah ada pelajaran di jam ini' : 'Bentrok: ustadz ini mengajar dua kelas di jam ini';
                            bentrok++;
                        }
                    });
                });
                return bentrok;
            }

            function perbaruiBar() {
                const bentrok = cekBentrok();
                formPindah.classList.toggle('hidden', pindahan.size === 0);
                formPindah.classList.toggle('flex', pindahan.size > 0);
                formPindah.querySelector('[data-teks-pindah]').textContent = `${pindahan.size} jadwal dipindah`;
                formPindah.querySelector('[data-teks-bentrok]').textContent = bentrok
                    ? `${bentrok} kartu bentrok (bertanda merah). Geser lagi sampai tidak bentrok.`
                    : 'Belum disimpan. Klik Simpan untuk menyimpan perubahan.';
                formPindah.querySelector('[data-simpan-pindah]').disabled = bentrok > 0;
                const wadah = formPindah.querySelector('[data-isian-pindah]');
                wadah.innerHTML = '';
                [...pindahan].forEach(([id, slot], i) => {
                    const [hari, waktu] = slot.split('-');
                    Object.entries({ id, hari, waktu_pelajaran_id: waktu }).forEach(([nama, nilai]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `pindah[${i}][${nama}]`;
                        input.value = nilai;
                        wadah.appendChild(input);
                    });
                });
            }

            grid.addEventListener('dragstart', (e) => {
                diseret = e.target.closest?.('[data-jadwal]');
                if (!diseret) return;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', diseret.dataset.jadwal);
                setTimeout(() => diseret?.classList.add('opacity-40'), 0);
            });
            grid.addEventListener('dragend', () => {
                diseret?.classList.remove('opacity-40');
                diseret = null;
                grid.querySelectorAll('[data-slot]').forEach((td) => td.classList.remove('bg-emerald-50'));
            });
            grid.addEventListener('dragover', (e) => {
                const td = e.target.closest('[data-slot]');
                if (!diseret || !td) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                grid.querySelectorAll('[data-slot]').forEach((x) => x.classList.toggle('bg-emerald-50', x === td));
            });
            grid.addEventListener('drop', (e) => {
                const td = e.target.closest('[data-slot]');
                if (!diseret || !td) return;
                e.preventDefault();
                const asalTd = diseret.closest('[data-slot]');
                if (asalTd === td) return;
                const wadah = td.querySelector('[data-isi-slot]');
                wadah.insertBefore(diseret, wadah.querySelector('[data-tambah]'));
                diseret.classList.add('outline', 'outline-2', 'outline-dashed', 'outline-amber-400');

                // Kembali ke tempat asal = tidak jadi dipindah
                if (td.dataset.slot === diseret.dataset.asal) {
                    pindahan.delete(diseret.dataset.jadwal);
                    diseret.classList.remove('outline', 'outline-2', 'outline-dashed', 'outline-amber-400');
                } else {
                    pindahan.set(diseret.dataset.jadwal, td.dataset.slot);
                }
                rapikanSlot(asalTd);
                rapikanSlot(td);
                perbaruiBar();
            });

            formPindah.querySelector('[data-batal-pindah]').addEventListener('click', () => {
                pindahan.clear();
                location.reload();
            });
            formPindah.addEventListener('submit', () => pindahan.clear());
            // Ingatkan bila meninggalkan halaman sebelum disimpan
            window.addEventListener('beforeunload', (e) => {
                if (pindahan.size) { e.preventDefault(); e.returnValue = ''; }
            });
        }

        // Pencarian: sembunyikan kartu jadwal yang tidak cocok
        document.getElementById('cari-jadwal')?.addEventListener('input', (e) => {
            const kata = e.target.value.trim().toLowerCase();
            document.querySelectorAll('[data-cari]').forEach((el) => {
                el.style.display = !kata || el.dataset.cari.includes(kata) ? '' : 'none';
            });
        });
    })();
</script>
@endif
@endpush
