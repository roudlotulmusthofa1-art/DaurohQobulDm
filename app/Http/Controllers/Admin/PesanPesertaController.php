<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSiswa;
use App\Models\PesanPeserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin > PSB > Pesan Peserta
 * - Kotak masuk: pesan dari peserta (menu "Pesan untuk Admin" di dasbor siswa)
 * - Kirim pesan khusus ke satu peserta atau banyak peserta sekaligus (muncul di "Pesan dari Admin")
 * - Percakapan per peserta + balas
 */
class PesanPesertaController extends Controller
{
    /** Pilihan penerima pesan massal */
    public const TUJUAN = [
        'satu' => 'Satu peserta',
        'diterima' => 'Semua peserta diterima',
        'menunggu' => 'Semua yang masih menunggu',
        'ditolak' => 'Semua yang ditolak',
        'semua' => 'Semua pendaftar',
    ];

    public function index(Request $request): View
    {
        $saring = in_array($request->query('saring'), ['belum', 'semua'], true) ? $request->query('saring') : 'semua';

        // Satu baris per peserta: pesan terakhir + jumlah pesan peserta yang belum dibaca
        $ringkas = PesanPeserta::query()
            ->selectRaw('pendaftaran_siswa_id, MAX(id) AS terakhir_id, SUM(CASE WHEN dari = ? AND dibaca_at IS NULL THEN 1 ELSE 0 END) AS belum, SUM(CASE WHEN dari = ? THEN 1 ELSE 0 END) AS dari_peserta', [PesanPeserta::DARI_PESERTA, PesanPeserta::DARI_PESERTA])
            ->groupBy('pendaftaran_siswa_id')
            ->when($saring === 'belum', fn ($q) => $q->havingRaw('belum > 0'))
            ->when($request->q, fn ($q, $kata) => $q->whereIn('pendaftaran_siswa_id', PendaftaranSiswa::where('nama_lengkap', 'like', "%{$kata}%")->orWhere('nomor_pendaftaran', 'like', "%{$kata}%")->select('id')))
            ->orderByRaw('belum > 0 DESC')->orderByDesc('terakhir_id')
            ->paginate(20)->withQueryString();

        $terakhir = PesanPeserta::whereIn('id', $ringkas->pluck('terakhir_id'))->get()->keyBy('id');
        $peserta = PendaftaranSiswa::whereIn('id', $ringkas->pluck('pendaftaran_siswa_id'))->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'status', 'berkas_foto'])->keyBy('id');

        return view('admin.pesan-peserta.index', [
            'ringkas' => $ringkas,
            'terakhir' => $terakhir,
            'peserta' => $peserta,
            'saring' => $saring,
            'jumlahBelum' => PesanPeserta::dariPeserta()->belumDibaca()->count(),
            'pilihanPeserta' => PendaftaranSiswa::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nomor_pendaftaran', 'status']),
            'tujuanList' => self::TUJUAN,
            'jumlahTujuan' => PendaftaranSiswa::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    /** Percakapan dengan satu peserta. Pesan dari peserta ditandai sudah dibaca admin. */
    public function show(PendaftaranSiswa $peserta): View
    {
        $peserta->pesan()->dariPeserta()->belumDibaca()->update(['dibaca_at' => now()]);

        return view('admin.pesan-peserta.show', [
            'peserta' => $peserta->load(['kelasMaster', 'kamarMaster']),
            'pesan' => $peserta->pesan()->with(['pengirim:id,name', 'dibalas'])->orderBy('id')->get(),
        ]);
    }

    public function balas(Request $request, PendaftaranSiswa $peserta): RedirectResponse
    {
        $data = $request->validate([
            'isi' => ['required', 'string', 'max:3000'],
            'balasan_untuk_id' => ['nullable', 'integer', Rule::exists('pesan_peserta', 'id')->where('pendaftaran_siswa_id', $peserta->id)],
        ], ['isi.required' => 'Tulis balasan terlebih dahulu.']);

        $balasUntuk = $data['balasan_untuk_id'] ?? $peserta->pesan()->dariPeserta()->latest('id')->value('id');
        $peserta->pesan()->create([
            'dari' => PesanPeserta::DARI_ADMIN,
            'user_id' => $request->user()->id,
            'balasan_untuk_id' => $balasUntuk,
            'isi' => $data['isi'],
        ]);

        // Dibalas dari kotak pesan di Dashboard -> kembali ke Dashboard dengan percakapan yang sama
        if ($request->input('kembali') === 'dashboard') {
            return redirect()->route('admin.dashboard', ['pesan' => $peserta->id])->withFragment('pesan-peserta')
                ->with('success', 'Balasan terkirim ke dasbor peserta.');
        }

        return redirect()->route('admin.pesan-peserta.show', $peserta)->with('success', 'Balasan terkirim ke dasbor peserta.');
    }

    /** Pesan baru dari admin: ke satu peserta atau banyak sekaligus (disalin per peserta) */
    public function kirim(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tujuan' => ['required', Rule::in(array_keys(self::TUJUAN))],
            'pendaftaran_siswa_id' => ['required_if:tujuan,satu', 'nullable', 'integer', Rule::exists('pendaftaran_siswas', 'id')],
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:3000'],
        ], [
            'pendaftaran_siswa_id.required_if' => 'Pilih peserta penerima.',
            'judul.required' => 'Judul pesan wajib diisi.',
            'isi.required' => 'Isi pesan wajib diisi.',
        ]);

        $ids = match ($data['tujuan']) {
            'satu' => collect([(int) $data['pendaftaran_siswa_id']]),
            'semua' => PendaftaranSiswa::pluck('id'),
            default => PendaftaranSiswa::where('status', $data['tujuan'])->pluck('id'),
        };

        if ($ids->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ada peserta pada pilihan penerima itu.');
        }

        $sekarang = now();
        DB::transaction(function () use ($ids, $data, $request, $sekarang) {
            foreach ($ids->chunk(500) as $potong) {
                PesanPeserta::insert($potong->map(fn ($id) => [
                    'pendaftaran_siswa_id' => $id,
                    'dari' => PesanPeserta::DARI_ADMIN,
                    'user_id' => $request->user()->id,
                    'judul' => $data['judul'],
                    'isi' => $data['isi'],
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ])->all());
            }
        });

        if ($data['tujuan'] === 'satu') {
            return redirect()->route('admin.pesan-peserta.show', $ids->first())->with('success', 'Pesan terkirim ke dasbor peserta.');
        }

        return redirect()->route('admin.pesan-peserta.index')->with('success', "Pesan terkirim ke {$ids->count()} peserta (" . self::TUJUAN[$data['tujuan']] . ').');
    }

    public function hapus(PesanPeserta $pesan): RedirectResponse
    {
        $peserta = $pesan->pendaftaran_siswa_id;
        $pesan->delete();

        return redirect()->route('admin.pesan-peserta.show', $peserta)->with('success', 'Pesan dihapus.');
    }
}
