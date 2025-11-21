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
     * Menampilkan halaman utama (daftar siswa, form edit).
     * [PERBAIKAN]: Method ini sekarang juga menangani load data awal & AJAX.
     */
    public function index(Request $request, $class_list_id, $class_user_id = 0)
    {
        // 1. Ambil Info Detail Kelas
        $class_info = $this->getClassInfo($class_list_id);
        if (!$class_info) {
            return redirect()->route('class_list.index')->with('error', 'Kelas tidak ditemukan.');
        }

        // 2. Ambil data dropdown tahun ajaran (untuk fitur copy)
        $all_years = DB::table('sis_cyear')->orderBy('date_start', 'desc')->get();

        // 3. Siapkan data untuk form Edit
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
            if ($data) { $edit_data = $data; }
        }

        // 4. Ambil Daftar Siswa di Kelas (Query Utama)
        $search = $request->query('search');
        $perPage = $request->query('perPage', 10); // [BARU] Default 10 jika tidak ada input

        $query = DB::table('sis_class_user as cu')
            ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->where('cu.class_list_id', $class_list_id)
            ->where('u.is_active', 'yes')
            ->select(
                'cu.id as class_user_id', 
                'u.id as user_id', 
                'u.fullname', 
                'u.placeofbirth', 
                'u.dateofbirth', 
                'cu.is_active as class_is_active',
                'u.is_active as user_is_active'
            );

        if ($search) {
            $query->where('u.fullname', 'like', '%' . $search . '%');
        }

        // [PERBAIKAN] Gunakan variabel $perPage di sini
        $list_data = $query->orderBy('u.fullname', 'asc')
                           ->paginate($perPage)
                           ->withQueryString(); 

        // 5. Handle AJAX request
        if ($request->ajax()) {
            return view('admin.class_user._student_table', [
                'list_data' => $list_data,
                'class_list_id' => $class_list_id
            ]);
        }

        // 6. Tampilkan view utama
        return view('admin.class_user.index', [
            'class_info' => $class_info,
            'all_years' => $all_years,
            'other_classes' => [], // Kosongkan awal (akan diisi via AJAX saat pilih tahun)
            'edit_data' => $edit_data,
            'class_list_id' => $class_list_id,
            'list_data' => $list_data,
            'perPage' => $perPage // [BARU] Kirim nilai perPage ke view agar dropdown sesuai
        ]);
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
        $student_ids = $request->student_ids; 
        
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
                    'custom_field' => $cu->custom_field ?? '',
                    'update_by' => session('user_id'),
                    'created' => now(),
                    'updated' => now(),
                    // [PERBAIKAN] Semua kolom scholarship DIHAPUS
                ];
            }
        }
        
        if (!empty($newEntries)) {
            DB::table('sis_class_user')->insert($newEntries);
            
            // [PENTING] Sinkronisasi tagihan untuk siswa yang disalin
            foreach ($newEntries as $entry) {
                $this->syncPayItemsForStudent($entry['user_id'], $new_class_id);
            }

            return redirect()->route('class_user.index', $new_class_id)
                             ->with('success', count($newEntries) . ' siswa berhasil disalin ke kelas baru.');
        }

        return redirect()->route('class_user.index', $class_list_id)
                         ->with('error', 'Tidak ada siswa yang disalin (mungkin sudah ada di kelas tujuan).');
    }

    /**
     * [HELPER] Sinkronisasi PayItem 
     */
    private function syncPayItemsForStudent($user_id, $class_list_id)
    {
        // 1. Hapus item bayar siswa yang bentrok dengan item bayar kelas
        DB::table('sis_userpayitem')
            ->where('user_id', $user_id)
            ->whereIn('payitem_id', function ($query) use ($class_list_id) {
                $query->select('payitem_id')
                    ->from('sis_classpayitem')
                    ->where('class_list_id', $class_list_id);
            })
            ->delete();

        // 2. Salin item pembayaran dari KELAS ke SISWA
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
                'coa_cash2' => 0,
                'coa_receivable' => $pay->coa_receivable,
                'coa_payable' => $pay->coa_payable,
                'coa_revenue' => $pay->coa_revenue,
                'coa_cost' => $pay->coa_cost,
                'payvalue' => $pay->payvalue,
                'pay_repeat' => $pay->pay_repeat,
                'pay_start' => $pay->pay_start,
                'pay_end' => $pay->pay_end,
                'note' => $pay->note,
                'custom_field' => null,
                'is_active' => 'yes',
            ];
        }

        if (!empty($userPayItems)) {
            DB::table('sis_userpayitem')->insert($userPayItems);
        }
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
            ->leftJoin('sis_cgrade as grade', 'cl.cgrade_id', '=', 'grade.id') 
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
                'cl.cyear_id', 
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
     * [AJAX] Ambil daftar kelas berdasarkan tahun ajaran (kecuali kelas saat ini)
     */
    public function ajaxGetClassesByYear(Request $request)
    {
        $cyear_id = $request->query('cyear_id');
        $current_class_id = $request->query('current_class_id');

        if (!$cyear_id) return response()->json([]);

        $classes = DB::table('sis_class_list')
            ->where('cyear_id', $cyear_id)
            ->where('id', '!=', $current_class_id) // Jangan tampilkan kelas ini sendiri
            ->orderBy('title', 'asc')
            ->select('id', 'title')
            ->get();

        return response()->json($classes);
    }

    /**
     * Export Data Siswa per Kelas ke Excel
     */
    public function exportExcel($class_list_id)
    {
        // 1. Ambil Info Kelas (untuk nama file)
        $class_info = DB::table('sis_class_list')->where('id', $class_list_id)->first();
        $file_name = 'Data_Siswa_Kelas_' . str_replace(' ', '_', $class_info->title) . '.xls';

        // 2. Query Data (Replikasi dari kode lama 'exp_classuser')
        $data = DB::table('sis_class_list as cl')
            ->join('sis_class_user as cu', 'cl.id', '=', 'cu.class_list_id')
            ->join('sis_user as u', 'cu.user_id', '=', 'u.id')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_user as ayah', 's.father_id', '=', 'ayah.id')
            ->leftJoin('sis_user as ibu', 's.mother_id', '=', 'ibu.id')
            ->where('cl.id', $class_list_id)
            ->where('u.is_active', 'yes') // Hanya siswa aktif
            ->select(
                'cl.title as kelas',
                'u.fullname as nama',
                'u.gender as jenis_kelamin',
                'u.placeofbirth as tempat_lahir',
                'u.dateofbirth as tanggal_lahir',
                's.nis',
                'u.street as jalan',
                'u.city as kota',
                'ayah.fullname as ayah',
                'ibu.fullname as ibu',
                'u.home_phone as tlp_rumah',
                'u.mobile_phone as hp'
            )
            ->orderBy('u.fullname', 'asc')
            ->get();

        // 3. Generate Output HTML Table (Excel akan membacanya sebagai tabel)
        return response()->streamDownload(function() use ($data) {
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<table border="1" cellpadding="5" cellspacing="0">';
            
            // Header Tabel
            echo '<tr style="background-color: #f2f2f2; font-weight: bold;">
                    <th>No</th>
                    <th>Kelas</th>
                    <th>Nama Siswa</th>
                    <th>L/P</th>
                    <th>Tempat Lahir</th>
                    <th>Tanggal Lahir</th>
                    <th>NIS</th>
                    <th>Alamat</th>
                    <th>Kota</th>
                    <th>Nama Ayah</th>
                    <th>Nama Ibu</th>
                    <th>Tlp Rumah</th>
                    <th>HP</th>
                  </tr>';

            // Isi Data
            $no = 1;
            foreach ($data as $row) {
                // Konversi Gender (F->P, M->L) sesuai kode lama
                $gender = ($row->jenis_kelamin == 'F') ? 'P' : 'L';
                
                // Format Tanggal
                $tgl_lahir = ($row->tanggal_lahir && $row->tanggal_lahir != '0000-00-00') 
                             ? date('d-m-Y', strtotime($row->tanggal_lahir)) 
                             : '-';

                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . $row->kelas . '</td>';
                echo '<td>' . $row->nama . '</td>';
                echo '<td style="text-align:center;">' . $gender . '</td>';
                echo '<td>' . $row->tempat_lahir . '</td>';
                echo '<td>' . $tgl_lahir . '</td>'; // Excel otomatis mendeteksi format tanggal
                echo '<td style="mso-number-format:\'\@\'">' . $row->nis . '</td>'; // Format Text agar 0 di depan tidak hilang
                echo '<td>' . $row->jalan . '</td>';
                echo '<td>' . $row->kota . '</td>';
                echo '<td>' . $row->ayah . '</td>';
                echo '<td>' . $row->ibu . '</td>';
                echo '<td style="mso-number-format:\'\@\'">' . $row->tlp_rumah . '</td>';
                echo '<td style="mso-number-format:\'\@\'">' . $row->hp . '</td>';
                echo '</tr>';
            }

            echo '</table></body></html>';
        }, $file_name, [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"$file_name\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}