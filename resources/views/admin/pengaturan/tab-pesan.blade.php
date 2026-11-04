{{--
Pengaturan > Pesan: Template pesan WhatsApp (App\Support\PesanWa) + pratinjau.
Dimuat dari admin/pengaturan/index.blade.php. Variabel gaya ($kartu, $input, $label, $tombol, $ukuran) dari index.
--}}
<h2 class="text-lg font-bold text-slate-800">{{ __('Pesan WhatsApp') }}</h2>
<p class="text-sm text-slate-500">{{ __('Atur isi pesan WhatsApp yang dikirim otomatis. Berlaku untuk semua sesi. Klik tombol isian untuk menyisipkan data (nama, nomor, dll.) di posisi kursor.') }}</p>
<div class="mt-3 rounded-2xl bg-sky-50 ring-1 ring-sky-100 p-3 text-xs text-sky-900">
    {{ __('Format WhatsApp:') }} <b>*{{ __('tebal') }}*</b>, <i>_{{ __('miring') }}_</i>, <s>~{{ __('coret') }}~</s>. {{ __('Isian') }} <span class="font-mono">{catatan}</span> {{ __('dan') }} <span class="font-mono">{penempatan}</span> {{ __('sudah membawa judulnya sendiri dan otomatis hilang bila datanya kosong.') }}
</div>

<form method="POST" action="{{ route('admin.pengaturan.pesan') }}" class="mt-5 space-y-6">
    @csrf @method('PUT')
    @foreach ($pesanList as $grup => $daftar)
    <section>
        <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wider text-emerald-700">{{ __(\App\Support\PesanWa::GRUP[$grup]) }}</h3>
        <div class="space-y-4">
            @foreach ($daftar as $p)
            <article id="{{ $p['kunci'] }}" class="rounded-2xl ring-1 ring-slate-200 overflow-hidden">
                <div class="flex flex-wrap items-center gap-3 px-4 py-3 bg-[#f8fbfa] border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800">{{ $p['judul'] }}
                            @if ($p['diubah'])<span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ __('DIUBAH') }}</span>@endif
                        </p>
                        <p class="text-[11px] text-slate-500">{{ __('Kepada:') }} {{ $p['penerima'] }} · {{ $p['kapan'] }}</p>
                    </div>
                    {{-- Saklar kirim / tidak --}}
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                        <span class="relative">
                            <input type="checkbox" name="aktif[{{ $p['kunci'] }}]" value="1" @checked($p['aktif']) class="peer sr-only">
                            <span class="block w-10 h-[22px] rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></span>
                            <span class="absolute top-0.5 left-0.5 w-[18px] h-[18px] rounded-full bg-white shadow transition-transform peer-checked:translate-x-[18px]"></span>
                        </span>
                        {{ __('Kirim') }}
                    </label>
                </div>
                <div class="grid lg:grid-cols-2">
                    <div class="p-4 space-y-2 lg:border-r border-slate-100">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($p['isian'] as $isian)
                            <button type="button" @click="sisip('{{ $p['kunci'] }}', '{{ $isian }}')" title="{{ __(\App\Support\PesanWa::ISIAN[$isian]) }}"
                                class="rounded-lg bg-emerald-50 px-2 py-1 font-mono text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100">{{ '{' . $isian . '}' }}</button>
                            @endforeach
                        </div>
                        <textarea name="teks[{{ $p['kunci'] }}]" rows="9" maxlength="3000" :ref="'ta-' + '{{ $p['kunci'] }}'" v-model="teks['{{ $p['kunci'] }}']"
                            class="w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-3 py-2.5 text-[13px] leading-relaxed text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">{{ $p['teks'] }}</textarea>
                        <div class="flex justify-between gap-2">
                            <span class="text-[11px] text-slate-400" v-text="(teks['{{ $p['kunci'] }}'] || '').length + ' / 3000 ' + @js(__('karakter'))"></span>
                            <button type="submit" name="kembalikan" value="{{ $p['kunci'] }}" formnovalidate @disabled(! $p['diubah'])
                                @click="konfirmasi" data-konfirmasi="{{ __('Kembalikan pesan ini ke teks bawaan?') }}"
                                class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 disabled:opacity-40 disabled:cursor-not-allowed">{{ __('Kembalikan ke bawaan') }}</button>
                        </div>
                    </div>
                    {{-- Pratinjau ala WhatsApp dengan data contoh --}}
                    <div class="p-4 bg-[#efeae2]">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Pratinjau (data contoh)') }}</p>
                        <div class="mt-2 ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#d9fdd3] px-3 py-2 text-[13px] leading-relaxed text-slate-800 shadow-sm whitespace-pre-wrap break-words" v-html="pratinjau(teks['{{ $p['kunci'] }}'])"></div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @endforeach

    <div class="sticky bottom-3 z-10 flex justify-end">
        <button class="{{ $tombol }} shadow-xl">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            {{ __('Simpan Semua Pesan') }}
        </button>
    </div>
</form>


@push('scripts')
<script>
    // Bagian Vue tab Pesan: isi tiap template (v-model), sisipkan isian, pratinjau ala WhatsApp
    window.tabPengaturan = {
        data() {
            return {
                // { psb_daftar: 'teks...', ... } -> diisi dari server, diubah lewat textarea (v-model)
                teks: @json($pesanList->flatten(1)->pluck('teks', 'kunci')),
                contoh: @json(\App\Support\PesanWa::CONTOH),
                isianDikenal: @json(array_keys(\App\Support\PesanWa::ISIAN)),
            };
        },
        methods: {
            // Teks template -> HTML pratinjau (data contoh, *tebal*, _miring_, ~coret~). Teks di-escape dulu.
            pratinjau(teks) {
                if (!teks) return '<span class="text-slate-400">' + @js(__('(kosong = memakai teks bawaan)')) + '</span>';
                const data = { ...this.contoh, catatan: '*Catatan admin:*\n' + this.contoh.catatan, penempatan: '*Penempatan:*\n' + this.contoh.penempatan };
                const hasil = teks.replace(/\{(\w+)\}/g, (m, k) => this.isianDikenal.includes(k) ? (data[k] ?? '') : m)
                    .replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
                return hasil.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/\*([^*\n]+)\*/g, '<b>$1</b>')
                    .replace(/(^|\W)_([^_\n]+)_(?=\W|$)/g, '$1<i>$2</i>')
                    .replace(/~([^~\n]+)~/g, '<s>$1</s>');
            },
            // Sisipkan {isian} di posisi kursor textarea
            sisip(kunci, isian) {
                const ta = this.$refs['ta-' + kunci];
                const kata = '{' + isian + '}';
                const teks = this.teks[kunci] || '';
                const awal = ta.selectionStart ?? teks.length;
                const akhir = ta.selectionEnd ?? awal;
                this.teks[kunci] = teks.slice(0, awal) + kata + teks.slice(akhir);
                this.$nextTick(() => { ta.focus(); ta.selectionStart = ta.selectionEnd = awal + kata.length; });
            },
        },
    };
</script>
@endpush
