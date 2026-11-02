<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Akademik > Tahun & Bulan Ajaran
 * Tahun ajaran (mis. 1448/1449) dibagi menjadi beberapa bulan ajaran (pengganti semester).
 * Hanya satu tahun ajaran dan satu bulan ajaran yang aktif; jadwal mengajar mengikuti bulan aktif.
 */
class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahun = TahunAjaran::with(['bulan' => fn ($q) => $q->withCount('jadwal')])
            ->orderByDesc('is_active')->orderByDesc('tanggal_mulai')->orderByDesc('id')->get();

        return view('admin.akademik.tahun-ajaran', [
            'tahunList' => $tahun,
            'bulanAktif' => BulanAjaran::aktif(),
            'namaHijriyah' => BulanAjaran::NAMA_HIJRIYAH,
        ]);
    }

    /* ---------------- Tahun ajaran ---------------- */

    public function store(Request $request)
    {
        $data = $this->validasiTahun($request);
        $tahun = TahunAjaran::create($data);

        if (! TahunAjaran::aktif()) {
            $tahun->aktifkan();
        }
        if ($request->boolean('isi_bulan') && $tahun->tanggal_mulai && $tahun->tanggal_selesai) {
            $this->isiBulanHijriyah($tahun);
        }

        return back()->with('success', "Tahun ajaran {$tahun->nama} ditambahkan.");
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->update($this->validasiTahun($request));

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama} diperbarui.");
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        $jadwal = \App\Models\JadwalMengajar::whereIn('bulan_ajaran_id', $tahunAjaran->bulan()->pluck('id'))->count();
        $nama = $tahunAjaran->nama;
        $tahunAjaran->delete(); // bulan & jadwalnya ikut terhapus

        return back()->with('success', "Tahun ajaran {$nama} dihapus" . ($jadwal ? " beserta {$jadwal} jadwal." : '.'));
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->aktifkan();

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama} sekarang aktif.");
    }

    /** Buat bulan ajaran otomatis mengikuti bulan Hijriyah di antara tanggal mulai & selesai. */
    public function isiBulan(TahunAjaran $tahunAjaran)
    {
        if (! $tahunAjaran->tanggal_mulai || ! $tahunAjaran->tanggal_selesai) {
            return back()->with('error', 'Isi tanggal mulai dan selesai tahun ajaran dulu.');
        }
        if ($tahunAjaran->bulan()->exists()) {
            return back()->with('error', 'Tahun ajaran ini sudah punya bulan. Hapus dulu bulan yang ada bila ingin dibuat ulang.');
        }

        $jumlah = $this->isiBulanHijriyah($tahunAjaran);

        return back()->with('success', "{$jumlah} bulan ajaran dibuat mengikuti kalender Hijriyah.");
    }

    /* ---------------- Bulan ajaran ---------------- */

    public function storeBulan(Request $request, TahunAjaran $tahunAjaran)
    {
        $data = $this->validasiBulan($request);
        $data['urutan'] ??= (int) $tahunAjaran->bulan()->max('urutan') + 1;
        $bulan = $tahunAjaran->bulan()->create($data);

        if (! BulanAjaran::aktif() && $tahunAjaran->is_active) {
            $bulan->aktifkan();
        }

        return back()->with('success', "Bulan {$bulan->nama} ditambahkan.")->with('buka', $tahunAjaran->id);
    }

    public function updateBulan(Request $request, BulanAjaran $bulanAjaran)
    {
        $bulanAjaran->update($this->validasiBulan($request));

        return back()->with('success', "Bulan {$bulanAjaran->nama} diperbarui.")->with('buka', $bulanAjaran->tahun_ajaran_id);
    }

    public function destroyBulan(BulanAjaran $bulanAjaran)
    {
        $jadwal = $bulanAjaran->jadwal()->count();
        $bulanAjaran->delete();

        return back()->with('success', "Bulan {$bulanAjaran->nama} dihapus" . ($jadwal ? " beserta {$jadwal} jadwal." : '.'))
            ->with('buka', $bulanAjaran->tahun_ajaran_id);
    }

    public function aktifkanBulan(BulanAjaran $bulanAjaran)
    {
        $bulanAjaran->aktifkan();

        return back()->with('success', "Bulan {$bulanAjaran->nama} sekarang aktif. Jadwal mengajar memakai bulan ini.")
            ->with('buka', $bulanAjaran->tahun_ajaran_id);
    }

    /* ---------------- Bantuan ---------------- */

    private function validasiTahun(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'nama_masehi' => ['nullable', 'string', 'max:50'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama tahun ajaran wajib diisi (mis. 1448/1449).',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);
    }

    private function validasiBulan(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'urutan' => ['nullable', 'integer', 'min:1', 'max:24'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'nama.required' => 'Nama bulan wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);
    }

    /**
     * Pecah rentang tanggal tahun ajaran menjadi bulan-bulan Hijriyah (kalender Umm al-Qura).
     * Mis. 11 Sep 2026 – 29 Jan 2027 -> Rabi'ul Awal 1448, Rabi'ul Akhir 1448, ...
     */
    private function isiBulanHijriyah(TahunAjaran $tahun): int
    {
        $zona = new \DateTimeZone('Asia/Jakarta');
        $kal = \IntlCalendar::createInstance($zona, 'id_ID@calendar=islamic-umalqura');
        $mulai = Carbon::parse($tahun->tanggal_mulai->toDateString(), 'Asia/Jakarta')->startOfDay();
        $akhir = Carbon::parse($tahun->tanggal_selesai->toDateString(), 'Asia/Jakarta')->startOfDay();

        $kal->setTime($mulai->getTimestampMs());
        $kal->set(\IntlCalendar::FIELD_DAY_OF_MONTH, 1);
        $kal->set(\IntlCalendar::FIELD_HOUR_OF_DAY, 12);

        $baris = [];
        $urutan = 1;
        while ($urutan <= 24) {
            $awalBulan = Carbon::createFromTimestampMs($kal->getTime(), 'Asia/Jakarta')->startOfDay();
            $nama = BulanAjaran::NAMA_HIJRIYAH[$kal->get(\IntlCalendar::FIELD_MONTH)] . ' ' . $kal->get(\IntlCalendar::FIELD_YEAR);
            $kal->add(\IntlCalendar::FIELD_MONTH, 1);
            $akhirBulan = Carbon::createFromTimestampMs($kal->getTime(), 'Asia/Jakarta')->startOfDay()->subDay();

            if ($awalBulan->gt($akhir)) {
                break;
            }

            $baris[] = [
                'nama' => $nama,
                'urutan' => $urutan++,
                'tanggal_mulai' => $awalBulan->max($mulai)->toDateString(),
                'tanggal_selesai' => $akhirBulan->min($akhir)->toDateString(),
            ];
        }

        DB::transaction(function () use ($tahun, $baris) {
            foreach ($baris as $b) {
                $tahun->bulan()->create($b);
            }
        });

        if ($tahun->is_active && ! BulanAjaran::aktif()) {
            $tahun->bulan()->first()?->aktifkan();
        }

        return count($baris);
    }
}
