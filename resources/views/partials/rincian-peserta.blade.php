{{--
    Rincian data peserta (dipakai di detail peserta akun asatid & profil akun siswa).
    Variabel: $s (PendaftaranSiswa), $kartu (kelas CSS kartu)
--}}
@php
$seksi = [
    [__('Data Pribadi'), 'from-emerald-400 to-teal-600', [
        __('Nama (Indonesia)') => $s->nama_lengkap,
        __('Nama (Arab)') => $s->nama_lengkap_arab,
        __('Tempat, tanggal lahir') => trim(($s->tempat_lahir ?? '') . ($s->tanggal_lahir ? ', ' . $s->tanggal_lahir->locale('id')->translatedFormat('d F Y') : ''), ', '),
        __('Umur') => $s->tanggal_lahir ? $s->tanggal_lahir->age . ' ' . __('tahun') : null,
        __('Status pernikahan') => isset($s::STATUS_PERNIKAHAN[$s->status_pernikahan]) ? __($s::STATUS_PERNIKAHAN[$s->status_pernikahan]) : null,
        __('No. WhatsApp') => $s->no_hp,
    ]],
    [__('Alamat'), 'from-rose-400 to-pink-600', [
        __('Provinsi') => $s->provinsi,
        __('Kota/Kabupaten') => $s->kota,
        __('Kecamatan') => $s->kecamatan,
        __('Kelurahan/Desa') => $s->kelurahan,
        __('Alamat lengkap') => $s->alamat,
    ]],
    [__('Wali & Muarrif'), 'from-sky-400 to-blue-600', [
        __('Nama wali') => $s->nama_wali,
        __('Hubungan') => $s->hubungan_wali,
        __('Pekerjaan wali') => $s->pekerjaan_wali,
        __('No. WhatsApp wali') => $s->no_hp_wali,
        __('Nama muarrif') => $s->nama_muarrif,
        __('No. WhatsApp muarrif') => $s->no_hp_muarrif,
    ]],
    [__('Pendidikan Formal'), 'from-violet-400 to-purple-600', [
        __('Pendidikan terakhir') => isset($s::PENDIDIKAN_TERAKHIR[$s->pendidikan_terakhir]) ? __($s::PENDIDIKAN_TERAKHIR[$s->pendidikan_terakhir]) : null,
        __('Nama sekolah') => $s->nama_sekolah,
        __('Daerah') => $s->nama_daerah,
    ]],
    [__('Data Tambahan'), 'from-amber-400 to-orange-500', [
        __('Marhalah yang diinginkan') => $s->marhalah,
        __('Lama belajar') => $s->lama_belajar,
        __('Kesehatan') => $s->kesehatan,
        __('Status ekonomi') => $s->status_ekonomi,
        __('Tujuan belajar') => $s->tujuan,
        __('Rencana setelah lulus') => $s->rencana,
        __('Pernah mengikuti Dauroh Qobul') => isset($s::PERNAH_DAUROH[$s->pernah_dauroh]) ? __($s::PERNAH_DAUROH[$s->pernah_dauroh]) : null,
    ]],
];
$pondok = $s->riwayat_pondok ?? [];
$kitab = $s->riwayat_kitab ?? [];
@endphp
    {{-- ===== Data per bagian ===== --}}
    <div class="grid lg:grid-cols-2 gap-5">
        @foreach ($seksi as [$judul, $warna, $baris])
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2.5 font-bold text-slate-800">
                <span class="w-2 h-6 rounded-full bg-gradient-to-b {{ $warna }}"></span>{{ $judul }}
            </h3>
            <dl class="mt-3 divide-y divide-slate-100">
                @foreach ($baris as $label => $nilai)
                <div class="py-2.5 grid grid-cols-5 gap-3 text-sm">
                    <dt class="col-span-2 text-slate-400">{{ $label }}</dt>
                    <dd class="col-span-3 font-medium text-slate-800 break-words" dir="auto">{{ filled($nilai) ? $nilai : '-' }}</dd>
                </div>
                @endforeach
            </dl>
        </section>
        @endforeach

        {{-- Riwayat mondok --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2.5 font-bold text-slate-800">
                <span class="w-2 h-6 rounded-full bg-gradient-to-b from-teal-400 to-emerald-600"></span>{{ __('Riwayat Mondok') }}
            </h3>
            <div class="mt-4 space-y-2.5">
                @forelse ($pondok as $i => $r)
                <div class="rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 p-3.5 {{ $loop->last ? 'ring-emerald-200 bg-emerald-50/40' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-bold text-slate-800" dir="auto">{{ $r['asal_pondok'] ?? '-' }}</p>
                        @if ($loop->last)<span class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 rounded-full px-2 py-0.5">{{ __('Terakhir') }}</span>@endif
                    </div>
                    <p class="mt-1 text-xs text-slate-500" dir="auto">
                        {{ $r['nama_pengasuh'] ?? '-' }} · {{ $r['nama_daerah'] ?? '-' }} · {{ $r['tahun_belajar'] ?? '-' }} {{ __('tahun') }}
                    </p>
                </div>
                @empty
                <p class="py-4 text-center text-sm text-slate-400">{{ __('Tidak ada riwayat mondok.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- Riwayat kitab --}}
        <section class="{{ $kartu }} p-5 sm:p-6">
            <h3 class="flex items-center gap-2.5 font-bold text-slate-800">
                <span class="w-2 h-6 rounded-full bg-gradient-to-b from-indigo-400 to-blue-600"></span>{{ __('Kitab yang Pernah Dipelajari') }}
            </h3>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($kitab as $k)
                <span class="inline-flex items-center gap-2 rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-100 pl-3 pr-2 py-2 text-sm">
                    <span class="font-semibold text-slate-800" dir="auto">{{ $k['nama_kitab'] ?? '-' }}</span>
                    <span class="text-[11px] text-slate-500" dir="auto">{{ $k['fan_kitab'] ?? '' }}</span>
                    <span class="text-[10px] font-bold rounded-full px-2 py-0.5 {{ ($k['sudah_diujikan'] ?? '') === 'sudah' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ ($k['sudah_diujikan'] ?? '') === 'sudah' ? __('Sudah diujikan') : __('Belum diujikan') }}
                    </span>
                </span>
                @empty
                <p class="w-full py-4 text-center text-sm text-slate-400">{{ __('Tidak ada data kitab.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
