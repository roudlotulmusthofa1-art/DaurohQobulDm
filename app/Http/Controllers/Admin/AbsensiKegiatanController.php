<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\JenisKegiatan;
use App\Models\PendaftaranSiswa;
use App\Support\CatatKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin > Akademik > Absensi Kegiatan.
 * Admin membuat absensi kegiatan (nama kegiatan + tanggal + jam), lalu mengaktifkannya.
 * Kegiatan yang AKTIF tampil di dasbor SEMUA asatid dan bisa dicatat bersama-sama
 * (lihat Asatid\AbsensiController). Admin menutupnya bila selesai: peserta yang belum tercatat = Alfa.
 */
class AbsensiKegiatanController extends Controller
{
    /** GET /admin/absensi-kegiatan */
    public function index(Request $request)
    {
        $saring = $request->query('saring', 'berjalan'); // berjalan | selesai | semua

        Absensi::tutupYangLewat(); // yang sudah lewat batas alfa ditutup otomatis (belum tercatat = Alfa)

        $daftar = Absensi::with('kegiatan')
            ->where('jenis', Absensi::KEGIATAN)
            ->where('dari_admin', true)
            ->when($saring === 'berjalan', fn ($q) => $q->whereNull('selesai_at'))
            ->when($saring === 'selesai', fn ($q) => $q->whereNotNull('selesai_at'))
            ->withCount([
                'detail',
                'detail as tercatat_count' => fn ($q) => $q->whereNotNull('dicatat_oleh'),
            ])
            ->orderByRaw('selesai_at IS NULL DESC')->orderBy('tanggal')->orderBy('jam_mulai')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        // Nama asatid yang ikut mencatat tiap kegiatan
        $pencatat = AbsensiDetail::whereIn('absensi_id', $daftar->pluck('id'))
            ->whereNotNull('dicatat_oleh')
            ->with('pencatat:id,name')
            ->get(['absensi_id', 'dicatat_oleh'])
            ->groupBy('absensi_id')
            ->map(fn ($d) => $d->pluck('pencatat.name')->filter()->unique()->values());

        return view('admin.akademik.absensi-kegiatan', [
            'daftar'       => $daftar,
            'pencatat'     => $pencatat,
            'saring'       => $saring,
            'kegiatanList' => JenisKegiatan::where('is_active', true)->ordered()->get(['id', 'nama', 'warna']),
            'jumlahPeserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->count(),
            'jumlah'       => [
                'berjalan' => Absensi::where('jenis', Absensi::KEGIATAN)->where('dari_admin', true)->whereNull('selesai_at')->count(),
                'selesai'  => Absensi::where('jenis', Absensi::KEGIATAN)->where('dari_admin', true)->whereNotNull('selesai_at')->count(),
            ],
        ]);
    }

    /** POST /admin/absensi-kegiatan — buat absensi kegiatan baru */
    public function store(Request $request)
    {
        $data = $this->validasi($request);

        Absensi::create($data + [
            'jenis'            => Absensi::KEGIATAN,
            'user_id'          => Auth::id(),
            'dari_admin'       => true,
            'aktif'            => $request->boolean('aktif'),
            'tanggal_hijriyah' => Absensi::hijriyah($data['tanggal']),
        ]);

        return back()->with('success', $request->boolean('aktif')
            ? 'Absensi kegiatan dibuat dan sudah tampil di dasbor semua asatid.'
            : 'Absensi kegiatan dibuat. Tekan "Aktifkan" agar tampil di dasbor asatid.');
    }

    /** PUT /admin/absensi-kegiatan/{absensi} — ubah nama/waktu (selama belum ditutup) */
    public function update(Request $request, Absensi $absensi)
    {
        $this->pastikanDariAdmin($absensi);
        abort_if($absensi->selesai_at, 422, 'Absensi kegiatan ini sudah ditutup.');

        $data = $this->validasi($request);
        $absensi->update($data + ['tanggal_hijriyah' => Absensi::hijriyah($data['tanggal'])]);

        return back()->with('success', 'Absensi kegiatan diperbarui.');
    }

    /** POST /admin/absensi-kegiatan/{absensi}/aktif — Aktifkan / Nonaktifkan untuk asatid */
    public function aktif(Absensi $absensi)
    {
        $this->pastikanDariAdmin($absensi);
        abort_if($absensi->selesai_at, 422, 'Absensi kegiatan ini sudah ditutup.');

        $absensi->update(['aktif' => ! $absensi->aktif]);

        return back()->with('success', $absensi->aktif
            ? 'Diaktifkan: semua asatid sekarang bisa mengabsen kegiatan ini.'
            : 'Dinonaktifkan: kegiatan ini disembunyikan dari dasbor asatid.');
    }

