<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\BidangIlmu;
use App\Models\JenisKegiatan;
use App\Models\Kelas;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\WaktuPelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Absensi peserta oleh asatid.
 *
 * 1. Absensi kelas    : pilih kelas, pelajaran, tanggal, jam ke -> kartu per peserta -> simpan sekaligus.
 * 2. Absensi kegiatan : pilih kegiatan + jam mulai / batas terlambat / batas alfa -> pindai QR atau cari nama,
 *                       tiap peserta langsung tersimpan -> tutup absensi (yang belum tercatat jadi alfa).
 *
 * Jam untuk menentukan hadir/terlambat/alfa diambil dari HP guru (dikirim sebagai "jam"),
 * supaya sesuai waktu setempat walaupun server memakai zona waktu lain.
 */
class AbsensiController extends Controller
{
    /** Halaman pilihan: Absensi Kelas / Absensi Kegiatan + riwayat */
    public function index()
    {
        $riwayat = Absensi::with(['kelas', 'pelajaran', 'waktuPelajaran', 'kegiatan'])
            ->withCount('detail')
            ->where('user_id', Auth::id())
            ->terbaru()
            ->limit(12)
            ->get();

        $berjalan = $riwayat->where('jenis', Absensi::KEGIATAN)->whereNull('selesai_at');

        return $this->tampil('asatid.absensi.index', compact('riwayat', 'berjalan'));
    }

    /* =====================================================================
     |  Absensi kelas
     * ===================================================================== */

    public function kelas(Request $request)
    {
        return $this->tampil('asatid.absensi.kelas', [
            'kelasList' => Kelas::where('is_active', true)->ordered()->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get(['id', 'nama', 'kode']),
            'pelajaranList' => BidangIlmu::where('is_active', true)->ordered()->get(['id', 'nama', 'warna']),
            'jamList' => WaktuPelajaran::where('is_active', true)->ordered()->get(['id', 'nama', 'jam_mulai', 'jam_selesai']),
            // Dari tombol "Ubah" di halaman hasil: form langsung terisi
            'awal' => $request->only(['kelas_id', 'bidang_ilmu_id', 'waktu_pelajaran_id', 'tanggal']),
        ]);
    }

    /** JSON: peserta di kelas itu + status yang sudah tersimpan (kalau absensi yang sama pernah diisi) */
    public function pesertaKelas(Request $request): JsonResponse
    {
        $data = $request->validate($this->aturanKelas());

        $absensi = $this->cariAbsensiKelas($data);
        $tersimpan = $absensi ? $absensi->detail()->get()->keyBy('pendaftaran_siswa_id') : collect();

        $peserta = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->where('kelas_id', $data['kelas_id'])
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($s) => $this->dataSiswa($s) + [
                'status' => $tersimpan[$s->id]->status ?? null,
                'catatan' => $tersimpan[$s->id]->catatan ?? '',
            ]);

