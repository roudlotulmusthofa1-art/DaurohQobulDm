<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\NilaiHarian;
use App\Models\NilaiUjian;
use App\Models\NilaiUjianLisan;
use App\Models\PendaftaranSiswa;

/**
 * Admin > Laporan > Laporan Nilai: ringkasan nilai Kelas & Pelajaran per santri.
 * - Harian % = rata-rata total Nilai Harian (sudah dari 100) semua kitab
 * - Lisan %  = rata-rata persen Ujian Lisan
 * - Ujian %  = rata-rata persen Ujian (tulis)
 * - Nilai Akhir % = rata-rata bagian yang sudah ada nilainya (bobot sama)
 * Ranking & predikat dihitung Vue (supaya bisa dihitung ulang per kelas).
 * Rincian setiap nilai ikut dikirim untuk jendela "rincian santri".
 */
class LaporanNilaiController extends Controller
{
    /** GET /admin/laporan-nilai */
    public function index()
    {
        $harian = NilaiHarian::with(['kitab:id,nama', 'bulanAjaran:id,nama'])->orderBy('tanggal')->get()->groupBy('pendaftaran_siswa_id');
        $lisan = NilaiUjianLisan::orderBy('tanggal')->get()->groupBy('pendaftaran_siswa_id');
        $ujian = NilaiUjian::orderBy('tanggal')->get()->groupBy('pendaftaran_siswa_id');
        $persen = fn ($n, $maks) => $maks ? round($n / $maks * 100, 1) : 0;
        $rata = fn ($list) => $list->count() ? round($list->avg(), 1) : null;

        $santri = PendaftaranSiswa::with('kelasMaster:id,nama')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')->get()
            ->map(function (PendaftaranSiswa $s) use ($harian, $lisan, $ujian, $persen, $rata) {
                $h = ($harian[$s->id] ?? collect())->map(fn (NilaiHarian $n) => [
                    'kitab' => $n->kitab?->nama ?? '-',
                    'bulan' => $n->bulanAjaran?->nama,
                    'nilai' => collect(NilaiHarian::ASPEK)->keys()->map(fn ($k) => (int) $n->{$k})->all(),
                    'total' => (int) $n->total,
                ])->values();
                $l = ($lisan[$s->id] ?? collect())->map(fn (NilaiUjianLisan $n) => [
                    'nama'    => collect([$n->fan, $n->kitab])->filter()->implode(' · ') ?: '-',
                    'tanggal' => $n->tanggal->toDateString(),
                    'nilai'   => (int) $n->total,
                    'maks'    => (int) $n->total_maks,
                    'persen'  => $persen($n->total, $n->total_maks),
                ])->values();
                $u = ($ujian[$s->id] ?? collect())->map(fn (NilaiUjian $n) => [
                    'nama'    => collect([$n->nama_ujian, $n->fan, $n->kitab])->filter()->unique()->implode(' · ') ?: '-',   // ujian boleh banyak fan
                    'tanggal' => $n->tanggal->toDateString(),
                    'nilai'   => (int) $n->nilai,
                    'maks'    => (int) $n->nilai_maks,
                    'persen'  => $persen($n->nilai, $n->nilai_maks),
                ])->values();

                $bagian = [
                    'harian' => $rata($h->pluck('total')),
                    'lisan'  => $rata($l->pluck('persen')),
                    'ujian'  => $rata($u->pluck('persen')),
                ];
                $ada = array_filter($bagian, fn ($v) => $v !== null);

                return [
                    'id'       => $s->id,
                    'nama'     => $s->nama_lengkap,
                    'arab'     => $s->nama_lengkap_arab,
                    'nomor'    => $s->nomor_pendaftaran,
                    'kelas_id' => $s->kelas_id,
                    'kelas'    => $s->kelasMaster?->nama,
                    'foto'     => $s->foto_url,
                    'persen'   => $bagian,
                    'akhir'    => $ada ? round(array_sum($ada) / count($ada), 1) : null,
                    'jumlah'   => ['harian' => $h->count(), 'lisan' => $l->count(), 'ujian' => $u->count()],
                    'rincian'  => ['harian' => $h, 'lisan' => $l, 'ujian' => $u],
                ];
            })->values();

        return view('admin.laporan-nilai.index', [
            'santri'    => $santri,
            'aspek'     => NilaiHarian::daftarAspek(),
            'kelasList' => Kelas::ordered()->get(['id', 'nama']),
        ]);
    }
}
