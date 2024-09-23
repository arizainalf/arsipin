<?php

namespace App\Models;

use App\Models\Arsip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class CopyFiles extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
