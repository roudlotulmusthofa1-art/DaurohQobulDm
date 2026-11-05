{{-- Pesan berhasil / gagal sekarang tampil sebagai notifikasi pojok (partials/notifikasi). Di sini tinggal ringkasan isian yang salah. --}}
@if ($errors->any())
<div class="p-3.5 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
    <p class="font-semibold">{{ __('Periksa kembali isian Anda:') }}</p>
    <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
