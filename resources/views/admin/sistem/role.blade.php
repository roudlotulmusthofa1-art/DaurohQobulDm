{{--
    Admin > Sistem > Role & Permission (khusus Super Admin)
    Kiri  : daftar role (klik untuk memilih).
    Kanan : form role terpilih + centang menu yang boleh dibuka (izin).
    Semua tampilan diatur Vue (data dari controller lewat @json), form tetap dikirim biasa (POST/PUT).
--}}
@extends('layouts.admin')

@section('title', 'Role & Permission')

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
@endphp

<div id="halaman-role" class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-6 mb-10">
    @foreach (['success' => 'bg-emerald-50 ring-emerald-200 text-emerald-900', 'error' => 'bg-rose-50 ring-rose-200 text-rose-800'] as $kunciFlash => $gayaFlash)
    @if (session($kunciFlash))
    <div class="p-3.5 rounded-2xl ring-1 text-sm {{ $gayaFlash }}">{{ session($kunciFlash) }}</div>
    @endif
    @endforeach
    @if ($errors->any())
    <div class="p-3.5 rounded-2xl ring-1 ring-rose-200 bg-rose-50 text-rose-800 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ===== Judul ===== --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">Sistem</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Role & Permission</h1>
                <p class="mt-1.5 text-sm text-white/70 max-w-2xl">Buat role pengurus (mis. Bendahara, Sekretaris) lalu centang menu admin yang boleh dibuka. Berikan role ke akun admin lewat menu <b>Daftar User</b>.</p>
            </div>
            <button type="button" @click="tambahRole"
                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-[#0b332c] text-sm font-bold hover:bg-emerald-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Role
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,320px)_minmax(0,1fr)] gap-6 items-start">
        {{-- ===== Daftar role ===== --}}
        <div class="{{ $kartu }} p-3 space-y-1.5">
            <p class="px-2 pt-1 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Daftar Role</p>
            <button v-for="r in roles" :key="r.id" type="button" @click="pilih(r)"
                class="w-full flex items-center gap-3 p-3 rounded-2xl text-left transition-colors"
                :class="terpilih?.id === r.id ? 'bg-[#0b332c]/[0.06] ring-1 ring-[#0b332c]/15' : 'hover:bg-slate-50'">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center font-bold ring-1 shrink-0" :class="gaya(r.warna).lencana" v-text="r.nama.charAt(0).toUpperCase()"></span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-center gap-1.5">
                        <span class="font-semibold text-slate-800 truncate" v-text="r.nama"></span>
                        <span v-if="r.bawaan" class="text-[10px] px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 font-semibold">bawaan</span>
                    </span>
                    <span class="block text-xs text-slate-500" v-text="ringkasIzin(r)"></span>
                </span>
                <span class="text-xs font-semibold text-slate-500 tabular-nums" :title="r.jumlah + ' akun'" v-text="r.jumlah"></span>
            </button>
            <div v-if="terpilih && !terpilih.id" class="w-full flex items-center gap-3 p-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center font-bold ring-1 shrink-0" :class="gaya(form.warna).lencana" v-text="(form.nama || '?').charAt(0).toUpperCase()"></span>
                <span class="font-semibold text-emerald-800 truncate" v-text="form.nama || 'Role baru'"></span>
            </div>
        </div>

        {{-- ===== Form role terpilih ===== --}}
        <form v-if="terpilih" method="POST" :action="aksiForm" class="{{ $kartu }} p-5 sm:p-6 space-y-6" @input="diubah = true" @change="diubah = true" @submit="diubah = false">
            @csrf
            <input v-if="terpilih.id" type="hidden" name="_method" value="PUT">
            <input type="hidden" name="role_form" :value="terpilih.id ?? 'baru'">
            <input type="hidden" name="warna" :value="form.warna">

            <div class="flex flex-wrap items-start gap-3">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-slate-800" v-text="terpilih.id ? 'Ubah Role' : 'Role Baru'"></h2>
                    <p class="text-sm text-slate-500" v-text="terpilih.id ? terpilih.jumlah + ' akun memakai role ini' : 'Isi nama lalu centang menu yang boleh dibuka.'"></p>
                </div>
                <span v-if="diubah" class="text-xs px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200 font-semibold">Belum disimpan</span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Nama role</span>
                    <input type="text" name="nama" v-model="form.nama" required maxlength="80" placeholder="mis. Bendahara"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                </label>
                <div>
                    <span class="text-sm font-semibold text-slate-700">Warna lencana</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button v-for="w in daftarWarna" :key="w" type="button" @click="form.warna = w; diubah = true" :title="w"
                            class="w-8 h-8 rounded-full ring-2 ring-offset-2 transition" :class="[gaya(w).titik, form.warna === w ? 'ring-slate-700' : 'ring-transparent']"></button>
                    </div>
                </div>
                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Keterangan <span class="font-normal text-slate-400">(opsional)</span></span>
                    <input type="text" name="keterangan" v-model="form.keterangan" maxlength="255" placeholder="Tugas singkat role ini"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500">
                </label>
            </div>

            {{-- Izin menu --}}
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <h3 class="font-bold text-slate-800 flex-1">Permission (menu yang boleh dibuka)</h3>
                    <template v-if="bisaAturIzin">
                        <span class="text-xs text-slate-500" v-text="form.izin.length + ' dari ' + totalIzin + ' menu'"></span>
                        <button type="button" @click="aturSemua(true)" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">Pilih semua</button>
                        <button type="button" @click="aturSemua(false)" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">Kosongkan</button>
                    </template>
                </div>

                {{-- Role bawaan: izinnya tetap --}}
                <div v-if="!bisaAturIzin" class="p-4 rounded-2xl bg-slate-50 ring-1 ring-slate-200 text-sm text-slate-600">
                    <template v-if="terpilih.kunci === 'admin'">Super Admin selalu bisa membuka <b>semua menu</b>, termasuk halaman Role & Permission ini.</template>
                    <template v-else-if="terpilih.kunci === 'asatid'">Akun asatid membuka <b>dasbor asatid</b> (jadwal, absensi, peserta, penilaian), bukan panel admin.</template>
                    <template v-else>Akun peserta membuka <b>dasbor siswa</b> (profil, kelulusan, pesan), bukan panel admin.</template>
                </div>

                <div v-else class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    <div v-for="grup in daftarIzin" :key="grup.label" class="rounded-2xl ring-1 ring-slate-200 p-3.5">
                        <label class="flex items-center gap-2 pb-2 mb-2 border-b border-slate-100 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded accent-emerald-600"
                                :checked="statusGrup(grup) === 'semua'" :indeterminate.prop="statusGrup(grup) === 'sebagian'"
                                @change="aturGrup(grup, $event.target.checked)">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600" v-text="grup.label"></span>
                        </label>
                        <label v-for="i in grup.izin" :key="i.kunci" class="flex items-center gap-2.5 py-1.5 cursor-pointer text-sm text-slate-700 hover:text-slate-900">
                            <input type="checkbox" name="izin[]" :value="i.kunci" v-model="form.izin" class="w-4 h-4 rounded accent-emerald-600">
                            <span v-text="i.label"></span>
                        </label>
                    </div>
                </div>
                <p v-if="bisaAturIzin && !form.izin.length" class="mt-3 text-xs text-amber-700">Belum ada menu dicentang: akun dengan role ini hanya bisa membuka Dashboard.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="terpilih.id && !diubah"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span v-text="terpilih.id ? 'Simpan Perubahan' : 'Tambah Role'"></span>
                </button>
                <button type="button" v-if="diubah || !terpilih.id" @click="batal" class="px-4 py-2.5 rounded-2xl text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <span class="flex-1"></span>
                <button v-if="terpilih.id && !terpilih.bawaan" type="button" @click="hapus"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-sm font-semibold text-rose-600 hover:bg-rose-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Hapus Role
                </button>
            </div>
        </form>
    </div>

    {{-- Form hapus (dikirim lewat Vue setelah konfirmasi) --}}
    <form ref="formHapus" method="POST" :action="terpilih?.id ? urlRole(terpilih.id) : ''" class="hidden">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Kelas Tailwind ditulis lengkap (bukan disusun "bg-" + warna) supaya ikut dibuat oleh Tailwind
        const WARNA = {
            emerald: { lencana: 'bg-emerald-50 text-emerald-700 ring-emerald-200', titik: 'bg-emerald-500' },
            sky:     { lencana: 'bg-sky-50 text-sky-700 ring-sky-200',             titik: 'bg-sky-500' },
            amber:   { lencana: 'bg-amber-50 text-amber-700 ring-amber-200',       titik: 'bg-amber-500' },
            violet:  { lencana: 'bg-violet-50 text-violet-700 ring-violet-200',    titik: 'bg-violet-500' },
            rose:    { lencana: 'bg-rose-50 text-rose-700 ring-rose-200',          titik: 'bg-rose-500' },
            indigo:  { lencana: 'bg-indigo-50 text-indigo-700 ring-indigo-200',    titik: 'bg-indigo-500' },
            teal:    { lencana: 'bg-teal-50 text-teal-700 ring-teal-200',          titik: 'bg-teal-500' },
            slate:   { lencana: 'bg-slate-100 text-slate-700 ring-slate-200',      titik: 'bg-slate-500' },
        };
        const URL_ROLE = @json(url('admin/role'));

        Vue.createApp({
            data() {
                return {
                    roles: @json($roles),
                    daftarIzin: @json($daftarIzin),
                    daftarWarna: @json($warna),
                    terpilih: null,   // role yang sedang dibuka di kanan ({ id: null } = role baru)
                    form: { nama: '', keterangan: '', warna: 'violet', izin: [] },
                    diubah: false,
                };
            },
            computed: {
                aksiForm() {
                    return this.terpilih?.id ? this.urlRole(this.terpilih.id) : URL_ROLE;
                },
                // Role bawaan (Super Admin / Asatid / Peserta) tidak punya centang izin
                bisaAturIzin() {
                    return this.terpilih && !this.terpilih.bawaan;
                },
                totalIzin() {
                    return this.daftarIzin.reduce((n, g) => n + g.izin.length, 0);
                },
            },
            methods: {
                ...metodeHalaman,
                gaya(warna) {
                    return WARNA[warna] ?? WARNA.slate;
                },
                urlRole(id) {
                    return URL_ROLE + '/' + id;
                },
                ringkasIzin(r) {
                    if (r.kunci === 'admin') return 'Semua menu';
                    if (r.bawaan) return r.kunci === 'asatid' ? 'Dasbor asatid' : 'Dasbor siswa';
                    return r.izin.length ? r.izin.length + ' menu admin' : 'Hanya dashboard';
                },
                bolehPindah() {
                    return !this.diubah || confirm('Perubahan belum disimpan. Tinggalkan?');
                },
                pilih(r) {
                    if (this.terpilih?.id === r.id || !this.bolehPindah()) return;
                    this.terpilih = r;
                    this.form = { nama: r.nama, keterangan: r.keterangan ?? '', warna: r.warna, izin: [...r.izin] };
                    this.diubah = false;
                    history.replaceState(null, '', '?role=' + r.id);
                },
                tambahRole() {
                    if (!this.bolehPindah()) return;
                    this.terpilih = { id: null, bawaan: false, kunci: '' };
                    this.form = { nama: '', keterangan: '', warna: 'violet', izin: [] };
                    this.diubah = false;
                    this.$nextTick(() => document.querySelector('#halaman-role input[name=nama]')?.focus());
                },
                // Kembalikan isian ke data tersimpan (role baru dibatalkan -> buka role pertama)
                batal() {
                    const r = this.terpilih?.id ? this.terpilih : this.roles[0];
                    this.diubah = false;
                    this.terpilih = null;
                    this.pilih(r);
                },
                hapus() {
                    if (!confirm('Hapus role ' + this.terpilih.nama + '?')) return;
                    this.diubah = false;
                    this.$refs.formHapus.submit();
                },
                // Centang / kosongkan semua menu
                aturSemua(nyala) {
                    this.form.izin = nyala ? this.daftarIzin.flatMap(g => g.izin.map(i => i.kunci)) : [];
                    this.diubah = true;
                },
                statusGrup(grup) {
                    const n = grup.izin.filter(i => this.form.izin.includes(i.kunci)).length;
                    return n === 0 ? 'kosong' : (n === grup.izin.length ? 'semua' : 'sebagian');
                },
                aturGrup(grup, nyala) {
                    const kunci = grup.izin.map(i => i.kunci);
                    this.form.izin = this.form.izin.filter(k => !kunci.includes(k));
                    if (nyala) this.form.izin.push(...kunci);
                    this.diubah = true;
                },
            },
            mounted() {
                window.addEventListener('beforeunload', (e) => { if (this.diubah) e.preventDefault(); });

                // Gagal validasi: buka lagi form yang tadi diisi
                const lama = @json(old('role_form'));
                if (lama) {
                    const r = lama === 'baru' ? null : this.roles.find(x => String(x.id) === String(lama));
                    r ? this.pilih(r) : this.tambahRole();
                    this.form = {
                        nama: @json(old('nama', '')),
                        keterangan: @json(old('keterangan', '')) ?? '',
                        warna: @json(old('warna', 'violet')),
                        izin: @json(old('izin', [])),
                    };
                    this.diubah = true;
                    return;
                }
                const id = new URLSearchParams(location.search).get('role');
                this.pilih(this.roles.find(r => String(r.id) === id) ?? this.roles[0]);
            },
        }).mount('#halaman-role');
    });
</script>
@endpush
