<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CyearController extends Controller
{
    /**
     * Helper function untuk mengambil data list Tahun Ajaran dengan paginasi/search
     */
    private function getPaginatedData(Request $request)
    {
        $searchTerm = $request->query('search');
        $query = DB::table('sis_cyear');

        if ($searchTerm) {
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        return $query->orderBy('date_start', 'desc')->paginate(10)->withQueryString();
    }

    /**
     * Logika bisnis kunci: Nonaktifkan semua tahun ajaran lain.
     */
    private function deactivateOtherYears()
    {
        // Sesuai kode lama: $this->db->simple_query("UPDATE sis_cyear SET is_active='no'");
        DB::table('sis_cyear')->update(['is_active' => 'no']);
    }

    /**
     * Menampilkan halaman daftar dan form 'Tambah'
     */
    public function index(Request $request)
    {
        $list_data = $this->getPaginatedData($request);

        if ($request->ajax()) {
            return view('admin.cyear._cyear_table', ['list_data' => $list_data]);
        }

        // Data kosong untuk form 'Tambah'
        $data_form = (object)[
            'id' => 0,
            'title' => '',
            'date_start' => '',
            'date_end' => '',
            'note' => '',
            'is_active' => 'no',
        ];

        return view('admin.cyear.index', [
            'data_form' => $data_form,
            'form_action' => route('cyear.store'),
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
            return view('admin.cyear._cyear_table', ['list_data' => $list_data]);
        }

        $data_form = DB::table('sis_cyear')->find($id);

        if (!$data_form) {
            return redirect()->route('cyear.index')->with('error', 'Tahun Ajaran tidak ditemukan.');
        }

        return view('admin.cyear.index', [
            'data_form' => $data_form,
            'form_action' => route('cyear.update', $id),
            'form_method' => 'PUT',
            'list_data' => $list_data,
            'searchTerm' => $request->query('search')
        ]);
    }

    /**
     * Menyimpan data Tahun Ajaran baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);

        // [LOGIKA KUNCI] Nonaktifkan tahun ajaran lain jika ini aktif
        if ($request->is_active == 'yes') {
            $this->deactivateOtherYears();
        }

        DB::table('sis_cyear')->insert([
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'note' => $request->note ?? '',
            'is_active' => $request->is_active ?? 'no',
        ]);

        return redirect()->route('cyear.index')->with('success', 'Tahun Ajaran baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data Tahun Ajaran
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);

        // [LOGIKA KUNCI] Nonaktifkan tahun ajaran lain jika ini aktif
        if ($request->is_active == 'yes') {
            $this->deactivateOtherYears();
        }

        DB::table('sis_cyear')->where('id', $id)->update([
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'note' => $request->note ?? '',
            'is_active' => $request->is_active ?? 'no',
        ]);

        // Redirect ke index() agar form kembali ke mode 'Tambah'
        return redirect()->route('cyear.index')->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    /**
     * Menghapus data Tahun Ajaran
     */
    public function destroy(string $id)
    {
        try {
            DB::table('sis_cyear')->where('id', $id)->delete();
            return redirect()->route('cyear.index')->with('success', 'Tahun Ajaran berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani foreign key constraint
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('cyear.index')->with('error', 'Gagal menghapus: Tahun Ajaran ini sudah digunakan di data lain.');
            }
            return redirect()->route('cyear.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}