{{--
Pengaturan > Sesi: Sesi dauroh: buat, aktifkan, ubah, hapus (database & folder terpisah).
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
<div class="flex flex-col sm:flex-row sm:items-start gap-3">
    <div class="flex-1">
        <h2 class="text-lg font-bold text-slate-800">Sesi Dauroh</h2>
        <p class="text-sm text-slate-500">Tiap sesi (angkatan/gelombang) punya database dan folder file sendiri. Semua menu menampilkan data <b>sesi aktif</b>. Akun admin & asatid dipakai bersama semua sesi.</p>
    </div>
    <button type="button" @click="bukaDialog('dialog-sesi-baru')" class="{{ $tombol }} shrink-0 self-start">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Sesi Baru
    </button>
</div>

<div class="mt-5 grid xl:grid-cols-2 gap-4">
    @foreach ($sesiList as $s)
    @php $r = $ringkasSesi[$s->id]; @endphp
    <article class="relative rounded-2xl p-5 ring-1 {{ $s->is_active ? 'ring-2 ring-emerald-500 bg-emerald-50/40' : 'ring-slate-200 bg-white' }}">
        <div class="flex items-start gap-3">
            <span class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center text-sm font-extrabold {{ $s->is_active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }}">{{ mb_strtoupper(mb_substr($s->nama, 0, 2)) }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <h3 class="font-bold text-slate-800 truncate">{{ $s->nama }}</h3>
                    @if ($s->is_active)<span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">AKTIF</span>@endif
                    @if ($s->utama)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">UTAMA</span>@endif
                </div>
                @if ($s->periode)<p class="text-xs text-slate-500">{{ $s->periode }}</p>@endif
                @if ($s->keterangan)<p class="mt-1 text-xs text-slate-500">{{ $s->keterangan }}</p>@endif
            </div>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2"><dt class="text-slate-400">Peserta</dt><dd class="font-bold text-slate-700">{{ $r['ada'] ? number_format($r['peserta'], 0, ',', '.') : 'Database hilang' }}</dd></div>
            <div class="rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2"><dt class="text-slate-400">File</dt><dd class="font-bold text-slate-700">{{ $s->utama ? 'Folder utama' : $ukuran($r['ukuran_file']) }}</dd></div>
            <div class="col-span-2 rounded-xl bg-white/80 ring-1 ring-slate-100 px-3 py-2 min-w-0"><dt class="text-slate-400">Database · folder</dt><dd class="font-mono text-[11px] text-slate-600 truncate">{{ $s->nama_database }} · storage/{{ $s->folder ?: '(utama)' }}</dd></div>
        </dl>
        <div class="mt-4 flex flex-wrap gap-2">
            @unless ($s->is_active)
            <form method="POST" action="{{ route('admin.pengaturan.sesi.aktifkan', $s) }}" @submit="konfirmasi" data-konfirmasi="Jadikan {{ $s->nama }} sesi aktif? Semua menu akan menampilkan data sesi ini, dan peserta yang sedang login akan dikeluarkan.">@csrf
                <button class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Aktifkan</button>
            </form>
            @endunless
            <button type="button" @click="bukaDialog('dialog-sesi-{{ $s->id }}')" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Ubah</button>
            @unless ($s->utama || $s->is_active)
            <button type="button" @click="bukaDialog('dialog-hapus-sesi-{{ $s->id }}')" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">Hapus</button>
            @endunless
        </div>
    </article>

    {{-- Ubah sesi --}}
    <dialog id="dialog-sesi-{{ $s->id }}" ref="dialog-sesi-{{ $s->id }}" class="dialog-pengaturan" @click.self="tutupDialog">
        <form method="POST" action="{{ route('admin.pengaturan.sesi.ubah', $s) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-slate-800">Ubah Sesi</h3><button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
            <label class="block"><span class="{{ $label }}">Nama sesi *</span><input name="nama" required maxlength="150" value="{{ $s->nama }}" class="{{ $input }}"></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block"><span class="{{ $label }}">Tanggal mulai</span><input type="date" name="tanggal_mulai" value="{{ $s->tanggal_mulai?->toDateString() }}" class="{{ $input }}"></label>
                <label class="block"><span class="{{ $label }}">Tanggal selesai</span><input type="date" name="tanggal_selesai" value="{{ $s->tanggal_selesai?->toDateString() }}" class="{{ $input }}"></label>
            </div>
            <label class="block"><span class="{{ $label }}">Keterangan</span><textarea name="keterangan" rows="2" maxlength="1000" class="{{ $input }}">{{ $s->keterangan }}</textarea></label>
            <div class="flex justify-end gap-2"><button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="{{ $tombol }}">Simpan</button></div>
        </form>
    </dialog>

    {{-- Hapus sesi --}}
    @unless ($s->utama || $s->is_active)
    <dialog id="dialog-hapus-sesi-{{ $s->id }}" ref="dialog-hapus-sesi-{{ $s->id }}" class="dialog-pengaturan" @click.self="tutupDialog">
        <form method="POST" action="{{ route('admin.pengaturan.sesi.hapus', $s) }}" class="p-6 space-y-4">
            @csrf @method('DELETE')
            <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-rose-700">Hapus Sesi</h3><button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
            <p class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800">Database <span class="font-mono">{{ $s->database }}</span>, semua file, dan riwayat backup sesi <b>{{ $s->nama }}</b> akan dihapus permanen. Unduh backup dulu bila masih diperlukan.</p>
            <label class="block"><span class="{{ $label }}">Ketik nama sesi: <span class="font-mono text-rose-600">{{ $s->nama }}</span></span><input name="ketik" required autocomplete="off" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">Password Anda</span><input type="password" name="password_konfirmasi" required autocomplete="current-password" class="{{ $input }}"></label>
            <div class="flex justify-end gap-2"><button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Hapus Permanen</button></div>
        </form>
    </dialog>
    @endunless
    @endforeach
</div>

{{-- Sesi baru --}}
<dialog id="dialog-sesi-baru" ref="dialog-sesi-baru" class="dialog-pengaturan" @click.self="tutupDialog">
    <form method="POST" action="{{ route('admin.pengaturan.sesi.simpan') }}" class="p-6 space-y-4" @submit="sedangProses = true">
        @csrf
        <div class="flex items-center justify-between"><h3 class="text-lg font-extrabold text-slate-800">Sesi Baru</h3><button type="button" @click="tutupDialog" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button></div>
        <p class="text-xs text-slate-500">Database dan folder file baru dibuat otomatis. Akun admin & asatid langsung bisa dipakai di sesi ini.</p>
        <label class="block"><span class="{{ $label }}">Nama sesi *</span><input name="nama" required maxlength="150" value="{{ old('nama') }}" placeholder="cth. Dauroh 1448 Gelombang 1" class="{{ $input }}"></label>
        <div class="grid grid-cols-2 gap-3">
            <label class="block"><span class="{{ $label }}">Tanggal mulai</span><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="{{ $input }}"></label>
            <label class="block"><span class="{{ $label }}">Tanggal selesai</span><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="{{ $input }}"></label>
        </div>
        <label class="block"><span class="{{ $label }}">Keterangan</span><textarea name="keterangan" rows="2" maxlength="1000" class="{{ $input }}">{{ old('keterangan') }}</textarea></label>
        <div class="rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 p-3 space-y-2">
            <label class="block"><span class="{{ $label }}">Salin data awal dari</span>
                <select name="salin_dari" class="{{ $input }}">
                    <option value="">— Mulai kosong —</option>
                    @foreach ($sesiList as $s)<option value="{{ $s->id }}" @selected($s->is_active)>{{ $s->nama }}</option>@endforeach
                </select></label>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="salin_master" value="1" checked class="rounded text-emerald-600"> Master data (kelas, kamar, waktu pelajaran, dll.)</label>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="salin_pengaturan" value="1" checked class="rounded text-emerald-600"> Pengaturan (profil pesantren, notifikasi)</label>
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="aktifkan" value="1" class="rounded text-emerald-600"> Langsung jadikan sesi aktif</label>
        <div class="flex justify-end gap-2"><button type="button" @click="tutupDialog" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200">Batal</button><button class="{{ $tombol }}" :disabled="sedangProses" v-text="sedangProses ? 'Memproses… (bisa ±1 menit)' : 'Buat Sesi'"></button></div>
    </form>
</dialog>

@push('styles')
<style>
    /* Pop-up di tengah layar (Tailwind mereset margin bawaan <dialog>) */
    .dialog-pengaturan { margin: auto; inset: 0; max-height: calc(100vh - 2rem); width: min(520px, calc(100vw - 2rem)); border-radius: 1.5rem; padding: 0; border: 0; box-shadow: 0 30px 80px -20px rgba(11, 51, 44, .45); }
    .dialog-pengaturan::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(3px); }
</style>
@endpush

@push('scripts')
<script>
    // Bagian Vue tab Sesi: tombol "Buat Sesi" menampilkan "Memproses…" + buka lagi dialog bila gagal validasi
    window.tabPengaturan = {
        data() {
            return { sedangProses: false };
        },
        mounted() {
            @if ($errors->any() && old('nama') !== null && ! request()->old('_method'))
            this.bukaDialog('dialog-sesi-baru');
            @endif
        },
    };
</script>
@endpush
