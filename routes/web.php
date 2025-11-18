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
use App\Http\Controllers\Admin\CgradeController;
use App\Http\Controllers\Admin\CgroupController;
use App\Http\Controllers\Admin\CtypeController;
use App\Http\Controllers\Admin\CschoolController;
use App\Http\Controllers\Admin\ClassListController;
use App\Http\Controllers\Admin\ClassUserController;

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
    Route::get('/sclass/class_user/{class_list_id}/edit/{class_user_id}', [ClassUserController::class, 'index'])->name('class_user.edit');
    Route::post('/sclass/class_user/{class_list_id}', [ClassUserController::class, 'store'])->name('class_user.store');
    Route::put('/sclass/class_user/{class_list_id}/{class_user_id}', [ClassUserController::class, 'update'])->name('class_user.update');
    Route::delete('/sclass/class_user/{class_list_id}/{class_user_id}', [ClassUserController::class, 'destroy'])->name('class_user.destroy');
    Route::post('/sclass/class_user/{class_list_id}/copy', [ClassUserController::class, 'copyStudents'])->name('class_user.copy');
    Route::get('/sclass/ajax_search_students', [ClassUserController::class, 'ajaxSearchStudents'])->name('class_user.ajax_search');
    Route::get('/sclass/ajax_get_classes', [ClassUserController::class, 'ajaxGetClassesByYear'])->name('class_user.ajax_get_classes');
});
