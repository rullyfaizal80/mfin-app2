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
            ->select('title')
            ->get();

        $types = DB::table('sis_ctype')
            ->select('title')
            ->get();

        // 3. Logika Filter
        if ($request->has('filter_btn')) {
            if ($request->filled('fclass_list') && $request->input('fclass_list') !== 'pilih') {
                return redirect()->route('fincom.student_list.index', ['class_list_id' => $request->input('fclass_list')]);
            }
            $filters['fgrade'] = $request->input('fgrade');
        } else {
            $filters['ffrom'] = date("Y");
            $filters['fto'] = date("Y") + 1;
            $filters['fgrade'] = "-";
        }

        // 4. Query Dropdown Kelas
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
            'dk'            => $dk, 
            'page_title'    => 'Komponen Per Siswa',
        ];

        return view('fincom.student_list.index', $data);
    }

    public function ax_get_student_list(Request $request, $class_list_id = 0)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // --- 1. MENANGKAP PARAMETER SORTING DARI DATATABLES ---
        $orderColumnIndex = $request->input('order.0.column'); // Index kolom yang diklik
        $orderDir = $request->input('order.0.dir', 'asc');     // Arah asc/desc

        // Mapping index kolom tabel di View ke nama kolom di Database
        $orderableColumns = [
            0 => 'sis_user.id',          // No
            1 => 'nis',                  // NIS
            2 => 'fullname',             // Nama Lengkap
            3 => 'sis_class_list.title', // Kelas
            4 => 'dateofbirth',          // Tgl Lahir
            5 => 'gender',               // L/P
            6 => 'mobile_phone',         // Kontak
        ];

        // 2. Query Utama
        $baseQuery = DB::table('sis_student')
            ->leftJoin('sis_user', 'sis_student.id', '=', 'sis_user.id')
            ->leftJoin('sis_class_user', 'sis_student.id', '=', 'sis_class_user.user_id')
            ->join('sis_class_list', 'sis_class_user.class_list_id', '=', 'sis_class_list.id')
            ->join('sis_cyear', 'sis_class_list.cyear_id', '=', 'sis_cyear.id')
            ->where('sis_user.is_active', 'yes')
            ->where('is_student', 'yes')
            ->where('sis_cyear.is_active', 'yes');

        // Filter Dropdown Kelas
        if ($class_list_id > 0) {
            $baseQuery->where('sis_class_user.class_list_id', $class_list_id);
        }

        $recordsTotal = $baseQuery->count();

        // 3. Filter Pencarian
        $filteredQuery = clone $baseQuery;
        if (!empty($search)) {
            $filteredQuery->whereRaw('(nis like ? OR fullname like ?)', ["%{$search}%", "%{$search}%"]);
        }

        $recordsFiltered = $filteredQuery->count();

        // --- 4. TERAPKAN LOGIKA SORTING (Dinamis sesuai klik) ---
        if (isset($orderableColumns[$orderColumnIndex])) {
            $filteredQuery->orderBy($orderableColumns[$orderColumnIndex], $orderDir);
        } else {
            $filteredQuery->orderBy('fullname', 'asc'); // Default urutan jika baru dimuat
        }

        // 5. Eksekusi Pengambilan Data
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
            // (Baris ->orderBy yang kaku sudah dihapus dari sini)
            ->get();

        // 6. Susun JSON DataTables
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
            
            // --- MODIFIKASI TOMBOL AKSI ---
            $action = '<div class="btn-group">';
            // Mengubah tombol Komp Persiswa menjadi polos (outline) & menghapus tombol Hapus
            $action .= '<a href="'.url('fincom/userpayitem/student_list/'.$row->user_id).'" class="btn btn-sm btn-outline-primary fw-bold px-3">Komp Persiswa</a>';
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
     * Fitur Hapus Dinonaktifkan
     */
    public function delete($id)
    {
        return redirect()->route('fincom.student_list.index')
                         ->with('error', 'Aksi Ditolak: Fitur hapus siswa dinonaktifkan secara sistem untuk menjaga integritas riwayat transaksi keuangan.');
    }
}