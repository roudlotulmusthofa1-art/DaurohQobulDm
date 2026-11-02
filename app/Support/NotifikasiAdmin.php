<?php

namespace App\Support;

use App\Models\Kamar;
use App\Models\PendaftaranAdmin;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Route;

/**
 * Notifikasi untuk admin (ikon lonceng di kanan atas).
 *
 * Semua notifikasi dihitung langsung dari data yang ada (tidak disimpan terpisah),
 * jadi otomatis hilang begitu masalahnya sudah dibereskan.
 * Tiap jenis bisa dihidupkan/dimatikan di menu Sistem > Pengaturan.
 */
class NotifikasiAdmin
{
    /**
     * Daftar jenis notifikasi.
     * Menambah jenis baru: tambah satu baris di sini + satu blok di kumpulkan().
     */
    public const JENIS = [
        'pendaftar_baru' => [
            'judul' => 'Pendaftar baru',
            'keterangan' => 'Ada pendaftar yang masih menunggu verifikasi.',
            'ikon' => 'user-plus',
            'warna' => 'amber',
        ],
        'asatid_baru' => [
            'judul' => 'Pendaftar asatid baru',
            'keterangan' => 'Ada calon asatid yang menunggu verifikasi.',
            'ikon' => 'cap',
            'warna' => 'emerald',
        ],
        'admin_baru' => [
            'judul' => 'Pendaftar admin baru',
            'keterangan' => 'Ada calon admin yang menunggu verifikasi.',
            'ikon' => 'user-plus',
            'warna' => 'sky',
        ],
        'catatan_peserta' => [
            'judul' => 'Catatan dari peserta',
            'keterangan' => 'Pendaftar yang menunggu menulis catatan untuk admin.',
            'ikon' => 'chat',
            'warna' => 'sky',
        ],
        'syarat_belum_lengkap' => [
            'judul' => 'Persyaratan belum lengkap',
            'keterangan' => 'Santri sudah diterima, tapi ada persyaratan yang belum dicentang.',
            'ikon' => 'clipboard',
            'warna' => 'rose',
        ],
        'belum_kelas' => [
            'judul' => 'Santri belum dapat kelas',
            'keterangan' => 'Santri sudah diterima, tapi kelasnya belum dipilih.',
            'ikon' => 'cap',
            'warna' => 'emerald',
        ],
        'belum_kamar' => [
            'judul' => 'Santri belum dapat kamar/kasur',
            'keterangan' => 'Santri sudah diterima, tapi kamar atau nomor kasurnya belum diatur.',
            'ikon' => 'bed',
            'warna' => 'violet',
        ],
        'kamar_penuh' => [
            'judul' => 'Kamar penuh',
            'keterangan' => 'Isi kamar sudah mencapai kapasitas.',
            'ikon' => 'home',
            'warna' => 'orange',
        ],
    ];

    /** Path ikon (gaya Heroicons outline). */
    public const IKON = [
        'user-plus' => 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z',
        'chat' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
        'clipboard' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
        'cap' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
        'bed' => 'M3 7.5V18m18-6v6M3 15h18M3 12h18v-1.5A2.25 2.25 0 0018.75 8.25h-7.5V12M7.5 10.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z',
        'home' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    ];

    /** Kelas warna ikon (ditulis lengkap supaya Tailwind ikut membuatnya). */
    public const WARNA = [
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'sky' => 'bg-sky-50 text-sky-600 ring-sky-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
        'orange' => 'bg-orange-50 text-orange-600 ring-orange-100',
    ];

    /** Apakah jenis notifikasi ini dihidupkan? (bawaan: hidup) */
    public static function aktif(string $jenis): bool
    {
        return Pengaturan::ambil('notif_' . $jenis, '1') !== '0';
    }

    /**
     * Kumpulkan notifikasi yang sedang aktif.
     *
     * @return array{items: array<int, array>, total: int}
     */
    public static function kumpulkan(): array
    {
        $items = [];
        $total = 0;
        $diterima = fn () => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA);
        $urlSantri = Route::has('admin.santri.index') ? route('admin.santri.index') : null;

