# Catatan Proyek Dauroh Qobul

Ringkasan hal penting dari percakapan dengan Claude (sampai 4 November 2026), supaya tetap ada walaupun riwayat chat dihapus.

---

## 1. Aturan kerja

- **Tampilan memakai Tailwind CSS**: kelas Tailwind langsung di file Blade. Warna dan font diatur di `resources/css/app.css`.
- **Interaksi memakai Vue 3**: template Vue ditulis langsung di file Blade, memakai `window.Vue` dari `resources/js/app.js`.
- **Setiap perubahan ditunjukkan dulu**: kode **sebelum** dan **sesudah**, nama file dan barisnya, lalu menunggu persetujuan sebelum dipasang.
- **Tulisan baru di tampilan selalu dibungkus `__('...')`**, supaya bisa diterjemahkan di **Pengaturan → Bahasa**.
  - Di HTML: `{{ __('Data Santri') }}`
  - Di Vue/JavaScript: `@js(__('Data Santri'))`
- **Sebelum naik ke server:** jalankan `npm run build` untuk mengecek tampilan Vue. Kalau ada migrasi baru, uji juga `php artisan migrate` dan `php artisan sesi:migrate` di MySQL lokal.

---

## 2. Cara kirim ke server (daurohqobul.com)

1. Commit perubahan:
   ```bash
   git add -A
   ```
   ```bash
   git commit -m "pesan perubahan"
   ```
2. Jalankan **`deploy.bat`**. Script ini otomatis:
   - mengirim ke branch `main`;
   - menarik kode di server;
   - menjalankan `npm run build`, `migrate`, `sesi:migrate`, dan `storage:link`;
   - membersihkan cache.
3. Pastikan di akhir muncul ✅ **"Deployment Finished"**.

**Melihat error di server.** Server memakai Docker, bukan cPanel. Jalankan dari **Git Bash** di laptop:
```bash
cd /d/laragon/www/DaurohQobulDm
```
```bash
source .deploy.conf && ssh $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app sh -c 'grep -n \"\.ERROR\" storage/logs/laravel.log | tail -n 8'"
```
Kalau awal barisnya `PS D:\laragon\...`, berarti perintah berjalan di **laptop**, bukan di server.

---

## 3. Fitur yang sudah dibuat

### Bahasa Arab dan tampilan kanan-ke-kiri
- **Tombol bahasa** di header admin, asatid, dan peserta (Indonesia ⇄ العربية). Saat bahasa Arab dipilih, tampilan berbalik kanan-ke-kiri (`dir="rtl"`).
- **Pengaturan → Bahasa**: daftar semua tulisan bahasa Indonesia per halaman (sekitar 2.258 tulisan). Kolom Arab diisi sendiri.
  - Kolom yang kosong tetap tampil dalam bahasa Indonesia. File `lang/ar.json` tidak dipakai lagi.
  - Terjemahan disimpan di tabel `terjemahan` (database utama, berlaku untuk semua sesi).
  - Tulisan yang berisi `:nama`, `:jumlah`, dan sejenisnya: tulis kata itu persis sama di terjemahan Arab.
  - Usulan terjemahan ada di `lang/usulan-ar.json` (2026-11-07: semua tulisan sudah punya usulan, termasuk fitur Penilaian, Nilai Harian, Pergantian Pelajaran, Pengingat). Usulan baru dipakai setelah admin menyalakan "Pakai semua usulan" lalu Simpan per halaman.
  - Kelompok halaman baru: "Admin — Penilaian", "Asatid — Penilaian"; panel Pergantian Pelajaran ikut "Admin — Dasbor"; pengingat pelajaran ikut "Asatid — Header & kerangka". Nama 6 penilaian Nilai Harian dibaca dari `NilaiHarian::ASPEK` (`TeksTampilan::dariKode`).
- **File penting:**
  - `app/Support/TeksTampilan.php`: pemindai tulisan dan pengelompokan halaman (daftar `HALAMAN`).
  - `app/Support/PemuatTerjemahan.php`, `app/Models/Terjemahan.php`
  - `resources/views/admin/pengaturan/tab-bahasa.blade.php`
  - `resources/views/partials/font-arab.blade.php`: aturan tampilan kanan-ke-kiri.

### Font Arab: Amiri (gaya kitab)
- **Berlaku untuk:** semua huruf Arab, memakai Amiri dari paket npm `@fontsource/amiri`. Huruf Latin tetap Plus Jakarta Sans.
- **Mengubah ukurannya:** di `resources/css/app.css`, cari `size-adjust: 125%;` (ada 2), ubah angkanya, lalu jalankan `npm run build`.

### Password
- **Ganti password sendiri** untuk asatid dan peserta, lewat kartu atau menu **Ganti Password** di dasbor masing-masing. Password lama wajib benar.
- **Reset password oleh admin**: password baru **diketik admin sendiri**, lalu dikirim otomatis ke WhatsApp. Tersedia di:
  - detail **Data Asatid**;
  - detail **Data Admin** (khusus Super Admin);
  - detail **Santri**.
