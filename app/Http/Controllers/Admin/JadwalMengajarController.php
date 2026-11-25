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
use App\Support\EksporDokumen;
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
            // Saat filter kelas aktif: jam mengajar ustadz di kelas lain, supaya form tahu ustadz mana yang tidak kosong
            'sibukKelasLain' => $bulan && $kelasFilter
                ? JadwalMengajar::with('kelas')->where('bulan_ajaran_id', $bulan->id)->where('kelas_id', '!=', $kelasFilter)->whereNotNull('user_id')->get()
                    ->map(fn ($j) => ['hari' => (int) $j->hari, 'waktu' => (int) $j->waktu_pelajaran_id, 'user_id' => $j->user_id, 'kelas' => $j->kelas?->nama])->values()
                : collect(),
            'tampilan' => $tampilan,
            'bulanLain' => $bulan ? BulanAjaran::with('tahunAjaran')->whereKeyNot($bulan->id)->withCount('jadwal')->having('jadwal_count', '>', 0)->get() : collect(),
        ]);
    }

    /**
     * GET /admin/jadwal/export — Print, PDF, atau Word.
     * kelompok: semua (satu tabel) | kelas | ustadz (satu tabel untuk tiap kelas/ustadz).
     */
    public function export(Request $request)
    {
        $v = $request->validate([
            'format'   => ['required', Rule::in(EksporDokumen::FORMAT)],
            'bulan'    => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'kelompok' => ['nullable', Rule::in(['semua', 'kelas', 'ustadz'])],
            'kelas'    => ['nullable', 'integer'],
            'ustadz'   => ['nullable', 'integer'],
            'pisah'    => ['nullable', 'boolean'],
        ]);
        $kelompok = $v['kelompok'] ?? 'semua';
        $bulan = BulanAjaran::with('tahunAjaran')->findOrFail($v['bulan']);

        $jadwal = JadwalMengajar::with(['kelas', 'kitab'])
            ->where('bulan_ajaran_id', $bulan->id)
            ->when($v['kelas'] ?? null, fn ($q, $k) => $q->where('kelas_id', $k))
            ->when($kelompok === 'ustadz' ? ($v['ustadz'] ?? null) : null, fn ($q, $u) => $q->where('user_id', $u))
            ->get();

        $ustadzList = $this->ustadzList();
        $namaUstadz = $ustadzList->pluck('nama', 'id');
        $arabUstadz = $ustadzList->pluck('arab', 'id');
        $urutKelas = Kelas::ordered()->pluck('id')->flip();
        $semuaWaktu = WaktuPelajaran::ordered()->get();

        // Satu kelompok = satu tabel; hanya jam yang berisi pelajaran yang ditampilkan
        $buatGrup = fn ($isi, $judul, $arab = null) => [
            'judul' => $judul,
            'arab' => $arab,
            'waktu' => $semuaWaktu->whereIn('id', $isi->pluck('waktu_pelajaran_id'))->values(),
            'slot' => $isi->sortBy(fn ($j) => $urutKelas[$j->kelas_id] ?? 999)->groupBy(fn ($j) => $j->waktu_pelajaran_id . '-' . $j->hari),
            'tampilKelas' => $kelompok !== 'kelas',
            'tampilUstadz' => $kelompok !== 'ustadz',
        ];

        $grup = match ($kelompok) {
            'kelas' => $jadwal->groupBy('kelas_id')
                ->sortBy(fn ($isi, $id) => $urutKelas[$id] ?? 999)
                ->map(fn ($isi) => $buatGrup($isi, $isi->first()->kelas?->nama ?? '-'))->values(),
            'ustadz' => $jadwal->groupBy(fn ($j) => $j->user_id ?? 0)
                ->sortBy(fn ($isi, $id) => $id ? ($namaUstadz[$id] ?? '') : "\u{10FFFF}") // belum ada ustadz di akhir
                ->map(fn ($isi, $id) => $buatGrup($isi, $namaUstadz[$id] ?? __('Belum ada ustadz'), $arabUstadz[$id] ?? null))->values(),
            default => $jadwal->isEmpty() ? collect() : collect([$buatGrup($jadwal, null)]),
        };

        $namaKelompok = ['semua' => __('Semua Kelas'), 'kelas' => __('Per Kelas'), 'ustadz' => __('Per Ustadz')][$kelompok];

        return EksporDokumen::kirim('admin.export.jadwal', [
            'judul'      => __('Jadwal Pelajaran') . ' · ' . $namaKelompok,
            'keterangan' => __('Tahun Ajaran') . ' ' . $bulan->tahunAjaran?->nama . ('H'),
            'grup'       => $grup,
            'hariList'   => collect(JadwalMengajar::HARI)->only($jadwal->pluck('hari')->unique()->all())->all(),
            'namaUstadz' => $namaUstadz,
            'arabUstadz' => $arabUstadz,
            'pisah'      => $request->boolean('pisah'),
        ], $v['format'], 'jadwal-pelajaran-' . $kelompok, true);
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
        // Centang "Tukar": jam tujuan berisi pelajaran kelas yang sama -> keduanya bertukar tempat
        if ($request->boolean('tukar')) {
            $this->tukarKartu($jadwal, (int) $data['hari'], (int) $data['waktu_pelajaran_id'], $data);

            return back()->with('success', 'Jadwal ditukar.');
        }
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
     * Kartu ditarik ke slot lain di tampilan Grid: "pindah" (ganti hari/jam), "salin" (tambah jadwal sama di hari/jam baru),
     * atau "tukar" (slot tujuan sudah berisi pelajaran kelas yang sama: kedua kartu bertukar tempat, kitab & ustadz ikut kartunya).
     * Langsung tersimpan; dijawab JSON supaya halaman tidak dimuat ulang.
     */
    public function geser(Request $request, JadwalMengajar $jadwal)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['pindah', 'salin', 'tukar'])],
            'hari' => ['required', 'integer', Rule::in(array_keys(JadwalMengajar::HARI))],
            'waktu_pelajaran_id' => ['required', 'integer', Rule::exists('waktu_pelajaran', 'id')],
        ]);
        $baru = ['hari' => (int) $data['hari'], 'waktu_pelajaran_id' => (int) $data['waktu_pelajaran_id']]
            + $jadwal->only(['bulan_ajaran_id', 'kelas_id', 'kitab_id', 'user_id', 'catatan']);

        if ($data['mode'] === 'tukar') {
            $asal = ['hari' => $jadwal->hari, 'waktu_pelajaran_id' => $jadwal->waktu_pelajaran_id];
            $pasangan = $this->tukarKartu($jadwal, $baru['hari'], $baru['waktu_pelajaran_id']);

            return response()->json([
                'pesan' => __('Jadwal ditukar.'),
                'id' => $jadwal->id,
                'pasangan_id' => $pasangan->id,
                'pasangan_hari' => $asal['hari'],
                'pasangan_waktu' => $asal['waktu_pelajaran_id'],
            ]);
        }

        if ($data['mode'] === 'pindah') {
            $this->cekBentrok($baru, $jadwal->id);
            $jadwal->update($baru);

            return response()->json(['pesan' => __('Jadwal dipindah.'), 'id' => $jadwal->id]);
        }

        $this->cekBentrok($baru); // termasuk cek frekuensi kitab
        $salinan = JadwalMengajar::create($baru);

        return response()->json(['pesan' => __('Jadwal disalin.'), 'id' => $salinan->id]);
    }

    /**
     * Tukar tempat dua kartu satu kelas: $jadwal ke slot tujuan, kartu di slot tujuan ke slot asal $jadwal.
     * Kartunya yang dipindah (bukan isinya), jadi riwayat seperti tanda pelajaran tetap ikut kartunya.
     * $isiBaru = isi form (kitab, ustadz, catatan baru). Kosong = isi kartu tidak diganti (geser kartu).
     * Hasil: kartu pasangan (sekarang di slot asal $jadwal).
     */
    private function tukarKartu(JadwalMengajar $jadwal, int $hari, int $waktuId, array $isiBaru = []): JadwalMengajar
    {
        if (isset($isiBaru['kelas_id']) && (int) $isiBaru['kelas_id'] !== (int) $jadwal->kelas_id) {
            throw ValidationException::withMessages(['jadwal' => __('Tukar hanya bisa dengan pelajaran kelas yang sama.')]);
        }
        $pasangan = JadwalMengajar::where('bulan_ajaran_id', $jadwal->bulan_ajaran_id)
            ->where('kelas_id', $jadwal->kelas_id)->where('hari', $hari)->where('waktu_pelajaran_id', $waktuId)
            ->whereKeyNot($jadwal->id)->first();
        if (! $pasangan) {
            throw ValidationException::withMessages(['jadwal' => __('Tidak ada pelajaran kelas ini di tempat tujuan untuk ditukar. Muat ulang halaman lalu coba lagi.')]);
        }

        $asal = ['hari' => $jadwal->hari, 'waktu_pelajaran_id' => $jadwal->waktu_pelajaran_id];
        $tujuan = ['hari' => $hari, 'waktu_pelajaran_id' => $waktuId];

        // Bentrok ustadz: ustadz (baru) kartu ini di slot tujuan, dan ustadz pasangan di slot asal (kedua kartu tidak dihitung)
        $kecuali = [$jadwal->id, $pasangan->id];
        $ustadzBaru = array_key_exists('user_id', $isiBaru) ? $isiBaru['user_id'] : $jadwal->user_id;
        foreach ([[$ustadzBaru, $tujuan], [$pasangan->user_id, $asal]] as [$userId, $slot]) {
            if (! $userId) {
                continue;
            }
            $lain = JadwalMengajar::with('kelas')->where('bulan_ajaran_id', $jadwal->bulan_ajaran_id)
                ->where('hari', $slot['hari'])->where('waktu_pelajaran_id', $slot['waktu_pelajaran_id'])
                ->where('user_id', $userId)->whereKeyNot($kecuali)->first();
            if ($lain) {
                throw ValidationException::withMessages(['jadwal' => __('Tidak bisa ditukar: :ustadz sudah mengajar di kelas :kelas pada jam itu.', [
                    'ustadz' => User::find($userId)?->name ?? __('ustadz'), 'kelas' => $lain->kelas?->nama ?? '-',
                ])]);
            }
        }

        // Kitab baru: cek frekuensi di kelas ini (kartu ini tidak dihitung)
        $kitabBaru = $isiBaru['kitab_id'] ?? null;
        if ($kitabBaru && (int) $kitabBaru !== (int) $jadwal->kitab_id && ($kitab = Kitab::find($kitabBaru))) {
            $sudah = JadwalMengajar::where('bulan_ajaran_id', $jadwal->bulan_ajaran_id)
                ->where('kelas_id', $jadwal->kelas_id)->where('kitab_id', $kitab->id)->whereKeyNot($jadwal->id)->count();
            if ($sudah >= $kitab->frekuensi) {
                throw ValidationException::withMessages(['jadwal' => "Kitab {$kitab->nama} di kelas ini sudah {$sudah} kali seminggu (batas frekuensi {$kitab->frekuensi})."]);
            }
        }

        // Satu kelas hanya boleh satu pelajaran per jam (aturan database), jadi kartu ini diparkir dulu di hari 0
        DB::transaction(function () use ($jadwal, $pasangan, $asal, $tujuan, $isiBaru) {
            $jadwal->update(['hari' => 0]);
            $pasangan->update($asal);
            $jadwal->update(array_merge($isiBaru, $tujuan));
        });

        return $pasangan;
    }

    /**
     * Ubah Sekaligus: ganti ustadz dan/atau kitab di banyak jadwal yang dicentang (dari jendela "Ubah Sekaligus").
     * Dipakai mis. ustadz berhalangan, atau mengganti pelajaran + ustadznya di satu kelas.
     * Ditolak bila ustadz baru bentrok di jam yang sama, atau kitab baru melebihi frekuensinya di suatu kelas.
     */
    public function ubahSekaligus(Request $request)
    {
        $data = $request->validate([
            'bulan_ajaran_id' => ['required', 'integer', Rule::exists('bulan_ajaran', 'id')],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', PendaftaranAsatid::ROLE)],
            'kitab_id' => ['nullable', 'integer', Rule::exists('kitab', 'id')],
        ], [
            'ids.required' => 'Centang dulu jadwal yang mau diubah.',
            'user_id.exists' => 'Ustadz harus akun asatid yang sudah diterima.',
        ]);

        if (empty($data['user_id']) && empty($data['kitab_id'])) {
            throw ValidationException::withMessages(['jadwal' => 'Pilih ustadz baru atau kitab baru dulu.']);
        }

        $dipilih = JadwalMengajar::with('kelas')->where('bulan_ajaran_id', $data['bulan_ajaran_id'])->whereIn('id', $data['ids'])->get();
        if ($dipilih->isEmpty()) {
            throw ValidationException::withMessages(['jadwal' => 'Jadwal yang dicentang tidak ditemukan. Muat ulang halaman lalu coba lagi.']);
        }
        $lainnya = JadwalMengajar::with('kelas')->where('bulan_ajaran_id', $data['bulan_ajaran_id'])->whereNotIn('id', $dipilih->pluck('id'))->get();

        // Ustadz baru: tidak boleh sudah mengajar di jam yang sama, dan tidak boleh dapat dua kelas di satu jam
        if (! empty($data['user_id'])) {
            foreach ($dipilih->groupBy(fn ($j) => $j->hari . '-' . $j->waktu_pelajaran_id) as $isi) {
                $j = $isi->first();
                $namaJam = (JadwalMengajar::HARI[$j->hari] ?? '') . ' ' . (WaktuPelajaran::find($j->waktu_pelajaran_id)?->nama ?? '');
                if ($isi->count() > 1) {
                    throw ValidationException::withMessages(['jadwal' => "Bentrok: ustadz baru akan mengajar {$isi->count()} kelas sekaligus di {$namaJam}. Hilangkan centang salah satunya."]);
                }
                $sama = $lainnya->first(fn ($x) => $x->hari == $j->hari && $x->waktu_pelajaran_id == $j->waktu_pelajaran_id && $x->user_id == $data['user_id']);
                if ($sama) {
                    throw ValidationException::withMessages(['jadwal' => "Bentrok: ustadz baru sudah mengajar kelas " . ($sama->kelas?->nama ?? '-') . " di {$namaJam}."]);
                }
            }
        }

        // Kitab baru: jumlahnya per kelas tidak boleh melebihi frekuensi kitab
        if (! empty($data['kitab_id']) && ($kitab = Kitab::find($data['kitab_id'])) && $kitab->frekuensi) {
            foreach ($dipilih->groupBy('kelas_id') as $kelasId => $isi) {
                $jumlah = $isi->count() + $lainnya->where('kelas_id', $kelasId)->where('kitab_id', $kitab->id)->count();
                if ($jumlah > $kitab->frekuensi) {
                    $namaKelas = $isi->first()->kelas?->nama ?? '-';
                    throw ValidationException::withMessages(['jadwal' => "Kitab {$kitab->nama} di kelas {$namaKelas} akan menjadi {$jumlah} kali seminggu (batas frekuensi {$kitab->frekuensi})."]);
                }
            }
        }

        $ubah = array_filter(['user_id' => $data['user_id'] ?? null, 'kitab_id' => $data['kitab_id'] ?? null]);
        JadwalMengajar::whereIn('id', $dipilih->pluck('id'))->update($ubah);

        return back()->with('success', "{$dipilih->count()} jadwal berhasil diubah.");
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
