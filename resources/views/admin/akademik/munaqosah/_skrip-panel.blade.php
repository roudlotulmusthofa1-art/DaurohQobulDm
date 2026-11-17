{{--
    Munaqosah: data & fungsi Vue BERSAMA untuk panel _panel-isian / _panel-nilai / _panel-dakwah / _panel-absensi / _panel-catatan.
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
                // Panel 6 Detail Absensi: status => label + warna kotak & lencana
                // (kelas warna ditulis utuh supaya ikut dibuat Tailwind)
                STATUS_ABSENSI: [
                    { kunci: 'hadir', label: @js(__('Hadir')), kotak: 'bg-emerald-50 text-emerald-700 ring-emerald-200', aktif: 'bg-emerald-600 text-white ring-emerald-600' },
                    { kunci: 'terlambat', label: @js(__('Terlambat')), kotak: 'bg-amber-50 text-amber-700 ring-amber-200', aktif: 'bg-amber-500 text-white ring-amber-500' },
                    { kunci: 'sakit', label: @js(__('Sakit')), kotak: 'bg-sky-50 text-sky-700 ring-sky-200', aktif: 'bg-sky-600 text-white ring-sky-600' },
                    { kunci: 'izin', label: @js(__('Izin')), kotak: 'bg-violet-50 text-violet-700 ring-violet-200', aktif: 'bg-violet-600 text-white ring-violet-600' },
                    { kunci: 'alfa', label: @js(__('Alfa')), kotak: 'bg-rose-50 text-rose-700 ring-rose-200', aktif: 'bg-rose-600 text-white ring-rose-600' },
                ],
                absensi: [],
                saringAbsensi: '',
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
                    kaliAbsen: @js(__('kali absen')), dicatat: @js(__('Dicatat oleh')),
                },
            }),
            computed: {
                catatanTampil() {
                    return this.catatan.filter((c) => !this.saringCatatan || c.jenis === this.saringCatatan);
                },
                absensiTampil() {
                    return this.absensi.filter((a) => !this.saringAbsensi || a.status === this.saringAbsensi);
                },
                // Persen kehadiran = (hadir + terlambat) / semua absensi
                persenHadir() {
                    if (!this.absensi.length) return null;
                    return (this.jumlahAbsensi('hadir') + this.jumlahAbsensi('terlambat')) / this.absensi.length * 100;
                },
            },
            methods: {
                persen: (v) => (v === null || v === undefined ? '–' : (Math.round(v * 10) / 10) + '%'),
                tanggal: (t) => (t ? `${Number(t.slice(8, 10))} ${BULAN[Number(t.slice(5, 7)) - 1]} ${t.slice(0, 4)}` : '–'),
                dakwahPer(j) { return this.dakwah.filter((n) => n.jenis === j); },
                jumlahAbsensi(s) { return s ? this.absensi.filter((a) => a.status === s).length : this.absensi.length; },
                statusAbsensi(s) { return this.STATUS_ABSENSI.find((x) => x.kunci === s) || { label: s, kotak: 'bg-slate-50 text-slate-600 ring-slate-200' }; },
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