- **File penting:**
  - `app/Support/ResetPasswordAkun.php`, `app/Models/Concerns/ResetPasswordAkun.php`
  - `app/Support/GantiPasswordSendiri.php`
  - `resources/views/partials/form-password-baru.blade.php`, `partials/reset-password-akun.blade.php`, `partials/ganti-password.blade.php`
- **Template WhatsApp:** "Reset password asatid" dan "Reset password admin" di **Pengaturan → Pesan**.

### Data Admin: Edit Data
- **Tombol Edit Data** di detail Data Admin (khusus Super Admin). Bisa mengubah nama, nama Arab, nomor WhatsApp, alamat, foto, email, dan password.

### Tombol Edit di menu admin (2026-11-14)
- **Laporan Absensi > detail**: tombol **Ubah kehadiran** → tiap santri pilih H/T/S/I/A → **Simpan perubahan** (form biasa, `PUT admin.laporan-absensi.update`, `LaporanAbsensiController@update`; hanya baris milik absensi itu). Terlambat/Alfa otomatis ikut Catatan > Pelanggaran.
- **Penilaian > Catatan**: tombol ✏️ (hanya catatan tulisan asatid) → jenis, isi, tanggal, jam (`PUT admin.penilaian.catatan.ubah`, `PenilaianController@ubah`).
- **Nilai Harian**: tombol ✏️ → 6 nilai, batas dari `NilaiHarian::ASPEK` (`PUT admin.penilaian.nilai-harian.ubah`, `ubahNilaiHarian`).
- **Penilaian Dakwah**: ✏️ memakai jendela Beri Nilai (`ubahId`), `PUT admin.penilaian.dakwah.nilai.ubah`. Cek isian bersama di `isianNilai()`; nama nilai/waktu lama tetap boleh walau pilihannya sudah dihapus.
- **Perizinan**: ✏️ memakai jendela Beri Izin (`ubahId`), `PUT admin.penilaian.perizinan.ubah`. Cek isian bersama di `isianIzin()`; waktu kembali tidak diubah di sini (tetap lewat "Ubah kembali"); mulai tidak boleh setelah waktu kembali.
- Penilai/pemberi izin awal tetap tercatat. Tidak ada migrasi baru.

### Halaman "Dalam masa pembuatan" (2026-11-14)
- Menu admin yang halamannya belum dibuat membuka satu halaman umum `admin/dalam-pembuatan.blade.php` (header + kartu "Dalam masa pembuatan" + tombol Kembali ke Dashboard). Judul, kelompok & ikon otomatis dari `MenuAdmin`.
- Daftarnya di `App\Support\HalamanDalamPembuatan::DAFTAR` (nama rute => alamat); rutenya dibuat otomatis di `routes/web.php` (grup admin). Sekarang: Biaya Pendidikan, Pembayaran Santri, Laporan Akademik, Laporan Keuangan, Fitur Unggulan, Kurikulum Terintegrasi.
- **Kalau halaman aslinya sudah dibuat:** hapus barisnya dari DAFTAR, lalu buat rute aslinya dengan NAMA yang sama (mis. `admin.biaya-pendidikan.index`), supaya menu & izin Role tetap jalan.
- Menu asatid & peserta semuanya sudah punya halaman (lencana kartu Penilaian di beranda asatid sekarang "Nilai & catatan").

### Admin versi HP (layout)
- **Cara kerja:** dilihat dari **lebar layar**. Di bawah 1024px (`lg`), sidebar disembunyikan, bilah atas jadi hijau tua ringkas (tombol kembali + judul halaman), dan muncul menu bawah: Beranda, PSB, Santri, Pesan, Menu.
- **Tombol Menu** membuka lembar berisi semua menu sesuai izin role, Lihat Website, dan Keluar.
- **File penting:** `resources/views/layouts/admin.blade.php`, `resources/views/partials/menu-bawah-admin.blade.php`.
- Dashboard tetap punya file HP sendiri (`admin/dashboard-hp.blade.php`).
- **Tabel jadi kartu (pola):** di atas tabel ada blok `lg:hidden` berisi kartu per baris; tabelnya diberi `hidden lg:block`. Tombol di kartu memakai aksi/handler yang sama dengan tabel. Dipakai di Data Masuk, Data Santri, Buku Kas, Kelulusan, Laporan Absensi, Master Data, Daftar User.
- **Jadwal Mengajar di HP:** pilihan hari (bisa digeser) + daftar jam pelajaran hari itu. Hapus & geser jadwal hanya di laptop atau tampilan Per Kelas/Per Ustadz.
- **Aturan grid untuk HP:** grid satu kolom tulis `grid grid-cols-1 md:grid-cols-2` (bukan `grid md:grid-cols-2`), supaya teks panjang (email) tidak mendorong kotak lebih lebar dari layar.
- Bilah simpan & toast yang menempel di bawah dinaikkan di HP (`bottom-24 lg:bottom-…`) agar tidak tertutup menu bawah.

