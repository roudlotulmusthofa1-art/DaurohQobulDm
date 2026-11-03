<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\PendaftaranSiswa;
use App\Services\WhatsAppService;
use App\Support\PesanWa;
use Illuminate\Http\Request;
use App\Http\Requests\UpdatePendaftaranRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminPendaftaranController extends Controller
{
    /** Tombol Aktif/Nonaktif pendaftaran di halaman Data Masuk Peserta */
    public function bukaTutup(Request $request)
    {
        $dibuka = $request->validate(['dibuka' => ['required', 'boolean']])['dibuka'];
        \App\Models\Pengaturan::simpan('psb_dibuka', $dibuka ? '1' : '0');

        return response()->json([
            'dibuka' => (bool) $dibuka,
            'pesan' => $dibuka ? 'Pendaftaran DIBUKA. Peserta bisa mendaftar.' : 'Pendaftaran DITUTUP. Tidak ada pendaftar baru yang bisa masuk.',
        ]);
    }

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

        $psbDibuka = PendaftaranSiswa::pendaftaranDibuka();

        return view('admin.pendaftaran.index', compact('pendaftar', 'stat', 'statusTetap', 'psbDibuka'));
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

        // Kasur per kamar untuk pilihan "Nomor Kasur": kapasitas + kasur yang sudah ditempati santri lain
        $terisi = PendaftaranSiswa::whereIn('kamar_id', $kamarList->pluck('id'))
            ->whereNotNull('nomor_kasur')
            ->whereKeyNot($pendaftaran->id)
            ->get(['kamar_id', 'nomor_kasur', 'nama_lengkap'])
            ->groupBy('kamar_id');

        $kasurPerKamar = $kamarList->mapWithKeys(fn ($k) => [$k->id => [
            'kapasitas' => (int) $k->kapasitas,
            'terisi' => ($terisi[$k->id] ?? collect())->mapWithKeys(fn ($s) => [$s->nomor_kasur => $s->nama_lengkap]),
        ]]);

        return view('admin.pendaftaran.show', [
            'p' => $pendaftaran,
            'kelasList' => $kelasList,
            'kamarList' => $kamarList,
            'kasurPerKamar' => $kasurPerKamar,
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
        $this->simpanData($pendaftaran, $data);
        $pendaftaran->load(['kelasMaster', 'kamarMaster']); // nama kelas/kamar terbaru untuk chat WA

        // 2) Terima + buat akun siswa di tabel users
        $pendaftaran->terima($data['catatan_admin']);

        // 3) Kirim chat WhatsApp otomatis lewat Fonnte (isi pesan diatur di Pengaturan > Pesan)
        $terkirim = $this->kirimChatKonfirmasi($pendaftaran, $wa);
        if ($terkirim === null) {
            return redirect()->route('admin.pendaftaran.show', $pendaftaran)->with('success', 'Pendaftar diterima. (Pesan WhatsApp dimatikan di Pengaturan › Pesan.)');
        }

        return redirect()->route('admin.pendaftaran.show', $pendaftaran)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Pendaftar diterima dan chat WhatsApp terkirim.'
                : 'Pendaftar diterima, tetapi chat WhatsApp otomatis GAGAL: ' . $wa->alasan . '. Kirim lewat tombol "Kirim lewat WhatsApp saya" di bawah.'
        );
    }

    /**
     * Tombol "Kirim Ulang Chat Konfirmasi" di detail pendaftar (selalu ada untuk yang sudah diterima/ditolak),
     * dipakai bila chat sebelumnya gagal terkirim atau wali minta dikirim lagi.
     */
    public function kirimUlangChat(PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        if (! in_array($pendaftaran->status, [PendaftaranSiswa::DITERIMA, PendaftaranSiswa::DITOLAK], true)) {
            return back()->with('error', 'Pendaftar ini belum diterima atau ditolak, jadi belum ada chat konfirmasi.');
        }

        $terkirim = $this->kirimChatKonfirmasi($pendaftaran->load(['kelasMaster', 'kamarMaster']), $wa);

        return back()->with(...match ($terkirim) {
            null => ['error', 'Pesan WhatsApp untuk pendaftar ' . ($pendaftaran->status === PendaftaranSiswa::DITERIMA ? 'diterima' : 'ditolak') . ' sedang dimatikan di Pengaturan › Pesan.'],
            true => ['success', 'Chat konfirmasi berhasil dikirim ulang ke WhatsApp.'],
            false => ['error', 'Chat otomatis GAGAL: ' . $wa->alasan . '. Kirim lewat tombol "Kirim lewat WhatsApp saya".'],
        });
    }

    /**
     * Kirim chat konfirmasi sesuai status (diterima / ditolak).
     * @return bool|null  true = terkirim, false = gagal, null = pesan dimatikan di Pengaturan > Pesan
     */
    private function kirimChatKonfirmasi(PendaftaranSiswa $pendaftaran, WhatsAppService $wa): ?bool
    {
        $chat = $pendaftaran->chatKonfirmasi(); // diterima: ke wali; ditolak: ke wali & pendaftar
        if (! $chat) {
            return null;
        }

        $terkirim = false;
        foreach ($chat['tujuan'] as $nomor) {
            $terkirim = $wa->kirim($nomor, $chat['pesan']) || $terkirim;
        }
        if (! $chat['tujuan']) {
            $wa->alasan = 'nomor WhatsApp wali/pendaftar kosong';
        }

        return $terkirim;
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
            // Nomor kasur di kamar yang dipilih: 1..kapasitas dan belum ditempati santri lain
            'nomor_kasur' => [
                'nullable', 'integer', 'min:1',
                function ($attribute, $value, $fail) use ($request, $pendaftaran) {
                    $kamar = Kamar::find($request->input('kamar_id'));
                    if (! $kamar) {
                        $fail('Pilih kamar dulu sebelum menentukan nomor kasur.');
                        return;
                    }
                    if ($kamar->kapasitas > 0 && $value > $kamar->kapasitas) {
                        $fail("Nomor kasur melebihi kapasitas {$kamar->nama} ({$kamar->kapasitas} kasur).");
                        return;
                    }
                    $penghuni = PendaftaranSiswa::where('kamar_id', $kamar->id)->where('nomor_kasur', $value)
                        ->whereKeyNot($pendaftaran->id)->value('nama_lengkap');
                    if ($penghuni) {
                        $fail("Kasur {$value} di {$kamar->nama} sudah ditempati {$penghuni}.");
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
            'nomor_kasur'      => $request->input('kamar_id') ? ($request->input('nomor_kasur') ?: null) : null,
        ];

        foreach (array_keys(PendaftaranSiswa::SYARAT) as $kolom) {
            $data[$kolom] = $request->boolean($kolom);
        }

        return $data;
    }
    /**
     * Simpan isian Persyaratan. Model mengosongkan nomor kasur saat kamar berganti,
     * jadi nomor kasur pilihan admin dipasang lagi sesudahnya.
     */
    private function simpanData(PendaftaranSiswa $pendaftaran, array $data): void
    {
        $pendaftaran->update($data);

        if ($pendaftaran->nomor_kasur != $data['nomor_kasur']) {
            $pendaftaran->forceFill(['nomor_kasur' => $data['nomor_kasur']])->saveQuietly();
        }
    }

    /** Tombol "Simpan Persyaratan & Catatan" (tanpa menerima) */
    public function simpanSyarat(Request $request, PendaftaranSiswa $pendaftaran)
    {
        $this->simpanData($pendaftaran, $this->dataSyarat($request, $pendaftaran));

        return back()->with('success', 'Persyaratan dan catatan berhasil disimpan.');
    }

    public function tolak(Request $request, PendaftaranSiswa $pendaftaran, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500']);

        $pendaftaran->tolak($request->catatan_admin);

        $terkirim = $this->kirimChatKonfirmasi($pendaftaran->refresh(), $wa);

        if ($terkirim === false) {
            return redirect()->route('admin.pendaftaran.show', $pendaftaran)->with('error',
                'Pendaftar ditolak, tetapi chat WhatsApp otomatis GAGAL: ' . $wa->alasan . '. Kirim lewat tombol "Kirim lewat WhatsApp saya" di bawah.');
        }

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
