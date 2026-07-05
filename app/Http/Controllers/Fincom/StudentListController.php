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
        // 1. Ambil flash data message
        $message = session('message');

        // 2. Ambil data grades dan types
        $grades = DB::table('sis_cgrade')
            ->orderByRaw('CAST(title AS unsigned)')
            ->select('title') // Opsional, sesuaikan kebutuhan view
            ->get();

        $types = DB::table('sis_ctype')
            ->select('title')
            ->get();

        // 3. Logika Filter (Sesuai dengan if($this->input->post('filter_btn')) di CI2)
        if ($request->has('filter_btn')) {
            if ($request->filled('fclass_list') && $request->input('fclass_list') !== 'pilih') {
                // Di Laravel, gunakan routing. Asumsi rute bernama 'fincom.student_list.index'
                return redirect()->route('fincom.student_list.index', ['class_list_id' => $request->input('fclass_list')]);
            }
            $filters['fgrade'] = $request->input('fgrade');
        } else {
            // Default filter
            $filters['ffrom'] = date("Y");
            $filters['fto'] = date("Y") + 1;
            $filters['fgrade'] = "-";
        }

        // 4. Query Dropdown Kelas ($dk) - PERSIS seperti query CI2
        $dk = DB::table('sis_class_list as m')
            ->join('sis_csubject as s', 'm.csubject_id', '=', 's.id')
            ->join('sis_cyear as a', 'm.cyear_id', '=', 'a.id')
            ->where('a.is_active', 'yes')
            ->select('m.id', 'm.title as kelas', 'a.title as tahun', 's.title as subject')
            ->orderBy('m.title', 'asc')
            ->get();

        // 5. Susun data untuk dikirim ke view
        $data = [
            'class_list_id' => $class_list_id,
            'grades'        => $grades,
            'types'         => $types,
            'filters'       => $filters,
            'message'       => $message,
            'dk'            => $dk, // Tetap gunakan 'dk' agar view lama tidak perlu banyak ubah jika disalin
            'page_title'    => 'Komponen Per Siswa', // Disamakan dengan CI2
        ];

        // Pastikan Anda sudah membuat view 'fincom.student_list.index'
        return view('fincom.student_list.index', $data);
    }

    public function ax_get_student_list(Request $request, $class_list_id = 0)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // 1. Menggunakan nama tabel utuh persis CI2.
        $baseQuery = DB::table('sis_student')
            ->leftJoin('sis_user', 'sis_student.id', '=', 'sis_user.id')
            ->leftJoin('sis_class_user', 'sis_student.id', '=', 'sis_class_user.user_id')
            ->join('sis_class_list', 'sis_class_user.class_list_id', '=', 'sis_class_list.id')
            ->join('sis_cyear', 'sis_class_list.cyear_id', '=', 'sis_cyear.id')
            ->where('sis_user.is_active', 'yes')
            ->where('is_student', 'yes') // Tanpa alias agar DB mencari sendiri
            ->where('sis_cyear.is_active', 'yes');

        // Filter Dropdown
        if ($class_list_id > 0) {
            $baseQuery->where('sis_class_user.class_list_id', $class_list_id);
        }

        $recordsTotal = $baseQuery->count();

        // 2. Filter Pencarian (Tanpa alias seperti CI2)
        $filteredQuery = clone $baseQuery;
        if (!empty($search)) {
            $filteredQuery->whereRaw('(nis like ? OR fullname like ?)', ["%{$search}%", "%{$search}%"]);
        }

        $recordsFiltered = $filteredQuery->count();

        // 3. Select persis bawaan CI2
        $students = $filteredQuery->select(
                'sis_user.id as user_id', 
                'nis', 
                'fullname', 
                'dateofbirth', 
                'gender', 
                'mobile_phone', 
                'home_phone', 
                'sis_class_list.title as class_name'
            )
            ->offset($start)
            ->limit($length)
            ->orderBy('fullname', 'asc') // Urut berdasarkan nama
            ->get();

        // 4. Susun JSON DataTables
        $data = [];
        $no = $start + 1;
        foreach ($students as $row) {
            $col = [];
            $col[] = $no++;
            $col[] = $row->nis; 
            $col[] = $row->fullname;
            $col[] = $row->class_name;
            $col[] = $row->dateofbirth ? date('d M Y', strtotime($row->dateofbirth)) : '-';
            $col[] = ($row->gender == 'M') ? 'L' : 'P';
            $col[] = ($row->home_phone ?: '-') . ' / ' . ($row->mobile_phone ?: '-');
            
            // Tombol Aksi Komponen & Hapus (Hanya Notif)
$action = '<div class="btn-group">';
$action .= '<a href="'.url('fincom/userpayitem/student_list/'.$row->user_id).'" class="btn btn-sm btn-primary">Komp Persiswa</a>';

// Tombol hapus tidak mengarah ke URL, melainkan langsung memicu alert javascript
$action .= '<button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="alert(\'Aksi Ditolak: Fitur hapus siswa dinonaktifkan secara sistem untuk menjaga integritas riwayat transaksi keuangan.\')"><i class="bi bi-trash"></i></button>';
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

    /**
     * Fitur Hapus Dinonaktifkan (Sesuai Logika CI2 Asli)
     */
    public function delete($id)
    {
        // Sistem langsung menghentikan proses dan melempar notifikasi error
        return redirect()->route('fincom.student_list.index')
                         ->with('error', 'Aksi Ditolak: Fitur hapus siswa dinonaktifkan secara sistem untuk menjaga integritas riwayat transaksi keuangan.');
    }

}