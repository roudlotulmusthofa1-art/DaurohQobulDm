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
     * Simpan hasil geser kartu di tampilan Grid: beberapa jadwal pindah hari/jam sekaligus.
     * Bentrok diperiksa pada susunan akhir (jadi dua kartu boleh saling bertukar tempat).
     */
    public function pindah(Request $request)
    {
        $data = $request->validate([
            'bulan_ajaran_id' => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'pindah' => ['required', 'array', 'max:200'],
            'pindah.*.id' => ['required', 'integer', 'distinct'],
            'pindah.*.hari' => ['required', 'integer', Rule::in(array_keys(JadwalMengajar::HARI))],
            'pindah.*.waktu_pelajaran_id' => ['required', 'integer', Rule::exists('waktu_pelajaran', 'id')],
        ], ['pindah.required' => 'Belum ada jadwal yang dipindah.']);

        $semua = JadwalMengajar::with(['kelas', 'waktu'])->where('bulan_ajaran_id', $data['bulan_ajaran_id'])->get()->keyBy('id');
        $pindah = collect($data['pindah'])->keyBy('id');

        if ($pindah->keys()->diff($semua->keys())->isNotEmpty()) {
            throw ValidationException::withMessages(['jadwal' => 'Ada jadwal yang tidak ditemukan di bulan ini. Muat ulang halaman lalu coba lagi.']);
        }

        // Susunan akhir setelah dipindah, lalu cek bentrok per slot (hari + jam)
        $akhir = $semua->map(fn ($j) => [
            'hari' => (int) ($pindah[$j->id]['hari'] ?? $j->hari),
            'waktu' => (int) ($pindah[$j->id]['waktu_pelajaran_id'] ?? $j->waktu_pelajaran_id),
            'kelas' => $j->kelas_id,
            'ustadz' => $j->user_id,
        ]);
        foreach ($akhir->groupBy(fn ($a) => $a['hari'] . '-' . $a['waktu']) as $isi) {
            $slot = JadwalMengajar::HARI[$isi->first()['hari']] . ', ' . (\App\Models\WaktuPelajaran::find($isi->first()['waktu'])?->nama ?? '-');
            if ($isi->pluck('kelas')->duplicates()->isNotEmpty()) {
                $kelas = $semua->firstWhere('kelas_id', $isi->pluck('kelas')->duplicates()->first())?->kelas?->nama;
                throw ValidationException::withMessages(['jadwal' => "Bentrok di {$slot}: kelas {$kelas} punya dua pelajaran."]);
            }
            if ($isi->pluck('ustadz')->filter()->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['jadwal' => "Bentrok di {$slot}: satu ustadz mengajar dua kelas sekaligus."]);
            }
        }

        DB::transaction(function () use ($pindah) {
            // Pindahkan dulu ke hari sementara (8, 9, ...) agar tidak menabrak aturan unik saat dua kartu bertukar tempat
            $sementara = 8;
            foreach ($pindah as $id => $p) {
                JadwalMengajar::whereKey($id)->update(['hari' => $sementara++]);
            }
            foreach ($pindah as $id => $p) {
                JadwalMengajar::whereKey($id)->update(['hari' => $p['hari'], 'waktu_pelajaran_id' => $p['waktu_pelajaran_id']]);
            }
        });

        return back()->with('success', $pindah->count() . ' jadwal berhasil dipindah.');
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
            return 'Ustadz ini sudah mengajar di kelas ' . ($lain->kelas?->nama ?? '-') . ' pada hari dan jam yang sama.';
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
