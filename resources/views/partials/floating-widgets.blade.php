{{--
    Widget mengambang bawaan web asli: selalu terlihat di posisi yang sama
    walau halaman di-scroll (position: fixed), jadi ditaruh di luar <main>
    supaya tidak ikut alur konten per section.
--}}

{{-- Pill navigasi cepat (bawah tengah) --}}
<div class="fixed bottom-5 left-1/2 -translate-x-1/2 z-40">
    <div class="flex items-center gap-1 bg-teal-700 text-white rounded-full shadow-lg px-2 py-2">
        <button type="button" aria-label="Aksi cepat" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/15 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>
        </button>
        <button type="button" aria-label="Informasi" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/15 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9"/>
                <path stroke-linecap="round" d="M12 11v5m0-8h.01"/>
            </svg>
        </button>
        <button type="button" aria-label="Info pesantren" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/15 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12.05 12.05 0 0121 12v3.5m-18 0V12a12.05 12.05 0 012.84-3.42L12 14zm-6 3.5v-4.36a2 2 0 011-1.73L12 14"/>
            </svg>
        </button>
        <button type="button" aria-label="Menu lainnya" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/15 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/>
            </svg>
        </button>
    </div>
</div>

{{-- Indikator status (pojok kanan bawah) --}}
<div class="fixed bottom-5 right-5 z-40">
    <div class="w-11 h-11 bg-white rounded-xl shadow-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2-7 4 14 2-7h6"/>
        </svg>
    </div>
</div>
