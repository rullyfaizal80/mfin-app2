<?php

namespace App\Services;

use App\Models\User;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Mengambil dan menyusun pohon menu untuk pengguna tertentu berdasarkan hak aksesnya.
     *
     * @param int $userId ID dari user yang sedang login.
     * @return Collection Mengembalikan koleksi menu yang sudah tersusun.
     */
    public function getMenuForUser(int $userId): Collection
    {
        // 1. Ambil data user beserta relasi grupnya menggunakan Eloquent.
        // 'with('groups')' membuat query lebih efisien (Eager Loading).
        $user = User::with('groups')->find($userId);

        // Jika user tidak ditemukan, kembalikan koleksi kosong.
        if (!$user) {
            return collect();
        }

        // 2. Dapatkan semua ID grup yang dimiliki oleh user.
        $groupIds = $user->groups->pluck('id');

        // Jika user tidak tergabung dalam grup manapun, kembalikan koleksi kosong.
        if ($groupIds->isEmpty()) {
            return collect();
        }

        // 3. Dapatkan semua ID halaman (page_id) yang diizinkan untuk grup-grup tersebut.
        // Kita menggunakan DB::table() di sini karena 'sis_acl' adalah tabel pivot sederhana.
        $allowedPageIds = DB::table('sis_acl')
            ->whereIn('group_id', $groupIds)
            ->pluck('page_id')
            ->unique();

        // Jika tidak ada halaman yang diizinkan, kembalikan koleksi kosong.
        if ($allowedPageIds->isEmpty()) {
            return collect();
        }

        // 4. Ini adalah query utama.
        // Ambil semua menu utama (parent_id = 0) yang diizinkan,
        // DAN secara bersamaan ambil juga anak-anaknya (sub-menu) yang juga diizinkan.
        $menuTree = Page::whereIn('id', $allowedPageIds)      // Hanya ambil halaman yang diizinkan
                        ->where('is_menu', 1)                 // Pastikan itu adalah item menu
                        ->where('enabled', 1)                 // Pastikan menu tersebut aktif
                        ->where('parent_id', 0)               // Mulai dari menu paling atas (utama)
                        ->with(['children' => function ($query) use ($allowedPageIds) {
                            // 'with()' akan memuat relasi 'children' yang kita buat di Page Model.
                            // Fungsi di dalamnya adalah filter tambahan untuk sub-menu.
                            $query->whereIn('id', $allowedPageIds) // Sub-menu juga harus diizinkan
                                  ->where('is_menu', 1)
                                  ->where('enabled', 1)
                                  ->orderBy('ordering'); // Urutkan sub-menu
                        }])
                        ->orderBy('ordering') // Urutkan menu utama
                        ->get();
        
        return $menuTree;
    }
}

