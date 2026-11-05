<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\Sesi;
use App\Models\Terjemahan;
use App\Support\CadanganDatabase;
use App\Support\NotifikasiAdmin;
use App\Support\PengelolaSesi;
use App\Support\PesanWa;
use App\Support\SesiAplikasi;
use App\Support\TeksTampilan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Admin > Sistem > Pengaturan
 * Tab: Umum (profil pesantren), Profil (akun admin yang login), Notifikasi (lonceng),
 *      Database (backup, restore, hapus data sesi aktif), Sesi (angkatan/gelombang dauroh: database & file terpisah).
 */
class PengaturanController extends Controller
{
    public const TAB = [
        'umum' => ['label' => 'Umum', 'ikon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        'profil' => ['label' => 'Profil', 'ikon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        'notifikasi' => ['label' => 'Notifikasi', 'ikon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
        'pesan' => ['label' => 'Pesan', 'ikon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
        'database' => ['label' => 'Database', 'ikon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125'],
        'sesi' => ['label' => 'Sesi', 'ikon' => 'M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3'],
        'bahasa' => ['label' => 'Bahasa', 'ikon' => 'M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802'],
    ];

    public function index(Request $request): View
    {
        $tab = array_key_exists($request->query('tab'), self::TAB) ? $request->query('tab') : 'umum';
        $data = ['tab' => $tab, 'tabList' => self::TAB];

        if ($tab === 'umum') {
            $data['profil'] = Pengaturan::profilPesantren();
        } elseif ($tab === 'notifikasi') {
            $data['notifikasi'] = collect(NotifikasiAdmin::JENIS)->map(fn($j, $kunci) => $j + [
                'kunci' => $kunci,
                'aktif' => NotifikasiAdmin::aktif($kunci),
                'ikon_path' => NotifikasiAdmin::IKON[$j['ikon']],
                'warna_kelas' => NotifikasiAdmin::WARNA[$j['warna']],
            ]);
        } elseif ($tab === 'pesan') {
            $data['pesanList'] = collect(PesanWa::TEMPLATE)->map(fn($t, $kunci) => $t + [
                'kunci' => $kunci,
                'teks' => PesanWa::teks($kunci),
                'aktif' => PesanWa::aktif($kunci),
                'diubah' => PesanWa::diubah($kunci),
            ])->groupBy('grup', true);
        } elseif ($tab === 'database') {
            $data['db'] = $this->infoDatabase();
            $data['server'] = $this->infoServer();
            $data['sesiAktif'] = SesiAplikasi::aktif();
            $data['cadangan'] = $data['sesiAktif'] ? CadanganDatabase::daftar($data['sesiAktif']) : [];
            $data['kelompok'] = CadanganDatabase::KELOMPOK;
        } elseif ($tab === 'sesi') {
            $data['sesiList'] = Sesi::orderByDesc('is_active')->orderByDesc('id')->get();
            $data['sesiAktif'] = SesiAplikasi::aktif();
            $data['ringkasSesi'] = $data['sesiList']->mapWithKeys(fn(Sesi $s) => [$s->id => $this->ringkasSesi($s)]);
        } elseif ($tab === 'bahasa') {
            $data += $this->dataBahasa($request);
        }

        return view('admin.pengaturan.index', $data);
    }

    /* ---------------- Umum: profil pesantren ---------------- */

    public function simpanUmum(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ], [
            'nama.required' => 'Nama pesantren wajib diisi.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
        ]);

        foreach (['nama', 'alamat', 'telepon', 'email'] as $kunci) {
            Pengaturan::simpan('pesantren_' . $kunci, $data[$kunci] ?? null);
        }

        if ($request->hasFile('logo')) {
            $lama = Pengaturan::ambil('pesantren_logo');
            Pengaturan::simpan('pesantren_logo', $request->file('logo')->store('pengaturan', 'public'));
            if ($lama) {
                Storage::disk('public')->delete($lama);
            }
        }

        return $this->keTab('umum', 'Profil pesantren disimpan.');
    }

    public function hapusLogo(): RedirectResponse
    {
        if ($lama = Pengaturan::ambil('pesantren_logo')) {
            Storage::disk('public')->delete($lama);
        }
        Pengaturan::simpan('pesantren_logo', null);

        return $this->keTab('umum', 'Logo dihapus. Logo bawaan aplikasi dipakai kembali.');
    }

    /* ---------------- Profil: akun admin yang sedang login ---------------- */

    public function simpanProfil(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id), Rule::unique('pusat.users', 'email')->ignore($user->id)],
            'password_lama' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'password_lama.required_with' => 'Masukkan password lama untuk mengganti password.',
            'password_lama.current_password' => 'Password lama salah.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = $data['password']; // di-hash otomatis (cast "hashed")
        }
        $user->save(); // akun admin ikut diperbarui di semua sesi

        return $this->keTab('profil', 'Profil disimpan.' . (!empty($data['password']) ? ' Password sudah diganti.' : ''));
    }

    /**
     * Upload foto profil akun yang sedang login. Disimpan di disk "bersama" karena akun admin dipakai semua sesi;
     * user->save() otomatis menyalin kolom foto ke database sesi lain (App\Support\AkunBersama).
     */
    public function simpanFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ]);

        $user = $request->user();
        $lama = $user->foto;
        $user->foto = $request->file('foto')->store('akun/foto', 'bersama');
        $user->save();

        if ($lama) {
            Storage::disk('bersama')->delete($lama);
        }

        return $this->keTab('profil', 'Foto profil diperbarui.');
    }

    public function hapusFoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->foto) {
            Storage::disk('bersama')->delete($user->foto);
            $user->foto = null;
            $user->save();
        }

        return $this->keTab('profil', 'Foto profil dihapus.');
    }

    /* ---------------- Notifikasi ---------------- */

    public function update(Request $request): RedirectResponse
    {
        // Kotak yang tidak dicentang tidak terkirim -> dianggap mati
        $dipilih = (array) $request->input('notif', []);

        foreach (array_keys(NotifikasiAdmin::JENIS) as $kunci) {
            Pengaturan::simpan('notif_' . $kunci, !empty($dipilih[$kunci]) ? '1' : '0');
        }

        return $this->keTab('notifikasi', 'Pengaturan notifikasi berhasil disimpan.');
    }

    /* ---------------- Pesan WhatsApp ---------------- */

    public function simpanPesan(Request $request): RedirectResponse
    {
        $kunciList = array_keys(PesanWa::TEMPLATE);

        // Tombol "Kembalikan ke bawaan" untuk satu pesan
        if (in_array($request->input('kembalikan'), $kunciList, true)) {
            Pengaturan::simpanPusat('pesan_wa_' . $request->input('kembalikan'), null);
            $judul = PesanWa::TEMPLATE[$request->input('kembalikan')]['judul'];

            return redirect()->route('admin.pengaturan.index', ['tab' => 'pesan'])->withFragment($request->input('kembalikan'))
                ->with('success', "Pesan \"{$judul}\" dikembalikan ke teks bawaan.");
        }

        $data = $request->validate([
            'teks' => ['required', 'array'],
            'teks.*' => ['nullable', 'string', 'max:3000'],
            'aktif' => ['nullable', 'array'],
        ], ['teks.*.max' => 'Pesan maksimal 3000 karakter.']);

        $rapi = fn($s) => trim(str_replace("\r\n", "\n", (string) $s));
        foreach ($kunciList as $kunci) {
            if (!array_key_exists($kunci, $data['teks'])) {
                continue;
            }
            $teks = $rapi($data['teks'][$kunci]);
            // Sama dengan bawaan / dikosongkan -> pakai bawaan (simpan kosong)
            Pengaturan::simpanPusat('pesan_wa_' . $kunci, $teks === '' || $teks === $rapi(PesanWa::TEMPLATE[$kunci]['bawaan']) ? null : $teks);
            Pengaturan::simpanPusat('pesan_wa_' . $kunci . '_aktif', !empty($data['aktif'][$kunci]) ? '1' : '0');
        }

        return $this->keTab('pesan', 'Pesan WhatsApp disimpan. Berlaku untuk semua sesi.');
    }

    /* ---------------- Database (sesi aktif) ---------------- */

    /**
     * Disk & memori (RAM) server. RAM hanya terbaca di server Linux (termasuk Docker);
     * di Windows/Laragon ditampilkan "tidak tersedia".
     */
    private function infoServer(): array
    {
        $folder = storage_path();
        $total = @disk_total_space($folder) ?: 0;
        $sisa = @disk_free_space($folder) ?: 0;

        $ram = null;
        if (is_readable('/proc/meminfo')) {
            preg_match_all('/^(\w+):\s+(\d+) kB/m', (string) file_get_contents('/proc/meminfo'), $m);
            $info = array_combine($m[1], array_map(fn($v) => (int) $v * 1024, $m[2]));
            if (isset($info['MemTotal'])) {
                $tersedia = $info['MemAvailable'] ?? ($info['MemFree'] ?? 0);
                $ram = ['total' => $info['MemTotal'], 'sisa' => $tersedia, 'terpakai' => $info['MemTotal'] - $tersedia];
            }
        }

        // Pemakaian aplikasi: semua database sesi + folder file + backup
        $ukuranFolder = function (string $path): int {
            $n = 0;
            if (is_dir($path)) {
                foreach (File::allFiles($path) as $f) {
                    $n += $f->getSize();
                }
            }

            return $n;
        };
        $semuaDb = 0;
        foreach (SesiAplikasi::semuaKoneksi() as $koneksi => $sesi) {
            $semuaDb += (int) rescue(fn() => DB::connection('pusat')->selectOne(
                'SELECT COALESCE(SUM(data_length + index_length), 0) AS n FROM information_schema.tables WHERE table_schema = ?',
                [config("database.connections.{$koneksi}.database")]
            )->n, 0, false);
        }

        return [
            'disk' => ['total' => $total, 'sisa' => $sisa, 'terpakai' => max(0, $total - $sisa)],
            'ram' => $ram,
            'aplikasi' => [
                'database' => $semuaDb,
                'file' => $ukuranFolder(storage_path('app/public')),
                'backup' => $ukuranFolder(storage_path('app/cadangan')),
                'log' => $ukuranFolder(storage_path('logs')),
            ],
        ];
    }

    private function infoDatabase(): array
    {
        $tabel = collect(Schema::getTables(DB::connection()->getDatabaseName()))
            ->map(fn($t) => ['nama' => $t['name'], 'ukuran' => (int) ($t['size'] ?? 0), 'baris' => DB::table($t['name'])->count()])
            ->sortByDesc('baris')->values();

        return [
            'driver' => DB::connection()->getDriverName(),
            'nama' => DB::connection()->getDatabaseName(),
            'versi' => rescue(fn() => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION), '-', false),
            'tabel' => $tabel,
            'ukuran' => $tabel->sum('ukuran'),
            'baris' => $tabel->sum('baris'),
            'bisa_backup' => in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true),
        ];
    }

    /** Unduh backup database sesi aktif langsung (.sql) */
    public function backup(): StreamedResponse|RedirectResponse
    {
        $sesi = SesiAplikasi::aktif();
        if (!$sesi || !in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $this->keTab('database', null, 'Backup otomatis hanya tersedia untuk database MySQL/MariaDB.');
        }

        $namaFile = 'backup-' . $sesi->kode . '-' . now()->format('Y-m-d-His') . '.sql';

        return response()->streamDownload(function () use ($sesi) {
            CadanganDatabase::tulis($sesi->koneksi(), function ($teks) {
                echo $teks;
            });
        }, $namaFile, ['Content-Type' => 'application/sql']);
    }

    /** Simpan backup di server (masuk daftar "Riwayat backup") */
    public function simpanBackup(): RedirectResponse
    {
        $nama = CadanganDatabase::simpan(SesiAplikasi::aktif());

        return $this->keTab('database', "Backup tersimpan di server: {$nama}");
    }

    /** Unduh salah satu file di riwayat backup */
    public function unduhBackup(string $nama): BinaryFileResponse|RedirectResponse
    {
        try {
            return response()->download(CadanganDatabase::pathFile(SesiAplikasi::aktif(), $nama));
        } catch (Throwable $e) {
            return $this->keTab('database', null, $e->getMessage());
        }
    }

    public function hapusBackup(string $nama): RedirectResponse
    {
        try {
            unlink(CadanganDatabase::pathFile(SesiAplikasi::aktif(), $nama));
        } catch (Throwable $e) {
            return $this->keTab('database', null, $e->getMessage());
        }

        return $this->keTab('database', "File backup {$nama} dihapus.");
    }

    /** Restore dari file upload (.sql) atau dari riwayat backup di server */
    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'sumber' => ['required', Rule::in(['upload', 'server'])],
            'file' => [
                'required_if:sumber,upload',
                'nullable',
                'file',
                'max:102400',
                function ($attr, $file, $gagal) {
                    if ($file && strtolower($file->getClientOriginalExtension()) !== 'sql') {
                        $gagal('File harus berakhiran .sql');
                    }
                }
            ],
            'nama' => ['required_if:sumber,server', 'nullable', 'string'],
            'password_konfirmasi' => ['required', 'current_password'],
            'yakin' => ['accepted'],
        ], $this->pesanKonfirmasi() + ['file.required_if' => 'Pilih file backup (.sql).', 'file.max' => 'Ukuran file maksimal 100 MB.']);

        $sesi = SesiAplikasi::aktif();
        @set_time_limit(600);
        try {
            $path = $request->sumber === 'upload' ? $request->file('file')->getRealPath() : CadanganDatabase::pathFile($sesi, $request->nama);
            $cadangan = CadanganDatabase::pulihkan($sesi, $path);
        } catch (Throwable $e) {
            report($e);

            return $this->keTab('database', null, 'Restore gagal: ' . $e->getMessage() . ' Bila data sempat berubah, salinan sebelum restore ada di riwayat backup.');
        }

        return $this->keTab('database', "Restore berhasil untuk sesi {$sesi->nama}. Data sebelumnya disimpan otomatis: {$cadangan}");
    }

    /** Hapus data per kelompok di sesi aktif */
    public function hapusData(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kelompok' => ['required', 'array', 'min:1'],
            'kelompok.*' => [Rule::in(array_keys(CadanganDatabase::KELOMPOK))],
            'ketik' => ['required', 'in:HAPUS'],
            'password_konfirmasi' => ['required', 'current_password'],
        ], $this->pesanKonfirmasi() + [
                'kelompok.required' => 'Pilih minimal satu kelompok data.',
                'ketik.in' => 'Ketik HAPUS (huruf besar) untuk konfirmasi.',
                'ketik.required' => 'Ketik HAPUS untuk konfirmasi.',
            ]);

        $sesi = SesiAplikasi::aktif();
        try {
            $cadangan = CadanganDatabase::hapusKelompok($sesi, $data['kelompok']);
        } catch (Throwable $e) {
            report($e);

            return $this->keTab('database', null, 'Hapus data gagal: ' . $e->getMessage());
        }

        $label = collect($data['kelompok'])->map(fn($k) => CadanganDatabase::KELOMPOK[$k]['label'])->implode(', ');

        return $this->keTab('database', "Data {$label} di sesi {$sesi->nama} sudah dihapus. Backup sebelum dihapus: {$cadangan}");
    }

    /* ---------------- Sesi ---------------- */

    public function simpanSesi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'salin_dari' => ['nullable', 'integer', Rule::exists('pusat.sesi', 'id')],
            'salin_master' => ['nullable', 'boolean'],
            'salin_pengaturan' => ['nullable', 'boolean'],
            'aktifkan' => ['nullable', 'boolean'],
        ], ['nama.required' => 'Nama sesi wajib diisi.', 'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);

        @set_time_limit(300);
        try {
            $sesi = PengelolaSesi::buat(
                $data,
                !empty($data['salin_dari']) ? Sesi::find($data['salin_dari']) : null,
                !empty($data['salin_master']),
                !empty($data['salin_pengaturan']),
            );
        } catch (Throwable $e) {
            report($e);

            return $this->keTab('sesi', null, $e->getMessage());
        }

        if (!empty($data['aktifkan'])) {
            $this->jadikanAktif($sesi);
        }

        return $this->keTab('sesi', "Sesi {$sesi->nama} dibuat (database {$sesi->database})." . (!empty($data['aktifkan']) ? ' Sesi ini sekarang aktif.' : ''));
    }

    public function ubahSesi(Request $request, Sesi $sesi): RedirectResponse
    {
        $sesi->update($request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], ['nama.required' => 'Nama sesi wajib diisi.']));

        return $this->keTab('sesi', "Sesi {$sesi->nama} diperbarui.");
    }

    public function aktifkanSesi(Sesi $sesi): RedirectResponse
    {
        @set_time_limit(300);
        try {
            PengelolaSesi::siapkan($sesi); // pastikan tabel & akun admin/asatid sudah terbaru
        } catch (Throwable $e) {
            report($e);

            return $this->keTab('sesi', null, "Sesi {$sesi->nama} tidak bisa dibuka: " . $e->getMessage());
        }
        $this->jadikanAktif($sesi);

        return $this->keTab('sesi', "Sesi aktif sekarang: {$sesi->nama}. Semua menu menampilkan data sesi ini.");
    }

    public function hapusSesi(Request $request, Sesi $sesi): RedirectResponse
    {
        $request->validate([
            'ketik' => ['required', Rule::in([$sesi->nama])],
            'password_konfirmasi' => ['required', 'current_password'],
        ], $this->pesanKonfirmasi() + ['ketik.in' => 'Ketik nama sesi persis sama untuk konfirmasi.', 'ketik.required' => 'Ketik nama sesi untuk konfirmasi.']);

        if ($sesi->utama || $sesi->is_active) {
            return $this->keTab('sesi', null, 'Sesi utama dan sesi yang sedang aktif tidak bisa dihapus.');
        }

        PengelolaSesi::hapus($sesi);

        return $this->keTab('sesi', "Sesi {$sesi->nama} beserta database dan filenya sudah dihapus.");
    }

    private function jadikanAktif(Sesi $sesi): void
    {
        DB::connection('pusat')->transaction(function () use ($sesi) {
            Sesi::whereKeyNot($sesi->id)->update(['is_active' => false]);
            $sesi->update(['is_active' => true]);
        });
        session(['sesi_login' => $sesi->id]); // admin yang memindah tetap login
    }

    /** Jumlah peserta & ukuran file per sesi (untuk kartu sesi) */
    private function ringkasSesi(Sesi $sesi): array
    {
        try {
            $koneksi = $sesi->koneksi();
            $peserta = Schema::connection($koneksi)->hasTable('pendaftaran_siswas') ? DB::connection($koneksi)->table('pendaftaran_siswas')->count() : 0;
            $ada = true;
        } catch (Throwable) {
            $peserta = 0;
            $ada = false;
        }

        $ukuranFile = 0;
        $folder = storage_path('app/public/' . $sesi->folder);
        if (!$sesi->utama && is_dir($folder)) {
            foreach (File::allFiles($folder) as $f) {
                $ukuranFile += $f->getSize();
            }
        }

        return ['peserta' => $peserta, 'ada' => $ada, 'ukuran_file' => $ukuranFile];
    }

    private function pesanKonfirmasi(): array
    {
        return [
            'password_konfirmasi.required' => 'Masukkan password Anda untuk konfirmasi.',
            'password_konfirmasi.current_password' => 'Password konfirmasi salah.',
            'yakin.accepted' => 'Centang pernyataan bahwa Anda yakin.',
        ];
    }

    /* ---------------- Bahasa: terjemahan Arab buatan admin ---------------- */

    /** Data tab Bahasa: daftar halaman, halaman terpilih, dan tulisan-tulisannya beserta terjemahan sekarang. */
    private function dataBahasa(Request $request): array
    {
        // Halaman yang belum punya tulisan __('...') tidak ditampilkan di pilihan
        $halamanList = array_filter(TeksTampilan::semua(), fn($h) => count($h['teks']) > 0) ?: TeksTampilan::semua();
        $kode = array_key_exists((string) $request->query('halaman'), $halamanList)
            ? $request->query('halaman')
            : (collect($halamanList)->search(fn($h) => count($h['teks']) > 0) ?: array_key_first($halamanList));

        $dariAdmin = Terjemahan::semua();
        // Usulan terjemahan (dipakai bila tombol "usulan" dinyalakan). File: lang/usulan-ar.json
        $usulan = json_decode(@file_get_contents(lang_path('usulan-ar.json')) ?: '{}', true) ?: [];

        $baris = collect($halamanList[$kode]['teks'])->map(fn($file, $teks) => [
            'teks' => $teks,
            'arab' => $dariAdmin[$teks] ?? '',
            'usulan' => $usulan[$teks] ?? '',
            'file' => $file,
        ])->values();

        return [
            'halamanList' => $halamanList,
            'halamanKode' => $kode,
            'barisBahasa' => $baris,
            'jumlahTerjemah' => $baris->where('arab', '!=', '')->count(),
        ];
    }

    public function simpanBahasa(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'halaman' => ['required', 'string', Rule::in(array_keys(TeksTampilan::HALAMAN))],
            'teks' => ['array'],
            'teks.*' => ['required', 'string', 'max:2000'],
            'arab' => ['array'],
            'arab.*' => ['nullable', 'string', 'max:2000'],
        ], [
            'arab.*.max' => 'Terjemahan terlalu panjang (maks. 2000 huruf).',
        ]);

        foreach ($data['teks'] ?? [] as $i => $teks) {
            Terjemahan::simpanSatu($teks, $data['arab'][$i] ?? null);
        }
        Terjemahan::lupakanCache();

        return redirect()->route('admin.pengaturan.index', ['tab' => 'bahasa', 'halaman' => $data['halaman']])
            ->with('success', 'Terjemahan disimpan.');
    }

    private function keTab(string $tab, ?string $sukses = null, ?string $gagal = null): RedirectResponse
    {
        $r = redirect()->route('admin.pengaturan.index', ['tab' => $tab]);

        return $gagal ? $r->with('error', $gagal) : $r->with('success', $sukses);
    }
}
