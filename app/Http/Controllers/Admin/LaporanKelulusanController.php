<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Munaqosah;
use App\Models\Pengaturan;
use App\Models\PendaftaranSiswa;
use App\Services\WhatsAppService;
use App\Support\DataCatatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Laporan > Laporan Kelulusan (admin/laporan-kelulusan).
 * Daftar santri yang sudah diputuskan Lajnah di halaman Istimaroh (Munaqosah):
 *   tab "lulus" = Lulus / Diterima, tab "tidak" = Tidak Lulus / Tidak Diterima.
 * Tiap tab punya pesan WhatsApp sendiri (ditulis admin, disimpan untuk semua sesi: Pengaturan pusat
 * "pesan_kelulusan_lulus" / "pesan_kelulusan_tidak"; kosong = contoh bawaan di BAWAAN).
 * Kirim ke nomor WhatsApp santri sendiri (no_hp): otomatis lewat Fonnte, atau lewat WhatsApp admin (wa.me).
 * Setelah dikirim, santri ditandai "sudah diberitahu" (kolom diberitahu_* di tabel munaqosah).
 */
class LaporanKelulusanController extends Controller
{
    /** Contoh pesan bawaan (bisa diganti admin di tiap tab) */
    public const BAWAAN = [
        Munaqosah::LULUS => "Assalamu'alaikum warahmatullahi wabarakatuh.\n\nAlhamdulillah, berdasarkan hasil musyawarah para asatid (Istimaroh), ananda *{nama}* ({nomor}) dinyatakan:\n\n*LULUS / DITERIMA* ✅\n\n{catatan}\n\nSemoga Allah memberkahi ilmu dan langkah ananda. Informasi selanjutnya akan kami sampaikan kemudian.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.\n_{pesantren}_",
        Munaqosah::TIDAK => "Assalamu'alaikum warahmatullahi wabarakatuh.\n\nBerdasarkan hasil musyawarah para asatid (Istimaroh), dengan berat hati kami sampaikan bahwa ananda *{nama}* ({nomor}) dinyatakan:\n\n*BELUM LULUS / TIDAK DITERIMA*\n\n{catatan}\n\nJangan berkecil hati, tetap semangat menuntut ilmu. Semoga Allah memberikan jalan terbaik.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.\n_{pesantren}_",
    ];

    /** Isian yang bisa dipakai di pesan => keterangan */
    public const ISIAN = [
        'nama'      => 'Nama lengkap santri',
        'nomor'     => 'Nomor pendaftaran',
        'kelas'     => 'Kelas santri',
        'catatan'   => 'Catatan Lajnah (beserta judulnya, hilang bila kosong)',
        'pesantren' => 'Nama pesantren (Pengaturan > Umum)',
    ];

    /** GET /admin/laporan-kelulusan?tab=lulus|tidak */
    public function index(Request $request)
    {
        $tab = $request->query('tab') === Munaqosah::TIDAK ? Munaqosah::TIDAK : Munaqosah::LULUS;

        $santri = Munaqosah::with('siswa.kelasMaster:id,nama')
            ->where('status', Munaqosah::LAJNAH)
            ->whereIn('keputusan', [Munaqosah::LULUS, Munaqosah::TIDAK])
            ->get()
            ->filter(fn (Munaqosah $m) => $m->siswa)
            ->sortBy(fn (Munaqosah $m) => mb_strtolower($m->siswa->nama_lengkap))
            ->map(fn (Munaqosah $m) => $this->baris($m))
            ->values();

        return view('admin.laporan-kelulusan.index', [
            'tab'    => $tab,
            'santri' => $santri,
            'pesan'  => collect(self::BAWAAN)->map(fn ($b, $k) => [
                'teks'   => self::teks($k),
                'bawaan' => $b,
                'diubah' => filled(Pengaturan::ambilPusat('pesan_kelulusan_' . $k)),
            ])->all(),
            'isian'  => collect(self::ISIAN)->map(fn ($l) => __($l))->all(),
            'contoh' => ['nama' => 'Muhammad Fulan', 'nomor' => 'PSB20260001', 'kelas' => "I'dadiyah",
                'catatan' => "*Catatan:*\nHarap hadir pada pertemuan wali santri.", 'pesantren' => Pengaturan::profilPesantren()['nama'] ?? ''],
        ]);
    }

    /** PUT /admin/laporan-kelulusan/pesan/{jenis}: simpan pesan satu tab (kosong / sama dengan contoh = pakai bawaan) */
    public function simpanPesan(Request $request, string $jenis): JsonResponse
    {
        abort_unless(array_key_exists($jenis, self::BAWAAN), 404);
        $teks = trim(str_replace("\r\n", "\n", (string) $request->validate(['teks' => ['nullable', 'string', 'max:3000']])['teks']));
        $pakaiBawaan = $teks === '' || $teks === trim(self::BAWAAN[$jenis]);
        Pengaturan::simpanPusat('pesan_kelulusan_' . $jenis, $pakaiBawaan ? null : $teks);

        return response()->json([
            'pesan'  => __('Pesan disimpan.'),
            'teks'   => self::teks($jenis),
            'diubah' => ! $pakaiBawaan,
        ]);
    }