### Absensi kegiatan bersama (dibuat admin)
- **Admin > Akademik > Absensi Kegiatan** (`Admin/AbsensiKegiatanController`, view `admin/akademik/absensi-kegiatan`): buat (nama dari Master Data > Jenis Kegiatan + tanggal + jam mulai/terlambat/alfa + cara absen), Aktifkan/Nonaktifkan, Tutup (yang belum tercatat = Alfa), Ubah, Hapus. Terlihat siapa saja asatid yang mencatat.
- **Asatid > Absensi > Kegiatan**: daftar kegiatan aktif dari admin; SEMUA asatid bisa mencatat di kegiatan yang sama. Halaman catat memperbarui diri tiap 10 detik (`absensi.kegiatan.status`). Asatid tidak lagi membuat kegiatan sendiri.
- Migrasi `2026_11_13_100000_absensi_kegiatan_dari_admin`: `absensi.dari_admin`, `absensi.aktif`, `absensi_detail.dicatat_oleh` (perlu `migrate` + `sesi:migrate`).

- **Tutup otomatis (2026-11-06):** begitu tanggal + batas alfa lewat (jam WIB), absensi kegiatan tertutup sendiri dan yang belum tercatat = Alfa (`Absensi::tutupKalauLewat()` / `tutupYangLewat()`, dipanggil saat halaman absensi / laporan dibuka; tanpa cron).

