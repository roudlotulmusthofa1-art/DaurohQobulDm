<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\Kelas;
use App\Models\NilaiHarian;
use App\Models\PendaftaranSiswa;
use App\Models\Ujian;
use App\Support\DaftarKitab;
use Illuminate\Http\Request;

/**
 * Admin > Penilaian > Kelas & Pelajaran: admin ikut MENGISI nilai, sama persis seperti asatid.
 * Halamannya memakai tampilan asatid (asatid/penilaian/harian & ujian-nilai) dengan tata letak admin.
 * Ambil-data & simpan memakai fungsi asatid yang sama (lihat routes/web.php), jadi aturannya tidak berbeda;
 * penilainya tercatat atas nama admin yang mengisi.
 */
class IsiNilaiController extends Controller
{
    /** Pengaturan tampilan bersama untuk admin */
    private function tampilanAdmin(string $tab, array $rute): array
    {
        return [
            'tataLetak'    => 'layouts.admin',
            'bungkus'      => 'mx-4 md:mx-10 lg:mx-20 mt-8',
            // Tombol simpan menempel di dalam isi halaman (di HP di atas menu bawah admin)
            'batangBawah'  => 'sticky bottom-20 lg:bottom-0 z-30 rounded-2xl bg-white/95 backdrop-blur ring-1 ring-slate-200 shadow-lg px-4 py-3',
            'kembaliAdmin' => route('admin.penilaian.kelas.index', ['tab' => $tab]),
            'rute'         => $rute,
        ];
    }

    /** GET /admin/penilaian/isi/harian */
    public function harian(Request $request)
    {
        return view('asatid.penilaian.harian', [
            'kelasList' => Kelas::where('is_active', true)->ordered()
                ->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get(['id', 'nama', 'kode']),
            'kitabList' => DaftarKitab::pilihan(),
            'terjadwal' => DaftarKitab::terjadwal(),
            'aspek'     => NilaiHarian::daftarAspek(),
            'bulan'     => BulanAjaran::aktif(),
            'awal'      => $request->only(['kelas_id', 'siswa_id', 'kitab_id', 'bidang_ilmu_id', 'tanggal']),
        ] + $this->tampilanAdmin('harian', [
            'peserta' => route('admin.penilaian.isi.harian.peserta'),
            'simpan'  => route('admin.penilaian.isi.harian.simpan'),
        ]));
    }

    /** GET /admin/penilaian/isi/ujian */
    public function ujian()
    {
        return view('asatid.penilaian.ujian-nilai', [
            'ujian'     => Ujian::daftar(true),
            'kelasList' => Kelas::where('is_active', true)->ordered()
                ->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get(['id', 'nama', 'kode']),
            'hariIni'   => now('Asia/Jakarta')->toDateString(),
        ] + $this->tampilanAdmin('ujian', [
            'peserta' => route('admin.penilaian.isi.ujian.peserta'),
            'simpan'  => route('admin.penilaian.isi.ujian.simpan'),
            'soal'    => route('admin.penilaian.kelas.index', ['tab' => 'ujian', 'panel' => 'soal']),
        ]));
    }
}
