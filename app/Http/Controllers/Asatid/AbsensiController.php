<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\JenisKegiatan;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\PendaftaranAsatid;
use App\Models\PendaftaranSiswa;
use App\Models\WaktuPelajaran;
use App\Support\CatatKegiatan;
use App\Support\DaftarKitab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Absensi peserta oleh asatid.
 *
 * 1. Absensi kelas    : pilih kelas, kitab, tanggal, jam ke -> kartu per peserta -> simpan sekaligus.
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
        Absensi::tutupYangLewat(); // absensi kegiatan yang lewat batas alfa ditutup otomatis

        $riwayat = Absensi::with(['kelas', 'pelajaran', 'kitab', 'waktuPelajaran', 'kegiatan'])
            ->withCount('detail')
            ->where('user_id', Auth::id())
            ->terbaru()
            ->limit(12)
            ->get();

        $berjalan = $riwayat->where('jenis', Absensi::KEGIATAN)->whereNull('selesai_at');

        // Absensi kegiatan dari admin yang sedang dibuka untuk semua asatid
        $kegiatanAdmin = $this->kegiatanAdmin();

        return $this->tampil('asatid.absensi.index', compact('riwayat', 'berjalan', 'kegiatanAdmin'));
    }

    /* =====================================================================
     |  Absensi kelas
     * ===================================================================== */

    public function kelas(Request $request)
    {
        return $this->tampil('asatid.absensi.kelas', [
            'kelasList' => Kelas::where('is_active', true)->ordered()->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])->get(['id', 'nama', 'kode']),
            // Pilih KITAB (bukan bidang ilmu) supaya nyambung dengan Penilaian > Nilai Harian
            'kitabList' => DaftarKitab::pilihan(),
            'terjadwal' => DaftarKitab::terjadwal(),             // kitab terjadwal di tiap kelas: ditaruh paling atas
            'jadwalGuru' => DaftarKitab::jadwalGuru(Auth::id()), // kelas + jam + hari cocok jadwal guru: kitab terpilih otomatis
            'jamList' => WaktuPelajaran::where('is_active', true)->ordered()->get(['id', 'nama', 'jam_mulai', 'jam_selesai']),
            // Dari tombol "Ubah" di halaman hasil / "Absen sekarang" di pengingat: form langsung terisi
            'awal' => $request->only(['kelas_id', 'kitab_id', 'waktu_pelajaran_id', 'tanggal']),
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
                'waktu_pelajaran_id' => $data['waktu_pelajaran_id'],
                'tanggal' => $data['tanggal'],
            ]);
            // Kitab disimpan; bidang ilmunya ikut diisi dari kitab itu (laporan lama tetap terbaca)
            $absensi->fill([
                'kitab_id' => $data['kitab_id'],
                'bidang_ilmu_id' => Kitab::whereKey($data['kitab_id'])->value('bidang_ilmu_id'),
                'user_id' => Auth::id(),
                'tanggal_hijriyah' => Absensi::hijriyah($data['tanggal']),
            ])->save();

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
        Absensi::tutupYangLewat();

        // Absensi kegiatan sekarang dibuat & diaktifkan admin (Admin > Akademik > Absensi Kegiatan)
        return $this->tampil('asatid.absensi.kegiatan', [
            'kegiatanAdmin' => $this->kegiatanAdmin(),
        ]);
    }

    /** Buat sesi absensi kegiatan, lalu masuk ke halaman pencatatan */
    public function mulaiKegiatan(Request $request)
    {
        // Absensi kegiatan sekarang dibuat admin; asatid tinggal mengabsen kegiatan yang aktif
        return redirect()->route('asatid.absensi.kegiatan')
            ->with('error', 'Absensi kegiatan sekarang dibuat oleh admin. Pilih kegiatan yang aktif di daftar.');

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
        $this->pastikanBolehCatat($absensi);

        if ($absensi->tutupKalauLewat()) {
            return redirect()->route('asatid.absensi.show', $absensi)
                ->with('sukses', 'Waktu absensi sudah lewat batas alfa. Absensi ditutup otomatis, peserta yang belum tercatat dihitung Alfa.');
        }
        if ($absensi->selesai_at) {
            return redirect()->route('asatid.absensi.show', $absensi);
        }

        $peserta = CatatKegiatan::peserta($absensi, 'asatid.peserta.show');

        $absensi->load('kegiatan');

        return $this->tampil('asatid.absensi.catat', compact('absensi', 'peserta'));
    }

    /**
     * JSON: catat satu peserta. Body: { kode } (hasil pindai QR) atau { siswa_id, status }, plus { jam }.
     * Hasil pindai QR memakai status otomatis menurut jam.
     */
    public function simpanCatatan(Request $request, Absensi $absensi): JsonResponse
    {
        // Milik sendiri atau kegiatan admin; ditutup / dinonaktifkan dicek di CatatKegiatan (pesannya lebih jelas)
        abort_unless($absensi->jenis === Absensi::KEGIATAN, 404);
        abort_unless((int) $absensi->user_id === (int) Auth::id() || $absensi->dari_admin, 404);

        return CatatKegiatan::simpan($request, $absensi, 'asatid.peserta.show'); // aturan sama dengan admin
    }

    /**
     * JSON: status semua peserta yang sudah tercatat (dipanggil tiap 10 detik oleh halaman catat),
     * supaya catatan asatid lain di kegiatan yang sama ikut terlihat.
     */
    public function statusKegiatan(Absensi $absensi): JsonResponse
    {
        abort_unless($absensi->jenis === Absensi::KEGIATAN, 404);
        abort_unless((int) $absensi->user_id === (int) Auth::id() || $absensi->dari_admin, 404);

        return CatatKegiatan::status($absensi);
    }

    /** Tutup absensi kegiatan: peserta yang belum tercatat ditandai alfa */
    public function selesaiKegiatan(Absensi $absensi)
    {
        $this->pastikanMilik($absensi, Absensi::KEGIATAN);

        $absensi->tutup();

        return redirect()->route('asatid.absensi.show', $absensi)->with('sukses', 'Absensi kegiatan ditutup dan tersimpan.');
    }

    /* =====================================================================
     |  Hasil satu absensi
     * ===================================================================== */

    public function show(Absensi $absensi)
    {
        if (! ($absensi->jenis === Absensi::KEGIATAN && $absensi->dari_admin)) {
            $this->pastikanMilik($absensi);
        }

        $absensi->load(['kelas', 'pelajaran', 'kitab', 'waktuPelajaran', 'kegiatan', 'detail.siswa.kelasMaster', 'detail.pencatat:id,name']);
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
            'kitab_id' => ['required', Rule::exists('kitab', 'id')],
            'waktu_pelajaran_id' => ['required', Rule::exists('waktu_pelajaran', 'id')],
            'tanggal' => ['required', 'date'],
        ];
    }

    /**
     * Absensi kelas yang sama (kelas + kitab + jam + tanggal) tidak dibuat dua kali.
     * Absensi lama (belum punya kitab) dianggap sama kalau bidang ilmunya sama dengan bidang ilmu kitab itu.
     */
    private function cariAbsensiKelas(array $data): ?Absensi
    {
        $bidang = Kitab::whereKey($data['kitab_id'])->value('bidang_ilmu_id');

        return Absensi::where('jenis', Absensi::KELAS)
            ->where('kelas_id', $data['kelas_id'])
            ->where(fn ($q) => $q->where('kitab_id', $data['kitab_id'])
                ->orWhere(fn ($q) => $q->whereNull('kitab_id')->whereNotNull('bidang_ilmu_id')->where('bidang_ilmu_id', $bidang)))
            ->orderByRaw('kitab_id is null') // yang sudah punya kitab didahulukan
            ->where('waktu_pelajaran_id', $data['waktu_pelajaran_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->first();
    }

    /** Absensi kegiatan: milik sendiri, atau dibuat admin & sedang terbuka (dicatat bersama) */
    private function pastikanBolehCatat(Absensi $absensi): void
    {
        abort_unless($absensi->jenis === Absensi::KEGIATAN, 404);
        abort_unless($absensi->bolehDicatatOleh(Auth::id()) || ($absensi->dari_admin && $absensi->selesai_at), 404);
    }

    /** Daftar absensi kegiatan dari admin yang sedang terbuka (urut tanggal & jam) */
    private function kegiatanAdmin()
    {
        return Absensi::kegiatanTerbuka()->with('kegiatan')
            ->withCount('detail')
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->get();
    }

    private function pastikanMilik(Absensi $absensi, ?string $jenis = null): void
    {
        abort_unless((int) $absensi->user_id === (int) Auth::id(), 404);
        abort_if($jenis && $absensi->jenis !== $jenis, 404);
    }

    private function dataSiswa(PendaftaranSiswa $s): array
    {
        return CatatKegiatan::siswa($s, 'asatid.peserta.show');
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
