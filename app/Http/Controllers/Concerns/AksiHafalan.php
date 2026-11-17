<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Hafalan;
use App\Models\HafalanSelesai;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Aksi halaman "Hafalan" yang SAMA untuk asatid (Asatid\HafalanController) dan admin (Admin\HafalanController > Nilai Hafalan).
 * Bedanya hanya bolehUbahSemua(): admin boleh mengubah / menghapus semua baris, asatid hanya baris miliknya sendiri.
 * Tampilan: resources/views/asatid/hafalan.blade.php.
 */
trait AksiHafalan
{
    /** Admin = true (semua baris), asatid = false (hanya baris yang ia nilai sendiri) */
    abstract protected function bolehUbahSemua(): bool;

    /** Data awal halaman isi hafalan */
    protected function dataHalaman(Request $request): array
    {
        return [
            'kelasList' => Kelas::where('is_active', true)->ordered()
                ->withCount(['santri' => fn ($q) => $q->where('status', PendaftaranSiswa::DITERIMA)])
                ->get(['id', 'nama', 'kode']),
            'kitabList' => Hafalan::daftarKitab(),
            'nilaiList' => Hafalan::pilihanNilai(),
            'hariIni'   => Hafalan::sekarang()->toDateString(),
            // Dibuka dari tombol "Buka" di laporan admin: langsung pilih halaqoh, santri, kitab
            'awal'      => [
                'kelas' => (int) $request->query('kelas') ?: null,
                'siswa' => (int) $request->query('siswa') ?: null,
                'kitab' => (int) $request->query('kitab') ?: null,
            ],
        ];
    }

    /** GET .../hafalan/peserta?kelas_id= : santri halaqoh itu + ringkasan per kitab */
    public function peserta(Request $request): JsonResponse
    {
        $kelasId = $request->validate(['kelas_id' => ['required', 'integer', 'exists:kelas,id']])['kelas_id'];
        $santri = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)->where('kelas_id', $kelasId)
            ->orderBy('nama_lengkap')->get();
        $ringkas = $this->ringkasan($santri->pluck('id')->all());

