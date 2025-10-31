<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\GroupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute untuk tamu (belum login)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']); // Nama 'login' sudah ada di atas
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


// Rute untuk pengguna yang sudah login, dilindungi oleh middleware kustom
Route::middleware(['custom.auth'])->group(function () {
    
    // Rute dashboard (sekarang hanya ada satu definisi)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Rute user manager
    Route::get('/admin/user', [UserController::class, 'index'])->name('admin.user.index');
    
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
        
    // Semua rute lain yang memerlukan login harus diletakkan di sini
});