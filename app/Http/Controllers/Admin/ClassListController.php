<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassListController extends Controller
{
    /**
     * Helper untuk mengambil data dropdown yang dibutuhkan di form
     */
    private function getFormData()
    {
        // [LOGIKA KUNCI] Ambil guru (Wali Kelas) dari sis_user
        $teachers = DB::table('sis_user')
                        ->where('is_teacher', 'yes')
                        ->orderBy('fullname', 'asc')
                        ->select('id', 'fullname')
                        ->get();

        $schools = DB::table('sis_cschool')->orderBy('name', 'asc')->get();
        $years = DB::table('sis_cyear')->orderBy('date_start', 'desc')->get();
        $subjects = DB::table('sis_csubject')->orderBy('title', 'asc')->get();
        $grades = DB::table('sis_cgrade')
                        ->orderBy(DB::raw("CASE WHEN title = 'TK' THEN 1 WHEN title = 'PG' THEN 2 WHEN title = '0' THEN 3 WHEN title REGEXP '^[0-9]+$' THEN 4 ELSE 5 END"))
                        ->orderBy(DB::raw('CAST(title AS UNSIGNED)'), 'asc')
                        ->orderBy('title', 'asc')
                        ->get();
        $groups = DB::table('sis_cgroup')->orderBy('title', 'asc')->get();
        $types = DB::table('sis_ctype')->orderBy('title', 'asc')->get();

        return compact('teachers', 'schools', 'years', 'subjects', 'grades', 'groups', 'types');
    }


    /**
     * Menampilkan halaman daftar kelas (dengan filter)
     */
    public function index(Request $request)
    {
        // 1. Ambil input filter
        $filters = [
            'fschool' => $request->query('fschool', '-'),
            'fcyear' => $request->query('fcyear', '-'),
            'fgrade' => $request->query('fgrade', '-'),
            'ftype' => $request->query('ftype', '-'),
            'search' => $request->query('search', ''),
        ];

        // 2. [LOGIKA KUNCI] Set default Tahun Ajaran ke yang aktif jika filter kosong
        if ($filters['fcyear'] == '-') {
            $activeYear = DB::table('sis_cyear')->where('is_active', 'yes')->first();
            if ($activeYear) {
                $filters['fcyear'] = $activeYear->id;
            }
        }

        // 3. Ambil data dropdown untuk form filter
        $filter_data = [
            'schools' => DB::table('sis_cschool')->orderBy('name', 'asc')->get(),
            'cyears' => DB::table('sis_cyear')->orderBy('date_start', 'desc')->get(),
            'grades' => DB::table('sis_cgrade')->orderBy(DB::raw('CAST(title AS UNSIGNED)'), 'asc')->get(),
            'types' => DB::table('sis_ctype')->orderBy('title', 'asc')->get(),
        ];

        // 4. Bangun query utama dengan 7 JOIN
        $query = DB::table('sis_class_list as cl')
            ->leftJoin('sis_cschool as school', 'cl.cschool_id', '=', 'school.id')
            ->leftJoin('sis_cyear as year', 'cl.cyear_id', '=', 'year.id')
            ->leftJoin('sis_csubject as subject', 'cl.csubject_id', '=', 'subject.id')
            ->leftJoin('sis_cgrade as grade', 'cl.cgrade_id', '=', 'grade.id')
            ->leftJoin('sis_cgroup as grp', 'cl.cgroup_id', '=', 'grp.id')
            ->leftJoin('sis_ctype as type', 'cl.ctype_id', '=', 'type.id')
            ->select(
                'cl.id', 
                'cl.title as class_list_title', 
                'school.name as cschool_name', 
                'year.title as cyear_title', 
                'subject.title as csubject_title', 
                'grade.title as cgrade_title', 
                'grp.title as cgroup_title', 
                'type.title as ctype_title'
            );

        // 5. Terapkan filter
        if ($filters['fschool'] != '-') { $query->where('cl.cschool_id', $filters['fschool']); }
        if ($filters['fcyear'] != '-') { $query->where('cl.cyear_id', $filters['fcyear']); }
        if ($filters['fgrade'] != '-') { $query->where('cl.cgrade_id', $filters['fgrade']); }
        if ($filters['ftype'] != '-') { $query->where('cl.ctype_id', $filters['ftype']); }
        if ($filters['search'] != '') {
            $query->where('cl.title', 'like', '%' . $filters['search'] . '%');
        }

        // 6. Ambil data (sesuai urutan kode lama)
        $list_data = $query->orderBy('cl.title', 'asc')
                           ->orderBy('grade.title', 'asc')
                           ->orderBy('grp.title', 'asc')
                           ->paginate(10)
                           ->withQueryString(); // Agar filter tetap ada di pagination

        // 7. Handle AJAX request (untuk pagination & filter)
        if ($request->ajax()) {
            return view('admin.class_list._class_list_table', ['list_data' => $list_data]);
        }

        // 8. Tampilkan view utama
        return view('admin.class_list.index', [
            'list_data' => $list_data,
            'filters' => $filters,
            'filter_data' => $filter_data
        ]);
    }

    /**
     * Menampilkan form untuk membuat kelas baru
     */
    public function create()
    {
        return view('admin.class_list.create', [
            'dropdowns' => $this->getFormData()
        ]);
    }

    /**
     * Menyimpan kelas baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'cyear_id' => 'required|integer|gt:0',
            'csubject_id' => 'required|integer|gt:0',
            'ctype_id' => 'required|integer|gt:0',
        ], [
            'cyear_id.gt' => 'Tahun Ajaran wajib dipilih.',
            'csubject_id.gt' => 'Jurusan wajib dipilih.',
            'ctype_id.gt' => 'Tipe wajib dipilih.',
        ]);

        DB::table('sis_class_list')->insert([
            'title' => $request->title,
            'cschool_id' => $request->cschool_id ?? 0,
            'cyear_id' => $request->cyear_id,
            'csubject_id' => $request->csubject_id,
            'cgrade_id' => $request->cgrade_id ?? 0,
            'cgroup_id' => $request->cgroup_id ?? 0,
            'ctype_id' => $request->ctype_id,
            'parent1_id' => $request->parent1_id ?? 0,
            'parent2_id' => $request->parent2_id ?? 0,
            'customfield' => '', // [PERBAIKAN] Kolom 'customfield' diisi string kosong
            'update_by' => session('user_id'),
            'created' => now(),
            'updated' => now(),
        ]);

        return redirect()->route('class_list.index')->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit kelas
     */
    public function edit(string $id)
    {
        $data = DB::table('sis_class_list')->find($id);
        if (!$data) {
            return redirect()->route('class_list.index')->with('error', 'Data kelas tidak ditemukan.');
        }

        return view('admin.class_list.edit', [
            'data' => $data,
            'dropdowns' => $this->getFormData()
        ]);
    }

    /**
     * Memperbarui data kelas di database
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'cyear_id' => 'required|integer|gt:0',
            'csubject_id' => 'required|integer|gt:0',
            'ctype_id' => 'required|integer|gt:0',
        ], [
            'cyear_id.gt' => 'Tahun Ajaran wajib dipilih.',
            'csubject_id.gt' => 'Jurusan wajib dipilih.',
            'ctype_id.gt' => 'Tipe wajib dipilih.',
        ]);

        DB::table('sis_class_list')->where('id', $id)->update([
            'title' => $request->title,
            'cschool_id' => $request->cschool_id ?? 0,
            'cyear_id' => $request->cyear_id,
            'csubject_id' => $request->csubject_id,
            'cgrade_id' => $request->cgrade_id ?? 0,
            'cgroup_id' => $request->cgroup_id ?? 0,
            'ctype_id' => $request->ctype_id,
            'parent1_id' => $request->parent1_id ?? 0,
            'parent2_id' => $request->parent2_id ?? 0,
            'customfield' => '', // [PERBAIKAN] Kolom 'customfield' diisi string kosong
            'update_by' => session('user_id'),
            'updated' => now(),
        ]);

        return redirect()->route('class_list.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Menghapus data kelas
     */
    public function destroy(string $id)
    {
        try {
            DB::table('sis_class_list')->where('id', $id)->delete();
            return redirect()->route('class_list.index')->with('success', 'Data kelas berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint (Error 1451)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('class_list.index')->with('error', 'Gagal menghapus: Kelas ini sudah berisi siswa.');
            }
            return redirect()->route('class_list.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}