        return response()->json([
            'peserta' => $peserta,
            'sudah_ada' => (bool) $absensi,
            'hijriyah' => Absensi::hijriyah($data['tanggal']),
        ]);
    }

    /** Simpan absensi kelas sekaligus (membuat baru atau memperbarui yang sudah ada) */
    public function simpanKelas(Request $request): JsonResponse
    {
        $data = $request->validate($this->aturanKelas() + [
            'data' => ['required', 'array', 'min:1'],
            'data.*.id' => ['required', 'integer'],
            'data.*.status' => ['required', Rule::in(array_keys(Absensi::STATUS))],
            'data.*.catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'data.*.status.required' => 'Masih ada peserta yang belum diberi keterangan.',
        ]);

        // Hanya peserta yang memang ada di kelas itu
        $idSah = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->where('kelas_id', $data['kelas_id'])->pluck('id')->flip();

        $absensi = DB::transaction(function () use ($data, $idSah) {
            $absensi = $this->cariAbsensiKelas($data) ?? new Absensi([
                'jenis' => Absensi::KELAS,
                'kelas_id' => $data['kelas_id'],
                'bidang_ilmu_id' => $data['bidang_ilmu_id'],
                'waktu_pelajaran_id' => $data['waktu_pelajaran_id'],
                'tanggal' => $data['tanggal'],
            ]);
            $absensi->fill(['user_id' => Auth::id(), 'tanggal_hijriyah' => Absensi::hijriyah($data['tanggal'])])->save();

            foreach ($data['data'] as $baris) {
                if (! isset($idSah[$baris['id']])) {
                    continue;
                }
                AbsensiDetail::updateOrCreate(
                    ['absensi_id' => $absensi->id, 'pendaftaran_siswa_id' => $baris['id']],
                    ['status' => $baris['status'], 'catatan' => trim((string) ($baris['catatan'] ?? '')) ?: null],
                );
            }

            return $absensi;
        });

        session()->flash('sukses', 'Absensi kelas berhasil disimpan.');

        return response()->json(['redirect' => route('asatid.absensi.show', $absensi)]);
    }

    /* =====================================================================
     |  Absensi kegiatan
     * ===================================================================== */

    public function kegiatan()
    {
        return $this->tampil('asatid.absensi.kegiatan', [
            'kegiatanList' => JenisKegiatan::where('is_active', true)->ordered()->get(['id', 'nama', 'warna', 'deskripsi']),
        ]);
    }

    /** Buat sesi absensi kegiatan, lalu masuk ke halaman pencatatan */
    public function mulaiKegiatan(Request $request)
    {
        $data = $request->validate([
            'jenis_kegiatan_id' => ['required', Rule::exists('jenis_kegiatan', 'id')->where('is_active', true)],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'batas_terlambat' => ['required', 'date_format:H:i', 'after_or_equal:jam_mulai'],
            'batas_alfa' => ['required', 'date_format:H:i', 'after:batas_terlambat'],
            'metode' => ['required', Rule::in(['qr', 'manual'])],
        ], [
            'jenis_kegiatan_id.required' => 'Pilih nama kegiatan terlebih dahulu.',
            'batas_terlambat.after_or_equal' => 'Batas terlambat tidak boleh sebelum jam mulai.',
            'batas_alfa.after' => 'Batas alfa harus setelah batas terlambat.',
        ]);

        $absensi = Absensi::create($data + [
            'jenis' => Absensi::KEGIATAN,
            'user_id' => Auth::id(),
            'tanggal_hijriyah' => Absensi::hijriyah($data['tanggal']),
        ]);

        return redirect()->route('asatid.absensi.kegiatan.catat', $absensi);
    }

    /** Halaman pencatatan kegiatan: pindai QR / cari nama */
    public function catatKegiatan(Absensi $absensi)
    {
        $this->pastikanMilik($absensi, Absensi::KEGIATAN);

        if ($absensi->selesai_at) {
            return redirect()->route('asatid.absensi.show', $absensi);
        }

        $tercatat = $absensi->detail()->get()->keyBy('pendaftaran_siswa_id');

        $peserta = PendaftaranSiswa::with('kelasMaster')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($s) => $this->dataSiswa($s) + [
                'status' => $tercatat[$s->id]->status ?? null,
                'waktu' => isset($tercatat[$s->id]) ? substr((string) $tercatat[$s->id]->waktu, 0, 5) : null,
            ]);

        $absensi->load('kegiatan');

        return $this->tampil('asatid.absensi.catat', compact('absensi', 'peserta'));
    }

    /**
     * JSON: catat satu peserta. Body: { kode } (hasil pindai QR) atau { siswa_id, status }, plus { jam }.
     * Hasil pindai QR memakai status otomatis menurut jam.
     */
    public function simpanCatatan(Request $request, Absensi $absensi): JsonResponse
    {
        $this->pastikanMilik($absensi, Absensi::KEGIATAN);
        abort_if($absensi->selesai_at, 422, 'Absensi kegiatan ini sudah ditutup.');

        $data = $request->validate([
            'kode' => ['nullable', 'string', 'max:100', 'required_without:siswa_id'],
            'siswa_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(array_keys(Absensi::STATUS))],
            'jam' => ['nullable', 'date_format:H:i'],
        ]);

        $siswa = PendaftaranSiswa::with('kelasMaster')->where('status', PendaftaranSiswa::DITERIMA)
            ->when($data['siswa_id'] ?? null,
                fn ($q, $id) => $q->whereKey($id),
                fn ($q) => $q->where(fn ($w) => $w
                    ->where('kode_qr', trim($data['kode']))
                    ->orWhere('nomor_pendaftaran', strtoupper(trim($data['kode'])))))
            ->first();

        if (! $siswa) {
            return response()->json(['message' => 'Kode tidak dikenali. Pastikan kartu milik peserta yang sudah diterima.'], 404);
        }

        $jam = $data['jam'] ?? now('Asia/Jakarta')->format('H:i');
        $status = $data['status'] ?? $absensi->statusMenurutJam($jam);

        $lama = AbsensiDetail::where('absensi_id', $absensi->id)->where('pendaftaran_siswa_id', $siswa->id)->first();

        // Pindai ulang kartu yang sudah tercatat tidak mengubah statusnya
        if ($lama && empty($data['status'])) {
            return response()->json([
                'siswa' => $this->dataSiswa($siswa) + ['status' => $lama->status, 'waktu' => substr((string) $lama->waktu, 0, 5)],
                'ulang' => true,
                'message' => $siswa->nama_lengkap . ' sudah tercatat sebelumnya.',
            ]);
        }

        $detail = AbsensiDetail::updateOrCreate(
            ['absensi_id' => $absensi->id, 'pendaftaran_siswa_id' => $siswa->id],
            ['status' => $status, 'waktu' => $lama?->waktu ?? $jam],
        );

        return response()->json([
            'siswa' => $this->dataSiswa($siswa) + ['status' => $detail->status, 'waktu' => substr((string) $detail->waktu, 0, 5)],
            'ulang' => false,
            'message' => $siswa->nama_lengkap . ' dicatat ' . Absensi::STATUS[$status][0] . '.',
        ]);
    }

    /** Tutup absensi kegiatan: peserta yang belum tercatat ditandai alfa */
    public function selesaiKegiatan(Absensi $absensi)
    {
        $this->pastikanMilik($absensi, Absensi::KEGIATAN);

        DB::transaction(function () use ($absensi) {
            $sudah = $absensi->detail()->pluck('pendaftaran_siswa_id');
            $sekarang = now();

            $baru = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
                ->whereNotIn('id', $sudah)->pluck('id')
                ->map(fn ($id) => [
                    'absensi_id' => $absensi->id, 'pendaftaran_siswa_id' => $id, 'status' => 'alfa',
                    'created_at' => $sekarang, 'updated_at' => $sekarang,
                ]);

            foreach ($baru->chunk(500) as $potong) {
                AbsensiDetail::insert($potong->all());
            }

            $absensi->update(['selesai_at' => $sekarang]);
        });

        return redirect()->route('asatid.absensi.show', $absensi)->with('sukses', 'Absensi kegiatan ditutup dan tersimpan.');
    }

    /* =====================================================================
     |  Hasil satu absensi
     * ===================================================================== */

    public function show(Absensi $absensi)
    {
        $this->pastikanMilik($absensi);

        $absensi->load(['kelas', 'pelajaran', 'waktuPelajaran', 'kegiatan', 'detail.siswa.kelasMaster']);
        $detail = $absensi->detail->sortBy(fn ($d) => $d->siswa?->nama_lengkap)->values();
        $hitung = collect(Absensi::STATUS)->map(fn ($s, $k) => $detail->where('status', $k)->count());

        return $this->tampil('asatid.absensi.show', compact('absensi', 'detail', 'hitung'));
    }

    /** Hapus absensi milik sendiri (mis. salah pilih kelas) */
    public function destroy(Absensi $absensi)
    {
        $this->pastikanMilik($absensi);
        $absensi->delete();

        return redirect()->route('asatid.absensi')->with('sukses', 'Absensi dihapus.');
    }

    /* =====================================================================
     |  Bantuan
     * ===================================================================== */

    private function aturanKelas(): array
    {
        return [
            'kelas_id' => ['required', Rule::exists('kelas', 'id')],
            'bidang_ilmu_id' => ['required', Rule::exists('bidang_ilmu', 'id')],
            'waktu_pelajaran_id' => ['required', Rule::exists('waktu_pelajaran', 'id')],
            'tanggal' => ['required', 'date'],
        ];
    }

    /** Absensi kelas yang sama (kelas + pelajaran + jam + tanggal) tidak dibuat dua kali */
    private function cariAbsensiKelas(array $data): ?Absensi
    {
        return Absensi::where('jenis', Absensi::KELAS)
            ->where('kelas_id', $data['kelas_id'])
            ->where('bidang_ilmu_id', $data['bidang_ilmu_id'])
            ->where('waktu_pelajaran_id', $data['waktu_pelajaran_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->first();
    }

    private function pastikanMilik(Absensi $absensi, ?string $jenis = null): void
    {
        abort_unless((int) $absensi->user_id === (int) Auth::id(), 404);
        abort_if($jenis && $absensi->jenis !== $jenis, 404);
    }

    private function dataSiswa(PendaftaranSiswa $s): array
    {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'arab' => $s->nama_lengkap_arab,
            'nomor' => $s->nomor_pendaftaran,
            'foto' => $s->foto_url,
            'kelas' => $s->relationLoaded('kelasMaster') ? $s->kelasMaster?->nama : null,
        ];
    }

    /** Sama seperti halaman asatid lain: profil singkat + notifikasi */
    private function tampil(string $view, array $data = [])
    {
        $user = Auth::user();
        $asatid = PendaftaranAsatid::where('user_id', $user->id)->first();

        return view($view, $data + [
            'user' => $user,
            'asatid' => $asatid,
            'notif' => AsatidDashboardController::notifikasi($asatid),
        ]);
    }
}