    /** POST /admin/laporan-kelulusan/{santri}/fonnte: kirim otomatis lewat Fonnte (dipanggil satu per satu, juga saat "kirim semua") */
    public function kirimFonnte(PendaftaranSiswa $santri, WhatsAppService $wa): JsonResponse
    {
        $m = $this->munaqosah($santri);
        if (! $wa->kirim($santri->no_hp, self::pesanUntuk($m))) {
            return response()->json(['message' => __('Gagal mengirim ke :nama: :alasan', ['nama' => $santri->nama_lengkap, 'alasan' => $wa->alasan])], 422);
        }

        return response()->json(['pesan' => __('Pesan terkirim ke :nama.', ['nama' => $santri->nama_lengkap])] + $this->tandai($m, 'fonnte'));
    }

    /** POST /admin/laporan-kelulusan/{santri}/wa: tandai sudah diberitahu lewat WhatsApp admin (tautan wa.me dibuka) */
    public function tandaiWa(PendaftaranSiswa $santri): JsonResponse
    {
        return response()->json($this->tandai($this->munaqosah($santri), 'wa'));
    }

    // ------------------------------------------------------------------

    /** Teks pesan satu tab yang dipakai sekarang (ubahan admin, atau contoh bawaan) */
    public static function teks(string $jenis): string
    {
        $isi = Pengaturan::ambilPusat('pesan_kelulusan_' . $jenis);

        return filled($isi) ? $isi : self::BAWAAN[$jenis];
    }

    /** Pesan siap kirim untuk satu santri (juga dipakai dasbor siswa: kotak "Pesan dari Pesantren") */
    public static function pesanUntuk(Munaqosah $m): string
    {
        $s = $m->siswa;
        $data = [
            'nama'      => $s->nama_lengkap,
            'nomor'     => $s->nomor_pendaftaran,
            'kelas'     => $s->kelasMaster?->nama,
            'catatan'   => filled($m->catatan_lajnah) ? "*Catatan:*\n" . trim($m->catatan_lajnah) : '',
            'pesantren' => Pengaturan::profilPesantren()['nama'] ?? '',
        ];
        $hasil = preg_replace_callback('/\{(\w+)\}/', fn ($x) => array_key_exists($x[1], self::ISIAN) ? (string) ($data[$x[1]] ?? '') : $x[0], self::teks($m->keputusan));
        // Isian kosong meninggalkan baris kosong -> paling banyak satu baris kosong
        $hasil = preg_replace("/[ \t]+\n/", "\n", $hasil);

        return trim(preg_replace("/\n{3,}/", "\n\n", $hasil));
    }

    /** Santri ini harus sudah diputuskan Lajnah */
    private function munaqosah(PendaftaranSiswa $santri): Munaqosah
    {
        $m = Munaqosah::with('siswa.kelasMaster:id,nama')->where('pendaftaran_siswa_id', $santri->id)
            ->where('status', Munaqosah::LAJNAH)->whereIn('keputusan', [Munaqosah::LULUS, Munaqosah::TIDAK])->first();
        abort_unless($m, 404, __('Santri ini belum diputuskan Lajnah.'));

        return $m;
    }

    private function tandai(Munaqosah $m, string $lewat): array
    {
        $m->update([
            'diberitahu_pada'      => Munaqosah::sekarang(),
            'diberitahu_lewat'     => $lewat,
            'diberitahu_keputusan' => $m->keputusan,
        ]);

        return ['baris' => $this->baris($m->fresh('siswa.kelasMaster'))];
    }

    /** Data satu santri untuk daftar (Vue) */
    private function baris(Munaqosah $m): array
    {
        $s = $m->siswa;
        $nomorWa = $s->no_hp ? WhatsAppService::nomorWa($s->no_hp) : null;

        return DataCatatan::siswa($s) + [
            'id'         => $s->id,
            'keputusan'  => $m->keputusan,
            'hp'         => $s->no_hp,
            'wa'         => $nomorWa,
            // Isian pesan; pesan untuk tombol "WA saya" & pratinjau dirangkai di halaman dari teks tab yang sedang dipakai
            'catatan'    => filled($m->catatan_lajnah) ? "*Catatan:*\n" . trim($m->catatan_lajnah) : '',
            'diputuskan' => $m->diputuskan_pada?->format('Y-m-d H:i'),
            'sudah'      => $m->sudahDiberitahu(),
            'lewat'      => $m->sudahDiberitahu() ? $m->diberitahu_lewat : null,
            'diberitahu' => $m->sudahDiberitahu() ? $m->diberitahu_pada?->format('Y-m-d H:i') : null,
        ];
    }
}
