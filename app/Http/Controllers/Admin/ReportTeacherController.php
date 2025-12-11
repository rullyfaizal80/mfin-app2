<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportTeacherController extends Controller
{
    /**
     * HALAMAN UTAMA (INDEX)
     */
    public function index(Request $request)
    {
        // 1. QUERY UTAMA GURU
        $query = DB::table('sis_teacher as t')
            ->join('sis_user as u', 't.id', '=', 'u.id')
            ->select(
                'u.id',
                't.nik', // NIP/NIK Guru
                'u.fullname',
                'u.placeofbirth',
                'u.dateofbirth',
                'u.home_phone',
                'u.mobile_phone',
                'u.email',
                'u.is_active' // Status Aktif
            )
            ->where('u.is_teacher', 'yes'); // Hanya user bertipe guru

        // 2. FILTER LOGIC
        
        // Filter NIP (NIK)
        if ($request->filled('snik')) {
            $query->where('t.nik', 'like', '%' . $request->snik . '%');
        }

        // Filter Nama
        if ($request->filled('snama')) {
            $query->where('u.fullname', 'like', '%' . $request->snama . '%');
        }

        // Filter Masa Kerja (Tahun)
        // Logic ini meniru query CI lama: Menghitung selisih tahun dari tabel work_experience
        if ($request->filled('smasa')) {
            $masaTahun = $request->smasa;
            
            // Subquery untuk menghitung total masa kerja per guru
            $subQuery = DB::table('sis_work_experience')
                ->select('teacher_id', DB::raw('ROUND(DATEDIFF(MAX(end_date), MIN(start_date))/360) as masa_kerja'))
                ->groupBy('teacher_id');

            // Join ke Subquery
            $query->joinSub($subQuery, 'we', function ($join) {
                $join->on('t.id', '=', 'we.teacher_id');
            });

            // Filter berdasarkan hasil hitungan masa kerja
            $query->where('we.masa_kerja', $masaTahun);
        }

        // 3. EKSEKUSI (Pagination)
        $perPage = $request->input('per_page', 10);
        $teachers = $query->orderBy('u.fullname', 'asc')
                          ->paginate($perPage)
                          ->withQueryString();

        return view('reports.rep_teacher.index', [
            'teachers' => $teachers,
            'req' => $request
        ]);
    }

    /**
     * HALAMAN DETAIL (REPORT GURU)
     */
    public function detail($id)
    {
        // 1. Data Pribadi Guru
        $teacher = DB::table('sis_user as u')
            ->join('sis_teacher as t', 'u.id', '=', 't.id')
            ->where('u.id', $id)
            ->select(
                'u.*', 
                't.nik', 
                't.foundation_license_no'
            )
            ->first();

        if (!$teacher) abort(404);

        // 2. Data Pengalaman Kerja
        $experience = DB::table('sis_work_experience')
            ->where('teacher_id', $id)
            ->orderBy('start_date', 'desc')
            ->get();

        // 3. Data Pendidikan (SAYA HAPUS/KOSONGKAN AGAR TIDAK ERROR)
        // Karena di CodeIgniter lama Anda juga tidak mengambil data ini.
        $education = []; 

        return view('reports.rep_teacher.detail', [
            'teacher' => $teacher,
            'experience' => $experience,
            'education' => $education
        ]);
    }
}