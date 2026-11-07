{{-- Skrip Vue untuk admin/partials/pergantian-pelajaran (lihat keterangan di sana). --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('pergantian-pelajaran');
    if (!el) return;
    const DATA = @json(route('admin.pergantian.data'));
    const TANDA = @json(route('admin.pergantian.tanda'));
    const CSRF = @json(csrf_token());
    const jamSekarang = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    window.Vue.createApp({
        data: () => ({
            jam: @json(\App\Http\Controllers\Admin\PergantianPelajaranController::susun()),
            AKSEN: {
                sekarang: 'bg-emerald-500 text-white',
                berikut: 'bg-amber-400 text-white',
                nanti: 'bg-slate-100 text-slate-500',
                lewat: 'bg-slate-100 text-slate-400',
            },
            LABEL: {
                sekarang: @js(__('Sekarang')),
                berikut: @js(__('Berikutnya')),
                nanti: @js(__('Nanti')),
                lewat: @js(__('Selesai')),
            },
            mengirim: null,
            diperbarui: jamSekarang(),
        }),
        computed: {
            jumlahJadwal() { return this.jam.reduce((n, j) => n + j.baris.length, 0); },
        },
        mounted() {
            setInterval(this.muat, 30000); // status absen / dilihat guru ikut berubah
        },
        methods: {
            async muat() {
                if (document.hidden) return;
                try {
                    const res = await fetch(DATA, { headers: { Accept: 'application/json' } });
                    if (res.ok) { this.jam = (await res.json()).jam; this.diperbarui = jamSekarang(); }
                } catch (e) { /* offline: tampilkan data terakhir */ }
            },
            async kirim(isi, kunci) {
                this.mengirim = kunci;
                try {
                    const res = await fetch(TANDA, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(isi),
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || @js(__('Gagal mengirim tanda.')));
                    this.jam = json.jam;
                    this.diperbarui = jamSekarang();
                    window.notifikasi?.sukses(json.message);
                } catch (e) {
                    window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                } finally {
                    this.mengirim = null;
                }
            },
        },
    }).mount(el);
});
</script>
