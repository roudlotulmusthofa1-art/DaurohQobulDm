{{--
    Dasbor siswa: Kartu QR (dipindai asatid untuk absensi atau keperluan lain).
    Kode QR = kolom kode_qr peserta (dibuat otomatis saat mendaftar). Absensi hanya menerima peserta yang sudah DITERIMA.
    Tampil sebagai tombol melayang "QR Saya" (kanan bawah); diketuk -> panel muncul dari bawah (buka-tutup),
    jadi dasbor tetap hanya berisi sapaan + 4 menu.
    Vue: buka/tutup panel, layar penuh (layar HP dijaga tetap menyala), unduh kartu PNG, salin kode.
--}}
@php
use App\Models\PendaftaranSiswa;
$diterima = $siswa->status === PendaftaranSiswa::DITERIMA;
$infoQr = collect([$siswa->kelasMaster?->nama ? 'Kelas ' . $siswa->kelasMaster->nama : null, $siswa->kamarMaster?->nama ? 'Kamar ' . $siswa->kamarMaster->nama : null])->filter()->implode(' · ');
@endphp

@if ($siswa->kode_qr && $siswa->status !== PendaftaranSiswa::DITOLAK)
{{-- Ruang kosong supaya tombol melayang tidak menutupi kartu menu paling bawah --}}
<div class="h-20" aria-hidden="true"></div>

{{-- Tombol & panel dicetak di akhir <body> (lewat stack "scripts"), DI LUAR <main>:
     <main> punya animasi transform yang membuat position:fixed tidak menempel ke layar. --}}
@push('scripts')
<style>
    /* Panel QR: HP -> meluncur dari bawah, layar lebar -> muncul membesar di tengah */
    .panel-qr[open] { animation: panel-qr-naik .3s cubic-bezier(.2, .8, .2, 1); }
    .panel-qr.menutup { animation: panel-qr-turun .2s ease-in forwards; }
    .panel-qr[open]::backdrop { animation: panel-qr-redup .3s ease; }
    @keyframes panel-qr-naik { from { transform: translateY(100%); } }
    @keyframes panel-qr-turun { to { transform: translateY(100%); } }
    @keyframes panel-qr-redup { from { opacity: 0; } }
    @media (min-width: 640px) {
        @keyframes panel-qr-naik { from { transform: translateY(24px) scale(.96); opacity: 0; } }
        @keyframes panel-qr-turun { to { transform: translateY(24px) scale(.96); opacity: 0; } }
    }
</style>