### Penilaian (2026-11-06)
- **Admin > PENILAIAN**: Kelas & Pelajaran (`admin/penilaian/kelas`, 3 tombol: Nilai Harian · Ujian Lisan · Ujian) dan **Catatan** (`admin/penilaian/catatan`, `Admin/PenilaianController`): tombol Semua · Pencapaian · Pelanggaran, ringkasan, rekap per santri, semua catatan (saring + hapus).
- **Asatid > Penilaian** (`Asatid/PenilaianController`, view `asatid/penilaian/*`): kartu Nilai Harian/Ujian Lisan/Ujian (segera hadir) + **Catatan** = pilih santri (boleh beberapa) → jenis Pencapaian/Pelanggaran → isi catatan (wajib) → tanggal (hari ikut) & jam (otomatis sekarang, bisa diubah). Asatid hanya melihat/menghapus catatannya sendiri.
- Tabel `catatan_siswa` (kolom `jenis` = pencapaian | pelanggaran; migrasi `2026_11_14_120000_...`). Data bersama di `App\Support\DataCatatan`, model `CatatanSiswa`.
- Terlambat & Alfa dari absensi kelas/kegiatan ikut dihitung sebagai Pelanggaran, dibaca langsung dari tabel absensi (tidak disalin).
- **Nilai Harian (2026-11-07):** Asatid > Penilaian > Nilai Harian (`asatid/penilaian/harian`, route `asatid.penilaian.harian`): pilih kelas → kitab (yang terjadwal di kelas itu bulan ini paling atas) + tanggal → santri → 6 penilaian. Batas: menjawab pertanyaan guru 20, kelengkapan belajar 10, konsentrasi & keaktifan 20, menulis faidah 15, menjawab sistematis 20, tugas tepat waktu 15 (total 100). Batas ada di SATU tempat: `NilaiHarian::ASPEK`; dicek di layar, validasi server, dan saat menyimpan model. **Satu santri + kitab + bulan ajaran aktif = satu nilai** (2026-11-11): asatid meneruskan nilai yang sama setiap hari, bukan nilai baru tiap hari; kolom `tanggal` = terakhir dinilai. Tidak ada bulan ajaran aktif = tidak bisa menilai. Admin menyaring per bulan ajaran (awalnya bulan aktif). Tabel `nilai_harian` (migrasi `2026_11_15_100000_...` + `2026_11_17_100000_nilai_harian_per_bulan_ajaran`: tambah `bulan_ajaran_id`, nilai lama dobel di bulan yang sama → yang terbaru dipakai, sisanya dihapus; perlu `migrate` + `sesi:migrate`).
- Admin > Penilaian > Kelas & Pelajaran > Nilai Harian (`admin/penilaian/partials/nilai-harian.blade.php`): saringan kelas/kitab/periode, ringkasan, rekap per santri (rata-rata total & tiap penilaian), semua nilai (bisa dihapus).
- **Absensi Kelas:** kolom "Catatan mudarris" dihapus (catatan lama tetap di `absensi_detail.catatan`, tidak dihapus). Diganti 2 tombol di kartu santri: **Nilai Harian** (kelas, pelajaran, tanggal, santri langsung terisi) dan **Catatan** (`?siswa=ID`, santri langsung terpilih). Keduanya buka tab baru supaya absensi tidak hilang.
- **Absensi Kelas pakai KITAB (2026-11-07):** langkah 2 "Pilih kitab" (bukan bidang ilmu). Kolom baru `absensi.kitab_id` (migrasi `2026_11_15_110000_tambah_kitab_absensi`); `bidang_ilmu_id` tetap diisi dari bidang ilmu kitab, jadi absensi lama tetap tampil ("Pelajaran" di laporan bila belum ada kitab). Kelas + jam + hari cocok jadwal guru → kitab terpilih otomatis. Daftar kitab bersama di `App\Support\DaftarKitab` (dipakai absensi & nilai harian). Absensi lama tanpa kitab dianggap sama bila bidang ilmunya sama (tidak dobel saat diubah).
- **Pengingat ganti pelajaran (2026-11-07):** `asatid/partials/pengingat-pelajaran.blade.php`, dimuat di `layouts/asatid` (semua halaman asatid). Dari jadwal mengajar guru (`AsatidDashboardController::dataPengingat`, diperbarui tiap 30 menit lewat `asatid.jadwal.pengingat`). Jam dari HP guru: 5 menit sebelum mulai & saat mulai → jendela + bunyi (Web Audio) + getar + notifikasi browser (kalau diizinkan) + tombol "Absen sekarang" (kelas, kitab, jam, tanggal terisi). Tiap pengingat sekali (localStorage). Lonceng juga menampilkan "Sekarang/Berikutnya". Batasan: hanya selama web terbuka; bunyi butuh halaman disentuh sekali. Notifikasi HP saat browser tertutup = belum (perlu Web Push + penjadwal server).
- **Pergantian Pelajaran di dasbor admin (2026-11-07):** panel "Jadwal Hari Ini" (laptop & HP) diganti `admin/partials/pergantian-pelajaran` (+ `-skrip`, dibungkus `v-pre` karena berada di dalam aplikasi Vue dasbor; skrip dimuat SETELAH mount dasbor). Jadwal hari ini per jam ke (Sekarang/Berikutnya/Nanti/Selesai, jam WIB), status guru: sudah absen (absensi kelas + jam ke hari ini), tanda terkirim, dilihat guru. Tombol "Kirim tanda ke semua guru jam ini" + "Tanda" per guru (`Admin\PergantianPelajaranController`, route `admin.pergantian.data|tanda`, panel diperbarui tiap 30 detik). Tabel `tanda_pelajaran` (satu jadwal + tanggal = satu baris; dikirim ulang = belum dilihat lagi). Guru: pengingat-pelajaran mengecek `asatid.tanda.cek` tiap 20 detik → jendela "Admin: sudah pergantian pelajaran"; ditutup/Absen = `asatid.tanda.lihat` (tidak tercatat saat admin "Masuk sebagai"). Pengingat otomatis tetap jalan.
- **Nama Arab di halaman admin (2026-11-14):** setiap nama santri/asatid/admin di halaman admin diberi nama Arab di bawahnya lewat komponen `<x-nama-arab :teks="..." />` / `<x-nama-arab vue="n.arab" />` (`resources/views/components/nama-arab.blade.php`, rata kiri, kosong = tidak tampil). Nomor pendaftaran yang dulu tampil di bawah nama dihapus (diganti nama Arab); nomor tetap di kartu QR, kolom No. Pendaftaran, halaman detail, dan pencarian. Nama dari akun (oleh/penilai/pencatat/pengabsen/pengirim, admin yang login): `App\Support\NamaArab` (`untukUser($ids)` sekali di awal daftar, lalu `user($id)`) dan `$user->nama_arab`; baris JSON memakai kunci `oleh_arab`. Dropdown `<select>` tidak diubah.
- **Ujian Lisan (2026-11-14):** Admin > Penilaian > Kelas & Pelajaran > tab **Ujian Lisan** (`admin/penilaian/partials/ujian-lisan.blade.php`, Vue di `kelas.blade.php`): tombol **Pengaturan Ujian Lisan** (fan + kitab boleh kosong + jumlah pertanyaan + nilai maksimal tiap pertanyaan, bisa dibedakan; aktif/mati, ubah, hapus) dan **Beri Nilai** (admin ikut menilai), lalu daftar semua nilai (saring fan + cari, ubah, hapus). Asatid > Penilaian > **Ujian Lisan** (`asatid/penilaian/lisan.blade.php`): santri → fan yang diujikan (ujian aktif) → tanggal (WIB) → nilai tiap pertanyaan. Satu santri + ujian + tanggal = satu nilai (dinilai lagi = diperbarui). Tabel `ujian_lisan` & `nilai_ujian_lisan` (nama fan/kitab & batas nilai disalin, jadi ujian yang dihapus tidak merusak nilai lama). Simpan nilai lewat `NilaiUjianLisan::simpanDari()` (dipakai admin & asatid). **Catatan per santri (2026-11-14):** langkah 5 "Catatan" (boleh kosong) di paling bawah form asatid & jendela Beri Nilai admin; kolom `nilai_ujian_lisan.catatan` (migrasi `2026_11_22_100000_tambah_catatan_nilai_ujian_lisan`, perlu `migrate` + `sesi:migrate`). Tampil di daftar nilai admin (kotak kuning di bawah nilai, ikut dicari) dan "Nilai yang Anda isi" asatid; dinilai ulang = catatan lama muncul lagi. Migrasi `2026_11_19_100000_...` (perlu `migrate` + `sesi:migrate`). Nilai Harian asatid juga punya langkah **Pilih fan / kategori** (menyaring kitab).
- **Ujian tulis (2026-11-14):** alur: admin atur ujian → asatid kirim soal → admin periksa/edit & setujui → asatid isi nilai → rekap admin.
  - **Admin > Kelas & Pelajaran > tab Ujian**: tombol **Pengaturan Ujian** (nama/periode bebas boleh kosong, fan, centang kitab ≥1 dari fan itu, nilai maksimal bawaan 100, aktif/mati, ubah, hapus), lalu **2 panel** (alamat `?tab=ujian&panel=nilai|soal`):
    - **Nilai** (`admin/penilaian/partials/ujian.blade.php`, Vue `window.UJIAN_TULIS`, nama berawalan `ut`): ringkasan per kitab (dinilai, rata-rata, tertinggi, terendah, jumlah soal), Rekap per santri (kolom per kitab), Semua nilai (✏️ ubah, 🗑️ hapus). Saringan ujian/halaqoh/cari.
    - **Soal Ujian** (`admin/penilaian/partials/ujian-soal.blade.php`, Vue `window.UJIAN_SOAL`, nama berawalan `us`): Menunggu/Disetujui/Semua; **Periksa** = edit pertanyaan/jawaban/nilai (tambah/hapus butir) + catatan → **Setujui & kirim ke asatid** (atau Simpan dulu). Kiriman asli asatid disimpan di `butir_asli`; butir yang diubah/ditambah admin diberi tanda.
    - Kedua Vue dipasang lewat `mixins: [window.UJIAN_TULIS, window.UJIAN_SOAL]` di `kelas.blade.php`; datanya dari `PenilaianController@kelas` (`ujianTulis`, `nilaiUjian`, `soalUjian`, `panelUjian`). Simpan/hapus lewat `Admin/UjianController`. Tidak ada menu sidebar Soal Ujian lagi; alamat lama `admin/penilaian/soal-ujian` diarahkan ke panel Soal.
  - **Asatid > menu Soal Ujian** (kartu sendiri di beranda, setelah Penilaian; route `asatid.soal-ujian`, view `asatid/soal-ujian.blade.php`, `Asatid/UjianController@soal`), 2 tab (`?tab=buat|disetujui`): **Buat Soal** = halaqoh (boleh beberapa) → ujian & kelompok kitab (sesuai pengaturan admin) → pertanyaan/jawaban/nilai → Kirim ke admin; "Soal saya": menunggu = ubah/hapus (dikirim ulang), disetujui = versi koreksi admin + catatan. **Soal Disetujui** = soal SEMUA asatid yang sudah disetujui admin (hanya dibaca; saringan ujian + cari; "Lihat soal" = pertanyaan, jawaban, nilai; lencana "Soal Anda"). Alamat lama `asatid/penilaian/ujian/soal` & `asatid/penilaian/kelas/ujian` diarahkan ke sini. Beranda asatid sekarang 9 kartu (laptop 3 × 3; HP: Ganti Password selebar layar lewat `penuhHp`).
  - **Nilai ujian juga per kelompok (2026-11-14):** cara pembuatan soal dari admin berlaku SAMA untuk nilai. Tombol di Nilai Ujian asatid = `u.kelompok` (sama dengan Buat Soal); kolom & kartu rekap admin = satu per kelompok. `nilai_ujian.kitab_ids` + `nilai_ujian.kelompok` (kunci `Ujian::kunci()` = id kitab urut digabung "-", mis. "2-3"); satu santri + ujian + kelompok = satu nilai; kiriman yang kitabnya tidak sama dengan kelompok admin ditolak (`Ujian::cariKelompok`). Migrasi `2026_11_23_100000_nilai_ujian_per_kelompok` (kitab_id lama -> kitab_ids [id], kelompok "id"; perlu `migrate` + `sesi:migrate`). Kalau admin MENGUBAH cara pembuatan soal setelah nilai diisi, nilai lama tetap ada di "Semua nilai" tapi tidak masuk kolom rekap (kelompoknya beda).
  - **Asatid > Penilaian > Nilai Ujian** (kelompok "Kelas & Pelajaran"; `asatid/penilaian/ujian-nilai`): ujian & kelompok kitab → halaqoh (santri dimuat `asatid.penilaian.ujian.nilai.peserta`) → tanggal → satu angka per santri (0..maks) → Simpan; hanya yang baru/berubah dikirim.
  - **Bulan ujian (2026-11-14):** di Pengaturan Ujian, kolom "Nama / periode" diganti CENTANG BULAN AJARAN (boleh 1, 2, 3 bulan sekaligus = satu ujian; dikelompokkan per tahun ajaran, bulan aktif diberi titik hijau, form baru otomatis mencentang bulan aktif). Wajib minimal 1 bulan. `ujian.nama` diisi otomatis dari nama bulan urut tahun ajaran & urutan (`Ujian::namaDariBulan`, mis. "Muharram, Rabi'ul Akhir"); tabel `ujian_bulan` (migrasi `2026_11_24_100000_create_ujian_bulan_table`, perlu `migrate` + `sesi:migrate`). Ujian lama tanpa bulan tetap memakai nama lamanya (Aktif/Mati tetap bisa; saat Ubah tinggal centang bulan).
  - **Saringan BULAN** di kartu atas tab Ujian (Semua bulan + bulan yang dipakai ujian; awalnya bulan aktif bila ada ujiannya): menyaring chip ujian, pilihan ujian & daftar nilai di panel Nilai, serta daftar & jumlah soal di panel Soal Ujian (`utUjianBulan`, `utIdUjianBulan`, `utPilihBulan`).
  - **Cara pembuatan soal diatur admin** (Pengaturan Ujian, setelah kitab dicentang): **Per kitab** (satu soal tiap kitab) · **Seluruh fan** (satu soal untuk semua kitab) · **Atur kelompok** (tiap kitab dimasukkan ke Kelompok 1/2/…; kitab satu kelompok = satu soal; setiap kitab wajib tepat di satu kelompok). Kolom `ujian.cara_soal` + `ujian.kelompok_soal` ([[2,3],[5]]). `Ujian::kelompok()` = tombol pilihan asatid di Buat Soal; `Ujian::cariKelompok()` menolak kiriman yang kitabnya tidak sama persis dengan salah satu kelompok. Soal menyimpan `soal_ujian.kitab_ids` (json) + nama kelompok di kolom `kitab` (mis. "jurmiyyah + متممة" / "Seluruh fan Nahwu"). Migrasi `2026_11_21_100000_ujian_kelompok_soal` (kitab_id lama disalin ke kitab_ids lalu dihapus; perlu `migrate` + `sesi:migrate`).
  - Tabel `ujian`, `ujian_kitab`, `soal_ujian`, `nilai_ujian` (migrasi `2026_11_20_100000_create_ujian_tables`, perlu `migrate` + `sesi:migrate`). Satu santri + ujian + kitab = satu nilai (diisi lagi = diperbarui). Nama ujian/fan/kitab disalin ke soal & nilai. Model `Ujian`, `SoalUjian` (cek butir di `butirDari()`), `NilaiUjian` (`simpanDari()`).
