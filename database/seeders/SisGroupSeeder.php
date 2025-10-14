<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_group')->insert([
            ['id' => 1, 'group_name' => 'Administrator', 'ordering' => 1],
            ['id' => 2, 'group_name' => 'Keuangan', 'ordering' => 2],
            ['id' => 3, 'group_name' => 'Tata Usaha', 'ordering' => 3],
            ['id' => 4, 'group_name' => 'Kasir', 'ordering' => 4],
            ['id' => 5, 'group_name' => 'Kabid', 'ordering' => 5],
        ]);
    }
}
