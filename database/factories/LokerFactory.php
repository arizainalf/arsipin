<?php

namespace Database\Factories;

use App\Models\Loker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class LokerFactory extends Factory
{
    protected $model = Loker::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'nama' => $this->faker->unique()->bothify('?-#'), // Contoh kode seperti A-1
        ];
    }
}
