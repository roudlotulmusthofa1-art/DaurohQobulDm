{{-- Dokumen Export Data Santri: semua / per kelas / per kamar --}}
@extends('admin.export._dokumen')

@php $teks = fn ($nilai) => \App\Support\EksporDokumen::teks($nilai); @endphp

@section('isi')
@forelse ($grup as $i => $g)
<div class="{{ $i && $pisah ? 'halaman-baru' : '' }}">
    @if ($i === 0 || $pisah)
        @include('admin.export._kop')
    @endif
    @if ($g['judul'])
    <h2 class="grup">{!! $teks($g['judul']) !!} ({{ __(':jumlah santri', ['jumlah' => $g['santri']->count()]) }})</h2>
    @endif
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;" class="tengah">{{ __('No') }}</th>
                <th style="width: 12%;">{{ __('Nomor') }}</th>
                <th style="width: 20%;">{{ __('Nama') }}</th>
                <th style="width: 17%;">{{ __('Nama Arab') }}</th>
                <th style="width: 11%;">{{ __('Kelas') }}</th>
                <th style="width: 11%;">{{ __('Kamar') }}</th>
                <th style="width: 8%;" class="tengah">{{ __('Kasur') }}</th>
                <th style="width: 16%;">{{ __('Wali') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($g['santri'] as $n => $s)
            <tr>
                <td class="tengah">{{ $n + 1 }}</td>
                <td>{{ $s->nomor_pendaftaran }}</td>
                <td>{!! $teks($s->nama_lengkap) !!}@if ($s->kamar_id && $s->jabatan_kamar) <span class="kecil">({{ __(\App\Models\PendaftaranSiswa::JABATAN_KAMAR[$s->jabatan_kamar] ?? '') }})</span>@endif</td>
                <td style="text-align: right;">{!! $teks($s->nama_lengkap_arab) !!}</td>
                <td>{!! $teks($s->kelasMaster?->nama ?? '-') !!}</td>
                <td>{!! $teks($s->kamarMaster?->nama ?? '-') !!}</td>
                <td class="tengah">{{ $s->nomor_kasur ?? '-' }}</td>
                <td>{!! $teks($s->nama_wali ?? '-') !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@empty
    @include('admin.export._kop')
    <p class="kosong">{{ __('Tidak ada santri yang cocok dengan pilihan ini.') }}</p>
@endforelse
@endsection
