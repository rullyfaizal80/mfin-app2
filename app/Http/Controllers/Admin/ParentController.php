<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    /**
     * Menampilkan halaman daftar orang tua.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->query('search');
        $perPage = $request->query('perPage', 10);

        // Query ini menggabungkan sis_user dan sis_parents
        // (Mirip dengan ax_get_parents() di kode lama Anda)
        $query = DB::table('sis_user')
            ->join('sis_parents', 'sis_user.id', '=', 'sis_parents.id')
            ->where('sis_user.is_parent', 'yes') // Filter utama
            ->select(
                'sis_user.id',
                'sis_user.fullname',
                'sis_user.gender',
                'sis_user.mobile_phone',
                'sis_user.home_phone',
                'sis_parents.company_phone' // Ambil telp kantor dari sis_parents
            );

        // Logika pencarian: hanya berdasarkan Nama
        if ($searchTerm) {
            $query->where('sis_user.fullname', 'like', '%' . $searchTerm . '%');
        }

        // Urutkan berdasarkan Nama (Fullname)
        $query->orderBy('sis_user.fullname', 'asc');

        $parents = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.parent._parent_table', ['parents' => $parents]);
        }

        return view('admin.parent.index', [
            'parents' => $parents,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }
}