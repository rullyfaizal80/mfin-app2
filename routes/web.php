<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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


