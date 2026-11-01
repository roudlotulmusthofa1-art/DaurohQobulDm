<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Perilaku bersama semua tabel master data:
 * - urutan otomatis (data baru di paling bawah)
 * - slug otomatis dari nama (jika model punya kolom slug)
 * - scope ordered()
 */
trait IsMasterData
{
    public static function bootIsMasterData(): void
    {
        static::creating(function (Model $model) {
            if (blank($model->urutan)) {
                $model->urutan = (int) static::max('urutan') + 1;
            }
        });

        static::saving(function (Model $model) {
            if (! in_array('slug', $model->getFillable(), true) || filled($model->slug)) {
                return;
            }

            $base = Str::slug($model->nama, '_') ?: 'item';
            $slug = $base;
            $i = 2;

            while (static::where('slug', $slug)
                ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
                ->exists()) {
                $slug = $base.'_'.$i++;
            }

            $model->slug = $slug;
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /** Nama relasi yang dihitung (mis. jumlah kitab). Null = tidak ada. */
    public static function countRelation(): ?string
    {
        return null;
    }
}
