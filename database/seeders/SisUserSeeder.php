<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_user')->insert([
            [
                'username'      => 'admin',
                'password'      => md5('admin'), // MD5 sesuai sistem lama
                'fullname'      => 'Administrator',
                'nickname'      => 'Admin',
                'is_active'     => 'yes',
                'is_admin'      => 'yes',
                'is_teacher'    => 'no',
                'is_student'    => 'no',
                'is_parent'     => 'no',
                'is_educator'   => 'no',
                'user_type'     => 'admin',
                'created'       => now(),
                'updated'       => now(),
            ],
            [
                'username'      => 'kasir',
                'password'      => md5('kasir'),
                'fullname'      => 'Kasir Sekolah',
                'nickname'      => 'Kasir',
                'is_active'     => 'yes',
                'is_admin'      => 'no',
                'is_teacher'    => 'no',
                'is_student'    => 'no',
                'is_parent'     => 'no',
                'is_educator'   => 'no',
                'user_type'     => 'kasir',
                'created'       => now(),
                'updated'       => now(),
            ],
            [
                'username'      => 'keuangan',
                'password'      => md5('keuangan'),
                'fullname'      => 'Staff Keuangan',
                'nickname'      => 'Keuangan',
                'is_active'     => 'yes',
                'is_admin'      => 'no',
                'is_teacher'    => 'no',
                'is_student'    => 'no',
                'is_parent'     => 'no',
                'is_educator'   => 'no',
                'user_type'     => 'keuangan',
                'created'       => now(),
                'updated'       => now(),
            ],
            [
                'username'      => 'guru1',
                'password'      => md5('guru1'),
                'fullname'      => 'Guru Mata Pelajaran',
                'nickname'      => 'Guru1',
                'is_active'     => 'yes',
                'is_admin'      => 'no',
                'is_teacher'    => 'yes',
                'is_student'    => 'no',
                'is_parent'     => 'no',
                'is_educator'   => 'no',
                'user_type'     => 'guru',
                'created'       => now(),
                'updated'       => now(),
            ],
            [
                'username'      => 'siswa1',
                'password'      => md5('siswa1'),
                'fullname'      => 'Siswa Contoh',
                'nickname'      => 'Siswa1',
                'is_active'     => 'yes',
                'is_admin'      => 'no',
                'is_teacher'    => 'no',
                'is_student'    => 'yes',
                'is_parent'     => 'no',
                'is_educator'   => 'no',
                'user_type'     => 'siswa',
                'created'       => now(),
                'updated'       => now(),
            ],
            [
                'username'      => 'ortu1',
                'password'      => md5('ortu1'),
                'fullname'      => 'Orang Tua Siswa 1',
                'nickname'      => 'Ortu1',
                'is_active'     => 'yes',
                'is_admin'      => 'no',
                'is_teacher'    => 'no',
                'is_student'    => 'no',
                'is_parent'     => 'yes',
                'is_educator'   => 'no',
                'user_type'     => 'orangtua',
                'created'       => now(),
                'updated'       => now(),
            ],
        ]);
    }
}
