{{--
    Akun Asatid > Pesan dari Admin (kotak masuk).
    Pesan dikirim admin dari menu Data Asatid > Kirim Pesan. Pesan yang tampil otomatis ditandai sudah dibaca
    (tanda BARU tetap terlihat saat halaman ini dibuka). Isi panjang bisa dibuka-tutup (Vue).
--}}
@extends('layouts.asatid')

@section('judul', __('Pesan dari Admin'))

@section('content')
@php
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$ikonSurat = 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75';
@endphp

<div id="halaman-pesan-asatid" class="space-y-4 max-w-3xl mx-auto">
    @if (count($idBaru))
    <p class="rounded-2xl bg-violet-50 ring-1 ring-violet-100 px-4 py-3 text-sm text-violet-800">
        {{ __('Ada') }} <b>{{ __(':jumlah pesan baru', ['jumlah' => count($idBaru)]) }}</b> {{ __('dari admin. Pesan bertanda') }} <b>{{ __('BARU') }}</b> {{ __('kini sudah ditandai dibaca.') }}
    </p>
    @endif

    @forelse ($pesan as $p)
    @php $baru = in_array($p->id, $idBaru); $panjang = mb_strlen($p->isi) > 280; @endphp
    <article class="{{ $kartu }} p-5 sm:p-6 {{ $baru ? 'ring-2 ring-violet-300' : '' }}">
        <div class="flex items-start gap-3">
            <span class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-br from-violet-400 to-purple-600 text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonSurat }}"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-bold text-slate-800">{{ $p->judul ?: __('Pesan dari admin') }}</h2>
                    @if ($baru)<span class="rounded-full bg-violet-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ __('BARU') }}</span>@endif
                </div>
                <p class="text-xs text-slate-400">
                    {{ $p->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y · H:i') }}{{ $p->pengirim ? ' · ' . __('dari :nama', ['nama' => $p->pengirim->name]) : '' }}
                </p>
            </div>
        </div>

        <p dir="auto" class="mt-4 text-[15px] leading-relaxed text-slate-700 whitespace-pre-line break-words"
            @if ($panjang) :class="{ 'line-clamp-4': !terbuka.includes({{ $p->id }}) }" @endif>{{ $p->isi }}</p>
        @if ($panjang)
        <button type="button" @click="bukaTutup({{ $p->id }})" class="mt-2 text-sm font-semibold text-violet-700 hover:underline"
            v-text="terbuka.includes({{ $p->id }}) ? @js(__('Tutup')) : @js(__('Baca selengkapnya'))"></button>
        @endif
    </article>
    @empty
    <div class="{{ $kartu }} py-16 px-6 text-center">
        <span class="mx-auto w-14 h-14 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonSurat }}"/></svg>
        </span>
        <p class="mt-3 font-bold text-slate-700">{{ __('Belum ada pesan dari admin') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Pengumuman dan pesan khusus untuk Anda akan muncul di sini.') }}</p>
    </div>
    @endforelse

    {{ $pesan->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Vue.createApp({
            data() {
                return { terbuka: [] }; // id pesan panjang yang sedang dibuka
            },
            methods: {
                bukaTutup(id) {
                    this.terbuka = this.terbuka.includes(id) ? this.terbuka.filter((x) => x !== id) : [...this.terbuka, id];
                },
            },
        }).mount('#halaman-pesan-asatid');
    });
</script>
@endpush
