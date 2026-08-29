<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Tampilkan halaman depan (landing page) Ribath Masjid Riyadh Solo.
     */
    public function index(): View
    {
        $stats = [
            ['value' => 200, 'suffix' => '+', 'label' => 'Santri Aktif'],
            ['value' => 15, 'suffix' => '', 'label' => 'Tahun Berpengalaman'],
            ['value' => null, 'suffix' => '', 'label' => 'Program Terpadu'],
        ];

        $fitur = [
            [
                'icon' => 'book',
                'color' => 'blue',
                'title' => 'Manajemen Akademik',
                'desc' => 'Kurikulum 45 kitab klasik dengan tracking progress real-time untuk setiap santri',
                'points' => ['45+ Kitab Klasik', 'Progress Real-time', 'Assessment Digital'],
            ],
            [
                'icon' => 'users',
                'color' => 'purple',
                'title' => 'Portal Wali Santri',
                'desc' => 'Pantau perkembangan putra Anda kapan saja melalui dashboard yang mudah digunakan',
                'points' => ['Dashboard Interaktif', 'Laporan Berkala', 'Notifikasi Real-time'],
            ],
            [
                'icon' => 'calendar',
                'color' => 'orange',
                'title' => 'Jadwal Terintegrasi',
                'desc' => '18+ jam aktivitas harian terorganisir dari subuh hingga malam dengan reminder otomatis',
                'points' => ['18+ Jam Aktivitas', 'Reminder Otomatis', 'Sinkronisasi Cloud'],
            ],
        ];

        $kurikulum = [
            [
                'icon' => 'book-open',
                'color' => 'blue',
                'title' => 'Kelas Tamhidi',
                'level' => 'Kelas 1',
                'duration' => '1 Tahun',
                'desc' => 'Membekali santri baru dengan kemampuan bahasa Arab dan pelajaran dasar agama yang wajib diketahui oleh seorang Muslim. Dapat dilewati melalui ujian.',
                'points' => ['Bahasa Arab Dasar', 'Fiqh Pemula', 'Akidah Islam'],
                'progress' => 1,
            ],
            [
                'icon' => 'academic-cap',
                'color' => 'purple',
                'title' => 'Ibtida 1 & 2',
                'level' => 'Kelas 2-3',
                'duration' => '2 Tahun',
                'desc' => 'Mematangkan fiqh dan nahwu dari dasar, praktek membaca untuk meningkatkan kemampuan membaca, dan mengajarkan kitab dasar dari mata pelajaran lain.',
                'points' => ['Fiqh Menengah', 'Nahwu Shorf', 'Hadits Dasar'],
                'progress' => 2,
            ],
            [
                'icon' => 'globe',
                'color' => 'green',
                'title' => 'Tsanawiyah 1 & 2',
                'level' => 'Kelas 4-5',
                'duration' => '2 Tahun',
                'desc' => 'Pematangan fiqh tingkat lanjut sebagai persiapan masuk kelas takhassus dan penguasaan ilmu lain untuk pengembangan.',
                'points' => ['Fiqh Lanjutan', 'Ushul Fiqh', 'Tafsir Al-Quran'],
                'progress' => 3,
            ],
            [
                'icon' => 'target',
                'color' => 'orange',
                'title' => 'Program Takhassus',
                'level' => 'Spesialisasi',
                'duration' => '2-3 Tahun',
                'desc' => 'Penjurusan lebih dalam ke suatu ilmu. Saat ini tersedia jurusan ilmu fiqh dengan rancangan yang terus dikembangkan.',
                'points' => ['Spesialisasi Fiqh', 'Penelitian Mendalam', 'Metodologi Ijtihad'],
                'progress' => 4,
                'highlight' => true,
            ],
            [
                'icon' => 'award',
                'color' => 'teal',
                'title' => '45 Kitab Kuning Klasik',
                'level' => 'Semua Level',
                'duration' => '5 Tahun',
                'desc' => 'Pembelajaran komprehensif 45 kitab klasik meliputi Nahwu, Shorf, Fiqh, Tauhid, Tajwid, Tafsir, Hadits, Ushul Fiqh, Mantiq, dan Balaghoh.',
                'points' => ['45 Kitab Klasik', 'Metodologi Studi', 'Pemahaman Komprehensif'],
                'progress' => 5,
            ],
        ];

        $perbandingan = [
            ['program' => 'Kelas Tamhidi', 'durasi' => '1 Tahun', 'fokus' => 'Bahasa Arab & Dasar Agama', 'target' => 'Siap lanjut ke Ibtida'],
            ['program' => 'Ibtida 1 & 2', 'durasi' => '2 Tahun', 'fokus' => 'Fiqh & Nahwu Shorf', 'target' => 'Menguasai kitab dasar'],
            ['program' => 'Tsanawiyah 1 & 2', 'durasi' => '2 Tahun', 'fokus' => 'Fiqh Lanjut & Ushul', 'target' => 'Siap masuk Takhassus'],
            ['program' => 'Program Takhassus', 'durasi' => '2-3 Tahun', 'fokus' => 'Spesialisasi Fiqh', 'target' => 'Ahli bidang tertentu'],
            ['program' => 'Total Program', 'durasi' => '5 Tahun', 'fokus' => '45 Kitab Klasik', 'target' => 'Ulama yang kompeten', 'highlight' => true],
        ];

        $kontak = [
            'alamat' => 'Jl. Ibu Pertiwi, Ps. Kliwon, Kec. Ps. Kliwon, Kota Surakarta, Jawa Tengah, Indonesia',
            'whatsapp' => '+967 733262518',
            'email' => 'daurohdmsolo.com',
            'jam' => 'Fast Response - Hubungi Kami Kapan Saja',
        ];

        return view('landing', compact('stats', 'fitur', 'kurikulum', 'perbandingan', 'kontak'));
    }
}