        return response()->json([
            'peserta' => $santri->map(fn (PendaftaranSiswa $s) => [
                'id'    => $s->id,
                'nama'  => $s->nama_lengkap,
                'arab'  => $s->nama_lengkap_arab,
                'nomor' => $s->nomor_pendaftaran,
                'foto'  => $s->foto_url,
                'kitab' => (object) ($ringkas[$s->id] ?? []),
            ])->values(),
        ]);
    }

    /** GET .../hafalan/riwayat?siswa_id=&kitab_id= : semua setoran santri untuk kitab itu + tanda hafal */
    public function riwayat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'integer', 'exists:pendaftaran_siswas,id'],
            'kitab_id' => ['required', 'integer', 'exists:kitab,id'],
        ]);

        return response()->json($this->dataRiwayat($data['siswa_id'], $data['kitab_id']));
    }

    /** POST .../hafalan : tambah baris baru, atau ubah baris (id) */
    public function simpan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id'             => ['nullable', 'integer', 'exists:hafalan,id'],
            'siswa_id'       => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'kitab_id'       => ['required', 'integer', 'exists:kitab,id'],
            'tanggal'        => ['required', 'date'],
            'halaman_dari'   => ['required', 'integer', 'min:0', 'max:9999'],
            'halaman_sampai' => ['required', 'integer', 'min:0', 'max:9999', 'gte:halaman_dari'],
            'ragu'           => ['required', 'integer', 'min:0', 'max:999'],
            'salah'          => ['required', 'integer', 'min:0', 'max:999'],
            'nilai'          => ['required', 'string', 'max:100'],
        ], [
            'halaman_sampai.gte' => __('Halaman "sampai" tidak boleh lebih kecil dari halaman "dari".'),
            'nilai.required'     => __('Pilih nilainya dulu.'),
        ]);

        if ($data['id'] ?? null) {
            $baris = Hafalan::findOrFail($data['id']);
            $this->pastikanBoleh($baris);
        } else {
            abort_unless(in_array((int) $data['kitab_id'], Hafalan::idKitab(), true), 422, __('Kitab ini tidak ada di daftar hafalan (Pengaturan).'));
            abort_unless(in_array($data['nilai'], Hafalan::pilihanNilai(), true), 422, __('Nilai tidak ada di pilihan nilai (Pengaturan).'));
            $baris = new Hafalan(['dinilai_oleh' => Auth::id()]);
        }

        $baris->fill([
            'pendaftaran_siswa_id' => $data['siswa_id'],
            'kitab_id'             => $data['kitab_id'],
            'tanggal'              => $data['tanggal'],
            'halaman_dari'         => $data['halaman_dari'],
            'halaman_sampai'       => $data['halaman_sampai'],
            'ragu'                 => $data['ragu'],
            'salah'                => $data['salah'],
            'nilai'                => $data['nilai'],
        ])->save();

        return response()->json([
            'pesan' => ($data['id'] ?? null) ? __('Perubahan disimpan.') : __('Setoran hafalan disimpan.'),
        ] + $this->dataRiwayat($data['siswa_id'], $data['kitab_id']));
    }

    /** DELETE .../hafalan/{hafalan} */
    public function hapus(Hafalan $hafalan): JsonResponse
    {
        $this->pastikanBoleh($hafalan);
        $hafalan->delete();

        return response()->json(['pesan' => __('Baris dihapus.')] + $this->dataRiwayat($hafalan->pendaftaran_siswa_id, $hafalan->kitab_id));
    }

    /** POST .../hafalan/selesai : tandai / batalkan "sudah hafal seluruh kitab" (selesai = true|false) */
    public function selesai(Request $request): JsonResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'integer', 'exists:pendaftaran_siswas,id'],
            'kitab_id' => ['required', 'integer', 'exists:kitab,id'],
            'selesai'  => ['required', 'boolean'],
        ]);
        $kunci = ['pendaftaran_siswa_id' => $data['siswa_id'], 'kitab_id' => $data['kitab_id']];

        if ($data['selesai']) {
            HafalanSelesai::updateOrCreate($kunci, ['ditandai_oleh' => Auth::id(), 'ditandai_pada' => Hafalan::sekarang()]);
            $pesan = __('Ditandai: sudah hafal seluruh kitab.');
        } else {
            HafalanSelesai::where($kunci)->delete();
            $pesan = __('Tanda hafal dibatalkan.');
        }

        return response()->json(['pesan' => $pesan] + $this->dataRiwayat($data['siswa_id'], $data['kitab_id']));
    }

    // ------------------------------------------------------------------

    private function pastikanBoleh(Hafalan $baris): void
    {
        abort_unless($this->bolehUbahSemua() || (int) $baris->dinilai_oleh === (int) Auth::id(), 403, __('Hanya penilai baris ini yang boleh mengubahnya.'));
    }

    /** { siswa_id: { kitab_id: {jumlah, halaman, nilai, selesai} } } */
    protected function ringkasan(array $siswaIds): array
    {
        $hasil = [];
        Hafalan::whereIn('pendaftaran_siswa_id', $siswaIds)->orderBy('tanggal')->orderBy('id')->get()
            ->each(function (Hafalan $h) use (&$hasil) {
                $r = &$hasil[$h->pendaftaran_siswa_id][$h->kitab_id];
                $r['jumlah'] = ($r['jumlah'] ?? 0) + 1;
                $r['halaman'] = max($r['halaman'] ?? 0, $h->halaman_sampai);
                $r['nilai'] = $h->nilai;                      // urut tanggal -> yang terakhir
                $r['tanggal'] = $h->tanggal->toDateString();
                $r['selesai'] ??= false;
            });
        HafalanSelesai::whereIn('pendaftaran_siswa_id', $siswaIds)->get()
            ->each(function (HafalanSelesai $s) use (&$hasil) {
                $r = &$hasil[$s->pendaftaran_siswa_id][$s->kitab_id];
                $r = ($r ?? []) + ['jumlah' => 0, 'halaman' => null, 'nilai' => null, 'tanggal' => null];
                $r['selesai'] = true;
            });

        return $hasil;
    }

    private function dataRiwayat(int $siswaId, int $kitabId): array
    {
        $selesai = HafalanSelesai::with('penanda:id,name')->where('pendaftaran_siswa_id', $siswaId)->where('kitab_id', $kitabId)->first();

        return [
            'baris' => Hafalan::with('penilai:id,name')->where('pendaftaran_siswa_id', $siswaId)->where('kitab_id', $kitabId)
                ->orderBy('tanggal')->orderBy('id')->get()
                ->map(fn (Hafalan $h) => [
                    'id'      => $h->id,
                    'tanggal' => $h->tanggal->toDateString(),
                    'dari'    => $h->halaman_dari,
                    'sampai'  => $h->halaman_sampai,
                    'ragu'    => $h->ragu,
                    'salah'   => $h->salah,
                    'nilai'   => $h->nilai,
                    'oleh'    => $h->penilai?->name,
                    'boleh'   => $this->bolehUbahSemua() || (int) $h->dinilai_oleh === (int) Auth::id(),
                ])->values(),
            'selesai' => $selesai ? [
                'oleh'  => $selesai->penanda?->name,
                'waktu' => $selesai->ditandai_pada?->format('Y-m-d H:i'),
            ] : null,
        ];
    }
}
