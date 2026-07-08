<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassListController extends Controller
{
    /**
     * Menampilkan halaman utama Class List beserta data Dropdown Filter
     */
    public function index(Request $request)
    {
        $cyears = DB::table('sis_cyear')->orderBy('date_start', 'desc')->get();
        
        // Ambil tahun ajaran yang aktif (is_active = 'yes')
        // Sebagai cadangan (fallback), jika tidak ada yang aktif, sistem mengambil tahun paling terbaru.
        $active_year = DB::table('sis_cyear')->where('is_active', 'yes')->first() 
                       ?? DB::table('sis_cyear')->orderBy('date_start', 'desc')->first();

        $data = [
            'page_title'     => 'Komponen Per Kelas',
            'schools'        => DB::table('sis_cschool')->orderBy('name', 'asc')->get(),
            'grades'         => DB::table('sis_cgrade')->orderByRaw('CAST(title AS unsigned) ASC')->get(),
            'types'          => DB::table('sis_ctype')->orderBy('title', 'asc')->get(),
            'cyears'         => $cyears,
            'active_year_id' => $active_year ? $active_year->id : '-', // Menyimpan ID tahun aktif untuk diset di View
        ];

        return view('fincom.class_list.index', $data);
    }

    /**
     * Memproses data DataTables Server-side via AJAX
     */
    public function ajax(Request $request)
    {
        // Parameter standar DataTables
        $limit  = $request->input('length', 10);
        $start  = $request->input('start', 0);
        $search = $request->input('search.value');

        // Parameter Filter Custom dari view
        $f_school = $request->input('f_school');
        $f_cyear  = $request->input('f_cyear');
        $f_grade  = $request->input('f_grade');
        $f_type   = $request->input('f_type');

        // Base Query menggunakan Join sesuai struktur database
        $query = DB::table('sis_class_list as cl')
            ->leftJoin('sis_csubject as subj', 'cl.csubject_id', '=', 'subj.id')
            ->leftJoin('sis_cgrade as gr', 'cl.cgrade_id', '=', 'gr.id')
            ->leftJoin('sis_cgroup as cg', 'cl.cgroup_id', '=', 'cg.id')
            ->leftJoin('sis_ctype as ty', 'cl.ctype_id', '=', 'ty.id')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->select(
                'cl.id',
                'cl.title as class_title',
                'subj.title as subject_title',
                'gr.title as grade_title',
                'cg.title as group_title',
                'ty.title as type_title',
                'sch.name as school_name',
                'cy.title as year_title'
            );

        // --- Terapkan Filter Dropdown ---
        if (!empty($f_school) && $f_school !== '-') {
            $query->where('cl.cschool_id', $f_school);
        }
        if (!empty($f_cyear) && $f_cyear !== '-') {
            $query->where('cl.cyear_id', $f_cyear);
        }
        if (!empty($f_grade) && $f_grade !== '-') {
            $query->where('gr.title', 'like', "%{$f_grade}%");
        }
        if (!empty($f_type) && $f_type !== '-') {
            $query->where('ty.title', 'like', "%{$f_type}%");
        }

        // --- Terapkan Pencarian Global (Search Box DataTables) ---
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('cl.title', 'like', "%{$search}%")
                  ->orWhere('sch.name', 'like', "%{$search}%")
                  ->orWhere('ty.title', 'like', "%{$search}%");
            });
        }

        // Hitung total records setelah filter diaplikasikan (PENTING: Lakukan count() SEBELUM orderBy agar sorting jalan)
        $totalRecords = $query->count();

        // --- Terapkan Sorting Server-Side Berdasarkan Request ---
        $order_column_idx = $request->input('order.0.column', 0); // Default index 0 (Nama Kelas)
        $order_dir        = $request->input('order.0.dir', 'asc');   // Default urutan asc

        // Peta index kolom DataTables ke kolom riil di Database
        $columns_map = [
            0 => 'cl.title',       // Kolom index 0: Nama Kelas
            1 => 'gr.title',       // Kolom index 1: Tingkat
            2 => 'ty.title',       // Kolom index 2: Tipe Kelas
            3 => 'sch.name',       // Kolom index 3: Nama Sekolah / Unit
            4 => 'cy.date_start',  // Kolom index 4: Tahun Ajaran (Diurutkan berdasarkan tanggal aktual, bukan sekadar nama)
        ];

        // Ambil nama kolom berdasarkan peta index, jika tidak valid arahkan ke Nama Kelas
        $sort_column = $columns_map[$order_column_idx] ?? 'cl.title';
        $query->orderBy($sort_column, $order_dir);
        
        // Tarik data dengan batasan limit dan offset pagination
        $results = $query->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($results as $res) {
            // Merakit Nama Kelas & Subjek (Diubah text-muted menjadi text-body-secondary untuk Dark Mode)
            $className = '<strong>' . ($res->class_title ?? '-') . '</strong><br>' . 
                         '<small class="text-body-secondary">' . ($res->subject_title ?? '') . '</small>';
            
            // Merakit Tingkat & Grup Kelas
            $grade = ($res->grade_title ?? '') . ' ' . ($res->group_title ?? '');
            
            // Merakit Tombol Aksi (Diubah menjadi btn-outline-primary agar polos)
           $actionUrl = url('fincom/classpayitem/index/' . $res->id);
$btnAction = '<a href="' . $actionUrl . '" class="btn btn-sm btn-outline-primary fw-bold py-1 px-3 shadow-sm">' .
             'Komp. Pembayaran Kelas</a>';

            $data[] = [
                $className,
                $grade,
                $res->type_title ?? '-',
                $res->school_name ?? '-',
                $res->year_title ?? '-',
                $btnAction
            ];
        }

        // Return response dengan struktur standar JSON DataTables
        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data"            => $data
        ]);
    }
}