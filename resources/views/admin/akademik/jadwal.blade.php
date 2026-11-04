{{--
    Admin > Akademik > Jadwal Mengajar
    Tampilan: grid (jam × hari), per kelas, per ustadz.

    Seluruh halaman adalah aplikasi Vue #halaman-jadwal (skrip di bawah):
      - data jadwal dikirim dari server lewat @json, lalu grid & daftar digambar Vue (v-for)
      - kartu jadwal = komponen <kartu-jadwal> (templatenya di <template id="tpl-kartu-jadwal">)
      - geser kartu (drag & drop) hanya mengubah hari/jam di data; bar "Simpan" mengirim perubahan
      - satu <dialog> form untuk tambah & ubah jadwal (v-model)
--}}
@extends('layouts.admin')

@section('title', __('Jadwal Mengajar'))

@section('content')
@php
use App\Models\Absensi;
use App\Models\JadwalMengajar;
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'mt-1 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none';
$label = 'block text-xs font-semibold text-slate-600';
$hari = JadwalMengajar::HARI;
$tautan = fn (array $ubah) => route('admin.jadwal.index', array_filter(array_merge(['bulan' => $bulan?->id, 'kelas' => $kelasFilter, 'tampilan' => $tampilan], $ubah)));
$inisial = fn ($nama) => collect(preg_split('/\s+/', trim(preg_replace('/^(Ustadz|Ust\.?|Syekh|KH\.?)\s+/i', '', (string) $nama))))->filter()->take(3)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');

// ----- Data untuk Vue -----
$indeksKelas = $kelasList->pluck('id')->flip();
$dataJadwal = $jadwal->map(fn ($j) => [
    'id' => $j->id,
    'hari' => (int) $j->hari,
    'waktu' => (int) $j->waktu_pelajaran_id,
    'kelas_id' => $j->kelas_id,
    'kitab_id' => $j->kitab_id,
    'user_id' => $j->user_id,
    'catatan' => $j->catatan,
    'kelas' => $j->kelas?->nama,
    'kitab' => $j->kitab?->nama,
    'ustadz' => $namaUstadz[$j->user_id] ?? null,
    'ustadz_arab' => $arabUstadz[$j->user_id] ?? null, // nama Arab ustadz (tampil di bawah nama Indonesia)
    'inisial' => $inisial($namaUstadz[$j->user_id] ?? ''),
    'warna' => ($indeksKelas[$j->kelas_id] ?? 0) % 6, // indeks warna kelas
])->values();
$dataWaktu = $waktuList->map(fn ($w) => ['id' => $w->id, 'nama' => $w->nama, 'urutan' => $w->urutan, 'jam' => $w->jam_mulai ? $w->jam_mulai . '–' . $w->jam_selesai : null])->values();
$dataKelas = $kelasList->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama])->values();
// Pilihan kitab di form: kitab kurikulum bulan ini (tahun ajaran & bulan ke cocok) ditandai "cocok"
$dataKitab = $kitabList->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama, 'cocok' => $kitabBulanIni->contains($k->id)])->values();
@endphp

