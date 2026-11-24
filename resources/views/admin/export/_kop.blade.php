{{-- Kop dokumen: logo, nama pesantren, alamat, judul --}}
<table class="kop">
    <tr>
        @if ($logo)
        <td style="width: 52px;"><img src="{{ $logo }}" alt="" width="44" height="44" style="width: 44px; height: 44px;"></td>
        @endif
        <td>
            <div class="kop-nama">{!! \App\Support\EksporDokumen::teks($profil['nama']) !!}</div>
            @if ($profil['alamat'] || $profil['telepon'])
            <div class="kop-alamat">{{ collect([$profil['alamat'], $profil['telepon']])->filter()->implode(' · ') }}</div>
            @endif
        </td>
    </tr>
</table>
<h1 class="judul">{{ $judul }}</h1>
<p class="keterangan">{{ $keterangan }} </p>
