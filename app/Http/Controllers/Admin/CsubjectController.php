<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CsubjectController extends Controller
{
    /**
     * Helper function untuk mengambil data list Jurusan dengan paginasi/search
     */
    private function getPaginatedData(Request $request)
    {
        $searchTerm = $request->query('search');
        // Menggunakan tabel 'sis_csubject' sesuai file SQL
        $query = DB::table('sis_csubject');

        if ($searchTerm) {
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        // Urutkan berdasarkan 'title' ascending (IPA, IPS, ...)
        return $query->orderBy('title', 'asc')->paginate(10)->withQueryString();
    }

    /**
     * Menampilkan halaman daftar dan form 'Tambah'
     */
    public function index(Request $request)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.csubject._csubject_table', ['list_data' => $list_data]);
        }

        // Data kosong untuk form 'Tambah'
        $data_form = (object)[
            'id' => 0,
            'title' => '',
        ];

        return view('admin.csubject.index', [
            'data_form' => $data_form,
            'form_action' => route('csubject.store'),
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
            return view('admin.csubject._csubject_table', ['list_data' => $list_data]);
        }

        $data_form = DB::table('sis_csubject')->find($id);

        if (!$data_form) {
            return redirect()->route('csubject.index')->with('error', 'Jurusan tidak ditemukan.');
        }

        return view('admin.csubject.index', [
            'data_form' => $data_form,
            'form_action' => route('csubject.update', $id),
            'form_method' => 'PUT',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menyimpan data Jurusan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_csubject')->insert([
            'title' => $request->title,
        ]);

        return redirect()->route('csubject.index')->with('success', 'Jurusan baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Jurusan
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_csubject')->where('id', $id)->update([
            'title' => $request->title,
        ]);

        // Redirect ke index() agar form kembali ke mode 'Tambah'
        return redirect()->route('csubject.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Menghapus data Jurusan
     */
    public function destroy(string $id)
    {
        try {
            // Sesuai kode lama, cek foreign key
            DB::table('sis_csubject')->where('id', $id)->delete();
            return redirect()->route('csubject.index')->with('success', 'Jurusan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint (Error 1451)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('csubject.index')->with('error', 'Gagal menghapus: Jurusan ini sudah digunakan di data lain.');
            }
            return redirect()->route('csubject.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}