- **Penilaian Dakwah (2026-11-10):** Admin > PENILAIAN > **Penilaian Dakwah** (`admin/penilaian/dakwah`, `Admin/PenilaianDakwahController`), 2 panel: **Khitoba'** (tombol Pengaturan Nilai = jenis nilai + waktu khitoba'; Beri Nilai = santri → waktu → tanggal → nilai → catatan) dan **Khurujud Dakwah** (tombol Jenis Nilai; Beri Nilai = santri → tanggal → nilai → catatan). Di bawahnya rincian hasil (cari + hapus). Pilihan ditulis admin sendiri (tombol +) di tabel `pilihan_dakwah`; hasil di `nilai_dakwah` (nama nilai/waktu disimpan sebagai tulisan, jadi pilihan yang dihapus tidak merusak nilai lama). Migrasi `2026_11_16_100000_...` (perlu `migrate` + `sesi:migrate`). Ringkasan gabungan: Admin > LAPORAN > **Laporan Dakwah** (`admin/laporan-dakwah`): saringan periode & jenis, angka ringkas, sebaran nilai, rekap per santri, semua penilaian. Asatid belum bisa menilai dakwah.
- **Perizinan (2026-11-14):** Admin > PENILAIAN > **Perizinan** (`admin/penilaian/perizinan`, `Admin/PerizinanController`, khusus admin). Tombol **Jenis Izin** (nama ditulis admin sendiri, tabel `jenis_izin`) dan **Beri Izin** (santri → jenis → mulai tanggal+jam → berakhir tanggal+jam → catatan; tabel `izin_santri`). Satu santri boleh beberapa izin sehari. Tombol **Sudah kembali** mencatat waktu kembali (otomatis jam sekarang WIB, bisa diubah / dibatalkan). Tombol **Pindai Kembali** (`PerizinanController@pindai`, route `admin.penilaian.perizinan.pindai`): kartu QR santri (`kode_qr`) dipindai kamera (html5-qrcode lewat `window.muatPemindaiQr`, sama seperti absensi kegiatan) atau alat scanner USB / ketik nomor pendaftaran → izin yang sedang berjalan & belum kembali (batas paling awal) ditandai kembali sekarang; hasil tepat / terlambat / tidak sedang izin tampil besar + bunyi bip. Kamera butuh https atau localhost. Status: Belum mulai / Sedang izin / Terlambat / Kembali tepat waktu (dihitung ulang tiap 30 detik di layar pakai jam server WIB). **Terlambat** (kembali lewat batas, atau batas lewat tapi belum kembali) otomatis tampil di Penilaian > Catatan > Pelanggaran (`DataCatatan::barisIzin`, sumber `izin`, label "Terlambat izin"; tidak disimpan ke `catatan_siswa`, jadi ikut berubah bila waktu kembali dikoreksi). Waktu disimpan sebagai WIB apa adanya (`IzinSantri::sekarang()`, jangan `now()` biasa). Migrasi `2026_11_18_100000_create_perizinan_tables` (perlu `migrate` + `sesi:migrate`).
- **Aktif / Nonaktif akun (2026-11-14):** Sistem > Daftar User, kolom **Status** (tombol geser, khusus Super Admin, tidak bisa untuk akun sendiri; route `admin.akun.aktif`). Kolom `users.aktif` (migrasi `2026_11_18_110000_tambah_aktif_users`, perlu `migrate` + `sesi:migrate`; akun admin/asatid tersalin ke semua sesi lewat AkunBersama). Nonaktif = ditolak di `LoginController` dan yang sedang masuk dikeluarkan oleh `CekSesiLogin` (kecuali saat Super Admin "Masuk sebagai" akun itu).
- Master Data **Jenis Pelanggaran & Jenis Pencapaian sudah DIHAPUS** (model, tab, seeder, migrasi). Migrasi `2026_11_14_130000_hapus_jenis_pelanggaran_pencapaian` membuang tabelnya di server.
- **Kelulusan** disembunyikan dari menu Akademik (akan diperbaiki); halaman `admin/kelulusan` & datanya masih ada.

