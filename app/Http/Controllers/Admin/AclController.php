<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AclController extends Controller
{
    /**
     * Menampilkan halaman daftar grup untuk pengaturan ACL.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Mengambil data dari tabel sis_group
        $query = DB::table('sis_group');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('group_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('ordering', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->orderBy('ordering', 'asc');
        $groups = $query->paginate($perPage)->withQueryString();

        // Cek jika ini permintaan AJAX (untuk pencarian live)
        if ($request->ajax()) {
            return view('admin.acl._acl_table', ['groups' => $groups]);
        }

        // Jika bukan, muat halaman lengkap
        return view('admin.acl.index', [
            'groups' => $groups,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    /**
     * [BARU] Menampilkan halaman edit permission untuk grup tertentu.
     */
    public function edit($id)
    {
        // 1. Dapatkan grup yang akan diedit
        $group = DB::table('sis_group')->where('id', $id)->first();
        if (!$group) {
            return redirect()->route('admin.acl.index')->with('error', 'Group not found!');
        }

        // 2. Dapatkan daftar ID halaman yang SUDAH diizinkan untuk grup ini
        $allowedPageIds = DB::table('sis_acl')
                            ->where('group_id', $id)
                            ->pluck('page_id')
                            ->toArray(); // ->toArray() agar mudah dipakai di 'whereNotIn'

        // 3. Dapatkan daftar halaman yang BELUM diizinkan (Available Pages)
        // Kita juga filter agar hanya 'page' yang 'enabled' dan 'is_menu'
        $availablePages = DB::table('sis_page')
                            ->whereNotIn('id', $allowedPageIds)
                            ->where('enabled', 1)
                            ->where('is_menu', 1)
                            ->orderBy('title')
                            ->get();

        // 4. Dapatkan daftar halaman yang SUDAH diizinkan (Allowed Pages)
        $allowedPages = DB::table('sis_page')
                          ->whereIn('id', $allowedPageIds)
                          ->orderBy('title')
                          ->get();

        // 5. Tampilkan view
        return view('admin.acl.edit', [
            'group' => $group,
            'availablePages' => $availablePages,
            'allowedPages' => $allowedPages
        ]);
    }

    /**
     * [BARU] Menambahkan permission ke grup (via AJAX).
     */
    public function addPermission(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'page_id' => 'required|integer',
        ]);

        // Cek agar tidak duplikat
        $exists = DB::table('sis_acl')
                    ->where('group_id', $request->group_id)
                    ->where('page_id', $request->page_id)
                    ->exists();

        if (!$exists) {
            DB::table('sis_acl')->insert([
                'group_id' => $request->group_id,
                'page_id' => $request->page_id,
                'acl_level' => 'ro' // Sesuai data Anda, default 'ro'
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * [BARU] Menghapus permission dari grup (via AJAX).
     */
    public function removePermission(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'page_id' => 'required|integer',
        ]);

        DB::table('sis_acl')
            ->where('group_id', $request->group_id)
            ->where('page_id', $request->page_id)
            ->delete();

        return response()->json(['success' => true]);
    }
}