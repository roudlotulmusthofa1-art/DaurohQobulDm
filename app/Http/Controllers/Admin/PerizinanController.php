<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinSantri;
use App\Models\JenisIzin;
use App\Models\PendaftaranSiswa;
use App\Support\DataCatatan;
use App\Support\NamaArab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin > Penilaian > Perizinan (khusus admin).
 * - Tombol "Jenis Izin" : admin menulis sendiri nama jenis izin (tabel jenis_izin).
 * - Tombol "Beri Izin"  : santri -> jenis izin -> mulai (tanggal + jam) -> berakhir (tanggal + jam) -> catatan.
 * - Tombol "Sudah kembali" pada izin yang berjalan: mencatat waktu kembali (otomatis sekarang, bisa diubah).
 * - Tombol "Pindai Kembali": kartu QR santri dipindai kamera / alat scanner -> izinnya langsung ditandai kembali.
 * Izin yang terlambat otomatis tampil sebagai pelanggaran di Penilaian > Catatan (App\Support\DataCatatan::barisIzin).
 * Semua waktu dalam WIB.
 */
class PerizinanController extends Controller
{
    /** GET /admin/penilaian/perizinan */
    public function index()
    {
        $sekarang = now('Asia/Jakarta');

        return view('admin.penilaian.perizinan', [
            'jenis'    => JenisIzin::untukVue(),
            'siswa'    => DataCatatan::daftarSiswa(),
            'izin'     => IzinSantri::with(['siswa.kelasMaster', 'pemberi:id,name'])
                ->orderByDesc('mulai')->orderByDesc('id')->get()
                ->tap(fn ($i) => NamaArab::untukUser($i->pluck('diberi_oleh'))) // nama Arab pemberi izin sekaligus
                ->map(fn (IzinSantri $i) => $i->baris())->values(),
            'hariIni'  => $sekarang->toDateString(),
            'jamIni'   => $sekarang->format('H:i'),
        ]);
    }

