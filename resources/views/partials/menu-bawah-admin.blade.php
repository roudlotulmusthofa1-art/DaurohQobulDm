{{--
    Menu bawah admin versi HP (hanya tampil di layar kecil, di bawah lebar "lg" = 1024px).
    Dipasang di layouts/admin.blade.php, jadi berlaku untuk semua halaman admin.
    Isi: 4 tombol pintas + tombol "Menu" yang membuka lembar berisi semua menu (sesuai izin role).
    Tanpa Vue: lembar dibuka/ditutup dengan <dialog> biasa (showModal / close).
--}}
@php
use App\Models\PendaftaranSiswa;
use App\Models\PesanPeserta;
use App\Support\MenuAdmin;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$akun = auth()->user();
$rute = fn (string $nama) => Route::has($nama) ? route($nama) : '#';

// Angka merah: pendaftar menunggu & pesan peserta belum dibaca (0 bila tabel belum ada)
$jmlMenunggu = MenuAdmin::boleh($akun, 'admin.data-masuk.index')
    ? rescue(fn () => PendaftaranSiswa::where('status', PendaftaranSiswa::MENUNGGU)->count(), 0, false) : 0;
$jmlPesan = MenuAdmin::boleh($akun, 'admin.pesan-peserta.index') && Schema::hasTable('pesan_peserta')
    ? rescue(fn () => PesanPeserta::dariPeserta()->belumDibaca()->count(), 0, false) : 0;

// [label, nama route, pola route aktif, ikon heroicons, angka merah]
$tombolBawah = [
    [__('Beranda'), 'admin.dashboard', ['admin.dashboard'], 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 0],
    [__('PSB'), 'admin.data-masuk.index', ['admin.data-masuk.*', 'admin.pendaftaran.*'], 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z', $jmlMenunggu],
    [__('Santri'), 'admin.santri.index', ['admin.santri.*'], 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342', 0],
    [__('Pesan'), 'admin.pesan-peserta.index', ['admin.pesan-peserta.*'], 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z', $jmlPesan],
];
$tombolBawah = array_filter($tombolBawah, fn ($t) => MenuAdmin::boleh($akun, $t[1]));
$adaYangAktif = collect($tombolBawah)->contains(fn ($t) => request()->routeIs(...$t[2]));

// Semua menu untuk lembar "Menu" (sudah disaring sesuai izin role)
$semuaMenu = collect(MenuAdmin::untukAkun($akun))->filter(fn ($m) => ! empty($m['submenu']));
@endphp

<style>
    .lembar-admin[open] { animation: lembar-admin-naik .28s cubic-bezier(.2, .8, .2, 1); }
    .lembar-admin[open]::backdrop { animation: lembar-admin-redup .28s ease; }
    @keyframes lembar-admin-naik { from { transform: translateY(100%); } }
    @keyframes lembar-admin-redup { from { opacity: 0; } }
</style>

{{-- ===== Menu bawah ===== --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 px-2 pt-2 pb-[max(.5rem,env(safe-area-inset-bottom))] print:hidden" v-pre>
    <div class="flex text-[10px] font-semibold">
        @foreach ($tombolBawah as [$label, $nama, $pola, $ikon, $angka])
        @php $aktif = request()->routeIs(...$pola); @endphp
        <a href="{{ $rute($nama) }}" class="relative flex-1 flex flex-col items-center gap-1 py-1 {{ $aktif ? 'text-emerald-700' : 'text-slate-500' }}">
            <span class="flex items-center justify-center w-12 h-7 rounded-full transition-colors {{ $aktif ? 'bg-emerald-50' : '' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="{{ $aktif ? 2.2 : 1.8 }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
            </span>
            {{ $label }}
            @if ($angka)
            <span class="absolute top-0 left-1/2 ml-2 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center ring-2 ring-white">{{ $angka > 99 ? '99+' : $angka }}</span>
            @endif
        </a>
        @endforeach
        <button type="button" onclick="document.getElementById('lembar-menu-admin').showModal()"
            class="flex-1 flex flex-col items-center gap-1 py-1 {{ $adaYangAktif ? 'text-slate-500' : 'text-emerald-700' }}">
            <span class="flex items-center justify-center w-12 h-7 rounded-full {{ $adaYangAktif ? '' : 'bg-emerald-50' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z"/></svg>
            </span>
            {{ __('Menu') }}
        </button>
    </div>
</nav>

{{-- ===== Lembar: Semua Menu (naik dari bawah) ===== --}}
<dialog id="lembar-menu-admin" onclick="if (event.target === this) this.close()" v-pre
    class="lembar-admin lg:hidden m-0 mt-auto w-full max-w-none max-h-[85dvh] overflow-y-auto p-0 rounded-t-[28px] bg-white backdrop:bg-black/40">
    <div class="sticky top-0 z-10 bg-white px-5 pt-3 pb-2 border-b border-slate-100">
        <span class="mx-auto block w-12 h-1.5 rounded-full bg-slate-200"></span>
        <div class="mt-3 flex items-center justify-between">
            <h2 class="font-extrabold text-slate-800">{{ __('Semua Menu') }}</h2>
            <button type="button" onclick="this.closest('dialog').close()" aria-label="{{ __('Tutup') }}" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100">✕</button>
        </div>
    </div>
    <div class="p-5 space-y-5">
        @foreach ($semuaMenu as $grup)
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $grup['label'] }}</p>
            <div class="mt-2 grid grid-cols-3 gap-2">
                @foreach ($grup['submenu'] as $sub)
                @php $subAktif = request()->routeIs(...(array) ($sub['active'] ?? $sub['route'])); @endphp
                <a href="{{ $rute($sub['route']) }}"
                    class="flex flex-col items-center gap-1.5 rounded-2xl px-2 py-3 text-center active:scale-95 transition {{ $subAktif ? 'bg-emerald-600 text-white' : 'bg-[#f3f7f5] text-slate-700' }} {{ Route::has($sub['route']) ? '' : 'opacity-40 pointer-events-none' }}">
                    <i data-lucide="{{ $sub['icon'] ?? 'circle' }}" class="w-5 h-5 {{ $subAktif ? 'text-white' : 'text-emerald-700' }}"></i>
                    <span class="text-[11px] font-semibold leading-tight">{{ $sub['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach

        <a href="/" target="_blank" rel="noopener"
            class="flex items-center justify-center gap-2 w-full rounded-2xl bg-slate-100 py-3 text-sm font-bold text-slate-700">
            {{ __('Lihat Website') }}
        </a>
        <form method="POST" action="{{ route('admin.logout') }}" onsubmit="return confirm(@js(__('Keluar dari akun admin?')))">
            @csrf
            <button class="w-full rounded-2xl bg-rose-50 py-3 text-sm font-bold text-rose-600">{{ __('Keluar') }}</button>
        </form>
    </div>
</dialog>
