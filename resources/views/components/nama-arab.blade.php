{{--
    Nama Arab di bawah nama orang (santri / asatid / admin), dipakai di semua halaman admin supaya gayanya seragam.
    Huruf Arab otomatis memakai font Amiri (lihat resources/css/app.css). Rata kiri, tepat di bawah nama Latin.
    Kalau nama Arab kosong, tidak ada yang tampil.
    - Data PHP : <x-nama-arab :teks="$santri->nama_lengkap_arab" />
    - Data Vue : <x-nama-arab vue="n.arab" />            (jadi <span v-if="n.arab" v-text="n.arab">)
    - Ganti gaya: <x-nama-arab vue="n.arab" kelas="text-xs text-white/70" />
--}}
@props(['teks' => null, 'vue' => null, 'kelas' => 'text-sm text-slate-500'])
@if ($vue)
<span v-if="{{ $vue }}" v-text="{{ $vue }}" dir="rtl" lang="ar" {{ $attributes->merge(['class' => 'block truncate leading-snug text-left ' . $kelas]) }}></span>
@elseif (filled($teks))
<span dir="rtl" lang="ar" {{ $attributes->merge(['class' => 'block truncate leading-snug text-left ' . $kelas]) }}>{{ $teks }}</span>
@endif
