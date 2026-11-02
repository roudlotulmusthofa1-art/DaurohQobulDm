<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BidangIlmu;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kitab;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Akademik > Kurikulum & Kitab
 * Kitab per bidang ilmu (Master Data > Bidang Ilmu), untuk satu tahun ajaran, diajarkan pada bulan ke berapa
 * (pilihan bulan mengikuti jumlah bulan di tahun ajaran itu). Kelas dipilih di Jadwal Mengajar.
 * Kartu menunjukkan apakah kitab sudah masuk Jadwal Mengajar bulan aktif.
 */
class KurikulumController extends Controller
{
    public function index(Request $request)
    {
        $semua = Kitab::with(['bidangIlmu', 'tahunAjaran.bulan'])->orderBy('nama')->get();
        $bulanAktif = BulanAjaran::aktif();
        $tahunList = TahunAjaran::with('bulan')->orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $tahunAktif = $tahunList->firstWhere('is_active', true);

        // Berapa kelas yang sudah menjadwalkan kitab ini di bulan aktif
        $terjadwal = $bulanAktif
            ? JadwalMengajar::where('bulan_ajaran_id', $bulanAktif->id)->whereNotNull('kitab_id')
                ->selectRaw('kitab_id, count(distinct kelas_id) as n')->groupBy('kitab_id')->pluck('n', 'kitab_id')
            : collect();

        $kata = mb_strtolower(trim((string) $request->q));
        $bidangDipilih = $request->query('bidang');
        $tahunDipilih = $request->query('tahun');

        $tersaring = $semua->filter(fn (Kitab $k) =>
            (! $bidangDipilih || (string) $k->bidang_ilmu_id === (string) $bidangDipilih || ($bidangDipilih === 'tanpa' && ! $k->bidang_ilmu_id))
            && (! $tahunDipilih || (string) $k->tahun_ajaran_id === (string) $tahunDipilih)
            && ($kata === '' || str_contains(mb_strtolower($k->nama . ' ' . $k->nama_arab . ' ' . $k->deskripsi), $kata)));

        return view('admin.akademik.kurikulum', [
            'grup' => $tersaring->groupBy(fn ($k) => $k->bidang_ilmu_id ?? 'tanpa'),
            'jumlahPerBidang' => $semua->countBy(fn ($k) => $k->bidang_ilmu_id ?? 'tanpa'),
            'semua' => $semua,
            'bidangList' => BidangIlmu::ordered()->get(),
            'tahunList' => $tahunList,
            'tahunAktif' => $tahunAktif,
            'terjadwal' => $terjadwal,
            'bulanAktif' => $bulanAktif,
            // Kitab yang diajarkan di bulan aktif (tahun ajaran & bulan ke cocok)
            'jumlahBulanIni' => $bulanAktif ? Kitab::untukBulan($bulanAktif)->count() : 0,
            'filter' => ['q' => $request->q, 'bidang' => $bidangDipilih, 'tahun' => $tahunDipilih],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        Kitab::create($data);

        return back()->with('success', "Kitab {$data['nama']} ditambahkan.");
    }

    public function update(Request $request, Kitab $kurikulum)
    {
        $kurikulum->update($this->validasi($request));

        return back()->with('success', "Kitab {$kurikulum->nama} diperbarui.");
    }

    public function destroy(Kitab $kurikulum)
    {
        $jadwal = $kurikulum->jadwal()->count();
        $kurikulum->delete(); // jadwal yang memakai kitab ini menjadi "tanpa kitab"

        return back()->with('success', "Kitab {$kurikulum->nama} dihapus." . ($jadwal ? " {$jadwal} jadwal yang memakainya perlu dipilihkan kitab lain." : ''));
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nama_arab' => ['nullable', 'string', 'max:150'],
            'tahun_ajaran_id' => ['required', 'integer', Rule::exists('tahun_ajaran', 'id')],
            'bidang_ilmu_id' => ['nullable', 'integer', Rule::exists('bidang_ilmu', 'id')],
            'bulan_ke' => ['nullable', 'array'],
            'bulan_ke.*' => ['integer', 'min:1'],
            'frekuensi' => ['required', 'integer', 'min:1', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama.required' => 'Nama kitab wajib diisi.',
            'tahun_ajaran_id.required' => 'Pilih tahun ajaran.',
            'frekuensi.required' => 'Frekuensi per minggu wajib diisi.',
        ]);

        // Bulan ke harus ada di tahun ajaran yang dipilih
        $urutan = TahunAjaran::find($data['tahun_ajaran_id'])->bulan()->pluck('urutan')->map(fn ($n) => (int) $n);
        $data['bulan_ke'] = collect($data['bulan_ke'] ?? [])->map(fn ($n) => (int) $n)
            ->filter(fn ($n) => $urutan->contains($n))->unique()->sort()->values()->all();

        return $data;
    }
}
