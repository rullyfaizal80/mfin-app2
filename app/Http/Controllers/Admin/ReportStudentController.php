<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStudentController extends Controller
{
    /**
     * HALAMAN UTAMA (INDEX)
     */
    public function index(Request $request)
    {
        // 1. AMBIL DATA SEKOLAH
        $schools = DB::table('sis_cschool')->orderBy('name', 'asc')->get();

        // 2. QUERY UTAMA
        // Menggunakan logic "Left Join" biasa agar semua history (duplikat) tampil
        // Ini juga memperbaiki masalah Filter Sekolah yang kosong karena sebelumnya terlalu ketat
        $query = DB::table('sis_student as s')
            ->join('sis_user as u', 's.id', '=', 'u.id')
            
            // JOIN CLASS USER (Tanpa filter is_active agar seperti aplikasi lama/duplikat muncul)
            ->leftJoin('sis_class_user as cu', 'u.id', '=', 'cu.user_id')
            ->leftJoin('sis_class_list as cl', 'cu.class_list_id', '=', 'cl.id')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            
            // JOIN ORTU
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')

            ->select(
                'u.id as user_id',
                's.nis',
                'u.fullname',
                'u.dateofbirth', // Pastikan ini terambil
                'u.street', 
                'u.city',
                
                // Alamat Gabungan
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                
                'cl.title as class_name',
                'sch.name as school_name',
                'sch.id as school_id',
                
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            )
            ->where('u.is_student', 'yes');

        // 3. FILTER LOGIC
        if ($request->filled('school_id') && $request->school_id != '0') {
            $query->where('cl.cschool_id', $request->school_id);
        }
        if ($request->filled('snis')) {
            $query->where('s.nis', 'like', '%' . $request->snis . '%');
        }
        if ($request->filled('snama')) {
            $query->where('u.fullname', 'like', '%' . $request->snama . '%');
        }
        if ($request->filled('sortu')) {
            $keyword = $request->sortu;
            $query->where(function($q) use ($keyword) {
                $q->where('father.fullname', 'like', '%' . $keyword . '%')
                  ->orWhere('mother.fullname', 'like', '%' . $keyword . '%');
            });
        }
        if ($request->filled('salamat')) {
            $query->where('u.street', 'like', '%' . $request->salamat . '%')
                  ->orWhere('u.city', 'like', '%' . $request->salamat . '%');
        }

        // 4. EKSEKUSI
        $perPage = $request->input('per_page', 10);
        $students = $query->orderBy('u.fullname', 'asc')
                          ->paginate($perPage)
                          ->withQueryString();

        return view('school.reportstudent.index', [
            'schools' => $schools,
            'students' => $students,
            'req' => $request
        ]);
    }

    /**
     * HALAMAN CETAK (PRINT)
     */
    public function print(Request $request)
    {
        // Copy Query Index (Konsisten)
        $query = DB::table('sis_student as s')
            ->join('sis_user as u', 's.id', '=', 'u.id')
            ->leftJoin('sis_class_user as cu', 'u.id', '=', 'cu.user_id')
            ->leftJoin('sis_class_list as cl', 'cu.class_list_id', '=', 'cl.id')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->select(
                'u.id as user_id', 's.nis', 'u.fullname', 
                'u.dateofbirth', // Tambah ini
                'u.street', 'u.city',
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                'cl.title as class_name', 'sch.name as school_name',
                'father.fullname as father_name', 'mother.fullname as mother_name'
            )
            ->where('u.is_student', 'yes');

        // Apply Filters
        if ($request->filled('school_id') && $request->school_id != '0') {
            $query->where('cl.cschool_id', $request->school_id);
        }
        if ($request->filled('snis')) {
            $query->where('s.nis', 'like', '%' . $request->snis . '%');
        }
        if ($request->filled('snama')) {
            $query->where('u.fullname', 'like', '%' . $request->snama . '%');
        }
        if ($request->filled('sortu')) {
            $keyword = $request->sortu;
            $query->where(function($q) use ($keyword) {
                $q->where('father.fullname', 'like', '%' . $keyword . '%')
                  ->orWhere('mother.fullname', 'like', '%' . $keyword . '%');
            });
        }
        if ($request->filled('salamat')) {
            $query->where('u.street', 'like', '%' . $request->salamat . '%');
        }

        // [PERBAIKAN] LIMIT DIHILANGKAN agar semua data terambil
        // Hati-hati: 14.000 data mungkin memakan waktu load browser agak lama (3-5 detik)
        $students = $query->orderBy('u.fullname', 'asc')->get();

        $filterTitle = "Semua Sekolah";
        if($request->filled('school_id') && $request->school_id != '0'){
            $school = DB::table('sis_cschool')->where('id', $request->school_id)->first();
            if($school) $filterTitle = $school->name;
        }

        return view('school.reportstudent.print', [
            'students' => $students,
            'filterTitle' => $filterTitle,
            'req' => $request
        ]);
    }
}