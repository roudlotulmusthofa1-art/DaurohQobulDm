<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/**
 * Menu "Laporan Akademik" (belum pernah dibuat) diganti "Laporan Nilai".
 * Role yang dulu diberi izin Laporan Akademik otomatis mendapat izin Laporan Nilai.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->ganti('admin.laporan-akademik.index', 'admin.laporan-nilai.index');
    }

    public function down(): void
    {
        $this->ganti('admin.laporan-nilai.index', 'admin.laporan-akademik.index');
    }

    private function ganti(string $lama, string $baru): void
    {
        Role::query()->get()->each(function (Role $r) use ($lama, $baru) {
            $izin = (array) ($r->izin ?? []);
            if (! in_array($lama, $izin, true)) {
                return;
            }
            $r->izin = array_values(array_unique(array_map(fn ($k) => $k === $lama ? $baru : $k, $izin)));
            $r->save();
        });
    }
};
