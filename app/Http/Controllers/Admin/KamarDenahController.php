<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Denah kamar (dibuka dari modal Edit Kamar di Master Data).
 *
 * Kasur dinomori 1..kapasitas. Santri menempati kasur lewat kolom
 * pendaftaran_siswas.nomor_kasur (+ kamar_id = kamar ini).
 */
class KamarDenahController extends Controller
{
    /** GET /admin/master-data/kamar/{kamar}/denah */
    public function show(Kamar $kamar): JsonResponse
    {
        return response()->json($this->data($kamar));
    }

    /**
     * POST /admin/master-data/kamar/{kamar}/kasur
     * body: { santri_id, nomor_kasur }  -> tempatkan santri di kasur itu
     */
    public function tempatkan(Request $request, Kamar $kamar): JsonResponse
    {
        $data = $request->validate([
            'santri_id' => ['required', 'integer', Rule::exists('pendaftaran_siswas', 'id')->where('status', PendaftaranSiswa::DITERIMA)],
            'nomor_kasur' => ['required', 'integer', 'min:1', 'max:' . max(1, $kamar->kapasitas)],
        ], [
            'santri_id.required' => 'Pilih santri dulu.',
            'nomor_kasur.max' => 'Nomor kasur melebihi kapasitas kamar.',
        ]);

        $santri = PendaftaranSiswa::findOrFail($data['santri_id']);

        // Kasur sudah ditempati santri lain?
        $penghuni = PendaftaranSiswa::where('kamar_id', $kamar->id)
            ->where('nomor_kasur', $data['nomor_kasur'])
            ->whereKeyNot($santri->id)
            ->first();
        if ($penghuni) {
            throw ValidationException::withMessages([
                'nomor_kasur' => "Kasur {$data['nomor_kasur']} sudah ditempati {$penghuni->nama_lengkap}. Kosongkan dulu.",
            ]);
        }

        // Santri dari luar kamar ini -> cek kamar masih muat
        if ($santri->kamar_id !== $kamar->id && $kamar->penuh()) {
            throw ValidationException::withMessages([
                'santri_id' => "Kamar {$kamar->nama} sudah penuh ({$kamar->kapasitas} santri).",
            ]);
        }

        $santri->forceFill(['kamar_id' => $kamar->id, 'nomor_kasur' => $data['nomor_kasur']])->save();

        return response()->json([
            'message' => "{$santri->nama_lengkap} ditempatkan di kasur {$data['nomor_kasur']}.",
        ] + $this->data($kamar));
    }

    /**
     * DELETE /admin/master-data/kamar/{kamar}/kasur/{santri}?keluar=1
     * Kosongkan kasur. Dengan ?keluar=1 santri sekalian dikeluarkan dari kamar ini.
     */
    public function kosongkan(Request $request, Kamar $kamar, PendaftaranSiswa $santri): JsonResponse
    {
        abort_unless($santri->kamar_id === $kamar->id, 404);

        $keluar = $request->boolean('keluar');
        $santri->forceFill(['nomor_kasur' => null] + ($keluar ? ['kamar_id' => null] : []))->save();

        return response()->json([
            'message' => $keluar
                ? "{$santri->nama_lengkap} dikeluarkan dari {$kamar->nama}."
                : "Kasur {$santri->nama_lengkap} dikosongkan.",
        ] + $this->data($kamar));
    }

    /* ------------------------------------------------------------------ */

    /** Data untuk menggambar denah. */
    private function data(Kamar $kamar): array
    {
        $kamar->refresh();

        $penghuni = PendaftaranSiswa::where('kamar_id', $kamar->id)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn (PendaftaranSiswa $s) => $this->santri($s));

        // Santri diterima yang belum punya kamar -> bisa langsung ditempatkan di sini
        $tanpaKamar = PendaftaranSiswa::where('status', PendaftaranSiswa::DITERIMA)
            ->whereNull('kamar_id')
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn (PendaftaranSiswa $s) => $this->santri($s));

        return [
            'kamar' => [
                'id' => $kamar->id,
                'nama' => $kamar->nama,
                'kapasitas' => $kamar->kapasitas,
                'kasur_per_baris' => $kamar->kasur_per_baris ?: 4,
            ],
            'penghuni' => $penghuni->values(),
            'tanpa_kamar' => $tanpaKamar->values(),
        ];
    }

    private function santri(PendaftaranSiswa $s): array
    {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'arab' => $s->nama_lengkap_arab, // nama Arab (tampil di bawah nama)
            'nomor' => $s->nomor_pendaftaran,
            'nomor_kasur' => $s->nomor_kasur,
            'foto' => $s->berkas_foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($s->berkas_foto) : null,
        ];
    }
}
