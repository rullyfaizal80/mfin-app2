<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Menampilkan halaman daftar grup.
     */
    public function index(Request $request)
{
    $searchTerm = $request->query('search');
    $perPage = $request->query('perPage', 10);

    $query = DB::table('sis_group');

    // [PERBAIKAN] Tambahkan pencarian untuk kolom 'ordering'
    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('group_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('ordering', 'like', '%' . $searchTerm . '%'); // <-- Tambahkan baris ini
        });
    }

    $query->orderBy('ordering', 'asc');
    $groups = $query->paginate($perPage)->withQueryString();

    if ($request->ajax()) {
        return view('admin.group._group_table', ['groups' => $groups]);
    }

    return view('admin.group.index', [
        'groups' => $groups,
        'searchTerm' => $searchTerm,
        'perPage' => $perPage
    ]);
}

    /**
     * Menampilkan form untuk membuat grup baru.
     */
    public function create()
    {
        return view('admin.group.create');
    }

    /**
     * Menyimpan grup baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:45|unique:sis_group,group_name',
            'ordering' => 'required|integer',
        ]);

        DB::table('sis_group')->insert([
            'group_name' => $request->group_name,
            'ordering' => $request->ordering,
        ]);

        return redirect()->route('admin.group.index')
                         ->with('success', 'Group created successfully!');
    }

    /**
     * Menampilkan form untuk mengedit grup.
     */
    public function edit($id)
    {
        $group = DB::table('sis_group')->where('id', $id)->first();
        if (!$group) {
            return redirect()->route('admin.group.index')->with('error', 'Group not found!');
        }
        return view('admin.group.edit', ['group' => $group]);
    }

    /**
     * Memperbarui data grup di database.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'group_name' => 'required|string|max:45|unique:sis_group,group_name,' . $id,
            'ordering' => 'required|integer',
        ]);

        DB::table('sis_group')->where('id', $id)->update([
            'group_name' => $request->group_name,
            'ordering' => $request->ordering,
        ]);

        return redirect()->route('admin.group.index')
                         ->with('success', 'Group updated successfully!');
    }

    /**
     * Menghapus grup dari database.
     */
    public function destroy($id)
    {
        DB::table('sis_group')->where('id', $id)->delete();
        return redirect()->route('admin.group.index')
                         ->with('success', 'Group deleted successfully!');
    }
}