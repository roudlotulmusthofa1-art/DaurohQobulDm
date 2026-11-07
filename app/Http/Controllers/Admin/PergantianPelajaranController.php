<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\PendaftaranAsatid;
use App\Models\TandaPelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dasbor admin > Pergantian Pelajaran (partial admin/partials/pergantian-pelajaran).
 * - data()  : jadwal hari ini per jam ke + status tiap guru (sudah absen? tanda terkirim / dilihat?)
 * - tanda() : kirim tanda "sudah pergantian pelajaran" ke satu guru (jadwal_id) atau semua guru di satu jam (waktu_id)
 * Guru melihat tanda sebagai jendela pengingat (asatid/partials/pengingat-pelajaran).
 */
class PergantianPelajaranController extends Controller
{
    /** GET /admin/pergantian-pelajaran — dipanggil panel tiap 30 detik */
    public function data(): JsonResponse
    {
        return response()->json(['jam' => self::susun()]);
    }

    /** POST /admin/pergantian-pelajaran/tanda  { jadwal_id } atau { waktu_id } */
    public function tanda(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['nullable', 'integer', 'required_without:waktu_id'],
            'waktu_id'  => ['nullable', 'integer'],
        ]);

        $hariIni = now('Asia/Jakarta');
        $jadwal = self::jadwalHariIni()
            ->whereNotNull('user_id')
            ->when($data['jadwal_id'] ?? null, fn ($c, $id) => $c->where('id', $id))
            ->when(! ($data['jadwal_id'] ?? null), fn ($c) => $c->where('waktu_pelajaran_id', $data['waktu_id']));

        abort_if($jadwal->isEmpty(), 422, __('Tidak ada guru yang bisa diberi tanda di jadwal ini.'));

        foreach ($jadwal as $j) {
            TandaPelajaran::updateOrCreate(
                ['jadwal_mengajar_id' => $j->id, 'tanggal' => $hariIni->toDateString()],
                ['user_id' => $j->user_id, 'dikirim_oleh' => Auth::id(), 'dikirim_at' => now(), 'dilihat_at' => null],
            );
        }

        return response()->json([
            'message' => __('Tanda ganti pelajaran terkirim ke :jumlah guru.', ['jumlah' => $jadwal->count()]),
            'jam'     => self::susun(),
        ]);
    }

    /** Jadwal mengajar hari ini (bulan ajaran aktif, hari menurut WIB) */
    private static function jadwalHariIni()
    {
        $bulan = BulanAjaran::aktif();

        return $bulan
            ? JadwalMengajar::with(['kelas:id,nama,urutan', 'kitab:id,nama', 'waktu', 'ustadz:id,name'])
                ->where('bulan_ajaran_id', $bulan->id)->where('hari', now('Asia/Jakarta')->dayOfWeekIso)->get()
            : collect();
    }

    /**
     * Data panel: [{waktu_id, jam, mulai, selesai, keadaan: sekarang|berikut|nanti|lewat, baris: [...]}]
     * Urutan: yang sedang berjalan, lalu yang akan datang, lalu yang sudah lewat.
     */
    public static function susun(): array
    {
        $sekarang = now('Asia/Jakarta');
        $jamIni = $sekarang->format('H:i');
        $tanggal = $sekarang->toDateString();
        $jadwal = self::jadwalHariIni();
        if ($jadwal->isEmpty()) {
            return [];
        }

        $nama = PendaftaranAsatid::whereNotNull('user_id')->pluck('nama_lengkap', 'user_id');
        $arab = PendaftaranAsatid::whereNotNull('user_id')->pluck('nama_lengkap_arab', 'user_id');
        $tanda = TandaPelajaran::whereIn('jadwal_mengajar_id', $jadwal->pluck('id'))->whereDate('tanggal', $tanggal)
            ->get()->keyBy('jadwal_mengajar_id');
        // Sudah absen = ada absensi kelas hari ini untuk kelas + jam ke yang sama
        $absen = Absensi::where('jenis', Absensi::KELAS)->whereDate('tanggal', $tanggal)
            ->get(['kelas_id', 'waktu_pelajaran_id'])
            ->map(fn ($a) => $a->kelas_id . ':' . $a->waktu_pelajaran_id)->flip();

        $jamSaja = fn ($t) => $t ? $t->setTimezone('Asia/Jakarta')->format('H:i') : null;

        $kelompok = $jadwal->groupBy('waktu_pelajaran_id')->map(function ($isi) use ($nama, $arab, $tanda, $absen, $jamSaja, $jamIni) {
            $w = $isi->first()->waktu;
            $mulai = $w?->jam_mulai;
            $selesai = $w?->jam_selesai;
            $keadaan = ! $mulai ? 'nanti'
                : ($mulai <= $jamIni && $jamIni < ($selesai ?: '23:59') ? 'sekarang'
                : ($mulai > $jamIni ? 'nanti' : 'lewat'));

            return [
                'waktu_id' => $isi->first()->waktu_pelajaran_id,
                'jam'      => $w?->nama ?? __('Jam'),
                'mulai'    => $mulai,
                'selesai'  => $selesai,
                'urutan'   => $w?->urutan ?? 99,
                'keadaan'  => $keadaan,
                'baris'    => $isi->sortBy(fn ($j) => $j->kelas?->urutan ?? 99)->map(fn ($j) => [
                    'jadwal_id' => $j->id,
                    'kelas'     => $j->kelas?->nama,
                    'kitab'     => $j->kitab?->nama,
                    'ustadz'    => $nama[$j->user_id] ?? $j->ustadz?->name,
                    'arab'      => $arab[$j->user_id] ?? null,
                    'ada_guru'  => (bool) $j->user_id,
                    'absen'     => isset($absen[$j->kelas_id . ':' . $j->waktu_pelajaran_id]),
                    'dikirim'   => $jamSaja($tanda[$j->id]->dikirim_at ?? null),
                    'dilihat'   => $jamSaja($tanda[$j->id]->dilihat_at ?? null),
                ])->values()->all(),
            ];
        });

        // Jam pertama yang akan datang = "berikut"
        $berikut = $kelompok->where('keadaan', 'nanti')->filter(fn ($k) => $k['mulai'])->sortBy('mulai')->keys()->first();
        if ($berikut !== null) {
            $kelompok[$berikut] = ['keadaan' => 'berikut'] + $kelompok[$berikut];
        }

        $urutKeadaan = ['sekarang' => 0, 'berikut' => 1, 'nanti' => 2, 'lewat' => 3];

        return $kelompok->sortBy(fn ($k) => [$urutKeadaan[$k['keadaan']], $k['mulai'] ?? '99', $k['urutan']])->values()->all();
    }
}
