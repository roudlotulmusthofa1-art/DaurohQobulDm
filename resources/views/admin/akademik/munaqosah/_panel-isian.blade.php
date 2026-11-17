{{--
    Munaqosah > detail: satu panel isian dari Pengaturan (Nilai Ilmu / Nilai Suluk / Catatan Mandub).
    $nomor = nomor panel, $b = 'ilmu' | 'suluk' | 'mandub'. Dipakai di dalam Vue #halaman-munaqosah-detail.
    Tahap Musyrifin: kolom bisa diisi (isian[b][kolom_id]). Tahap Lajnah: hanya dibaca dari isian tersimpan.
--}}
<section class="{{ $kartu }} p-5 sm:p-6">
    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel($nomor) !!} <span v-text="bagian['{{ $b }}']"></span></h2>

    {{-- Tahap Lajnah: hanya dibaca --}}
    <template v-if="lajnah">
        <dl v-if="(tersimpan['{{ $b }}'] || []).length" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div v-for="(x, i) in tersimpan['{{ $b }}']" :key="i" class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 px-4 py-3"
                :class="x.jenis === 'teks_panjang' ? 'md:col-span-2' : ''">
                <dt class="text-xs font-semibold text-slate-500" v-text="x.label"></dt>
                <dd class="mt-1 text-sm font-semibold text-slate-800 whitespace-pre-line break-words" v-text="tampilNilai(x.nilai)"></dd>
            </div>
        </dl>
        <p v-else class="mt-3 text-sm text-slate-400">{{ __('Tidak ada isian.') }}</p>
    </template>

    {{-- Tahap Musyrifin: diisi --}}
    <template v-else>
        <p v-if="!kolom['{{ $b }}'].length" class="mt-3 rounded-2xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-400">
            {{ __('Belum ada kolom. Atur dulu lewat tombol Pengaturan di halaman Istimaroh.') }}</p>
        <div v-else class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="k in kolom['{{ $b }}']" :key="k.id" :class="k.jenis === 'teks_panjang' || k.jenis === 'centang' ? 'md:col-span-2' : ''">
                <label :for="'mq-{{ $b }}-' + k.id" class="text-xs font-semibold text-slate-500" v-text="k.label"></label>
                <input v-if="k.jenis === 'teks'" :id="'mq-{{ $b }}-' + k.id" type="text" maxlength="1000" v-model="isian['{{ $b }}'][k.id]" class="mt-1 {{ $input }}">
                <textarea v-else-if="k.jenis === 'teks_panjang'" :id="'mq-{{ $b }}-' + k.id" rows="4" maxlength="10000" v-model="isian['{{ $b }}'][k.id]" class="mt-1 {{ $input }}"></textarea>
                <select v-else-if="k.jenis === 'dropdown'" :id="'mq-{{ $b }}-' + k.id" v-model="isian['{{ $b }}'][k.id]" class="mt-1 {{ $input }}">
                    <option value="" v-text="tp.pilih"></option>
                    <option v-for="p in k.pilihan" :key="p" :value="p" v-text="p"></option>
                </select>
                <div v-else class="mt-1.5 flex flex-wrap gap-2">
                    <label v-for="p in k.pilihan" :key="p" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm ring-1 cursor-pointer transition"
                        :class="isian['{{ $b }}'][k.id].includes(p) ? 'bg-emerald-50 ring-emerald-300 text-emerald-800 font-semibold' : 'bg-[#f8fbfa] ring-slate-200 text-slate-600'">
                        <input type="checkbox" :value="p" v-model="isian['{{ $b }}'][k.id]" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span v-text="p"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Isian lama dari kolom yang sudah dihapus --}}
        <div v-if="isianLama('{{ $b }}').length" class="mt-4 rounded-2xl bg-slate-50 ring-1 ring-slate-100 px-4 py-3">
            <p class="text-[11px] font-semibold text-slate-400">{{ __('Isian lama (kolomnya sudah dihapus, tetap disimpan):') }}</p>
            <p v-for="(x, i) in isianLama('{{ $b }}')" :key="i" class="mt-1 text-xs text-slate-600"><b v-text="x.label + ': '"></b><span v-text="tampilNilai(x.nilai)"></span></p>
        </div>
    </template>
</section>
