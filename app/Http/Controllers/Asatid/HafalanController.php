<?php

namespace App\Http\Controllers\Asatid;

use App\Http\Controllers\Concerns\AksiHafalan;
use App\Http\Controllers\Controller;
use App\Models\PendaftaranAsatid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Akun Asatid > Hafalan: pilih halaqoh > santri > kitab, lihat semua setoran sebelumnya,
 * tambah baris baru (tanggal, halaman dari-sampai, ragu-ragu, kesalahan, nilai), dan tandai "sudah hafal seluruh kitab".
 * Aksi JSON di trait AksiHafalan (asatid hanya boleh mengubah / menghapus baris miliknya sendiri).
 * Hasilnya masuk ke Admin > Akademik > Hafalan.
 */
class HafalanController extends Controller
{
    use AksiHafalan;

    protected function bolehUbahSemua(): bool
    {
        return false;
    }

    /** GET /asatid/hafalan */
    public function index(Request $request)
    {
        $user = Auth::user();
        $asatid = PendaftaranAsatid::where('user_id', $user->id)->first();

        return view('asatid.hafalan', $this->dataHalaman($request) + [
            'user'   => $user,
            'asatid' => $asatid,
            'notif'  => AsatidDashboardController::notifikasi($asatid),
        ]);
    }
}
