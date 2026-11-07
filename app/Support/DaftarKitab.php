<?php

namespace App\Support;

use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kitab;
use Illuminate\Support\Collection;

/**
 * Pilihan kitab untuk asatid (Absensi Kelas & Penilaian > Nilai Harian).
 * - pilihan()   : kitab aktif bulan ajaran ini (atau semua kitab aktif kalau belum ada bulan aktif)
 * - terjadwal() : kitab yang dijadwalkan di tiap kelas bulan ini {kelas_id: [kitab_id, ...]} (ditaruh paling atas)
 * - jadwalGuru(): jadwal asatid yang login {hari, kelas_id, kitab_id, waktu_pelajaran_id} (kitab terpilih otomatis)
 */
class DaftarKitab
{
    public static function pilihan(): Collection
    {
        $bulan = BulanAjaran::aktif();

        return Kitab::with('bidangIlmu:id,nama')->where('is_active', true)
            ->when($bulan, fn ($q) => $q->untukBulan($bulan))
            ->orderBy('nama')->get()
            ->map(fn ($k) => [
                'id' => $k->id, 'nama' => $k->nama, 'arab' => $k->nama_arab,
                'bidang_ilmu_id' => $k->bidang_ilmu_id, 'bidang' => $k->bidangIlmu?->nama,
            ])->values();
    }

    public static function terjadwal(): Collection
    {
        $bulan = BulanAjaran::aktif();

        return $bulan
            ? JadwalMengajar::where('bulan_ajaran_id', $bulan->id)->whereNotNull('kitab_id')
                ->get(['kelas_id', 'kitab_id'])->groupBy('kelas_id')->map(fn ($j) => $j->pluck('kitab_id')->unique()->values())
            : collect();
    }

    public static function jadwalGuru(?int $userId): Collection
    {
        $bulan = BulanAjaran::aktif();

        return $bulan && $userId
            ? JadwalMengajar::where('bulan_ajaran_id', $bulan->id)->where('user_id', $userId)->whereNotNull('kitab_id')
                ->get(['hari', 'kelas_id', 'kitab_id', 'waktu_pelajaran_id'])
            : collect();
    }
}
