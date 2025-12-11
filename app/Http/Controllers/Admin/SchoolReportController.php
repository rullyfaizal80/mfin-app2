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
        // 1. QUERY LIST KELAS
        $classList = DB::table('sis_class_list as cl')
            ->join('sis_csubject as s', 'cl.csubject_id', '=', 's.id')
            ->join('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id') 
            ->select('cl.id', 'cl.title', 's.title as subject', 'cy.title as year_title')
            ->orderBy('cy.date_start', 'desc')
            ->orderBy('cl.title', 'asc')
            ->get();

        $selectedClass = null;

        // 2. Default Checkbox
        if (!$request->has('filter_btn') && !$request->has('page') && !$request->has('search')) {
            $request->merge([
                'nis' => 'on', 'fullname_sis' => 'on', 'old' => 'on',      
                'adress' => 'on', 'no_hp' => null, 'fullname_par' => null 
            ]);
        }

        // 3. QUERY UTAMA (SISWA)
        $query = DB::table('sis_user as u')
            ->join('sis_student as s', 'u.id', '=', 's.id') 
            ->leftJoin('sis_class_user as cu', 'u.id', '=', 'cu.user_id') 
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->select(
                'u.id as user_id', 
                'u.fullname', 
                'u.placeofbirth', 
                'u.dateofbirth',
                'u.mobile_phone', 
                'u.home_phone', 
                'u.gender',
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                's.nis',
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            );

        // FILTER WAJIB: Hanya siswa
        $query->where('u.is_student', 'yes'); 

        // 4. FILTER KELAS
        $classId = $request->fclass_list;

        if ($request->filled('fclass_list') && $classId != '0' && $classId != 'all') {
            $selectedClass = $classList->where('id', $classId)->first();
            $query->where('cu.class_list_id', $classId);
        }

        // 4.5 FILTER PENCARIAN (NAMA / NIS)
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                $q->where('u.fullname', 'like', "%{$keyword}%")
                  ->orWhere('s.nis', 'like', "%{$keyword}%");
            });
        }

        // Urutkan nama
        $query->orderBy('u.fullname', 'asc');

        // --- UPDATE BAGIAN INI (PAGINATION DYNAMIC) ---
        
        // Ambil nilai per_page dari input, default ke 10 jika tidak ada
        $perPage = $request->input('per_page', 10); 
        
        // Validasi agar user tidak menginput angka aneh (opsional, tapi bagus untuk keamanan)
        if(!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        // Eksekusi
        $students = $query->paginate($perPage)->withQueryString();

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

        // 1. HEADER LAPORAN
        $classTitle = "SEMUA DATA SISWA AKTIF"; 
        $subjectTitle = "Tahun Ajaran: " . date('Y');

        if ($classId && $classId != '0' && $classId != 'all') {
            $classInfo = DB::table('sis_class_list as cl')
                ->join('sis_csubject as s', 'cl.csubject_id', '=', 's.id')
                ->join('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id') 
                ->where('cl.id', $classId)
                ->select('cl.title', 's.title as subject', 'cy.title as year_title')
                ->first();
            
            if($classInfo){
                $classTitle = "KELAS: " . strtoupper($classInfo->title);
                $subjectTitle = strtoupper($classInfo->subject) . " (" . $classInfo->year_title . ")";
            }
        }

        // 2. DEFINISI KOLOM (UPDATED: L/P DIPISAH)
        $columns = [];
        $columns['NO'] = 'no'; 

        if ($request->has('nis'))           $columns['NIS'] = 'nis';
        if ($request->has('fullname_sis'))  $columns['NAMA SISWA'] = 'fullname';
        
        // PERUBAHAN DISINI: Memisahkan L/P dan Tgl Lahir
        if ($request->has('old')) {
            $columns['L/P'] = 'gender';
            $columns['TGL LAHIR'] = 'dob'; 
        }

        if ($request->has('adress'))        $columns['ALAMAT'] = 'full_address';
        if ($request->has('no_hp'))         $columns['NO HP'] = 'mobile_phone';
        if ($request->has('fullname_par'))  $columns['ORANG TUA'] = 'parents'; 

        // 3. QUERY DATA
        $query = DB::table('sis_user as u')
            ->join('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_class_user as cu', 'u.id', '=', 'cu.user_id')
            ->leftJoin('sis_user as father', 's.father_id', '=', 'father.id')
            ->leftJoin('sis_user as mother', 's.mother_id', '=', 'mother.id')
            ->select(
                'u.fullname', 'u.dateofbirth', 'u.gender', 'u.mobile_phone',
                DB::raw("CONCAT(IFNULL(u.street,''), ', ', IFNULL(u.city,'')) as full_address"),
                's.nis',
                'father.fullname as father_name',
                'mother.fullname as mother_name'
            );
        
        $query->where('u.is_student', 'yes');

        if ($classId && $classId != '0' && $classId != 'all') {
            $query->where('cu.class_list_id', $classId);
        } else {
             $query->orderBy('u.fullname', 'asc');
        }

        $students = $query->orderBy('u.fullname', 'asc')->get();

        // 4. FORMATTING DATA
        $data = [];
        $no = 1;
        foreach($students as $row) {
            $item = new \stdClass();
            $item->no = $no++;
            $item->nis = $row->nis;
            $item->fullname = strtoupper($row->fullname);
            
            // PISAH DATA L/P DAN TGL LAHIR
            $item->gender = ($row->gender == 'L') ? 'L' : 'P';
            $item->dob = $row->dateofbirth ? date('d M Y', strtotime($row->dateofbirth)) : '-';
            
            $item->full_address = $row->full_address;
            $item->mobile_phone = $row->mobile_phone;

            $ayah = $row->father_name ?? '-';
            $ibu  = $row->mother_name ?? '-';
            $item->parents = "Ayah: $ayah\nIbu: $ibu"; 
            
            $data[] = $item;
        }

        return view('school.report.print', [
            'classTitle' => $classTitle,
            'subjectTitle' => $subjectTitle,
            'students' => $data,
            'columns' => $columns
        ]);
    }

    /**
     * HALAMAN DETAIL BIODATA
     */
    public function detail($user_id)
    {
        // 1. AMBIL DATA PRIBADI SISWA
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
                'father.fullname as father_name', 'father.mobile_phone as father_phone',
                'mother.fullname as mother_name', 'mother.mobile_phone as mother_phone',
                'guardian.fullname as guardian_name'
            )
            ->first();

        if (!$student) abort(404);

        // 2. AMBIL RIWAYAT KELAS (FIX: INI YANG MENYEBABKAN ERROR SEBELUMNYA)
        $historyKelas = DB::table('sis_class_user as cu')
            ->join('sis_class_list as cl', 'cu.class_list_id', '=', 'cl.id')
            ->join('sis_csubject as s', 'cl.csubject_id', '=', 's.id')
            ->join('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->where('cu.user_id', $user_id)
            ->orderBy('cy.date_start', 'desc') // Urutkan dari tahun terbaru
            ->select(
                'cl.title as class_name',
                's.title as subject',
                'cy.title as year',
                'cu.is_active'
            )
            ->get();

        // Ambil nama kelas aktif saat ini (untuk keperluan judul jika perlu)
        $activeClass = $historyKelas->where('is_active', 'yes')->first();
        $className = $activeClass ? $activeClass->class_name : '-';

        return view('school.report.detail', [
            'student' => $student,
            'className' => $className,
            'historyKelas' => $historyKelas // Variabel ini sekarang dikirim ke view
        ]);
    }
}