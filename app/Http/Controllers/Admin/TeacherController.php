<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        $query = DB::table('sis_user')
            ->leftJoin('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
            ->where('sis_user.is_teacher', 'yes')
            ->select(
                'sis_user.id',
                'sis_teacher.nik',
                'sis_user.fullname',
                'sis_user.mobile_phone',
                'sis_user.home_phone',
                'sis_user.email'
            );

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sis_teacher.nik', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sis_user.fullname', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->orderBy('sis_user.fullname', 'asc');
        $teachers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.teacher._teacher_table', ['teachers' => $teachers]);
        }

        return view('admin.teacher.index', [
            'teachers' => $teachers,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * Menampilkan form untuk membuat guru baru.
     */
    public function create()
    {
        return view('admin.teacher.create');
    }

    /**
     * [MODIFIKASI] Menyimpan data guru baru ke database (2 tabel).
     * Disesuaikan kembali dengan form 'Create' yang sederhana (bukan 'Edit').
     */
    public function store(Request $request)
    {
        // 1. Validasi Data (hanya NIK, Fullname, Email)
        $request->validate([
            'fullname' => 'required|string|max:45',
            'nik' => [ // NIK/NIP
                'required', 'string', 'max:45', 'alpha_dash',
                // Validasi unique NIK dihapus (sesuai keputusan kita sebelumnya)
            ],
            'email'    => [
                'nullable', 'email', 'max:45',
                Rule::unique('sis_user', 'email') // Email tetap harus unik
            ],
            // Validasi 'ktp_upload' dihapus dari sini
        ], [
            'nik.alpha_dash' => 'NIK/NIP hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 2. Gunakan Transaksi Database
        DB::transaction(function () use ($request) {
            
            // 3a. Simpan ke 'sis_user'
            $newUserId = DB::table('sis_user')->insertGetId([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'username' => $request->nik, 
                'password' => Hash::make($request->nik),
                'placeofbirth' => $request->placeofbirth,
                'dateofbirth' => $request->dateofbirth,
                'gender' => $request->gender,
                'street' => $request->street,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postalcode' => $request->postalcode,
                'home_phone' => $request->home_phone,
                'mobile_phone' => $request->mobile_phone,
                'email' => $request->email,
                'religion' => $request->religion,
                'bank_acc' => '', // [PERBAIKAN] Set default string kosong
                'is_active' => $request->is_active,
                'is_teacher' => 'yes',
                'is_parent' => 'no', // [PERBAIKAN] Set default 'no'
                'is_student' => 'no', 
                'is_admin' => 'no',
                'is_educator' => 'no',
                'is_company' => 'no',
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'), 
            ]);

            // 3b. Simpan ke 'sis_teacher'
            DB::table('sis_teacher')->insert([
                'id' => $newUserId,
                'nik' => $request->nik,
                'emp_status' => $request->emp_status ?? 'permanent',
                'att_id' => $request->att_id ?? 0,
                'grade' => $request->grade ?? '',
                'license_no' => $request->license_no ?? '',
                'foundation_license_no' => $request->foundation_license_no ?? '',
                'career_objective' => $request->career_objective ?? '',
                'skill' => $request->skill ?? '',
                'reference' => $request->reference ?? '',
                'note' => $request->note ?? '',
                
                // [PERBAIKAN] Field ini tidak ada di 'create', set default string kosong
                'training' => '', 
                'organization' => '',
                'personal_identity' => '', // (KTP di-upload di menu 'edit')

                'iduser' => 0, 
                'created' => now(),
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);
            // Upload KTP tidak di-handle di sini (sesuai form 'create')

        }); // Transaksi Selesai

        // 4. Redirect kembali
        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data pendidik (Personal Detail).
     */
    public function edit(string $id)
    {
        $teacher = DB::table('sis_user')
                    ->leftJoin('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
                    ->where('sis_user.id', $id)
                    ->where('sis_user.is_teacher', 'yes')
                    ->select('sis_user.*', 'sis_teacher.*', 'sis_user.id as user_id')
                    ->first();

        if (!$teacher) {
            return redirect()->route('teacher.index')->with('error', 'Data pendidik tidak ditemukan.');
        }

        return view('admin.teacher.edit', [
            'teacher' => $teacher
        ]);
    }

    /**
     * [PERBAIKAN] Memperbarui data pendidik (Personal Detail).
     * Termasuk upload KTP, bank_acc, is_parent, training, organization.
     */
    /**
     * [PERBAIKAN] Memperbarui data pendidik (Personal Detail).
     * Menghapus 'training' dan 'organization' dari update ini.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Data (Sudah benar, NIK tidak dicek unique)
        $request->validate([
            'fullname' => 'required|string|max:45',
            'nik' => ['required', 'string', 'max:45', 'alpha_dash'],
            'email' => ['nullable', 'email', 'max:45', Rule::unique('sis_user', 'email')->ignore($id)],
            'ktp_upload' => 'nullable|file|mimes:jpg,jpeg,png|max:1024' // Validasi KTP
        ], [
            'nik.alpha_dash' => 'NIK/NIP hanya boleh berisi huruf, angka, strip (-), dan underscore (_).',
            'email.unique' => 'Email ini sudah terdaftar.',
            'ktp_upload.max' => 'Ukuran file KTP tidak boleh lebih dari 1MB.'
        ]);

        // 2. Gunakan Transaksi Database
        DB::transaction(function () use ($request, $id) {
            
            // 3a. Update tabel 'sis_user'
            DB::table('sis_user')->where('id', $id)->update([
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'username' => $request->nik,
                'password' => Hash::make($request->nik),
                'placeofbirth' => $request->placeofbirth,
                'dateofbirth' => $request->dateofbirth,
                'gender' => $request->gender,
                'street' => $request->street,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postalcode' => $request->postalcode,
                'home_phone' => $request->home_phone,
                'mobile_phone' => $request->mobile_phone,
                'email' => $request->email,
                'religion' => $request->religion,
                'bank_acc' => $request->bank_acc ?? '',
                'is_active' => $request->is_active,
                'is_parent' => $request->is_parent ?? 'no',
                'updated' => now(),
                'update_by' => session('user_id'),
            ]);

            // 3b. Siapkan data untuk 'sis_teacher'
            $teacherData = [
                'nik' => $request->nik,
                'emp_status' => $request->emp_status ?? 'permanent',
                'att_id' => $request->att_id ?? 0,
                'grade' => $request->grade ?? '',
                'license_no' => $request->license_no ?? '',
                'foundation_license_no' => $request->foundation_license_no ?? '',
                'career_objective' => $request->career_objective ?? '',
                'skill' => $request->skill ?? '',
                'reference' => $request->reference ?? '',
                'note' => $request->note ?? '',
                // [DIHAPUS] 'training' dan 'organization' dihapus dari sini
                'updated' => now(),
                'update_by' => session('user_id'),
            ];

            // 3c. Handle Upload File KTP jika ada file baru
           if ($request->hasFile('ktp_upload')) {

    // Ambil data lama
    $oldData = DB::table('sis_teacher')->find($id);

    // Hapus file lama jika ada
    if ($oldData && $oldData->personal_identity) {
        $oldFilePath = 'uploads/teachers/' . $oldData->personal_identity;

        if (file_exists(public_path($oldFilePath))) {
            unlink(public_path($oldFilePath));
        }
    }

    // Buat nama file baru
    $filename = 'personal_identity' . $id . '.' . 
                $request->file('ktp_upload')->getClientOriginalExtension();

    // Simpan file langsung ke public/uploads/teachers
    $request->file('ktp_upload')->storeAs(
        'uploads/teachers', 
        $filename, 
        'public_uploads'    // disk custom
    );

    // Simpan ke DB hanya nama filenya
    $teacherData['personal_identity'] = $filename;
}


            // 3d. Update 'sis_teacher'
            // Kita gunakan 'update' saja, asumsi data sudah dibuat saat 'create'
            DB::table('sis_teacher')->where('id', $id)->update($teacherData);

        }); // Transaksi Selesai
        
        // [PERBAIKAN] Redirect kembali ke halaman edit (Tab 1) agar bisa lihat perubahan
        return redirect()->route('teacher.edit', $id)
                         ->with('success', 'Data pendidik berhasil diperbarui.');
    }

    /**
     * Menghapus data guru/pendidik dari database (6 tabel).
     */
    public function destroy($id)
    {
        if ($id == session('user_id')) {
            return redirect()->route('teacher.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($id) {
            // 0. Hapus file KTP jika ada
            $oldData = DB::table('sis_teacher')->find($id);
            if ($oldData && $oldData->personal_identity) {
                Storage::delete('public/uploads/teachers/' . $oldData->personal_identity);
            }

            // 1. Hapus data CV
            DB::table('sis_education_level')->where('teacher_id', $id)->delete();
            DB::table('sis_work_experience')->where('teacher_id', $id)->delete();
            DB::table('sis_training')->where('teacher_id', $id)->delete();
            DB::table('sis_organization')->where('teacher_id', $id)->delete();
            
            // 2. Hapus data 'sis_teacher'
            DB::table('sis_teacher')->where('id', $id)->delete();
            
            // 3. Hapus data 'sis_user'
            DB::table('sis_user')->where('id', $id)->delete();
        });

        return redirect()->route('teacher.index')
                         ->with('success', 'Data pendidik berhasil dihapus.');
    }

    
    // --- METHOD AJAX UNTUK "PERSONAL CV" ---

    /**
     * [BARU] Menampilkan halaman "Personal CV" (Tab 2).
     */
    public function editCv(Request $request, string $id)
    {
        $teacher = DB::table('sis_user')
                    ->leftJoin('sis_teacher', 'sis_user.id', '=', 'sis_teacher.id')
                    ->where('sis_user.id', $id)
                    ->where('sis_user.is_teacher', 'yes')
                    ->select('sis_user.fullname', 'sis_user.id as user_id')
                    ->first();

        if (!$teacher) {
            return redirect()->route('teacher.index')->with('error', 'Data pendidik tidak ditemukan.');
        }

        $education = DB::table('sis_education_level')->where('teacher_id', $id)->orderBy('start_date', 'desc')->get();
        $work = DB::table('sis_work_experience')->where('teacher_id', $id)->orderBy('start_date', 'desc')->get();
        $training = DB::table('sis_training')->where('teacher_id', $id)->orderBy('year', 'desc')->get();
        $organization = DB::table('sis_organization')->where('teacher_id', $id)->orderBy('year', 'desc')->get();
        
        return view('admin.teacher.cv', [
            'teacher' => $teacher,
            'education' => $education,
            'work' => $work,
            'training' => $training,
            'organization' => $organization
        ]);
    }

    public function storeEducation(Request $request)
{
    // [PERBAIKAN] Validasi manual
    $validator = Validator::make($request->all(), [
        'teacher_id' => 'required|integer',
        'level' => 'required|string|max:45',
        'institution' => 'required|string|max:45',
        'start_date' => 'required|date',
    ]);

    // [PERBAIKAN] Cek jika gagal, kirim error JSON
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::table('sis_education_level')->insert([
        'teacher_id' => $request->teacher_id,
        'level' => $request->level,
        'institution' => $request->institution,
        'major' => $request->major ?? '',
        'city' => $request->city ?? '',
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ]);

    $education = DB::table('sis_education_level')->where('teacher_id', $request->teacher_id)->orderBy('start_date', 'desc')->get();
    return view('admin.teacher._cv_education_table', ['education' => $education]);
}

    public function destroyEducation(string $id)
    {
        $item = DB::table('sis_education_level')->find($id);
        if (!$item) { return response()->json(['error' => 'Data not found'], 404); }
        
        $teacher_id = $item->teacher_id;
        DB::table('sis_education_level')->where('id', $id)->delete();
        
        $education = DB::table('sis_education_level')->where('teacher_id', $teacher_id)->orderBy('start_date', 'desc')->get();
        return view('admin.teacher._cv_education_table', ['education' => $education]);
    }

    public function storeWork(Request $request)
{
    // [PERBAIKAN] Validasi manual
    $validator = Validator::make($request->all(), [
        'teacher_id' => 'required|integer',
        'institution' => 'required|string|max:45',
        'position' => 'required|string|max:45',
        'start_date' => 'required|date',
    ]);

    // [PERBAIKAN] Cek jika gagal, kirim error JSON
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::table('sis_work_experience')->insert([
        'teacher_id' => $request->teacher_id,
        'institution' => $request->institution,
        'position' => $request->position,
        'responsibility' => $request->responsibility ?? '',
        'city' => $request->city ?? '',
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ]);

    $work = DB::table('sis_work_experience')->where('teacher_id', $request->teacher_id)->orderBy('start_date', 'desc')->get();
    return view('admin.teacher._cv_work_table', ['work' => $work]);
}

    public function destroyWork(string $id)
    {
        $item = DB::table('sis_work_experience')->find($id);
        if (!$item) { return response()->json(['error' => 'Data not found'], 404); }

        $teacher_id = $item->teacher_id;
        DB::table('sis_work_experience')->where('id', $id)->delete();
        
        $work = DB::table('sis_work_experience')->where('teacher_id', $teacher_id)->orderBy('start_date', 'desc')->get();
        return view('admin.teacher._cv_work_table', ['work' => $work]);
    }

    public function storeTraining(Request $request)
{
    // [PERBAIKAN] Validasi manual
    $validator = Validator::make($request->all(), [
        'teacher_id' => 'required|integer',
        'year' => 'required|date',
        'title' => 'required|string|max:100',
    ]);

    // [PERBAIKAN] Cek jika gagal, kirim error JSON
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::table('sis_training')->insert([
        'teacher_id' => $request->teacher_id,
        'year' => $request->year,
        'provider' => $request->provider ?? '',
        'title' => $request->title,
    ]);

    $training = DB::table('sis_training')->where('teacher_id', $request->teacher_id)->orderBy('year', 'desc')->get();
    return view('admin.teacher._cv_training_table', ['training' => $training]);
}

    public function destroyTraining(string $id)
    {
        $item = DB::table('sis_training')->find($id);
        if (!$item) { return response()->json(['error' => 'Data not found'], 404); }

        $teacher_id = $item->teacher_id;
        DB::table('sis_training')->where('id', $id)->delete();
        
        $training = DB::table('sis_training')->where('teacher_id', $teacher_id)->orderBy('year', 'desc')->get();
        return view('admin.teacher._cv_training_table', ['training' => $training]);
    }

    public function storeOrganization(Request $request)
{
    // [PERBAIKAN] Validasi manual
    $validator = Validator::make($request->all(), [
        'teacher_id' => 'required|integer',
        'year' => 'required|date',
        'organization_name' => 'required|string|max:50',
    ]);

    // [PERBAIKAN] Cek jika gagal, kirim error JSON
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::table('sis_organization')->insert([
        'teacher_id' => $request->teacher_id,
        'year' => $request->year,
        'organization_name' => $request->organization_name,
        'position' => $request->position ?? '',
    ]);

    $organization = DB::table('sis_organization')->where('teacher_id', $request->teacher_id)->orderBy('year', 'desc')->get();
    return view('admin.teacher._cv_organization_table', ['organization' => $organization]);
}

    public function destroyOrganization(string $id)
    {
        $item = DB::table('sis_organization')->find($id);
        if (!$item) { return response()->json(['error' => 'Data not found'], 404); }

        $teacher_id = $item->teacher_id;
        DB::table('sis_organization')->where('id', $id)->delete();
        
        $organization = DB::table('sis_organization')->where('teacher_id', $teacher_id)->orderBy('year', 'desc')->get();
        return view('admin.teacher._cv_organization_table', ['organization' => $organization]);
    }

    
}