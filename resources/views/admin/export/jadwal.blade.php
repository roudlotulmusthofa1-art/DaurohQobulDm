{{-- Dokumen Export Jadwal Mengajar: tabel jam × hari, untuk semua / per kelas / per ustadz --}}
@extends('admin.export._dokumen')

@php $teks = fn ($nilai) => \App\Support\EksporDokumen::teks($nilai); @endphp

@section('isi')
@forelse ($grup as $i => $g)
<div class="{{ $i && $pisah ? 'halaman-baru' : '' }}">
    @if ($i === 0 || $pisah)
        @include('admin.export._kop')
    @endif
    @if ($g['judul'])
    <h2 class="grup">{!! $teks($g['judul']) !!}@if ($g['arab']) · {!! $teks($g['arab']) !!}@endif</h2>
    @endif
    <table class="data jadwal">
        <thead>
            <tr>
                <th style="width: 12%;">{{ __('Jam') }}</th>
                @foreach ($hariList as $namaHari)
                <th class="tengah">{{ __($namaHari) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($g['waktu'] as $w)
            <tr>
                <td><b>{!! $teks($w->nama) !!}</b>@if ($w->jam_mulai)<br><span class="kecil">{{ $w->jam_mulai }}–{{ $w->jam_selesai }}</span>@endif</td>
                @foreach ($hariList as $kodeHari => $namaHari)
                <td>
                    @foreach ($g['slot'][$w->id . '-' . $kodeHari] ?? [] as $j)
                    <div class="slot">
                        <div class="kitab">{!! $teks($j->kitab?->nama ?? '-') !!}</div>
                        @if ($g['tampilKelas'])<div class="kecil">{!! $teks($j->kelas?->nama ?? '-') !!}</div>@endif
                        @if ($g['tampilUstadz'])<div class="kecil">{!! $teks($namaUstadz[$j->user_id] ?? __('Belum ada ustadz')) !!}</div>@endif
                    </div>
                    @endforeach
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@empty
    @include('admin.export._kop')
    <p class="kosong">{{ __('Tidak ada jadwal yang cocok dengan pilihan ini.') }}</p>
@endforelse
@endsection
