{{--
    Isian formulir berita (dipakai Admin > Berita dan menu Tulis Berita asatid/siswa).
    Diletakkan di dalam aplikasi Vue yang memakai mixin window.MIXIN_TULIS_BERITA (berita/partials/skrip-tulis).
    Variabel: $admin (bool) -> tampilkan isian "Tampil sampai"
--}}
@php
$isian = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500';
$label = 'block text-xs font-semibold text-slate-600 mb-1.5';
@endphp
<div class="space-y-4">
    <div>
        <label class="{{ $label }}">{{ __('Judul berita') }} <span class="text-rose-500">*</span></label>
        <input v-model="form.judul" type="text" maxlength="200" required class="{{ $isian }}" placeholder="{{ __('cth. Kegiatan Belajar mengajar') }}">
    </div>
    <div>
        <label class="{{ $label }}">{{ __('Ringkasan') }} <span class="font-normal text-slate-400">{{ __('(opsional, tampil di bawah judul)') }}</span></label>
        <textarea v-model="form.ringkasan" rows="2" maxlength="300" class="{{ $isian }}" placeholder="{{ __('Satu-dua kalimat inti berita') }}"></textarea>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">{{ __('Kategori') }}</label>
            <select v-model="form.kategori" class="{{ $isian }}">
                <option value="">{{ __('— Tanpa kategori —') }}</option>
                <option v-for="k in kategoriPilihan" :key="k" :value="k" v-text="k"></option>
                <option value="__lain">{{ __('Lainnya…') }}</option>
            </select>
        </div>
        {{-- Pilihan "Lainnya": kategori ditulis sendiri --}}
        <div v-if="form.kategori === '__lain'">
            <label class="{{ $label }}">{{ __('Tulis kategori') }} <span class="text-rose-500">*</span></label>
            <input v-model="form.kategori_lain" type="text" maxlength="80" required class="{{ $isian }}" placeholder="{{ __('cth. Silaturahmi') }}">
        </div>
    </div>

    {{-- Lokasi: otomatis dari HP (seperti lokasi di status WA) atau cari nama tempat; peta kecil sebagai pratinjau --}}
    <div>
        <label class="{{ $label }}">{{ __('Lokasi') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></label>
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" @click="lokasiSaya" :disabled="mencariLokasi"
                class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-sm font-bold hover:bg-emerald-100 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <span v-text="mencariLokasi ? @js(__('Mencari lokasi…')) : @js(__('Pakai lokasi saya sekarang'))"></span>
            </button>
            <div class="relative flex-1">
                <input v-model="cariTempat" @input="cariLokasi" type="search" class="{{ $isian }}" placeholder="{{ __('atau cari nama tempat…') }}">
                <div v-if="usulanTempat.length" class="absolute z-20 left-0 right-0 mt-1 bg-white rounded-xl ring-1 ring-slate-200 shadow-lg overflow-hidden max-h-64 overflow-y-auto">
                    <button v-for="t in usulanTempat" :key="t.kunci" type="button" @click="pilihLokasi(t)"
                        class="w-full text-left px-3.5 py-2.5 hover:bg-emerald-50 border-b border-slate-100 last:border-0">
                        <span class="block text-sm font-semibold text-slate-700" v-text="t.nama"></span>
                        <span class="block text-[11px] text-slate-400 truncate" v-text="t.lengkap"></span>
                    </button>
                </div>
            </div>
        </div>
        <input v-model="form.lokasi" type="text" maxlength="80" class="{{ $isian }} uppercase mt-2" placeholder="{{ __('Nama lokasi, cth. Karanganyar, Jawa Tengah') }}">
        <div v-if="form.lat !== null && form.lokasi" class="mt-2 rounded-xl overflow-hidden ring-1 ring-slate-200">
            <iframe :src="petaKecil(form.lat, form.lng)" class="w-full h-44 border-0" loading="lazy" title="{{ __('Peta lokasi') }}"></iframe>
            <div class="flex items-center justify-between gap-2 px-3 py-2 bg-slate-50 text-xs">
                <span class="text-slate-500">{{ __('Titik lokasi akan tampil sebagai peta di halaman berita.') }}</span>
                <button type="button" @click="hapusLokasi" class="shrink-0 font-bold text-rose-600 hover:underline">{{ __('Hapus lokasi') }}</button>
            </div>
        </div>
    </div>
    <div>
        <label class="{{ $label }}">{{ __('Isi berita') }} <span class="text-rose-500">*</span></label>
        <textarea v-model="form.isi" rows="10" required class="{{ $isian }} leading-relaxed" placeholder="{{ __('Tulis isi berita. Tekan Enter untuk paragraf baru.') }}"></textarea>
    </div>

    {{-- Foto --}}
    <div>
        <label class="{{ $label }}">{{ __('Foto') }} <span class="font-normal text-slate-400" v-text="@js(__('(boleh lebih dari 1, maksimal :n; foto pertama jadi foto utama)')).replace(':n', maksFoto)"></span></label>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <div v-for="(f, i) in fotoTampil" :key="f.kunci" class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100">
                <img :src="f.url" alt="" class="w-full h-full object-cover">
                <span v-if="i === 0" class="absolute left-1.5 top-1.5 px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold">{{ __('Utama') }}</span>
                <span v-if="f.ukuran" class="absolute left-1.5 bottom-1.5 px-1.5 py-0.5 rounded bg-black/55 text-white text-[10px]" v-text="f.ukuran"></span>
                <button type="button" @click="buangFoto(f)" title="{{ __('Hapus foto') }}"
                    class="absolute right-1.5 top-1.5 w-7 h-7 rounded-full bg-black/55 text-white text-lg leading-none hover:bg-rose-600">&times;</button>
            </div>
            <label v-if="fotoTampil.length < maksFoto"
                class="aspect-square rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center gap-1 text-slate-400 hover:border-emerald-500 hover:text-emerald-600 cursor-pointer transition-colors">
                <svg v-if="!memproses" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                <span class="text-[11px] font-semibold text-center px-1" v-text="memproses ? @js(__('Menyiapkan foto…')) : @js(__('Tambah foto'))"></span>
                <input type="file" accept="image/*" multiple class="hidden" @change="pilihFoto" :disabled="memproses">
            </label>
        </div>
        <p class="mt-1.5 text-[11px] text-slate-400">{{ __('Besar foto tidak dibatasi: foto diperkecil otomatis sebelum dikirim agar cepat dibuka.') }}</p>
    </div>

    @if ($admin ?? false)
    <div>
        <label class="{{ $label }}">{{ __('Tampil di landing page sampai') }} <span class="font-normal text-slate-400">{{ __('(kosongkan = tampil terus)') }}</span></label>
        <input v-model="form.tampil_sampai" type="date" :min="hariIni" class="{{ $isian }} sm:max-w-xs">
    </div>
    @endif

    <p v-if="galat" class="rounded-xl bg-rose-50 ring-1 ring-rose-100 px-3 py-2 text-sm text-rose-700" v-text="galat"></p>
</div>
