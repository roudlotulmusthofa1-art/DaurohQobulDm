{{--
    Pop-up "Kirim Pesan ke Asatid" (Data Asatid: daftar & detail).
    Dipakai:  @include('partials.kirim-pesan-asatid', ['penerimaPesan' => $penerimaPesan, 'pilihAwal' => $a->user_id ?? null])
    Letakkan DI DALAM elemen yang di-mount Vue; aplikasi Vue halaman itu memakai  mixins: [window.mixinKirimPesan]
    Tombol pembukanya:  <button type="button" @click="bukaKirimPesan">
--}}
@php
$galatPesan = $errors->getBag('kirimPesan');
$isianPesan = 'mt-1 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15';
@endphp

<dialog ref="dialogPesan" @click.self="tutupKirimPesan" class="m-auto w-[min(560px,calc(100%-2rem))] max-h-[90vh] rounded-3xl p-0 backdrop:bg-black/40">
    <form method="POST" action="{{ route('admin.asatid.pesan.kirim') }}" class="p-6 space-y-4" @submit="pesanMengirim = true">
        @csrf
        <input type="hidden" name="tujuan" :value="pesan.tujuan">
        <template v-if="pesan.tujuan === 'pilih'">
            <input v-for="id in pesan.dipilih" :key="id" type="hidden" name="user_ids[]" :value="id">
        </template>

        <div class="flex items-start gap-3">
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            </span>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-slate-800">{{ __('Kirim Pesan ke Asatid') }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Pesan tampil di dasbor asatid ›') }} <b>{{ __('Pesan dari Admin') }}</b> {{ __('dan di lonceng notifikasinya.') }}</p>
            </div>
            <button type="button" @click="tutupKirimPesan" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">✕</button>
        </div>

        @if ($galatPesan->any())
        <div class="p-3 rounded-2xl bg-rose-50 ring-1 ring-rose-200 text-sm text-rose-700">
            @foreach ($galatPesan->all() as $pesanGalat)
            <p>{{ $pesanGalat }}</p>
            @endforeach
        </div>
        @endif

        {{-- Penerima: semua / pilih --}}
        <div class="grid grid-cols-2 gap-2 p-1 rounded-2xl bg-slate-100">
            <button type="button" @click="pesan.tujuan = 'semua'" class="py-2 rounded-xl text-sm font-bold transition"
                :class="pesan.tujuan === 'semua' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'"
                v-text="@js(__('Semua asatid')) + ' (' + penerimaPesan.length + ')'"></button>
            <button type="button" @click="pesan.tujuan = 'pilih'" class="py-2 rounded-xl text-sm font-bold transition"
                :class="pesan.tujuan === 'pilih' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'">{{ __('Pilih asatid') }}</button>
        </div>

        <div v-if="pesan.tujuan === 'pilih'" class="rounded-2xl ring-1 ring-slate-200 overflow-hidden">
            <div class="flex items-center gap-2 p-2 border-b border-slate-100 bg-slate-50">
                <input type="search" v-model.trim="pesan.cari" placeholder="{{ __('Cari nama asatid…') }}"
                    class="flex-1 min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-emerald-400">
                <button type="button" @click="pilihSemuaTampil" class="shrink-0 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 hover:bg-emerald-50">{{ __('Pilih semua') }}</button>
                <button type="button" @click="pesan.dipilih = []" class="shrink-0 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100">{{ __('Kosongkan') }}</button>
            </div>
            <div class="max-h-56 overflow-y-auto divide-y divide-slate-100">
                <label v-for="a in penerimaTampil" :key="a.user_id" class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-emerald-50/50"
                    :class="pesan.dipilih.includes(a.user_id) ? 'bg-emerald-50/70' : ''">
                    <input type="checkbox" :value="a.user_id" v-model="pesan.dipilih" class="w-4 h-4 accent-emerald-600">
                    <img v-if="a.foto" :src="a.foto" alt="" class="w-8 h-8 rounded-xl object-cover">
                    <span v-else class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center" v-text="a.nama.charAt(0).toUpperCase()"></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-800 truncate" v-text="a.nama"></span>
                        <x-nama-arab vue="a.arab" kelas="text-xs text-slate-500" />
                    </span>
                </label>
                <p v-if="!penerimaTampil.length" class="px-3 py-6 text-center text-sm text-slate-400">{{ __('Tidak ada asatid yang cocok.') }}</p>
            </div>
            <p class="px-3 py-2 text-xs bg-slate-50 border-t border-slate-100" :class="pesan.dipilih.length ? 'text-emerald-700 font-semibold' : 'text-slate-400'"
                v-text="pesan.dipilih.length ? @js(__(':jumlah asatid dipilih')).replace(':jumlah', pesan.dipilih.length) : @js(__('Belum ada yang dipilih'))"></p>
        </div>

        <label class="block">
            <span class="text-sm font-semibold text-slate-700">{{ __('Judul') }} <span class="font-normal text-slate-400">{{ __('(opsional)') }}</span></span>
            <input type="text" name="judul" v-model="pesan.judul" maxlength="150" placeholder="{{ __('mis. Rapat asatid hari Kamis') }}" class="{{ $isianPesan }}">
        </label>
        <label class="block">
            <span class="flex items-center justify-between text-sm font-semibold text-slate-700">
                {{ __('Isi Pesan') }}
                <span class="text-xs font-normal" :class="pesan.isi.length > 3000 ? 'text-rose-600' : 'text-slate-400'" v-text="pesan.isi.length + '/3000'"></span>
            </span>
            <textarea ref="isiPesan" name="isi" v-model="pesan.isi" rows="5" required maxlength="3000" dir="auto"
                placeholder="{{ __('Tulis pesan untuk asatid…') }}" class="{{ $isianPesan }}"></textarea>
        </label>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" @click="tutupKirimPesan" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ __('Batal') }}</button>
            <button type="submit" :disabled="!bisaKirimPesan || pesanMengirim"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-sky-600 text-white text-sm font-bold hover:bg-sky-700 disabled:opacity-40 disabled:cursor-not-allowed"
                v-text="pesanMengirim ? @js(__('Mengirim…')) : @js(__('Kirim ke :jumlah asatid')).replace(':jumlah', jumlahPenerimaPesan)"></button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    // Bagian Vue untuk pop-up Kirim Pesan (dipasang lewat  mixins: [window.mixinKirimPesan])
    window.mixinKirimPesan = {
        data() {
            const pilihAwal = @json($pilihAwal ?? null);
            const lamaDipilih = @json(array_map('intval', (array) old('user_ids', [])));
            return {
                penerimaPesan: @json($penerimaPesan),
                pesan: {
                    tujuan: @json(old('tujuan')) || (pilihAwal ? 'pilih' : 'semua'),
                    dipilih: lamaDipilih.length ? lamaDipilih : (pilihAwal ? [pilihAwal] : []),
                    cari: '',
                    judul: @json(old('judul', '')),
                    isi: @json(old('isi', '')),
                },
                pesanMengirim: false,
            };
        },
        computed: {
            penerimaTampil() {
                const kata = this.pesan.cari.toLowerCase();
                return kata ? this.penerimaPesan.filter((a) => a.nama.toLowerCase().includes(kata) || String(a.nomor).toLowerCase().includes(kata)) : this.penerimaPesan;
            },
            jumlahPenerimaPesan() {
                return this.pesan.tujuan === 'semua' ? this.penerimaPesan.length : this.pesan.dipilih.length;
            },
            bisaKirimPesan() {
                return this.jumlahPenerimaPesan > 0 && this.pesan.isi.trim().length > 0;
            },
        },
        methods: {
            bukaKirimPesan() {
                this.$refs.dialogPesan.showModal();
                this.$nextTick(() => this.$refs.isiPesan.focus());
            },
            tutupKirimPesan() {
                this.$refs.dialogPesan.close();
            },
            // Centang semua asatid yang sedang tampil (hasil pencarian)
            pilihSemuaTampil() {
                const id = this.penerimaTampil.map((a) => a.user_id);
                this.pesan.dipilih = [...new Set([...this.pesan.dipilih, ...id])];
            },
        },
        mounted() {
            // Gagal kirim -> buka lagi pop-up beserta pesan kesalahannya
            if (@json($galatPesan->any())) this.bukaKirimPesan();
        },
    };
</script>
@endpush
