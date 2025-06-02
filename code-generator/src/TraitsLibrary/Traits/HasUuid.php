<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait HasUuid
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getUuidKeyName(): string
    {
        return 'uuid';
    }

    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), $model->getUuidKeyName())) {
                $model->{$model->getUuidKeyName()} = (string) Str::uuid();
            }
        });
    }
}
