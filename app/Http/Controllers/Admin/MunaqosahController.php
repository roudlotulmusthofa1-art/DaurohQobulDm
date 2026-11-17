<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\Munaqosah;
use App\Models\MunaqosahKolom;
use App\Models\NilaiDakwah;
use App\Models\NilaiHarian;
use App\Models\NilaiUjian;
use App\Models\NilaiUjianLisan;
use App\Models\PendaftaranSiswa;
use App\Support\DataCatatan;
use App\Support\NamaArab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Akademik > Munaqosah: musyawarah para asatid, apakah santri lulus / diterima atau tidak.
 * Tab 1 Musyrifin Dauroh : 3 pengaturan kolom (Nilai Ilmu, Suluk, Catatan Mandub) + kartu santri yang belum dikirim.
 *                          Halaman detail (tab baru) berisi 8 panel (6 = Detail Absensi); Simpan = kartu pindah ke tab Lajnah.
 * Tab 2 Lajnah Qobul Darul Musthofa : kartu yang sudah dikirim; detail hanya dibaca + catatan khusus Lajnah
 *                          + keputusan Lulus/Diterima atau Tidak (boleh diubah). Belum diputuskan = boleh dikembalikan.
 */
class MunaqosahController extends Controller
{
    /** GET /admin/munaqosah?tab=musyrifin|lajnah */
    public function index(Request $request)
    {
        $tab = $request->query('tab') === Munaqosah::LAJNAH ? Munaqosah::LAJNAH : Munaqosah::MUSYRIFIN;
        $data = Munaqosah::all()->keyBy('pendaftaran_siswa_id');

        $santri = PendaftaranSiswa::with('kelasMaster:id,nama')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')->get()
            ->map(function (PendaftaranSiswa $s) use ($data) {
                /** @var Munaqosah|null $m */
                $m = $data[$s->id] ?? null;

                return DataCatatan::siswa($s) + [
                    'id'        => $s->id,
                    'status'    => $m?->status ?? Munaqosah::MUSYRIFIN,
                    'terisi'    => $m ? collect($m->isian ?? [])->flatten(1)->filter(fn ($b) => self::adaNilai($b['nilai'] ?? null))->count() : 0,
                    'isi'       => self::jumlahIsi($m),   // { ilmu: n, suluk: n, mandub: n } untuk tanda ✓ di tombol kartu
                    'keputusan' => $m?->keputusan,
                    'dikirim'   => $m?->dikirim_pada?->format('Y-m-d H:i'),
                    'diputuskan' => $m?->diputuskan_pada?->format('Y-m-d H:i'),
                    'url'       => route('admin.munaqosah.show', $s),
                ];
            })->values();

        return view('admin.akademik.munaqosah.index', [
            'tab'    => $tab,
            'santri' => $santri,
            'kolom'  => MunaqosahKolom::perBagian(),
            'bagian' => collect(MunaqosahKolom::BAGIAN)->map(fn ($l) => __($l))->all(),
            'jenis'  => collect(MunaqosahKolom::JENIS)->map(fn ($l) => __($l))->all(),
        ]);
    }

