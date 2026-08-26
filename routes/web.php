<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AclController;
use App\Http\Controllers\Admin\CyearController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CsubjectController;
use App\Http\Controllers\Admin\CgradeController;
use App\Http\Controllers\Admin\CgroupController;
use App\Http\Controllers\Admin\CtypeController;
use App\Http\Controllers\Admin\CschoolController;
use App\Http\Controllers\Admin\ClassListController;
use App\Http\Controllers\Admin\ClassUserController;
use App\Http\Controllers\Admin\SavingsController;
use App\Http\Controllers\Admin\SchoolReportController;
use App\Http\Controllers\Admin\ReportStudentController;
use App\Http\Controllers\Admin\ReportTeacherController;
use App\Http\Controllers\Fincom\PaymentController;
use App\Http\Controllers\Fincom\TransexpenseController;
use App\Http\Controllers\Reports\PayitemController;
use App\Http\Controllers\Fincom\StudentListController;
use App\Http\Controllers\Fincom\ReceivableController;

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 */

// Rute untuk tamu (belum login)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']);  // Nama 'login' sudah ada di atas
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute untuk pengguna yang sudah login, dilindungi oleh middleware kustom
Route::middleware(['custom.auth'])->group(function () {
    // Rute dashboard (sekarang hanya ada satu definisi)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/user', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/admin/user/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/admin/user', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/admin/user/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/admin/user/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::delete('/admin/user/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    Route::get('/admin/level', [LevelController::class, 'index'])->name('admin.level.index');
    Route::get('/admin/level/create', [LevelController::class, 'create'])->name('admin.level.create');
    Route::post('/admin/level', [LevelController::class, 'store'])->name('admin.level.store');
    Route::delete('/admin/level/{id}', [LevelController::class, 'destroy'])->name('admin.level.destroy');
    Route::get('/admin/level/{id}/edit', [LevelController::class, 'edit'])->name('admin.level.edit');
    Route::put('/admin/level/{id}', [LevelController::class, 'update'])->name('admin.level.update');

    Route::get('/admin/group', [GroupController::class, 'index'])->name('admin.group.index');
    Route::get('/admin/group/create', [GroupController::class, 'create'])->name('admin.group.create');
    Route::post('/admin/group', [GroupController::class, 'store'])->name('admin.group.store');
    Route::get('/admin/group/{id}/edit', [GroupController::class, 'edit'])->name('admin.group.edit');
    Route::put('/admin/group/{id}', [GroupController::class, 'update'])->name('admin.group.update');
    Route::delete('/admin/group/{id}', [GroupController::class, 'destroy'])->name('admin.group.destroy');

    Route::get('/admin/acl', [AclController::class, 'index'])->name('admin.acl.index');
    Route::get('/admin/acl/{id}/edit', [AclController::class, 'edit'])->name('admin.acl.edit');
    Route::post('/admin/acl/add', [AclController::class, 'addPermission'])->name('admin.acl.add');
    Route::delete('/admin/acl/remove', [AclController::class, 'removePermission'])->name('admin.acl.remove');

    Route::get('/user/student', [StudentController::class, 'index'])->name('student.index');
    Route::get('/user/student/create', [StudentController::class, 'create'])->name('student.create');
    Route::post('/user/student', [StudentController::class, 'store'])->name('student.store');
    Route::delete('/user/student/{id}', [StudentController::class, 'destroy'])->name('student.destroy');
    Route::get('/user/student/{id}/edit', [StudentController::class, 'edit'])->name('student.edit');
    Route::put('/user/student/{id}', [StudentController::class, 'update'])->name('student.update');

    Route::get('/user/parents', [ParentController::class, 'index'])->name('parent.index');
    Route::get('/user/parents/create', [ParentController::class, 'create'])->name('parent.create');
    Route::post('/user/parents', [ParentController::class, 'store'])->name('parent.store');
    Route::delete('/user/parents/{id}', [ParentController::class, 'destroy'])->name('parent.destroy');
    Route::get('/user/parents/{id}/edit', [ParentController::class, 'edit'])->name('parent.edit');
    Route::put('/user/parents/{id}', [ParentController::class, 'update'])->name('parent.update');

    Route::get('/user/teacher', [TeacherController::class, 'index'])->name('teacher.index');
    Route::get('/user/teacher/create', [TeacherController::class, 'create'])->name('teacher.create');
    Route::post('/user/teacher', [TeacherController::class, 'store'])->name('teacher.store');
    Route::delete('/user/teacher/{id}', [TeacherController::class, 'destroy'])->name('teacher.destroy');
    Route::get('/user/teacher/{id}/edit', [TeacherController::class, 'edit'])->name('teacher.edit');
    Route::put('/user/teacher/{id}', [TeacherController::class, 'update'])->name('teacher.update');

    Route::get('/user/teacher/{id}/cv', [TeacherController::class, 'editCv'])->name('teacher.editCv');
    Route::post('/user/teacher/cv/education', [TeacherController::class, 'storeEducation'])->name('teacher.cv.education.store');
    Route::delete('/user/teacher/cv/education/{id}', [TeacherController::class, 'destroyEducation'])->name('teacher.cv.education.destroy');
    Route::post('/user/teacher/cv/work', [TeacherController::class, 'storeWork'])->name('teacher.cv.work.store');
    Route::delete('/user/teacher/cv/work/{id}', [TeacherController::class, 'destroyWork'])->name('teacher.cv.work.destroy');
    Route::post('/user/teacher/cv/training', [TeacherController::class, 'storeTraining'])->name('teacher.cv.training.store');
    Route::delete('/user/teacher/cv/training/{id}', [TeacherController::class, 'destroyTraining'])->name('teacher.cv.training.destroy');
    Route::post('/user/teacher/cv/organization', [TeacherController::class, 'storeOrganization'])->name('teacher.cv.organization.store');
    Route::delete('/user/teacher/cv/organization/{id}', [TeacherController::class, 'destroyOrganization'])->name('teacher.cv.organization.destroy');

    Route::get('/master/cyear', [CyearController::class, 'index'])->name('cyear.index');
    Route::post('/master/cyear', [CyearController::class, 'store'])->name('cyear.store');
    Route::get('/master/cyear/{id}/edit', [CyearController::class, 'edit'])->name('cyear.edit');
    Route::put('/master/cyear/{id}', [CyearController::class, 'update'])->name('cyear.update');
    Route::delete('/master/cyear/{id}', [CyearController::class, 'destroy'])->name('cyear.destroy');

    Route::get('/master/csubject', [CsubjectController::class, 'index'])->name('csubject.index');
    Route::post('/master/csubject', [CsubjectController::class, 'store'])->name('csubject.store');
    Route::get('/master/csubject/{id}/edit', [CsubjectController::class, 'edit'])->name('csubject.edit');
    Route::put('/master/csubject/{id}', [CsubjectController::class, 'update'])->name('csubject.update');
    Route::delete('/master/csubject/{id}', [CsubjectController::class, 'destroy'])->name('csubject.destroy');

    Route::get('/master/cgrade', [CgradeController::class, 'index'])->name('cgrade.index');
    Route::post('/master/cgrade', [CgradeController::class, 'store'])->name('cgrade.store');
    Route::get('/master/cgrade/{id}/edit', [CgradeController::class, 'edit'])->name('cgrade.edit');
    Route::put('/master/cgrade/{id}', [CgradeController::class, 'update'])->name('cgrade.update');
    Route::delete('/master/cgrade/{id}', [CgradeController::class, 'destroy'])->name('cgrade.destroy');

    Route::get('/master/cgroup', [CgroupController::class, 'index'])->name('cgroup.index');
    Route::post('/master/cgroup', [CgroupController::class, 'store'])->name('cgroup.store');
    Route::get('/master/cgroup/{id}/edit', [CgroupController::class, 'edit'])->name('cgroup.edit');
    Route::put('/master/cgroup/{id}', [CgroupController::class, 'update'])->name('cgroup.update');
    Route::delete('/master/cgroup/{id}', [CgroupController::class, 'destroy'])->name('cgroup.destroy');
    
    Route::get('/master/ctype', [CtypeController::class, 'index'])->name('ctype.index');
    Route::post('/master/ctype', [CtypeController::class, 'store'])->name('ctype.store');
    Route::get('/master/ctype/{id}/edit', [CtypeController::class, 'edit'])->name('ctype.edit');
    Route::put('/master/ctype/{id}', [CtypeController::class, 'update'])->name('ctype.update');
    Route::delete('/master/ctype/{id}', [CtypeController::class, 'destroy'])->name('ctype.destroy');

    Route::get('/master/cschool', [CschoolController::class, 'index'])->name('cschool.index');
    Route::post('/master/cschool', [CschoolController::class, 'store'])->name('cschool.store');
    Route::get('/master/cschool/{id}/edit', [CschoolController::class, 'edit'])->name('cschool.edit');
    Route::put('/master/cschool/{id}', [CschoolController::class, 'update'])->name('cschool.update');
    Route::delete('/master/cschool/{id}', [CschoolController::class, 'destroy'])->name('cschool.destroy');

    Route::get('/sclass/class_list', [ClassListController::class, 'index'])->name('class_list.index');
    Route::get('/sclass/class_list/create', [ClassListController::class, 'create'])->name('class_list.create');
    Route::post('/sclass/class_list', [ClassListController::class, 'store'])->name('class_list.store');
    Route::get('/sclass/class_list/{id}/edit', [ClassListController::class, 'edit'])->name('class_list.edit');
    Route::put('/sclass/class_list/{id}', [ClassListController::class, 'update'])->name('class_list.update');
    Route::delete('/sclass/class_list/{id}', [ClassListController::class, 'destroy'])->name('class_list.destroy');

    Route::get('/sclass/class_user/{class_list_id}', [ClassUserController::class, 'index'])->name('class_user.index');
    Route::post('/sclass/class_user/{class_list_id}', [ClassUserController::class, 'store'])->name('class_user.store');    
    Route::delete('/sclass/class_user/{class_list_id}/{class_user_id}', [ClassUserController::class, 'destroy'])->name('class_user.destroy');
    Route::post('/sclass/class_user/{class_list_id}/copy', [ClassUserController::class, 'copyStudents'])->name('class_user.copy');
    Route::get('/sclass/ajax_get_classes', [ClassUserController::class, 'ajaxGetClassesByYear'])->name('class_user.ajax_get_classes');
    Route::get('/sclass/class_user/{class_list_id}/export', [ClassUserController::class, 'exportExcel'])->name('class_user.export');
    Route::get('/sclass/ajax_search_students', [ClassUserController::class, 'ajaxSearchStudents'])->name('class_user.ajax_search');

    Route::prefix('fincom')->group(function () {
        // 1. Halaman Utama (Daftar Transaksi)
        Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
        
        // 2. Halaman Form (Sesuai URL Legacy) - WAJIB DI ATAS '{user_id}'
        // URL: .../fincom/savings/create_new -> Setoran
        Route::get('/savings/create_new', [SavingsController::class, 'createDeposit'])->name('savings.create_new');
        
        // URL: .../fincom/savings/create -> Penarikan
        Route::get('/savings/create', [SavingsController::class, 'createWithdrawal'])->name('savings.create');
        
        // 3. Proses Simpan & AJAX
        Route::post('/savings', [SavingsController::class, 'store'])->name('savings.store');
        Route::get('/savings/ajax-user', [SavingsController::class, 'ajaxSearchUser'])->name('savings.ajax_user');
        
        Route::delete('/savings/delete/{id}', [SavingsController::class, 'destroy'])->name('savings.destroy');
        
        Route::get('/savings/print/{user_id}', [SavingsController::class, 'printRecap'])->name('savings.print');
        // 4. Halaman Rincian (Parameter {user_id}) - WAJIB PALING BAWAH
        Route::get('/savings/{user_id}', [SavingsController::class, 'show'])->name('savings.show');
    });

    // Group Reports
    Route::prefix('reports')->group(function () {
        // Laporan Daftar Kelas
        Route::get('/class_list', [App\Http\Controllers\Admin\ReportClassListController::class, 'index'])->name('reports.class_list.index');
        Route::get('/class_list/print', [App\Http\Controllers\Admin\ReportClassListController::class, 'print'])->name('reports.class_list.print');
        
        Route::get('/class_user/{class_list_id}', [App\Http\Controllers\Admin\ReportClassUserController::class, 'index'])->name('reports.class_user.index');
        
        // [UBAH JADI GET] Agar parameter filter dari URL bisa langsung dibaca
        Route::get('/class_user/print/{class_list_id}', [App\Http\Controllers\Admin\ReportClassUserController::class, 'print'])->name('reports.class_user.print');
        
        Route::get('/class_user/student_print/{student_id}', [App\Http\Controllers\Admin\ReportClassUserController::class, 'printStudent'])->name('reports.class_user.student_print');
       
    });

    Route::prefix('school')->group(function () {
        // 1. Halaman Utama (Filter & Tabel)
        Route::get('/report', [SchoolReportController::class, 'index'])->name('school.report.index');    
        // 2. Proses Cetak PDF (Menggunakan Method yang sama dengan Index untuk menangkap filter)
        Route::get('/report/print', [SchoolReportController::class, 'print'])->name('school.report.print');    
        // 3. Detail Biodata Siswa
        Route::get('/report/detail/{user_id}', [SchoolReportController::class, 'detail'])->name('school.report.detail');
    });   

    Route::prefix('school/reportstudent')->name('school.reportstudent.')->group(function () {
        Route::get('/', [ReportStudentController::class, 'index'])->name('index');
        Route::get('/print', [ReportStudentController::class, 'print'])->name('print');
    });
    
    Route::prefix('reports/rep_teacher')->name('reports.rep_teacher.')->group(function () {
        Route::get('/', [ReportTeacherController::class, 'index'])->name('index');
        Route::get('/detail/{id}', [ReportTeacherController::class, 'detail'])->name('detail');
    });

    Route::prefix('fincom/transexpense')->name('fincom.transexpense.')->group(function () {
        Route::get('/list_expense', [App\Http\Controllers\Fincom\TransincomeController::class, 'listExpenseComponent'])->name('list_expense');
        Route::get('/', [TransexpenseController::class, 'index'])->name('index');
        Route::get('/receipt/{id}', [TransexpenseController::class, 'receipt'])->name('receipt');
        Route::get('/p_expense/{cas_id}/{awal}/{akhir}', [TransexpenseController::class, 'p_expense'])->name('p_expense');
        Route::get('/p_list_expense/{payitem_id}/{awal}/{akhir}', [App\Http\Controllers\Fincom\TransincomeController::class, 'pListExpenseComponent'])->name('p_list_expense');
        Route::get('/create', [TransexpenseController::class, 'create'])->name('create');
        Route::post('/store', [TransexpenseController::class, 'store'])->name('store');
        Route::post('/verify-admin', [TransexpenseController::class, 'verifyAdmin'])->name('verifyAdmin');
        Route::get('/search-payto', [TransexpenseController::class, 'searchPayto'])->name('searchPayto');
        Route::get('/{id}/edit', [TransexpenseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TransexpenseController::class, 'update'])->name('update');
        Route::delete('/{id}', [TransexpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('fincom/transincome')->name('fincom.transincome.')->group(function () {
        Route::get('/', [App\Http\Controllers\Fincom\TransincomeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Fincom\TransincomeController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\Fincom\TransincomeController::class, 'store'])->name('store');
        Route::get('/search-payto', [App\Http\Controllers\Fincom\TransincomeController::class, 'searchPayto'])->name('searchPayto');
        Route::post('/verify-admin', [App\Http\Controllers\Fincom\TransincomeController::class, 'verifyAdmin'])->name('verifyAdmin');
        Route::get('/p_income/{cas_id}/{awal}/{akhir}', [App\Http\Controllers\Fincom\TransincomeController::class, 'p_income'])->name('p_income');
        Route::get('/print-kwitansi/{id}', [App\Http\Controllers\Fincom\TransincomeController::class, 'print_kwitansi'])->name('print_kwitansi');
        Route::get('/{id}/edit', [App\Http\Controllers\Fincom\TransincomeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Fincom\TransincomeController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Fincom\TransincomeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('fincom/period')->name('fincom.period.')->group(function () {
        Route::get('/', [App\Http\Controllers\Fincom\PeriodController::class, 'index'])->name('index');
        Route::post('/store', [App\Http\Controllers\Fincom\PeriodController::class, 'store'])->name('store');
        Route::put('/update/{id}', [App\Http\Controllers\Fincom\PeriodController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [App\Http\Controllers\Fincom\PeriodController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('fincom/batch')->name('fincom.batch.')->group(function () {
        Route::get('/lproc/{period_id}', [App\Http\Controllers\Fincom\BatchController::class, 'lproc'])->name('lproc');
        Route::get('/proc-tuition/{period_id}', [App\Http\Controllers\Fincom\BatchController::class, 'procTuition'])->name('proc_tuition');
    
        // Rute get-student dan process-tuition yang lama dihapus, diganti dengan 1 rute ini:
        Route::post('/process-chunk/{period_id}', [App\Http\Controllers\Fincom\BatchController::class, 'processChunk'])->name('process_chunk');
    });
    
        // Group Route Fincom Payitem
Route::prefix('fincom/payitem')->name('fincom.payitem.')->group(function () {
    // Halaman Utama & AJAX Data
    Route::get('/student', [App\Http\Controllers\Fincom\PayitemController::class, 'index'])->name('student.index');
    Route::post('/student/data', [App\Http\Controllers\Fincom\PayitemController::class, 'data'])->name('student.data');
    
    // Create
    Route::get('/student/create', [App\Http\Controllers\Fincom\PayitemController::class, 'create'])->name('student.create');
    Route::post('/student/store', [App\Http\Controllers\Fincom\PayitemController::class, 'store'])->name('student.store');
    
    // Edit (DIPERBARUI PADA BARIS UPDATE DI BAWAH INI)
    Route::get('/student/{id}/edit', [App\Http\Controllers\Fincom\PayitemController::class, 'edit'])->name('student.edit');
    Route::match(['post', 'put'], '/student/{id}/update', [App\Http\Controllers\Fincom\PayitemController::class, 'update'])->name('student.update');
    
    // Delete & Sync
    Route::delete('/student/{id}', [App\Http\Controllers\Fincom\PayitemController::class, 'destroy'])->name('student.destroy');
    Route::post('/student/{id}/sync', [App\Http\Controllers\Fincom\PayitemController::class, 'sync'])->name('student.sync');
});

// Group untuk Reports
Route::prefix('reports')->name('reports.')->group(function () {
    // URL: /reports/payitems
    Route::get('/payitems', [PayitemController::class, 'index'])->name('payitems');
    Route::post('/payitems', [PayitemController::class, 'index'])->name('payitems.search');
});

// Group untuk Fincom (Keuangan)
Route::prefix('fincom')->name('fincom.')->group(function () {
    
    // Group untuk Student List
    Route::prefix('student_list')->name('student_list.')->group(function () {
        // URL: /fincom/student_list/{class_list_id?}
        Route::get('/{class_list_id?}', [StudentListController::class, 'index'])->name('index');        
        // URL: /fincom/student_list/ax_get_student_list/{class_list_id?}
        Route::post('/ax_get_student_list/{class_list_id?}', [StudentListController::class, 'ax_get_student_list'])->name('ajax');
    });

    // Group untuk Modul Class List (Daftar Kelas Fincom)
    Route::prefix('class_list')->name('class_list.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Fincom\ClassListController::class, 'index'])->name('index');
        Route::post('/ajax', [\App\Http\Controllers\Fincom\ClassListController::class, 'ajax'])->name('ajax');
    });

    // Group untuk Modul Komponen Pembayaran Kelas
    Route::prefix('classpayitem')->name('classpayitem.')->group(function () {
        Route::get('/index/{class_list_id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'index'])->name('index');
        Route::post('/store/{class_list_id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'destroy'])->name('destroy');
        
        // Fitur Khusus: Sinkronisasi & Salin Data
        Route::post('/sync/{class_list_id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'sync'])->name('sync');
        Route::post('/copy/{class_list_id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'copy'])->name('copy');
        Route::post('/ax_get_classpayitem/{class_id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'ax_get_classpayitem']);
        Route::get('get_master_payitem/{id}', [\App\Http\Controllers\Fincom\ClassPayItemController::class, 'get_master_payitem'])->name('get_master_payitem');
    });

    // Group untuk Modul User Pay Item (Komponen Persiswa)
    Route::prefix('userpayitem')->name('userpayitem.')->group(function () {
        // URL: /fincom/userpayitem/student_list/{id}
        Route::get('/student_list/{id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'student_list'])->name('student_list');
        // URL: /fincom/userpayitem/del_item/{student_id}/{id}
        Route::get('/del_item/{student_id}/{id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'del_item'])->name('del_item');
        // Route baru untuk memproses form tambah dari Modal
        Route::post('/add_item/{student_id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'add_item'])->name('add_item');
        // Route baru untuk memproses update data dari Modal Edit
        Route::put('/update_item/{student_id}/{id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'update_item'])->name('update_item');
        // --- ROUTE BARU UNTUK FITUR "DITANGGUNG OLEH" ---
        Route::get('/search_payer', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'search_payer'])->name('search_payer');
        Route::get('/set_payer/{student_id}/{payer_id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'set_payer'])->name('set_payer');
        Route::get('/del_payer/{student_id}', [\App\Http\Controllers\Fincom\UserPayItemController::class, 'del_payer'])->name('del_payer');
    });
   
    Route::prefix('receivable')->name('receivable.')->controller(ReceivableController::class)->group(function () {
        // Halaman Utama Receivable
        Route::get('/', 'index')->name('index');
        Route::post('ajax', 'getData')->name('ajax');

        // Route ilist
        Route::get('ilist', 'ilist')->name('ilist');
        Route::post('ilist/ajax', 'getIlistData')->name('ilist.ajax');

        // === ROUTE REPORT (Wajib di atas rute wildcard {id}) ===
        Route::get('report', 'report')->name('report');
        Route::post('report/ajax', 'getReportData')->name('report.ajax');
        Route::get('reportall/{payitem_id}/{school_id}', 'reportAll')->name('reportall');
        Route::get('reportdetail/{user_id}', 'reportDetail')->name('reportdetail');

        // === ROUTE CREATE & EDIT ===
        Route::get('create', 'create')->name('create');
        Route::match(['get', 'post'], '{id}/edit', 'edit')->name('edit');

        // === ROUTE UNLOCK ===
        Route::get('{id}/unlock', 'unlockForm')->name('unlockForm');
        Route::post('{id}/unlock', 'unlockProcess')->name('unlock.process');

        // === ROUTE AJAX SEARCH ===
        Route::post('search-student', 'searchStudent')->name('searchStudent');
        Route::get('get-user-payitems/{userId}', 'getUserPayItems')->name('getUserPayItems');
    });
});

    Route::prefix('fincom/payment')->name('fincom.payment.')->group(function () {
    // Rute CRUD Utama (Tidak ada perubahan, sudah benar)
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/create', [PaymentController::class, 'create'])->name('create');
    Route::post('/store', [PaymentController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PaymentController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PaymentController::class, 'update'])->name('update');

    // Rute AJAX
    Route::get('/ajax-student', [PaymentController::class, 'ajaxStudent'])->name('ajax_student');
    
    // Tetap POST karena mengirim parameter pencarian {nis: fnis} di body (payload)
    Route::post('/ajax-getnis', [PaymentController::class, 'ajaxGetNis'])->name('ajax_getnis');
    
    // [REVISI] Ubah 4 baris di bawah ini menjadi GET agar sesuai dengan $.get() di JavaScript
    Route::get('/ajax-getuserpayid/{id}', [PaymentController::class, 'ajaxGetUserPayId'])->name('ajax_getuserpayid');
    Route::get('/ajax-getuserpayment/{uid}/{id}', [PaymentController::class, 'ajaxGetUserPayment'])->name('ajax_getuserpayment');
    Route::get('/ajax-list-payment/{uid}', [PaymentController::class, 'ajaxListPayment'])->name('ajax_list_payment');
    Route::get('/ajax-list-payment-edit/{trxId}', [PaymentController::class, 'ajaxListPaymentEdit'])->name('ajax_list_payment_edit');
    
    // TAMBAHKAN INI UNTUK STRUK PRINT
    Route::get('/reportall/{id}', [PaymentController::class, 'reportall'])->name('reportall');
});
});
