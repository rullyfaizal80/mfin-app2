<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CschoolController extends Controller
{
    /**
     * Helper function untuk mengambil data list Sekolah dengan paginasi/search
     */
    private function getPaginatedData(Request $request)
    {
        $searchTerm = $request->query('search');
        // Menggunakan tabel 'sis_cschool'
        $query = DB::table('sis_cschool')
                    // Join ke sis_user untuk dapat nama Kepala Sekolah
                   ->leftJoin('sis_user', 'sis_cschool.headmaster_id', '=', 'sis_user.id')
                   ->select('sis_cschool.*', 'sis_user.fullname as headmaster_name');

        if ($searchTerm) {
            $query->where('sis_cschool.name', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('sis_cschool.name', 'asc')->paginate(10)->withQueryString();
    }

    /**
     * Menampilkan halaman daftar dan form 'Tambah'
     */
    public function index(Request $request)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.cschool._cschool_table', ['list_data' => $list_data]);
        }

        // Data kosong untuk form 'Tambah'
        $data_form = (object)[
            'id' => 0,
            'name' => '',
            'street' => '',
            'city' => '',
            'province' => '',
            'country' => '',
            'postalcode' => '',
            'telephone' => '',
            'fax' => '',
            'email' => '',
            'website' => '',
            'headmaster_id' => 0,
            'headmaster_name' => '', // Untuk helper di view
        ];

        return view('admin.cschool.index', [
            'data_form' => $data_form,
            'form_action' => route('cschool.store'),
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
            return view('admin.cschool._cschool_table', ['list_data' => $list_data]);
        }

        // Ambil data untuk form edit, join dengan nama KepSek
        $data_form = DB::table('sis_cschool')
                        ->leftJoin('sis_user', 'sis_cschool.headmaster_id', '=', 'sis_user.id')
                        ->select('sis_cschool.*', 'sis_user.fullname as headmaster_name')
                        ->where('sis_cschool.id', $id)
                        ->first();

        if (!$data_form) {
            return redirect()->route('cschool.index')->with('error', 'Sekolah tidak ditemukan.');
        }

        return view('admin.cschool.index', [
            'data_form' => $data_form,
            'form_action' => route('cschool.update', $id),
            'form_method' => 'PUT',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menyimpan data Sekolah baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
        ]);

        DB::table('sis_cschool')->insert([
            'name' => $request->name,
            'street' => $request->street ?? '',
            'city' => $request->city ?? '',
            'province' => $request->province ?? '',
            'country' => $request->country ?? '',
            'postalcode' => $request->postalcode ?? '',
            'telephone' => $request->telephone ?? '',
            'fax' => $request->fax ?? '',
            'email' => $request->email ?? '',
            'website' => $request->website ?? '',
            'headmaster_id' => $request->headmaster_id ?? 0,
            'custom_field' => '', // Wajib diisi karena NOT NULL
        ]);

        return redirect()->route('cschool.index')->with('success', 'Sekolah baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Sekolah
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
        ]);

        DB::table('sis_cschool')->where('id', $id)->update([
            'name' => $request->name,
            'street' => $request->street ?? '',
            'city' => $request->city ?? '',
            'province' => $request->province ?? '',
            'country' => $request->country ?? '',
            'postalcode' => $request->postalcode ?? '',
            'telephone' => $request->telephone ?? '',
            'fax' => $request->fax ?? '',
            'email' => $request->email ?? '',
            'website' => $request->website ?? '',
            'headmaster_id' => $request->headmaster_id ?? 0,
        ]);

        // Redirect ke index() agar form kembali ke mode 'Tambah'
        return redirect()->route('cschool.index')->with('success', 'Sekolah berhasil diperbarui.');
    }

    /**
     * Menghapus data Sekolah
     */
    public function destroy(string $id)
    {
        try {
            DB::table('sis_cschool')->where('id', $id)->delete();
            return redirect()->route('cschool.index')->with('success', 'Sekolah berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint (Error 1451)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('cschool.index')->with('error', 'Gagal menghapus: Sekolah ini sudah digunakan di data lain.');
            }
            return redirect()->route('cschool.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}