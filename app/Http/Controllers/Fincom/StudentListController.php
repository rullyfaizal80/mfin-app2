<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentListController extends Controller
{
    /**
     * Menampilkan halaman utama daftar siswa
     */
    public function index(Request $request, $class_list_id = 0)
    {
        // 1. Ambil data untuk dropdown filter (Sesuai tpl asli)
        $grades = DB::table('sis_cgrade')
            ->orderByRaw('CAST(title AS UNSIGNED) ASC')
            ->get();

        $types = DB::table('sis_ctype')
            ->orderBy('title', 'ASC')
            ->get();

        $classes = DB::table('sis_class_list')
            ->join('sis_cyear', 'sis_cyear.id', '=', 'sis_class_list.cyear_id')
            ->where('sis_cyear.is_active', 'yes')
            ->select('sis_class_list.*')
            ->orderBy('sis_class_list.title', 'ASC')
            ->get();

        $data = [
            'page_title'    => 'Daftar Siswa',
            'class_list_id' => $class_list_id,
            'grades'        => $grades,
            'types'         => $types,
            'classes'       => $classes,
        ];

        return view('fincom.student_list.index', $data);
    }

    /**
     * Fungsi Ajax DataTable (Sudah diperbaiki untuk mencegah Ajax Error)
     */
    public function ax_get_student_list(Request $request, $class_list_id = 0)
{
    $draw   = $request->input('draw');
    $start  = $request->input('start', 0);
    $length = $request->input('length', 10);
    $search = $request->input('search.value');

    // 1. Query Dasar - Tambahkan join ke sis_cyear untuk mengunci tahun ajaran aktif
    $query = DB::table('sis_user as su')
        ->join('sis_class_user as scu', 'scu.user_id', '=', 'su.id')
        ->join('sis_class_list as scl', 'scl.id', '=', 'scu.class_list_id')
        ->join('sis_cyear as scy', 'scy.id', '=', 'scl.cyear_id') // Join ke tahun ajaran
        ->select(
            'su.id as student_id', 
            'su.username', 
            'su.fullname', 
            'su.dateofbirth', 
            'su.gender', 
            'su.home_phone', 
            'su.mobile_phone', 
            'scl.title as class_name'
        );

    // KUNCI UTAMA: Hanya ambil kelas siswa di tahun ajaran yang sedang aktif ('yes')
    // Ini otomatis membuang riwayat kelas lama siswa sehingga nama tidak duplikat
    $query->where('scy.is_active', 'yes');

    // 2. Filter Pilihan Dropdown Kelas
    if ($class_list_id > 0) {
        $query->where('scu.class_list_id', $class_list_id);
    }

    $recordsTotal = $query->count();

    // 3. Filter Search Pencarian
    if (!empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('su.username', 'like', "%{$search}%")
              ->orWhere('su.fullname', 'like', "%{$search}%");
        });
    }

    $recordsFiltered = $query->count();

    $students = $query->offset($start)
        ->limit($length)
        ->orderBy('su.fullname', 'asc')
        ->get();

    $data = [];
    $no = $start + 1;
    foreach ($students as $row) {
        $col = [];
        $col[] = $no++;
        $col[] = $row->username; 
        $col[] = $row->fullname;
        $col[] = $row->class_name;
        $col[] = $row->dateofbirth ? date('d M Y', strtotime($row->dateofbirth)) : '-';
        $col[] = ($row->gender == 'M') ? 'L' : 'P';
        $col[] = ($row->home_phone ?: '-') . ' / ' . ($row->mobile_phone ?: '-');
        
        // Tombol Aksi Komponen
        $action = '<div class="btn-group">';
        $action .= '<a href="'.url('fincom/userpayitem/student_list/'.$row->student_id).'" class="btn btn-sm btn-primary">Komp Persiswa</a>';
        $action .= '<a href="'.url('fincom/student/edit/'.$row->student_id).'" class="btn btn-sm btn-info text-white"><i class="bi bi-pencil"></i></a>';
        $action .= '</div>';
        
        $col[] = $action;
        $data[] = $col;
    }

    return response()->json([
        "draw"            => intval($draw),
        "recordsTotal"    => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data"            => $data
    ]);
}

}