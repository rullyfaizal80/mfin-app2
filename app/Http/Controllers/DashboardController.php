<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data user aktif
        $user = session('user_id');

        // 🔹 Ambil group_id user
        $groupId = DB::table('sis_user')->where('id', $user)->value('group_id');

        // 🔹 Ambil menu berdasarkan group dari tabel ACL
        $menuItems = DB::table('sis_acl as a')
            ->join('sis_page as p', 'a.page_id', '=', 'p.id')
            ->where('a.group_id', $groupId)
            ->where('p.enabled', 1)
            ->where('p.is_menu', 1)
            ->orderBy('p.parent_id')
            ->orderBy('p.ordering')
            ->select('p.*')
            ->get();

        // 🔹 Susun jadi struktur tree
        $menuTree = $this->buildMenuTree($menuItems);

        // Kirim ke view dashboard
        return view('dashboard', compact('menuTree'));
    }

    private function buildMenuTree($menuItems, $parentId = 0)
    {
        $tree = [];

        foreach ($menuItems as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildMenuTree($menuItems, $item->id);
                if ($children) {
                    $item->children = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
