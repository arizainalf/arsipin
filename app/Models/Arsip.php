<?php

namespace App\Models;

use App\Models\Loker;
use Ramsey\Uuid\Uuid;
use App\Models\Riwayat;
use App\Models\CopyFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Arsip extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function loker(){
        return $this->belongsTo(Loker::class);
    }

    public function copyFiles(){
        return $this->hasMany(CopyFiles::class);
    }

    public function riwayats(){
        return $this->hasMany(Riwayat::class);
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
