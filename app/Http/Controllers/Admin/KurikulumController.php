<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidangIlmu;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\Kitab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Akademik > Kurikulum & Kitab
 * Kitab per bidang ilmu (Master Data > Bidang Ilmu), diajarkan di kelas mana (Master Data > Kelas)
 * dan pada bulan ajaran ke berapa. Kartu menunjukkan apakah kitab sudah masuk Jadwal Mengajar bulan aktif.
 */
class KurikulumController extends Controller
{
    public function index(Request $request)
    {
        $semua = Kitab::with(['bidangIlmu', 'kelas'])->orderBy('nama')->get();
        $bulanAktif = BulanAjaran::aktif();

        // Berapa kelas yang sudah menjadwalkan kitab ini di bulan aktif
        $terjadwal = $bulanAktif
            ? JadwalMengajar::where('bulan_ajaran_id', $bulanAktif->id)->whereNotNull('kitab_id')
                ->selectRaw('kitab_id, count(distinct kelas_id) as n')->groupBy('kitab_id')->pluck('n', 'kitab_id')
            : collect();

        $kata = mb_strtolower(trim((string) $request->q));
        $bidangDipilih = $request->query('bidang');
        $jenisDipilih = $request->query('jenis');

        $tersaring = $semua->filter(fn (Kitab $k) =>
            (! $bidangDipilih || (string) $k->bidang_ilmu_id === (string) $bidangDipilih || ($bidangDipilih === 'tanpa' && ! $k->bidang_ilmu_id))
            && (! $jenisDipilih || $k->jenis === $jenisDipilih)
            && ($kata === '' || str_contains(mb_strtolower($k->nama . ' ' . $k->nama_arab . ' ' . $k->pengarang . ' ' . $k->deskripsi), $kata)));

        $bidangList = BidangIlmu::ordered()->get();
        $kelasList = Kelas::ordered()->get();

        return view('admin.akademik.kurikulum', [
            'grup' => $tersaring->groupBy(fn ($k) => $k->bidang_ilmu_id ?? 'tanpa'),
            'jumlahPerBidang' => $semua->countBy(fn ($k) => $k->bidang_ilmu_id ?? 'tanpa'),
            'semua' => $semua,
            'bidangList' => $bidangList,
            'kelasList' => $kelasList,
            // Kartu ringkasan: jumlah kitab di 3 kelas pertama
            'kelasRingkas' => $kelasList->take(3)->map(fn ($kelas) => [
                'nama' => $kelas->nama,
                'jumlah' => $semua->filter(fn ($k) => $k->kelas->contains('id', $kelas->id))->count(),
            ]),
            'terjadwal' => $terjadwal,
            'bulanAktif' => $bulanAktif,
            'filter' => ['q' => $request->q, 'bidang' => $bidangDipilih, 'jenis' => $jenisDipilih],
        ]);
    }

    public function store(Request $request)
    {
        [$data, $kelas] = $this->validasi($request);

        DB::transaction(function () use ($data, $kelas) {
            Kitab::create($data)->kelas()->sync($kelas);
        });

        return back()->with('success', "Kitab {$data['nama']} ditambahkan.");
    }

    public function update(Request $request, Kitab $kurikulum)
    {
        [$data, $kelas] = $this->validasi($request);

        DB::transaction(function () use ($kurikulum, $data, $kelas) {
            $kurikulum->update($data);
            $kurikulum->kelas()->sync($kelas);
        });

        return back()->with('success', "Kitab {$kurikulum->nama} diperbarui.");
    }

    public function destroy(Kitab $kurikulum)
    {
        $jadwal = $kurikulum->jadwal()->count();
        $kurikulum->delete(); // jadwal yang memakai kitab ini menjadi "tanpa kitab"

        return back()->with('success', "Kitab {$kurikulum->nama} dihapus." . ($jadwal ? " {$jadwal} jadwal yang memakainya perlu dipilihkan kitab lain." : ''));
    }

    /** @return array{0: array, 1: array<int>} data kitab + daftar kelas_id */
    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nama_arab' => ['nullable', 'string', 'max:150'],
            'pengarang' => ['nullable', 'string', 'max:150'],
            'bidang_ilmu_id' => ['nullable', 'integer', Rule::exists('bidang_ilmu', 'id')],
            'jenis' => ['required', Rule::in(array_keys(Kitab::JENIS))],
            'bulan_ke' => ['nullable', 'array'],
            'bulan_ke.*' => ['integer', 'min:1', 'max:24'],
            'frekuensi' => ['required', 'integer', 'min:1', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kelas' => ['nullable', 'array'],
            'kelas.*' => ['integer', Rule::exists('kelas', 'id')],
        ], [
            'nama.required' => 'Nama kitab wajib diisi.',
            'frekuensi.required' => 'Frekuensi per minggu wajib diisi.',
        ]);

        $kelas = array_map('intval', $data['kelas'] ?? []);
        unset($data['kelas']);
        $data['bulan_ke'] = collect($data['bulan_ke'] ?? [])->map(fn ($n) => (int) $n)->unique()->sort()->values()->all();

        return [$data, $kelas];
    }
}
