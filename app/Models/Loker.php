<?php

namespace App\Models;

use App\Models\Arsip;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loker extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function arsips(){
        return $this->hasMany(Arsip::class);
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