### Data Santri: Ketua & Wakil kamar (2026-11-06)
- Kartu kamar > daftar anggota: tombol ⋮ per santri → Ketua / Wakil / Anggota biasa (`AdminSantriController@jabatanKamar`, rute `admin.santri.jabatan-kamar`). Lencana tampil di daftar anggota, denah kasur, dan kolom Kamar tabel.
- Kolom `pendaftaran_siswas.jabatan_kamar` (migrasi `2026_11_14_110000_...`). Satu kamar maks 1 ketua & 1 wakil (yang lama otomatis lepas). Pindah kamar → jabatan lepas (model `booted()` + Kelola Massal).

### Daftar Peserta asatid: Kartu / Foto
- Tombol Kartu | Foto di `asatid/peserta.blade.php`; pilihan diingat per perangkat (localStorage `tampilan_peserta`).

### Notifikasi pojok kanan bawah (semua halaman)
- File: `resources/views/partials/notifikasi.blade.php`, dipasang di semua layout + login, PSB, Dashboard HP, pendaftaran asatid/admin.
- Otomatis dari server: `->with('success'|'sukses'|'status')` hijau, `'error'` merah, `'warning'` kuning, `$errors` merah ("Ada N isian yang belum benar").
- Dari JavaScript: `notifikasi.sukses('…')`, `.gagal()`, `.peringatan()`, `.info()`. Menutup sendiri (5 dtk, error 8 dtk), berhenti saat disentuh/kursor, ✕ untuk tutup. Tampil di atas pop-up.
- Kotak pesan sukses/gagal lama di atas halaman sudah dihapus. Ringkasan `$errors` di formulir yang kolomnya tidak punya teks merah sendiri DIBIARKAN.
- Halaman baru: cukup `->with('success', '…')` di controller, tidak perlu kotak pesan di view.

