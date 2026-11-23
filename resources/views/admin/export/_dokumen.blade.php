{{--
    Kerangka dokumen Export (Print, PDF, Word).
    CSS ditulis di sini, bukan Tailwind, karena mPDF dan Microsoft Word tidak bisa membaca kelas Tailwind.
    Tailwind + Vue hanya dipakai untuk bilah tombol di halaman Print.
--}}
<!DOCTYPE html>
<html lang="id" @if ($format === 'word') xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} · {{ $profil['nama'] }}</title>
    @if ($format === 'cetak')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @if ($format === 'word')
        @page Bagian1 { size: {{ $mendatar ? '841.9pt 595.3pt' : '595.3pt 841.9pt' }}; mso-page-orientation: {{ $mendatar ? 'landscape' : 'portrait' }}; margin: 36pt; }
        div.Bagian1 { page: Bagian1; }
        @elseif ($format === 'cetak')
        @page { size: A4 {{ $mendatar ? 'landscape' : 'portrait' }}; margin: 10mm; }
        @endif
        body { font-family: dejavusans, Arial, sans-serif; font-size: 10pt; color: #1e293b; }
        {{-- xbriyaz = font Arab bawaan mPDF (PDF); Print memakai Amiri, Word memakai Traditional Arabic bila Amiri tidak terpasang --}}
        .arab { font-family: xbriyaz, "Amiri Kitab", Amiri, "Traditional Arabic", serif; font-size: 13pt; }
        table.kop { width: 100%; border-collapse: collapse; border-bottom: 2px solid #0b332c; margin-bottom: 10px; }
        table.kop td { border: 0; padding: 0 0 6px; vertical-align: middle; }
        .kop-nama { font-size: 15pt; font-weight: bold; color: #0b332c; }
        .kop-alamat { font-size: 8.5pt; color: #475569; }
        h1.judul { font-size: 13pt; margin: 4px 0 1px; text-align: center; color: #0b332c; }
        .keterangan { font-size: 8.5pt; color: #475569; text-align: center; margin: 0 0 10px; }
        h2.grup { font-size: 11pt; margin: 12px 0 5px; color: #0b332c; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background-color: #0b332c; color: #ffffff; border: 1px solid #0b332c; padding: 5px; font-size: 9pt; text-align: left; }
        table.data td { border: 1px solid #94a3b8; padding: 4px 5px; font-size: 9pt; vertical-align: top; }
        table.data tr { page-break-inside: avoid; break-inside: avoid; }
        table.jadwal th, table.jadwal td { font-size: 8pt; }
        .tengah { text-align: center; }
        .kitab { font-weight: bold; }
        .kecil { font-size: 7.5pt; color: #475569; }
        .slot { margin-bottom: 4px; }
        .kosong { padding: 20px; text-align: center; color: #64748b; }
        .halaman-baru { page-break-before: always; break-before: page; }
        @if ($format === 'cetak')
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .kertas { width: {{ $mendatar ? '277mm' : '190mm' }}; max-width: 100%; }
        @media print {
            body { background: #fff !important; }
            .kertas { width: auto; max-width: none; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
        }
        @endif
    </style>
</head>
<body @if ($format === 'cetak') class="bg-slate-200" @endif>
    @if ($format === 'cetak')
    <div id="bilah-cetak" class="print:hidden sticky top-0 z-10 bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <p class="font-bold text-slate-800">{{ $judul }}</p>
            <button type="button" @click="cetak" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">{{ __('Print') }}</button>
        </div>
    </div>
    @endif

    <div class="{{ $format === 'word' ? 'Bagian1' : 'kertas' }} {{ $format === 'cetak' ? 'mx-auto my-4 bg-white p-6 shadow' : '' }}">
        @yield('isi')
    </div>

    @if ($format === 'cetak')
    <script type="module">
        window.Vue.createApp({ methods: { cetak() { window.print(); } } }).mount('#bilah-cetak');
    </script>
    @endif
</body>
</html>
