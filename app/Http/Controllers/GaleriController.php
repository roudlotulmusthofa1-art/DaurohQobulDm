<?php

namespace App\Http\Controllers;

use App\Support\Galeri;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/** Halaman publik /galeri: foto unggahan admin + foto berita yang sudah tampil. ?asal=admin|berita */
class GaleriController extends Controller
{
    private const PER_HALAMAN = 24;

    public function index(Request $request): View
    {
        $asal = in_array($request->query('asal'), ['admin', 'berita'], true) ? $request->query('asal') : null;
        $semua = Galeri::semua($asal);
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        $foto = new LengthAwarePaginator(
            $semua->forPage($halaman, self::PER_HALAMAN)->values(),
            $semua->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => array_filter(['asal' => $asal])],
        );

        return view('galeri.index', compact('foto', 'asal'));
    }
}
