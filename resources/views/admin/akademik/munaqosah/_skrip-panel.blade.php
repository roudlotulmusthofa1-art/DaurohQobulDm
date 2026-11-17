{{--
    Munaqosah: data & fungsi Vue BERSAMA untuk panel _panel-isian / _panel-nilai / _panel-dakwah / _panel-catatan.
    Dipakai halaman detail (Lajnah) dan modal panel di halaman Munaqosah (Musyrifin) lewat
        mixins: [window.MUNAQOSAH_PANEL]
    Tulisan panel ada di "tp" (teks panel) supaya tidak bentrok dengan "teks" milik halaman.
    Include di AWAL @section('content') supaya skrip ini dimuat sebelum skrip halaman.
--}}
@push('scripts')
<script>
    (function () {
        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        window.MUNAQOSAH_PANEL = {
            data: () => ({
                BAGIAN_NILAI: [
                    { kunci: 'harian', label: @js(__('Harian')), titik: 'bg-sky-500' },
                    { kunci: 'lisan', label: @js(__('Lisan')), titik: 'bg-violet-500' },
                    { kunci: 'ujian', label: @js(__('Ujian')), titik: 'bg-orange-500' },
                ],
                SARING_CATATAN: [
                    { kunci: '', label: @js(__('Semua')) },
                    { kunci: 'pencapaian', label: @js(__('Pencapaian')) },
                    { kunci: 'pelanggaran', label: @js(__('Pelanggaran')) },
                ],
                lajnah: false,
                isian: {},       // { ilmu: { kolom_id: nilai }, ... } yang sedang diisi
                tersimpan: {},   // isian tersimpan dari server { ilmu: [ {kolom_id, label, jenis, nilai} ], ... }
                nilai: { persen: {}, akhir: null, rincian: { harian: [], lisan: [], ujian: [] } },
                aspek: [],
                dakwah: [],
                jenisDakwah: {},
                catatan: [],
                saringCatatan: '',
                tp: {
                    lisan: @js(__('Ujian Lisan')), ujian: @js(__('Ujian')), nilaiKecil: @js(__('nilai')),
                    kosongLisan: @js(__('Belum ada nilai ujian lisan.')), kosongUjian: @js(__('Belum ada nilai ujian.')),
                    oleh: @js(__('oleh')), pilih: @js(__('— Pilih —')),
                },
            }),
            computed: {
                catatanTampil() {
                    return this.catatan.filter((c) => !this.saringCatatan || c.jenis === this.saringCatatan);
                },
            },
            methods: {
                persen: (v) => (v === null || v === undefined ? '–' : (Math.round(v * 10) / 10) + '%'),
                tanggal: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}` : '–'),
                dakwahPer(j) { return this.dakwah.filter((n) => n.jenis === j); },
                jumlahCatatan(j) { return j ? this.catatan.filter((c) => c.jenis === j).length : this.catatan.length; },
                // Isian lama dari kolom yang sudah dihapus admin (tetap ditampilkan)
                isianLama(b) { return (this.tersimpan[b] || []).filter((x) => x.dihapus); },
                tampilNilai(v) {
                    if (Array.isArray(v)) return v.length ? v.join(', ') : '–';
                    return v === null || v === undefined || v === '' ? '–' : String(v);
                },
                // Isian awal untuk formulir: { ilmu: { kolom_id: nilai }, ... } dari isian tersimpan
                isianDari(kolom, tersimpan) {
                    const awal = {};
                    Object.keys(kolom).forEach((b) => {
                        const lama = {};
                        ((tersimpan || {})[b] || []).forEach((x) => { lama[x.kolom_id] = x.nilai; });
                        awal[b] = {};
                        kolom[b].forEach((k) => {
                            const v = lama[k.id];
                            awal[b][k.id] = k.jenis === 'centang' ? (Array.isArray(v) ? [...v] : []) : (v ?? '');
                        });
                    });
                    return awal;
                },
            },
        };
    })();
</script>
@endpush
