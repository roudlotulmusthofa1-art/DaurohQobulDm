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
        <a href="..." @click="kembali">                     kembali ke halaman sebelumnya (href = cadangan bila tidak ada riwayat)
        <a href="..." @click="kembali" data-lewati="/kegiatan/">  sama, tapi halaman sebelumnya yang alamatnya memuat teks itu dilewati

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
            if (!confirm(e.currentTarget.dataset.konfirmasi || @js(__('Yakin?')))) e.preventDefault();
        },
        kirimForm(e) {
            e.target.form.requestSubmit();
        },
        pindahHalaman(e) {
            if (e.target.value) location.href = e.target.value;
        },
        // Kembali ke halaman sebelumnya. Bila tidak ada (mis. dibuka langsung / tab baru): pindah ke href.
        // Halaman sebelumnya dilewati (pakai href) bila: halaman yang sama, halaman login,
        // atau cocok dengan data-lewati="potongan-alamat,..." (mis. halaman yang otomatis melempar balik ke sini).
        kembali(e) {
            e.preventDefault();
            const tautan = e.currentTarget.getAttribute('href');
            const lewati = (e.currentTarget.dataset.lewati || '').split(',').map((x) => x.trim()).filter(Boolean);
            let asal = null;
            try { asal = document.referrer ? new URL(document.referrer) : null; } catch (err) { asal = null; }
            const layak = asal && asal.origin === location.origin
                && asal.pathname !== location.pathname
                && !asal.pathname.startsWith('/login')
                && !lewati.some((p) => asal.pathname.includes(p));
            if (history.length > 1 && layak) {
                history.back();
                return;
            }
            // Tidak ada halaman sebelumnya yang layak -> buka alamat cadangan (tab tidak ditutup)
            location.href = tautan;
        },
    };
</script>
