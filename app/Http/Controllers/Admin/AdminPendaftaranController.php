<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use App\Http\Requests\UpdatePendaftaranRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminPendaftaranController extends Controller
{
    public function index(Request $request)
    {
        // status tetap dari route (data-diterima); kalau tidak ada, pakai dropdown filter
        $statusTetap = $request->route('status');
        $status = $statusTetap ?: $request->status;

        $pendaftar = PendaftaranSiswa::query()
            ->cari($request->q)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stat = [
            'total'    => PendaftaranSiswa::count(),
            'menunggu' => PendaftaranSiswa::where('status', 'menunggu')->count(),
            'diterima' => PendaftaranSiswa::where('status', 'diterima')->count(),
            'ditolak'  => PendaftaranSiswa::where('status', 'ditolak')->count(),
        ];

        return view('admin.pendaftaran.index', compact('pendaftar', 'stat', 'statusTetap'));
    }

    public function show(PendaftaranSiswa $pendaftaran)
    {
        $pendaftaran->load(['kelasMaster', 'kamarMaster']);

        // Pilihan dari Master Data: hanya yang aktif, ditambah pilihan yang sedang dipakai santri ini
        $kelasList = Kelas::ordered()
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $pendaftaran->kelas_id))
            ->get();

        $kamarList = Kamar::ordered()
            ->withCount('santri')
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $pendaftaran->kamar_id))
            ->get();

        return view('admin.pendaftaran.show', [
            'p' => $pendaftaran,
            'kelasList' => $kelasList,
            'kamarList' => $kamarList,
        ]);
    }

    public function edit(PendaftaranSiswa $pendaftaran)
    {
        return view('admin.pendaftaran.edit', ['p' => $pendaftaran]);
    }

    public function update(UpdatePendaftaranRequest $request, PendaftaranSiswa $pendaftaran)
    {
        // Hanya data yang lolos validasi (tanpa file berkas)
        $data = $request->safe()->except(array_keys(PendaftaranSiswa::BERKAS));

        // Rapikan daftar supaya urutannya 0, 1, 2, ... tanpa lompatan
        $data['riwayat_pondok'] = array_values($data['riwayat_pondok']);
        $data['riwayat_kitab']  = array_values($data['riwayat_kitab']);

        // Berkas hanya diganti kalau ada file baru; file lama dihapus
        foreach (array_keys(PendaftaranSiswa::BERKAS) as $kolom) {
            if ($request->hasFile($kolom)) {
                if ($pendaftaran->$kolom) {
                    Storage::disk('public')->delete($pendaftaran->$kolom);
                }

                $data[$kolom] = $request->file($kolom)
                    ->store("psb/{$pendaftaran->nomor_pendaftaran}", 'public');
            }
        }

        $pendaftaran->update($data);

        // Kalau sudah punya akun siswa, samakan namanya
        $pendaftaran->user?->update(['name' => $pendaftaran->nama_lengkap]);

        return redirect()
            ->route('admin.pendaftaran.show', $pendaftaran)
            ->with('success', 'Data pendaftar berhasil diperbarui.');
    }
    public function terima(Request $request, PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        if ($pendaftaran->status === PendaftaranSiswa::DITERIMA) {
            return back()->with('error', 'Pendaftar ini sudah diterima sebelumnya.');
        }

        if (! $pendaftaran->email || ! $pendaftaran->password) {
            return back()->with('error', 'Pendaftar ini belum punya email/password login, jadi belum bisa diterima.');
        }

        // 1) Simpan ceklis, paspor, kelas, kamar, dan catatan lebih dulu
        $data = $this->dataSyarat($request, $pendaftaran);
        $pendaftaran->update($data);
        $pendaftaran->load(['kelasMaster', 'kamarMaster']); // nama kelas/kamar terbaru untuk chat WA

        // 2) Terima + buat akun siswa di tabel users
        $pendaftaran->terima($data['catatan_admin']);

        // 3) Kirim chat WhatsApp
        $penempatan = $pendaftaran->ringkasanPenempatan();

        $pesan = "Assalamu'alaikum, alhamdulillah santri *{$pendaftaran->nama_lengkap}* dinyatakan *DITERIMA*.\n\n"
            . ($penempatan ? "*Penempatan:*\n{$penempatan}\n\n" : '')
            . "*Status persyaratan:*\n" . $pendaftaran->ringkasanSyarat() . "\n\n"
            . ($pendaftaran->catatan_admin ? "*Catatan admin:*\n{$pendaftaran->catatan_admin}\n\n" : '')
            . "Silakan masuk ke akun siswa:\n" . url('/login') . "\n"
            . "Email: *{$pendaftaran->email}*\n"
            . "Password: sesuai yang Anda isi saat mendaftar.";

        $terkirim = $wa->kirim($pendaftaran->no_hp_wali, $pesan);

        return redirect()->route('admin.pendaftaran.show', $pendaftaran)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Pendaftar diterima dan chat WhatsApp terkirim.'
                : 'Pendaftar diterima, tetapi chat WhatsApp GAGAL terkirim. Hubungi wali lewat ikon WhatsApp di halaman ini.'
        );
    }

    /** Ambil isian ceklis + catatan dari form. Kotak yang tidak dicentang tidak dikirim browser, jadi dianggap false. */
    private function dataSyarat(Request $request, PendaftaranSiswa $pendaftaran): array
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
            'syarat_no_paspor' => 'nullable|integer|min:1|max:200',
            // Harus ada di tabel Master Data
            'kelas_id' => ['nullable', 'integer', Rule::exists('kelas', 'id')],
            'kamar_id' => [
                'nullable', 'integer', Rule::exists('kamar', 'id'),
                // Tolak kalau kamar sudah penuh (santri ini sendiri tidak ikut dihitung)
                function ($attribute, $value, $fail) use ($pendaftaran) {
                    $kamar = Kamar::find($value);
                    if ($kamar && $kamar->penuh($pendaftaran->id)) {
                        $fail("Kamar {$kamar->nama} sudah penuh ({$kamar->kapasitas} santri).");
                    }
                },
            ],
        ], [
            'kelas_id.exists' => 'Kelas yang dipilih tidak ada di Master Data.',
            'kamar_id.exists' => 'Kamar yang dipilih tidak ada di Master Data.',
        ]);

        $data = [
            'catatan_admin' => $request->input('catatan_admin'),
            'syarat_no_paspor' => $request->input('syarat_no_paspor'),
            'kelas_id'         => $request->input('kelas_id') ?: null,
            'kamar_id'         => $request->input('kamar_id') ?: null,
        ];

        foreach (array_keys(PendaftaranSiswa::SYARAT) as $kolom) {
            $data[$kolom] = $request->boolean($kolom);
        }

        return $data;
    }
    /** Tombol "Simpan Persyaratan & Catatan" (tanpa menerima) */
    public function simpanSyarat(Request $request, PendaftaranSiswa $pendaftaran)
    {
        $pendaftaran->update($this->dataSyarat($request, $pendaftaran));

        return back()->with('success', 'Persyaratan dan catatan berhasil disimpan.');
    }

    public function tolak(Request $request, PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500']);

        $pendaftaran->tolak($request->catatan_admin);

        $pesan = "Assalamu'alaikum, mohon maaf pendaftaran santri *{$pendaftaran->nama_lengkap}* *DITOLAK*.\n\n"
            . "Alasan:\n" . $request->catatan_admin;
        $terkirim = $wa->kirim($pendaftaran->no_hp_wali, $pesan);
        $wa->kirim($pendaftaran->no_hp, $pesan);

        return redirect()->route('admin.pendaftaran.index')->with('success', 'Pendaftar ditolak.');
    }

    /**
     * Hapus pendaftar/santri beserta berkas unggahan dan akun login siswanya.
     * Dipakai tombol tempat sampah di Data Masuk Peserta (form biasa) dan Data Santri (fetch/JSON).
     */
    public function destroy(Request $request, PendaftaranSiswa $pendaftaran)
    {
        $nama = $pendaftaran->nama_lengkap;

        DB::transaction(function () use ($pendaftaran) {
            $user = $pendaftaran->user;

            $pendaftaran->delete();

            // Akun login ikut dihapus, tapi HANYA akun siswa (akun admin tidak tersentuh)
            if ($user && $user->role === 'siswa') {
                $user->delete();
            }
        });

        // Berkas dihapus setelah data terhapus (kalau gagal di atas, berkas tetap aman)
        foreach (array_keys(PendaftaranSiswa::BERKAS) as $kolom) {
            if ($pendaftaran->$kolom) {
                Storage::disk('public')->delete($pendaftaran->$kolom);
            }
        }

        $pesan = "Data {$nama} berhasil dihapus.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesan]);
        }

        // Kembali ke halaman sebelumnya (pencarian/filter tetap)
        return back()->with('success', $pesan);
    }
}
