<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KasKategori;
use App\Models\KasTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Keuangan > Buku Kas: catat pemasukan & pengeluaran kas harian (data sesi aktif).
 * Filter lewat URL (start_date, end_date, tipe, kategori); tampilan & pop-up diatur Vue
 * di resources/views/admin/keuangan/buku-kas.blade.php.
 */
class BukuKasController extends Controller
{
    public function index(Request $request)
    {
        $hariIni = now('Asia/Jakarta')->startOfDay();
        $mulai = $this->tanggal($request->start_date) ?? $hariIni->copy()->subDays(29);
        $sampai = $this->tanggal($request->end_date) ?? $hariIni->copy();
        if ($mulai->gt($sampai)) {
            [$mulai, $sampai] = [$sampai, $mulai];
        }
        $tipe = array_key_exists($request->tipe, KasTransaksi::TIPE) ? $request->tipe : null;
        $kategoriId = $request->integer('kategori') ?: null;

        $query = KasTransaksi::query()
            ->whereBetween('tanggal', [$mulai->toDateString(), $sampai->toDateString()])
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->when($kategoriId, fn ($q) => $q->where('kas_kategori_id', $kategoriId));

        $periode = KasTransaksi::total($query);

        $transaksi = $query->clone()->with('kategori')
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('admin.keuangan.buku-kas', [
            'transaksi' => $transaksi,
            'baris'     => $transaksi->getCollection()->map(fn (KasTransaksi $t) => $this->dataBaris($t))->values(),
            'kategori'  => KasKategori::withCount('transaksi')->orderBy('tipe')->orderBy('nama')->get()
                ->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama, 'tipe' => $k->tipe, 'jumlah' => $k->transaksi_count]),
            'saldo'     => KasTransaksi::saldo(),
            'periode'   => $periode,
            'filter'    => [
                'start_date' => $mulai->toDateString(),
                'end_date'   => $sampai->toDateString(),
                'tipe'       => $tipe ?? '',
                'kategori'   => $kategoriId ? (string) $kategoriId : '',
            ],
            'hariIni'   => $hariIni->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasiTransaksi($request);
        $data['user_id'] = Auth::id();
        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('buku-kas', 'public');
        }

        $t = KasTransaksi::create($data);

        return $this->kembali($request)->with('success', 'Transaksi "' . $t->deskripsi . '" dicatat.');
    }

    public function update(Request $request, KasTransaksi $transaksi)
    {
        $data = $this->validasiTransaksi($request);

        // Bukti: ganti dengan file baru, atau hapus bila dicentang
        if ($request->hasFile('bukti') || $request->boolean('hapus_bukti')) {
            if ($transaksi->bukti) {
                Storage::disk('public')->delete($transaksi->bukti);
            }
            $data['bukti'] = $request->hasFile('bukti') ? $request->file('bukti')->store('buku-kas', 'public') : null;
        }

        $transaksi->update($data);

        return $this->kembali($request)->with('success', 'Transaksi "' . $transaksi->deskripsi . '" diperbarui.');
    }

    public function destroy(Request $request, KasTransaksi $transaksi)
    {
        if ($transaksi->bukti) {
            Storage::disk('public')->delete($transaksi->bukti);
        }
        $transaksi->delete();

        return $this->kembali($request)->with('success', 'Transaksi dihapus.');
    }

    // ===== Kelola Kategori =====

    public function simpanKategori(Request $request)
    {
        $k = KasKategori::create($this->validasiKategori($request));

        return $this->kembali($request)->with('success', "Kategori {$k->nama} ditambahkan.")->with('buka', 'kategori');
    }

    public function ubahKategori(Request $request, KasKategori $kategori)
    {
        $data = $this->validasiKategori($request, $kategori);

        if ($data['tipe'] !== $kategori->tipe && $kategori->transaksi()->exists()) {
            return $this->kembali($request)->with('error', "Tipe kategori {$kategori->nama} tidak bisa diganti karena sudah dipakai transaksi.")->with('buka', 'kategori');
        }

        $kategori->update($data);

        return $this->kembali($request)->with('success', "Kategori {$kategori->nama} disimpan.")->with('buka', 'kategori');
    }

    public function hapusKategori(Request $request, KasKategori $kategori)
    {
        if ($n = $kategori->transaksi()->count()) {
            return $this->kembali($request)->with('error', "Kategori {$kategori->nama} masih dipakai {$n} transaksi.")->with('buka', 'kategori');
        }

        $kategori->delete();

        return $this->kembali($request)->with('success', "Kategori {$kategori->nama} dihapus.")->with('buka', 'kategori');
    }

    // ===== Bantuan =====

    private function validasiTransaksi(Request $request): array
    {
        $request->merge(['jumlah' => preg_replace('/\D/', '', (string) $request->jumlah)]); // "1.000.000" -> 1000000

        $data = $request->validate([
            'tanggal'         => ['required', 'date'],
            'tipe'            => ['required', Rule::in(array_keys(KasTransaksi::TIPE))],
            'kas_kategori_id' => ['nullable', Rule::exists('kas_kategori', 'id')->where('tipe', $request->tipe)],
            'deskripsi'       => ['required', 'string', 'max:255'],
            'pihak'           => ['nullable', 'string', 'max:150'],
            'jumlah'          => ['required', 'integer', 'min:1', 'max:999999999999'],
            'bukti'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ], [
            'kas_kategori_id.exists' => 'Kategori tidak sesuai dengan tipe transaksi.',
            'jumlah.min'             => 'Jumlah harus lebih dari 0.',
            'bukti.mimes'            => 'Bukti harus berupa gambar (JPG/PNG/WEBP) atau PDF.',
            'bukti.max'              => 'Ukuran bukti maksimal 4 MB.',
        ]);
        unset($data['bukti']);

        return $data;
    }

    private function validasiKategori(Request $request, ?KasKategori $kategori = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:60',
                Rule::unique('kas_kategori', 'nama')->where('tipe', $request->tipe)->ignore($kategori?->id)],
            'tipe' => ['required', Rule::in(array_keys(KasTransaksi::TIPE))],
        ], ['nama.unique' => 'Kategori dengan nama & tipe ini sudah ada.']);
    }

    /** Kembali ke halaman Buku Kas dengan filter yang sama seperti sebelumnya */
    private function kembali(Request $request)
    {
        $filter = array_filter((array) $request->input('_filter', []), fn ($v) => is_string($v) && $v !== '');

        return redirect()->route('admin.buku-kas.index', array_intersect_key($filter, array_flip(['start_date', 'end_date', 'tipe', 'kategori', 'page'])));
    }

    private function tanggal(?string $teks): ?Carbon
    {
        try {
            return $teks ? Carbon::createFromFormat('Y-m-d', $teks, 'Asia/Jakarta')->startOfDay() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function dataBaris(KasTransaksi $t): array
    {
        $bukti = $t->bukti ? Storage::disk('public')->url($t->bukti) : null;

        return [
            'id'              => $t->id,
            'tanggal'         => $t->tanggal->toDateString(),
            'tanggal_label'   => $t->tanggal->locale('id')->translatedFormat('d F Y'),
            'tipe'            => $t->tipe,
            'kas_kategori_id' => $t->kas_kategori_id,
            'kategori'        => $t->kategori?->nama,
            'deskripsi'       => $t->deskripsi,
            'pihak'           => $t->pihak,
            'jumlah'          => $t->jumlah,
            'bukti'           => $bukti,
            'bukti_gambar'    => $bukti && ! str_ends_with(strtolower($t->bukti), '.pdf'),
        ];
    }
}
