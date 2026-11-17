<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\JadwalKegiatan;
use App\Models\JenisKegiatan;
use App\Models\PendaftaranSiswa;
use App\Support\CatatKegiatan;
use App\Support\NamaArab;
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
 * Pengulangan: "Sekali" (satu tanggal) atau "Berulang" (pilih hari, seperti alarm HP) -> JadwalKegiatan;
 * pada hari itu absensinya dibuat otomatis & langsung aktif (JadwalKegiatan::buatHariIni).
 */
class AbsensiKegiatanController extends Controller
{
    /** GET /admin/absensi-kegiatan */
    public function index(Request $request)
    {
        $saring = $request->query('saring', 'berjalan'); // berjalan | selesai | semua
        $cari = trim((string) $request->query('cari'));   // cari nama kegiatan

        Absensi::tutupYangLewat(); // yang sudah lewat batas alfa ditutup otomatis (belum tercatat = Alfa)

        // Kegiatan BERULANG: satu kartu per jadwal (tab Berjalan / Semua, halaman pertama),
        // berisi absensi hari ini yang sedang berjalan + tautan ke hasil terakhir. Tab Selesai hanya absensi sekali.
        $jadwalList = $saring === 'selesai' || $request->integer('page', 1) > 1 ? collect() :
            JadwalKegiatan::with('kegiatan:id,nama,warna')
                ->when($cari !== '', fn ($q) => $q->whereHas('kegiatan', fn ($k) => $k->where('nama', 'like', "%{$cari}%")))
                ->orderBy('jam_mulai')->get();

        $absensiBerulang = fn (bool $selesai) => Absensi::where('jenis', Absensi::KEGIATAN)
            ->whereIn('jadwal_kegiatan_id', $jadwalList->pluck('id'))
            ->when($selesai, fn ($q) => $q->whereNotNull('selesai_at'), fn ($q) => $q->whereNull('selesai_at'))
            ->withCount(['detail as tercatat_count' => fn ($q) => $q->whereNotNull('dicatat_oleh')])
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->get()->unique('jadwal_kegiatan_id')->keyBy('jadwal_kegiatan_id');
        $berulangBerjalan = $absensiBerulang(false);
        $berulangTerakhir = $absensiBerulang(true);

        $daftar = Absensi::with('kegiatan')
            ->where('jenis', Absensi::KEGIATAN)
            ->where('dari_admin', true)
            ->whereNull('jadwal_kegiatan_id') // absensi dari kegiatan berulang tampil di kartu berulangnya
            ->when($saring === 'berjalan', fn ($q) => $q->whereNull('selesai_at'))
            ->when($saring === 'selesai', fn ($q) => $q->whereNotNull('selesai_at'))
            ->when($cari !== '', fn ($q) => $q->whereHas('kegiatan', fn ($k) => $k->where('nama', 'like', "%{$cari}%")))
            ->withCount([
                'detail',
                'detail as tercatat_count' => fn ($q) => $q->whereNotNull('dicatat_oleh'),
            ])
            ->orderByRaw('selesai_at IS NULL DESC')->orderBy('tanggal')->orderBy('jam_mulai')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        // Nama asatid yang ikut mencatat tiap kegiatan (+ nama Arab, tampil di bawah nama)
        $detailPencatat = AbsensiDetail::whereIn('absensi_id', $daftar->pluck('id')->merge($berulangBerjalan->pluck('id')))
            ->whereNotNull('dicatat_oleh')
            ->with('pencatat:id,name')
            ->get(['absensi_id', 'dicatat_oleh']);
        NamaArab::untukUser($detailPencatat->pluck('dicatat_oleh')); // ambil sekaligus, tanpa kueri per baris
        $pencatat = $detailPencatat
            ->groupBy('absensi_id')
            ->map(fn ($d) => $d->filter(fn ($x) => $x->pencatat?->name)->unique('dicatat_oleh')
                ->map(fn ($x) => ['nama' => $x->pencatat->name, 'arab' => NamaArab::user($x->dicatat_oleh)])->values());

        return view('admin.akademik.absensi-kegiatan', [
            'daftar'       => $daftar,
            'pencatat'     => $pencatat,
            'saring'       => $saring,
            'cari'         => $cari,
            'kegiatanList' => JenisKegiatan::where('is_active', true)->ordered()->get(['id', 'nama', 'warna']),
            'jadwalList'   => $jadwalList,
            'berulangBerjalan' => $berulangBerjalan,
            'berulangTerakhir' => $berulangTerakhir,
            'jumlahPeserta' => PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->count(),
            'jumlah'       => [
                'berjalan' => Absensi::where('jenis', Absensi::KEGIATAN)->where('dari_admin', true)->whereNull('jadwal_kegiatan_id')->whereNull('selesai_at')->count()
                    + JadwalKegiatan::count(),
                'selesai'  => Absensi::where('jenis', Absensi::KEGIATAN)->where('dari_admin', true)->whereNull('jadwal_kegiatan_id')->whereNotNull('selesai_at')->count(),
            ],
        ]);
    }

