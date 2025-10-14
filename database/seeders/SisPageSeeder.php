<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisPageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_page')->insert([
            // 🏫 Modul Sekolah
            ['id' => 510, 'name' => 'student', 'title' => 'Siswa & Guru', 'link' => 'user/student', 'parent_id' => 0, 'ordering' => 1, 'icon' => 'page.png', 'description' => '', 'enabled' => 1, 'is_menu' => 1, 'application_id' => 1],
            ['id' => 511, 'name' => 'student', 'title' => 'Siswa', 'link' => 'user/student', 'parent_id' => 510, 'ordering' => 1, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 1],
            ['id' => 512, 'name' => 'parents', 'title' => 'Orang Tua / Wali Murid', 'link' => 'user/parents', 'parent_id' => 510, 'ordering' => 2, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 1],

            // 💰 Modul Kasir
            ['id' => 700, 'name' => 'trans', 'title' => 'Pembayaran Siswa', 'link' => 'fincom/payment/', 'parent_id' => 0, 'ordering' => 1, 'icon' => '', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 2],
            ['id' => 702, 'name' => 'payment_create', 'title' => 'Entri Pembayaran Siswa', 'link' => 'fincom/payment/create/', 'parent_id' => 700, 'ordering' => 1, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 2],
            ['id' => 703, 'name' => 'payment_list', 'title' => 'Daftar Pembayaran Siswa', 'link' => 'fincom/payment', 'parent_id' => 700, 'ordering' => 2, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 2],

            // 📒 Modul Keuangan
            ['id' => 640, 'name' => 'payroll_teacher', 'title' => 'Keuangan Guru', 'link' => 'payroll/loan', 'parent_id' => 0, 'ordering' => 1, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 5],
            ['id' => 643, 'name' => 'salary', 'title' => 'Penggajian', 'link' => 'fincom/salary/period', 'parent_id' => 640, 'ordering' => 1, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 5],

            // ⚙️ Modul Admin
            ['id' => 900, 'name' => 'admin', 'title' => 'User', 'link' => 'admin/user', 'parent_id' => 0, 'ordering' => 1, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 4],
            ['id' => 902, 'name' => 'group', 'title' => 'Grup', 'link' => 'admin/group', 'parent_id' => 0, 'ordering' => 2, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 4],
            ['id' => 903, 'name' => 'acl', 'title' => 'Akses Kontrol', 'link' => 'admin/acl', 'parent_id' => 0, 'ordering' => 3, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 4],
            ['id' => 905, 'name' => 'level', 'title' => 'Level', 'link' => 'admin/level', 'parent_id' => 0, 'ordering' => 4, 'icon' => 'page.png', 'description' => null, 'enabled' => 1, 'is_menu' => 1, 'application_id' => 4],
        ]);
    }
}
