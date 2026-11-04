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

### Admin versi HP (layout)
- **Cara kerja:** dilihat dari **lebar layar**. Di bawah 1024px (`lg`), sidebar disembunyikan, bilah atas jadi hijau tua ringkas (tombol kembali + judul halaman), dan muncul menu bawah: Beranda, PSB, Santri, Pesan, Menu.
- **Tombol Menu** membuka lembar berisi semua menu sesuai izin role, Lihat Website, dan Keluar.
- **File penting:** `resources/views/layouts/admin.blade.php`, `resources/views/partials/menu-bawah-admin.blade.php`.
- Dashboard tetap punya file HP sendiri (`admin/dashboard-hp.blade.php`).
- **Tabel jadi kartu (pola):** di atas tabel ada blok `lg:hidden` berisi kartu per baris; tabelnya diberi `hidden lg:block`. Tombol di kartu memakai aksi/handler yang sama dengan tabel. Dipakai di Data Masuk, Data Santri, Buku Kas, Kelulusan, Laporan Absensi, Master Data, Daftar User.
- **Jadwal Mengajar di HP:** pilihan hari (bisa digeser) + daftar jam pelajaran hari itu. Hapus & geser jadwal hanya di laptop atau tampilan Per Kelas/Per Ustadz.
- **Aturan grid untuk HP:** grid satu kolom tulis `grid grid-cols-1 md:grid-cols-2` (bukan `grid md:grid-cols-2`), supaya teks panjang (email) tidak mendorong kotak lebih lebar dari layar.
- Bilah simpan & toast yang menempel di bawah dinaikkan di HP (`bottom-24 lg:bottom-…`) agar tidak tertutup menu bawah.

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
