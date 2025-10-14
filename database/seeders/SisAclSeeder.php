<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisAclSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_acl')->insert([
            // 🔹 Administrator: akses penuh ke semua menu
            ['id' => 1, 'group_id' => 1, 'page_id' => 100, 'acl_level' => 'ro'],
            ['id' => 2, 'group_id' => 1, 'page_id' => 200, 'acl_level' => 'ro'],
            ['id' => 3, 'group_id' => 1, 'page_id' => 300, 'acl_level' => 'ro'],

            // 🔹 Kasir: akses menu transaksi & keuangan
            ['id' => 4, 'group_id' => 4, 'page_id' => 700, 'acl_level' => 'ro'],
            ['id' => 5, 'group_id' => 4, 'page_id' => 710, 'acl_level' => 'ro'],
            ['id' => 6, 'group_id' => 4, 'page_id' => 720, 'acl_level' => 'ro'],

            // 🔹 Keuangan: akses laporan & pengeluaran
            ['id' => 7, 'group_id' => 2, 'page_id' => 500, 'acl_level' => 'ro'],
            ['id' => 8, 'group_id' => 2, 'page_id' => 510, 'acl_level' => 'ro'],
            ['id' => 9, 'group_id' => 2, 'page_id' => 520, 'acl_level' => 'ro'],
        ]);
    }
}
