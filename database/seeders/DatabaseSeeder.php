<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Loker;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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

        
        // Loop huruf dari A sampai P
        foreach (range('A', 'Z') as $hurufLoker) {
            // Loop nomor dari 1 sampai 4
            for ($nomorLoker = 1; $nomorLoker <= 4; $nomorLoker++) {
                Loker::create([
                    'uuid' => Uuid::uuid4()->toString(), // Menghasilkan UUID
                    'nama' => $hurufLoker . '-' . $nomorLoker, // Kode loker misalnya A-1, A-2, dst.
                ]);
            }
        }

        $loker = [[
            'uuid' => Uuid::uuid4()->toString(),
            'nama' => 'Keluar',
        ],[
            'uuid' => Uuid::uuid4()->toString(),
            'nama' => 'Lunas',
        ]];

        DB::table('lokers')->insert($loker);
    }
}
