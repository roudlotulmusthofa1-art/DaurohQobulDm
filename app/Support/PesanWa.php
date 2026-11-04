<?php

namespace App\Support;

use App\Models\Pengaturan;

/**
 * Template pesan WhatsApp yang bisa diubah admin di Pengaturan > Pesan.
 * Disimpan di database utama (berlaku untuk semua sesi) dengan kunci "pesan_wa_{kunci}" dan "pesan_wa_{kunci}_aktif".
 *
 * Cara pakai di controller:
 *   $pesan = PesanWa::buat('psb_terima', ['nama' => ..., 'email' => ...]);
 *   if ($pesan) $wa->kirim($nomor, $pesan);     // null = pesan dimatikan admin
 *
 * Isian {catatan}, {penempatan}, {syarat} sudah berisi judulnya sendiri
 * dan otomatis hilang bila datanya kosong.
 */
class PesanWa
{
    public const GRUP = [
        'psb' => 'Pendaftaran Santri (PSB)',
        'asatid' => 'Pendaftaran Asatid',
        'admin' => 'Pendaftaran Admin',
    ];

    /** Keterangan semua isian yang bisa dipakai di template */
    public const ISIAN = [
        'nama' => 'Nama lengkap pendaftar',
        'nomor' => 'Nomor pendaftaran',
        'email' => 'Email login',
        'password' => 'Password baru (hanya untuk pesan reset password)',
        'link_login' => 'Alamat halaman login',
        'alasan' => 'Alasan penolakan dari admin',
        'catatan' => 'Catatan admin (beserta judulnya, hilang bila kosong)',
        'penempatan' => 'Kelas & kamar (beserta judulnya, hilang bila kosong)',
        'syarat' => 'Status persyaratan santri',
        'pesantren' => 'Nama pesantren (Pengaturan > Umum)',
    ];

