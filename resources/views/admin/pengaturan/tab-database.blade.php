{{--
Pengaturan > Database: Memori server, backup, restore, hapus data (sesi aktif).
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
@php
$kotak = 'rounded-2xl ring-1 ring-slate-200 p-5';
$kecil = 'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 transition-colors';
@endphp
<h2 class="text-lg font-bold text-slate-800">{{ __('Database') }}</h2>
<p class="text-sm text-slate-500">{{ __('Backup, restore, dan hapus data untuk sesi yang sedang aktif.') }}</p>

<div class="mt-4 flex items-center gap-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 px-4 py-3 text-sm">
    <span class="w-2.5 h-2.5 shrink-0 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
    <p class="min-w-0 text-emerald-900">{{ __('Sesi aktif:') }} <b>{{ $sesiAktif?->nama ?? '-' }}</b> <span class="text-emerald-700/80">· {{ __('database') }} <span class="font-mono">{{ $db['nama'] }}</span></span></p>
    <a href="{{ route('admin.pengaturan.index', ['tab' => 'sesi']) }}" class="ml-auto shrink-0 text-xs font-semibold text-emerald-700 hover:underline">{{ __('Ganti sesi') }}</a>
</div>

<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
    @foreach ([
        [__('Database'), $db['nama']],
        [__('Jenis'), strtoupper($db['driver']) . ' ' . \Illuminate\Support\Str::before((string) $db['versi'], '-')],
        [__('Jumlah tabel'), number_format($db['tabel']->count(), 0, ',', '.')],
        [__('Ukuran'), $ukuran($db['ukuran'])],
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
$meterList = [$meter(__('Penyimpanan (disk) server'), $server['disk'], 'bg-emerald-500'), $meter(__('Memori (RAM) server'), $server['ram'], 'bg-sky-500')];
@endphp
<section class="mt-5 rounded-2xl ring-1 ring-slate-200 p-5">
    <p class="font-bold text-slate-800">{{ __('Memori Server') }}</p>
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
                <div><dt class="text-slate-400">{{ __('Terpakai') }}</dt><dd class="font-bold text-slate-700 tabular-nums">{{ $m['terpakai'] }}</dd></div>
                <div><dt class="text-slate-400">{{ __('Sisa') }}</dt><dd class="font-bold text-emerald-700 tabular-nums">{{ $m['sisa'] }}</dd></div>
                <div><dt class="text-slate-400">{{ __('Total') }}</dt><dd class="font-bold text-slate-700 tabular-nums">{{ $m['total'] }}</dd></div>
            </dl>
            @else
            <p class="mt-2 text-xs text-slate-400">{{ __('Tidak tersedia di komputer ini (terbaca di server Linux).') }}</p>
            @endif
        </div>
        @endforeach
    </div>
    <p class="mt-4 text-xs font-semibold text-slate-600">{{ __('Dipakai aplikasi ini') }}</p>
    <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        @foreach ([__('Database (semua sesi)') => $server['aplikasi']['database'], __('File upload') => $server['aplikasi']['file'], __('Backup') => $server['aplikasi']['backup'], __('Log') => $server['aplikasi']['log']] as $judulPakai => $nilaiPakai)
        <div class="rounded-xl ring-1 ring-slate-100 px-3 py-2"><p class="text-slate-400">{{ $judulPakai }}</p><p class="font-bold text-slate-700 tabular-nums">{{ $ukuran($nilaiPakai) }}</p></div>
        @endforeach
    </div>
</section>

@if (! $db['bisa_backup'])
<p class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('Backup, restore, dan hapus data hanya tersedia untuk database MySQL/MariaDB.') }}</p>
@else
{{-- ----- Backup ----- --}}
<section class="mt-5 {{ $kotak }}">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <span class="w-11 h-11 shrink-0 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800">{{ __('Backup Data') }}</p>
            <p class="text-xs text-slate-500">{{ __('Salinan seluruh data sesi ini (:jumlah baris) dalam file .sql. File berisi data pribadi, simpan di tempat aman. Berkas upload (foto, dokumen) tidak ikut.', ['jumlah' => number_format($db['baris'], 0, ',', '.')]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <form method="POST" action="{{ route('admin.pengaturan.backup.simpan') }}">@csrf
                <button class="{{ $kecil }} text-slate-700 ring-slate-200 hover:bg-slate-50 py-2.5">{{ __('Simpan di server') }}</button>
            </form>
            <a href="{{ route('admin.pengaturan.backup') }}" class="{{ $tombol }} justify-center">{{ __('Download Backup') }}</a>
        </div>
    </div>

    <div class="mt-4 rounded-xl ring-1 ring-slate-100 overflow-hidden">
        <p class="px-4 py-2.5 bg-[#f8fbfa] text-xs font-bold text-slate-600">{{ __('Riwayat backup di server') }} <span class="font-normal text-slate-400">{{ __('(dibuat otomatis sebelum restore & hapus data)') }}</span></p>
        @forelse ($cadangan as $c)
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 px-4 py-2.5 border-t border-slate-100 text-sm">
            <span class="min-w-0 flex-1 font-mono text-[12px] text-slate-700 truncate" title="{{ $c['nama'] }}">{{ $c['nama'] }}</span>
            <span class="text-xs text-slate-400 tabular-nums">{{ $ukuran($c['ukuran']) }} · {{ $c['waktu']->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
            <a href="{{ route('admin.pengaturan.backup.unduh', $c['nama']) }}" class="{{ $kecil }} text-sky-700 ring-sky-200 hover:bg-sky-50">{{ __('Unduh') }}</a>
            <button type="button" @click="pilihPulihkan('{{ $c['nama'] }}')" class="{{ $kecil }} text-amber-700 ring-amber-200 hover:bg-amber-50">{{ __('Restore') }}</button>
            <form method="POST" action="{{ route('admin.pengaturan.backup.hapus', $c['nama']) }}" @submit="konfirmasi" data-konfirmasi="{{ __('Hapus file backup ini?') }}">@csrf @method('DELETE')
                <button class="{{ $kecil }} text-rose-600 ring-rose-200 hover:bg-rose-50">{{ __('Hapus') }}</button>
            </form>
        </div>
        @empty
        <p class="px-4 py-4 border-t border-slate-100 text-xs text-slate-400">{{ __('Belum ada backup tersimpan di server.') }}</p>
        @endforelse
    </div>
</section>

{{-- ----- Restore ----- --}}
<form method="POST" action="{{ route('admin.pengaturan.restore') }}" enctype="multipart/form-data" class="mt-5 {{ $kotak }} ring-amber-200 bg-amber-50/40" ref="formRestore">
    @csrf
    <div class="flex items-start gap-4">
        <span class="w-11 h-11 shrink-0 rounded-2xl bg-amber-400 text-[#0b332c] flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
        </span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800">{{ __('Restore Data') }}</p>
            <p class="text-xs text-slate-600">{{ __('Mengganti SELURUH data sesi') }} <b>{{ $sesiAktif?->nama }}</b> {{ __('dengan isi file backup. Data sekarang otomatis di-backup dulu. Akun admin & asatid disamakan kembali setelahnya.') }}</p>
        </div>
    </div>
    <div class="mt-4 grid sm:grid-cols-2 gap-4">
        <div>
            <span class="{{ $label }}">{{ __('Sumber backup') }}</span>
            <div class="mt-1.5 flex gap-2 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="sumber" value="upload" v-model="sumberRestore" class="text-amber-500"> {{ __('Upload file .sql') }}</label>
                <label class="flex items-center gap-2"><input type="radio" name="sumber" value="server" v-model="sumberRestore" class="text-amber-500" @disabled(! $cadangan)> {{ __('Dari server') }}</label>
            </div>
            <input type="file" name="file" accept=".sql" v-show="sumberRestore === 'upload'" :disabled="sumberRestore !== 'upload'" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 file:ring-1 file:ring-slate-200">
            <select name="nama" v-model="namaCadangan" v-show="sumberRestore === 'server'" :disabled="sumberRestore !== 'server'" class="{{ $input }}">
                @foreach ($cadangan as $c)<option value="{{ $c['nama'] }}">{{ $c['nama'] }}</option>@endforeach
            </select>
        </div>
        <label class="block"><span class="{{ $label }}">{{ __('Password Anda') }}</span>
            <input type="password" name="password_konfirmasi" ref="passwordRestore" required autocomplete="current-password" class="{{ $input }}"></label>
    </div>
    <label class="mt-4 flex items-start gap-2 text-sm text-slate-700">
        <input type="checkbox" name="yakin" value="1" required class="mt-0.5 rounded text-amber-500">
        {{ __('Saya mengerti seluruh data sesi ini akan diganti dengan isi file backup.') }}
    </label>
    <div class="mt-4 flex justify-end">
        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600" @click="konfirmasi" data-konfirmasi="{{ __('Yakin restore? Semua data sesi ini akan diganti.') }}">{{ __('Restore Sekarang') }}</button>
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
            <p class="font-bold text-slate-800">{{ __('Hapus Data') }}</p>
            <p class="text-xs text-slate-600">{{ __('Pilih kelompok data yang dihapus dari sesi') }} <b>{{ $sesiAktif?->nama }}</b>. {{ __('Backup otomatis dibuat dulu. Akun admin & asatid tidak ikut terhapus.') }}</p>
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
        <label class="block"><span class="{{ $label }}">{{ __('Ketik') }} <span class="font-mono text-rose-600">HAPUS</span> {{ __('untuk konfirmasi') }}</span>
            <input name="ketik" required autocomplete="off" class="{{ $input }}"></label>
        <label class="block"><span class="{{ $label }}">{{ __('Password Anda') }}</span>
            <input type="password" name="password_konfirmasi" required autocomplete="current-password" class="{{ $input }}"></label>
    </div>
    <div class="mt-4 flex justify-end">
        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700" @click="konfirmasi" data-konfirmasi="{{ __('Yakin menghapus data yang dipilih?') }}">{{ __('Hapus Data Terpilih') }}</button>
    </div>
</form>
@endif

<details class="mt-5 rounded-2xl ring-1 ring-slate-200 overflow-hidden">
    <summary class="cursor-pointer px-5 py-3 bg-[#f8fbfa] text-sm font-bold text-slate-700">{{ __('Isi tabel (:jumlah)', ['jumlah' => $db['tabel']->count()]) }}</summary>
    <div class="max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white text-xs text-slate-400">
                <tr><th class="px-5 py-2 text-left font-semibold">{{ __('Tabel') }}</th><th class="px-5 py-2 text-right font-semibold">{{ __('Jumlah data') }}</th><th class="px-5 py-2 text-right font-semibold">{{ __('Ukuran') }}</th></tr>
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


@push('scripts')
<script>
    // Bagian Vue tab Database: pilihan sumber restore + tombol "Restore" di riwayat backup
    window.tabPengaturan = {
        data() {
            return {
                sumberRestore: @json(old('sumber', 'upload')), // 'upload' | 'server'
                namaCadangan: @json(old('nama', $cadangan[0]['nama'] ?? '')),
            };
        },
        methods: {
            // Tombol "Restore" di riwayat: isi form restore lalu arahkan ke kolom password
            pilihPulihkan(nama) {
                this.sumberRestore = 'server';
                this.namaCadangan = nama;
                this.$refs.formRestore.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.$refs.passwordRestore.focus({ preventScroll: true });
            },
        },
    };
</script>
@endpush
