<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arsip;

class ArsipSeeder extends Seeder
{
    public function run(): void
    {
        Arsip::factory()->count(50)->create(); // Sesuaikan jumlah data yang diinginkan
    }
}
