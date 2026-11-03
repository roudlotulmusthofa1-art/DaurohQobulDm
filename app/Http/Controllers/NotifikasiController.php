<?php

namespace App\Http\Controllers;

use App\Support\StatusNotifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Lonceng notifikasi (semua peran): tandai dibaca / hapus. */
class NotifikasiController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kunci' => ['required', 'array', 'max:100'],
            'kunci.*' => ['required', 'string', 'max:150'],
            'aksi' => ['required', 'in:baca,hapus'],
        ]);

        StatusNotifikasi::simpan($request->user()->id, $data['kunci'], $data['aksi']);

        return response()->json(['ok' => true]);
    }
}