    /** POST /admin/penilaian/perizinan/jenis — tambah satu jenis izin (tombol "+") */
    public function simpanJenis(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_izin', 'nama')],
        ], [
            'nama.required' => __('Tulis namanya dulu.'),
            'nama.unique'   => __('Nama ini sudah ada.'),
        ]);

        $jenis = JenisIzin::create(['nama' => trim($data['nama']), 'urutan' => (int) JenisIzin::max('urutan') + 1]);

        return response()->json([
            'message' => __('Jenis izin ditambahkan.'),
            'jenis'   => ['id' => $jenis->id, 'nama' => $jenis->nama],
        ]);
    }

    /** DELETE /admin/penilaian/perizinan/jenis/{jenis} — izin lama yang memakai nama ini tetap ada */
    public function hapusJenis(JenisIzin $jenis): JsonResponse
    {
        $jenis->delete();

        return response()->json(['message' => __('Jenis izin dihapus.')]);
    }

    /** POST /admin/penilaian/perizinan — beri izin satu santri */
    public function simpan(Request $request): JsonResponse
    {
        $izin = IzinSantri::create($this->isianIzin($request) + ['diberi_oleh' => Auth::id()]);

        return response()->json([
            'message' => __('Izin :jenis untuk :nama disimpan.', ['jenis' => $izin->jenis, 'nama' => $izin->siswa?->nama_lengkap]),
            'baris'   => $this->baris($izin->id),
        ]);
    }

    /** PUT /admin/penilaian/perizinan/{izin} — ubah izin (santri, jenis, mulai, berakhir, catatan); waktu kembali tidak diubah di sini */
    public function ubah(Request $request, IzinSantri $izin): JsonResponse
    {
        $isian = $this->isianIzin($request, $izin);
        $kembali = $izin->getAttributes()['kembali'] ?? null;
        if ($kembali && substr((string) $kembali, 0, 19) < $isian['mulai']) {
            throw ValidationException::withMessages(['mulai_jam' => __('Waktu mulai tidak boleh setelah waktu kembali yang sudah dicatat.')]);
        }

        $izin->update($isian);

        return response()->json([
            'message' => __('Izin :jenis untuk :nama diperbarui.', ['jenis' => $izin->jenis, 'nama' => $izin->siswa?->nama_lengkap]),
            'baris'   => $this->baris($izin->id),
        ]);
    }

    /**
     * Cek isian Beri Izin / Ubah Izin -> kolom tabel izin_santri (waktu disimpan sebagai WIB apa adanya).
     * Saat mengubah, jenis izin yang lama tetap boleh dipakai walau sudah dihapus dari pengaturan.
     */
    private function isianIzin(Request $request, ?IzinSantri $lama = null): array
    {
        $jenisLama = $lama && $request->input('jenis') === $lama->jenis;

        $data = $request->validate([
            'siswa_id'        => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'jenis'           => array_filter(['required', 'string', 'max:100', $jenisLama ? null : Rule::exists('jenis_izin', 'nama')]),
            'mulai_tanggal'   => ['required', 'date'],
            'mulai_jam'       => ['required', 'date_format:H:i'],
            'selesai_tanggal' => ['required', 'date', 'after_or_equal:mulai_tanggal'],
            'selesai_jam'     => ['required', 'date_format:H:i'],
            'catatan'         => ['nullable', 'string', 'max:5000'],
        ], [
            'siswa_id.required'              => __('Pilih santri dulu.'),
            'siswa_id.exists'                => __('Santri tidak ditemukan / belum diterima.'),
            'jenis.required'                 => __('Pilih jenis izin dulu.'),
            'jenis.exists'                   => __('Jenis izin tidak ada di pengaturan.'),
            'mulai_jam.required'             => __('Isi jam mulai izin.'),
            'selesai_jam.required'           => __('Isi jam berakhir izin.'),
            'selesai_tanggal.after_or_equal' => __('Tanggal berakhir tidak boleh sebelum tanggal mulai.'),
        ]);

        $mulai = $data['mulai_tanggal'] . ' ' . $data['mulai_jam'] . ':00';
        $selesai = $data['selesai_tanggal'] . ' ' . $data['selesai_jam'] . ':00';
        if ($selesai <= $mulai) {
            throw ValidationException::withMessages(['selesai_jam' => __('Waktu berakhir harus setelah waktu mulai.')]);
        }

        return [
            'pendaftaran_siswa_id' => $data['siswa_id'],
            'jenis'                => $data['jenis'],
            'mulai'                => $mulai,
            'selesai'              => $selesai,
            'catatan'              => trim((string) ($data['catatan'] ?? '')) ?: null,
        ];
    }

    /** PUT /admin/penilaian/perizinan/{izin}/kembali — catat waktu kembali (kosong = batalkan "sudah kembali") */
    public function kembali(Request $request, IzinSantri $izin): JsonResponse
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date', 'required_with:jam'],
            'jam'     => ['nullable', 'date_format:H:i', 'required_with:tanggal'],
        ]);

        $kembali = ! empty($data['tanggal']) ? $data['tanggal'] . ' ' . $data['jam'] . ':00' : null;
        if ($kembali && $kembali < substr((string) $izin->getAttributes()['mulai'], 0, 19)) {
            throw ValidationException::withMessages(['jam' => __('Waktu kembali tidak boleh sebelum izin dimulai.')]);
        }

        $izin->update(['kembali' => $kembali]);
        $baris = $this->baris($izin->id);

        return response()->json([
            'message' => ! $kembali
                ? __('Tanda kembali dibatalkan.')
                : ($baris['status'] === 'terlambat'
                    ? __(':nama kembali terlambat :lama. Otomatis masuk catatan pelanggaran.', ['nama' => $baris['siswa'], 'lama' => $baris['telat']])
                    : __(':nama kembali tepat waktu.', ['nama' => $baris['siswa']])),
            'baris' => $baris,
        ]);
    }

    /**
     * POST /admin/penilaian/perizinan/pindai — tombol "Pindai Kembali": kartu QR santri (atau nomor pendaftaran) dipindai,
     * izin santri itu yang sedang berjalan & belum kembali ditandai kembali SEKARANG (WIB).
     * Kalau ada beberapa, yang batasnya paling awal. Hasil: tipe tepat | terlambat | tidak (tidak sedang izin).
     */
    public function pindai(Request $request): JsonResponse
    {
        $kode = trim((string) $request->validate(['kode' => ['required', 'string', 'max:100']])['kode']);

        $siswa = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->where(fn ($w) => $w->where('kode_qr', $kode)->orWhere('nomor_pendaftaran', strtoupper($kode)))
            ->first();
        if (! $siswa) {
            return response()->json(['message' => __('Kode tidak dikenali. Pastikan kartu milik santri yang sudah diterima.')], 404);
        }

        $sekarang = IzinSantri::sekarang();
        $izin = IzinSantri::where('pendaftaran_siswa_id', $siswa->id)
            ->whereNull('kembali')->where('mulai', '<=', $sekarang)
            ->orderBy('selesai')->orderBy('id')->first();

        $santri = ['nama' => $siswa->nama_lengkap, 'arab' => $siswa->nama_lengkap_arab, 'foto' => $siswa->foto_url, 'nomor' => $siswa->nomor_pendaftaran];
        if (! $izin) {
            return response()->json([
                'tipe'    => 'tidak',
                'santri'  => $santri,
                'message' => __(':nama tidak sedang izin.', ['nama' => $siswa->nama_lengkap]),
            ]);
        }

        $izin->update(['kembali' => $sekarang]);
        $baris = $this->baris($izin->id);

        return response()->json([
            'tipe'    => $baris['status'], // tepat | terlambat
            'santri'  => $santri,
            'message' => $baris['status'] === 'terlambat'
                ? __('Terlambat :lama', ['lama' => $baris['telat']])
                : __('Kembali tepat waktu'),
            'baris'   => $baris,
        ]);
    }

    /** DELETE /admin/penilaian/perizinan/{izin} */
    public function hapus(IzinSantri $izin): JsonResponse
    {
        $izin->delete();

        return response()->json(['message' => __('Izin dihapus.')]);
    }

    private function baris(int $id): array
    {
        return IzinSantri::with(['siswa.kelasMaster', 'pemberi:id,name'])->findOrFail($id)->baris();
    }
}
