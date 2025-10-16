<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;

// ✅ Route untuk menampilkan halaman login (GET)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');

// Login routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    if (!session()->has('user_id')) {
        return redirect()->route('login');
    }

    $menuTree = session('menuTree', collect()); // ambil dari session

    return view('dashboard', compact('menuTree'));
})->name('dashboard');

// [PERUBAHAN] Ganti ['auth'] menjadi ['custom.auth']
Route::middleware(['custom.auth'])->group(function () {
    
    // Pindahkan rute dashboard ke sini
    Route::get('/dashboard', function () {
        return view('dashboard'); // Sesuaikan dengan controller Anda jika perlu
    })->name('dashboard');
    
    // Rute user manager Anda
    Route::get('/admin/user', [UserController::class, 'index'])->name('admin.user.index');
    
    // Semua rute lain yang memerlukan login harus diletakkan di sini
});