        // 1. Pendaftar baru: satu baris per pendaftar (5 terbaru)
        if (static::aktif('pendaftar_baru')) {
            $q = PendaftaranSiswa::where('status', PendaftaranSiswa::MENUNGGU);
            $total += (clone $q)->count();
            foreach ($q->latest()->take(5)->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'created_at']) as $p) {
                $items[] = static::item('pendaftar_baru',
                    'Pendaftar baru: <b>' . e($p->nama_lengkap) . '</b>',
                    $p->nomor_pendaftaran,
                    route('admin.pendaftaran.show', $p->id),
                    $p->created_at);
            }
        }

        // 1b. Pendaftar asatid baru: satu baris per pendaftar (5 terbaru)
        if (static::aktif('asatid_baru')) {
            $q = PendaftaranAsatid::where('status', PendaftaranAsatid::MENUNGGU);
            $total += (clone $q)->count();
            foreach ($q->latest()->take(5)->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'created_at']) as $a) {
                $items[] = static::item('asatid_baru',
                    'Pendaftar asatid baru: <b>' . e($a->nama_lengkap) . '</b>',
                    $a->nomor_pendaftaran,
                    route('admin.asatid.show', $a->id),
                    $a->created_at);
            }
        }

        // 1c. Pendaftar admin baru: satu baris per pendaftar (5 terbaru)
        if (static::aktif('admin_baru')) {
            $q = PendaftaranAdmin::where('status', PendaftaranAdmin::MENUNGGU);
            $total += (clone $q)->count();
            foreach ($q->latest()->take(5)->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'created_at']) as $a) {
                $items[] = static::item('admin_baru',
                    'Pendaftar admin baru: <b>' . e($a->nama_lengkap) . '</b>',
                    $a->nomor_pendaftaran,
                    route('admin.data-admin.show', $a->id),
                    $a->created_at);
            }
        }

        // 2. Catatan dari peserta: satu baris per pendaftar (5 terbaru)
        if (static::aktif('catatan_peserta')) {
            $q = PendaftaranSiswa::where('status', PendaftaranSiswa::MENUNGGU)
                ->whereNotNull('catatan_peserta')->where('catatan_peserta', '!=', '');
            $total += (clone $q)->count();
            foreach ($q->latest()->take(5)->get(['id', 'nama_lengkap', 'created_at']) as $p) {
                $items[] = static::item('catatan_peserta',
                    '<b>' . e($p->nama_lengkap) . '</b> menulis catatan untuk admin',
                    'Baca di detail siswa',
                    route('admin.pendaftaran.show', $p->id),
                    $p->created_at);
            }
        }

        // 3-5. Ringkasan per jenis (satu baris berisi jumlah)
        $ringkasan = [
            'syarat_belum_lengkap' => [
                fn ($q) => $q->where(function ($w) {
                    foreach (array_keys(PendaftaranSiswa::SYARAT) as $kolom) {
                        $w->orWhere($kolom, false)->orWhereNull($kolom);
                    }
                }),
                ':n santri diterima, persyaratannya belum lengkap',
            ],
            'belum_kelas' => [
                fn ($q) => $q->whereNull('kelas_id'),
                ':n santri diterima belum dapat kelas',
            ],
            'belum_kamar' => [
                fn ($q) => $q->where(fn ($w) => $w->whereNull('kamar_id')->orWhereNull('nomor_kasur')),
                ':n santri diterima belum dapat kamar/kasur',
            ],
        ];
        foreach ($ringkasan as $jenis => [$saring, $teks]) {
            if (! static::aktif($jenis)) {
                continue;
            }
            $n = $saring($diterima())->count();
            if ($n > 0) {
                $total += $n;
                $items[] = static::item($jenis, str_replace(':n', '<b>' . $n . '</b>', $teks), 'Lihat data santri', $urlSantri);
            }
        }

        // 6. Kamar penuh: satu baris per kamar
        if (static::aktif('kamar_penuh')) {
            $urlKamar = Route::has('admin.master-data.index') ? route('admin.master-data.index', ['tab' => 'kamar']) : null;
            $kamarPenuh = Kamar::where('is_active', true)->where('kapasitas', '>', 0)
                ->withCount('santri')->get()
                ->filter(fn ($k) => $k->santri_count >= $k->kapasitas);
            $total += $kamarPenuh->count();
            foreach ($kamarPenuh->take(5) as $k) {
                $items[] = static::item('kamar_penuh',
                    'Kamar <b>' . e($k->nama) . '</b> sudah penuh',
                    "{$k->santri_count}/{$k->kapasitas} santri",
                    $urlKamar);
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    private static function item(string $jenis, string $teksHtml, ?string $keterangan, ?string $url, $waktu = null): array
    {
        $j = static::JENIS[$jenis];

        return [
            'jenis' => $jenis,
            'teks' => $teksHtml, // sudah di-escape di atas (e()), aman ditampilkan dengan {!! !!}
            'keterangan' => $keterangan,
            'url' => $url,
            'waktu' => $waktu?->locale('id')->diffForHumans(),
            'ikon' => static::IKON[$j['ikon']],
            'warna' => static::WARNA[$j['warna']],
        ];
    }
}
