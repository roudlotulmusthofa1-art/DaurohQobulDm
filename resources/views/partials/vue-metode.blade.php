{{--
    Metode Vue yang sering dipakai halaman-halaman Blade (dimuat oleh layout admin/asatid/siswa).
    Cara pakai di skrip halaman:

        Vue.createApp({
            data() { return { ... }; },
            methods: { ...metodeHalaman, metodeLain() { ... } },
        }).mount('#id-halaman');

    Di HTML:
        <button @click="bukaDialog('dialog-x')">           buka <dialog id="dialog-x" ref="dialog-x">
        <dialog ... @click.self="tutupDialog">              klik area gelap di luar kotak -> tutup
        <button @click="tutupDialog">                       tombol Batal / ✕ di dalam dialog
        <form @submit="konfirmasi" data-konfirmasi="Hapus?">  tanya dulu sebelum dikirim
        <select @change="kirimForm">                        kirim form saat pilihan diganti
        <select @change="pindahHalaman">                    buka alamat dari value pilihan
        <a href="..." @click="kembali">                     kembali ke halaman sebelumnya

    Ingat: teks dari database di dalam elemen yang di-mount Vue diberi v-pre
    (atau ditampilkan lewat v-text), supaya tulisan "{{ ... }}" di data tidak dijalankan Vue.
--}}
<script>
    window.metodeHalaman = {
        // Buka <dialog> lewat ref (ref sama dengan id dialog)
        bukaDialog(nama) {
            (this.$refs[nama] || document.getElementById(nama))?.showModal();
        },
        // Tutup dialog tempat tombol/area yang diklik berada
        tutupDialog(e) {
            e.target.closest('dialog')?.close();
        },
        // Tanya "yakin?" sebelum form dikirim. Pesannya di atribut data-konfirmasi.
        konfirmasi(e) {
            if (!confirm(e.currentTarget.dataset.konfirmasi || 'Yakin?')) e.preventDefault();
        },
        kirimForm(e) {
            e.target.form.requestSubmit();
        },
        pindahHalaman(e) {
            if (e.target.value) location.href = e.target.value;
        },
        // Kembali ke halaman sebelumnya. Bila dibuka di tab baru: tab ditutup, atau pindah ke href.
        kembali(e) {
            e.preventDefault();
            const tautan = e.currentTarget.getAttribute('href');
            const dariSitusIni = document.referrer && new URL(document.referrer).origin === location.origin;
            if (history.length > 1 && dariSitusIni) {
                history.back();
                return;
            }
            window.close();
            setTimeout(() => (location.href = tautan), 250);
        },
    };
</script>
