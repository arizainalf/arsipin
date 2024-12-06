<?php

namespace Database\Seeders;

use App\Models\CopyFiles;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CopyFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CopyFiles::factory()->count(50)->create();
    }
}