### Formulir pendaftaran: validasi & pesan error
- Berkas dibaca & dicek (ukuran 5 MB, format JPG/PNG/PDF; pas foto JPG/PNG/WEBP) SAAT DIPILIH (`pilihFile`). Berkas dari Drive/awan yang tidak bisa dibaca -> pesan di kolom berkas, bukan "koneksi".
- Gagal kirim: pesan sesuai jenis (403 ditutup, 413 terlalu besar, 419 sesi habis, 429 terlalu sering, 5xx server, offline vs berkas tidak terkirim).
- Semua pesan validasi server berbahasa Indonesia (`StorePendaftaranRequest::messages/attributes`). Error riwayat pondok/kitab diarahkan ke kolomnya (langkah 4).
- Kelurahan, Nama & No. WA Muarrif, Nama & No. WA yang bisa dihubungi, Nama Sekolah, Nama Daerah: WAJIB (browser & server).

### Data Santri: cek persyaratan belum lengkap
- Kotak "Persyaratan belum lengkap" di atas pencarian: jumlah santri + satu tombol per syarat (klik = saring tabel).
- Saringan "Persyaratan" di panel Filter; kolom "Persyaratan" (3/5 + nama syarat yang belum) di tabel & kartu HP.
- Alamat `admin/santri?syarat=kurang` langsung tersaring; notifikasi lonceng "Persyaratan belum lengkap" memakai alamat ini.
- Data dari `AdminSantriController::baris()` → `syarat_kurang`. Syarat baru cukup ditambah di `PendaftaranSiswa::SYARAT`.

