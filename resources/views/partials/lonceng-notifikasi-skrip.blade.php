{{--
    Data & cara kerja lonceng notifikasi (mixin Vue) — dipakai aplikasi header di layout admin, asatid & siswa.
    Butuh variabel $lonceng = App\Support\StatusNotifikasi::terapkan(...)  -> ['items' => [...], 'belum' => n]
    Diletakkan SEBELUM skrip aplikasi header (di luar elemen yang di-mount Vue).
--}}
<script>
    window.mixinNotifikasi = {
        data() {
            return {
                notif: @json($lonceng['items']),
                notifTotal: @json($lonceng['belum']), // angka merah di lonceng
            };
        },
        computed: {
            notifLencana() {
                return this.notifTotal > 9 ? '9+' : String(this.notifTotal);
            },
        },
        methods: {
            // Simpan status ke server; keepalive = tetap terkirim walau halaman langsung pindah
            kirimStatusNotif(kunci, aksi) {
                if (!kunci.length) return;
                fetch(@json(route('notifikasi.status')), {
                    method: 'POST',
                    keepalive: true,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({ kunci, aksi }),
                }).catch(() => {});
            },
            tandaiDibaca(n) {
                if (n.dibaca) return false;
                n.dibaca = true;
                this.notifTotal = Math.max(0, this.notifTotal - n.bobot);
                return true;
            },
            // Klik notifikasi: tandai dibaca lalu buka halamannya
            bukaNotif(n, e) {
                if (this.tandaiDibaca(n)) this.kirimStatusNotif([n.kunci], 'baca');
                if (!n.url) e.preventDefault();
            },
            hapusNotif(n) {
                this.tandaiDibaca(n);
                this.notif = this.notif.filter((x) => x.kunci !== n.kunci);
                this.kirimStatusNotif([n.kunci], 'hapus');
            },
            bacaSemuaNotif() {
                const kunci = this.notif.filter((n) => this.tandaiDibaca(n)).map((n) => n.kunci);
                this.kirimStatusNotif(kunci, 'baca');
            },
            hapusSemuaNotif() {
                if (!confirm('Hapus semua notifikasi?')) return;
                const kunci = this.notif.map((n) => n.kunci);
                this.notif = [];
                this.notifTotal = 0;
                this.kirimStatusNotif(kunci, 'hapus');
            },
        },
    };
</script>