    /**
     * PUT /admin/munaqosah/kolom/{bagian}: simpan SELURUH daftar kolom satu bagian sekaligus
     * (urutan = urutan kiriman; kolom yang tidak dikirim lagi = dihapus).
     */
    public function simpanKolom(Request $request, string $bagian): JsonResponse
    {
        abort_unless(array_key_exists($bagian, MunaqosahKolom::BAGIAN), 404);

        $data = $request->validate([
            'kolom'             => ['present', 'array', 'max:100'],
            'kolom.*.id'        => ['nullable', 'integer'],
            'kolom.*.label'     => ['required', 'string', 'max:255'],
            'kolom.*.jenis'     => ['required', Rule::in(array_keys(MunaqosahKolom::JENIS))],
            'kolom.*.pilihan'   => ['nullable', 'array', 'max:100'],
            'kolom.*.pilihan.*' => ['nullable', 'string', 'max:255'],
        ], [], [
            'kolom.*.label' => __('nama label'),
            'kolom.*.jenis' => __('jenis isian'),
        ]);

        foreach ($data['kolom'] as $i => $k) {
            $pilihan = collect($k['pilihan'] ?? [])->map(fn ($p) => trim((string) $p))->filter()->unique()->values()->all();
            if (in_array($k['jenis'], MunaqosahKolom::BERPILIHAN, true) && ! $pilihan) {
                throw ValidationException::withMessages([
                    "kolom.$i.pilihan" => __('Kolom ":label" perlu minimal satu pilihan.', ['label' => $k['label']]),
                ]);
            }
            $data['kolom'][$i]['pilihan'] = in_array($k['jenis'], MunaqosahKolom::BERPILIHAN, true) ? $pilihan : null;
        }

        DB::transaction(function () use ($data, $bagian) {
            $lama = MunaqosahKolom::where('bagian', $bagian)->get()->keyBy('id');
            $dipakai = [];
            foreach ($data['kolom'] as $urutan => $k) {
                $kolom = isset($k['id']) && $lama->has($k['id']) ? $lama[$k['id']] : new MunaqosahKolom(['bagian' => $bagian]);
                $kolom->fill([
                    'label'   => trim($k['label']),
                    'jenis'   => $k['jenis'],
                    'pilihan' => $k['pilihan'],
                    'urutan'  => $urutan,
                ])->save();
                $dipakai[] = $kolom->id;
            }
            MunaqosahKolom::where('bagian', $bagian)->whereNotIn('id', $dipakai)->delete();
        });

        return response()->json([
            'pesan' => __('Pengaturan :bagian disimpan.', ['bagian' => __(MunaqosahKolom::BAGIAN[$bagian])]),
            'kolom' => MunaqosahKolom::perBagian()[$bagian],
        ]);
    }

