{{-- Konten saja. Menggunakan layout admin yang sudah ada (sidebar/topbar). --}}
@extends('layouts.admin')

@section('content')
<div id="halaman-data-masuk">
    <div class="space-y-5 mx-4 md:mx-10 lg:mx-20 mt-8">
        <x-header-admin :label="__('PSB')"
            :judul="($statusTetap ?? null) === 'diterima' ? __('Data Terkonfirmasi Peserta') : __('Data Masuk Peserta')"
            :keterangan="__('Verifikasi pendaftar baru, lalu terima atau tolak.')" />

        {{-- ===== Status pendaftaran: Aktif (formulir dibuka) / Nonaktif (ditutup) ===== --}}
        <div class="rounded-2xl ring-1 p-4 sm:p-5 flex flex-wrap items-center gap-4 transition-colors"
            :class="psbDibuka ? 'bg-emerald-50 ring-emerald-200' : 'bg-rose-50 ring-rose-200'">
            <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center text-white"
                :class="psbDibuka ? 'bg-emerald-500' : 'bg-rose-500'">
                <svg v-if="psbDibuka" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <div class="flex-1 min-w-[200px]">
                <p class="font-bold" :class="psbDibuka ? 'text-emerald-800' : 'text-rose-800'" v-text="psbDibuka ? @js(__('Pendaftaran AKTIF')) : @js(__('Pendaftaran NONAKTIF'))"></p>
                <p class="text-sm" :class="psbDibuka ? 'text-emerald-700' : 'text-rose-700'"
                    v-text="psbDibuka ? @js(__('Formulir pendaftaran dibuka, peserta baru bisa mendaftar.')) : @js(__('Formulir pendaftaran ditutup, tidak ada pendaftar baru yang bisa masuk.'))"></p>
            </div>
            <a href="{{ route('psb.daftar') }}" target="_blank" rel="noopener" class="text-sm font-semibold underline underline-offset-2"
                :class="psbDibuka ? 'text-emerald-700' : 'text-rose-700'">{{ __('Lihat formulir') }}</a>
            <button type="button" @click="ubahPsb" :disabled="psbSibuk" role="switch" :aria-checked="psbDibuka ? 'true' : 'false'"
                :title="psbDibuka ? @js(__('Klik untuk menonaktifkan pendaftaran')) : @js(__('Klik untuk mengaktifkan pendaftaran'))"
                class="inline-flex items-center gap-3 rounded-full pl-1.5 pr-4 py-1.5 text-sm font-bold text-white shadow-sm disabled:opacity-60 transition-colors"
                :class="psbDibuka ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                <span class="relative inline-flex h-6 w-11 rounded-full bg-white/30">
                    <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="psbDibuka ? 'translate-x-5' : ''"></span>
                </span>
                <span v-text="psbDibuka ? @js(__('Aktif')) : @js(__('Nonaktif'))"></span>
            </button>
            <p v-if="psbPesan" class="basis-full text-xs font-semibold" :class="psbDibuka ? 'text-emerald-700' : 'text-rose-700'" v-text="psbPesan"></p>
        </div>

        {{-- Kartu statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ([[__('Total'), $stat['total'], 'slate', ''], [__('Menunggu'), $stat['menunggu'], 'amber', 'menunggu'], [__('Diterima'), $stat['diterima'], 'emerald', 'diterima'], [__('Ditolak'), $stat['ditolak'], 'red', 'ditolak']] as [$label, $jml, $warna, $filter])
                <a href="{{ route('admin.pendaftaran.index', array_filter(['status' => $filter])) }}"
                    class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-sm">
                    <p class="text-xs text-slate-500">{{ $label }}</p>
                    <p class="text-2xl font-bold text-{{ $warna }}-600">{{ $jml }}</p>
                </a>
            @endforeach
        </div>

        {{-- Pencarian --}}
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="{{ __('Cari nama / nomor / wali...') }}"
                class="flex-1 min-w-[200px] border border-slate-300 rounded-lg px-3 py-2 text-sm">
            @unless ($statusTetap ?? null)
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Semua status') }}</option>
                    @foreach (['menunggu', 'diterima', 'ditolak'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ __(ucfirst($s)) }}</option>
                    @endforeach
                </select>
            @endunless
            <button class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm">{{ __('Cari') }}</button>
        </form>

        {{-- ===== HP (di bawah lg): satu pendaftar = satu kartu ===== --}}
        <div class="lg:hidden space-y-2.5">
            @forelse ($pendaftar as $p)
            <article class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 shadow-[0_1px_2px_rgba(16,24,40,0.04)]">
                <a href="{{ route('admin.pendaftaran.show', $p) }}" class="flex items-center gap-3">
                    @if ($p->foto_url)
                    <img src="{{ $p->foto_url }}" alt="" class="w-11 h-11 shrink-0 rounded-xl object-cover">
                    @else
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-teal-50 text-teal-700 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($p->nama_lengkap, 0, 1)) }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block font-bold text-slate-800 truncate">{{ $p->nama_lengkap }}</span>
                        <x-nama-arab :teks="$p->nama_lengkap_arab" />
                    </span>
                    <span @class([
                        'shrink-0 px-2.5 py-1 rounded-full text-[11px] font-semibold',
                        'bg-amber-100 text-amber-700' => $p->status === 'menunggu',
                        'bg-emerald-100 text-emerald-700' => $p->status === 'diterima',
                        'bg-red-100 text-red-700' => $p->status === 'ditolak',
                    ])>{{ __($p->label_status) }}</span>
                </a>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1" dir="auto">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        {{ $p->kota ?: '-' }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        {{ $p->created_at->translatedFormat('d M Y') }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.pendaftaran.show', $p) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-50 py-2 text-sm font-semibold text-teal-700 active:scale-95 transition">{{ __('Lihat detail') }}</a>
                    <form method="POST" action="{{ route('admin.pendaftaran.destroy', $p) }}"
                        @submit="konfirmasi" data-konfirmasi="{{ __('Hapus data :nama?', ['nama' => $p->nama_lengkap]) . "\n" . __('Berkas unggahan dan akun login siswanya juga ikut terhapus.') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-50 py-2 text-sm font-semibold text-rose-700 active:scale-95 transition">{{ __('Hapus') }}</button>
                    </form>
                </div>
            </article>
            @empty
            <p class="rounded-2xl bg-white py-10 text-center text-sm text-slate-400 ring-1 ring-slate-200/80">{{ __('Belum ada pendaftar.') }}</p>
            @endforelse
        </div>

        {{-- ===== Laptop (lg ke atas): tabel ===== --}}
        <div class="hidden lg:block bg-white rounded-2xl border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-3">{{ __('No. Pendaftaran') }}</th>
                        <th class="px-4 py-3">{{ __('Nama') }}</th>
                        <th class="px-4 py-3">{{ __('Kota') }}</th>
                        <th class="px-4 py-3">{{ __('Tanggal') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pendaftar as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $p->nomor_pendaftaran }}</td>
                            <td class="px-4 py-3">{{ $p->nama_lengkap }}<x-nama-arab :teks="$p->nama_lengkap_arab" /></td>
                            {{-- Kota asal saja --}}
                            <td class="px-4 py-3" dir="auto">{{ $p->kota ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    'bg-amber-100 text-amber-700' => $p->status === 'menunggu',
                                    'bg-emerald-100 text-emerald-700' => $p->status === 'diterima',
                                    'bg-red-100 text-red-700' => $p->status === 'ditolak',
                                ])>{{ __($p->label_status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Ikon mata: buka detail siswa --}}
                                    <a href="{{ route('admin.pendaftaran.show', $p) }}" target="_blank"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-teal-700 transition hover:bg-teal-50"
                                        title="{{ __('Lihat detail siswa') }}" aria-label="{{ __('Lihat detail :nama', ['nama' => $p->nama_lengkap]) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>

                                    {{-- Ikon tempat sampah: hapus (minta konfirmasi dulu) --}}
                                    <form method="POST" action="{{ route('admin.pendaftaran.destroy', $p) }}"
                                        @submit="konfirmasi" data-konfirmasi="{{ __('Hapus data :nama?', ['nama' => $p->nama_lengkap]) . "\n" . __('Berkas unggahan dan akun login siswanya juga ikut terhapus.') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50"
                                            title="{{ __('Hapus') }}" aria-label="{{ __('Hapus :nama', ['nama' => $p->nama_lengkap]) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">{{ __('Belum ada pendaftar.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pendaftar->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Aplikasi Vue halaman ini: konfirmasi hapus pendaftar (metode dari partials/vue-metode.blade.php)
    // + tombol Aktif/Nonaktif pendaftaran
    document.addEventListener('DOMContentLoaded', () => Vue.createApp({
        data() {
            return { psbDibuka: @json($psbDibuka), psbSibuk: false, psbPesan: '' };
        },
        methods: {
            ...metodeHalaman,
            async ubahPsb() {
                const baru = !this.psbDibuka;
                if (!baru && !confirm(@js(__('Nonaktifkan pendaftaran? Formulir akan ditutup dan tidak ada pendaftar baru yang bisa masuk.')))) return;
                this.psbSibuk = true;
                try {
                    const res = await fetch(@json(route('admin.pendaftaran.buka-tutup')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        body: JSON.stringify({ dibuka: baru }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || @js(__('Gagal menyimpan.')));
                    this.psbDibuka = data.dibuka;
                    this.psbPesan = data.pesan;
                } catch (e) {
                    this.psbPesan = e.message || @js(__('Gagal menyimpan. Coba lagi.'));
                } finally {
                    this.psbSibuk = false;
                }
            },
        },
    }).mount('#halaman-data-masuk'));
</script>
@endpush
