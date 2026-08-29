@extends('layouts.admin')

@section('title', 'Data Asatid')

@section('content')

<div class="flex min-h-[60vh] flex-col items-center justify-center gap-6">

    <div class="text-4xl text-amber-900">
        Halaman Data Kelas
    </div>
    <div class="text-4xl text-amber-900">
        Dalam masa pembuatan
    </div>

    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf

        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-3 font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 15l3-3m0 0-3-3m3 3H3" />
            </svg>

            Logout
        </button>
    </form>

</div>

@endsection