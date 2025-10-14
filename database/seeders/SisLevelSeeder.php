<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_level')->insert([
            ['id' => 1, 'title' => 'Kadiv', 'grade' => 1],
            ['id' => 2, 'title' => 'Kabid', 'grade' => 2],
            ['id' => 3, 'title' => 'Ka. Biro', 'grade' => 3],
            ['id' => 4, 'title' => 'Staff', 'grade' => 4],
        ]);
    }
}
