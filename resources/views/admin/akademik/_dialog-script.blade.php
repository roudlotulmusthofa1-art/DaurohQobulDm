{{-- Buka/tutup <dialog>: tombol [data-buka="id-dialog"] membuka, [data-tutup] atau klik di luar menutup --}}
<script>
    (function () {
        document.addEventListener('click', (e) => {
            const buka = e.target.closest('[data-buka]');
            if (buka) {
                e.preventDefault();
                document.getElementById(buka.dataset.buka)?.showModal();
                return;
            }
            if (e.target.closest('[data-tutup]')) {
                e.target.closest('dialog')?.close();
                return;
            }
            if (e.target.tagName === 'DIALOG') e.target.close(); // klik di area gelap
        });
    })();
</script>
