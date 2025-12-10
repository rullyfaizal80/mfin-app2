<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolReportController extends Controller
{
    /**
     * HALAMAN UTAMA (INDEX)
     */
    public function index(Request $request)
    {
        // 1. QUERY KELAS (Tetap sama)
        $classList = DB::table('sis_class_list as cl')
            ->join('sis_csubject as s', 'cl.csubject_id', '=', 's.id')
            ->join('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id') 
            ->select('cl.id', 'cl.title', 's.title as subject', 'cy.title as year_title')
            ->orderBy('cy.date_start', 'desc')
            ->orderBy('cl.title', 'asc')
            ->get();

        $selectedClass = null;

        // 2. Default Checkbox
        if (!$request->has('filter_btn') && !$request->has('page')) {
            $request->merge([
                'nis' => 'on', 'fullname_sis' => 'on', 'old' => 'on',      
                'adress' => 'on', 'no_hp' => null, 'fullname_par' => null 
            ]);
        }

        // 3. QUERY UTAMA SISWA
        $query = DB::table('sis_user as u')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            // PERBAIKAN 1: Gunakan leftJoin agar siswa yang belum punya kelas tetap terdeteksi
            ->leftJoin('sis_class_user as cu', 'u.id', '=', 'cu.user_id') 
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->select(
                'u.id as user_id', 'u.fullname', 'u.placeofbirth', 'u.dateofbirth',
                'u.mobile_phone', 'u.home_phone', 'u.gender',
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                's.nis',
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            );

        // PERBAIKAN 2: Tambahkan filter global 'is_student' (Sesuai kode CI lama)
        // Pastikan kolom ini ada di tabel sis_user (u) atau sis_student (s).
        // Jika error "Column not found", coba ganti jadi 's.is_student'
        $query->where('u.is_student', 'yes'); 

        // 4. FILTER LOGIC
        $classId = $request->fclass_list;

        if ($request->filled('fclass_list') && $classId != '0' && $classId != 'all') {
            
            // --- JIKA PILIH KELAS SPESIFIK ---
            $selectedClass = $classList->where('id', $classId)->first();
            
            $query->where('cu.class_list_id', $classId);
            $query->orderBy('u.fullname', 'asc');

        } else {
            
            // --- JIKA PILIH SEMUA SISWA ---
            // PERBAIKAN 3: Hapus filter 'is_active' yang ketat. 
            // Kita hanya mengandalkan filter 'is_student' di atas.
            
            $query->distinct(); // Agar siswa tidak muncul double
            $query->orderBy('u.fullname', 'asc');
        }

        // 5. Eksekusi
        $students = $query->paginate(50)->withQueryString();

        return view('school.report.index', [
            'classList' => $classList,
            'students'  => $students,
            'selectedClass' => $selectedClass,
            'req' => $request
        ]);
    }

    /**
     * HALAMAN CETAK (PRINT)
     */
    public function print(Request $request)
    {
        $classId = $request->fclass_list;

        // 1. Info Kelas Header
        $classTitle = "Semua Siswa Aktif";
        $subjectTitle = "";

        // Jika memilih kelas spesifik
        if ($classId && $classId != '0' && $classId != 'all') {
            $classInfo = DB::table('sis_class_list as cl')
                ->join('sis_csubject as s', 'cl.csubject_id', '=', 's.id')
                ->where('cl.id', $classId)
                ->select('cl.title', 's.title as subject')
                ->first();
            
            if($classInfo){
                $classTitle = $classInfo->title;
                $subjectTitle = $classInfo->subject;
            }
        }

        // 2. Definisi Kolom yang Dipilih
        $columns = [];
        $columns['No'] = 'no'; 

        if ($request->has('nis'))           $columns['NIS'] = 'nis';
        if ($request->has('fullname_sis'))  $columns['Nama Siswa'] = 'fullname';
        if ($request->has('old'))           $columns['Umur'] = 'age';
        if ($request->has('adress'))        $columns['Alamat'] = 'full_address';
        if ($request->has('no_hp'))         $columns['No HP'] = 'mobile_phone';
        
        if ($request->has('fullname_par')) {
            $columns['Orang Tua'] = 'parents'; 
        }

        // 3. Ambil Data (Query Ulang agar konsisten dengan Index)
        $query = DB::table('sis_user as u')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->join('sis_class_user as cu', 'u.id', '=', 'cu.user_id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->select(
                'u.fullname', 'u.dateofbirth', 'u.mobile_phone',
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                's.nis',
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            );

        // Filter Logic untuk Print (Sama dengan Index)
        if ($classId && $classId != '0' && $classId != 'all') {
            $query->where('cu.class_list_id', $classId);
        } else {
            $query->where('cu.is_active', 'yes');
            // PERBAIKAN: Hapus groupBy, ganti distinct
            // $query->groupBy('u.id'); // HAPUS INI
            $query->distinct();       // GANTI INI
        }

        $students = $query->orderBy('u.fullname', 'asc')->get();

        // 4. Formatting Data (Umur & Ortu)
        $students->transform(function ($item) {
            // Hitung Umur
            $item->age = $item->dateofbirth 
                ? Carbon::parse($item->dateofbirth)->age . ' Thn' 
                : '-';
            
            // Gabung Nama Ortu
            $ayah = $item->father_name ?? '-';
            $ibu  = $item->mother_name ?? '-';
            $item->parents = "A: $ayah / I: $ibu";

            return $item;
        });

        // Struktur data untuk view print disesuaikan agar fleksibel
        return view('school.report.print', [
            'classTitle' => $classTitle,
            'subjectTitle' => $subjectTitle,
            'students' => $students,
            'columns' => $columns
        ]);
    }

    /**
     * HALAMAN DETAIL BIODATA
     */
    public function detail($user_id)
    {
        $student = DB::table('sis_user as u')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->leftJoin('sis_user as guardian', 's.parent_id', '=', 'guardian.id')
            ->where('u.id', $user_id)
            ->select(
                'u.*',
                's.nis', 's.nin', 's.height', 's.weight', 's.cronic_desease',
                's.parent_relation', 's.live_with', 's.birthorder', 's.total_sibling',
                's.distancetoschool', 's.gotoschool_with',
                'father.fullname as father_name',
                'mother.fullname as mother_name',
                'guardian.fullname as guardian_name'
            )
            ->first();

        if (!$student) abort(404);

        // Ambil Kelas Terakhir
        $className = DB::table('sis_class_user as cu')
            ->join('sis_class_list as cl', 'cu.class_list_id', '=', 'cl.id')
            ->where('cu.user_id', $user_id)
            ->orderBy('cu.id', 'desc')
            ->value('cl.title');

        return view('school.report.detail', [
            'student' => $student,
            'className' => $className ?? '-'
        ]);
    }
}