<div id="halaman-jadwal">
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20">
    @include('admin.akademik._flash')

    @if (! $bulan)
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <p class="text-lg font-bold text-slate-700">{{ __('Belum ada bulan ajaran') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Jadwal mengajar dibuat per bulan ajaran. Buat tahun ajaran dan bulannya dulu.') }}</p>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="mt-4 inline-flex rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">{{ __('Buka Tahun & Bulan Ajaran') }}</a>
    </div>
    @else

    {{-- ===== Judul & tombol ===== --}}
    <x-header-admin :label="__('Akademik')" :judul="__('Jadwal Mengajar')"
        :keterangan="__('Bulan ke-:urutan', ['urutan' => $bulan->urutan]) . ' · ' . $bulan->nama . ' · ' . __('TA') . ' ' . $bulan->tahunAjaran?->nama">
        <x-slot:aksi>
                <button type="button" @click="bukaDialog('dialog-salin')" class="inline-flex items-center gap-2 rounded-2xl bg-sky-400 px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-sky-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/></svg>
                    {{ __('Salin Jadwal') }}
                </button>
                <button type="button" @click="bukaDialog('dialog-ganti')" class="inline-flex items-center gap-2 rounded-2xl bg-amber-300 px-4 py-2.5 text-sm font-bold text-[#3b2a00] hover:bg-amber-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    {{ __('Ganti Ustadz') }}
                </button>
                <button type="button" @click="bukaTambah()" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-[#0b332c] hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    {{ __('Tambah Jadwal') }}
                </button>
        </x-slot:aksi>
    </x-header-admin>

    {{-- ===== Periode ===== --}}
    <section class="{{ $kartu }} p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="flex items-center gap-2 font-bold text-slate-800">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                {{ __('Periode') }}
            </h2>
            <a href="{{ route('admin.tahun-ajaran.index') }}" class="rounded-xl bg-[#0b332c] px-3 py-2 text-xs font-bold text-white hover:bg-[#135347]">{{ __('Kelola Tahun & Bulan') }}</a>
        </div>
        <form method="GET" class="mt-4 grid sm:grid-cols-2 gap-3">
            <input type="hidden" name="tampilan" value="{{ $tampilan }}">
            <label><span class="{{ $label }}">{{ __('Tahun ajaran') }}</span>
                {{-- Ganti tahun -> buka bulan pertama tahun itu (alamatnya ada di value) --}}
                <select class="{{ $input }}" @change="pindahHalaman">
                    @foreach ($tahunList as $t)
                    <option value="{{ $t->bulan->first() ? route('admin.jadwal.index', ['bulan' => $t->bulan->first()->id, 'tampilan' => $tampilan]) : '' }}" @selected($bulan->tahun_ajaran_id === $t->id)>{{ $t->nama }}{{ $t->is_active ? ' ✓ ' . __('aktif') : '' }}{{ $t->bulan->isEmpty() ? ' ' . __('(belum ada bulan)') : '' }}</option>
                    @endforeach
                </select></label>
            <label><span class="{{ $label }}">{{ __('Bulan ajaran') }}</span>
                <select name="bulan" @change="kirimForm" class="{{ $input }}">
                    @foreach ($tahunList->firstWhere('id', $bulan->tahun_ajaran_id)?->bulan ?? [] as $b)
                    <option value="{{ $b->id }}" @selected($b->id === $bulan->id)>{{ __('Bulan ke-:urutan', ['urutan' => $b->urutan]) }} · {{ $b->nama }}{{ $b->is_active ? ' ✓ ' . __('aktif') : '' }}</option>
                    @endforeach
                </select></label>
        </form>
        <div class="mt-3 rounded-2xl bg-[#f8fbfa] px-4 py-3 text-sm">
            <p>{{ __('Status:') }} <b class="{{ $bulan->is_active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $bulan->is_active ? __('Bulan aktif') : __('Tidak aktif') }}</b>
                @unless ($bulan->is_active)
                <span class="text-xs text-slate-400">· {{ __('jadwal yang tampil di dasbor asatid adalah jadwal bulan aktif') }}</span>
                @endunless
            </p>
            @if ($bulan->tanggal_mulai)
            <p class="text-slate-600">{{ __('Periode:') }} {{ $bulan->periode }} <span class="text-emerald-700">({{ Absensi::hijriyah($bulan->tanggal_mulai->toDateString()) }} – {{ Absensi::hijriyah($bulan->tanggal_selesai->toDateString()) }})</span></p>
            @endif
        </div>
    </section>

    {{-- ===== Cari, kelas, tampilan ===== --}}
    <section class="{{ $kartu }} p-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <label class="flex-1 flex items-center gap-2 rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 px-3 focus-within:ring-2 focus-within:ring-emerald-500">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="search" v-model.trim="cari" placeholder="{{ __('Cari jadwal (kitab, ustadz, kelas)…') }}" class="flex-1 bg-transparent border-0 py-2.5 text-sm outline-none focus:ring-0">
        </label>
        <label class="flex items-center gap-2 text-sm text-slate-600">{{ __('Kelas:') }}
            <select @change="pindahHalaman" class="rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-sm">
                <option value="{{ $tautan(['kelas' => null]) }}">{{ __('Semua Kelas') }}</option>
                @foreach ($kelasList as $k)<option value="{{ $tautan(['kelas' => $k->id]) }}" @selected((string) $kelasFilter === (string) $k->id)>{{ $k->nama }}</option>@endforeach
            </select></label>
        <div class="flex gap-1.5">
            @foreach (['grid' => __('Grid'), 'kelas' => __('Per Kelas'), 'ustadz' => __('Per Ustadz')] as $kode => $nama)
            <a href="{{ $tautan(['tampilan' => $kode]) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors {{ $tampilan === $kode ? 'bg-[#0b332c] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $nama }}</a>
            @endforeach
        </div>
    </section>

    @if ($waktuList->isEmpty())
    <div class="{{ $kartu }} p-6 text-center text-sm text-slate-500">{{ __('Belum ada jam pelajaran. Tambahkan di') }} <a href="{{ route('admin.master-data.index', ['tab' => 'waktu-pelajaran']) }}" class="font-semibold text-emerald-700 hover:underline">{{ __('Master Data › Waktu Pelajaran') }}</a>.</div>
    @endif

    {{-- ===== Tampilan GRID (digambar Vue dari data) ===== --}}
    @if ($tampilan === 'grid' && $waktuList->isNotEmpty())
    <p class="mb-5 flex items-center gap-2 px-1 text-xs text-slate-500">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/></svg>
        <span>{{ __('Geser kartu ke hari/jam lain untuk memindahkan jadwal, lalu klik') }} <b>{{ __('Simpan') }}</b>. {{ __('Klik ikon pensil untuk mengubah isi jadwal.') }}</span>
    </p>
    <section class="{{ $kartu }} overflow-x-auto">
        {{-- table-fixed: semua kolom hari sama lebar, sel berbentuk agak kotak --}}
        <table class="w-full min-w-[1040px] table-fixed border-collapse text-sm">
            <thead>
                <tr class="bg-[#f8fbfa]">
                    <th class="w-32 px-3 py-3 text-left font-bold text-slate-700">{{ __('Waktu') }}</th>
                    <th v-for="(namaHari, nomor) in hari" :key="nomor" class="px-2 py-3 font-bold text-slate-700" v-text="namaHari"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="w in waktu" :key="w.id" class="border-t border-slate-100 align-top">
                    <td class="px-3 py-3">
                        <p class="font-bold text-slate-700" v-text="w.nama"></p>
                        <p v-if="w.jam" class="text-[11px] text-slate-400 tabular-nums" v-text="w.jam"></p>
                    </td>
                    {{-- Satu sel = satu slot (hari + jam). Kartu bisa dijatuhkan di sini. --}}
                    <td v-for="(namaHari, nomor) in hari" :key="nomor"
                        class="p-2 border-l border-slate-100 transition-colors" :class="{ 'bg-emerald-50': sorot === nomor + '-' + w.id }"
                        @dragover.prevent="sorot = nomor + '-' + w.id" @dragleave="sorot = null" @drop.prevent="lepas(Number(nomor), w.id)">
                        {{-- Tinggi minimum 112px; tombol "+" mengisi sisa ruang (sel kosong = kotak penuh) --}}
                        <div class="flex min-h-28 flex-col gap-3">
                            <kartu-jadwal v-for="j in isiSlot(Number(nomor), w.id)" :key="j.id" :j="j"
                                :bentrok="pesanBentrok[j.id]" :dipindah="sudahDipindah(j)"
                                @ubah="bukaUbah" @seret="seret = j" @selesai-seret="seret = null; sorot = null"></kartu-jadwal>
                            <button type="button" @click="bukaTambah(Number(nomor), w.id)"
                                class="w-full flex-1 min-h-7 rounded-xl border border-dashed border-slate-200 text-[11px] text-slate-300 hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-600 transition-colors"
                                v-text="isiSlot(Number(nomor), w.id).length ? '+' : @js(__('Kosong'))"></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    {{-- Bar simpan: muncul setelah ada kartu yang digeser --}}
    <form v-show="pindahan.length" style="display: none" method="POST" action="{{ route('admin.jadwal.pindah') }}"
        @submit="adaPerubahan = false"
        class="fixed bottom-5 left-1/2 z-40 -translate-x-1/2 w-[min(640px,calc(100vw-2rem))] flex items-center gap-3 rounded-2xl bg-[#0b332c] px-4 py-3 text-white shadow-2xl shadow-[#0b332c]/40 ring-1 ring-white/10">
        @csrf
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <template v-for="(j, i) in pindahan" :key="j.id">
            <input type="hidden" :name="'pindah[' + i + '][id]'" :value="j.id">
            <input type="hidden" :name="'pindah[' + i + '][hari]'" :value="j.hari">
            <input type="hidden" :name="'pindah[' + i + '][waktu_pelajaran_id]'" :value="j.waktu">
        </template>
        <span class="w-9 h-9 shrink-0 rounded-xl bg-amber-400 text-[#0b332c] flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold" v-text="pindahan.length + ' ' + @js(__('jadwal dipindah'))"></p>
            <p class="text-[11px] text-white/70 truncate"
                v-text="jumlahBentrok ? jumlahBentrok + ' ' + @js(__('kartu bentrok (bertanda merah). Geser lagi sampai tidak bentrok.')) : @js(__('Belum disimpan. Klik Simpan untuk menyimpan perubahan.'))"></p>
        </div>
        <button type="button" @click="batalPindah" class="rounded-xl px-3 py-2 text-sm font-semibold text-white/80 ring-1 ring-white/20 hover:bg-white/10">{{ __('Batal') }}</button>
        <button :disabled="jumlahBentrok > 0" class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-[#0b332c] hover:bg-amber-300 disabled:opacity-40 disabled:cursor-not-allowed">{{ __('Simpan') }}</button>
    </form>
    @endif

    {{-- ===== Tampilan PER KELAS / PER USTADZ (digambar Vue dari data) ===== --}}
    @if ($tampilan !== 'grid')
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <section v-for="g in kelompok" :key="g.kunci" class="{{ $kartu }} p-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-extrabold text-white" :class="g.latar" v-text="g.singkat"></span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-slate-800 truncate" v-text="g.judul"></h3>
                    <p v-if="g.judulArab" dir="rtl" lang="ar" class="text-sm text-slate-500 truncate" v-text="g.judulArab"></p>
                    <p class="text-xs text-slate-400" v-text="g.daftar.length + ' ' + @js(__('jam / minggu'))"></p>
                </div>
            </div>
            <div class="mt-4 space-y-1.5">
                <kartu-jadwal v-for="j in g.daftar" :key="j.id" :j="j" lengkap @ubah="bukaUbah"></kartu-jadwal>
            </div>
        </section>
        <div v-if="!kelompok.length" class="{{ $kartu }} md:col-span-2 xl:col-span-3 py-12 text-center text-sm text-slate-500"
            v-text="jadwal.length ? @js(__('Tidak ada jadwal yang cocok dengan pencarian.')) : @js(__('Belum ada jadwal di bulan ini.'))"></div>
    </div>
    @endif
    @endif
</div>

@if ($bulan)
{{-- ===== Dialog tambah / ubah jadwal (isian memakai v-model ke data "form") ===== --}}
<dialog id="dialog-jadwal" ref="dialog-jadwal" class="dialog-akademik" @click.self="tutupDialog">
    <form method="POST" :action="form.id ? alamatUbah.replace('__ID__', form.id) : '{{ route('admin.jadwal.store') }}'" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="_method" :value="form.id ? 'PUT' : 'POST'">
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800" v-text="form.id ? @js(__('Ubah Jadwal')) : @js(__('Tambah Jadwal'))"></h2>
            <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-xs text-slate-500">{{ __('Bulan ke-:urutan', ['urutan' => $bulan->urutan]) }} · {{ $bulan->nama }} · {{ __('TA') }} {{ $bulan->tahunAjaran?->nama }}</p>
        <div class="grid grid-cols-2 gap-3">
            <label><span class="{{ $label }}">{{ __('Hari *') }}</span>
                <select name="hari" v-model="form.hari" required class="{{ $input }}">@foreach ($hari as $n => $h)<option value="{{ $n }}">{{ __($h) }}</option>@endforeach</select></label>
            <label><span class="{{ $label }}">{{ __('Jam pelajaran *') }}</span>
                <select name="waktu_pelajaran_id" v-model="form.waktu_pelajaran_id" required class="{{ $input }}">@foreach ($waktuList as $w)<option value="{{ $w->id }}">{{ $w->nama }}{{ $w->jam_mulai ? ' (' . $w->jam_mulai . ')' : '' }}</option>@endforeach</select></label>
            <label class="col-span-2"><span class="{{ $label }}">{{ __('Kelas *') }}</span>
                <select name="kelas_id" v-model="form.kelas_id" required class="{{ $input }}"><option value="">{{ __('Pilih kelas…') }}</option>@foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach</select></label>
            <label class="col-span-2"><span class="{{ $label }}">{{ __('Kitab') }} <span class="font-normal text-slate-400">{{ __('(dari Kurikulum bulan ini)') }}</span></span>
                <select name="kitab_id" v-model="form.kitab_id" class="{{ $input }}">
                    <option value="">{{ __('— Tanpa kitab —') }}</option>
                    <option v-for="k in pilihanKitab" :key="k.id" :value="String(k.id)" v-text="k.cocok ? k.nama : k.nama + ' ' + @js(__('(bukan kurikulum bulan ini)'))"></option>
                </select></label>
            <label class="col-span-2"><span class="{{ $label }}">{{ __('Ustadz') }} <span class="font-normal text-slate-400">{{ __('(akun asatid yang diterima)') }}</span></span>
                <select name="user_id" v-model="form.user_id" class="{{ $input }}">
                    <option value="">{{ __('— Belum ditentukan —') }}</option>
                    @foreach ($ustadzList as $u)<option value="{{ $u->id }}">{{ $u->nama }}{{ $u->arab ? ' — ' . $u->arab : '' }}</option>@endforeach
                </select>
                @if ($ustadzList->isEmpty())<span class="mt-1 block text-[11px] text-amber-700">{{ __('Belum ada asatid yang diterima di Manajemen Data › Data Asatid.') }}</span>@endif
            </label>
            <label class="col-span-2"><span class="{{ $label }}">{{ __('Catatan') }}</span>
                <input name="catatan" v-model="form.catatan" maxlength="255" class="{{ $input }}"></label>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button class="rounded-xl bg-[#0b332c] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#135347]">{{ __('Simpan') }}</button>
        </div>
    </form>
</dialog>

{{-- ===== Dialog salin jadwal ===== --}}
<dialog id="dialog-salin" ref="dialog-salin" class="dialog-akademik" @click.self="tutupDialog">
    <form method="POST" action="{{ route('admin.jadwal.salin') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="ke" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800">{{ __('Salin Jadwal') }}</h2>
            <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-sm text-slate-500">{{ __('Salin seluruh jadwal dari bulan lain ke') }} <b>{{ $bulan->nama }}</b>.</p>
        @if ($bulanLain->isEmpty())
            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800 ring-1 ring-amber-100">{{ __('Belum ada bulan lain yang punya jadwal.') }}</p>
        @else
        <label class="block"><span class="{{ $label }}">{{ __('Salin dari') }}</span>
            <select name="dari" required class="{{ $input }}">
                @foreach ($bulanLain as $b)<option value="{{ $b->id }}">{{ $b->nama }} · {{ __('TA') }} {{ $b->tahunAjaran?->nama }} ({{ __(':jumlah jadwal', ['jumlah' => $b->jadwal_count]) }})</option>@endforeach
            </select></label>
        <label class="flex items-start gap-2.5 rounded-xl bg-rose-50 p-3 ring-1 ring-rose-100 cursor-pointer">
            <input type="checkbox" name="timpa" value="1" class="mt-0.5 h-4 w-4 accent-rose-600">
            <span class="text-xs text-rose-900"><b>{{ __('Hapus dulu jadwal yang sudah ada') }}</b> {{ __('di :bulan.', ['bulan' => $bulan->nama]) }} {{ __('Tanpa centang, slot yang sudah terisi dilewati.') }}</span>
        </label>
        @endif
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
            @if ($bulanLain->isNotEmpty())<button class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-700">{{ __('Salin') }}</button>@endif
        </div>
    </form>
</dialog>

{{-- ===== Dialog ganti ustadz ===== --}}
<dialog id="dialog-ganti" ref="dialog-ganti" class="dialog-akademik" @click.self="tutupDialog">
    <form method="POST" action="{{ route('admin.jadwal.ganti-ustadz') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="bulan_ajaran_id" value="{{ $bulan->id }}">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-800">{{ __('Ganti Ustadz') }}</h2>
            <button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <p class="text-sm text-slate-500">{{ __('Semua jadwal ustadz lama di') }} <b>{{ $bulan->nama }}</b> {{ __('dipindahkan ke ustadz pengganti.') }}</p>
        @php $ustadzBerjadwal = $jadwal->pluck('user_id')->filter()->unique(); @endphp
        <label class="block"><span class="{{ $label }}">{{ __('Ustadz lama') }}</span>
            <select name="dari" required class="{{ $input }}">
                @forelse ($ustadzList->whereIn('id', $ustadzBerjadwal) as $u)<option value="{{ $u->id }}">{{ $u->nama }}{{ $u->arab ? ' — ' . $u->arab : '' }} ({{ __(':jumlah jadwal', ['jumlah' => $jadwal->where('user_id', $u->id)->count()]) }})</option>
                @empty<option value="">{{ __('Belum ada ustadz yang dijadwalkan') }}</option>@endforelse
            </select></label>
        <label class="block"><span class="{{ $label }}">{{ __('Ustadz pengganti') }}</span>
            <select name="ke" required class="{{ $input }}">@foreach ($ustadzList as $u)<option value="{{ $u->id }}">{{ $u->nama }}{{ $u->arab ? ' — ' . $u->arab : '' }}</option>@endforeach</select></label>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-600">{{ __('Ganti') }}</button>
        </div>
    </form>
</dialog>
@endif
</div>

{{-- ===== Template komponen <kartu-jadwal> (dibaca Vue, tidak tampil sendiri) =====
     props: j (data jadwal), lengkap (tampilkan hari & jam), bentrok (pesan bentrok), dipindah (sudah digeser) --}}
<template id="tpl-kartu-jadwal">
    <div class="group relative rounded-xl px-2.5 py-2 ring-1 transition hover:shadow-md cursor-grab active:cursor-grabbing"
        :class="[warna[0], { 'ring-2 ring-rose-500 animate-pulse': bentrok, 'outline outline-2 outline-dashed outline-amber-400': dipindah }]"
        :title="bentrok || ''" :draggable="!lengkap" @dragstart="$emit('seret')" @dragend="$emit('selesai-seret')">
        <div class="absolute right-1 top-1 flex gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
            <button type="button" title="{{ __('Ubah jadwal') }}" @click="$emit('ubah', j)"
                class="rounded-md bg-white/90 p-1 text-slate-500 ring-1 ring-slate-200 hover:text-sky-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            </button>
            <form method="POST" :action="alamatHapus" @submit="konfirmasi" data-konfirmasi="{{ __('Hapus jadwal ini?') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                <button title="{{ __('Hapus') }}" class="rounded-md bg-white/90 p-1 text-slate-500 ring-1 ring-slate-200 hover:text-rose-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </form>
        </div>
        <p v-if="lengkap" class="text-[10px] font-bold uppercase tracking-wider text-slate-400" v-text="namaHari + ' · ' + namaWaktu"></p>
        <p class="pr-10 text-sm font-bold leading-tight mb-2" :class="warna[1]" v-text="j.kitab || @js(__('Tanpa kitab'))"></p>
        <p class="mt-0.5 text-sm leading-tight" :class="[j.ustadz ? 'text-slate-600' : 'text-amber-600', j.ustadz_arab ? '' : 'mb-1']" v-text="j.ustadz || @js(__('Ustadz belum ditentukan'))"></p>
        <p v-if="j.ustadz_arab" dir="rtl" lang="ar" class="text-[15px] leading-snug text-slate-600 mb-1" v-text="j.ustadz_arab"></p>
        <div class="mt-1 flex items-center justify-between gap-1">
            <span class="truncate text-[11px] font-semibold text-slate-700" v-text="j.kelas"></span>
            <span v-if="j.inisial" class="shrink-0 text-[9px] font-bold text-slate-400" v-text="j.inisial"></span>
        </div>
        <p v-if="j.catatan" class="mt-0.5 text-[10px] italic text-slate-500" v-text="j.catatan"></p>
    </div>
</template>
@endsection

@push('styles')
@include('admin.akademik._dialog-style')
@endpush

@push('scripts')
@if ($bulan)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const HARI = @json(array_map(fn ($h) => __($h), $hari));         // { 1: 'Senin', ... 7: 'Ahad' }
        const WAKTU = @json($dataWaktu);   // jam pelajaran
        const KELAS = @json($dataKelas);
        const KITAB = @json($dataKitab);
        const ALAMAT_HAPUS = @json(route('admin.jadwal.destroy', '__ID__'));
        // Warna kartu per kelas: [latar kartu, warna teks judul, warna lingkaran]
        const WARNA = [
            ['bg-sky-50 ring-sky-200', 'text-sky-800', 'bg-sky-500'],
            ['bg-emerald-50 ring-emerald-200', 'text-emerald-800', 'bg-emerald-500'],
            ['bg-violet-50 ring-violet-200', 'text-violet-800', 'bg-violet-500'],
            ['bg-amber-50 ring-amber-200', 'text-amber-800', 'bg-amber-500'],
            ['bg-rose-50 ring-rose-200', 'text-rose-800', 'bg-rose-500'],
            ['bg-teal-50 ring-teal-200', 'text-teal-800', 'bg-teal-500'],
        ];
        const kosongkanForm = () => ({ id: null, hari: '1', waktu_pelajaran_id: String(WAKTU[0]?.id ?? ''), kelas_id: '', kitab_id: '', user_id: '', catatan: '' });

        const app = Vue.createApp({
            data() {
                return {
                    hari: HARI,
                    waktu: WAKTU,
                    // Setiap jadwal menyimpan posisi awalnya (asalHari/asalWaktu) untuk tahu mana yang digeser
                    jadwal: @json($dataJadwal).map((j) => ({ ...j, asalHari: j.hari, asalWaktu: j.waktu })),
                    tampilan: @json($tampilan),
                    kelasFilter: @json($kelasFilter),
                    cari: '',
                    seret: null,        // kartu yang sedang digeser
                    sorot: null,        // slot "hari-jam" yang sedang dilewati kartu
                    adaPerubahan: true, // false saat form Simpan dikirim (agar tidak ditanya "tinggalkan halaman?")
                    form: kosongkanForm(),
                    alamatUbah: @json(route('admin.jadwal.update', '__ID__')),
                };
            },
            computed: {
                // Jadwal yang cocok dengan kata pencarian
                jadwalTampil() {
                    const kata = this.cari.toLowerCase();
                    if (!kata) return this.jadwal;
                    return this.jadwal.filter((j) =>
                        [j.kitab, j.ustadz, j.ustadz_arab, j.kelas, j.catatan, HARI[j.hari], WAKTU.find((w) => w.id === j.waktu)?.nama]
                            .join(' ').toLowerCase().includes(kata));
                },
                // Jadwal yang sudah digeser dari posisi awalnya
                pindahan() {
                    return this.jadwal.filter((j) => this.sudahDipindah(j));
                },
                // { idJadwal: 'pesan bentrok' } -> kelas atau ustadz yang sama di satu slot
                pesanBentrok() {
                    const pesan = {};
                    const perSlot = {};
                    this.jadwal.forEach((j) => (perSlot[j.hari + '-' + j.waktu] ??= []).push(j));
                    Object.values(perSlot).forEach((isi) => isi.forEach((j) => {
                        if (isi.filter((x) => x.kelas_id === j.kelas_id).length > 1) pesan[j.id] = @js(__('Bentrok: kelas ini sudah ada pelajaran di jam ini'));
                        else if (j.user_id && isi.filter((x) => x.user_id === j.user_id).length > 1) pesan[j.id] = @js(__('Bentrok: ustadz ini mengajar dua kelas di jam ini'));
                    }));
                    return pesan;
                },
                jumlahBentrok() {
                    return Object.keys(this.pesanBentrok).length;
                },
                // Tampilan per kelas / per ustadz
                kelompok() {
                    const perKelas = this.tampilan === 'kelas';
                    const grup = {};
                    const urutWaktu = (id) => WAKTU.find((w) => w.id === id)?.urutan ?? 0;
                    [...this.jadwalTampil]
                        .sort((a, b) => a.hari - b.hari || urutWaktu(a.waktu) - urutWaktu(b.waktu))
                        .forEach((j) => (grup[perKelas ? j.kelas_id : (j.user_id ?? 0)] ??= []).push(j));
                    return Object.entries(grup).map(([kunci, daftar]) => {
                        const judul = perKelas ? (KELAS.find((k) => String(k.id) === kunci)?.nama ?? '-') : (daftar[0].ustadz ?? @js(__('Belum ada ustadz')));
                        return {
                            kunci, daftar, judul,
                            judulArab: perKelas ? null : (daftar[0].ustadz_arab ?? null), // per ustadz: nama Arab di bawah nama
                            singkat: perKelas ? judul.slice(0, 2).toUpperCase() : (kunci !== '0' ? daftar[0].inisial : '?'),
                            latar: perKelas ? WARNA[daftar[0].warna][2] : (kunci !== '0' ? 'bg-[#0b332c]' : 'bg-slate-300'),
                        };
                    });
                },
                // Pilihan kitab: kitab kurikulum bulan ini + kitab yang sedang dipakai jadwal ini
                pilihanKitab() {
                    return KITAB.filter((k) => k.cocok || String(k.id) === String(this.form.kitab_id));
                },
            },
            methods: {
                ...metodeHalaman, // bukaDialog, tutupDialog, kirimForm, pindahHalaman, konfirmasi
                // Kartu di satu slot (hari + jam), diurutkan sesuai urutan kelas
                isiSlot(hari, waktu) {
                    return this.jadwalTampil.filter((j) => j.hari === hari && j.waktu === waktu)
                        .sort((a, b) => a.warna - b.warna || a.kelas_id - b.kelas_id);
                },
                sudahDipindah(j) {
                    return j.hari !== j.asalHari || j.waktu !== j.asalWaktu;
                },
                // Kartu dijatuhkan di slot baru: cukup ubah datanya, Vue menggambar ulang grid
                lepas(hari, waktu) {
                    if (this.seret) {
                        this.seret.hari = hari;
                        this.seret.waktu = waktu;
                    }
                    this.seret = null;
                    this.sorot = null;
                },
                batalPindah() {
                    this.jadwal.forEach((j) => { j.hari = j.asalHari; j.waktu = j.asalWaktu; });
                },
                bukaTambah(hari, waktu) {
                    this.form = { ...kosongkanForm(), ...(hari ? { hari: String(hari), waktu_pelajaran_id: String(waktu) } : {}), kelas_id: this.kelasFilter ? String(this.kelasFilter) : '' };
                    this.bukaDialog('dialog-jadwal');
                },
                bukaUbah(j) {
                    this.form = {
                        id: j.id, hari: String(j.asalHari), waktu_pelajaran_id: String(j.asalWaktu), kelas_id: String(j.kelas_id),
                        kitab_id: j.kitab_id ? String(j.kitab_id) : '', user_id: j.user_id ? String(j.user_id) : '', catatan: j.catatan ?? '',
                    };
                    this.bukaDialog('dialog-jadwal');
                },
                // Ingatkan bila meninggalkan halaman sebelum perpindahan disimpan
                ingatkanPerubahan(e) {
                    if (this.pindahan.length && this.adaPerubahan) { e.preventDefault(); e.returnValue = ''; }
                },
            },
            mounted() {
                window.addEventListener('beforeunload', this.ingatkanPerubahan);
            },
            unmounted() {
                window.removeEventListener('beforeunload', this.ingatkanPerubahan);
            },
        });

        // Komponen kartu jadwal (template: <template id="tpl-kartu-jadwal"> di atas)
        app.component('kartu-jadwal', {
            template: '#tpl-kartu-jadwal',
            props: { j: Object, lengkap: Boolean, bentrok: String, dipindah: Boolean },
            emits: ['ubah', 'seret', 'selesai-seret'],
            computed: {
                warna() { return WARNA[this.j.warna] ?? WARNA[0]; },
                namaHari() { return HARI[this.j.hari]; },
                namaWaktu() { return WAKTU.find((w) => w.id === this.j.waktu)?.nama ?? ''; },
                alamatHapus() { return ALAMAT_HAPUS.replace('__ID__', this.j.id); },
            },
            methods: { konfirmasi: metodeHalaman.konfirmasi },
        });

        app.mount('#halaman-jadwal');
    });
</script>
@else
<script>
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({ methods: { ...metodeHalaman } }).mount('#halaman-jadwal'));
</script>
@endif
@endpush
