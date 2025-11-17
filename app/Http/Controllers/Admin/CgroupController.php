<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CgroupController extends Controller
{
    /**
     * Helper function untuk mengambil data list Grup dengan paginasi/search
     */
    private function getPaginatedData(Request $request)
    {
        $searchTerm = $request->query('search');
        // Menggunakan tabel 'sis_cgroup' sesuai file SQL
        $query = DB::table('sis_cgroup');

        if ($searchTerm) {
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        // Urutkan berdasarkan 'title' ascending (A, B, C...)
        return $query->orderBy('title', 'asc')->paginate(10)->withQueryString();
    }

    /**
     * Menampilkan halaman daftar dan form 'Tambah'
     */
    public function index(Request $request)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.cgroup._cgroup_table', ['list_data' => $list_data]);
        }

        // Data kosong untuk form 'Tambah'
        $data_form = (object)[
            'id' => 0,
            'title' => '',
        ];

        return view('admin.cgroup.index', [
            'data_form' => $data_form,
            'form_action' => route('cgroup.store'),
            'form_method' => 'POST',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menampilkan halaman daftar dan form 'Edit'
     */
    public function edit(Request $request, string $id)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.cgroup._cgroup_table', ['list_data' => $list_data]);
        }

        $data_form = DB::table('sis_cgroup')->find($id);

        if (!$data_form) {
            return redirect()->route('cgroup.index')->with('error', 'Grup tidak ditemukan.');
        }

        return view('admin.cgroup.index', [
            'data_form' => $data_form,
            'form_action' => route('cgroup.update', $id),
            'form_method' => 'PUT',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menyimpan data Grup baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_cgroup')->insert([
            'title' => $request->title,
        ]);

        return redirect()->route('cgroup.index')->with('success', 'Grup baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Grup
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_cgroup')->where('id', $id)->update([
            'title' => $request->title,
        ]);

        // Redirect ke index() agar form kembali ke mode 'Tambah'
        return redirect()->route('cgroup.index')->with('success', 'Grup berhasil diperbarui.');
    }

    /**
     * Menghapus data Grup
     */
    public function destroy(string $id)
    {
        try {
            DB::table('sis_cgroup')->where('id', $id)->delete();
            return redirect()->route('cgroup.index')->with('success', 'Grup berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint (Error 1451)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('cgroup.index')->with('error', 'Gagal menghapus: Grup ini sudah digunakan di data lain.');
            }
            return redirect()->route('cgroup.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}