    /** POST /admin/absensi-kegiatan — buat absensi kegiatan baru (sekali), atau kegiatan berulang (pengulangan=berulang) */
    public function store(Request $request)
    {
        if ($request->input('pengulangan') === 'berulang') {
            JadwalKegiatan::create($this->validasi($request, true) + [
                'nyala'   => $request->boolean('aktif'),
                'user_id' => Auth::id(),
            ]);
            JadwalKegiatan::buatHariIni(); // hari ini termasuk & belum lewat batas alfa -> langsung muncul

            return back()->with('success', $request->boolean('aktif')
                ? 'Kegiatan berulang dibuat. Pada hari yang dipilih, absensinya otomatis muncul di dasbor semua asatid.'
                : 'Kegiatan berulang dibuat dalam keadaan OFF. Nyalakan saklarnya agar mulai berjalan.');
        }

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

    /* ---------------- Kegiatan berulang (seperti alarm) ---------------- */

    /** PUT /admin/absensi-kegiatan/jadwal/{jadwal} — ubah kegiatan berulang (berlaku mulai absensi berikutnya) */
    public function ubahJadwal(Request $request, JadwalKegiatan $jadwal)
    {
        $jadwal->update($this->validasi($request, true));
        JadwalKegiatan::buatHariIni();

        return back()->with('success', 'Kegiatan berulang diperbarui. Berlaku mulai absensi berikutnya.');
    }

    /** POST /admin/absensi-kegiatan/jadwal/{jadwal}/nyala — saklar ON / OFF */
    public function nyalaJadwal(JadwalKegiatan $jadwal)
    {
        $jadwal->update(['nyala' => ! $jadwal->nyala]);
        if ($jadwal->nyala) {
            JadwalKegiatan::buatHariIni();
        }

        return back()->with('success', $jadwal->nyala
            ? 'Kegiatan berulang dinyalakan (ON).'
            : 'Kegiatan berulang dimatikan (OFF). Absensinya tidak dibuat lagi sampai dinyalakan.');
    }

    /** DELETE /admin/absensi-kegiatan/jadwal/{jadwal} — absensi yang sudah dibuat tetap ada */
    public function hapusJadwal(JadwalKegiatan $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Kegiatan berulang dihapus. Absensi yang sudah dibuat sebelumnya tetap ada.');
    }

    /** $berulang = true: pilih hari (1=Senin..7=Ahad) sebagai ganti tanggal */
    private function validasi(Request $request, bool $berulang = false): array
    {
        $data = $request->validate([
            'jenis_kegiatan_id' => ['required', Rule::exists('jenis_kegiatan', 'id')->where('is_active', true)],
            'tanggal'           => $berulang ? ['nullable'] : ['required', 'date'],
            'hari'              => $berulang ? ['required', 'array', 'min:1'] : ['nullable'],
            'hari.*'            => ['integer', 'between:1,7'],
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'batas_terlambat'   => ['required', 'date_format:H:i', 'after_or_equal:jam_mulai'],
            'batas_alfa'        => ['required', 'date_format:H:i', 'after:batas_terlambat'],
            'metode'            => ['required', Rule::in(['qr', 'manual'])],
        ], [
            'jenis_kegiatan_id.required' => 'Pilih nama kegiatan terlebih dahulu.',
            'jenis_kegiatan_id.exists'   => 'Nama kegiatan tidak ditemukan / sudah nonaktif.',
            'tanggal.required'           => 'Tanggal wajib diisi.',
            'hari.required'              => 'Pilih minimal satu hari untuk kegiatan berulang.',
            'hari.min'                   => 'Pilih minimal satu hari untuk kegiatan berulang.',
            'jam_mulai.required'         => 'Jam mulai wajib diisi.',
            'batas_terlambat.required'   => 'Batas terlambat wajib diisi.',
            'batas_alfa.required'        => 'Batas alfa wajib diisi.',
            'batas_terlambat.after_or_equal' => 'Batas terlambat tidak boleh sebelum jam mulai.',
            'batas_alfa.after'           => 'Batas alfa harus setelah batas terlambat.',
            '*.date_format'              => 'Format jam harus JJ:MM, mis. 20:00.',
        ]);

        if ($berulang) {
            unset($data['tanggal']);
            $data['hari'] = collect($data['hari'])->map(fn ($h) => (int) $h)->unique()->sort()->values()->all();
        } else {
            unset($data['hari']);
        }

        return $data;
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
