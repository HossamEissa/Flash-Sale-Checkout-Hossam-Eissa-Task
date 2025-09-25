<?php

namespace App\Traits;

trait HasDiskColumn
{
    protected static function bootHasDiskColumn()
    {
        static::creating(function ($model) {
            $model->disk = $model->disk ?? config('filesystems.default', 'public');
        });

        static::updating(function ($model) {
            if (empty($model->disk)) {
                $model->disk = config('filesystems.default', 'public');
            }
        });
    }
}
