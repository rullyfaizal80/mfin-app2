<?php

use App\Http\Controllers\Admin\AclController;
use App\Http\Controllers\Admin\CyearController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CsubjectController;

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

    // [TAMBAHAN] Rute untuk "Personal CV" (Halaman Tab 2)
    Route::get('/user/teacher/{id}/cv', [TeacherController::class, 'editCv'])->name('teacher.editCv');
    // [TAMBAHAN] Rute AJAX untuk Riwayat Pendidikan
    Route::post('/user/teacher/cv/education', [TeacherController::class, 'storeEducation'])->name('teacher.cv.education.store');
    Route::delete('/user/teacher/cv/education/{id}', [TeacherController::class, 'destroyEducation'])->name('teacher.cv.education.destroy');
    // [TAMBAHAN] Rute AJAX untuk Pengalaman Kerja
    Route::post('/user/teacher/cv/work', [TeacherController::class, 'storeWork'])->name('teacher.cv.work.store');
    Route::delete('/user/teacher/cv/work/{id}', [TeacherController::class, 'destroyWork'])->name('teacher.cv.work.destroy');
    // [TAMBAHAN] Rute AJAX untuk Pelatihan
    Route::post('/user/teacher/cv/training', [TeacherController::class, 'storeTraining'])->name('teacher.cv.training.store');
    Route::delete('/user/teacher/cv/training/{id}', [TeacherController::class, 'destroyTraining'])->name('teacher.cv.training.destroy');
    // [TAMBAHAN] Rute AJAX untuk Organisasi
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
});
