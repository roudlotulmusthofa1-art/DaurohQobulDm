<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderPendidikan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Admin > Akademik > Kalender Pendidikan (admin/kalender-pendidikan).
 * Kalender satu bulan dengan patokan Masehi (?bulan=2026-11) atau Hijriyah (?hijri=1448-6).
 * Tiap kotak hari = tanggal Masehi + tanggal Hijriyah; kegiatan tampil sebagai pita berwarna.
 * Tambah / Ubah / Hapus kegiatan lewat JSON (Vue di admin/akademik/kalender-pendidikan.blade.php).
 */
class KalenderPendidikanController extends Controller
{
    /** GET /admin/kalender-pendidikan */
    public function index(Request $request): View
    {
        $hariIni = now('Asia/Jakarta')->startOfDay();
        [$hH, $bH, $yH] = KalenderPendidikan::bagianHijriyah($hariIni);

        // ----- Bulan yang ditampilkan -----
        $patokan = $request->filled('hijri') ? 'hijri' : 'masehi';
        if ($patokan === 'hijri') {
            [$y, $b] = array_map('intval', array_pad(explode('-', (string) $request->query('hijri')), 2, 0));
            if ($y < 1300 || $y > 1600 || $b < 1 || $b > 12) {
                [$y, $b] = [$yH, $bH];
            }
            [$awal, $akhir] = KalenderPendidikan::rentangBulanHijriyah($y, $b);
            $judul = KalenderPendidikan::namaBulanHijriyah($b) . ' ' . $y . ' H';
            $sub = $awal->locale(app()->getLocale())->translatedFormat('d M Y') . ' – ' . $akhir->locale(app()->getLocale())->translatedFormat('d M Y');
            $sebelum = ['hijri' => $b === 1 ? ($y - 1) . '-12' : $y . '-' . ($b - 1)];
            $sesudah = ['hijri' => $b === 12 ? ($y + 1) . '-1' : $y . '-' . ($b + 1)];
            $gantiPatokan = ['bulan' => $awal->copy()->addDays(14)->format('Y-m')];
        } else {
            try {
                $awal = Carbon::createFromFormat('Y-m-d', $request->query('bulan', $hariIni->format('Y-m')) . '-01', 'Asia/Jakarta')->startOfDay();
            } catch (\Throwable) {
                $awal = $hariIni->copy()->startOfMonth();
            }
            $akhir = $awal->copy()->endOfMonth()->startOfDay();
            $judul = $awal->locale(app()->getLocale())->translatedFormat('F Y');
            [, $b1, $y1] = KalenderPendidikan::bagianHijriyah($awal);
            [, $b2, $y2] = KalenderPendidikan::bagianHijriyah($akhir);
            $sub = KalenderPendidikan::namaBulanHijriyah($b1) . ($y1 !== $y2 ? ' ' . $y1 : '') . ' – ' . KalenderPendidikan::namaBulanHijriyah($b2) . ' ' . $y2 . ' H';
            $sebelum = ['bulan' => $awal->copy()->subMonth()->format('Y-m')];
            $sesudah = ['bulan' => $awal->copy()->addMonth()->format('Y-m')];
            [, $bTengah, $yTengah] = KalenderPendidikan::bagianHijriyah($awal->copy()->addDays(14));
            $gantiPatokan = ['hijri' => $yTengah . '-' . $bTengah];
        }

        // ----- Kotak hari: dari Ahad sebelum awal bulan sampai Sabtu sesudah akhir bulan -----
        $hari = [];
        $d = $awal->copy()->subDays($awal->dayOfWeek);
        $ujung = $akhir->copy()->addDays(6 - $akhir->dayOfWeek);
        $bulanHLalu = null;
        $bulanMLalu = null;
        while ($d->lte($ujung)) {
            [$h, $bh, $yh] = KalenderPendidikan::bagianHijriyah($d);
            $hari[] = [
                'tanggal' => $d->toDateString(),
                'masehi' => $d->day,
                'hijri' => $h,
                // nama bulan kecil di kotak pertama & saat bulan berganti
                'bulan_hijri' => ($h === 1 || $bulanHLalu === null) ? KalenderPendidikan::namaBulanHijriyah($bh) : null,
                'bulan_masehi' => ($d->day === 1 || $bulanMLalu === null) ? $d->locale(app()->getLocale())->translatedFormat('M') : null,
                'di_bulan' => $d->betweenIncluded($awal, $akhir),
                'hari_ini' => $d->equalTo($hariIni),
                'jumat' => $d->dayOfWeek === Carbon::FRIDAY,
            ];
            $bulanHLalu = $bh;
            $bulanMLalu = $d->month;
            $d->addDay();
        }

        $tabelAda = Schema::hasColumn('kalender_pendidikan', 'warna');

        return view('admin.akademik.kalender-pendidikan', [
            'patokan' => $patokan,
            'judul' => $judul,
            'sub' => $sub,
            'awal' => $awal->toDateString(),
            'akhir' => $akhir->toDateString(),
            'hari' => $hari,
            'kegiatan' => $tabelAda ? $this->semuaKegiatan() : [],
            'warna' => KalenderPendidikan::pilihanWarna(),
            'warnaAwal' => KalenderPendidikan::WARNA_AWAL,
            'tautan' => [
                'sebelum' => route('admin.kalender.index', $sebelum),
                'sesudah' => route('admin.kalender.index', $sesudah),
                'hariIni' => route('admin.kalender.index', $patokan === 'hijri' ? ['hijri' => $yH . '-' . $bH] : []),
                'masehi' => route('admin.kalender.index', $patokan === 'masehi' ? $request->only('bulan') : $gantiPatokan),
                'hijri' => route('admin.kalender.index', $patokan === 'hijri' ? $request->only('hijri') : $gantiPatokan),
            ],
            'hariIniTeks' => $hariIni->locale(app()->getLocale())->translatedFormat('l, d F Y') . ' · ' . KalenderPendidikan::hijriyah($hariIni),
            'tabelAda' => $tabelAda,
        ]);
    }

