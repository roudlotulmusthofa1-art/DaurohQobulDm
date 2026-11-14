{{--
    Admin > Penilaian > Kelas & Pelajaran (PenilaianController@kelas).
    Tiga halaman dalam satu menu: Nilai Harian · Ujian Lisan · Ujian, pindah lewat tombol di atas (seperti Master Data).
    - Nilai Harian: saringan + rekap per santri + semua nilai (isi di partials/nilai-harian.blade.php)
    - Ujian Lisan: pengaturan + beri nilai + semua nilai (isi di partials/ujian-lisan.blade.php)
    - Ujian: pengaturan ujian + ringkasan per kitab + rekap nilai dari asatid (isi di partials/ujian.blade.php, Vue lewat mixins)
    Vue: #halaman-penilaian-kelas (tab aktif + alamat ?tab=... ikut berubah).
--}}
@extends('layouts.admin')

@section('title', __('Kelas & Pelajaran'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$bagian = [
    'harian' => [
        'label' => __('Nilai Harian'),
        'ket'   => __('Nilai harian peserta per kelas & pelajaran yang diisi asatid setelah mengajar.'),
        'warna' => 'from-sky-400 to-blue-600',
        'aksen' => '#0284c7',
        'ikon'  => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
    ],
    'lisan' => [
        'label' => __('Ujian Lisan'),
        'ket'   => __('Ujian dengan pertanyaan lisan per fan & kitab, dinilai asatid atau admin.'),
        'warna' => 'from-violet-400 to-purple-600',
        'aksen' => '#7c3aed',
        'ikon'  => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z',
    ],
    'ujian' => [
        'label' => __('Ujian'),
        'ket'   => __('Nilai ujian tulis peserta per kelas & pelajaran.'),
        'warna' => 'from-amber-400 to-orange-500',
        'aksen' => '#d97706',
        'ikon'  => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    ],
];
@endphp

<div id="halaman-penilaian-kelas" v-cloak>
<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-8">
    <x-header-admin :label="__('Penilaian')" :judul="__('Kelas & Pelajaran')"
        :keterangan="__('Nilai harian, ujian lisan, dan ujian peserta yang diisi asatid.')" />

    {{-- ===== Tombol pindah halaman (seperti Master Data) ===== --}}
    <div class="-mx-1 overflow-x-auto px-1 pb-2">
        <div class="inline-flex gap-1.5 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5" role="tablist">
            @foreach ($bagian as $kunci => $b)
            <a href="?tab={{ $kunci }}" role="tab" style="--aksen: {{ $b['aksen'] }}"
                :aria-selected="tab === '{{ $kunci }}'"
                @click.prevent="pilih('{{ $kunci }}')"
                class="group flex shrink-0 items-center gap-2.5 whitespace-nowrap rounded-xl py-1.5 pl-1.5 pr-3 text-sm font-medium transition-all duration-200"
                :class="tab === '{{ $kunci }}'
                    ? 'bg-[linear-gradient(135deg,#0f4238,#0b332c)] text-white shadow-md shadow-[#0b332c]/25'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110"
                    :class="tab === '{{ $kunci }}'
                        ? 'bg-(--aksen) text-white shadow-[0_4px_12px_-2px_var(--aksen)]'
                        : 'bg-[color-mix(in_srgb,var(--aksen)_14%,white)] text-(--aksen)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $b['ikon'] }}"/></svg>
                </span>
                {{ $b['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- ===== Nilai Harian ===== --}}
    @include('admin.penilaian.partials.nilai-harian')

    {{-- ===== Ujian Lisan ===== --}}
    @include('admin.penilaian.partials.ujian-lisan')

    {{-- ===== Ujian (tulis): pengaturan + rekap nilai ===== --}}
    @include('admin.penilaian.partials.ujian')
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const HAPUS = @json(route('admin.penilaian.nilai-harian.hapus', ['nilai' => '__ID__']));
        const UBAH_HARIAN = @json(route('admin.penilaian.nilai-harian.ubah', ['nilai' => '__ID__']));
        const CSRF = @json(csrf_token());
        const ASPEK = @json($aspek);
        const kecil = (t) => (t || '').toString().toLowerCase();
        // Ujian Lisan
        const RUTE_LISAN = {
            ujian: @json(route('admin.penilaian.ujian-lisan.simpan')),
            hapusUjian: @json(route('admin.penilaian.ujian-lisan.hapus', ['ujian' => '__ID__'])),
            nilai: @json(route('admin.penilaian.nilai-lisan.simpan')),
            hapusNilai: @json(route('admin.penilaian.nilai-lisan.hapus', ['nilai' => '__ID__'])),
        };
        const HARI_INI = @json(now('Asia/Jakarta')->toDateString());
        const ujianKosong = () => ({ id: null, bidang_ilmu_id: '', kitab_id: '', jumlah: 5, rata: 20, maks: [20, 20, 20, 20, 20], is_active: true });

        // Kirim JSON; pesan galat validasi pertama ditampilkan
        async function kirim(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: body ? JSON.stringify(body) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const galat = json.errors ? Object.values(json.errors)[0][0] : null;
                throw new Error(galat || json.message || @js(__('Gagal menyimpan.')));
            }
            return json;
        }
        const gagal = (e) => (window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message));

        Vue.createApp({
            mixins: [window.UJIAN_TULIS, window.UJIAN_SOAL], // tab Ujian: panel Nilai (partials/ujian) + panel Soal Ujian (partials/ujian-soal)
            data: () => ({
                tab: @json($tab),
                aspek: ASPEK,
                nilai: @json($nilai),
                tampil: 'rekap',   // rekap | daftar
                ubahHarian: null,  // nilai harian yang sedang diubah (salinan)
                cari: '',
                menghapus: null,
                // Ujian Lisan
                ujianLisan: @json($ujianLisan),
                nilaiLisan: @json($nilaiLisan),
                fanList: @json($fanList),
                kitabFan: @json($kitabFan),
                siswaList: @json($siswaList),
                teksPertanyaan: @js(__('pertanyaan')),
                teksPertanyaanKe: @js(__('Pertanyaan')),
                teksMaks: @js(__('maks')),
                saringFanLisan: '',
                cariLisan: '',
                modalAturLisan: false,
                modalNilaiLisan: false,
                formUjian: ujianKosong(),
                formLisan: { siswa_id: null, ujian_lisan_id: null, tanggal: HARI_INI, nilai: [], catatan: '' },
                cariSantriLisan: '',
                adaNilaiLama: false,
                menyimpan: false,
            }),
            computed: {
                totalUbahHarian() {
                    return this.ubahHarian ? ASPEK.reduce((t, a) => t + (Number(this.ubahHarian.nilai[a.kunci]) || 0), 0) : 0;
                },
                // ===== Ujian Lisan =====
                ujianLisanAktif() { return this.ujianLisan.filter((u) => u.is_active); },
                ujianLisanTerpilih() { return this.ujianLisan.find((u) => u.id === this.formLisan.ujian_lisan_id) || null; },
                kitabDiFan() { return this.kitabFan.filter((k) => k.bidang_ilmu_id === Number(this.formUjian.bidang_ilmu_id)); },
                totalFormUjian() { return this.formUjian.maks.reduce((n, m) => n + (Number(m) || 0), 0); },
                totalNilaiLisan() { return this.formLisan.nilai.reduce((n, v) => n + (Number(v) || 0), 0); },
                santriLisan() { return this.siswaList.find((s) => s.siswa_id === this.formLisan.siswa_id) || null; },
                santriLisanCari() {
                    const k = kecil(this.cariSantriLisan.trim());
                    const hasil = k ? this.siswaList.filter((s) => kecil(s.siswa).includes(k) || kecil(s.nomor).includes(k) || kecil(s.kelas).includes(k)) : this.siswaList;
                    return hasil.slice(0, 50);
                },
                fanDiNilai() { return [...new Set(this.nilaiLisan.map((n) => n.fan).filter(Boolean))].sort(); },
                nilaiLisanTersaring() {
                    const k = kecil(this.cariLisan.trim());
                    return this.nilaiLisan.filter((n) => (!this.saringFanLisan || n.fan === this.saringFanLisan)
                        && (!k || kecil(n.siswa).includes(k) || kecil(n.nomor).includes(k) || kecil(n.kitab).includes(k) || kecil(n.catatan).includes(k)));
                },
                rataLisan() {
                    const d = this.nilaiLisanTersaring;
                    return d.length ? d.reduce((n, x) => n + x.persen, 0) / d.length : 0;
                },
                // ===== Nilai Harian =====
                nilaiTersaring() {
                    const k = kecil(this.cari.trim());
                    return k ? this.nilai.filter((n) => kecil(n.siswa).includes(k) || kecil(n.nomor).includes(k) || kecil(n.kitab).includes(k)) : this.nilai;
                },
                // Rekap per santri: jumlah penilaian, rata-rata total, rata-rata tiap penilaian
                rekap() {
                    const peta = {};
                    this.nilaiTersaring.forEach((n) => {
                        const r = peta[n.siswa_id] ||= { siswa_id: n.siswa_id, siswa: n.siswa, arab: n.arab, nomor: n.nomor, foto: n.foto, kelas: n.kelas, jumlah: 0, total: 0, aspek: {} };
                        r.jumlah++;
                        r.total += n.total;
                        ASPEK.forEach((a) => { r.aspek[a.kunci] = (r.aspek[a.kunci] || 0) + (n.nilai[a.kunci] || 0); });
                    });
                    return Object.values(peta).map((r) => ({
                        ...r,
                        rata: r.total / r.jumlah,
                        rataAspek: Object.fromEntries(ASPEK.map((a) => [a.kunci, r.aspek[a.kunci] / r.jumlah])),
                    })).sort((a, b) => b.rata - a.rata);
                },
                rataSemua() {
                    return this.nilaiTersaring.length ? this.nilaiTersaring.reduce((n, x) => n + x.total, 0) / this.nilaiTersaring.length : 0;
                },
            },
            methods: {
                bulat: (n) => (Math.round(n * 10) / 10).toLocaleString('id-ID'),
                inisial: (n) => (n || '?').trim().charAt(0).toUpperCase(),
                warnaTotal(t) {
                    if (t >= 80) return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                    if (t >= 60) return 'bg-amber-50 text-amber-700 ring-amber-200';
                    return 'bg-rose-50 text-rose-700 ring-rose-200';
                },
                bukaUbahHarian(n) {
                    this.ubahHarian = { id: n.id, siswa: n.siswa, kitab: n.kitab, bulan: n.bulan, nilai: { ...n.nilai } };
                },
                async simpanUbahHarian() {
                    const u = this.ubahHarian;
                    const lewat = ASPEK.find((a) => !(u.nilai[a.kunci] >= 0 && u.nilai[a.kunci] <= a.maks));
                    if (lewat) return gagal(new Error(@js(__(':nama: nilai 0 sampai :maks.')).replace(':nama', lewat.label).replace(':maks', lewat.maks)));
                    this.menyimpan = true;
                    try {
                        const json = await kirim(UBAH_HARIAN.replace('__ID__', u.id), 'PUT', u.nilai);
                        this.nilai = this.nilai.map((x) => (x.id === u.id ? json.baris : x));
                        this.ubahHarian = null;
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                async hapus(n) {
                    const tanya = @js(__('Hapus nilai harian :nama (:kitab, :bulan)?'));
                    if (!confirm(tanya.replace(':nama', n.siswa).replace(':kitab', n.kitab || '-').replace(':bulan', n.bulan || n.tanggal))) return;
                    this.menghapus = n.id;
                    try {
                        const res = await fetch(HAPUS.replace('__ID__', n.id), {
                            method: 'DELETE',
                            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(json.message || @js(__('Gagal menghapus.')));
                        this.nilai = this.nilai.filter((x) => x.id !== n.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) {
                        window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                    } finally {
                        this.menghapus = null;
                    }
                },
                /* ===== Ujian Lisan: pengaturan ===== */
                judulUjian: (u) => [u.fan || '-', u.kitab].filter(Boolean).join(' · '),
                ujianAktif(id) { return this.ujianLisanAktif.some((u) => u.id === id); },
                tglPanjang: (t) => (t ? new Date(t + 'T12:00:00').toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) : '-'),
                bukaAturLisan() {
                    this.formUjianBaru();
                    this.modalAturLisan = true;
                },
                formUjianBaru() { this.formUjian = ujianKosong(); },
                // Jumlah pertanyaan berubah: kotak nilai ditambah (diisi nilai "semua") / dikurangi
                aturJumlah(nilai) {
                    const n = Math.max(0, Math.min({{ \App\Models\UjianLisan::MAKS_PERTANYAAN }}, Number(nilai) || 0));
                    const m = this.formUjian.maks.slice(0, n);
                    while (m.length < n) m.push(Number(this.formUjian.rata) || 1);
                    this.formUjian.maks = m;
                },
                isiSemuaNilai(nilai) {
                    const r = Number(nilai) || 1;
                    this.formUjian.maks = this.formUjian.maks.map(() => r);
                },
                ubahUjian(u) {
                    this.formUjian = {
                        id: u.id, bidang_ilmu_id: u.bidang_ilmu_id || '', kitab_id: u.kitab_id || '',
                        jumlah: u.maks.length, rata: u.maks[0] || 1, maks: [...u.maks], is_active: u.is_active,
                    };
                },
                async kirimUjian(isi) {
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE_LISAN.ujian, 'POST', isi);
                        this.ujianLisan = json.ujian;
                        window.notifikasi?.sukses(json.message);
                        return true;
                    } catch (e) {
                        gagal(e);
                        return false;
                    } finally {
                        this.menyimpan = false;
                    }
                },
                async simpanUjian() {
                    const f = this.formUjian;
                    if (!f.maks.length) return gagal(new Error(@js(__('Isi jumlah pertanyaan.'))));
                    const ok = await this.kirimUjian({
                        id: f.id, bidang_ilmu_id: Number(f.bidang_ilmu_id), kitab_id: f.kitab_id ? Number(f.kitab_id) : null,
                        nilai_pertanyaan: f.maks.map(Number), is_active: f.is_active,
                    });
                    if (ok) this.formUjianBaru();
                },
                aktifkanUjian(u) {
                    this.kirimUjian({ id: u.id, bidang_ilmu_id: u.bidang_ilmu_id, kitab_id: u.kitab_id, nilai_pertanyaan: u.maks, is_active: !u.is_active });
                },
                async hapusUjian(u) {
                    const tanya = @js(__('Hapus ujian :nama? Nilai yang sudah ada tetap tersimpan.'));
                    if (!confirm(tanya.replace(':nama', this.judulUjian(u)))) return;
                    try {
                        const json = await kirim(RUTE_LISAN.hapusUjian.replace('__ID__', u.id), 'DELETE');
                        this.ujianLisan = json.ujian;
                        if (this.formUjian.id === u.id) this.formUjianBaru();
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); }
                },

                /* ===== Ujian Lisan: beri nilai (admin) ===== */
                bukaNilaiLisan() {
                    this.formLisan = { siswa_id: null, ujian_lisan_id: null, tanggal: HARI_INI, nilai: [], catatan: '' };
                    this.cariSantriLisan = '';
                    this.adaNilaiLama = false;
                    this.modalNilaiLisan = true;
                },
                ubahNilaiLisan(n) {
                    this.formLisan = { siswa_id: n.siswa_id, ujian_lisan_id: n.ujian_id, tanggal: n.tanggal, nilai: [], catatan: '' };
                    this.modalNilaiLisan = true;
                    this.isiNilaiLama();
                },
                pilihSantriLisan(id) { this.formLisan.siswa_id = id; this.isiNilaiLama(); },
                pilihUjianLisan(id) { this.formLisan.ujian_lisan_id = id; this.isiNilaiLama(); },
                // Kalau santri + ujian + tanggal ini sudah dinilai, nilainya dimunculkan (tinggal diperbarui)
                isiNilaiLama() {
                    const u = this.ujianLisanTerpilih;
                    if (!u) { this.formLisan.nilai = []; this.formLisan.catatan = ''; return; }
                    const lama = this.nilaiLisan.find((n) => n.siswa_id === this.formLisan.siswa_id && n.ujian_id === u.id && n.tanggal === this.formLisan.tanggal);
                    this.adaNilaiLama = !!lama;
                    this.formLisan.nilai = u.maks.map((m, i) => (lama && lama.nilai[i] !== undefined ? Math.min(lama.nilai[i], m) : ''));
                    this.formLisan.catatan = lama?.catatan || '';
                },
                // Angka di luar 0..maks langsung dikembalikan ke batasnya
                batasiNilai(i, m, e) {
                    if (e.target.value === '') { this.formLisan.nilai[i] = ''; return; }
                    let v = parseInt(e.target.value, 10);
                    if (Number.isNaN(v)) v = 0;
                    v = Math.max(0, Math.min(m, v));
                    this.formLisan.nilai[i] = v;
                    if (String(e.target.value) !== String(v)) e.target.value = v;
                },
                async simpanNilaiLisan() {
                    this.menyimpan = true;
                    try {
                        const json = await kirim(RUTE_LISAN.nilai, 'POST', { ...this.formLisan });
                        this.nilaiLisan = [json.baris, ...this.nilaiLisan.filter((n) => n.id !== json.baris.id)]
                            .sort((a, b) => b.tanggal.localeCompare(a.tanggal) || b.id - a.id);
                        this.ujianLisan.forEach((u) => { u.jumlah_nilai = this.nilaiLisan.filter((n) => n.ujian_id === u.id).length; });
                        window.notifikasi?.sukses(json.message);
                        this.modalNilaiLisan = false;
                    } catch (e) { gagal(e); } finally { this.menyimpan = false; }
                },
                async hapusNilaiLisan(n) {
                    const tanya = @js(__('Hapus nilai ujian lisan :nama (:fan, :tanggal)?'));
                    if (!confirm(tanya.replace(':nama', n.siswa).replace(':fan', n.fan || '-').replace(':tanggal', n.tanggal))) return;
                    this.menghapus = 'lisan-' + n.id;
                    try {
                        const json = await kirim(RUTE_LISAN.hapusNilai.replace('__ID__', n.id), 'DELETE');
                        this.nilaiLisan = this.nilaiLisan.filter((x) => x.id !== n.id);
                        window.notifikasi?.sukses(json.message);
                    } catch (e) { gagal(e); } finally { this.menghapus = null; }
                },

                // Pindah halaman tanpa memuat ulang; alamat ?tab=... ikut berubah (bisa dibagikan / dimuat ulang)
                pilih(kunci) {
                    this.tab = kunci;
                    const u = new URL(location.href);
                    u.searchParams.set('tab', kunci);
                    history.replaceState(null, '', u);
                },
            },
        }).mount('#halaman-penilaian-kelas');
    });
</script>
@endpush
