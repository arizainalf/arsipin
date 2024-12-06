<?php

namespace Database\Factories;

use App\Models\Arsip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArsipFactory extends Factory
{
    protected $model = Arsip::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'loker_id' => rand(1, 12), // Assuming you have a Loker factory
            'kode' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'cif' => $this->faker->regexify('[0-9]{10}'),
            'nama_lengkap' => $this->faker->name,
            'tanggal_mulai' => $this->faker->date,
            'tanggal_selesai' => $this->faker->date,
            'status' => $this->faker->randomElement(['0', '1']),
        ];
    }
}
