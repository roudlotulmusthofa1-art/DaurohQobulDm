{{--
    Admin > Data Santri > Cetak Kartu QR (dibuka di tab baru, siap print A4).
    Dibuka dari: tombol "Cetak Kartu QR" (semua), panel Kelola Massal (yang dicentang), ikon QR di tiap baris (satu santri).
    Vue: pilih ukuran (Kartu ID 10/hal, Stiker 24/hal, Besar 4/hal), saring kelas, cari nama, lalu Cetak.
    Ukuran memakai milimeter supaya hasil print pas di kertas A4.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Kartu QR · {{ $profil['nama'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4; margin: 8mm; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .kartu-qr svg { width: 100%; height: 100%; display: block; }
        .kartu-cetak { break-inside: avoid; page-break-inside: avoid; }
        @media print {
            body { background: #fff !important; }
            .kertas { box-shadow: none !important; padding: 0 !important; margin: 0 !important; width: auto !important; min-height: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-200 text-slate-800 antialiased">
<div id="cetak-qr">
    {{-- ===== Bilah alat (tidak ikut tercetak) ===== --}}
    <header class="print:hidden sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-3 mr-auto">
                <img src="{{ $profil['logo_url'] }}" alt="" class="w-9 h-9 object-contain">
                <div>
                    <h1 class="font-extrabold text-slate-800 leading-tight">Cetak Kartu QR Santri</h1>
                    <p class="text-xs text-slate-500"><b v-text="tersaring.length"></b> kartu · <span v-text="jumlahHalaman"></span> halaman A4</p>
                </div>
            </div>

            {{-- Ukuran kartu --}}
            <div class="flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                <button v-for="(u, kunci) in UKURAN" :key="kunci" type="button" @click="ukuran = kunci"
                    class="px-3 py-1.5 rounded-lg transition" :class="ukuran === kunci ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    v-text="u.label"></button>
            </div>

            <select v-model="kelas" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Semua kelas</option>
                <option value="-">Belum ada kelas</option>
                <option v-for="k in daftarKelas" :key="k.id" :value="String(k.id)" v-text="k.nama"></option>
            </select>
            <input type="search" v-model.trim="cari" placeholder="Cari nama / nomor…" class="w-44 rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" v-model="tampilFoto" class="accent-emerald-600"> Foto
            </label>

            <button type="button" @click="cetak" :disabled="!tersaring.length"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 disabled:opacity-40 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Cetak
            </button>
        </div>
        <p class="max-w-6xl mx-auto px-4 pb-2 text-[11px] text-slate-400">
            Saat mencetak: kertas <b>A4</b>, skala <b>100%</b> (jangan "Fit to page"), dan centang <b>Background graphics</b> agar warna ikut tercetak.
            @if ($pilihan) Halaman ini dibuka untuk santri yang dipilih saja. @endif
        </p>
    </header>

    {{-- ===== Kertas A4 (pratinjau di layar = hasil cetak) ===== --}}
    <main class="kertas mx-auto my-6 bg-white shadow-xl w-[210mm] min-h-[297mm] p-[8mm]">
        <p v-if="!tersaring.length" class="py-24 text-center text-slate-400">Tidak ada santri yang cocok.</p>

        {{-- Kartu ID: 85.6 × 54 mm, 2 kolom × 5 baris = 10 per halaman --}}
        <div v-if="ukuran === 'kartu'" class="grid grid-cols-[85.6mm_85.6mm] justify-center gap-x-[4mm] gap-y-[2mm]">
            <div v-for="s in tersaring" :key="s.id" class="kartu-cetak w-[85.6mm] h-[54mm] rounded-[3mm] overflow-hidden border border-slate-300 bg-white flex flex-col">
                <div class="h-[10mm] px-[3mm] flex items-center gap-[2mm] bg-gradient-to-r from-[#135347] to-[#0b332c] text-white">
                    <img src="{{ $profil['logo_url'] }}" alt="" class="h-[7mm] w-[7mm] object-contain bg-white rounded-full p-[0.5mm]">
                    <span class="text-[2.6mm] font-bold tracking-wide truncate">{{ $profil['nama'] }}</span>
                    <span class="ml-auto text-[2.2mm] text-emerald-200 font-semibold">KARTU SANTRI</span>
                </div>
                <div class="flex-1 flex items-center gap-[3mm] px-[3mm]">
                    <div class="kartu-qr w-[34mm] h-[34mm] shrink-0" v-html="s.qr"></div>
                    <div class="min-w-0 flex-1">
                        <img v-if="tampilFoto && s.foto" :src="s.foto" alt="" class="mb-[1.5mm] w-[11mm] h-[13mm] object-cover rounded-[1mm] border border-slate-200">
                        <p class="text-[3.4mm] font-extrabold leading-tight line-clamp-2" v-text="s.nama"></p>
                        <p v-if="s.arab" class="text-[3.2mm] text-slate-600 leading-tight truncate" dir="rtl" v-text="s.arab"></p>
                        <p class="mt-[1mm] text-[2.5mm] font-semibold text-emerald-700" v-text="'No. ' + s.nomor"></p>
                        <p v-if="infoKelas(s)" class="text-[2.4mm] text-slate-500 leading-tight" v-text="infoKelas(s)"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stiker: 45 × 43 mm, 4 kolom × 6 baris = 24 per halaman (garis putus-putus untuk menggunting) --}}
        <div v-else-if="ukuran === 'stiker'" class="grid grid-cols-[repeat(4,45mm)] justify-center gap-[3mm]">
            <div v-for="s in tersaring" :key="s.id" class="kartu-cetak w-[45mm] h-[43mm] border border-dashed border-slate-300 rounded-[2mm] flex flex-col items-center justify-center px-[2mm]">
                <div class="kartu-qr w-[30mm] h-[30mm]" v-html="s.qr"></div>
                <p class="mt-[1mm] w-full text-center text-[2.6mm] font-bold leading-tight truncate" v-text="s.nama"></p>
                <p class="text-[2.2mm] text-slate-500" v-text="s.nomor"></p>
            </div>
        </div>

        {{-- Besar: 95 × 138 mm, 2 × 2 = 4 per halaman --}}
        <div v-else class="grid grid-cols-[95mm_95mm] justify-center gap-[3mm]">
            <div v-for="s in tersaring" :key="s.id" class="kartu-cetak w-[95mm] h-[138mm] rounded-[4mm] overflow-hidden border border-slate-300 flex flex-col items-center text-center bg-gradient-to-b from-[#135347] to-[#071f1a] text-white px-[6mm] py-[6mm]">
                <div class="flex items-center gap-[2mm]">
                    <img src="{{ $profil['logo_url'] }}" alt="" class="h-[8mm] w-[8mm] object-contain bg-white rounded-full p-[0.6mm]">
                    <span class="text-[3.2mm] font-bold tracking-wide">{{ $profil['nama'] }}</span>
                </div>
                <div class="mt-[5mm] bg-white rounded-[4mm] p-[4mm]">
                    <div class="kartu-qr w-[66mm] h-[66mm]" v-html="s.qr"></div>
                </div>
                <img v-if="tampilFoto && s.foto" :src="s.foto" alt="" class="mt-[4mm] w-[14mm] h-[17mm] object-cover rounded-[1.5mm] border-2 border-white/40">
                <p class="mt-[4mm] text-[5mm] font-extrabold leading-tight line-clamp-2" v-text="s.nama"></p>
                <p v-if="s.arab" class="text-[4.5mm] text-emerald-100/90" dir="rtl" v-text="s.arab"></p>
                <p class="mt-[1.5mm] text-[3.2mm] text-white/75" v-text="'No. ' + s.nomor"></p>
                <p v-if="infoKelas(s)" class="text-[3mm] text-amber-200" v-text="infoKelas(s)"></p>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            data() {
                return {
                    santri: @json($santri),
                    daftarKelas: @json($kelas),
                    UKURAN: {
                        kartu: { label: 'Kartu ID · 10/hal', perHalaman: 10 },
                        stiker: { label: 'Stiker · 24/hal', perHalaman: 24 },
                        besar: { label: 'Besar · 4/hal', perHalaman: 4 },
                    },
                    ukuran: 'kartu',
                    kelas: '',
                    cari: '',
                    tampilFoto: false,
                };
            },
            computed: {
                tersaring() {
                    const kata = this.cari.toLowerCase();
                    return this.santri.filter((s) =>
                        (this.kelas === '' || (this.kelas === '-' ? !s.kelas_id : String(s.kelas_id) === this.kelas)) &&
                        (!kata || s.nama.toLowerCase().includes(kata) || String(s.nomor).toLowerCase().includes(kata)));
                },
                jumlahHalaman() {
                    return Math.max(1, Math.ceil(this.tersaring.length / this.UKURAN[this.ukuran].perHalaman));
                },
            },
            methods: {
                infoKelas(s) {
                    return [s.kelas ? 'Kelas ' + s.kelas : '', s.kamar ? 'Kamar ' + s.kamar + (s.kasur ? ' · Kasur ' + s.kasur : '') : '']
                        .filter(Boolean).join(' · ');
                },
                cetak() {
                    window.print();
                },
            },
            mounted() {
                // Pilihan ukuran terakhir diingat di browser ini
                try {
                    const lama = localStorage.getItem('kartu_qr_ukuran');
                    if (lama && this.UKURAN[lama]) this.ukuran = lama;
                } catch (e) { /* abaikan */ }
            },
            watch: {
                ukuran(v) {
                    try { localStorage.setItem('kartu_qr_ukuran', v); } catch (e) { /* abaikan */ }
                },
            },
        }).mount('#cetak-qr');
    });
</script>
</body>
</html>
