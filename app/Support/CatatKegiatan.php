<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\AbsensiDetail;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Mencatat kehadiran di absensi kegiatan (pindai QR / cari nama).
 * Dipakai bersama oleh asatid (Asatid\AbsensiController) dan admin (Admin\AbsensiKegiatanController),
 * supaya aturannya sama: status menurut jam, pindai ulang tidak mengubah status, kegiatan nonaktif/ditutup ditolak.
 * Hak akses (siapa boleh membuka absensi mana) dicek di controller masing-masing.
 * Tampilannya: resources/views/absensi/partials/catat-kegiatan.blade.php
 */
class CatatKegiatan
{
    /**
     * Data singkat satu santri untuk halaman catat.
     * $rinci = nama route halaman data santri (asatid.peserta.show / admin.pendaftaran.show), boleh null.
     */
    public static function siswa(PendaftaranSiswa $s, ?string $rinci = null): array
    {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'arab' => $s->nama_lengkap_arab,
            'nomor' => $s->nomor_pendaftaran,
            'foto' => $s->foto_url,
            'kelas' => $s->relationLoaded('kelasMaster') ? $s->kelasMaster?->nama : null,
            'alamat' => $s->alamat_singkat,
            'pondok' => $s->pondok_terakhir,
            'detail' => $rinci ? route($rinci, $s) : null,
        ];
    }

    /** Semua peserta (diterima) + status yang sudah tercatat di absensi ini */
    public static function peserta(Absensi $absensi, ?string $rinci = null): Collection
    {
        $tercatat = $absensi->detail()->with('pencatat:id,name')->get()->keyBy('pendaftaran_siswa_id');

        return PendaftaranSiswa::with('kelasMaster')
            ->where('status', PendaftaranSiswa::DITERIMA)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($s) => self::siswa($s, $rinci) + [
                'status' => $tercatat[$s->id]->status ?? null,
                'waktu' => isset($tercatat[$s->id]) ? substr((string) $tercatat[$s->id]->waktu, 0, 5) : null,
                'oleh' => isset($tercatat[$s->id]) ? $tercatat[$s->id]->pencatat?->name : null, // yang mencatat
            ]);
    }

    /**
     * JSON: catat satu peserta. Body: { kode } (hasil pindai QR) atau { siswa_id, status }, plus { jam }.
     * Hasil pindai QR memakai status otomatis menurut jam.
     */
    public static function simpan(Request $request, Absensi $absensi, ?string $rinci = null): JsonResponse
    {
        if ($absensi->tutupKalauLewat()) {
            return response()->json(['message' => __('Waktu absensi sudah lewat batas alfa. Absensi ditutup otomatis.'), 'ditutup' => true], 422);
        }
        abort_if($absensi->selesai_at, 422, __('Absensi kegiatan ini sudah ditutup.'));
        abort_if($absensi->dari_admin && ! $absensi->aktif, 422, __('Absensi kegiatan ini sedang dinonaktifkan admin.'));

        $data = $request->validate([
            'kode' => ['nullable', 'string', 'max:100', 'required_without:siswa_id'],
            'siswa_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(array_keys(Absensi::STATUS))],
            'jam' => ['nullable', 'date_format:H:i'],
        ]);

        $siswa = PendaftaranSiswa::with('kelasMaster')->where('status', PendaftaranSiswa::DITERIMA)
            ->when($data['siswa_id'] ?? null,
                fn ($q, $id) => $q->whereKey($id),
                fn ($q) => $q->where(fn ($w) => $w
                    ->where('kode_qr', trim($data['kode']))
                    ->orWhere('nomor_pendaftaran', strtoupper(trim($data['kode'])))))
            ->first();

        if (! $siswa) {
            return response()->json(['message' => __('Kode tidak dikenali. Pastikan kartu milik peserta yang sudah diterima.')], 404);
        }

        $jam = $data['jam'] ?? now('Asia/Jakarta')->format('H:i');
        $status = $data['status'] ?? $absensi->statusMenurutJam($jam);

        $lama = AbsensiDetail::where('absensi_id', $absensi->id)->where('pendaftaran_siswa_id', $siswa->id)->first();

        // Pindai ulang kartu yang sudah tercatat tidak mengubah statusnya
        if ($lama && empty($data['status'])) {
            return response()->json([
                'siswa' => self::siswa($siswa, $rinci) + ['status' => $lama->status, 'waktu' => substr((string) $lama->waktu, 0, 5), 'oleh' => $lama->pencatat?->name],
                'ulang' => true,
                'message' => __(':nama sudah tercatat sebelumnya.', ['nama' => $siswa->nama_lengkap]),
            ]);
        }

        $detail = AbsensiDetail::updateOrCreate(
            ['absensi_id' => $absensi->id, 'pendaftaran_siswa_id' => $siswa->id],
            ['status' => $status, 'waktu' => $lama?->waktu ?? $jam, 'dicatat_oleh' => Auth::id()],
        );

        return response()->json([
            'siswa' => self::siswa($siswa, $rinci) + ['status' => $detail->status, 'waktu' => substr((string) $detail->waktu, 0, 5), 'oleh' => Auth::user()->name],
            'ulang' => false,
            'message' => $siswa->nama_lengkap . ' ' . __('dicatat') . ' ' . __(Absensi::STATUS[$status][0]) . '.',
        ]);
    }

    /** JSON: status semua peserta yang sudah tercatat (halaman catat memanggilnya tiap 10 detik) */
    public static function status(Absensi $absensi): JsonResponse
    {
        $absensi->tutupKalauLewat();

        $detail = $absensi->detail()->with('pencatat:id,name')->get(['pendaftaran_siswa_id', 'status', 'waktu', 'dicatat_oleh'])
            ->mapWithKeys(fn ($d) => [$d->pendaftaran_siswa_id => [
                'status' => $d->status,
                'waktu' => substr((string) $d->waktu, 0, 5),
                'oleh' => $d->pencatat?->name,
            ]]);

        return response()->json([
            'tercatat' => $detail,
            'ditutup' => (bool) $absensi->selesai_at,
            'lewat_batas' => (bool) $absensi->selesai_at && $absensi->lewatBatasAlfa(), // ditutup karena waktunya habis
            'aktif' => (bool) $absensi->aktif,
        ]);
    }
}
