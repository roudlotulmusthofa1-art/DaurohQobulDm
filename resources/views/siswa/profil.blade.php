{{--
    Akun Siswa > Profil: tampilan SAMA dengan detail santri di admin (partials/detail-santri),
    tetapi hanya untuk dilihat: tidak ada tombol edit, Terima/Tolak, atau form persyaratan.
--}}
@extends('layouts.siswa')

@section('judul', __('Profil Saya'))

@section('content')
@php $p = $siswa; @endphp

<div class="space-y-6 pb-4">
    <p class="rounded-2xl bg-sky-50 ring-1 ring-sky-100 px-4 py-3 text-sm text-sky-900">
        {{ __('Data ini sesuai formulir pendaftaran dan hanya bisa dilihat. Bila ada yang salah, kirim pesan lewat menu') }}
        <a href="{{ route('siswa.pesan-saya') }}" class="font-semibold underline">{{ __('Pesan untuk Admin') }}</a>.
    </p>

    @include('partials.detail-santri', ['admin' => false, 'sisipan' => 'siswa._persyaratan'])
</div>
@endsection
