<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Menampilkan halaman daftar siswa.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Query ini menggabungkan sis_user dan sis_student
        $query = DB::table('sis_user')
            ->join('sis_student', 'sis_user.id', '=', 'sis_student.id')
            ->where('sis_user.is_student', 'yes') // Hanya ambil siswa
            ->select(
                'sis_user.id',
                'sis_student.nis',
                'sis_user.fullname',
                'sis_user.dateofbirth',
                'sis_user.gender',
                'sis_user.mobile_phone',
                'sis_user.home_phone'
            );

        // Logika pencarian: berdasarkan NIS atau Nama
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_student.nis', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.fullname', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan Nama (Fullname)
        $query->orderBy('sis_user.fullname', 'asc');

        $students = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.student._student_table', ['students' => $students]);
        }

        return view('admin.student.index', [
            'students' => $students,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }
}