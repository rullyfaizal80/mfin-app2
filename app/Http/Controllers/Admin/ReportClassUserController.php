<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportClassUserController extends Controller
{
    /**
     * HALAMAN UTAMA: Daftar Siswa (Dengan Filter Kolom Dinamis)
     */
    public function index(Request $request, $class_list_id)
    {
        // ==========================================
        // 1. AMBIL DATA KELAS (Definisi $classList)
        // ==========================================
        $classList = DB::table('sis_class_list as cl')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->leftJoin('sis_csubject as csub', 'cl.csubject_id', '=', 'csub.id')
            ->leftJoin('sis_cgrade as cg', 'cl.cgrade_id', '=', 'cg.id')
            ->leftJoin('sis_cgroup as cgrp', 'cl.cgroup_id', '=', 'cgrp.id')
            ->leftJoin('sis_ctype as ct', 'cl.ctype_id', '=', 'ct.id')
            ->leftJoin('sis_user as p1', 'cl.parent1_id', '=', 'p1.id')
            ->where('cl.id', $class_list_id)
            ->select(
                'cl.*', 
                'sch.name as school_name', 
                'cy.title as year_title', 
                'cg.title as grade_title', 
                'cgrp.title as group_title', 
                'ct.title as type_title', 
                'p1.fullname as wali1_name'
            )
            ->first();

        // Validasi jika kelas tidak ditemukan
        if (!$classList) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        // ==========================================
        // 2. QUERY DATA SISWA (FIXED ADDRESS)
        // ==========================================
        $students = DB::table('sis_class_user as cu')
            ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->where('cu.class_list_id', $class_list_id)
            ->select(
                'cu.id as class_user_id', 'cu.is_active', 
                'u.id as user_id', 'u.fullname', 'u.placeofbirth', 'u.dateofbirth', 
                'u.home_phone', 'u.mobile_phone', 
                
                // [FIX] Address dibuat manual dari street & city
                DB::raw('CONCAT(u.street, ", ", u.city) as address'), 
                
                'u.gender', 'u.email',
                's.nis', 's.nin',
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            )
            ->orderBy('u.fullname', 'asc')
            ->get();

        // ==========================================
        // 3. FILTER DEFAULT CHECKBOX
        // ==========================================
        if (!$request->has('filter_applied')) {
            $request->merge([
                'f_nis' => 'on',
                'f_name' => 'on',
                'f_gender' => 'on'
            ]);
        }

        // ==========================================
        // 4. RETURN VIEW
        // ==========================================
        return view('reports.class_user.index', [
            'classList' => $classList,      // Variabel ini sekarang sudah pasti ada
            'students' => $students,
            'class_list_id' => $class_list_id,
            'req' => $request 
        ]);
    }

    /**
     * PROSES CETAK (Fixed: Gender L/P & Address)
     */
    public function print(Request $request, $class_list_id)
    {
        // 1. Ambil Info Kelas (QUERY DIPERLENGKAP)
        $classList = DB::table('sis_class_list as cl')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            // [TAMBAHAN JOIN]
            ->leftJoin('sis_cgrade as cg', 'cl.cgrade_id', '=', 'cg.id')
            ->leftJoin('sis_cgroup as cgrp', 'cl.cgroup_id', '=', 'cgrp.id')
            ->leftJoin('sis_ctype as ct', 'cl.ctype_id', '=', 'ct.id')
            ->leftJoin('sis_user as p1', 'cl.parent1_id', '=', 'p1.id') // Wali Kelas
            ->where('cl.id', $class_list_id)
            ->select(
                'cl.title', 
                'sch.name as school_name', 
                'cy.title as year_title',
                // [TAMBAHAN SELECT]
                'cg.title as grade_title', 
                'cgrp.title as group_title', 
                'ct.title as type_title', 
                'p1.fullname as wali_name'
            )
            ->first();

        if (!$classList) abort(404);

        // 2. Tentukan Kolom yang akan dicetak
        $columns = [];
        $columns['No'] = 'no'; 

        if ($request->has('f_nis'))     $columns['NIS'] = 'nis';
        if ($request->has('f_nin'))     $columns['NISN'] = 'nin';
        if ($request->has('f_name'))    $columns['Nama Lengkap'] = 'fullname';
        
        // Gender Indonsia
        if ($request->has('f_gender'))  $columns['L/P'] = 'gender_indo';
        
        if ($request->has('f_born'))    $columns['TTL'] = 'born';
        if ($request->has('f_phone'))   $columns['Telp/HP'] = 'phone';
        if ($request->has('f_address')) $columns['Alamat'] = 'address_full';
        if ($request->has('f_father'))  $columns['Nama Ayah'] = 'father_name';
        if ($request->has('f_mother'))  $columns['Nama Ibu'] = 'mother_name';
        if ($request->has('f_email'))   $columns['Email'] = 'email';

        // 3. Query Data Siswa (Sama seperti sebelumnya)
        $students = DB::table('sis_class_user as cu')
            ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->where('cu.class_list_id', $class_list_id)
            ->select(
                'u.fullname', 'u.email', 's.nis', 's.nin',
                'father.fullname as father_name',
                'mother.fullname as mother_name',
                DB::raw("CASE WHEN u.gender = 'M' OR u.gender = 'L' THEN 'L' ELSE 'P' END as gender_indo"),
                DB::raw("CONCAT(u.placeofbirth, ', ', IFNULL(DATE_FORMAT(u.dateofbirth, '%d-%m-%Y'), '-')) as born"),
                DB::raw("CONCAT(IFNULL(u.home_phone,'-'), ' / ', IFNULL(u.mobile_phone,'-')) as phone"),
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as address_full")
            )
            ->orderBy('u.fullname', 'asc')
            ->get();

        return view('reports.class_user.print_list', [
            'classList' => $classList,
            'students' => $students,
            'columns' => $columns
        ]);
    }
    /**
     * CETAK BIODATA SATU SISWA (Profil Lengkap)
     */
    public function printStudent($user_id)
    {
        // Ambil data siswa lengkap dengan data orang tua
        $student = DB::table('sis_user as u')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->leftJoin('sis_user as guardian', 's.parent_id', '=', 'guardian.id')
            ->where('u.id', $user_id)
            ->select(
                'u.*', 
                's.nis', 
                's.nin',
                // HAPUS 's.school_prev' dan 's.year_in' karena tidak ada di DB
                
                'father.fullname as father_name', 
                'father.mobile_phone as father_phone',
                'mother.fullname as mother_name', 
                'mother.mobile_phone as mother_phone',
                'guardian.fullname as guardian_name', 
                'guardian.mobile_phone as guardian_phone'
            )
            ->first();

        if (!$student) abort(404);

        return view('reports.class_user.print_student', ['student' => $student]);
    }
}