<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisUserLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_userlevel')->insert([
            ['id' => 1, 'level_id' => 1, 'user_id' => 39],
            ['id' => 2, 'level_id' => 4, 'user_id' => 2469],
            ['id' => 3, 'level_id' => 4, 'user_id' => 2470],
            ['id' => 5, 'level_id' => 4, 'user_id' => 2472],
            ['id' => 6, 'level_id' => 4, 'user_id' => 2471],
            ['id' => 9, 'level_id' => 4, 'user_id' => 2366],
            ['id' => 14, 'level_id' => 4, 'user_id' => 2970],
            ['id' => 35, 'level_id' => 4, 'user_id' => 3695],
            ['id' => 37, 'level_id' => 4, 'user_id' => 234],
            ['id' => 40, 'level_id' => 4, 'user_id' => 3433],
            ['id' => 50, 'level_id' => 4, 'user_id' => 4463],
            ['id' => 62, 'level_id' => 4, 'user_id' => 4482],
            ['id' => 67, 'level_id' => 4, 'user_id' => 4481],
            ['id' => 69, 'level_id' => 4, 'user_id' => 5001],
            ['id' => 73, 'level_id' => 1, 'user_id' => 1],
            ['id' => 78, 'level_id' => 4, 'user_id' => 2971],
            ['id' => 88, 'level_id' => 4, 'user_id' => 5232],
            ['id' => 94, 'level_id' => 1, 'user_id' => 4530],
            ['id' => 95, 'level_id' => 1, 'user_id' => 414],
            ['id' => 101, 'level_id' => 1, 'user_id' => 3196],
            ['id' => 104, 'level_id' => 2, 'user_id' => 5223],
            ['id' => 108, 'level_id' => 4, 'user_id' => 2733],
            ['id' => 109, 'level_id' => 1, 'user_id' => 5710],
            ['id' => 111, 'level_id' => 4, 'user_id' => 5880],
        ]);
    }
}