<div id="kartu-qr">
    {{-- ===== Tombol melayang "QR Saya" ===== --}}
    <button type="button" @click="bukaPanel" title="Tampilkan kartu QR"
        class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2.5 pl-2.5 pr-4 py-2.5 rounded-2xl bg-[#0b332c] text-white ring-1 ring-white/10 shadow-[0_14px_32px_-10px_rgba(11,51,44,0.7)] hover:bg-[#135347] hover:-translate-y-0.5 active:scale-95 transition">
        <span class="relative w-10 h-10 rounded-xl bg-white text-[#0b332c] flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
            @if ($diterima)
            <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#0b332c]"></span>
            @endif
        </span>
        <span class="text-left leading-tight">
            <span class="block text-sm font-bold">QR Saya</span>
            <span class="block text-[10px] text-white/60">{{ $diterima ? 'Untuk absensi' : 'Belum aktif' }}</span>
        </span>
    </button>
    {{-- ===== Panel kartu QR (buka-tutup) ===== --}}
    <dialog ref="panel" @click.self="tutupPanel" @close="saatPanelTertutup"
        class="panel-qr m-0 mt-auto sm:m-auto w-full max-w-none sm:max-w-xl max-h-[92vh] overflow-y-auto p-0 rounded-t-[28px] sm:rounded-[28px] bg-white shadow-2xl backdrop:bg-[#071f1a]/60 backdrop:backdrop-blur-sm">
    <div class="relative overflow-hidden">
    <span class="pointer-events-none absolute -top-20 -left-20 w-64 h-64 rounded-full bg-emerald-300/20 blur-3xl"></span>
    <span class="pointer-events-none absolute -bottom-24 -right-16 w-64 h-64 rounded-full bg-sky-300/20 blur-3xl"></span>

    {{-- Pegangan (HP) + tombol tutup --}}
    <div class="relative flex items-center justify-center pt-3 sm:pt-4 min-h-[2.75rem]">
        <span class="w-12 h-1.5 rounded-full bg-slate-200 sm:hidden"></span>
        <button type="button" @click="tutupPanel" title="Tutup"
            class="absolute right-3 top-2.5 w-9 h-9 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="relative p-5 pt-2 pb-7 sm:p-7 sm:pt-2 flex flex-col sm:flex-row items-center gap-6">
        {{-- QR: ketuk untuk layar penuh --}}
        <button type="button" @click="bukaLayarPenuh" title="Tampilkan layar penuh"
            class="group relative shrink-0 rounded-3xl bg-white p-3 ring-1 ring-slate-200 shadow-lg shadow-emerald-900/10 hover:ring-emerald-300 transition">
            <span class="absolute inset-0 rounded-3xl ring-2 ring-emerald-400/0 group-hover:ring-emerald-400/60 transition"></span>
            <div v-pre data-qr-kecil class="w-40 h-40 sm:w-44 sm:h-44 [&_svg]:w-full [&_svg]:h-full {{ $diterima ? '' : 'opacity-40' }}">{!! $siswa->qr_svg !!}</div>
            <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-[#0b332c] px-3 py-1 text-[10px] font-semibold text-white shadow">Ketuk untuk memperbesar</span>
        </button>

        <div class="flex-1 min-w-0 text-center sm:text-left">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700/70">Kartu QR Digital</p>
            <h3 class="mt-1 text-xl sm:text-2xl font-extrabold text-slate-800 leading-tight">{{ $siswa->nama_lengkap }}</h3>
            <p class="mt-1 text-sm text-slate-500">
                No. {{ $siswa->nomor_pendaftaran }}@if ($infoQr) · {{ $infoQr }}@endif
            </p>

            @if ($diterima)
            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-50 ring-1 ring-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif · tunjukkan ke asatid saat absensi
            </p>
            @else
            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-50 ring-1 ring-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span> Bisa dipakai absensi setelah Anda diterima
            </p>
            @endif

            <div class="mt-4 flex flex-wrap justify-center sm:justify-start gap-2">
                <button type="button" @click="bukaLayarPenuh"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                    Layar Penuh
                </button>
                <button type="button" @click="unduhKartu" :disabled="mengunduh"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span v-text="mengunduh ? 'Menyiapkan…' : 'Unduh Kartu'"></span>
                </button>
                <button type="button" @click="salinKode"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25"/></svg>
                    <span v-text="tersalin ? 'Tersalin!' : 'Salin Kode'"></span>
                </button>
            </div>
        </div>
    </div>
    </div>
    </dialog>

    {{-- ===== Layar penuh: QR besar di latar putih supaya mudah dipindai ===== --}}
    <dialog ref="layarPenuh" @close="saatTutup" @click="tutupLayarPenuh"
        class="m-0 p-0 w-screen h-screen max-w-none max-h-none bg-white backdrop:bg-white">
        <div class="w-full h-full flex flex-col items-center justify-center gap-5 p-6 text-center select-none">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Kartu QR · Dauroh Qobul</p>
            <div v-pre class="w-[min(82vw,62vh)] aspect-square [&_svg]:w-full [&_svg]:h-full">{!! $siswa->qr_svg !!}</div>
            <div>
                <p class="text-2xl font-extrabold text-slate-800">{{ $siswa->nama_lengkap }}</p>
                <p class="mt-1 text-sm text-slate-500">No. {{ $siswa->nomor_pendaftaran }}@if ($infoQr) · {{ $infoQr }}@endif</p>
            </div>
            <p class="text-xs text-slate-400">Naikkan kecerahan layar agar mudah dipindai · ketuk di mana saja untuk menutup</p>
        </div>
    </dialog>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const DATA = {
            kode: @json($siswa->kode_qr),
            nama: @json($siswa->nama_lengkap),
            nomor: @json($siswa->nomor_pendaftaran),
            info: @json($infoQr),
            diterima: @json($diterima),
        };

        Vue.createApp({
            data() {
                return { tersalin: false, mengunduh: false, kunciLayar: null };
            },
            methods: {
                bukaPanel() {
                    this.$refs.panel.showModal();
                },
                // Tutup dengan animasi meluncur turun, baru benar-benar ditutup
                tutupPanel() {
                    const panel = this.$refs.panel;
                    if (!panel.open || panel.classList.contains('menutup')) return;
                    panel.classList.add('menutup');
                    setTimeout(() => panel.close(), 190);
                },
                saatPanelTertutup() {
                    this.$refs.panel.classList.remove('menutup');
                },
                async bukaLayarPenuh() {
                    this.$refs.layarPenuh.showModal();
                    // Jaga layar HP tetap menyala selama QR ditampilkan (bila browser mendukung)
                    try { this.kunciLayar = await navigator.wakeLock?.request('screen'); } catch (e) { /* tidak didukung */ }
                },
                tutupLayarPenuh() {
                    this.$refs.layarPenuh.close();
                },
                saatTutup() {
                    this.kunciLayar?.release?.();
                    this.kunciLayar = null;
                },
                async salinKode() {
                    try {
                        await navigator.clipboard.writeText(DATA.kode);
                        this.tersalin = true;
                        setTimeout(() => (this.tersalin = false), 2000);
                    } catch (e) {
                        prompt('Salin kode ini:', DATA.kode);
                    }
                },
                // Buat gambar kartu (PNG 1080×1350) dari QR di halaman, lalu unduh
                async unduhKartu() {
                    this.mengunduh = true;
                    try {
                        const svg = document.querySelector('#kartu-qr [data-qr-kecil] svg');
                        const url = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml' }));
                        const gambar = await new Promise((ok, gagal) => { const i = new Image(); i.onload = () => ok(i); i.onerror = gagal; i.src = url; });

                        const c = document.createElement('canvas');
                        c.width = 1080; c.height = 1350;
                        const x = c.getContext('2d');
                        const latar = x.createLinearGradient(0, 0, 1080, 1350);
                        latar.addColorStop(0, '#135347'); latar.addColorStop(1, '#071f1a');
                        x.fillStyle = latar; x.fillRect(0, 0, 1080, 1350);

                        x.textAlign = 'center';
                        x.fillStyle = '#fcd34d'; x.font = '600 34px system-ui, sans-serif';
                        x.fillText('KARTU QR · DAUROH QOBUL', 540, 120);

                        x.fillStyle = '#ffffff';
                        x.beginPath();
                        x.roundRect ? x.roundRect(170, 180, 740, 740, 48) : x.rect(170, 180, 740, 740); // HP lama: sudut kotak
                        x.fill();
                        x.drawImage(gambar, 210, 220, 660, 660);
                        URL.revokeObjectURL(url);

                        x.font = '800 60px system-ui, sans-serif';
                        x.fillText(DATA.nama.length > 28 ? DATA.nama.slice(0, 27) + '…' : DATA.nama, 540, 1040);
                        x.fillStyle = 'rgba(255,255,255,0.75)'; x.font = '500 38px system-ui, sans-serif';
                        x.fillText('No. ' + DATA.nomor, 540, 1105);
                        if (DATA.info) x.fillText(DATA.info, 540, 1160);
                        x.fillStyle = 'rgba(255,255,255,0.45)'; x.font = '400 28px system-ui, sans-serif';
                        x.fillText(DATA.diterima ? 'Tunjukkan kartu ini saat absensi' : 'Berlaku untuk absensi setelah diterima', 540, 1260);

                        const tautan = document.createElement('a');
                        tautan.download = 'kartu-qr-' + DATA.nomor + '.png';
                        tautan.href = c.toDataURL('image/png');
                        tautan.click();
                    } catch (e) {
                        alert('Kartu gagal dibuat. Coba lagi atau gunakan tombol Layar Penuh lalu screenshot.');
                    } finally {
                        this.mengunduh = false;
                    }
                },
            },
        }).mount('#kartu-qr');
    });
</script>
@endpush
@endif
