<?php

namespace App\Traits;

trait BootModel
{
    protected static function booted()
    {
        $authId = null;
        if (auth('sanctum')->check()) {
            $authId = auth('sanctum')->id();
        }
        static::creating(function ($model) use ($authId) {
            $model->created_by = $authId;
        });

        self::updated(function ($model) use ($authId) {
            $model->updated_by = $authId;
        });
    }
}
