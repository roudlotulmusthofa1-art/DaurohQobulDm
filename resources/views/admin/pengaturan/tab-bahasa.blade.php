{{--
Pengaturan > Bahasa: terjemahan Arab buatan admin, dikelompokkan per halaman.
- Daftar tulisan diambil otomatis dari semua __('...') di file tampilan (App\Support\TeksTampilan).
  Mengubah pengelompokan halaman: App\Support\TeksTampilan::HALAMAN.
- Disimpan di tabel "terjemahan" (database utama, semua sesi). Kolom Arab kosong = tampil bahasa Indonesia.
- Semua kolom Arab diisi admin sendiri (lang/ar.json tidak dipakai).
- Tombol geser "usulan" per baris: hidup = kolom diisi usulan dari lang/usulan-ar.json (terkunci),
  mati = kembali ke tulisan admin sendiri. Yang disimpan tetap isi kolom saat tombol Simpan ditekan.
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
@php
    $jumlahBaris = $barisBahasa->count();
    $adaUsulan = $barisBahasa->contains(fn ($b) => $b['usulan'] !== '');
@endphp

<h2 class="text-lg font-bold text-slate-800">Bahasa</h2>
<p class="text-sm text-slate-500">Terjemahan Arab untuk setiap tulisan, per halaman. Tampil saat tombol bahasa diganti ke العربية. Kolom yang dibiarkan kosong tetap tampil dalam bahasa Indonesia.</p>

{{-- Pilih halaman + cari --}}
<div class="mt-5 flex flex-col sm:flex-row gap-3">
    <form method="GET" action="{{ route('admin.pengaturan.index') }}" class="flex-1 min-w-0">
        <input type="hidden" name="tab" value="bahasa">
        <label for="pilih-halaman" class="{{ $label }}">Halaman</label>
        <select id="pilih-halaman" name="halaman" class="{{ $input }}" @change="$event.target.form.submit()">
            @foreach ($halamanList as $kode => $h)
            <option value="{{ $kode }}" @selected($kode === $halamanKode)>{{ $h['nama'] }} ({{ count($h['teks']) }} tulisan)</option>
            @endforeach
        </select>
    </form>
    @if ($jumlahBaris)
    <div class="sm:w-64">
        <label for="cari-teks" class="{{ $label }}">Cari tulisan</label>
        <input id="cari-teks" type="search" v-model="cari" placeholder="Ketik kata Indonesia / Arab…" class="{{ $input }}">
    </div>
    @endif
</div>

@if (! $jumlahBaris)
    {{-- Halaman ini belum punya tulisan yang ditandai __('...') --}}
    <div class="mt-5 rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-200 p-6 text-center">
        <p class="text-sm font-semibold text-slate-700">Belum ada tulisan yang bisa diterjemahkan di halaman ini.</p>
        <p class="mt-1 text-xs text-slate-500" v-pre>Tulisan baru muncul di sini setelah ditandai di file tampilannya, contoh: <code class="px-1 rounded bg-white ring-1 ring-slate-200">&#123;&#123; &#95;&#95;('Data Santri') &#125;&#125;</code></p>
    </div>
@else
<form method="POST" action="{{ route('admin.pengaturan.bahasa') }}" class="mt-5 rounded-2xl ring-1 ring-slate-200 overflow-hidden" @input="formDiubah = true">
    @csrf @method('PUT')
    <input type="hidden" name="halaman" value="{{ $halamanKode }}">

    {{-- Judul kolom (layar lebar) --}}
    <div class="hidden md:grid grid-cols-2 gap-4 px-5 py-3 bg-[#f5f9f7] border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
        <span>Bahasa Indonesia</span>
        <span class="flex items-center justify-between gap-3">
            {{ __('Bahasa Arab') }}
            @if ($adaUsulan)
            <span class="flex items-center gap-2 normal-case tracking-normal font-semibold">
                {{ __('Pakai semua usulan') }}
                <button type="button" role="switch" @click="gantiSemua" :aria-checked="semuaUsulan ? 'true' : 'false'"
                    class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="semuaUsulan ? 'bg-emerald-600' : 'bg-slate-300'">
                    <span class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform" :class="semuaUsulan ? 'translate-x-4' : ''"></span>
                </button>
            </span>
            @endif
        </span>
    </div>

    <div class="divide-y divide-slate-100">
        @foreach ($barisBahasa as $i => $b)
        <div class="grid md:grid-cols-2 gap-2 md:gap-4 px-5 py-3.5" v-show="cocok({{ $i }})">
            <div class="min-w-0" v-pre>
                <p class="text-sm font-medium text-slate-800 break-words">{{ $b['teks'] }}</p>
                @if (preg_match_all('/:[a-z_]+/i', $b['teks'], $isian))
                {{-- Contoh "Selamat Datang, :nama" -> :nama diganti otomatis (nama orang, angka, dll.) --}}
                <p class="mt-1 text-[11px] text-amber-700">Tulis juga {{ implode(', ', array_unique($isian[0])) }} di terjemahan, persis seperti ini (diganti otomatis).</p>
                @endif
                <p class="mt-1 text-[11px] text-slate-400 break-all">{{ implode(' · ', $b['file']) }}</p>
                <input type="hidden" name="teks[{{ $i }}]" value="{{ $b['teks'] }}">
            </div>
            <div class="flex items-start gap-3">
                @if ($b['usulan'] !== '')
                {{-- Tombol geser: hidup = pakai usulan terjemahan, mati = tulisan sendiri --}}
                <div class="flex flex-col items-center gap-0.5 pt-1.5">
                    <button type="button" role="switch" @click="gantiUsulan({{ $i }})" :aria-checked="pakai[{{ $i }}] ? 'true' : 'false'"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="pakai[{{ $i }}] ? 'bg-emerald-600' : 'bg-slate-300'">
                        <span class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform" :class="pakai[{{ $i }}] ? 'translate-x-4' : ''"></span>
                    </button>
                    <span class="text-[10px] text-slate-400">{{ __('usulan') }}</span>
                </div>
                @endif
                <textarea name="arab[{{ $i }}]" rows="1" dir="rtl" lang="ar" v-model="isi[{{ $i }}]" :readonly="pakai[{{ $i }}]"
                    placeholder="اكتب الترجمة هنا…"
                    class="w-full rounded-xl ring-1 ring-slate-200 border-0 px-3.5 py-2 text-[15px] focus:ring-2 focus:ring-emerald-500 outline-none resize-y"
                    :class="pakai[{{ $i }}] ? 'bg-emerald-50 text-emerald-800' : 'bg-[#f8fbfa] text-slate-800'"></textarea>
            </div>
        </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-[#f5f9f7] border-t border-slate-100">
        <p class="text-xs text-slate-500">Sudah diterjemahkan: <b class="text-slate-700">{{ $jumlahTerjemah }}</b> dari {{ $jumlahBaris }} tulisan.</p>
        <button type="submit" class="{{ $tombol }} disabled:opacity-40 disabled:cursor-not-allowed" :disabled="!formDiubah">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Simpan
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Bagian Vue tab Bahasa: pencarian tulisan (Indonesia atau Arab) + tombol "usulan" per baris
    window.tabPengaturan = {
        data() {
            const usulan = @json($barisBahasa->pluck('usulan'));
            const isi = @json($barisBahasa->map(fn ($b, $i) => (string) old('arab.' . $i, $b['arab'])));
            // Tombol tampil hidup bila yang tersimpan sama persis dengan usulan
            const pakai = isi.map((t, i) => usulan[i] !== '' && t === usulan[i]);
            return {
                cari: '',
                teksIndonesia: @json($barisBahasa->pluck('teks')->map(fn ($t) => mb_strtolower($t))),
                usulan,
                isi,
                pakai,
                // Tulisan sendiri tiap baris, dikembalikan saat tombol usulan dimatikan
                sendiri: isi.map((t, i) => pakai[i] ? '' : t),
            };
        },
        computed: {
            // Tombol "Pakai semua usulan" hidup bila semua baris yang punya usulan sedang memakainya
            semuaUsulan() {
                return this.usulan.every((u, i) => u === '' || this.pakai[i]);
            },
        },
        methods: {
            cocok(i) {
                const kata = this.cari.trim().toLowerCase();
                if (!kata) return true;
                return this.teksIndonesia[i].includes(kata) || (this.isi[i] || '').toLowerCase().includes(kata);
            },
            aturUsulan(i, hidup) {
                if (this.usulan[i] === '' || this.pakai[i] === hidup) return;
                if (hidup) this.sendiri[i] = this.isi[i];
                this.isi[i] = hidup ? this.usulan[i] : this.sendiri[i];
                this.pakai[i] = hidup;
                this.formDiubah = true;
            },
            gantiUsulan(i) {
                this.aturUsulan(i, !this.pakai[i]);
            },
            gantiSemua() {
                const hidup = !this.semuaUsulan;
                this.usulan.forEach((u, i) => this.aturUsulan(i, hidup));
            },
        },
    };
</script>
@endpush
@endif
