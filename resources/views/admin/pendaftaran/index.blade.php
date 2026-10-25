{{-- Konten saja. Menggunakan layout admin yang sudah ada (sidebar/topbar). --}}
@extends('layouts.admin')

@section('content')
<div class="space-y-5">
    <div class="rounded-2xl bg-teal-700 text-white p-5">
    <h1 class="text-xl font-bold">
        {{ ($statusTetap ?? null) === 'diterima' ? 'Data Terkonfirmasi Peserta' : 'Data Masuk Peserta' }}
    </h1>
        <p class="text-sm text-teal-100">Verifikasi pendaftar baru, lalu terima atau tolak.</p>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ([['Total', $stat['total'], 'slate', ''], ['Menunggu', $stat['menunggu'], 'amber', 'menunggu'], ['Diterima', $stat['diterima'], 'emerald', 'diterima'], ['Ditolak', $stat['ditolak'], 'red', 'ditolak']] as [$label, $jml, $warna, $filter])
            <a href="{{ route('admin.pendaftaran.index', array_filter(['status' => $filter])) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-sm">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $warna }}-600">{{ $jml }}</p>
            </a>
        @endforeach
    </div>

    {{-- Pencarian --}}
    <form method="GET" class="flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama / nomor / wali..."
               class="flex-1 min-w-[200px] border border-slate-300 rounded-lg px-3 py-2 text-sm">
        @unless ($statusTetap ?? null)
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (['menunggu', 'diterima', 'ditolak'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        @endunless
        <button class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm">Cari</button>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-4 py-3">No. Pendaftaran</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Program</th>
                    <th class="px-4 py-3">WhatsApp Wali</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pendaftar as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $p->nomor_pendaftaran }}</td>
                        <td class="px-4 py-3">{{ $p->nama_lengkap }}</td>
                        <td class="px-4 py-3">{{ $p->program_pilihan }}</td>
                        <td class="px-4 py-3">{{ $p->no_hp_wali }}</td>
                        <td class="px-4 py-3">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-2.5 py-1 rounded-full text-xs font-medium',
                                'bg-amber-100 text-amber-700' => $p->status === 'menunggu',
                                'bg-emerald-100 text-emerald-700' => $p->status === 'diterima',
                                'bg-red-100 text-red-700' => $p->status === 'ditolak',
                            ])>{{ $p->label_status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.pendaftaran.show', $p) }}" class="text-teal-700 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada pendaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pendaftar->links() }}
</div>
@endsection
