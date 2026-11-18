{{--
    Mixin Vue untuk formulir berita (window.MIXIN_TULIS_BERITA). Dipakai Admin > Berita & Tulis Berita (asatid/siswa).
    Halaman pemakai menyediakan: data `kategori` ([{nama, warna}]), `maksFoto`, `daftar`, `TOKEN` lewat window.TOKEN_BERITA.
    - bukaForm(berita|null)   -> isi `form`
    - kirimBerita(url)          -> kirim FormData (judul, isi, ..., foto[] baru, hapus_foto[] lama), balasan JSON {pesan, daftar}
    Foto diperkecil di browser (sisi terpanjang 1920px, JPEG 85%), jadi foto sebesar apa pun bisa dipilih.
--}}
<script>
    (() => {
        const SISI_MAKS = 1920;
        const MUTU = 0.85;
        const hariIni = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta' }).format(new Date());
        const ukuranTeks = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');

        // Perkecil satu foto -> File JPEG. GIF dibiarkan (agar tetap bergerak). Gagal dibaca -> file asli.
        async function perkecil(file) {
            if (file.type === 'image/gif') return file;
            let gambar;
            try {
                gambar = await createImageBitmap(file, { imageOrientation: 'from-image' });
            } catch (e) {
                gambar = await new Promise((ok, gagal) => {
                    const img = new Image();
                    img.onload = () => ok(img);
                    img.onerror = gagal;
                    img.src = URL.createObjectURL(file);
                }).catch(() => null);
            }
            if (!gambar) return file;
            const lebar = gambar.width, tinggi = gambar.height;
            const skala = Math.min(1, SISI_MAKS / Math.max(lebar, tinggi));
            const kanvas = document.createElement('canvas');
            kanvas.width = Math.round(lebar * skala);
            kanvas.height = Math.round(tinggi * skala);
            const ctx = kanvas.getContext('2d');
            ctx.fillStyle = '#fff'; // PNG transparan -> latar putih
            ctx.fillRect(0, 0, kanvas.width, kanvas.height);
            ctx.drawImage(gambar, 0, 0, kanvas.width, kanvas.height);
            const blob = await new Promise((ok) => kanvas.toBlob(ok, 'image/jpeg', MUTU));
            if (!blob) return file;
            // Foto kecil yang sudah JPEG/PNG/WEBP & lebih kecil dari hasil: pakai yang asli
            if (skala === 1 && blob.size >= file.size && /image\/(jpeg|png|webp)/.test(file.type)) return file;
            const nama = (file.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], nama, { type: 'image/jpeg' });
        }

        // Nama lokasi & pencarian tempat: OpenStreetMap (Nominatim), tanpa kunci API
        const NOMINATIM = 'https://nominatim.openstreetmap.org';
        const ambilJson = (url) => fetch(url, { headers: { 'Accept': 'application/json' } }).then((r) => (r.ok ? r.json() : Promise.reject(r.status)));
        // Alamat Nominatim -> "Kota/Kabupaten, Provinsi"
        const namaDaerah = (a = {}) => {
            const kota = a.city || a.town || a.county || a.municipality || a.village || a.suburb || '';
            const prov = a.state || a.region || '';
            return [kota, prov !== kota ? prov : ''].filter(Boolean).join(', ') || a.country || '';
        };
        let jedaCari = null;

        window.perkecilFoto = perkecil; // dipakai juga Admin > Galeri Foto
        window.MIXIN_TULIS_BERITA = {
            data() {
                return { form: null, galat: '', sibuk: false, memproses: false, hariIni, mencariLokasi: false, cariTempat: '', usulanTempat: [] };
            },
            computed: {
                kategoriPilihan() {
                    const nama = this.kategori.map((k) => k.nama);
                    // kategori lama yang sudah tidak aktif tetap bisa dipilih saat mengubah
                    if (this.form?.kategori && this.form.kategori !== '__lain' && !nama.includes(this.form.kategori)) nama.push(this.form.kategori);
                    return nama;
                },
                fotoTampil() {
                    if (!this.form) return [];
                    return this.form.fotoLama.filter((f) => !this.form.hapus.includes(f.id)).map((f) => ({ ...f, kunci: 'l' + f.id }))
                        .concat(this.form.fotoBaru);
                },
            },
            methods: {
                bukaForm(b = null) {
                    this.galat = '';
                    this.form = {
                        id: b?.id || null,
                        judul: b?.judul || '',
                        ringkasan: b?.ringkasan || '',
                        kategori: b?.kategori || '',
                        kategori_lain: '',
                        lokasi: b?.lokasi || '',
                        lat: b?.lat ?? null,
                        lng: b?.lng ?? null,
                        isi: b?.isi || '',
                        tampil_sampai: b?.tampil_sampai_tanggal || '',
                        status: b?.status || null,
                        fotoLama: b?.foto ? b.foto.map((f) => ({ ...f })) : [],
                        fotoBaru: [],
                        hapus: [],
                    };
                    this.cariTempat = '';
                    this.usulanTempat = [];
                },
                // ===== Lokasi =====
                petaKecil(lat, lng) {
                    const d = 0.01;
                    return 'https://www.openstreetmap.org/export/embed.html?bbox=' + [lng - d, lat - d, lng + d, lat + d].join('%2C')
                        + '&layer=mapnik&marker=' + lat + '%2C' + lng;
                },
                // Tombol "Pakai lokasi saya sekarang": titik dari HP -> nama daerah
                lokasiSaya() {
                    if (!navigator.geolocation || !window.isSecureContext) {
                        window.notifikasi?.gagal(@js(__('Lokasi otomatis tidak tersedia di browser ini. Cari nama tempat atau tulis sendiri.')));
                        return;
                    }
                    this.mencariLokasi = true;
                    navigator.geolocation.getCurrentPosition(async (pos) => {
                        const lat = +pos.coords.latitude.toFixed(7), lng = +pos.coords.longitude.toFixed(7);
                        this.form.lat = lat;
                        this.form.lng = lng;
                        try {
                            const r = await ambilJson(NOMINATIM + '/reverse?format=jsonv2&zoom=10&accept-language=id&lat=' + lat + '&lon=' + lng);
                            this.form.lokasi = namaDaerah(r.address).toUpperCase().slice(0, 80);
                        } catch (e) {
                            if (!this.form.lokasi) this.form.lokasi = @js(__('Lokasi saya'));
                            window.notifikasi?.peringatan(@js(__('Titik lokasi didapat, tapi nama daerah gagal dimuat. Tulis nama lokasinya sendiri.')));
                        } finally {
                            this.mencariLokasi = false;
                        }
                    }, (err) => {
                        this.mencariLokasi = false;
                        window.notifikasi?.gagal(err.code === 1
                            ? @js(__('Izin lokasi ditolak. Izinkan lokasi di pengaturan browser, atau cari nama tempat.'))
                            : @js(__('Lokasi tidak ditemukan. Pastikan GPS menyala, lalu coba lagi.')));
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
                },
                // Kotak "Cari tempat": usulan dari OpenStreetMap (jeda 0,6 detik setelah berhenti mengetik)
                cariLokasi() {
                    clearTimeout(jedaCari);
                    const q = this.cariTempat.trim();
                    if (q.length < 3) { this.usulanTempat = []; return; }
                    jedaCari = setTimeout(async () => {
                        try {
                            const hasil = await ambilJson(NOMINATIM + '/search?format=jsonv2&addressdetails=1&limit=6&accept-language=id&q=' + encodeURIComponent(q));
                            if (q !== this.cariTempat.trim()) return;
                            this.usulanTempat = hasil.map((r) => ({
                                kunci: r.place_id,
                                nama: r.name || r.display_name.split(',')[0],
                                lengkap: r.display_name,
                                daerah: namaDaerah(r.address),
                                lat: +(+r.lat).toFixed(7),
                                lng: +(+r.lon).toFixed(7),
                            }));
                        } catch (e) {
                            this.usulanTempat = [];
                        }
                    }, 600);
                },
                pilihLokasi(t) {
                    const daerah = t.daerah && !t.daerah.toLowerCase().startsWith(t.nama.toLowerCase()) ? t.nama + ', ' + t.daerah : (t.daerah || t.nama);
                    this.form.lokasi = daerah.toUpperCase().slice(0, 80);
                    this.form.lat = t.lat;
                    this.form.lng = t.lng;
                    this.cariTempat = '';
                    this.usulanTempat = [];
                },
                hapusLokasi() {
                    this.form.lokasi = '';
                    this.form.lat = null;
                    this.form.lng = null;
                },
                async pilihFoto(e) {
                    const file = Array.from(e.target.files || []);
                    e.target.value = '';
                    const sisa = this.maksFoto - this.fotoTampil.length;
                    if (file.length > sisa) window.notifikasi?.peringatan(@js(__('Foto paling banyak :n. Sebagian foto tidak ditambahkan.')).replace(':n', this.maksFoto));
                    this.memproses = true;
                    try {
                        for (const f of file.slice(0, Math.max(0, sisa))) {
                            if (!f.type.startsWith('image/') && !/\.(heic|heif|jpe?g|png|webp|gif)$/i.test(f.name)) {
                                window.notifikasi?.gagal(@js(__('":nama" bukan foto.')).replace(':nama', f.name));
                                continue;
                            }
                            const kecil = await perkecil(f);
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
                // Kirim formulir. Balasan JSON {pesan, daftar}. true = berhasil.
                async kirimBerita(url) {
                    this.galat = '';
                    if (this.memproses) { this.galat = @js(__('Tunggu, foto masih disiapkan.')); return false; }
                    const fd = new FormData();
                    ['judul', 'ringkasan', 'lokasi', 'isi', 'tampil_sampai'].forEach((k) => fd.append(k, this.form[k] ?? ''));
                    // Pilihan "Lainnya" -> kirim tulisan kategori sendiri
                    fd.append('kategori', this.form.kategori === '__lain' ? (this.form.kategori_lain || '').trim() : (this.form.kategori || ''));
                    if (this.form.lokasi && this.form.lat !== null) {
                        fd.append('lat', this.form.lat);
                        fd.append('lng', this.form.lng);
                    }
                    this.form.fotoBaru.forEach((f) => fd.append('foto[]', f.file, f.file.name));
                    this.form.hapus.forEach((id) => fd.append('hapus_foto[]', id));
                    this.sibuk = true;
                    try {
                        const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.TOKEN_BERITA }, body: fd });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            if (res.status === 413) throw new Error(@js(__('Foto terlalu besar untuk dikirim sekaligus. Kurangi jumlah foto lalu coba lagi.')));
                            if (res.status === 419) throw new Error(@js(__('Sesi halaman habis. Muat ulang halaman lalu kirim lagi.')));
                            if (res.status === 429) throw new Error(@js(__('Terlalu sering. Tunggu sebentar lalu coba lagi.')));
                            throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan. Coba lagi.'))));
                        }
                        this.form.fotoBaru.forEach((f) => URL.revokeObjectURL(f.url));
                        this.daftar = data.daftar;
                        this.form = null;
                        window.notifikasi?.sukses(data.pesan);
                        return true;
                    } catch (e) {
                        this.galat = e.message === 'Failed to fetch' ? @js(__('Koneksi terputus. Periksa internet lalu coba lagi.')) : e.message;
                        return false;
                    } finally {
                        this.sibuk = false;
                    }
                },
                // Aksi lain (JSON), balasan {pesan, daftar}
                async aksi(metode, url, isi = null) {
                    this.sibuk = true;
                    try {
                        const res = await fetch(url, {
                            method: metode,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.TOKEN_BERITA },
                            body: isi ? JSON.stringify(isi) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Gagal menyimpan. Coba lagi.'))));
                        if (data.daftar) this.daftar = data.daftar;
                        window.notifikasi?.sukses(data.pesan);
                        return data;
                    } catch (e) {
                        window.notifikasi?.gagal(e.message);
                        return null;
                    } finally {
                        this.sibuk = false;
                    }
                },
                // Label & warna status
                infoStatus(s) {
                    return {
                        menunggu: { teks: @js(__('Menunggu diperiksa')), kelas: 'bg-amber-50 text-amber-700 ring-amber-200' },
                        tampil: { teks: @js(__('Sedang tampil')), kelas: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
                        selesai: { teks: @js(__('Selesai tayang')), kelas: 'bg-slate-100 text-slate-600 ring-slate-200' },
                        ditolak: { teks: @js(__('Ditolak')), kelas: 'bg-rose-50 text-rose-700 ring-rose-200' },
                    }[s] || { teks: s, kelas: 'bg-slate-100 text-slate-600 ring-slate-200' };
                },
            },
        };
    })();
</script>
