<?php

namespace Database\Seeders;

use App\Models\Loker;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range('A', 'C') as $hurufLoker) {
            for ($nomorLoker = 1; $nomorLoker <= 4; $nomorLoker++) {
                Loker::create([
                    'uuid' => Uuid::uuid4()->toString(),
                    'nama' => $hurufLoker . '-' . $nomorLoker,
                ]);
            }
        }

        $loker = [
            'uuid' => Uuid::uuid4()->toString(),
            'nama' => 'Keluar',
        ];

        DB::table('lokers')->insert($loker);
    }
}
