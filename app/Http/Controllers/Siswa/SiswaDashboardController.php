<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSiswa;
use App\Models\PesanPeserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Dasbor siswa/peserta (role "siswa"). Tampilan mengikuti dasbor asatid.
 * Menu: Profil, Pengumuman Kelulusan, Pesan dari Admin, Pesan untuk Admin.
 * Semua halaman memakai layout resources/views/layouts/siswa.blade.php.
 */
class SiswaDashboardController extends Controller
{
    public function index(): View
    {
        $siswa = $this->siswa();

        return $this->tampil('siswa.dashboard', [
            'siswa' => $siswa,
            'diumumkan' => PendaftaranSiswa::kelulusanDiumumkan(), // kartu "Pengumuman Kelulusan"
            'jumlahPesanBaru' => $siswa->pesan()->dariAdmin()->belumDibaca()->count(),
            'jumlahPesanSaya' => $siswa->pesan()->dariPeserta()->count(),
        ]);
    }

    public function profil(): View
    {
        return $this->tampil('siswa.profil');
    }

    /** Menu "Ganti Password": peserta mengganti password sendiri (butuh password lama) */
    public function gantiPassword(): View
    {
        return $this->tampil('siswa.ganti-password');
    }

    public function simpanGantiPassword(Request $request): RedirectResponse
    {
        \App\Support\GantiPasswordSendiri::simpan($request, $this->siswa());

        return redirect()->route('siswa.ganti-password')->with('password_diganti', true);
    }

    public function kelulusan(): View
    {
        // Kelulusan AKHIR dauroh; hasil baru terlihat setelah admin menekan "Umumkan"
        return $this->tampil('siswa.kelulusan', ['diumumkan' => PendaftaranSiswa::kelulusanDiumumkan()]);
    }

    /** Pesan khusus dari admin. Semua pesan yang tampil dianggap sudah dibaca. */
    public function pesanAdmin(): View
    {
        $siswa = $this->siswa();
        $pesan = $siswa->pesan()->dariAdmin()->with('dibalas')->latest()->paginate(15);

        $baru = $pesan->getCollection()->whereNull('dibaca_at')->pluck('id');
        // Saat admin "Masuk sebagai" (hanya melihat), pesan tidak ditandai dibaca
        if ($baru->isNotEmpty() && ! \App\Support\MasukSebagai::aktif()) {
            PesanPeserta::whereIn('id', $baru)->update(['dibaca_at' => now()]);
        }

        return $this->tampil('siswa.pesan-admin', ['siswa' => $siswa, 'pesan' => $pesan, 'idBaru' => $baru->all()]);
    }

    /** Kirim pesan untuk admin + riwayat pesan beserta balasannya */
    public function pesanSaya(): View
    {
        $pesan = $this->siswa()->pesan()->dariPeserta()->with('balasan')->latest()->paginate(10);

        return $this->tampil('siswa.pesan-saya', compact('pesan'));
    }

    public function kirimPesan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['nullable', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:2000'],
        ], ['isi.required' => 'Tulis pesan Anda terlebih dahulu.', 'isi.max' => 'Pesan maksimal 2000 karakter.']);

        $this->siswa()->pesan()->create($data + ['dari' => PesanPeserta::DARI_PESERTA]);

        return redirect()->route('siswa.pesan-saya')->with('success', 'Pesan terkirim ke admin. Balasan akan muncul di halaman ini.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /* ---------------- Bantuan ---------------- */

    private function siswa(): PendaftaranSiswa
    {
        return PendaftaranSiswa::with(['kelasMaster', 'kamarMaster'])->where('user_id', Auth::id())->firstOrFail();
    }

    private function tampil(string $view, array $data = []): View
    {
        $siswa = $data['siswa'] ?? $this->siswa();

        return view($view, $data + [
            'siswa' => $siswa,
            'user' => Auth::user(),
            'notif' => $this->notifikasi($siswa),
        ]);
    }

    /** Lonceng: pesan admin yang belum dibaca + balasan admin + status kelulusan */
    private function notifikasi(PendaftaranSiswa $siswa): array
    {
        $items = [];
        foreach ($siswa->pesan()->dariAdmin()->belumDibaca()->latest()->take(5)->get() as $p) {
            $items[] = [
                'kunci' => 'pesan:' . $p->id,
                'teks' => ($p->balasan_untuk_id ? 'Admin membalas pesan Anda: ' : 'Pesan baru dari admin: ') . '<b>' . \App\Support\TeksAman::e(\Illuminate\Support\Str::limit($p->judul ?: $p->isi, 40)) . '</b>',
                'ket' => $p->created_at->diffForHumans(),
                'url' => route('siswa.pesan-admin'),
                'warna' => 'bg-sky-50 text-sky-600 ring-sky-100',
                'ikon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
            ];
        }
        // Kelulusan akhir dauroh baru diumumkan (14 hari terakhir)
        $diumumkan = PendaftaranSiswa::kelulusanDiumumkan();
        if ($siswa->status === PendaftaranSiswa::DITERIMA && $diumumkan?->gt(now()->subDays(14))) {
            $lulus = $siswa->status_kelulusan === PendaftaranSiswa::LULUS;
            $items[] = [
                'kunci' => 'kelulusan:' . $diumumkan->timestamp,
                'teks' => 'Pengumuman kelulusan sudah dibuka: <b>' . ($lulus ? 'LULUS' : ($siswa->status_kelulusan ? 'BELUM LULUS' : 'lihat hasilnya')) . '</b>',
                'ket' => $diumumkan->locale('id')->diffForHumans(),
                'url' => route('siswa.kelulusan'),
                'warna' => $lulus ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-amber-50 text-amber-600 ring-amber-100',
                'ikon' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342',
            ];
        }

        return $items;
    }
}
