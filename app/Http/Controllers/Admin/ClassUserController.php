<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClassUserController extends Controller
{
    /**
     * Menampilkan halaman utama (daftar siswa, form tambah/edit).
     * [PERBAIKAN]: Method ini sekarang juga menangani load data awal & AJAX.
     */
    public function index(Request $request, $class_list_id, $class_user_id = 0)
    {
        // 1. Ambil Info Detail Kelas untuk Header
        $class_info = $this->getClassInfo($class_list_id);
        if (!$class_info) {
            return redirect()->route('class_list.index')->with('error', 'Kelas tidak ditemukan.');
        }

        // 2. [DIHAPUS] Query siswa yang lambat dihapus dari sini

        // Ambil daftar kelas lain di tahun ajaran yang sama (untuk "Copy")
        $other_classes = DB::table('sis_class_list')
            ->where('cyear_id', $class_info->cyear_id)
            ->where('id', '!=', $class_list_id)
            ->orderBy('title', 'asc')
            ->get();

        // 3. Ambil data untuk Form Edit (jika mode edit)
        $edit_data = (object) [
            'id' => 0,
            'user_id' => '',
            'join_start' => $class_info->year_date_start,
            'join_end' => $class_info->year_date_end,
            'is_active' => 'yes',
            'fullname' => '',
        ];
        
        if ($class_user_id != 0) {
            $data = DB::table('sis_class_user as cu')
                ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
                ->where('cu.id', $class_user_id)
                ->select('cu.*', 'u.fullname')
                ->first();
            if ($data) {
                $edit_data = $data;
            }
        }

        // 4. Ambil Daftar Siswa di Kelas
        $search = $request->query('search');
        $query = DB::table('sis_class_user as cu')
            ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->where('cu.class_list_id', $class_list_id)
            ->where('u.is_active', 'yes')
            ->select(
                'cu.id as class_user_id', 
                'u.id as user_id', 
                's.nis', 
                'u.fullname', 
                'u.placeofbirth', 
                'u.dateofbirth', 
                'u.home_phone', 
                'u.mobile_phone', 
                'cu.is_active as class_is_active',
                'u.is_active as user_is_active' // <-- Ini untuk perbaikan status
            );

        if ($search) {
            $query->where('u.fullname', 'like', '%' . $search . '%');
        }

        $list_data = $query->orderBy('u.fullname', 'asc')->paginate(10)->withQueryString();

        // 5. Handle AJAX request (untuk pagination & search)
        if ($request->ajax()) {
            return view('admin.class_user._student_table', [
                'list_data' => $list_data,
                'class_list_id' => $class_list_id
            ]);
        }

        // 6. Tampilkan view utama
        return view('admin.class_user.index', [
            'class_info' => $class_info,
            // 'students' => $students, // <-- DIHAPUS
            'other_classes' => $other_classes,
            'edit_data' => $edit_data,
            'class_list_id' => $class_list_id,
            'list_data' => $list_data
        ]);
    }

    /**
     * [DIHAPUS] Method ajaxGetStudents() dihapus karena logikanya 
     * telah digabung ke dalam method index()
     */

    /**
     * Menambah siswa baru ke kelas (DAN SINKRONISASI PEMBAYARAN)
     */
    public function store(Request $request, $class_list_id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|gt:0',
            'join_start' => 'required|date',
        ], [
            'user_id.gt' => 'Siswa wajib dipilih.'
        ]);

        // Cek duplikat
        $existing = DB::table('sis_class_user')
                        ->where('class_list_id', $class_list_id)
                        ->where('user_id', $request->user_id)
                        ->first();

        if ($existing) {
            $validator->after(function ($validator) {
                $validator->errors()->add('user_id', 'Siswa ini sudah ada di dalam kelas.');
            });
        }

        if ($validator->fails()) {
            return redirect()->route('class_user.index', $class_list_id)
                                ->withErrors($validator)
                                ->withInput();
        }

        // [LOGIKA KUNCI] Mulai Transaksi Database
        try {
            DB::transaction(function () use ($request, $class_list_id) {
                $user_id = $request->user_id;

                // 1. Masukkan siswa ke kelas
                DB::table('sis_class_user')->insert([
                    'user_id' => $user_id,
                    'class_list_id' => $class_list_id,
                    'join_start' => $request->join_start,
                    'join_end' => $request->join_end,
                    'is_active' => $request->is_active,
                    'custom_field' => '', // Sesuai SQL
                    'update_by' => session('user_id'),
                    'created' => now(),
                    'updated' => now(),
                    'scholarship' => 0.00,
                    'scholarship_note' => 0.00,
                    'scholarship_start' => '0000-00-00', // [PERBAIKAN] Sesuai SQL
                    'scholarship_end' => '0000-00-00', // [PERBAIKAN] Sesuai SQL
                ]);

                // 2. Hapus item bayar siswa yang bentrok dengan item bayar kelas
                DB::table('sis_userpayitem')
                    ->where('user_id', $user_id)
                    ->whereIn('payitem_id', function ($query) use ($class_list_id) {
                        $query->select('payitem_id')
                            ->from('sis_classpayitem')
                            ->where('class_list_id', $class_list_id);
                    })
                    ->delete();

                // 3. Salin item pembayaran dari KELAS ke SISWA
                $payitemsToCopy = DB::select(
                    "SELECT cu.user_id, cp.*
                     FROM sis_class_user cu, sis_classpayitem cp
                     WHERE cu.class_list_id = ?
                     AND cu.user_id = ?
                     AND cu.class_list_id = cp.class_list_id
                     AND cp.payitem_id NOT IN (SELECT payitem_id FROM sis_userpayitem WHERE user_id = ?)",
                    [$class_list_id, $user_id, $user_id]
                );

                $userPayItems = [];
                foreach ($payitemsToCopy as $pay) {
                    $userPayItems[] = [
                        'user_id' => $pay->user_id,
                        'payitem_id' => $pay->payitem_id,
                        'coa_cash' => $pay->coa_cash,
                        'coa_cash2' => 0, // Sesuai SQL
                        'coa_receivable' => $pay->coa_receivable,
                        'coa_payable' => $pay->coa_payable,
                        'coa_revenue' => $pay->coa_revenue,
                        'coa_cost' => $pay->coa_cost,
                        'payvalue' => $pay->payvalue,
                        'pay_repeat' => $pay->pay_repeat,
                        'pay_start' => $pay->pay_start,
                        'pay_end' => $pay->pay_end,
                        'note' => $pay->note,
                        'custom_field' => null, // Sesuai SQL
                        'is_active' => 'yes',
                    ];
                }

                if (!empty($userPayItems)) {
                    DB::table('sis_userpayitem')->insert($userPayItems);
                }
            });

        } catch (\Exception $e) {
            Log::error('Gagal tambah siswa ke kelas: ' . $e->getMessage());
            return redirect()->route('class_user.index', $class_list_id)
                                ->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
        }

        return redirect()->route('class_user.index', $class_list_id)
                         ->with('success', 'Siswa berhasil ditambahkan ke kelas dan tagihan telah disinkronkan.');
    }

    /**
     * Memperbarui data join siswa di kelas
     */
    public function update(Request $request, $class_list_id, $class_user_id)
    {
        $request->validate([
            'join_start' => 'required|date',
            'join_end' => 'required|date',
        ]);

        // [LOGIKA KUNCI] Jika non-aktif, set tgl selesai hari ini
        $join_end = $request->join_end;
        if ($request->is_active == 'no') {
            $join_end = now()->toDateString();
        }

        DB::table('sis_class_user')->where('id', $class_user_id)->update([
            'join_start' => $request->join_start,
            'join_end' => $join_end,
            'is_active' => $request->is_active,
            'update_by' => session('user_id'),
            'updated' => now(),
        ]);

        return redirect()->route('class_user.index', $class_list_id)
                         ->with('success', 'Data siswa di kelas ini berhasil diperbarui.');
    }

    /**
     * Menghapus siswa dari kelas
     */
    public function destroy($class_list_id, $class_user_id)
    {
        try {
            DB::table('sis_class_user')->where('id', $class_user_id)->delete();
            return redirect()->route('class_user.index', $class_list_id)
                             ->with('success', 'Siswa berhasil dihapus dari kelas.');
        } catch (\Exception $e) {
            return redirect()->route('class_user.index', $class_list_id)
                             ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * Menyalin siswa terpilih ke kelas lain
     */
    public function copyStudents(Request $request, $class_list_id)
    {
        $request->validate([
            'class_copy_id' => 'required|integer|gt:0',
            'student_ids' => 'required|array|min:1',
        ],[
            'class_copy_id.gt' => 'Kelas tujuan wajib dipilih.',
            'student_ids.required' => 'Minimal satu siswa harus dipilih.'
        ]);

        $new_class_id = $request->class_copy_id;
        $student_ids = $request->student_ids; // array [user_id_1, user_id_2]
        
        $class_users_to_copy = DB::table('sis_class_user')
                                ->where('class_list_id', $class_list_id)
                                ->whereIn('user_id', $student_ids)
                                ->get();

        $newEntries = [];
        foreach ($class_users_to_copy as $cu) {
            // Cek duplikat di kelas baru
            $exists = DB::table('sis_class_user')
                        ->where('class_list_id', $new_class_id)
                        ->where('user_id', $cu->user_id)
                        ->exists();
            
            if (!$exists) {
                $newEntries[] = [
                    'user_id' => $cu->user_id,
                    'class_list_id' => $new_class_id,
                    'join_start' => $cu->join_start,
                    'join_end' => $cu->join_end,
                    'is_active' => $cu->is_active,
                    'custom_field' => $cu->custom_field,
                    'update_by' => session('user_id'),
                    'created' => now(),
                    'updated' => now(),
                    'scholarship' => $cu->scholarship,
                    'scholarship_note' => $cu->scholarship_note,
                    'scholarship_start' => $cu->scholarship_start,
                    'scholarship_end' => $cu->scholarship_end,
                ];
            }
        }
        
        if (!empty($newEntries)) {
            DB::table('sis_class_user')->insert($newEntries);
            return redirect()->route('class_user.index', $new_class_id)
                             ->with('success', count($newEntries) . ' siswa berhasil disalin ke kelas baru.');
        }

        return redirect()->route('class_user.index', $class_list_id)
                         ->with('error', 'Tidak ada siswa yang disalin (mungkin sudah ada di kelas tujuan).');
    }

    /**
     * Helper untuk mengambil info detail kelas
     */
    private function getClassInfo($class_list_id)
    {
        return DB::table('sis_class_list as cl')
            ->leftJoin('sis_cschool as school', 'cl.cschool_id', '=', 'school.id')
            ->leftJoin('sis_cyear as year', 'cl.cyear_id', '=', 'year.id')
            ->leftJoin('sis_csubject as subject', 'cl.csubject_id', '=', 'subject.id')
            ->leftJoin('sis_cgrade as grade', 'cl.cgrade_id', '=', 'grade.id') // <-- [PERBAIKAN] 'g' dihapus
            ->leftJoin('sis_cgroup as grp', 'cl.cgroup_id', '=', 'grp.id')
            ->leftJoin('sis_ctype as type', 'cl.ctype_id', '=', 'type.id')
            ->leftJoin('sis_user as parent1', 'cl.parent1_id', '=', 'parent1.id')
            ->leftJoin('sis_user as parent2', 'cl.parent2_id', '=', 'parent2.id')
            ->where('cl.id', $class_list_id)
            ->select(
                'cl.title as class_title',
                'school.name as school_name',
                'year.title as year_title',
                'year.date_start as year_date_start',
                'year.date_end as year_date_end',
                'cl.cyear_id', // Dibutuhkan untuk "copy"
                'subject.title as subject_title',
                'grade.title as grade_title',
                'grp.title as group_title',
                'type.title as type_title',
                'parent1.fullname as parent1_name',
                'parent2.fullname as parent2_name'
            )
            ->first();
    }

    /**
     * [AJAX] Menangani pencarian siswa untuk Select2
     */
    public function ajaxSearchStudents(Request $request, $class_list_id = 0)
    {
        $search = $request->query('term');
        
        if (empty($search)) {
            return response()->json(['items' => []]);
        }
        
        // Ambil ID kelas dari request jika ada (ini untuk filter NOT IN)
        $class_list_id = $request->query('class_list_id', 0);

        $query = DB::table('sis_user as u')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->where('u.is_student', 'yes')
            ->where('u.is_active', 'yes')
            ->where(function ($q) use ($search) {
                $q->where('u.fullname', 'like', '%' . $search . '%')
                  ->orWhere('s.nis', 'like', '%' . $search . '%');
            });

        // Filter siswa yang sudah ada di kelas ini
        if ($class_list_id != 0) {
             $query->whereNotIn('u.id', function ($subQuery) use ($class_list_id) {
                $subQuery->select('user_id')
                    ->from('sis_class_user')
                    ->where('class_list_id', $class_list_id);
            });
        }
           
        $students = $query->select('u.id', 'u.fullname', 's.nis')
                         ->limit(50) // Batasi 50 hasil
                         ->get();

        // Format data untuk Select2
        $results = [];
        foreach ($students as $student) {
            $results[] = [
                'id' => $student->id,
                'text' => $student->fullname . ' (NIS: ' . $student->nis . ')'
            ];
        }

        return response()->json(['results' => $results]);
    }
}