    public const TEMPLATE = [
        'psb_daftar' => [
            'grup' => 'psb', 'judul' => 'Pendaftaran diterima sistem', 'penerima' => 'Peserta (nomor WhatsApp peserta)',
            'kapan' => 'Setelah calon santri mengirim formulir PSB.',
            'isian' => ['nama', 'nomor', 'pesantren'],
            'bawaan' => "Assalamu'alaikum, pendaftaran santri *{nama}* berhasil kami terima.\nNomor pendaftaran: *{nomor}*\nData sedang diverifikasi admin. Kami akan menghubungi kembali setelah ada keputusan.",
        ],
        'psb_terima' => [
            'grup' => 'psb', 'judul' => 'Santri diterima', 'penerima' => 'Peserta (nomor WhatsApp peserta)',
            'kapan' => 'Saat admin menekan tombol Terima di Data Masuk Peserta.',
            'isian' => ['nama', 'penempatan', 'syarat', 'catatan', 'link_login', 'email', 'pesantren'],
            'bawaan' => "Assalamu'alaikum, alhamdulillah santri *{nama}* dinyatakan *DITERIMA*.\n\n{penempatan}\n\n*Status persyaratan:*\n{syarat}\n\n{catatan}\n\nSilakan masuk ke akun siswa:\n{link_login}\nEmail: *{email}*\nPassword: sesuai yang Anda isi saat mendaftar.",
        ],
        'psb_tolak' => [
            'grup' => 'psb', 'judul' => 'Santri ditolak', 'penerima' => 'Peserta (nomor WhatsApp peserta)',
            'kapan' => 'Saat admin menolak pendaftar.',
            'isian' => ['nama', 'alasan', 'pesantren'],
            'bawaan' => "Assalamu'alaikum, mohon maaf pendaftaran santri *{nama}* *DITOLAK*.\n\nAlasan:\n{alasan}",
        ],
        'psb_reset_password' => [
            'grup' => 'psb', 'judul' => 'Reset password peserta', 'penerima' => 'Peserta (nomor WhatsApp peserta)',
            'kapan' => 'Saat admin menekan "Reset Password" di detail peserta (peserta lupa password).',
            'isian' => ['nama', 'email', 'password', 'link_login', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, password akun siswa Anda sudah direset oleh admin.\n\nMasuk di:\n{link_login}\nEmail: *{email}*\nPassword baru: *{password}*\n\nMohon simpan pesan ini dan jangan bagikan ke orang lain.",
        ],
        'asatid_daftar' => [
            'grup' => 'asatid', 'judul' => 'Pendaftaran diterima sistem', 'penerima' => 'Pendaftar asatid',
            'kapan' => 'Setelah calon asatid mengirim formulir pendaftaran.',
            'isian' => ['nama', 'nomor', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, pendaftaran Anda sebagai asatid berhasil kami terima.\nNomor pendaftaran: *{nomor}*\nData sedang diverifikasi admin. Kami akan menghubungi kembali setelah ada keputusan.",
        ],
        'asatid_terima' => [
            'grup' => 'asatid', 'judul' => 'Asatid diterima', 'penerima' => 'Pendaftar asatid',
            'kapan' => 'Saat admin menerima pendaftar asatid.',
            'isian' => ['nama', 'catatan', 'link_login', 'email', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, alhamdulillah pendaftaran Anda sebagai *ASATID* dinyatakan *DITERIMA*.\n\n{catatan}\n\nSilakan masuk ke akun asatid:\n{link_login}\nEmail: *{email}*\nPassword: sesuai yang Anda isi saat mendaftar.",
        ],
        'asatid_tolak' => [
            'grup' => 'asatid', 'judul' => 'Asatid ditolak', 'penerima' => 'Pendaftar asatid',
            'kapan' => 'Saat admin menolak pendaftar asatid.',
            'isian' => ['nama', 'alasan', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, mohon maaf pendaftaran Anda sebagai asatid *DITOLAK*.\n\nAlasan:\n{alasan}",
        ],
        'asatid_reset_password' => [
            'grup' => 'asatid', 'judul' => 'Reset password asatid', 'penerima' => 'Asatid (nomor WhatsApp asatid)',
            'kapan' => 'Saat admin menekan "Reset Password" di detail Data Asatid (asatid lupa password).',
            'isian' => ['nama', 'email', 'password', 'link_login', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, password akun asatid Anda sudah direset oleh admin.\n\nMasuk di:\n{link_login}\nEmail: *{email}*\nPassword baru: *{password}*\n\nMohon simpan pesan ini dan jangan bagikan ke orang lain.",
        ],
        'admin_daftar' => [
            'grup' => 'admin', 'judul' => 'Pendaftaran diterima sistem', 'penerima' => 'Pendaftar admin',
            'kapan' => 'Setelah calon admin mengirim formulir pendaftaran.',
            'isian' => ['nama', 'nomor', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, pendaftaran Anda sebagai admin berhasil kami terima.\nNomor pendaftaran: *{nomor}*\nData sedang diverifikasi. Kami akan menghubungi kembali setelah ada keputusan.",
        ],
        'admin_terima' => [
            'grup' => 'admin', 'judul' => 'Admin diterima', 'penerima' => 'Pendaftar admin',
            'kapan' => 'Saat admin menerima pendaftar admin.',
            'isian' => ['nama', 'catatan', 'link_login', 'email', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, alhamdulillah pendaftaran Anda sebagai *ADMIN* dinyatakan *DITERIMA*.\n\n{catatan}\n\nSilakan masuk ke akun admin:\n{link_login}\nEmail: *{email}*\nPassword: sesuai yang Anda isi saat mendaftar.",
        ],
        'admin_tolak' => [
            'grup' => 'admin', 'judul' => 'Admin ditolak', 'penerima' => 'Pendaftar admin',
            'kapan' => 'Saat admin menolak pendaftar admin.',
            'isian' => ['nama', 'alasan', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, mohon maaf pendaftaran Anda sebagai admin *DITOLAK*.\n\nAlasan:\n{alasan}",
        ],
        'admin_reset_password' => [
            'grup' => 'admin', 'judul' => 'Reset password admin', 'penerima' => 'Admin (nomor WhatsApp admin)',
            'kapan' => 'Saat Super Admin menekan "Reset Password" di detail Data Admin (admin lupa password).',
            'isian' => ['nama', 'email', 'password', 'link_login', 'pesantren'],
            'bawaan' => "Assalamu'alaikum *{nama}*, password akun admin Anda sudah direset oleh Super Admin.\n\nMasuk di:\n{link_login}\nEmail: *{email}*\nPassword baru: *{password}*\n\nMohon simpan pesan ini dan jangan bagikan ke orang lain.",
        ],
    ];

    /** Contoh data untuk pratinjau di halaman pengaturan */
    public const CONTOH = [
        'nama' => 'Muhammad Fulan',
        'nomor' => 'PSB20260001',
        'email' => 'fulan@contoh.com',
        'link_login' => 'https://daurohqobul.com/login',
        'alasan' => 'Kuota kelas sudah penuh.',
        'catatan' => "Harap membawa kitab sendiri.",
        'penempatan' => "Kelas: I'dadiyah\nKamar: Abu Bakar (kasur 3)",
        'syarat' => "✅ Paspor\n✅ Surat rekomendasi",
        'pesantren' => 'Dauroh Qobul',
    ];

    /** Teks template yang dipakai sekarang (ubahan admin, atau bawaan) */
    public static function teks(string $kunci): string
    {
        $isi = Pengaturan::ambilPusat('pesan_wa_' . $kunci);

        return filled($isi) ? $isi : self::TEMPLATE[$kunci]['bawaan'];
    }

    public static function diubah(string $kunci): bool
    {
        return filled(Pengaturan::ambilPusat('pesan_wa_' . $kunci));
    }

    public static function aktif(string $kunci): bool
    {
        return Pengaturan::ambilPusat('pesan_wa_' . $kunci . '_aktif', '1') === '1';
    }

    /** Pesan siap kirim, atau null bila pesan ini dimatikan admin */
    public static function buat(string $kunci, array $data): ?string
    {
        if (! self::aktif($kunci)) {
            return null;
        }

        return self::isi(self::teks($kunci), $data);
    }

    /** Ganti {isian} dengan data. Isian opsional diberi judul, dan baris kosong berlebih dirapikan. */
    public static function isi(string $template, array $data): string
    {
        $data += ['pesantren' => Pengaturan::profilPesantren()['nama'] ?? '', 'link_login' => url('/login')];
        if (filled($data['catatan'] ?? null)) {
            $data['catatan'] = "*Catatan admin:*\n" . trim($data['catatan']);
        }
        if (filled($data['penempatan'] ?? null)) {
            $data['penempatan'] = "*Penempatan:*\n" . trim($data['penempatan']);
        }

        $hasil = preg_replace_callback('/\{(\w+)\}/', fn ($m) => array_key_exists($m[1], self::ISIAN) ? (string) ($data[$m[1]] ?? '') : $m[0], $template);

        // Isian kosong meninggalkan baris kosong -> rapikan jadi paling banyak satu baris kosong
        $hasil = preg_replace("/[ \t]+\n/", "\n", $hasil);
        $hasil = preg_replace("/\n{3,}/", "\n\n", $hasil);

        return trim($hasil);
    }
}
