<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\PitaPengumuman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin > Landing Page > Pita Pengumuman
 * Kelola kabar singkat di paling atas landing page (tambah, ubah, hidup/mati, urutan, hapus).
 * Halaman memakai Vue; semua simpan/hapus lewat JSON.
 */
class PitaPengumumanController extends Controller
{
    public function index(): View
    {
        return view('admin.landing-pengumuman', [
            'daftar' => $this->daftar(),
            'jenis' => PitaPengumuman::semuaJenis(),
            'jenisBawaan' => array_keys(PitaPengumuman::JENIS),
            'tujuan' => PitaPengumuman::TUJUAN,
            'warna' => PitaPengumuman::WARNA,
            'tampilan' => PitaPengumuman::tampilan(),
            'pilihanPosisi' => PitaPengumuman::POSISI,
            'pilihanUkuran' => PitaPengumuman::UKURAN,
        ]);
    }

    /** Tampilan pita (berlaku untuk semua pengumuman): posisi atas/bawah top bar, ukuran penuh/ringkas */
    public function tampilan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'posisi' => ['required', Rule::in(array_keys(PitaPengumuman::POSISI))],
            'ukuran' => ['required', Rule::in(array_keys(PitaPengumuman::UKURAN))],
        ]);
        Pengaturan::simpanPusat('pita_posisi', $data['posisi']);
        Pengaturan::simpanPusat('pita_ukuran', $data['ukuran']);

        return $this->balas('Tampilan pita disimpan.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validasi($request);
        $data['urutan'] = (int) PitaPengumuman::max('urutan') + 1;
        PitaPengumuman::create($data);

        return $this->balas('Pengumuman ditambahkan.');
    }

    public function update(Request $request, PitaPengumuman $pita): JsonResponse
    {
        $pita->update($this->validasi($request, $pita));

        return $this->balas('Pengumuman diperbarui.');
    }

    public function toggle(PitaPengumuman $pita): JsonResponse
    {
        $pita->update(['is_active' => ! $pita->is_active]);

        return $this->balas($pita->is_active ? 'Pengumuman dihidupkan.' : 'Pengumuman dimatikan.');
    }

    public function destroy(PitaPengumuman $pita): JsonResponse
    {
        $pita->delete();

        return $this->balas('Pengumuman dihapus.');
    }

    /** Tambah jenis pengumuman baru (mis. "Ujian", "Libur") */
    public function tambahJenis(Request $request): JsonResponse
    {
        $nama = trim(preg_replace('/\s+/', ' ', (string) $request->input('nama')));
        $request->merge(['nama' => $nama]);
        $request->validate(['nama' => ['required', 'string', 'max:20']], [], ['nama' => 'nama jenis']);

        $semua = PitaPengumuman::semuaJenis();
        $sudahAda = collect($semua)->contains(fn ($n, $k) => mb_strtolower($n) === mb_strtolower($nama) || mb_strtolower($k) === mb_strtolower($nama));
        if ($sudahAda) {
            return response()->json(['message' => "Jenis \"{$nama}\" sudah ada."], 422);
        }

        PitaPengumuman::simpanJenisTambahan([...PitaPengumuman::jenisTambahan(), $nama]);

        return response()->json(['pesan' => "Jenis \"{$nama}\" ditambahkan.", 'jenis' => PitaPengumuman::semuaJenis(), 'nama' => $nama]);
    }

    /** Hapus jenis tambahan. Pengumuman yang sudah memakainya tetap menampilkan nama jenis itu. */
    public function hapusJenis(Request $request): JsonResponse
    {
        $nama = (string) $request->input('nama');
        if (array_key_exists($nama, PitaPengumuman::JENIS)) {
            return response()->json(['message' => 'Jenis bawaan tidak bisa dihapus.'], 422);
        }
        $dipakai = PitaPengumuman::where('jenis', $nama)->count();
        PitaPengumuman::simpanJenisTambahan(array_diff(PitaPengumuman::jenisTambahan(), [$nama]));

        return response()->json([
            'pesan' => "Jenis \"{$nama}\" dihapus dari pilihan." . ($dipakai ? " ({$dipakai} pengumuman lama tetap memakai nama ini.)" : ''),
            'jenis' => PitaPengumuman::semuaJenis(),
        ]);
    }

    /** Simpan urutan baru: ids = [3, 1, 2] */
    public function urutan(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        foreach (array_values($ids) as $i => $id) {
            PitaPengumuman::whereKey($id)->update(['urutan' => $i + 1]);
        }

        return $this->balas('Urutan disimpan.');
    }

    private function validasi(Request $request, ?PitaPengumuman $pita = null): array
    {
        $data = $request->validate([
            // jenis yang sudah dihapus dari pilihan tetap boleh dipakai pengumuman lamanya
            'jenis' => ['required', Rule::in([...array_keys(PitaPengumuman::semuaJenis()), ...($pita ? [$pita->jenis] : [])])],
            'tujuan' => ['required', Rule::in(array_keys(PitaPengumuman::TUJUAN))],
            'judul' => ['required', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'mulai_lokal' => ['required', 'date'],
            'selesai_lokal' => ['required', 'date', 'after:mulai_lokal'],
            'tampil_sejak_lokal' => ['nullable', 'date', 'before_or_equal:mulai_lokal'],
            'hitung_mundur' => ['boolean'],
            'tombol_teks' => ['nullable', 'string', 'max:40', 'required_with:tombol_url'],
            // hanya alamat web biasa (https://..., /halaman, #bagian) — bukan javascript: dsb.
            'tombol_url' => ['nullable', 'string', 'max:255', 'required_with:tombol_teks', 'regex:/^(https?:\/\/|\/|#)/i'],
            'warna' => ['required', Rule::in(PitaPengumuman::WARNA)],
            'warna_hex' => ['nullable', 'required_if:warna,sendiri', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['boolean'],
        ], [
            'warna_hex.required_if' => 'Pilih warna sendiri dulu.',
            'warna_hex.regex' => 'Kode warna tidak valid (contoh: #0f766e).',
            'selesai_lokal.after' => 'Waktu selesai harus setelah waktu mulai.',
            'tampil_sejak_lokal.before_or_equal' => '"Tampil sejak" tidak boleh setelah waktu mulai.',
            'tombol_url.regex' => 'Alamat tombol harus diawali https://, / atau #.',
            'tombol_teks.required_with' => 'Isi juga tulisan tombolnya.',
            'tombol_url.required_with' => 'Isi juga alamat tujuan tombolnya.',
        ], [
            'judul' => 'judul',
            'mulai_lokal' => 'waktu mulai',
            'selesai_lokal' => 'waktu selesai',
            'tampil_sejak_lokal' => 'tampil sejak',
        ]);

        // Isian form dalam WIB -> simpan UTC
        $utc = fn ($v) => $v ? Carbon::parse($v, PitaPengumuman::ZONA)->utc() : null;

        return [
            'jenis' => $data['jenis'],
            'tujuan' => $data['tujuan'],
            'judul' => $data['judul'],
            'keterangan' => $data['keterangan'] ?? null,
            'mulai_at' => $utc($data['mulai_lokal']),
            'selesai_at' => $utc($data['selesai_lokal']),
            'tampil_sejak' => $utc($data['tampil_sejak_lokal'] ?? null),
            'hitung_mundur' => (bool) ($data['hitung_mundur'] ?? true),
            'tombol_teks' => $data['tombol_teks'] ?? null,
            'tombol_url' => $data['tombol_url'] ?? null,
            'warna' => $data['warna'],
            'warna_hex' => $data['warna'] === 'sendiri' ? strtolower($data['warna_hex']) : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    private function daftar(): array
    {
        return PitaPengumuman::orderBy('urutan')->orderBy('mulai_at')->get()
            ->map->untukAdmin()->all();
    }

    private function balas(string $pesan): JsonResponse
    {
        return response()->json(['pesan' => $pesan, 'daftar' => $this->daftar()]);
    }
}
