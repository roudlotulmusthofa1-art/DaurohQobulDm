<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranAsatid;
use App\Models\PesanAsatid;
use App\Services\WhatsAppService;
use App\Support\PesanWa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Admin > Manajemen Data > Data Asatid
 * Daftar asatid yang mendaftar lewat link khusus, lalu diterima/ditolak admin.
 */
class AdminDataAsatidController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;

        $asatid = PendaftaranAsatid::query()
            ->cari($request->q)
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stat = [
            'total'    => PendaftaranAsatid::count(),
            'menunggu' => PendaftaranAsatid::where('status', PendaftaranAsatid::MENUNGGU)->count(),
            'diterima' => PendaftaranAsatid::where('status', PendaftaranAsatid::DITERIMA)->count(),
            'ditolak'  => PendaftaranAsatid::where('status', PendaftaranAsatid::DITOLAK)->count(),
        ];

        // Link pendaftaran yang dibagikan admin ke calon asatid
        $linkDaftar = route('asatid.daftar', PendaftaranAsatid::kodeLink());

        return view('admin.asatid.index', compact('asatid', 'stat', 'status', 'linkDaftar') + [
            'penerimaPesan' => self::penerimaPesan(), // pilihan di pop-up "Kirim Pesan"
        ]);
    }

    public function show(PendaftaranAsatid $asatid)
    {
        return view('admin.asatid.show', [
            'a'             => $asatid,
            'penerimaPesan' => self::penerimaPesan(),
            // Riwayat pesan admin untuk asatid ini (terbaru di atas)
            'riwayatPesan'  => $asatid->user_id
                ? PesanAsatid::with('pengirim:id,name')->where('user_id', $asatid->user_id)->dariAdmin()->latest('id')->limit(30)->get()
                : collect(),
        ]);
    }

    /**
     * Kirim pesan ke asatid: semua asatid aktif, atau asatid yang dipilih.
     * Pesan muncul di dasbor asatid > Pesan dari Admin (+ notifikasi lonceng).
     */
    public function kirimPesan(Request $request)
    {
        $data = $request->validateWithBag('kirimPesan', [
            'tujuan'     => ['required', Rule::in(['semua', 'pilih'])],
            'user_ids'   => ['required_if:tujuan,pilih', 'array'],
            'user_ids.*' => ['integer'],
            'judul'      => ['nullable', 'string', 'max:150'],
            'isi'        => ['required', 'string', 'max:3000'],
        ], [
            'user_ids.required_if' => 'Pilih minimal satu asatid.',
            'isi.required'         => 'Isi pesan wajib diisi.',
            'isi.max'              => 'Isi pesan terlalu panjang (maks. 3000 karakter).',
        ]);

        // Hanya asatid yang sudah diterima & punya akun login
        $aktif = collect(self::penerimaPesan())->pluck('user_id');
        $tujuan = $data['tujuan'] === 'semua' ? $aktif : $aktif->intersect($data['user_ids'] ?? []);

        if ($tujuan->isEmpty()) {
            return back()->withErrors(['user_ids' => 'Tidak ada asatid aktif yang bisa dikirimi pesan.'], 'kirimPesan')->withInput();
        }

        $sekarang = now();
        PesanAsatid::insert($tujuan->values()->map(fn ($id) => [
            'user_id'     => $id,
            'pengirim_id' => $request->user()->id,
            'judul'       => $data['judul'] ?? null,
            'isi'         => $data['isi'],
            'created_at'  => $sekarang,
            'updated_at'  => $sekarang,
        ])->all());

        return back()->with('success', 'Pesan terkirim ke ' . $tujuan->count() . ' asatid. Pesan tampil di dasbor asatid › Pesan dari Admin.');
    }

    /** Hapus satu pesan yang sudah dikirim (dari riwayat di halaman detail asatid) */
    public function hapusPesan(PesanAsatid $pesan)
    {
        $pesan->delete();

        return back()->with('success', 'Pesan dihapus.');
    }

    /** Asatid yang bisa dikirimi pesan: sudah diterima & punya akun login */
    private static function penerimaPesan(): array
    {
        return PendaftaranAsatid::where('status', PendaftaranAsatid::DITERIMA)->whereNotNull('user_id')
            ->orderBy('nama_lengkap')->get()
            ->map(fn (PendaftaranAsatid $a) => [
                'user_id' => $a->user_id,
                'nama'    => $a->nama_lengkap,
                'arab'    => $a->nama_lengkap_arab,
                'nomor'   => $a->nomor_pendaftaran, // tetap dikirim: dipakai pencarian
                'foto'    => $a->foto_url,
            ])->all();
    }

    /** Tambah Cepat: admin langsung membuat data + akun login (status diterima) */
    public function tambahCepat(Request $request, WhatsAppService $wa)
    {
        [$baru, $pesan] = \App\Support\TambahCepat::simpan($request, PendaftaranAsatid::class, 'asatid_terima', $wa);

        return redirect()->route('admin.asatid.show', $baru)->with('success', $pesan);
    }

    /** Form ubah data asatid (oleh admin) */
    public function edit(PendaftaranAsatid $asatid)
    {
        return view('admin.asatid.edit', ['a' => $asatid]);
    }

    /**
     * Simpan perubahan data asatid.
     * Bila akun login sudah dibuat (diterima), nama/email/password akun ikut diperbarui
     * dan otomatis disalin ke semua sesi (App\Support\AkunBersama).
     */
    public function update(Request $request, PendaftaranAsatid $asatid)
    {
        $polaArab = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}-]+\z/u';
        // Alamat: huruf Arab + angka & tanda baca alamat (nomor rumah, RT/RW)
        $polaAlamat = '/\A[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟.\x{0027}0-9\/,()-]+\z/u';
        $userId = $asatid->user_id;

        $data = $request->validate([
            'nama_lengkap'      => ['required', 'string', 'max:150'],
            'nama_lengkap_arab' => ['nullable', 'string', 'max:150', "regex:$polaArab"],
            'tempat_lahir'      => ['nullable', 'string', 'max:100', "regex:$polaArab"],
            'tanggal_lahir'     => ['nullable', 'date', 'before:today'],
            'no_hp'             => ['required', 'string', 'regex:/^\+62[0-9]+$/', 'max:20'],
            'alamat'            => ['nullable', 'string', 'max:500', "regex:$polaAlamat"],
            'lama_belajar'      => ['nullable', 'string', 'max:100'],
            'tahun_selesai'     => ['nullable', 'digits:4'],
            'email'             => [
                'required', 'email', 'max:255',
                Rule::unique('pusat.pendaftaran_asatid', 'email')->ignore($asatid->id),
                'unique:pusat.pendaftaran_admin,email',
                'unique:pendaftaran_siswas,email',
                Rule::unique('users', 'email')->ignore($userId),
                Rule::unique('pusat.users', 'email')->ignore($userId),
            ],
            'password_baru'     => ['nullable', 'string', 'min:8', 'max:100'],
            'berkas_foto'       => ['nullable', 'image', 'max:5120'],
            'catatan_admin'     => ['nullable', 'string', 'max:1000'],
        ], [
            // Pesan per kolom (bukan '*.regex', supaya tidak menimpa pesan nomor WhatsApp)
            'nama_lengkap_arab.regex' => 'Kolom ini harus ditulis menggunakan huruf Arab.',
            'tempat_lahir.regex'  => 'Kolom ini harus ditulis menggunakan huruf Arab.',
            'alamat.regex'        => 'Kolom ini harus ditulis menggunakan huruf Arab.',
            'no_hp.regex'         => 'Format nomor WhatsApp tidak valid (contoh: +6281234567890).',
            'email.unique'        => 'Email ini sudah dipakai akun lain.',
            'password_baru.min'   => 'Password baru minimal 8 karakter.',
            'berkas_foto.max'     => 'Pas foto maksimal 5 MB.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
        ], [
            'nama_lengkap' => 'Nama lengkap', 'nama_lengkap_arab' => 'Nama Arab', 'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir', 'no_hp' => 'Nomor WhatsApp', 'alamat' => 'Alamat',
            'lama_belajar' => 'Lama belajar', 'tahun_selesai' => 'Tahun selesai', 'berkas_foto' => 'Pas foto',
        ]);

        $fotoLama = null;
        if ($request->hasFile('berkas_foto')) {
            $fotoLama = $asatid->berkas_foto;
            $data['berkas_foto'] = $request->file('berkas_foto')->store("asatid/{$asatid->nomor_pendaftaran}", 'bersama');
        } else {
            unset($data['berkas_foto']);
        }

        $passwordBaru = $data['password_baru'] ?? null;
        unset($data['password_baru']);
        if ($passwordBaru) {
            $data['password'] = Hash::make($passwordBaru);
        }

        DB::connection('pusat')->transaction(function () use ($asatid, $data) {
            $asatid->update($data);

            // Akun login ikut diperbarui (disimpan lewat model -> disalin ke semua sesi)
            if ($user = $asatid->user) {
                $user->forceFill(array_filter([
                    'name'     => $asatid->nama_lengkap,
                    'email'    => $asatid->email,
                    'password' => $data['password'] ?? null, // sudah ter-hash
                ]))->save();
            }
        });

        if ($fotoLama) {
            Storage::disk('bersama')->delete($fotoLama);
        }

        return redirect()->route('admin.asatid.show', $asatid)->with('success', 'Data ' . $asatid->nama_lengkap . ' berhasil diperbarui.');
    }

    public function terima(Request $request, PendaftaranAsatid $asatid, WhatsAppService $wa)
    {
        if ($asatid->status === PendaftaranAsatid::DITERIMA) {
            return back()->with('error', 'Asatid ini sudah diterima sebelumnya.');
        }

        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        // Terima + buat akun di tabel users dengan role asatid
        $asatid->terima($request->input('catatan_admin'));

        // Isi pesan diatur di Pengaturan > Pesan
        $pesan = PesanWa::buat('asatid_terima', ['nama' => $asatid->nama_lengkap, 'catatan' => $asatid->catatan_admin, 'email' => $asatid->email]);
        if (! $pesan) {
            return redirect()->route('admin.asatid.show', $asatid)->with('success', 'Asatid diterima. (Pesan WhatsApp dimatikan di Pengaturan › Pesan.)');
        }

        $terkirim = $wa->kirim($asatid->no_hp, $pesan);

        return redirect()->route('admin.asatid.show', $asatid)->with(
            $terkirim ? 'success' : 'error',
            $terkirim
                ? 'Asatid diterima dan chat WhatsApp terkirim.'
                : 'Asatid diterima, tetapi chat WhatsApp GAGAL terkirim. Hubungi lewat ikon WhatsApp di halaman ini.'
        );
    }

    public function tolak(Request $request, PendaftaranAsatid $asatid, WhatsAppService $wa)
    {
        $request->validate(['catatan_admin' => 'required|string|max:500'], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $asatid->tolak($request->catatan_admin);

        if ($pesan = PesanWa::buat('asatid_tolak', ['nama' => $asatid->nama_lengkap, 'alasan' => $request->catatan_admin])) {
            $wa->kirim($asatid->no_hp, $pesan);
        }

        return redirect()->route('admin.asatid.index')->with('success', 'Pendaftar asatid ditolak.');
    }

    /** Buat link pendaftaran baru; link lama langsung tidak berlaku. */
    public function linkBaru()
    {
        PendaftaranAsatid::buatKodeLinkBaru();

        return back()->with('success', 'Link pendaftaran baru sudah dibuat. Link lama tidak bisa dipakai lagi.');
    }

    /** Hapus data asatid beserta pas foto dan akun login-nya (hanya akun ber-role asatid). */
    /** Reset password akun login asatid (lupa password). Aturan & pesannya di App\Support\ResetPasswordAkun. */
    public function resetPassword(Request $request, PendaftaranAsatid $asatid, WhatsAppService $wa)
    {
        return \App\Support\ResetPasswordAkun::jalankan($request, $asatid, $wa);
    }

    public function destroy(PendaftaranAsatid $asatid)
    {
        $nama = $asatid->nama_lengkap;
        $foto = $asatid->berkas_foto;

        DB::connection('pusat')->transaction(function () use ($asatid) {
            $user = $asatid->user;
            $asatid->delete();

            if ($user && $user->role === PendaftaranAsatid::ROLE) {
                $user->delete();
            }
        });

        if ($foto) {
            Storage::disk('bersama')->delete($foto);
        }

        return redirect()->route('admin.asatid.index')->with('success', "Data {$nama} berhasil dihapus.");
    }
}
