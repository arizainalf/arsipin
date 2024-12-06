<?php

namespace Database\Factories;

use App\Models\Riwayat;
use App\Models\Arsip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RiwayatFactory extends Factory
{
    protected $model = Riwayat::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'arsip_id' => Arsip::factory(), // Generate arsip_id using ArsipFactory
            'jenis' => $this->faker->randomElement(['Masuk', 'Keluar']),
            'tanggal' => $this->faker->date(),
            'catatan' => $this->faker->sentence(),
        ];
    }
}
