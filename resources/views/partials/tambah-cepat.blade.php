{{--
    Pop-up "Tambah Cepat" untuk Data Asatid & Data Admin.
    Dipakai:  @include('partials.tambah-cepat', ['jenis' => 'Asatid', 'aksi' => route('admin.asatid.tambah-cepat')])
    Letakkan DI DALAM elemen yang di-mount Vue; aplikasi Vue halaman itu memakai  mixins: [window.mixinTambahCepat]
    Tombol pembukanya:  <button type="button" @click="bukaTambahCepat">
--}}
@php
$isianTc = 'mt-1 w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-2.5 text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15';
$galatTc = $errors->getBag('tambahCepat');
@endphp

<dialog ref="dialogTambahCepat" @click.self="tutupTambahCepat" class="m-auto w-[min(480px,calc(100%-2rem))] rounded-3xl p-0 backdrop:bg-black/40">
    <form method="POST" action="{{ $aksi }}" class="p-6 space-y-4" @submit="tcMengirim = true">
        @csrf
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <h2 class="text-lg font-bold text-slate-800">{{ __('Tambah Cepat :jenis', ['jenis' => __($jenis)]) }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Langsung diterima & akun login aktif. Data lain bisa dilengkapi nanti lewat tombol Edit.') }}</p>
            </div>
            <button type="button" @click="tutupTambahCepat" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">✕</button>
        </div>

        @if ($galatTc->any())
        <div class="p-3 rounded-2xl bg-rose-50 ring-1 ring-rose-200 text-sm text-rose-700">
            @foreach ($galatTc->all() as $pesan)
            <p>{{ $pesan }}</p>
            @endforeach
        </div>
        @endif

        <label class="block">
            <span class="text-sm font-semibold text-slate-700">{{ __('Nama Lengkap') }}</span>
            <input ref="tcNama" type="text" name="nama_lengkap" v-model="tc.nama_lengkap" required maxlength="150" placeholder="{{ __('mis. Ustadz Ahmad Fauzi') }}" class="{{ $isianTc }}">
        </label>
        <label class="block">
            <span class="text-sm font-semibold text-slate-700">{{ __('No. WhatsApp') }}</span>
            <input type="tel" name="no_hp" v-model="tc.no_hp" required maxlength="20" placeholder="081234567890" class="{{ $isianTc }}">
        </label>
        <label class="block">
            <span class="text-sm font-semibold text-slate-700">{{ __('Email (untuk login)') }}</span>
            <input type="email" name="email" v-model="tc.email" required maxlength="255" placeholder="nama@email.com" class="{{ $isianTc }}">
        </label>
        <label class="block">
            <span class="text-sm font-semibold text-slate-700">{{ __('Password') }}</span>
            <span class="relative block">
                <input :type="tcLihat ? 'text' : 'password'" name="password" v-model="tc.password" required minlength="8" maxlength="100" autocomplete="new-password"
                    placeholder="{{ __('Minimal 8 karakter') }}" class="{{ $isianTc }} pr-36">
                <span class="absolute right-2 top-1/2 -translate-y-1/2 mt-0.5 flex gap-1">
                    <button type="button" @click="tcLihat = !tcLihat" class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100" v-text="tcLihat ? @js(__('Sembunyi')) : @js(__('Lihat'))"></button>
                    <button type="button" @click="buatPasswordAcak" class="px-2 py-1 rounded-lg text-xs font-semibold text-emerald-700 hover:bg-emerald-50">{{ __('Acak') }}</button>
                </span>
            </span>
            <span v-if="tc.password && tc.password.length < 8" class="mt-1 block text-xs text-amber-600">{{ __('Kurang') }} <span v-text="8 - tc.password.length"></span> {{ __('karakter lagi.') }}</span>
            <span class="mt-1 block text-xs text-slate-400">{{ __('Catat password ini lalu berikan kepada yang bersangkutan.') }}</span>
        </label>
        <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-slate-50 ring-1 ring-slate-200 cursor-pointer">
            <input type="checkbox" name="kirim_wa" value="1" v-model="tc.kirim_wa" class="mt-0.5 accent-emerald-600">
            <span class="text-sm text-slate-700">{{ __('Kirim pesan "diterima" lewat WhatsApp') }}
                <span class="block text-xs text-slate-400">{{ __('Isi pesan mengikuti Pengaturan › Pesan (password tidak ikut dikirim).') }}</span>
            </span>
        </label>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" @click="tutupTambahCepat" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ __('Batal') }}</button>
            <button type="submit" :disabled="tcMengirim" class="px-5 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 disabled:opacity-50"
                v-text="tcMengirim ? @js(__('Menyimpan…')) : @js(__('Simpan & Aktifkan'))"></button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    // Bagian Vue untuk pop-up Tambah Cepat (dipasang lewat  mixins: [window.mixinTambahCepat])
    window.mixinTambahCepat = {
        data() {
            return {
                tc: {
                    nama_lengkap: @json(old('nama_lengkap', '')),
                    no_hp: @json(old('no_hp', '')),
                    email: @json(old('email', '')),
                    password: '',
                    kirim_wa: @json((bool) old('kirim_wa')),
                },
                tcLihat: false,
                tcMengirim: false,
            };
        },
        methods: {
            bukaTambahCepat() {
                this.$refs.dialogTambahCepat.showModal();
                this.$nextTick(() => this.$refs.tcNama.focus());
            },
            tutupTambahCepat() {
                this.$refs.dialogTambahCepat.close();
            },
            // Password acak 10 karakter (tanpa huruf yang mirip: l, 1, O, 0)
            buatPasswordAcak() {
                const huruf = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                const acak = crypto.getRandomValues(new Uint32Array(10));
                this.tc.password = Array.from(acak, (n) => huruf[n % huruf.length]).join('');
                this.tcLihat = true;
            },
        },
        mounted() {
            // Gagal simpan -> buka lagi pop-up beserta pesan kesalahannya
            if (@json($galatTc->any())) this.bukaTambahCepat();
        },
    };
</script>
@endpush
