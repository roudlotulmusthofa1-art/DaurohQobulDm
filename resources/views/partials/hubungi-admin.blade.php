{{--
    Pop-up "Hubungi Admin" untuk pengunjung (landing page & footer). Pesan masuk ke Admin > Kotak Pesan > tab Umum.
    Dipasang sekali di layouts/app.blade.php. Dibuka dari tombol mana pun dengan:  onclick="bukaHubungiAdmin()"
    Tanpa Vue (JavaScript biasa), seperti partials/notifikasi, supaya jalan di semua halaman publik.
    Gagal validasi (bag "hubungiAdmin") -> pop-up terbuka lagi dengan isian lama + pesan salahnya.
--}}
@php
$galat = $errors->getBag('hubungiAdmin');
$isian = 'mt-1.5 w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200 border-0 px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-teal-600 outline-none';
@endphp
<style>
    #dialog-hubungi-admin::backdrop { background: rgba(7, 31, 26, .55); backdrop-filter: blur(2px); }
    #dialog-hubungi-admin[open] { animation: hubungi-masuk .25s ease-out; }
    @keyframes hubungi-masuk { from { opacity: 0; transform: translateY(12px) scale(.98); } }
</style>

<dialog id="dialog-hubungi-admin" aria-labelledby="judul-hubungi-admin"
    class="m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl bg-white p-0 text-slate-800 shadow-2xl">
    <form method="POST" action="{{ route('hubungi-admin') }}" class="p-6 space-y-4">
        @csrf
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="judul-hubungi-admin" class="text-lg font-extrabold">{{ __('Hubungi Admin') }}</h2>
                <p class="text-xs text-slate-500">{{ __('Tulis pertanyaan Anda. Admin akan membalas lewat WhatsApp atau email yang Anda isi.') }}</p>
            </div>
            <button type="button" onclick="tutupHubungiAdmin()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="{{ __('Tutup') }}">✕</button>
        </div>

        @if ($galat->any())
        <div class="rounded-xl bg-rose-50 ring-1 ring-rose-100 p-3 text-sm text-rose-700">{{ $galat->first() }}</div>
        @endif

        <label class="block"><span class="text-xs font-semibold text-slate-600">{{ __('Nama') }} *</span>
            <input name="nama" required maxlength="100" value="{{ old('nama') }}" autocomplete="name" class="{{ $isian }}"></label>
        <label class="block"><span class="text-xs font-semibold text-slate-600">{{ __('Nomor WhatsApp / Email') }} *</span>
            <input name="kontak" required maxlength="100" value="{{ old('kontak') }}" placeholder="{{ __('cth. 0812xxxx atau nama@email.com') }}" class="{{ $isian }}"></label>
        <label class="block"><span class="text-xs font-semibold text-slate-600">{{ __('Pesan') }} *</span>
            <textarea name="isi" rows="5" required maxlength="2000" placeholder="{{ __('Tulis pesan Anda…') }}" class="{{ $isian }}">{{ old('isi') }}</textarea></label>

        {{-- Jebakan bot: manusia tidak melihat isian ini --}}
        <div class="hidden" aria-hidden="true"><input name="situs" tabindex="-1" autocomplete="off"></div>

        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="tutupHubungiAdmin()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                {{ __('Kirim Pesan') }}
            </button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        const dialog = document.getElementById('dialog-hubungi-admin');
        window.bukaHubungiAdmin = () => { dialog.showModal(); dialog.querySelector('[name=nama]').focus(); };
        window.tutupHubungiAdmin = () => dialog.close();
        // Klik di luar kotak -> tutup
        dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
        @if ($galat->any())
        window.bukaHubungiAdmin();
        @endif
    })();
</script>
