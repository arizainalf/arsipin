<?php

namespace App\Models;

use App\Models\Loker;
use App\Models\KeluarMasuk;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Arsip extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function loker(){
        return $this->belongsTo(Loker::class);
    }

    public function copyFiles(){
        return $this->hasMany(CopyFile::class);
    }

    public function keluarMasuks(){
        return $this->hasMany(KeluarMasuk::class);
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