    /** GET /admin/munaqosah/{santri}: halaman detail (dibuka di tab baru) */
    public function show(PendaftaranSiswa $santri)
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);

        $m = Munaqosah::with(['penyimpan:id,name', 'pemutus:id,name'])->firstWhere('pendaftaran_siswa_id', $santri->id);
        NamaArab::untukUser(collect([$m?->disimpan_oleh, $m?->diputuskan_oleh]));

        return view('admin.akademik.munaqosah.detail', $this->dataPanel($santri) + [
            'siswa'  => $santri,
            'kolom'  => MunaqosahKolom::perBagian(),
            'bagian' => collect(MunaqosahKolom::BAGIAN)->map(fn ($l) => __($l))->all(),
            'data'   => [
                'status'     => $m?->status ?? Munaqosah::MUSYRIFIN,
                'isian'      => $m?->isian ?? [],
                'keputusan'  => $m?->keputusan,
                'catatan'    => $m?->catatan_lajnah,
                'disimpan'   => $m?->penyimpan?->name,
                'disimpan_arab' => NamaArab::user($m?->disimpan_oleh),
                'dikirim'    => $m?->dikirim_pada?->format('Y-m-d H:i'),
                'diputuskan' => $m?->pemutus?->name,
                'diputuskan_arab' => NamaArab::user($m?->diputuskan_oleh),
                'diputuskan_pada' => $m?->diputuskan_pada?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /** GET /admin/munaqosah/{santri}/data: isi modal panel di tab Musyrifin (JSON) */
    public function data(PendaftaranSiswa $santri): JsonResponse
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);
        $m = Munaqosah::firstWhere('pendaftaran_siswa_id', $santri->id);

        return response()->json($this->dataPanel($santri) + [
            'kolom'     => MunaqosahKolom::perBagian(),
            'tersimpan' => $m?->isian ?? (object) [],
            'status'    => $m?->status ?? Munaqosah::MUSYRIFIN,
        ]);
    }

    /** GET /admin/munaqosah/{santri}/profil: panel Detail Santri untuk modal (potongan HTML) */
    public function profil(PendaftaranSiswa $santri)
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);

        return view('admin.akademik.munaqosah._profil', ['p' => $santri]);
    }

    /** POST /admin/munaqosah/{santri}/bagian/{bagian}: Simpan satu panel (ilmu | suluk | mandub). Santri tetap di Musyrifin. */
    public function simpanBagian(Request $request, PendaftaranSiswa $santri, string $bagian): JsonResponse
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);
        abort_unless(array_key_exists($bagian, MunaqosahKolom::BAGIAN), 404);
        $m = Munaqosah::firstOrNew(['pendaftaran_siswa_id' => $santri->id]);
        if ($m->status === Munaqosah::LAJNAH) {
            throw ValidationException::withMessages(['isian' => __('Santri ini sudah di tab Lajnah. Kembalikan ke Musyrifin dulu untuk mengubah isian.')]);
        }

        $request->validate(['isian' => ['nullable', 'array']]);
        $isian = $m->isian ?? [];
        $isian[$bagian] = $this->isianBagian(MunaqosahKolom::perBagian()[$bagian], $request->input('isian', []), $isian[$bagian] ?? []);

        $m->fill([
            'isian'         => $isian,
            'status'        => Munaqosah::MUSYRIFIN,
            'disimpan_oleh' => $request->user()->id,
        ])->save();

        return response()->json([
            'pesan'     => __(':bagian :nama disimpan.', ['bagian' => __(MunaqosahKolom::BAGIAN[$bagian]), 'nama' => $santri->nama_lengkap]),
            'tersimpan' => $isian,
            'isi'       => self::jumlahIsi($m),
        ]);
    }

    /** POST /admin/munaqosah/{santri}/kirim: tombol "Kirim ke Lajnah" di kartu (isian yang sudah disimpan per panel) */
    public function kirim(Request $request, PendaftaranSiswa $santri): JsonResponse
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);
        $m = Munaqosah::firstOrNew(['pendaftaran_siswa_id' => $santri->id]);
        if ($m->status === Munaqosah::LAJNAH) {
            throw ValidationException::withMessages(['isian' => __('Santri ini sudah di tab Lajnah.')]);
        }

        $m->fill([
            'isian'         => $m->isian ?? [],
            'status'        => Munaqosah::LAJNAH,
            'disimpan_oleh' => $request->user()->id,
            'dikirim_pada'  => Munaqosah::sekarang(),
        ])->save();

        return response()->json([
            'pesan'   => __(':nama dikirim ke Lajnah Qobul Darul Musthofa.', ['nama' => $santri->nama_lengkap]),
            'dikirim' => $m->dikirim_pada?->format('Y-m-d H:i'),
        ]);
    }

    /** POST /admin/munaqosah/{santri}: Simpan isian Musyrifin -> pindah ke tab Lajnah */
    public function simpan(Request $request, PendaftaranSiswa $santri): JsonResponse
    {
        abort_unless($santri->status === PendaftaranSiswa::DITERIMA, 404);
        $m = Munaqosah::firstOrNew(['pendaftaran_siswa_id' => $santri->id]);
        if ($m->status === Munaqosah::LAJNAH) {
            throw ValidationException::withMessages(['isian' => __('Santri ini sudah di tab Lajnah. Kembalikan ke Musyrifin dulu untuk mengubah isian.')]);
        }

        $request->validate(['isian' => ['nullable', 'array']]);
        $kiriman = $request->input('isian', []);
        $lama = $m->isian ?? [];
        $isian = [];

        foreach (MunaqosahKolom::perBagian() as $bagian => $daftar) {
            $isian[$bagian] = $this->isianBagian($daftar, $kiriman[$bagian] ?? [], $lama[$bagian] ?? []);
        }

        $m->fill([
            'isian'         => $isian,
            'status'        => Munaqosah::LAJNAH,
            'disimpan_oleh' => $request->user()->id,
            'dikirim_pada'  => Munaqosah::sekarang(),
        ])->save();

        session()->flash('success', __(':nama disimpan dan dikirim ke Lajnah Qobul Darul Musthofa.', ['nama' => $santri->nama_lengkap]));

        return response()->json(['ok' => true]);
    }

    /** POST /admin/munaqosah/{santri}/kembalikan: Lajnah mengembalikan ke Musyrifin (hanya bila belum diputuskan) */
    public function kembalikan(PendaftaranSiswa $santri)
    {
        $m = Munaqosah::where('pendaftaran_siswa_id', $santri->id)->firstOrFail();
        if ($m->keputusan) {
            return back()->with('error', __('Sudah ada keputusan Lajnah. Hapus keputusannya dulu bila ingin dikembalikan.'));
        }
        $m->update(['status' => Munaqosah::MUSYRIFIN]);

        return back()->with('success', __(':nama dikembalikan ke Musyrifin Dauroh.', ['nama' => $santri->nama_lengkap]));
    }

    /** POST /admin/munaqosah/{santri}/keputusan: keputusan Lajnah (lulus | tidak | batal) + catatan khusus */
    public function keputusan(Request $request, PendaftaranSiswa $santri)
    {
        $m = Munaqosah::where('pendaftaran_siswa_id', $santri->id)->where('status', Munaqosah::LAJNAH)->firstOrFail();
        $data = $request->validate([
            'keputusan'      => ['required', Rule::in([Munaqosah::LULUS, Munaqosah::TIDAK, 'batal', 'catatan'])],
            'catatan_lajnah' => ['nullable', 'string', 'max:10000'],
        ]);

        $m->catatan_lajnah = $data['catatan_lajnah'] ?? null;
        if ($data['keputusan'] === 'batal') {
            $m->fill(['keputusan' => null, 'diputuskan_oleh' => null, 'diputuskan_pada' => null]);
            $pesan = __('Keputusan untuk :nama dibatalkan.', ['nama' => $santri->nama_lengkap]);
        } elseif ($data['keputusan'] === 'catatan') {
            $pesan = __('Catatan Lajnah disimpan.');
        } else {
            $m->fill([
                'keputusan'       => $data['keputusan'],
                'diputuskan_oleh' => $request->user()->id,
                'diputuskan_pada' => Munaqosah::sekarang(),
            ]);
            $pesan = __(':nama diputuskan: :hasil.', ['nama' => $santri->nama_lengkap, 'hasil' => __(Munaqosah::KEPUTUSAN[$data['keputusan']])]);
        }
        $m->save();

        return back()->with('success', $pesan);
    }

    /** Nilai Harian, Ujian Lisan & Ujian satu santri (rincian + rata-rata persen), sama dengan Laporan Nilai */
    private function nilaiPelajaran(PendaftaranSiswa $s): array
    {
        $persen = fn ($n, $maks) => $maks ? round($n / $maks * 100, 1) : 0;
        $rata = fn ($list) => $list->count() ? round($list->avg(), 1) : null;

        $h = NilaiHarian::with(['kitab:id,nama', 'bulanAjaran:id,nama'])->where('pendaftaran_siswa_id', $s->id)->orderBy('tanggal')->get()
            ->map(fn (NilaiHarian $n) => [
                'kitab' => $n->kitab?->nama ?? '-',
                'bulan' => $n->bulanAjaran?->nama,
                'nilai' => collect(NilaiHarian::ASPEK)->keys()->map(fn ($k) => (int) $n->{$k})->all(),
                'total' => (int) $n->total,
            ])->values();
        $l = NilaiUjianLisan::where('pendaftaran_siswa_id', $s->id)->orderBy('tanggal')->get()
            ->map(fn (NilaiUjianLisan $n) => [
                'nama'    => collect([$n->fan, $n->kitab])->filter()->implode(' · ') ?: '-',
                'tanggal' => $n->tanggal->toDateString(),
                'nilai'   => (int) $n->total,
                'maks'    => (int) $n->total_maks,
                'persen'  => $persen($n->total, $n->total_maks),
                'catatan' => $n->catatan,
            ])->values();
        $u = NilaiUjian::where('pendaftaran_siswa_id', $s->id)->orderBy('tanggal')->get()
            ->map(fn (NilaiUjian $n) => [
                'nama'    => collect([$n->nama_ujian, $n->fan, $n->kitab])->filter()->unique()->implode(' · ') ?: '-',
                'tanggal' => $n->tanggal->toDateString(),
                'nilai'   => (int) $n->nilai,
                'maks'    => (int) $n->nilai_maks,
                'persen'  => $persen($n->nilai, $n->nilai_maks),
                'catatan' => null,
            ])->values();

        $bagian = ['harian' => $rata($h->pluck('total')), 'lisan' => $rata($l->pluck('persen')), 'ujian' => $rata($u->pluck('persen'))];
        $ada = array_filter($bagian, fn ($v) => $v !== null);

        return [
            'persen'  => $bagian,
            'akhir'   => $ada ? round(array_sum($ada) / count($ada), 1) : null,
            'rincian' => ['harian' => $h, 'lisan' => $l, 'ujian' => $u],
        ];
    }

    /** Data panel 3, 4, 6, 7 (nilai pelajaran, dakwah, absensi, catatan) satu santri: dipakai halaman detail & modal */
    private function dataPanel(PendaftaranSiswa $santri): array
    {
        return [
            'absensi' => $this->absensiSantri($santri),
            'nilai'   => $this->nilaiPelajaran($santri),
            'aspek'   => NilaiHarian::daftarAspek(),
            'dakwah'  => NilaiDakwah::lengkap()->where('pendaftaran_siswa_id', $santri->id)
                ->orderByDesc('tanggal')->orderByDesc('id')->get()
                ->tap(fn ($n) => NamaArab::untukUser($n->pluck('dinilai_oleh')))
                ->map(fn (NilaiDakwah $n) => $n->baris())->values(),
            'jenisDakwah' => collect(NilaiDakwah::JENIS)->map(fn ($l) => __($l))->all(),
            'catatan' => DataCatatan::semuaBaris()->where('siswa_id', $santri->id)->values(),
        ];
    }

    /** Panel 6 Detail Absensi: semua absensi kelas & kegiatan santri ini (terbaru dulu), hanya dibaca */
    private function absensiSantri(PendaftaranSiswa $santri): array
    {
        return AbsensiDetail::where('pendaftaran_siswa_id', $santri->id)
            ->with(['absensi.kelas', 'absensi.pelajaran', 'absensi.kitab', 'absensi.kegiatan', 'absensi.waktuPelajaran', 'absensi.pengabsen:id,name', 'pencatat:id,name'])
            ->get()
            ->filter(fn ($d) => $d->absensi && $d->absensi->tanggal)
            ->sortByDesc(fn ($d) => $d->absensi->tanggal->format('Y-m-d') . ' ' . ($d->absensi->jam_mulai ?? $d->absensi->waktuPelajaran?->jam_mulai ?? '') . sprintf('%010d', $d->id))
            ->map(function (AbsensiDetail $d) {
                $a = $d->absensi;
                $kegiatan = $a->jenis === Absensi::KEGIATAN;
                $jam = $d->waktu ?: ($kegiatan ? $a->jam_mulai : $a->waktuPelajaran?->jam_mulai);

                return [
                    'id'      => $d->id,
                    'status'  => $d->status,
                    'jenis'   => $kegiatan ? __('Absensi kegiatan') : __('Absensi kelas'),
                    'judul'   => $a->judul,
                    'tanggal' => $a->tanggal->toDateString(),
                    'hari'    => __($a->tanggal->locale('id')->translatedFormat('l')),
                    'jam'     => $jam ? substr((string) $jam, 0, 5) : null,
                    'catatan' => $d->catatan,
                    'oleh'    => $d->pencatat?->name ?? $a->pengabsen?->name,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Susun isian satu bagian dari kiriman { kolom_id: nilai } sesuai kolom sekarang.
     * Isian lama dari kolom yang sudah dihapus tetap disimpan (tidak hilang), diberi tanda "dihapus".
     */
    private function isianBagian(array $daftar, $kiriman, array $lama): array
    {
        $kiriman = is_array($kiriman) ? $kiriman : [];
        $baru = [];
        foreach ($daftar as $k) {
            $baru[] = [
                'kolom_id' => $k['id'],
                'label'    => $k['label'],
                'jenis'    => $k['jenis'],
                'nilai'    => $this->cekNilai($k, $kiriman[$k['id']] ?? null),
            ];
        }
        $ids = collect($daftar)->pluck('id')->all();
        foreach ($lama as $b) {
            if (! in_array($b['kolom_id'] ?? null, $ids, true) && self::adaNilai($b['nilai'] ?? null)) {
                $baru[] = ['dihapus' => true] + $b;
            }
        }

        return $baru;
    }

    /** Jumlah isian terisi per bagian: { ilmu: n, suluk: n, mandub: n } */
    private static function jumlahIsi(?Munaqosah $m): array
    {
        return collect(MunaqosahKolom::BAGIAN)->keys()->mapWithKeys(fn ($b) => [
            $b => collect(($m?->isian ?? [])[$b] ?? [])->filter(fn ($x) => empty($x['dihapus']) && self::adaNilai($x['nilai'] ?? null))->count(),
        ])->all();
    }

    /** Cek satu isian sesuai jenis kolomnya; pilihan yang tidak ada di daftar dibuang */
    private function cekNilai(array $k, $nilai)
    {
        return match ($k['jenis']) {
            'centang'  => collect(is_array($nilai) ? $nilai : [])->map(fn ($v) => (string) $v)
                ->filter(fn ($v) => in_array($v, $k['pilihan'], true))->unique()->values()->all(),
            'dropdown' => in_array((string) $nilai, $k['pilihan'], true) ? (string) $nilai : null,
            default    => is_scalar($nilai) && trim((string) $nilai) !== ''
                ? mb_substr(trim((string) $nilai), 0, $k['jenis'] === 'teks' ? 1000 : 10000)
                : null,
        };
    }

    private static function adaNilai($v): bool
    {
        return is_array($v) ? count($v) > 0 : ($v !== null && $v !== '');
    }
}
