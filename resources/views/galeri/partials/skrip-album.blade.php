{{--
    Mixin Vue untuk formulir album foto galeri (window.MIXIN_ALBUM_FOTO).
    Dipakai Kirim Foto (asatid/siswa, galeri/kirim) & Admin > Galeri Foto. Wajib setelah berita/partials/skrip-tulis
    (window.perkecilFoto). Halaman pemakai menyediakan data `maksFoto` dan token lewat window.TOKEN_BERITA.
    - bukaForm(album|null) / tutupForm()
    - kirimAlbum(url)   -> FormData (keterangan, tanggal, foto[] baru, hapus_foto[] lama)
    - aksi(metode, url, isi)
    Balasan JSON diteruskan ke terimaData(data) (bawaan: data.daftar -> this.daftar; admin menimpanya).
--}}
<script>
    (() => {
        const ukuranTeks = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');
        const pesanGagal = (res, data) => {
            if (res.status === 413) return @js(__('Foto terlalu besar untuk dikirim sekaligus. Kurangi jumlah foto lalu coba lagi.'));
            if (res.status === 419) return @js(__('Sesi halaman habis. Muat ulang halaman lalu coba lagi.'));
            if (res.status === 429) return @js(__('Terlalu sering. Tunggu sebentar lalu coba lagi.'));
            return data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan. Coba lagi.')));
        };
        const pesanKoneksi = (e) => (e.message === 'Failed to fetch' ? @js(__('Koneksi terputus. Periksa internet lalu coba lagi.')) : e.message);

        window.MIXIN_ALBUM_FOTO = {
            data() {
                return { form: null, galat: '', sibuk: false, memproses: false };
            },
            computed: {
                fotoTampil() {
                    if (!this.form) return [];
                    return this.form.fotoLama.filter((f) => !this.form.hapus.includes(f.id)).map((f) => ({ ...f, kunci: 'l' + f.id }))
                        .concat(this.form.fotoBaru);
                },
            },
            methods: {
                terimaData(data) {
                    if (data.daftar) this.daftar = data.daftar;
                },
                bukaForm(a = null) {
                    this.galat = '';
                    this.form = {
                        id: a?.id || null,
                        keterangan: a?.keterangan || '',
                        tanggal: a?.tanggal || '',
                        status: a?.status || null,
                        fotoLama: a?.foto ? a.foto.map((f) => ({ ...f })) : [],
                        fotoBaru: [],
                        hapus: [],
                    };
                },
                tutupForm() {
                    this.form?.fotoBaru.forEach((f) => URL.revokeObjectURL(f.url));
                    this.form = null;
                },
                async pilihFoto(e) {
                    const file = Array.from(e.target.files || []);
                    e.target.value = '';
                    const sisa = this.maksFoto - this.fotoTampil.length;
                    if (file.length > sisa) window.notifikasi?.peringatan(@js(__('Satu album paling banyak :n foto. Sebagian foto tidak ditambahkan.')).replace(':n', this.maksFoto));
                    this.memproses = true;
                    try {
                        for (const f of file.slice(0, Math.max(0, sisa))) {
                            if (!f.type.startsWith('image/') && !/\.(heic|heif|jpe?g|png|webp|gif)$/i.test(f.name)) {
                                window.notifikasi?.gagal(@js(__('":nama" bukan foto.')).replace(':nama', f.name));
                                continue;
                            }
                            const kecil = await window.perkecilFoto(f);
                            if (!/image\/(jpeg|png|webp|gif)/.test(kecil.type)) {
                                window.notifikasi?.gagal(@js(__('Foto ":nama" tidak bisa dibaca. Coba foto lain atau ubah ke JPG.')).replace(':nama', f.name));
                                continue;
                            }
                            this.form.fotoBaru.push({ kunci: 'b' + Math.random().toString(36).slice(2), file: kecil, url: URL.createObjectURL(kecil), ukuran: ukuranTeks(kecil.size) });
                        }
                    } finally {
                        this.memproses = false;
                    }
                },
                buangFoto(f) {
                    if (f.file) {
                        URL.revokeObjectURL(f.url);
                        this.form.fotoBaru = this.form.fotoBaru.filter((x) => x !== f);
                    } else {
                        this.form.hapus.push(f.id);
                    }
                },
                // Kirim formulir album. true = berhasil.
                async kirimAlbum(url) {
                    this.galat = '';
                    if (this.memproses) { this.galat = @js(__('Tunggu, foto masih disiapkan.')); return false; }
                    if (!this.fotoTampil.length) { this.galat = @js(__('Pilih foto dulu.')); return false; }
                    const fd = new FormData();
                    fd.append('keterangan', this.form.keterangan || '');
                    fd.append('tanggal', this.form.tanggal || '');
                    this.form.fotoBaru.forEach((f) => fd.append('foto[]', f.file, f.file.name));
                    this.form.hapus.forEach((id) => fd.append('hapus_foto[]', id));
                    this.sibuk = true;
                    try {
                        const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.TOKEN_BERITA }, body: fd });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(pesanGagal(res, data));
                        this.terimaData(data);
                        this.tutupForm();
                        window.notifikasi?.sukses(data.pesan);
                        return true;
                    } catch (e) {
                        this.galat = pesanKoneksi(e);
                        return false;
                    } finally {
                        this.sibuk = false;
                    }
                },
                // Aksi lain (JSON). Berhasil -> data balasan; gagal -> null.
                async aksi(metode, url, isi = null) {
                    this.sibuk = true;
                    try {
                        const res = await fetch(url, {
                            method: metode,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.TOKEN_BERITA },
                            body: isi ? JSON.stringify(isi) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(pesanGagal(res, data));
                        this.terimaData(data);
                        window.notifikasi?.sukses(data.pesan);
                        return data;
                    } catch (e) {
                        window.notifikasi?.gagal(pesanKoneksi(e));
                        return null;
                    } finally {
                        this.sibuk = false;
                    }
                },
                infoStatus(s) {
                    return {
                        menunggu: { teks: @js(__('Menunggu diperiksa')), kelas: 'bg-amber-50 text-amber-700 ring-amber-200' },
                        tampil: { teks: @js(__('Tampil di galeri')), kelas: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
                        ditolak: { teks: @js(__('Ditolak')), kelas: 'bg-rose-50 text-rose-700 ring-rose-200' },
                    }[s] || { teks: s, kelas: 'bg-slate-100 text-slate-600 ring-slate-200' };
                },
            },
        };
    })();
</script>
