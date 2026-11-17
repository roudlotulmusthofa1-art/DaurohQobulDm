<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AksiHafalan;
use App\Http\Controllers\Controller;
use App\Models\Hafalan;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\Pengaturan;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin > Akademik > Hafalan (admin/hafalan?tab=rekap|setoran).
 * - Pengaturan: kitab yang wajib dihafal (dari Kurikulum & Kitab) + pilihan nilai yang ditulis admin.
 * - Rekap Santri: kemajuan tiap santri per kitab (+ tanda Hafal).
 * - Semua Setoran: semua laporan hafalan dari para asatid (dan admin).
 * - Nilai Hafalan (admin/hafalan/nilai): halaman isi yang SAMA dengan asatid (asatid/hafalan.blade.php, layout admin);
 *   aksinya dari trait AksiHafalan, admin boleh mengubah / menghapus semua baris.
 */
class HafalanController extends Controller
{
    use AksiHafalan;

    protected function bolehUbahSemua(): bool
    {
        return true;
    }

    /** GET /admin/hafalan */
    public function index(Request $request)
    {
        $santri = PendaftaranSiswa::with('kelasMaster:id,nama')->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')->get();
        $ringkas = $this->ringkasan($santri->pluck('id')->all());

        $setoran = Hafalan::with(['siswa:id,nama_lengkap,nama_lengkap_arab,nomor_pendaftaran,kelas_id', 'siswa.kelasMaster:id,nama', 'kitab:id,nama', 'penilai:id,name'])
            ->orderByDesc('tanggal')->orderByDesc('id')->get()
            ->filter(fn (Hafalan $h) => $h->siswa && $h->kitab)
            ->map(fn (Hafalan $h) => [
                'id'       => $h->id,
                'siswa_id' => $h->pendaftaran_siswa_id,
                'siswa'    => $h->siswa->nama_lengkap,
                'arab'     => $h->siswa->nama_lengkap_arab,
                'nomor'    => $h->siswa->nomor_pendaftaran,
                'kelas_id' => $h->siswa->kelas_id,
                'kelas'    => $h->siswa->kelasMaster?->nama,
                'kitab_id' => $h->kitab_id,
                'kitab'    => $h->kitab->nama,
                'tanggal'  => $h->tanggal->toDateString(),
                'dari'     => $h->halaman_dari,
                'sampai'   => $h->halaman_sampai,
                'ragu'     => $h->ragu,
                'salah'    => $h->salah,
                'nilai'    => $h->nilai,
                'oleh'     => $h->penilai?->name,
            ])->values();

        return view('admin.akademik.hafalan.index', [
            'tab'        => $request->query('tab') === 'setoran' ? 'setoran' : 'rekap',
            'kitabList'  => Hafalan::daftarKitab(),
            'kitabPilih' => Hafalan::idKitab(),
            'nilaiList'  => Hafalan::pilihanNilai(),
            'nilaiBawaan' => Hafalan::NILAI_BAWAAN,
            'semuaKitab' => Kitab::with('bidangIlmu:id,nama')->orderBy('nama')->get(['id', 'nama', 'nama_arab', 'bidang_ilmu_id', 'is_active'])
                ->map(fn (Kitab $k) => ['id' => $k->id, 'nama' => $k->nama, 'arab' => $k->nama_arab, 'bidang' => $k->bidangIlmu?->nama, 'aktif' => (bool) $k->is_active])->values(),
            'kelasList'  => Kelas::where('is_active', true)->ordered()->get(['id', 'nama']),
            'santri'     => $santri->map(fn (PendaftaranSiswa $s) => [
                'id'       => $s->id,
                'nama'     => $s->nama_lengkap,
                'arab'     => $s->nama_lengkap_arab,
                'nomor'    => $s->nomor_pendaftaran,
                'foto'     => $s->foto_url,
                'kelas_id' => $s->kelas_id,
                'kelas'    => $s->kelasMaster?->nama,
                'kitab'    => (object) ($ringkas[$s->id] ?? []),
            ])->values(),
            'setoran'    => $setoran,
        ]);
    }

    /** PUT /admin/hafalan/pengaturan : kitab yang wajib dihafal + pilihan nilai */
    public function pengaturan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kitab'   => ['present', 'array'],
            'kitab.*' => ['integer', 'exists:kitab,id'],
            'nilai'   => ['required', 'array', 'min:1'],
            'nilai.*' => ['required', 'string', 'max:100', 'distinct'],
        ], [
            'nilai.required'   => __('Tulis minimal satu pilihan nilai.'),
            'nilai.min'        => __('Tulis minimal satu pilihan nilai.'),
            'nilai.*.required' => __('Pilihan nilai tidak boleh kosong.'),
            'nilai.*.distinct' => __('Ada pilihan nilai yang sama.'),
        ]);

        Pengaturan::simpan('hafalan_kitab', json_encode(array_values(array_unique(array_map('intval', $data['kitab'])))));
        Pengaturan::simpan('hafalan_nilai', json_encode(array_values(array_map('trim', $data['nilai'])), JSON_UNESCAPED_UNICODE));

        return response()->json([
            'pesan'      => __('Pengaturan hafalan disimpan.'),
            'kitabList'  => Hafalan::daftarKitab(),
            'kitabPilih' => Hafalan::idKitab(),
            'nilaiList'  => Hafalan::pilihanNilai(),
        ]);
    }

    /** GET /admin/hafalan/nilai : admin ikut menilai (halaman isi yang sama dengan asatid) */
    public function nilai(Request $request)
    {
        return view('asatid.hafalan', $this->dataHalaman($request) + [
            'tataLetak'    => 'layouts.admin',
            'bungkus'      => 'mx-4 md:mx-10 lg:mx-20 mt-8',
            'kembaliAdmin' => route('admin.hafalan.index'),
            'rute'         => [
                'peserta' => route('admin.hafalan.peserta'),
                'riwayat' => route('admin.hafalan.riwayat'),
                'simpan'  => route('admin.hafalan.simpan'),
                'hapus'   => route('admin.hafalan.hapus', '__ID__'),
                'selesai' => route('admin.hafalan.selesai'),
            ],
        ]);
    }
}
