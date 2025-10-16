<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCustomAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah session 'user_id' yang Anda buat saat login ada.
        if (!session()->has('user_id')) {
            // Jika tidak ada, kembalikan ke halaman login.
            return redirect()->route('login');
        }

        // Jika ada, izinkan untuk melanjutkan.
        return $next($request);
    }
}