### Tombol koneksi & muat ulang bisa ditarik
- Satu file untuk semua halaman: `resources/views/partials/status-koneksi.blade.php`.
- Bisa ditarik (jari/mouse); saat dilepas menempel ke sisi kiri/kanan terdekat. Posisi diingat per perangkat (localStorage `posisi_status_koneksi`).
- Klik dua kali pegangan (titik-titik) = kembali ke posisi awal.

### Header halaman admin seragam
- **Gaya header:** kartu hijau tua dengan label kecil, judul, keterangan, dan tombol di kanan.
- **Komponen bersama:** `resources/views/components/header-admin.blade.php`. Contoh pemakaian dan daftar gaya tombol ada di bagian atas file itu.

### Jadwal: nama ustadz Indonesia + Arab
- **Tempat:** grid, per kelas, per ustadz, pilihan ustadz, dan Jadwal Hari Ini di dasbor.
- **Asal nama Arab:** isian *Nama Lengkap (Arab)* di Data Asatid.

### Kotak Pesan admin: Siswa · Asatid · Umum
- **Halaman admin:** menu **Kotak Pesan** (`admin/pesan-peserta?asal=siswa|asatid|umum`), lonceng admin ikut memberi tahu.
- **Asatid → admin:** dasbor asatid › **Pesan untuk Admin** (`asatid/pesan-saya`). Disimpan di `pesan_asatid` (database utama) dengan kolom `dari` = `asatid`; balasan admin = `dari` `admin` dan muncul di "Pesan dari Admin".
- **Pengunjung → admin:** tombol **Hubungi Admin** (hero landing) dan **Hubungi Admin langsung** (footer) membuka `partials/hubungi-admin`. Disimpan di tabel `pesan_umum` (database utama). Admin membalas lewat tautan WA/email.

---

## 4. Masalah yang sudah dibereskan

- **Formulir pendaftaran atau edit santri kadang "Server Error" (500).**
  - Penyebab: kolom **tujuan** dan **rencana** di database hanya muat 255 huruf.
  - Perbaikan: kolom diperbesar menjadi TEXT (migrasi `2026_11_12_100000_perbesar_tujuan_rencana_pendaftaran_siswas.php`), dan batas formulir menjadi 5.000 huruf.
- **Pesan error nomor WhatsApp keliru ("harus huruf Arab").**
  - Perbaikan: pesan dibetulkan di formulir pendaftaran santri dan di halaman edit asatid/admin.
  - Contoh nomor di formulir sekarang `+6281234567890`.

---

## 5. Belum dikerjakan (bisa diminta nanti)

- **Penilaian Kelas & Pelajaran:** Nilai Harian & Ujian Lisan sudah jadi; alur Ujian (tulis) menunggu penjelasan dari pengguna.
- **Kelulusan:** menunya disembunyikan, menunggu perbaikan.

- **Formulir pendaftaran online**, perbaikan yang sudah diusulkan tapi belum dipasang:
  - WhatsApp dikirim *setelah* peserta mendapat jawaban, supaya formulir tidak tertahan saat Fonnte lambat;
  - cegah tombol Kirim terpencet dua kali;
  - ambil token baru otomatis kalau sesi halaman habis, supaya isian tidak hilang;
  - cek total ukuran berkas sebelum dikirim.
  - Pola yang sama berlaku untuk formulir asatid dan admin.
- **Terjemahan:**
  - Pesan dari sistem (notifikasi lonceng, pesan berhasil/gagal) belum ikut diterjemahkan.
  - Nama hari dan bulan masih bahasa Indonesia.
- **Halaman publik dan login** belum punya tombol bahasa dan belum berbalik kanan-ke-kiri.
- **Cache terjemahan:** kalau tab Bahasa dibuka sebelum tabel `terjemahan` dibuat di server, daftarnya bisa "tersangkut kosong". Untuk sementara jalankan `php artisan optimize:clear`. Kodenya bisa diperbaiki supaya tidak terjadi lagi.
- **Foto 404 di server:** file fotonya tidak ada di server. Ini bukan penyebab gagal simpan.
- **Semua halaman admin versi HP:** Tahap 1–3 selesai (2026-11-05). Sisa kecil: hapus/geser jadwal di tampilan per hari Jadwal (HP) belum ada; beberapa kata baru belum diterjemahkan Arab ("Pilih semua", "Diubah", "Nonaktif").
- **Admin ikut mengabsen kegiatan (2026-11-14):** Penilaian > Absensi Kegiatan (menu dipindah dari AKADEMIK ke PENILAIAN; alamat tetap `admin/absensi-kegiatan`). Kartu kegiatan yang **aktif** punya tombol **Absen** → `admin.absensi-kegiatan.catat` (`AbsensiKegiatanController@catat/simpanCatatan/status`). Halaman catat (pindai QR / cari nama) dipakai bersama asatid & admin: tampilan `absensi/partials/catat-kegiatan.blade.php` (variabel `$rute` simpan/status/selesai), aturan simpan `App\Support\CatatKegiatan`. Catatan admin & asatid saling terlihat (pembaruan 10 detik); kegiatan nonaktif/ditutup ditolak.
