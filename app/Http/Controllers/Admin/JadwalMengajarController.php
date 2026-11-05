<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulanAjaran;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\Kitab;
use App\Models\PendaftaranAsatid;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\WaktuPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Akademik > Jadwal Mengajar
 * Jadwal per bulan ajaran: baris = jam pelajaran (Master Data > Waktu Pelajaran), kolom = hari.
 * Tiap slot berisi kelas + kitab (Kurikulum) + ustadz (akun asatid yang sudah diterima).
 * Jadwal ini tampil juga di dasbor asatid (menu Jadwal Pelajaran).
 */
class JadwalMengajarController extends Controller
{
    public function index(Request $request)
    {
        $tahunList = TahunAjaran::with('bulan')->orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $bulan = $request->query('bulan')
            ? BulanAjaran::with('tahunAjaran')->find($request->query('bulan'))
            : BulanAjaran::aktif();
        $bulan ??= $tahunList->first()?->bulan->first()?->load('tahunAjaran');

        $kelasFilter = $request->query('kelas');
        $tampilan = in_array($request->query('tampilan'), ['grid', 'ustadz', 'kelas'], true) ? $request->query('tampilan') : 'grid';

        $jadwal = $bulan
            ? JadwalMengajar::with(['kelas', 'kitab.bidangIlmu', 'waktu'])
                ->where('bulan_ajaran_id', $bulan->id)
                ->when($kelasFilter, fn ($q, $k) => $q->where('kelas_id', $k))
                ->get()
            : collect();

        $ustadzList = $this->ustadzList();
        $namaUstadz = $ustadzList->pluck('nama', 'id');
        $arabUstadz = $ustadzList->pluck('arab', 'id'); // nama Arab ustadz (tampil di bawah nama Indonesia)

        return view('admin.akademik.jadwal', [
            'tahunList' => $tahunList,
            'bulan' => $bulan,
            'jadwal' => $jadwal,
            'slot' => $jadwal->groupBy(fn ($j) => $j->waktu_pelajaran_id . '-' . $j->hari),
            'waktuList' => WaktuPelajaran::ordered()->where('is_active', true)->get(),
            'kelasList' => Kelas::ordered()->get(),
            'kitabList' => Kitab::where('is_active', true)->orderBy('nama')->get(),
            // Kitab kurikulum bulan ini (tahun ajaran & bulan ke cocok) -> ditampilkan di pilihan form
            'kitabBulanIni' => $bulan ? Kitab::untukBulan($bulan)->pluck('id') : collect(),
            'ustadzList' => $ustadzList,
            'namaUstadz' => $namaUstadz,
            'arabUstadz' => $arabUstadz,
            'kelasFilter' => $kelasFilter,
            'tampilan' => $tampilan,
            'bulanLain' => $bulan ? BulanAjaran::with('tahunAjaran')->whereKeyNot($bulan->id)->withCount('jadwal')->having('jadwal_count', '>', 0)->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $this->cekBentrok($data);
        JadwalMengajar::create($data);

        return back()->with('success', 'Jadwal ditambahkan.');
    }

    public function update(Request $request, JadwalMengajar $jadwal)
    {
        $data = $this->validasi($request);
        $this->cekBentrok($data, $jadwal->id);
        $jadwal->update($data);

        return back()->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(JadwalMengajar $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal dihapus.');
    }

    /** Salin semua jadwal dari bulan lain ke bulan yang sedang dibuka. Slot yang sudah terisi dilewati. */
    public function salin(Request $request)
    {
        $data = $request->validate([
            'dari' => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'ke' => ['required', 'integer', 'different:dari', Rule::exists('bulan_ajaran', 'id')],
            'timpa' => ['nullable', 'boolean'],
        ], ['ke.different' => 'Pilih bulan sumber yang berbeda dengan bulan tujuan.']);

        [$disalin, $dilewati] = DB::transaction(function () use ($data) {
            if (! empty($data['timpa'])) {
                JadwalMengajar::where('bulan_ajaran_id', $data['ke'])->delete();
            }
            $disalin = 0;
            $dilewati = 0;
            foreach (JadwalMengajar::where('bulan_ajaran_id', $data['dari'])->get() as $j) {
                $baru = ['bulan_ajaran_id' => (int) $data['ke']] + $j->only(['hari', 'waktu_pelajaran_id', 'kelas_id', 'kitab_id', 'user_id', 'catatan']);
                if ($this->adaBentrok($baru)) {
                    $dilewati++;
                    continue;
                }
                JadwalMengajar::create($baru);
                $disalin++;
            }

            return [$disalin, $dilewati];
        });

        return back()->with('success', "{$disalin} jadwal disalin." . ($dilewati ? " {$dilewati} dilewati karena slotnya sudah terisi." : ''));
    }

    /**
     * Kartu ditarik ke slot lain di tampilan Grid: "pindah" (ganti hari/jam) atau "salin" (tambah jadwal sama di hari/jam baru).
     * Langsung tersimpan; dijawab JSON supaya halaman tidak dimuat ulang.
     */
    public function geser(Request $request, JadwalMengajar $jadwal)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['pindah', 'salin'])],
            'hari' => ['required', 'integer', Rule::in(array_keys(JadwalMengajar::HARI))],
            'waktu_pelajaran_id' => ['required', 'integer', Rule::exists('waktu_pelajaran', 'id')],
        ]);
        $baru = ['hari' => (int) $data['hari'], 'waktu_pelajaran_id' => (int) $data['waktu_pelajaran_id']]
            + $jadwal->only(['bulan_ajaran_id', 'kelas_id', 'kitab_id', 'user_id', 'catatan']);

        if ($data['mode'] === 'pindah') {
            $this->cekBentrok($baru, $jadwal->id);
            $jadwal->update($baru);

            return response()->json(['pesan' => __('Jadwal dipindah.'), 'id' => $jadwal->id]);
        }

        $this->cekBentrok($baru); // termasuk cek frekuensi kitab
        $salinan = JadwalMengajar::create($baru);

        return response()->json(['pesan' => __('Jadwal disalin.'), 'id' => $salinan->id]);
    }

    /** Ganti ustadz di seluruh jadwal bulan ini (mis. ustadz berhalangan). */
    public function gantiUstadz(Request $request)
    {
        $data = $request->validate([
            'bulan_ajaran_id' => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'dari' => ['required', 'integer'],
            'ke' => ['required', 'integer', 'different:dari', Rule::exists('users', 'id')->where('role', PendaftaranAsatid::ROLE)],
        ], ['ke.different' => 'Pilih ustadz pengganti yang berbeda.']);

        $jadwal = JadwalMengajar::where('bulan_ajaran_id', $data['bulan_ajaran_id'])->where('user_id', $data['dari'])->get();
        $bentrok = $jadwal->filter(fn ($j) => JadwalMengajar::where('bulan_ajaran_id', $j->bulan_ajaran_id)
            ->where('hari', $j->hari)->where('waktu_pelajaran_id', $j->waktu_pelajaran_id)
            ->where('user_id', $data['ke'])->exists());

        if ($bentrok->isNotEmpty()) {
            throw ValidationException::withMessages(['ke' => "Ustadz pengganti sudah mengajar di {$bentrok->count()} jam yang sama. Atur jadwal itu dulu."]);
        }

        JadwalMengajar::whereIn('id', $jadwal->pluck('id'))->update(['user_id' => $data['ke']]);

        return back()->with('success', "{$jadwal->count()} jadwal dipindahkan ke ustadz pengganti.");
    }

    /* ---------------- Bantuan ---------------- */

    /** Akun asatid yang sudah diterima: [id, nama, arab] (nama Indonesia & Arab dari data pendaftaran asatid bila ada) */
    private function ustadzList()
    {
        $data = PendaftaranAsatid::whereNotNull('user_id')->get(['user_id', 'nama_lengkap', 'nama_lengkap_arab'])->keyBy('user_id');

        return User::where('role', PendaftaranAsatid::ROLE)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($u) => (object) [
                'id' => $u->id,
                'nama' => $data[$u->id]->nama_lengkap ?? $u->name,
                'arab' => $data[$u->id]->nama_lengkap_arab ?? null,
            ])
            ->sortBy('nama')->values();
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'bulan_ajaran_id' => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'hari' => ['required', 'integer', Rule::in(array_keys(JadwalMengajar::HARI))],
            'waktu_pelajaran_id' => ['required', 'integer', Rule::exists('waktu_pelajaran', 'id')],
            'kelas_id' => ['required', 'integer', Rule::exists('kelas', 'id')],
            'kitab_id' => ['nullable', 'integer', Rule::exists('kitab', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', PendaftaranAsatid::ROLE)],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'kelas_id.required' => 'Pilih kelas.',
            'waktu_pelajaran_id.required' => 'Pilih jam pelajaran.',
            'user_id.exists' => 'Ustadz harus akun asatid yang sudah diterima.',
        ]);
    }

    /** Pesan bentrok (kelas sudah ada pelajaran / ustadz mengajar di kelas lain pada jam yang sama), atau null */
    private function adaBentrok(array $d, ?int $kecuali = null): ?string
    {
        $sama = fn () => JadwalMengajar::with('kelas')
            ->where('bulan_ajaran_id', $d['bulan_ajaran_id'])->where('hari', $d['hari'])
            ->where('waktu_pelajaran_id', $d['waktu_pelajaran_id'])
            ->when($kecuali, fn ($q) => $q->whereKeyNot($kecuali));

        if ($sama()->where('kelas_id', $d['kelas_id'])->exists()) {
            return 'Kelas ini sudah punya pelajaran di hari dan jam tersebut.';
        }
        if (! empty($d['user_id']) && ($lain = $sama()->where('user_id', $d['user_id'])->first())) {
            return 'Bentrok: ustadz ini sudah mengajar di kelas ' . ($lain->kelas?->nama ?? '-') . ' pada hari dan jam yang sama.';
        }
        // Frekuensi: jumlah jadwal kitab ini di kelas ini (seminggu) tidak boleh melebihi frekuensi kitab
        if (! empty($d['kitab_id']) && ($kitab = Kitab::find($d['kitab_id']))) {
            $sudah = JadwalMengajar::where('bulan_ajaran_id', $d['bulan_ajaran_id'])
                ->where('kelas_id', $d['kelas_id'])->where('kitab_id', $kitab->id)
                ->when($kecuali, fn ($q) => $q->whereKeyNot($kecuali))->count();
            if ($sudah >= $kitab->frekuensi) {
                return "Kitab {$kitab->nama} di kelas ini sudah {$sudah} kali seminggu (batas frekuensi {$kitab->frekuensi}).";
            }
        }

        return null;
    }

    private function cekBentrok(array $d, ?int $kecuali = null): void
    {
        if ($pesan = $this->adaBentrok($d, $kecuali)) {
            throw ValidationException::withMessages(['jadwal' => $pesan]);
        }
    }
}