    /** Semua kegiatan untuk Vue (urut tanggal mulai) */
    private function semuaKegiatan(): array
    {
        $hariIni = now('Asia/Jakarta')->startOfDay();

        return KalenderPendidikan::orderBy('tanggal_mulai')->orderBy('id')->get()
            ->map(fn ($k) => $k->untukTampilan($hariIni))->values()->all();
    }

    /** GET /admin/kalender-pendidikan/hijriyah?tanggal=2026-11-21 : tulisan Hijriyah untuk isian formulir */
    public function hijriyah(Request $request): JsonResponse
    {
        $data = $request->validate(['tanggal' => ['required', 'date_format:Y-m-d']]);

        return response()->json(['hijriyah' => KalenderPendidikan::hijriyah($data['tanggal'])]);
    }

    /** POST /admin/kalender-pendidikan */
    public function simpan(Request $request): JsonResponse
    {
        $kegiatan = KalenderPendidikan::create($this->valid($request) + ['dibuat_oleh' => $request->user()?->id]);

        return response()->json(['pesan' => __('Kegiatan ditambahkan ke kalender.'), 'kegiatan' => $kegiatan->untukTampilan()]);
    }

    /** PUT /admin/kalender-pendidikan/{kalender} */
    public function ubah(Request $request, KalenderPendidikan $kalender): JsonResponse
    {
        $kalender->update($this->valid($request));

        return response()->json(['pesan' => __('Kegiatan diperbarui.'), 'kegiatan' => $kalender->fresh()->untukTampilan()]);
    }

    /** DELETE /admin/kalender-pendidikan/{kalender} */
    public function hapus(KalenderPendidikan $kalender): JsonResponse
    {
        $kalender->delete();

        return response()->json(['pesan' => __('Kegiatan dihapus dari kalender.')]);
    }

    private function valid(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'warna' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ], [
            'judul.required' => __('Nama kegiatan wajib diisi.'),
            'warna.required' => __('Pilih warna kegiatan.'),
            'warna.regex' => __('Pilih warna kegiatan.'),
            'tanggal_mulai.required' => __('Tanggal mulai wajib diisi.'),
            'tanggal_selesai.required' => __('Tanggal selesai wajib diisi.'),
            'tanggal_selesai.after_or_equal' => __('Tanggal selesai tidak boleh sebelum tanggal mulai.'),
        ]);
        $data['warna'] = strtolower($data['warna']);

        return $data;
    }
}
