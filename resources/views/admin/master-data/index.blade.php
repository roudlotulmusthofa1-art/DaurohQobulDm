@extends('layouts.admin')



@section('content')
    <div id="master-data-app" v-cloak>
        <div class="mx-4 md:mx-10 lg:mx-20 mt-8">
            {{-- ================= Header =================
                 Latar gradasi hijau gelap, sama seperti sidebar --}}
            <x-header-admin class="mb-5" :label="__('Sistem')" :judul="__('Master Data')" :keterangan="__('Kelola data referensi dan kategori sistem')">
                <x-slot:aksi>
                    {{-- jumlah semua data di semua menu --}}
                    <div class="rounded-2xl bg-white/10 ring-1 ring-white/15 px-4 py-2 text-right">
                        <p class="text-2xl font-extrabold leading-tight" v-text="totalData()"></p>
                        <p class="text-xs text-white/60">{{ __('total data') }}</p>
                    </div>
                </x-slot:aksi>
            </x-header-admin>

            {{-- ================= Menu (tab) =================
                 Setiap menu punya warna sendiri lewat --aksen (diisi dari 'accent' di controller).
                 Menu aktif: latar hijau gelap + ikon berwarna penuh. --}}
            <div class="mb-6 -mx-1 overflow-x-auto px-1 pb-2">
                <div class="inline-flex gap-1.5 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5" role="tablist">
                    @foreach ($tabs as $key => $t)
                        <a href="?tab={{ $key }}" role="tab" style="--aksen: {{ $t['accent'] }}"
                            :aria-selected="activeKey === '{{ $key }}'"
                            class="group flex items-center gap-2.5 whitespace-nowrap rounded-xl py-1.5 pl-1.5 pr-3 text-sm font-medium transition-all duration-200"
                            :class="activeKey === '{{ $key }}'
                                ? 'bg-[linear-gradient(135deg,#0f4238,#0b332c)] text-white shadow-md shadow-[#0b332c]/25'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            @click.prevent="selectTab('{{ $key }}')">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110"
                                :class="activeKey === '{{ $key }}'
                                    ? 'bg-(--aksen) text-white shadow-[0_4px_12px_-2px_var(--aksen)]'
                                    : 'bg-[color-mix(in_srgb,var(--aksen)_14%,white)] text-(--aksen)'">
                                <x-master-data.icon :name="$t['icon']" size="16" />
                            </span>
                            {{ $t['label'] }}
                            {{-- jumlah data di menu ini --}}
                            <span class="min-w-5 rounded-full px-1.5 text-center text-[11px] font-semibold"
                                :class="activeKey === '{{ $key }}' ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-500'"
                                v-text="rows['{{ $key }}'].length"></span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ================= Isi tiap tab ================= --}}
            @foreach ($tabs as $key => $t)
                <section v-if="activeKey === '{{ $key }}'" style="--aksen: {{ $t['accent'] }}">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--aksen)_14%,white)] text-[var(--aksen)]">
                                <x-master-data.icon :name="$t['icon']" size="20" />
                            </span>
                            <div>
                                <h2 class="text-lg font-semibold text-[#0f1f1a]">{{ $t['title'] }}</h2>
                                <p class="text-sm text-gray-500">{{ $t['description'] }}</p>
                            </div>
                        </div>
                        <button type="button"
                            class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0f4a3f]"
                            @click="openModal()">
                            <x-master-data.icon name="plus" />
                            {{ $t['add_label'] }}
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                        {{-- garis tipis berwarna sesuai menu --}}
                        <div class="h-1 bg-[var(--aksen)]"></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-left text-[13px] font-medium text-gray-500">
                                        <th class="w-10 px-4 py-3"></th>
                                        <th class="w-20 px-3 py-3">{{ __('Urutan') }}</th>
                                        @foreach ($t['columns'] as $col)
                                            <th class="px-3 py-3 {{ $col['type'] === 'count' ? 'text-center' : '' }}">
                                                {{ $col['label'] }}</th>
                                        @endforeach
                                        <th class="w-20 px-3 py-3 text-center">{{ __('Status') }}</th>
                                        <th class="w-24 px-3 py-3 text-center">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Kosong --}}
                                    <tr v-if="!rows['{{ $key }}'].length">
                                        <td colspan="{{ count($t['columns']) + 4 }}"
                                            class="px-4 py-14 text-center text-gray-500">
                                            <x-master-data.icon name="inbox" size="32"
                                                class="mx-auto mb-2 text-gray-300" />
                                            {{ __('Belum ada data :nama.', ['nama' => strtolower($t['singular'])]) }}
                                        </td>
                                    </tr>

                                    {{-- Baris data --}}
                                    <tr v-for="(row, index) in rows['{{ $key }}']" :key="row.id"
                                        :draggable="drag.armedId === row.id"
                                        class="border-b border-gray-100 transition-colors last:border-0 hover:bg-gray-50/70"
                                        :class="{ 'bg-[#22c55e]/10': drag.overIndex === index, 'opacity-60': !row.is_active }"
                                        @dragstart="dragStart(index, $event)" @dragenter.prevent="dragEnter(index)"
                                        @dragover.prevent @drop.prevent @dragend="dragEnd">
                                        <td class="px-4 py-4">
                                            <button type="button"
                                                class="cursor-grab text-gray-400 hover:text-gray-600 active:cursor-grabbing"
                                                title="{{ __('Seret untuk mengubah urutan') }}" @mousedown="drag.armedId = row.id"
                                                @mouseup="drag.armedId = null">
                                                <x-master-data.icon name="grip" />
                                            </button>
                                        </td>
                                        <td class="px-3 py-4 text-gray-500" v-text="index + 1"></td>

                                        @foreach ($t['columns'] as $col)
                                            @include('admin.master-data.partials.cell', ['col' => $col])
                                        @endforeach

                                        <td class="px-3 py-4 text-center">
                                            <button type="button" class="rounded p-1 transition hover:bg-gray-100"
                                                :class="row.is_active ? 'text-[#22c55e]' : 'text-gray-400'"
                                                :title="row.is_active ? @js(__('Aktif — klik untuk nonaktifkan')) :
                                                    @js(__('Nonaktif — klik untuk aktifkan'))"
                                                @click="toggle(row)">
                                                <span v-if="row.is_active"><x-master-data.icon name="eye" /></span>
                                                <span v-else><x-master-data.icon name="eye-off" /></span>
                                            </button>
                                        </td>
                                        <td class="px-3 py-4">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button"
                                                    class="rounded p-1 text-blue-600 transition hover:bg-blue-50"
                                                    title="{{ __('Edit') }}" @click="openModal(row)">
                                                    <x-master-data.icon name="pencil" />
                                                </button>
                                                <button type="button"
                                                    class="rounded p-1 text-red-600 transition hover:bg-red-50"
                                                    title="{{ __('Hapus') }}" @click="askDelete(row)">
                                                    <x-master-data.icon name="trash" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endforeach

            {{-- ================= Modal tambah / edit ================= --}}
            <div v-if="modal.open"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center"
                @mousedown.self="modal.open = false">
                {{-- Modal Edit Kamar dibuat lebih lebar supaya denah kasur muat --}}
                <form id="master-data-form" class="w-full rounded-2xl bg-white shadow-xl"
                    :class="activeKey === 'kamar' && modal.item ? 'max-w-2xl' : 'max-w-lg'" @submit.prevent="save">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-[#0f1f1a]"
                            v-text="(modal.item ? @js(__('Edit')) + ' ' : @js(__('Tambah')) + ' ') + TABS[activeKey].singular"></h3>
                        <button type="button" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            @click="modal.open = false">
                            <x-master-data.icon name="x" size="18" />
                        </button>
                    </div>

                    @foreach ($tabs as $key => $t)
                        <div v-if="activeKey === '{{ $key }}'" class="grid grid-cols-2 gap-4 px-6 pt-5">
                            @foreach ($t['fields'] as $field)
                                @include('admin.master-data.partials.field', ['field' => $field])
                            @endforeach
                        </div>
                    @endforeach

                    {{-- ================= Denah kamar (hanya saat EDIT kamar) =================
                         Kasur dinomori 1..kapasitas. Klik kasur untuk menempatkan / mengubah santri.
                         Perubahan di denah langsung tersimpan (tidak menunggu tombol Simpan). --}}
                    <div v-if="activeKey === 'kamar' && modal.item" class="px-6 pt-5">
                        <div class="rounded-2xl border border-purple-100 bg-purple-50/50 p-4">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-500 text-white">
                                        <x-master-data.icon name="bed" size="18" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-[#0f1f1a]">{{ __('Denah Kamar') }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Klik kasur untuk menempatkan santri. Perubahan denah langsung tersimpan.') }}</p>
                                    </div>
                                </div>
                                <span v-if="denah.data && denah.data.kamar.kapasitas"
                                    class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-purple-700 ring-1 ring-purple-200"
                                    v-text="kasurTerisi() + ' / ' + denah.data.kamar.kapasitas + ' ' + @js(__('terisi'))"></span>
                            </div>

                            <p v-if="denah.loading" class="py-6 text-center text-sm text-gray-500">{{ __('Memuat denah…') }}</p>
                            <p v-else-if="denah.error && !denah.data" class="py-6 text-center text-sm text-red-600" v-text="denah.error"></p>

                            <template v-else-if="denah.data">
                                <p v-if="!denah.data.kamar.kapasitas" class="rounded-xl bg-white p-4 text-center text-sm text-gray-500">
                                    {{ __('Isi') }} <b>{{ __('Kapasitas (jumlah kasur)') }}</b> {{ __('di atas lalu klik Simpan, kemudian buka lagi untuk melihat denah.') }}
                                </p>

                                <template v-else>
                                    {{-- Kasur-kasur. Jumlah kolom = "Kasur per baris" --}}
                                    <div class="grid gap-2" :style="{ gridTemplateColumns: 'repeat(' + kolomDenah() + ', minmax(0, 1fr))' }">
                                        <button v-for="n in daftarKasur()" :key="n" type="button" @click="pilihKasur(n)"
                                            :title="penghuniKasur(n) ? @js(__('Kasur')) + ' ' + n + ': ' + penghuniKasur(n).nama : @js(__('Kasur')) + ' ' + n + ': ' + @js(__('kosong'))"
                                            class="relative flex flex-col items-center rounded-xl border-2 px-1.5 pb-2 pt-1.5 text-center transition hover:-translate-y-0.5"
                                            :class="[
                                                penghuniKasur(n) ? 'border-purple-200 bg-white shadow-sm' : 'border-dashed border-gray-300 bg-white/60 hover:border-purple-300',
                                                n > denah.data.kamar.kapasitas ? 'border-red-300 bg-red-50' : '',
                                                denah.pilih === n ? 'ring-2 ring-purple-500 ring-offset-1' : '',
                                            ]">
                                            {{-- nomor kasur --}}
                                            <span class="absolute left-1.5 top-1 text-[10px] font-semibold text-gray-400" v-text="n"></span>
                                            {{-- bantal --}}
                                            <span class="mb-1.5 h-1.5 w-1/2 rounded-full" :class="penghuniKasur(n) ? 'bg-purple-200' : 'bg-gray-200'"></span>
                                            {{-- foto / inisial / tanda tambah --}}
                                            <img v-if="penghuniKasur(n) && penghuniKasur(n).foto" :src="penghuniKasur(n).foto" alt=""
                                                v-on:error="penghuniKasur(n).foto = null" class="h-9 w-9 rounded-full object-cover">
                                            <span v-else-if="penghuniKasur(n)" class="flex h-9 w-9 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700"
                                                v-text="inisial(penghuniKasur(n).nama)"></span>
                                            <span v-else class="flex h-9 w-9 items-center justify-center text-gray-300">
                                                <x-master-data.icon name="plus" size="18" />
                                            </span>
                                            <span class="mt-1 w-full truncate text-[11px] font-medium"
                                                :class="penghuniKasur(n) ? 'text-gray-800' : 'text-gray-400'"
                                                v-text="penghuniKasur(n) ? penghuniKasur(n).nama : @js(__('Kosong'))"></span>
                                        </button>
                                    </div>

                                    {{-- pintu kamar --}}
                                    <div class="mt-2 flex justify-center">
                                        <span class="rounded-b-lg border-x-2 border-b-2 border-amber-300 bg-amber-50 px-6 py-0.5 text-[10px] font-medium text-amber-700">{{ __('Pintu') }}</span>
                                    </div>

                                    {{-- Panel kasur yang sedang dipilih --}}
                                    <div v-if="denah.pilih" class="mt-3 rounded-xl bg-white p-3 ring-1 ring-purple-100">
                                        <p class="mb-2 text-sm font-semibold text-[#0f1f1a]" v-text="@js(__('Kasur')) + ' ' + denah.pilih"></p>

                                        {{-- Kasur terisi --}}
                                        <div v-if="penghuniKasur(denah.pilih)" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-sm text-gray-700">
                                                {{ __('Ditempati') }} <b v-text="penghuniKasur(denah.pilih).nama"></b>
                                                <span class="text-xs text-gray-400" v-text="'(' + penghuniKasur(denah.pilih).nomor + ')'"></span>
                                            </p>
                                            <div class="flex gap-2">
                                                <button type="button" :disabled="denah.busy" @click="kosongkanKasur(penghuniKasur(denah.pilih), false)"
                                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60">
                                                    {{ __('Kosongkan kasur') }}</button>
                                                <button type="button" :disabled="denah.busy" @click="kosongkanKasur(penghuniKasur(denah.pilih), true)"
                                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 disabled:opacity-60">
                                                    {{ __('Keluarkan dari kamar') }}</button>
                                            </div>
                                        </div>

                                        {{-- Kasur kosong: pilih santri --}}
                                        <div v-else class="flex flex-col gap-2 sm:flex-row">
                                            <select v-model="denah.santriId" class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                                <option value="">{{ __('— Pilih santri —') }}</option>
                                                <optgroup v-if="santriTanpaKasur().length" label="{{ __('Penghuni kamar ini (belum dapat kasur)') }}">
                                                    <option v-for="s in santriTanpaKasur()" :key="'p' + s.id" :value="s.id" v-text="s.nama"></option>
                                                </optgroup>
                                                <optgroup v-if="denah.data.tanpa_kamar.length" label="{{ __('Santri yang belum punya kamar') }}">
                                                    <option v-for="s in denah.data.tanpa_kamar" :key="'t' + s.id" :value="s.id" v-text="s.nama"></option>
                                                </optgroup>
                                            </select>
                                            <button type="button" :disabled="denah.busy || !denah.santriId" @click="tempatkanSantri"
                                                class="shrink-0 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 disabled:opacity-60"
                                                v-text="denah.busy ? @js(__('Menyimpan…')) : @js(__('Tempatkan'))"></button>
                                        </div>
                                        <p v-if="!penghuniKasur(denah.pilih) && !santriTanpaKasur().length && !denah.data.tanpa_kamar.length"
                                            class="mt-2 text-xs text-gray-500">{{ __('Tidak ada santri yang bisa ditempatkan (semua sudah punya kamar & kasur).') }}</p>
                                        <p v-if="denah.error" class="mt-2 text-xs text-red-600" v-text="denah.error"></p>
                                    </div>

                                    {{-- Penghuni yang belum dapat kasur --}}
                                    <div v-if="santriTanpaKasur().length" class="mt-3">
                                        <p class="mb-1.5 text-xs font-medium text-amber-700">{{ __('Penghuni yang belum dapat kasur:') }}</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <span v-for="s in santriTanpaKasur()" :key="'c' + s.id"
                                                class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs text-amber-800 ring-1 ring-amber-200" v-text="s.nama"></span>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    {{-- Status aktif (sama untuk semua tab) --}}
                    <div class="px-6 py-5">
                        <label
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                            <span>
                                <span class="block text-sm font-medium text-gray-700">{{ __('Aktif') }}</span>
                                <span class="block text-xs text-gray-500">{{ __('Data nonaktif tidak muncul di pilihan form lain.') }}</span>
                            </span>
                            <span class="relative inline-flex">
                                <input v-model="modal.form.is_active" type="checkbox" class="peer sr-only">
                                <span class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-[#22c55e]"></span>
                                <span
                                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                        <button type="button"
                            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            @click="modal.open = false">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" :disabled="modal.saving"
                            class="rounded-xl bg-[#0b332c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f4a3f] disabled:cursor-not-allowed disabled:opacity-60"
                            v-text="modal.saving ? @js(__('Menyimpan…')) : @js(__('Simpan'))"></button>
                    </div>
                </form>
            </div>

            {{-- ================= Konfirmasi hapus ================= --}}
            <div v-if="confirm.open" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4"
                @mousedown.self="confirm.open = false">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <x-master-data.icon name="alert" size="22" />
                    </div>
                    <h3 class="text-center text-base font-semibold text-[#0f1f1a]"
                        v-text="@js(__('Hapus :nama?')).replace(':nama', TABS[activeKey].singular.toLowerCase())"></h3>
                    <p class="mt-1 text-center text-sm text-gray-500"
                        v-text="confirm.row ? '“' + confirm.row.nama + '” ' + @js(__('akan dihapus permanen.')) : ''"></p>
                    <div class="mt-6 grid grid-cols-2 gap-2">
                        <button type="button"
                            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            @click="confirm.open = false">{{ __('Batal') }}</button>
                        <button type="button" :disabled="confirm.busy"
                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            @click="doDelete" v-text="confirm.busy ? @js(__('Menghapus…')) : @js(__('Hapus'))"></button>
                    </div>
                </div>
            </div>

            {{-- ================= Notifikasi ================= --}}
            <div v-if="toast.show"
                class="fixed bottom-6 right-6 z-[60] flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="toast.type === 'error' ? 'bg-red-600' : 'bg-[#0b332c]'">
                <span v-if="toast.type === 'error'"><x-master-data.icon name="alert" /></span>
                <span v-else><x-master-data.icon name="check" /></span>
                <span v-text="toast.text"></span>
            </div>
        </div>
    </div>
@endsection

{{-- ==========================================================================
     Vue 3 — paling bawah. `window.Vue` disediakan resources/js/app.js (Vite),
     yang sudah dimuat oleh layouts.admin lewat @vite.
     ========================================================================== --}}
@push('scripts')
    <script type="module">
        const {
            createApp,
            reactive,
            ref
        } = window.Vue;

        const BASE_URL = @json(url('admin/master-data/api'));
        const DENAH_URL = @json(url('admin/master-data/kamar')); // + /{id}/denah, /{id}/kasur
        const CSRF = @json(csrf_token());
        const TABS = @json($tabs);
        const COLORS = @json($colors);

        /** Panggil API JSON. Melempar error berisi status & data bila gagal. */
        async function api(method, path = '', body = null) {
            // path boleh alamat lengkap (http...), selain itu ditempel ke BASE_URL
            const res = await fetch(path.startsWith('http') ? path : BASE_URL + path, {
                method,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const isJson = (res.headers.get('content-type') || '').includes('application/json');
            const data = isJson ? await res.json().catch(() => ({})) : {};
            if (!res.ok || !isJson) {
                const message = res.status === 419 ? @js(__('Sesi habis, silakan muat ulang halaman.')) : (data.message ||
                    @js(__('Terjadi kesalahan. Coba lagi.')));
                throw Object.assign(new Error(message), {
                    status: res.status,
                    data
                });
            }
            return data;
        }

        createApp({
            setup() {
                const activeKey = ref(@json($tab));
                const rows = reactive(@json($data));

                // ---------- Tampilan ----------
                const show = (v) => (v === null || v === undefined || v === '' ? '-' : v);
                const colorHex = (v) => COLORS[v] ?? '#f1f5f9';
                const timeRange = (r) => (r.jam_mulai || r.jam_selesai ?
                    `${r.jam_mulai ?? '--:--'} – ${r.jam_selesai ?? '--:--'}` : '-');
                // ---------- Kalender (Semester) ----------
                // Intl = fitur bawaan browser untuk format tanggal.
                // 'islamic-umalqura' = kalender Hijriyah Umm al-Qura.
                const FORMAT_MASEHI = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                const FORMAT_HIJRIYAH = new Intl.DateTimeFormat('id-u-ca-islamic-umalqura', { day: 'numeric', month: 'long', year: 'numeric' });
                const FORMAT_TAHUN_HIJRIYAH = new Intl.DateTimeFormat('id-u-ca-islamic-umalqura', { year: 'numeric' });
                const keTanggal = (iso) => (iso ? new Date(iso.slice(0, 10) + 'T00:00:00') : null); // "2026-07-13" -> Date

                const masehi = (iso) => (iso ? FORMAT_MASEHI.format(keTanggal(iso)) : '?');      // "13 Jul 2026"
                const hijriyahDari = (iso) => (iso ? FORMAT_HIJRIYAH.format(keTanggal(iso)) : ''); // "28 Muharam 1448 H"
                const tahunHijriyah = (iso) => (iso ? FORMAT_TAHUN_HIJRIYAH.formatToParts(keTanggal(iso))
                    .find((p) => p.type === 'year')?.value : null);                                // "1448"

                const periodeMasehi = (r) => (r.tanggal_mulai || r.tanggal_selesai ?
                    `${masehi(r.tanggal_mulai)} – ${masehi(r.tanggal_selesai)}` : '-');
                const periodeHijriyah = (r) => (r.tanggal_mulai_hijriyah || r.tanggal_selesai_hijriyah ?
                    `${r.tanggal_mulai_hijriyah || '?'} – ${r.tanggal_selesai_hijriyah || '?'}` : '-');
                // Jumlah semua data (angka di header)
                // (fungsi biasa, karena window.Vue di app.js tidak menyediakan computed)
                const totalData = () => Object.values(rows).reduce((n, list) => n + list.length, 0);

                function selectTab(key) {
                    activeKey.value = key;
                    const u = new URL(window.location.href);
                    u.searchParams.set('tab', key);
                    window.history.replaceState({}, '', u);
                }

                async function reload(key) {
                    rows[key] = (await api('GET', `/${key}`)).data;
                }

                // ---------- Notifikasi ----------
                const toast = reactive({
                    show: false,
                    text: '',
                    type: 'success'
                });
                let toastTimer;

                function notify(text, type = 'success') {
                    Object.assign(toast, {
                        show: true,
                        text,
                        type
                    });
                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => (toast.show = false), 3000);
                }

                // ---------- Tambah / Edit ----------
                const modal = reactive({
                    open: false,
                    item: null,
                    form: {},
                    errors: {},
                    saving: false
                });

                function openModal(item = null) {
                    const form = {
                        is_active: item?.is_active ?? true
                    };
                    for (const f of TABS[activeKey.value].fields) {
                        if (f.type === 'section') continue; // judul bagian, bukan isian
                        form[f.key] = item?.[f.key] ?? f.default ?? '';
                    }
                    Object.assign(modal, {
                        open: true,
                        item,
                        form,
                        errors: {},
                        saving: false
                    });
                    // Edit kamar -> muat denah kasurnya
                    if (activeKey.value === 'kamar' && item) muatDenah(item.id);
                    else denah.data = null;
                    setTimeout(() => document.querySelector(
                            '#master-data-form input:not([type=checkbox]), #master-data-form textarea')
                    ?.focus());
                }

                /**
                 * Dipanggil saat tanggal Masehi dipilih (lihat 'hijri' di controller).
                 * Mengisi tanggal Hijriyah-nya, dan tahun ajaran (Masehi & Hijriyah) bila masih kosong.
                 */
                function isiHijriyah(dari, ke) {
                    const f = modal.form;
                    f[ke] = hijriyahDari(f[dari]);

                    if (f.tanggal_mulai && f.tanggal_selesai) {
                        const m1 = f.tanggal_mulai.slice(0, 4), m2 = f.tanggal_selesai.slice(0, 4);
                        const h1 = tahunHijriyah(f.tanggal_mulai), h2 = tahunHijriyah(f.tanggal_selesai);
                        if (!f.tahun_ajaran) f.tahun_ajaran = m1 === m2 ? m1 : `${m1}/${m2}`;
                        if (!f.tahun_ajaran_hijriyah) f.tahun_ajaran_hijriyah = h1 === h2 ? `${h1} H` : `${h1}/${h2} H`;
                    }
                }

                async function save() {
                    const key = activeKey.value;
                    const payload = {};
                    for (const [k, v] of Object.entries(modal.form)) payload[k] = v === '' ? null : v;

                    modal.saving = true;
                    modal.errors = {};
                    try {
                        const res = modal.item ?
                            await api('PUT', `/${key}/${modal.item.id}`, payload) :
                            await api('POST', `/${key}`, payload);
                        modal.open = false;
                        notify(res.message);
                        await reload(key);
                    } catch (e) {
                        if (e.status === 422 && e.data?.errors) modal.errors = e.data.errors;
                        else notify(e.message, 'error');
                    } finally {
                        modal.saving = false;
                    }
                }

                // ---------- Denah kamar ----------
                const denah = reactive({
                    loading: false,
                    error: '',
                    data: null,     // { kamar, penghuni: [...], tanpa_kamar: [...] }
                    pilih: null,    // nomor kasur yang sedang diklik
                    santriId: '',   // santri yang dipilih untuk kasur kosong
                    busy: false,
                });

                async function muatDenah(kamarId) {
                    Object.assign(denah, { loading: true, error: '', data: null, pilih: null, santriId: '' });
                    try {
                        denah.data = await api('GET', `${DENAH_URL}/${kamarId}/denah`);
                    } catch (e) {
                        denah.error = e.message;
                    } finally {
                        denah.loading = false;
                    }
                }

                // Nomor kasur 1..kapasitas (ditambah nomor lebih besar bila kapasitas pernah dikurangi)
                function daftarKasur() {
                    const d = denah.data;
                    const maks = Math.max(d.kamar.kapasitas, ...d.penghuni.map((p) => p.nomor_kasur || 0));
                    return Array.from({ length: maks }, (_, i) => i + 1);
                }
                // Jumlah kolom denah: ikut isian "Kasur per baris" di form (langsung terlihat saat diubah)
                const kolomDenah = () => Math.min(Math.max(Number(modal.form.kasur_per_baris) || denah.data.kamar.kasur_per_baris || 4, 1), 12);
                const penghuniKasur = (n) => denah.data.penghuni.find((p) => p.nomor_kasur === n) || null;
                const santriTanpaKasur = () => denah.data.penghuni.filter((p) => !p.nomor_kasur);
                const kasurTerisi = () => denah.data.penghuni.filter((p) => p.nomor_kasur && p.nomor_kasur <= denah.data.kamar.kapasitas).length;
                const inisial = (nama) => (nama || '?').split(/\s+/).slice(0, 2).map((k) => k[0]).join('').toUpperCase();

                function pilihKasur(n) {
                    Object.assign(denah, { pilih: denah.pilih === n ? null : n, santriId: '', error: '' });
                }

                // Simpan hasil dari server, beri notifikasi, perbarui angka "Terisi" di tabel kamar
                async function kirimDenah(method, url, body = null) {
                    denah.busy = true;
                    denah.error = '';
                    try {
                        const { message, ...data } = await api(method, url, body);
                        denah.data = data;
                        Object.assign(denah, { pilih: null, santriId: '' });
                        notify(message);
                        reload('kamar');
                    } catch (e) {
                        const errors = e.data?.errors;
                        denah.error = errors ? Object.values(errors)[0][0] : e.message;
                    } finally {
                        denah.busy = false;
                    }
                }

                const tempatkanSantri = () => kirimDenah('POST', `${DENAH_URL}/${modal.item.id}/kasur`, {
                    santri_id: denah.santriId,
                    nomor_kasur: denah.pilih,
                });
                const kosongkanKasur = (santri, keluar) => kirimDenah('DELETE',
                    `${DENAH_URL}/${modal.item.id}/kasur/${santri.id}` + (keluar ? '?keluar=1' : ''));

                // ---------- Aktif / Nonaktif ----------
                async function toggle(row) {
                    row.is_active = !row.is_active;
                    try {
                        notify((await api('PATCH', `/${activeKey.value}/${row.id}/toggle`)).message);
                    } catch (e) {
                        row.is_active = !row.is_active;
                        notify(e.message, 'error');
                    }
                }

                // ---------- Hapus ----------
                const confirm = reactive({
                    open: false,
                    row: null,
                    busy: false
                });

                function askDelete(row) {
                    Object.assign(confirm, {
                        open: true,
                        row,
                        busy: false
                    });
                }

                async function doDelete() {
                    const key = activeKey.value;
                    confirm.busy = true;
                    try {
                        const res = await api('DELETE', `/${key}/${confirm.row.id}`);
                        rows[key] = rows[key].filter((r) => r.id !== confirm.row.id);
                        confirm.open = false;
                        notify(res.message);
                    } catch (e) {
                        notify(e.message, 'error');
                    } finally {
                        confirm.busy = false;
                    }
                }

                // ---------- Seret untuk mengubah urutan ----------
                const drag = reactive({
                    armedId: null,
                    from: null,
                    overIndex: null,
                    before: null
                });

                function dragStart(index, e) {
                    drag.from = index;
                    drag.before = rows[activeKey.value].map((r) => r.id);
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', String(index)); // wajib untuk Firefox
                }

                function dragEnter(index) {
                    if (drag.from === null || index === drag.from) return;
                    const list = rows[activeKey.value];
                    const [moved] = list.splice(drag.from, 1);
                    list.splice(index, 0, moved);
                    drag.from = index;
                    drag.overIndex = index;
                }

                async function dragEnd() {
                    const key = activeKey.value;
                    const before = drag.before;
                    const ids = rows[key].map((r) => r.id);
                    Object.assign(drag, {
                        armedId: null,
                        from: null,
                        overIndex: null,
                        before: null
                    });
                    if (!before || ids.join() === before.join()) return;

                    try {
                        notify((await api('POST', `/${key}/reorder`, {
                            ids
                        })).message);
                    } catch (e) {
                        rows[key].sort((a, b) => before.indexOf(a.id) - before.indexOf(b.id));
                        notify(e.message, 'error');
                    }
                }

                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        modal.open = false;
                        confirm.open = false;
                    }
                });

                return {
                    TABS,
                    activeKey,
                    rows,
                    modal,
                    confirm,
                    toast,
                    drag,
                    show,
                    colorHex,
                    timeRange,
                    periodeMasehi,
                    periodeHijriyah,
                    isiHijriyah,
                    totalData,
                    denah,
                    daftarKasur,
                    kolomDenah,
                    penghuniKasur,
                    santriTanpaKasur,
                    kasurTerisi,
                    inisial,
                    pilihKasur,
                    tempatkanSantri,
                    kosongkanKasur,
                    selectTab,
                    openModal,
                    save,
                    toggle,
                    askDelete,
                    doDelete,
                    dragStart,
                    dragEnter,
                    dragEnd,
                };
            },
        }).mount('#master-data-app');
    </script>
@endpush
