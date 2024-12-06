<?php

namespace Database\Factories;

use App\Models\Arsip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CopyFile>
 */
class CopyFilesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(), // Generate unique UUID
            'arsip_id' => Arsip::factory(), // Generate arsip_id using ArsipFactory
            'nama' => $this->faker->randomElement(['Dokumen A', 'Dokumen B', 'Dokumen C']),
            'keterangan' => $this->faker->regexify('[0-9]{10}'),
            'jenis' => $this->faker->randomElement(['Asli', 'Copy']), // Random Enum value
            'gambar' => $this->faker->optional()->imageUrl(640, 480, 'files', true, 'Gambar Copy File'), // Optional image URL
        ];
    }
}
