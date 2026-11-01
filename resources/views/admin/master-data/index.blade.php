@extends('layouts.admin')



@section('content')
    <div id="master-data-app" v-cloak>
        <div class="mx-4 md:mx-10 lg:mx-20 mt-8">
            <p class="mb-1 font-bold text-xl text-[#222722f0]">Master Data</p>
            <p class="mb-5 text-sm text-gray-500">Kelola data referensi dan kategori sistem</p>

            {{-- ================= Tab ================= --}}
            <div class="mb-6 overflow-x-auto">
                <div class="inline-flex gap-1 rounded-xl bg-white p-1 shadow-sm" role="tablist">
                    @foreach ($tabs as $key => $t)
                        <a href="?tab={{ $key }}" role="tab"
                            class="whitespace-nowrap rounded-lg px-6 py-2 text-center text-sm font-medium transition sm:min-w-36"
                            :class="activeKey === '{{ $key }}' ? 'bg-[#0b332c] text-white shadow-sm' :
                                'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            @click.prevent="selectTab('{{ $key }}')">{{ $t['label'] }}</a>
                    @endforeach
                </div>
            </div>

            {{-- ================= Isi tiap tab ================= --}}
            @foreach ($tabs as $key => $t)
                <section v-if="activeKey === '{{ $key }}'">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-[#0f1f1a]">{{ $t['title'] }}</h2>
                            <p class="text-sm text-gray-500">{{ $t['description'] }}</p>
                        </div>
                        <button type="button"
                            class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-[#0b332c] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0f4a3f]"
                            @click="openModal()">
                            <x-master-data.icon name="plus" />
                            {{ $t['add_label'] }}
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-left text-[13px] font-medium text-gray-500">
                                        <th class="w-10 px-4 py-3"></th>
                                        <th class="w-20 px-3 py-3">Urutan</th>
                                        @foreach ($t['columns'] as $col)
                                            <th class="px-3 py-3 {{ $col['type'] === 'count' ? 'text-center' : '' }}">
                                                {{ $col['label'] }}</th>
                                        @endforeach
                                        <th class="w-20 px-3 py-3 text-center">Status</th>
                                        <th class="w-24 px-3 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Kosong --}}
                                    <tr v-if="!rows['{{ $key }}'].length">
                                        <td colspan="{{ count($t['columns']) + 4 }}"
                                            class="px-4 py-14 text-center text-gray-500">
                                            <x-master-data.icon name="inbox" size="32"
                                                class="mx-auto mb-2 text-gray-300" />
                                            Belum ada data {{ strtolower($t['singular']) }}.
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
                                                title="Seret untuk mengubah urutan" @mousedown="drag.armedId = row.id"
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
                                                :title="row.is_active ? 'Aktif — klik untuk nonaktifkan' :
                                                    'Nonaktif — klik untuk aktifkan'"
                                                @click="toggle(row)">
                                                <span v-if="row.is_active"><x-master-data.icon name="eye" /></span>
                                                <span v-else><x-master-data.icon name="eye-off" /></span>
                                            </button>
                                        </td>
                                        <td class="px-3 py-4">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button"
                                                    class="rounded p-1 text-blue-600 transition hover:bg-blue-50"
                                                    title="Edit" @click="openModal(row)">
                                                    <x-master-data.icon name="pencil" />
                                                </button>
                                                <button type="button"
                                                    class="rounded p-1 text-red-600 transition hover:bg-red-50"
                                                    title="Hapus" @click="askDelete(row)">
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
                <form id="master-data-form" class="w-full max-w-lg rounded-2xl bg-white shadow-xl" @submit.prevent="save">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-[#0f1f1a]"
                            v-text="(modal.item ? 'Edit ' : 'Tambah ') + TABS[activeKey].singular"></h3>
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

                    {{-- Status aktif (sama untuk semua tab) --}}
                    <div class="px-6 py-5">
                        <label
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                            <span>
                                <span class="block text-sm font-medium text-gray-700">Aktif</span>
                                <span class="block text-xs text-gray-500">Data nonaktif tidak muncul di pilihan form
                                    lain.</span>
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
                            Batal
                        </button>
                        <button type="submit" :disabled="modal.saving"
                            class="rounded-xl bg-[#0b332c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0f4a3f] disabled:cursor-not-allowed disabled:opacity-60"
                            v-text="modal.saving ? 'Menyimpan…' : 'Simpan'"></button>
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
                        v-text="'Hapus ' + TABS[activeKey].singular.toLowerCase() + '?'"></h3>
                    <p class="mt-1 text-center text-sm text-gray-500"
                        v-text="confirm.row ? '“' + confirm.row.nama + '” akan dihapus permanen.' : ''"></p>
                    <div class="mt-6 grid grid-cols-2 gap-2">
                        <button type="button"
                            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            @click="confirm.open = false">Batal</button>
                        <button type="button" :disabled="confirm.busy"
                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            @click="doDelete" v-text="confirm.busy ? 'Menghapus…' : 'Hapus'"></button>
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
        const CSRF = @json(csrf_token());
        const TABS = @json($tabs);
        const COLORS = @json($colors);

        /** Panggil API JSON. Melempar error berisi status & data bila gagal. */
        async function api(method, path = '', body = null) {
            const res = await fetch(BASE_URL + path, {
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
                const message = res.status === 419 ? 'Sesi habis, silakan muat ulang halaman.' : (data.message ||
                    'Terjadi kesalahan. Coba lagi.');
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
                        form[f.key] = item?.[f.key] ?? f.default ?? '';
                    }
                    Object.assign(modal, {
                        open: true,
                        item,
                        form,
                        errors: {},
                        saving: false
                    });
                    setTimeout(() => document.querySelector(
                            '#master-data-form input:not([type=checkbox]), #master-data-form textarea')
                    ?.focus());
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
