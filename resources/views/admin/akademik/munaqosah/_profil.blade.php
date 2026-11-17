{{--
    Munaqosah: panel Detail Santri untuk modal di tab Musyrifin (MunaqosahController@profil).
    Hanya potongan HTML (tanpa layout), dimasukkan Vue lewat v-html. Mode lihat: tanpa persyaratan, catatan admin & berkas.
--}}
<div class="space-y-6">
    @include('partials.detail-santri', ['admin' => false, 'judul' => __('Istimaroh'), 'rahasia' => true, 'tanpaSyarat' => true])
</div>