    /** POST /admin/absensi-kegiatan/{absensi}/tutup — selesai: peserta yang belum tercatat = Alfa */
    public function tutup(Absensi $absensi)
    {
        $this->pastikanDariAdmin($absensi);
        abort_if($absensi->selesai_at, 422, 'Absensi kegiatan ini sudah ditutup.');

        $absensi->tutup();

        return back()->with('success', 'Absensi kegiatan ditutup. Peserta yang belum tercatat dihitung Alfa.');
    }

    /** DELETE /admin/absensi-kegiatan/{absensi} */
    public function destroy(Absensi $absensi)
    {
        $this->pastikanDariAdmin($absensi);
        $absensi->delete();

        return back()->with('success', 'Absensi kegiatan dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'jenis_kegiatan_id' => ['required', Rule::exists('jenis_kegiatan', 'id')->where('is_active', true)],
            'tanggal'           => ['required', 'date'],
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'batas_terlambat'   => ['required', 'date_format:H:i', 'after_or_equal:jam_mulai'],
            'batas_alfa'        => ['required', 'date_format:H:i', 'after:batas_terlambat'],
            'metode'            => ['required', Rule::in(['qr', 'manual'])],
        ], [
            'jenis_kegiatan_id.required' => 'Pilih nama kegiatan terlebih dahulu.',
            'jenis_kegiatan_id.exists'   => 'Nama kegiatan tidak ditemukan / sudah nonaktif.',
            'tanggal.required'           => 'Tanggal wajib diisi.',
            'jam_mulai.required'         => 'Jam mulai wajib diisi.',
            'batas_terlambat.required'   => 'Batas terlambat wajib diisi.',
            'batas_alfa.required'        => 'Batas alfa wajib diisi.',
            'batas_terlambat.after_or_equal' => 'Batas terlambat tidak boleh sebelum jam mulai.',
            'batas_alfa.after'           => 'Batas alfa harus setelah batas terlambat.',
            '*.date_format'              => 'Format jam harus JJ:MM, mis. 20:00.',
        ]);
    }

    /** GET /admin/absensi-kegiatan/{absensi}/catat — admin ikut mengabsen (pindai QR / cari nama) */
    public function catat(Absensi $absensi)
    {
        $this->pastikanDariAdmin($absensi);

        if ($absensi->tutupKalauLewat()) {
            return redirect()->route('admin.absensi-kegiatan.index', ['saring' => 'selesai'])
                ->with('success', __('Waktu absensi sudah lewat batas alfa. Absensi ditutup otomatis, peserta yang belum tercatat dihitung Alfa.'));
        }
        if ($absensi->selesai_at) {
            return redirect()->route('admin.absensi-kegiatan.index', ['saring' => 'selesai'])
                ->with('success', __('Absensi kegiatan ini sudah ditutup. Hasilnya bisa dilihat lewat tombol "Lihat hasil".'));
        }
        if (! $absensi->aktif) {
            return redirect()->route('admin.absensi-kegiatan.index')
                ->with('error', __('Aktifkan dulu absensi kegiatan ini sebelum mengabsen.'));
        }

        $absensi->load('kegiatan');

        return view('admin.akademik.absensi-kegiatan-catat', [
            'absensi' => $absensi,
            'peserta' => CatatKegiatan::peserta($absensi, 'admin.pendaftaran.show'),
        ]);
    }

    /** POST /admin/absensi-kegiatan/{absensi}/catat — catat satu peserta (aturan sama dengan asatid) */
    public function simpanCatatan(Request $request, Absensi $absensi): JsonResponse
    {
        $this->pastikanDariAdmin($absensi);

        return CatatKegiatan::simpan($request, $absensi, 'admin.pendaftaran.show');
    }

    /** GET /admin/absensi-kegiatan/{absensi}/status — catatan terbaru (dari asatid & admin) tiap 10 detik */
    public function status(Absensi $absensi): JsonResponse
    {
        $this->pastikanDariAdmin($absensi);

        return CatatKegiatan::status($absensi);
    }

    private function pastikanDariAdmin(Absensi $absensi): void
    {
        abort_unless($absensi->jenis === Absensi::KEGIATAN && $absensi->dari_admin, 404);
    }
}
