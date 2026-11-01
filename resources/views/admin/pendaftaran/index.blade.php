{{-- Konten saja. Menggunakan layout admin yang sudah ada (sidebar/topbar). --}}
@extends('layouts.admin')

@section('content')
    <div class="space-y-5 mx-4 md:mx-10 lg:mx-20 mt-8">
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
                        <th class="px-4 py-3">Kota</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pendaftar as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $p->nomor_pendaftaran }}</td>
                            <td class="px-4 py-3">{{ $p->nama_lengkap }}</td>
                            {{-- Kota asal saja --}}
                            <td class="px-4 py-3" dir="auto">{{ $p->kota ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    'bg-amber-100 text-amber-700' => $p->status === 'menunggu',
                                    'bg-emerald-100 text-emerald-700' => $p->status === 'diterima',
                                    'bg-red-100 text-red-700' => $p->status === 'ditolak',
                                ])>{{ $p->label_status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Ikon mata: buka detail siswa --}}
                                    <a href="{{ route('admin.pendaftaran.show', $p) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-teal-700 transition hover:bg-teal-50"
                                        title="Lihat detail siswa" aria-label="Lihat detail {{ $p->nama_lengkap }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>

                                    {{-- Ikon tempat sampah: hapus (minta konfirmasi dulu) --}}
                                    <form method="POST" action="{{ route('admin.pendaftaran.destroy', $p) }}"
                                        onsubmit="return confirm({{ Js::from('Hapus data ' . $p->nama_lengkap . "?\nBerkas unggahan dan akun login siswanya juga ikut terhapus.") }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50"
                                            title="Hapus" aria-label="Hapus {{ $p->nama_lengkap }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada pendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pendaftar->links() }}
    </div>
@endsection
