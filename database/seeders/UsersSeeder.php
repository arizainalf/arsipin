<?php

namespace Database\Seeders;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [[
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('11221122'),
        ],
        [
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'Ari Zainal Fauziah',
            'email' => 'arizainalf@gmailcom',
            'password' => bcrypt('11221122'),
        ]];

        DB::table('users')->insert($users);
    }
}
