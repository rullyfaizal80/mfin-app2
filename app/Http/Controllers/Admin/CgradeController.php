<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CgradeController extends Controller
{
    /**
     * Helper function untuk mengambil data list Tingkat dengan paginasi/search
     */
    private function getPaginatedData(Request $request)
{
    $searchTerm = $request->query('search');
    $query = DB::table('sis_cgrade');

    if ($searchTerm) {
        $query->where('title', 'like', '%' . $searchTerm . '%');
    }

    // Ranking logika lama:
    // 1. TK
    // 2. PG
    // 3. Huruf (non-angka selain TK/PG)
    // 4. Angka (urut numerik)
    return $query
        ->orderByRaw("
            CASE
                WHEN title = 'TK' THEN 1
                WHEN title = 'PG' THEN 2
                WHEN title REGEXP '^[0-9]+$' THEN 4
                ELSE 3
            END ASC
        ")
        ->orderByRaw("
            CASE 
                WHEN title REGEXP '^[0-9]+$' THEN CAST(title AS UNSIGNED)
                ELSE NULL
            END ASC
        ")
        ->orderBy('title','ASC') // untuk AA, BB, dll
        ->paginate(10)
        ->withQueryString();
}

    /**
     * Menampilkan halaman daftar dan form 'Tambah'
     */
    public function index(Request $request)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.cgrade._cgrade_table', ['list_data' => $list_data]);
        }

        // Data kosong untuk form 'Tambah'
        $data_form = (object)[
            'id' => 0,
            'title' => '',
        ];

        return view('admin.cgrade.index', [
            'data_form' => $data_form,
            'form_action' => route('cgrade.store'),
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
            return view('admin.cgrade._cgrade_table', ['list_data' => $list_data]);
        }

        $data_form = DB::table('sis_cgrade')->find($id);

        if (!$data_form) {
            return redirect()->route('cgrade.index')->with('error', 'Tingkat tidak ditemukan.');
        }

        return view('admin.cgrade.index', [
            'data_form' => $data_form,
            'form_action' => route('cgrade.update', $id),
            'form_method' => 'PUT',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menyimpan data Tingkat baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_cgrade')->insert([
            'title' => $request->title,
        ]);

        return redirect()->route('cgrade.index')->with('success', 'Tingkat baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Tingkat
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:45',
        ]);

        DB::table('sis_cgrade')->where('id', $id)->update([
            'title' => $request->title,
        ]);

        // Redirect ke index() agar form kembali ke mode 'Tambah'
        return redirect()->route('cgrade.index')->with('success', 'Tingkat berhasil diperbarui.');
    }

    /**
     * Menghapus data Tingkat
     */
    public function destroy(string $id)
    {
        try {
            // Sesuai kode lama, cek foreign key
            DB::table('sis_cgrade')->where('id', $id)->delete();
            return redirect()->route('cgrade.index')->with('success', 'Tingkat berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint (Error 1451)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('cgrade.index')->with('error', 'Gagal menghapus: Tingkat ini sudah digunakan di data lain.');
            }
            return redirect()->route('cgrade.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}