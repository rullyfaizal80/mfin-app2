<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisUserGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_usergroup')->insert([
            ['id' => 22, 'group_id' => 1, 'user_id' => 13],
            ['id' => 23, 'group_id' => 1, 'user_id' => 14],
            ['id' => 24, 'group_id' => 1, 'user_id' => 15],
            ['id' => 25, 'group_id' => 1, 'user_id' => 16],
            ['id' => 26, 'group_id' => 1, 'user_id' => 17],
            ['id' => 27, 'group_id' => 3, 'user_id' => 22],
            ['id' => 29, 'group_id' => 4, 'user_id' => 27],
            ['id' => 30, 'group_id' => 4, 'user_id' => 29],
            ['id' => 32, 'group_id' => 4, 'user_id' => 24],
            ['id' => 37, 'group_id' => 4, 'user_id' => 30],
            ['id' => 40, 'group_id' => 3, 'user_id' => 5],
            ['id' => 47, 'group_id' => 1, 'user_id' => 12],
            ['id' => 48, 'group_id' => 4, 'user_id' => 26],
            ['id' => 62, 'group_id' => 2, 'user_id' => 4],
            ['id' => 66, 'group_id' => 4, 'user_id' => 2472],
            ['id' => 67, 'group_id' => 4, 'user_id' => 2471],
            ['id' => 72, 'group_id' => 4, 'user_id' => 2970],
            ['id' => 104, 'group_id' => 4, 'user_id' => 234],
            ['id' => 107, 'group_id' => 4, 'user_id' => 3433],
            ['id' => 129, 'group_id' => 4, 'user_id' => 4463],
            ['id' => 154, 'group_id' => 3, 'user_id' => 4482],
            ['id' => 155, 'group_id' => 4, 'user_id' => 4482],
            ['id' => 166, 'group_id' => 3, 'user_id' => 4481],
            ['id' => 167, 'group_id' => 4, 'user_id' => 4481],
            ['id' => 170, 'group_id' => 3, 'user_id' => 5001],
            ['id' => 171, 'group_id' => 4, 'user_id' => 5001],
            ['id' => 179, 'group_id' => 1, 'user_id' => 1],
            ['id' => 180, 'group_id' => 101, 'user_id' => 1],
            ['id' => 188, 'group_id' => 3, 'user_id' => 2971],
            ['id' => 202, 'group_id' => 4, 'user_id' => 5232],
            ['id' => 215, 'group_id' => 101, 'user_id' => 4530],
            ['id' => 216, 'group_id' => 3, 'user_id' => 4530],
            ['id' => 217, 'group_id' => 4, 'user_id' => 4530],
            ['id' => 218, 'group_id' => 1, 'user_id' => 414],
            ['id' => 219, 'group_id' => 101, 'user_id' => 414],
            ['id' => 220, 'group_id' => 4, 'user_id' => 414],
            ['id' => 229, 'group_id' => 101, 'user_id' => 3196],
            ['id' => 230, 'group_id' => 3, 'user_id' => 3196],
            ['id' => 231, 'group_id' => 4, 'user_id' => 3196],
            ['id' => 238, 'group_id' => 101, 'user_id' => 5223],
            ['id' => 239, 'group_id' => 3, 'user_id' => 5223],
            ['id' => 240, 'group_id' => 4, 'user_id' => 5223],
            ['id' => 249, 'group_id' => 3, 'user_id' => 2733],
            ['id' => 250, 'group_id' => 4, 'user_id' => 2733],
            ['id' => 251, 'group_id' => 1, 'user_id' => 5710],
            ['id' => 252, 'group_id' => 101, 'user_id' => 5710],
            ['id' => 253, 'group_id' => 4, 'user_id' => 5710],
            ['id' => 256, 'group_id' => 3, 'user_id' => 5880],
            ['id' => 257, 'group_id' => 4, 'user_id' => 5880],
        ]);
    }
}
