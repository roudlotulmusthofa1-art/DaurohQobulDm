{{-- Pesan berhasil / gagal untuk halaman Akademik --}}
@if (session('success'))
<div class="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 ring-1 ring-emerald-100 text-emerald-800 text-sm">
    <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
    </span>
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">{{ session('error') }}</div>
@endif
@if ($errors->any())
<div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
    <p class="font-semibold">Periksa kembali isian Anda:</p>
    <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
