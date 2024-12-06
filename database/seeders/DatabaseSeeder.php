<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ArsipSeeder;
use Database\Seeders\LokerSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\RiwayatSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            LokerSeeder::class,
            ArsipSeeder::class,
            RiwayatSeeder::class
        ]);

    }

}
