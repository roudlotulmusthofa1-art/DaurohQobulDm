{{--
    Akun Asatid > Hafalan (Asatid\HafalanController@index). Dipakai juga admin: Admin > Akademik > Hafalan > Nilai Hafalan
    (Admin\HafalanController@nilai: $tataLetak, $rute, $bungkus, $kembaliAdmin).
    Urutan: 1) pilih halaqoh  2) pilih santri  3) pilih kitab (hanya kitab dari Pengaturan Hafalan admin)
            4) tabel SEMUA setoran sebelumnya + baris baru: tanggal, halaman dari-sampai, ragu-ragu, kesalahan, nilai -> Simpan
            5) tombol paling bawah: tandai "sudah hafal seluruh kitab" (bisa dibatalkan).
    Aksi JSON di trait App\Http\Controllers\Concerns\AksiHafalan. Asatid hanya mengubah / menghapus baris miliknya sendiri.
    ?kelas=&siswa=&kitab= langsung membuka santri & kitab itu (tombol "Buka" di laporan admin).
    Vue: #isi-hafalan.
--}}
@extends($tataLetak ?? 'layouts.asatid')

@section('judul', __('Hafalan'))
@section('title', __('Nilai Hafalan'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3.5 py-2.5 text-[15px] text-slate-800 focus:ring-2 focus:ring-teal-500 outline-none';
$nomor = 'w-6 h-6 rounded-full bg-[#0b332c] text-white text-xs flex items-center justify-center';
$ikonBuku = 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25';
@endphp

<div id="isi-hafalan" v-cloak class="space-y-5 pb-10 {{ $bungkus ?? '' }}">
    @isset($kembaliAdmin)
    <a href="{{ $kembaliAdmin }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        {{ __('Kembali ke Laporan Hafalan') }}
    </a>
    @endisset

    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-6">
        <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 w-64 h-64 rounded-full bg-teal-400/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-black/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonBuku }}"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Setoran') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ __('Hafalan') }}</h2>
                <p class="text-sm text-white/70">{{ __('Pilih halaqoh, santri, dan kitab. Riwayat setoran tetap terlihat, tinggal tambah baris berikutnya.') }}</p>
            </div>
        </div>
    </section>

    @if (! count($kitabList))
    <p class="p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada kitab hafalan. Minta admin memilih kitabnya di Akademik › Hafalan › Pengaturan.') }}</p>
    @endif

    {{-- ===== 1. Halaqoh ===== --}}
    <section class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">1</span>{{ __('Pilih halaqoh') }}</h3>
        <p v-if="!kelasList.length" class="mt-3 text-sm text-slate-400">{{ __('Belum ada halaqoh aktif.') }}</p>
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
            <button v-for="k in kelasList" :key="k.id" type="button" @click="pilihKelas(k.id)"
                class="rounded-2xl px-3 py-2.5 text-left ring-1 transition"
                :class="kelasId === k.id ? 'bg-teal-600 text-white ring-teal-600' : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
                <span class="block text-sm font-bold truncate" v-text="k.nama"></span>
                <span class="block text-[11px]" :class="kelasId === k.id ? 'text-white/75' : 'text-slate-400'" v-text="k.santri_count + ' ' + teks.santri"></span>
            </button>
        </div>
    </section>

    {{-- ===== 2. Santri ===== --}}
    <section v-if="kelasId" class="{{ $kartu }} p-5">
        <div class="flex flex-wrap items-center gap-3">
            <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">2</span>{{ __('Pilih santri') }}</h3>
            <input v-if="peserta.length > 6" type="search" v-model="cari" placeholder="{{ __('Cari nama…') }}" class="ml-auto w-full sm:w-56 {{ $input }} py-2 text-sm">
        </div>
        <p v-if="memuatPeserta" class="mt-3 text-sm text-slate-400" v-text="teks.memuat"></p>
        <p v-else-if="!peserta.length" class="mt-3 text-sm text-slate-400">{{ __('Tidak ada santri di halaqoh ini.') }}</p>
        <div v-else class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <button v-for="s in pesertaTampil" :key="s.id" type="button" @click="pilihSiswa(s)"
                class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-left ring-1 transition"
                :class="siswa && siswa.id === s.id ? 'bg-teal-50 ring-teal-400' : 'bg-white ring-slate-200 hover:bg-slate-50'">
                <img v-if="s.foto" :src="s.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-teal-400 to-[#0b332c] text-white font-extrabold flex items-center justify-center" v-text="inisial(s.nama)"></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-slate-800 truncate" v-text="s.nama"></span>
                    <x-nama-arab vue="s.arab" />
                    <span class="block text-[11px] text-slate-400 truncate" v-text="s.nomor"></span>
                </span>
                <span v-if="jumlahHafal(s)" class="shrink-0 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white" v-text="'✓ ' + jumlahHafal(s)"></span>
            </button>
        </div>
    </section>

    {{-- ===== 3. Kitab ===== --}}
    <section v-if="siswa" class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">3</span>{{ __('Pilih kitab yang dihafal') }}</h3>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <button v-for="k in kitabList" :key="k.id" type="button" @click="pilihKitab(k.id)"
                class="rounded-2xl px-3.5 py-3 text-left ring-1 transition"
                :class="kitabId === k.id ? 'bg-teal-600 text-white ring-teal-600' : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50'">
                <span class="flex items-center gap-2">
                    <span class="min-w-0 flex-1 text-sm font-bold truncate" v-text="k.nama"></span>
                    <span v-if="ringkas(siswa, k.id).selesai" class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold" :class="kitabId === k.id ? 'bg-white text-emerald-700' : 'bg-emerald-500 text-white'" v-text="teks.hafal"></span>
                </span>
                <span v-if="k.arab" dir="rtl" class="block text-base leading-snug" :class="kitabId === k.id ? 'text-white/85' : 'text-slate-500'" v-text="k.arab"></span>
                <span class="block text-[11px]" :class="kitabId === k.id ? 'text-white/75' : 'text-slate-400'" v-text="keteranganKitab(siswa, k.id)"></span>
            </button>
        </div>
    </section>

    {{-- ===== 4. Riwayat + baris baru ===== --}}
    <section v-if="siswa && kitabId" class="{{ $kartu }} p-5">
        <h3 class="flex items-center gap-2 font-bold text-slate-800"><span class="{{ $nomor }}">4</span>
            <span v-text="teks.setoran + ' ' + namaKitab + ' · ' + siswa.nama"></span></h3>

        <p v-if="memuatRiwayat" class="mt-4 text-sm text-slate-400" v-text="teks.memuat"></p>
        <template v-else>
            <div v-if="riwayat.selesai" class="mt-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 px-4 py-3 text-sm text-emerald-800">
                <b>✓ {{ __('Sudah hafal seluruh kitab ini.') }}</b>
                <span class="block text-xs text-emerald-700" v-text="teks.ditandai + ' ' + (riwayat.selesai.oleh || '–') + ' · ' + waktu(riwayat.selesai.waktu)"></span>
            </div>

            <p v-if="!riwayat.baris.length" class="mt-4 rounded-2xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-400">{{ __('Belum ada setoran. Isi baris pertama di bawah.') }}</p>
            <div v-else class="mt-4 -mx-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-2 font-semibold">#</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Tanggal') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Halaman') }}</th>
                            <th class="px-2 py-2 font-semibold text-center">{{ __('Ragu') }}</th>
                            <th class="px-2 py-2 font-semibold text-center">{{ __('Salah') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Nilai') }}</th>
                            <th class="px-2 py-2 font-semibold">{{ __('Penilai') }}</th>
                            <th class="px-5 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(b, i) in riwayat.baris" :key="b.id" :class="form.id === b.id ? 'bg-amber-50' : ''">
                            <td class="px-5 py-2.5 text-slate-400 tabular-nums" v-text="i + 1"></td>
                            <td class="px-2 py-2.5 whitespace-nowrap text-slate-700" v-text="tanggal(b.tanggal)"></td>
                            <td class="px-2 py-2.5 whitespace-nowrap font-semibold text-slate-800 tabular-nums" v-text="b.dari + ' – ' + b.sampai"></td>
                            <td class="px-2 py-2.5 text-center tabular-nums" :class="b.ragu ? 'text-amber-600 font-bold' : 'text-slate-400'" v-text="b.ragu"></td>
                            <td class="px-2 py-2.5 text-center tabular-nums" :class="b.salah ? 'text-rose-600 font-bold' : 'text-slate-400'" v-text="b.salah"></td>
                            <td class="px-2 py-2.5"><span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-700 ring-1 ring-teal-100 whitespace-nowrap" v-text="b.nilai"></span></td>
                            <td class="px-2 py-2.5 text-xs text-slate-500 whitespace-nowrap" v-text="b.oleh || '–'"></td>
                            <td class="px-5 py-2.5 whitespace-nowrap text-right">
                                <template v-if="b.boleh">
                                    <button type="button" @click="ubah(b)" class="text-xs font-semibold text-teal-700 hover:underline">{{ __('Ubah') }}</button>
                                    <button type="button" @click="hapus(b)" class="ml-2 text-xs font-semibold text-rose-600 hover:underline">{{ __('Hapus') }}</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Baris baru / ubah baris --}}
            <div v-if="!riwayat.selesai || form.id" class="mt-4 rounded-2xl ring-1 p-4" :class="form.id ? 'bg-amber-50/60 ring-amber-200' : 'bg-[#f8fbfa] ring-slate-200'">
                <p class="text-xs font-bold uppercase tracking-wider" :class="form.id ? 'text-amber-700' : 'text-teal-700'" v-text="form.id ? teks.ubahBaris : teks.barisBaru"></p>
                <div class="mt-3 grid grid-cols-2 md:grid-cols-6 gap-3">
                    <label class="block col-span-2 md:col-span-1"><span class="text-xs font-semibold text-slate-500">{{ __('Tanggal') }}</span>
                        <input type="date" v-model="form.tanggal" class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Halaman dari') }}</span>
                        <input type="number" inputmode="numeric" min="0" max="9999" v-model.number="form.dari" class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Sampai') }}</span>
                        <input type="number" inputmode="numeric" min="0" max="9999" v-model.number="form.sampai" class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Ragu-ragu (kali)') }}</span>
                        <input type="number" inputmode="numeric" min="0" max="999" v-model.number="form.ragu" class="mt-1 {{ $input }}"></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kesalahan') }}</span>
                        <input type="number" inputmode="numeric" min="0" max="999" v-model.number="form.salah" class="mt-1 {{ $input }}"></label>
                    <label class="block col-span-2 md:col-span-1"><span class="text-xs font-semibold text-slate-500">{{ __('Nilai') }}</span>
                        <select v-model="form.nilai" class="mt-1 {{ $input }}">
                            <option value="" v-text="teks.pilihNilai"></option>
                            <option v-for="n in nilaiPilihan" :key="n" :value="n" v-text="n"></option>
                        </select></label>
                </div>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <button v-if="form.id" type="button" @click="formBaru" class="px-4 py-2.5 rounded-2xl ring-1 ring-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('Batal ubah') }}</button>
                    <button type="button" @click="simpan" :disabled="menyimpan"
                        class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                        v-text="menyimpan ? teks.menyimpan : (form.id ? teks.simpanUbah : teks.simpan)"></button>
                </div>
            </div>

            {{-- 5. Tanda hafal seluruh kitab --}}
            <div class="mt-5 border-t border-slate-100 pt-5">
                <button v-if="!riwayat.selesai" type="button" @click="tandai(true)" :disabled="menandai || !riwayat.baris.length"
                    class="w-full rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-600/20 hover:brightness-105 disabled:opacity-40"
                    v-text="menandai ? teks.menyimpan : teks.tandai"></button>
                <button v-else type="button" @click="tandai(false)" :disabled="menandai"
                    class="w-full rounded-2xl ring-1 ring-rose-200 bg-white py-3 text-sm font-bold text-rose-600 hover:bg-rose-50 disabled:opacity-40"
                    v-text="menandai ? teks.menyimpan : teks.batalTanda"></button>
                <p v-if="!riwayat.selesai && !riwayat.baris.length" class="mt-2 text-center text-[11px] text-slate-400">{{ __('Tombol ini aktif setelah ada setoran.') }}</p>
            </div>
        </template>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const CSRF = @json(csrf_token());
        const RUTE = {
            peserta: @json($rute['peserta'] ?? route('asatid.hafalan.peserta')),
            riwayat: @json($rute['riwayat'] ?? route('asatid.hafalan.riwayat')),
            simpan: @json($rute['simpan'] ?? route('asatid.hafalan.simpan')),
            hapus: @json($rute['hapus'] ?? route('asatid.hafalan.hapus', '__ID__')),
            selesai: @json($rute['selesai'] ?? route('asatid.hafalan.selesai')),
        };
        async function kirimJson(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.errors ? Object.values(json.errors)[0][0] : (json.message || @js(__('Gagal menyimpan. Coba lagi.'))));
            return json;
        }
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const gagal = (pesan) => (window.notifikasi ? window.notifikasi.gagal(pesan) : alert(pesan));
        const sukses = (pesan) => window.notifikasi?.sukses(pesan);

        Vue.createApp({
            data: () => ({
                kelasList: @json($kelasList),
                kitabList: @json($kitabList),
                nilaiList: @json($nilaiList),
                hariIni: @js($hariIni),
                awal: @json($awal),
                kelasId: null,
                peserta: [],
                memuatPeserta: false,
                cari: '',
                siswa: null,
                kitabId: null,
                riwayat: { baris: [], selesai: null },
                memuatRiwayat: false,
                form: {},
                menyimpan: false,
                menandai: false,
                teks: {
                    santri: @js(__('santri')), memuat: @js(__('Memuat…')), hafal: @js(__('✓ Hafal')),
                    setoran: @js(__('Setoran')), ditandai: @js(__('Ditandai oleh')),
                    belumSetor: @js(__('Belum ada setoran')), kaliSetor: @js(__('setoran')), sampaiHal: @js(__('sampai hal.')),
                    barisBaru: @js(__('Baris baru')), ubahBaris: @js(__('Ubah baris')), pilihNilai: @js(__('— Pilih nilai —')),
                    simpan: @js(__('Simpan baris ini')), simpanUbah: @js(__('Simpan perubahan')), menyimpan: @js(__('Menyimpan…')),
                    tandai: @js(__('✓ Tandai: santri ini sudah hafal seluruh kitab ini')),
                    batalTanda: @js(__('Batalkan tanda hafal')),
                    yakinTandai: @js(__('Tandai :nama sudah hafal SELURUH kitab :kitab?')),
                    yakinBatal: @js(__('Batalkan tanda hafal kitab ini?')),
                    yakinHapus: @js(__('Hapus baris setoran ini?')),
                    halamanKosong: @js(__('Isi halaman dari dan sampai.')),
                    gagalMuat: @js(__('Gagal memuat data. Coba lagi.')),
                },
            }),
            computed: {
                pesertaTampil() {
                    const k = this.cari.trim().toLowerCase();
                    return this.peserta.filter((s) => !k || (s.nama || '').toLowerCase().includes(k) || (s.nomor || '').toLowerCase().includes(k));
                },
                namaKitab() { return (this.kitabList.find((k) => k.id === this.kitabId) || {}).nama || ''; },
                // Pilihan nilai + nilai lama baris yang sedang diubah (bila pilihannya sudah dihapus admin)
                nilaiPilihan() {
                    return this.form.nilai && !this.nilaiList.includes(this.form.nilai) ? [...this.nilaiList, this.form.nilai] : this.nilaiList;
                },
            },
            async mounted() {
                this.formBaru();
                // Dibuka dari tombol "Buka" di laporan admin
                if (this.awal.kelas && this.kelasList.some((k) => k.id === this.awal.kelas)) {
                    await this.pilihKelas(this.awal.kelas);
                    const s = this.peserta.find((x) => x.id === this.awal.siswa);
                    if (s) {
                        this.pilihSiswa(s);
                        if (this.kitabList.some((k) => k.id === this.awal.kitab)) this.pilihKitab(this.awal.kitab);
                    }
                }
            },
            methods: {
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                tanggal: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}` : '–'),
                waktu: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}, ${t.slice(11, 16)}` : '–'),
                ringkas(s, kitabId) { return (s && s.kitab && s.kitab[kitabId]) || {}; },
                jumlahHafal(s) { return this.kitabList.filter((k) => this.ringkas(s, k.id).selesai).length; },
                keteranganKitab(s, kitabId) {
                    const r = this.ringkas(s, kitabId);
                    if (!r.jumlah) return this.teks.belumSetor;
                    return r.jumlah + ' ' + this.teks.kaliSetor + ' · ' + this.teks.sampaiHal + ' ' + r.halaman;
                },
                async pilihKelas(id) {
                    this.kelasId = id;
                    this.siswa = null;
                    this.kitabId = null;
                    this.cari = '';
                    this.peserta = [];
                    this.memuatPeserta = true;
                    try {
                        const json = await kirimJson(RUTE.peserta + '?kelas_id=' + id, 'GET');
                        if (this.kelasId === id) this.peserta = json.peserta;
                    } catch (e) {
                        gagal(this.teks.gagalMuat);
                    } finally {
                        this.memuatPeserta = false;
                    }
                },
                pilihSiswa(s) {
                    this.siswa = s;
                    this.kitabId = null;
                    // Satu kitab saja -> langsung dipilih
                    if (this.kitabList.length === 1) this.pilihKitab(this.kitabList[0].id);
                },
                async pilihKitab(id) {
                    this.kitabId = id;
                    this.riwayat = { baris: [], selesai: null };
                    this.memuatRiwayat = true;
                    const siswaId = this.siswa.id;
                    try {
                        const json = await kirimJson(RUTE.riwayat + '?siswa_id=' + siswaId + '&kitab_id=' + id, 'GET');
                        if (this.kitabId === id && this.siswa && this.siswa.id === siswaId) this.terima(json);
                    } catch (e) {
                        gagal(this.teks.gagalMuat);
                    } finally {
                        this.memuatRiwayat = false;
                    }
                },
                // Data riwayat dari server -> tabel, ringkasan kartu santri/kitab, formulir baris baru
                terima(json) {
                    this.riwayat = { baris: json.baris, selesai: json.selesai };
                    const b = json.baris;
                    this.siswa.kitab = { ...(this.siswa.kitab || {}) };
                    this.siswa.kitab[this.kitabId] = {
                        jumlah: b.length,
                        halaman: b.length ? Math.max(...b.map((x) => x.sampai)) : null,
                        nilai: b.length ? b[b.length - 1].nilai : null,
                        selesai: !!json.selesai,
                    };
                    this.formBaru();
                },
                // Baris baru: tanggal hari ini, halaman "dari" melanjutkan halaman terakhir
                formBaru() {
                    const b = this.riwayat.baris;
                    const terakhir = b.length ? Math.max(...b.map((x) => x.sampai)) : 0;
                    this.form = { id: null, tanggal: this.hariIni, dari: terakhir ? terakhir + 1 : 1, sampai: null, ragu: 0, salah: 0, nilai: '' };
                },
                ubah(b) {
                    this.form = { id: b.id, tanggal: b.tanggal, dari: b.dari, sampai: b.sampai, ragu: b.ragu, salah: b.salah, nilai: b.nilai };
                },
                async simpan() {
                    const f = this.form;
                    if (f.dari === '' || f.dari === null || f.sampai === '' || f.sampai === null) return gagal(this.teks.halamanKosong);
                    this.menyimpan = true;
                    try {
                        const json = await kirimJson(RUTE.simpan, 'POST', {
                            id: f.id, siswa_id: this.siswa.id, kitab_id: this.kitabId, tanggal: f.tanggal,
                            halaman_dari: f.dari, halaman_sampai: f.sampai, ragu: f.ragu || 0, salah: f.salah || 0, nilai: f.nilai,
                        });
                        this.terima(json);
                        sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menyimpan = false;
                    }
                },
                async hapus(b) {
                    if (!confirm(this.teks.yakinHapus)) return;
                    try {
                        const json = await kirimJson(RUTE.hapus.replace('__ID__', b.id), 'DELETE');
                        this.terima(json);
                        sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    }
                },
                async tandai(selesai) {
                    const tanya = selesai ? this.teks.yakinTandai.replace(':nama', this.siswa.nama).replace(':kitab', this.namaKitab) : this.teks.yakinBatal;
                    if (!confirm(tanya)) return;
                    this.menandai = true;
                    try {
                        const json = await kirimJson(RUTE.selesai, 'POST', { siswa_id: this.siswa.id, kitab_id: this.kitabId, selesai });
                        this.terima(json);
                        sukses(json.pesan);
                    } catch (e) {
                        gagal(e.message);
                    } finally {
                        this.menandai = false;
                    }
                },
            },
        }).mount('#isi-hafalan');
    });
</script>
@endpush
