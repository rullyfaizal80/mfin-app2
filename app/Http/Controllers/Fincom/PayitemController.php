<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayitemController extends Controller
{
    public function index()
    {
        // Panggil fungsi getCoaData() agar dropdown di modal index terisi data
        $coa = $this->getCoaData();
        
        // Passing data $coa ke view
        return view('fincom.payitem.index', $coa);
    }

    public function create()
    {
        $coa = $this->getCoaData();
        return view('fincom.payitem.create', $coa);
    }

    public function store(Request $request)
{
    // 1. Validasi Inputan (Aturan 'unique' dihapus agar kode boleh sama)
    $request->validate([
        'payitem_code' => 'required|string|max:50', // <-- 'unique' sudah dihapus
        'title'        => 'required|string|max:255',
        'payitem_type' => 'required|string',
    ], [
        'payitem_code.required' => 'Kode komponen wajib diisi.',
        'title.required'        => 'Nama komponen wajib diisi.',
        'payitem_type.required' => 'Tipe komponen wajib dipilih.',
    ]);

    // 2. Bersihkan format angka pada payvalue
    $payvalue = 0;
    if ($request->filled('payvalue')) {
        $payvalue = preg_replace('/[^0-9]/', '', $request->payvalue);
    }

    try {
        // 3. Insert ke Database
        DB::table('sis_payitem')->insert([
            'payitem_code'   => $request->payitem_code,
            'title'          => $request->title,
            'payitem_type'   => $request->payitem_type,
            'payitem_user'   => 'student',
            'ordering'       => $request->ordering ?? 1,
            'payvalue'       => $payvalue,
            'coa_cash'       => $request->coa_cash ?? '0',
            'coa_payable'    => $request->coa_payable ?? '0',
            'coa_receivable' => $request->coa_receivable ?? '0',
            'coa_revenue'    => $request->coa_revenue ?? '0',
            'coa_cost'       => $request->coa_cost ?? '0',
        ]);

        // 4. Return response JSON
        return response()->json([
            'status'  => 'success',
            'message' => 'Data komponen berhasil ditambahkan!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal menyimpan data: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Update method edit untuk merespon request AJAX dari Modal
     */
    public function edit($id)
    {
        $details = DB::table('sis_payitem')->where('id', $id)->first();
        
        // Jika dipanggil via AJAX dari Modal Edit
        if (request()->ajax()) {
            return response()->json($details);
        }

        $coa = $this->getCoaData();
        return view('fincom.payitem.edit', array_merge(['details' => $details, 'id' => $id], $coa));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['payitem_code' => 'required']);

        $payvalue = $request->payvalue ? str_replace(['.', ','], '', $request->payvalue) : 0;

        DB::table('sis_payitem')->where('id', $id)->update([
            'payitem_code'   => $request->payitem_code,
            'title'          => $request->title,
            'payitem_type'   => $request->payitem_type,
            'ordering'       => $request->ordering ?? 1,
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
            'payvalue'       => $payvalue,
        ]);

        return redirect()->route('fincom.payitem.student.index')->with('success', 'Komponen Berhasil Diperbarui');
    }

    public function destroy($id)
    {
        DB::table('sis_userpayitem')->where('payitem_id', $id)->delete();
        DB::table('sis_payitem')->where('id', $id)->delete();
        return redirect()->route('fincom.payitem.student.index')->with('success', 'Komponen Berhasil Dihapus');
    }

    public function sync($id)
{
    try {
        // 1. Ambil data master komponen (payitem)
        $p = DB::table('sis_payitem')->where('id', $id)->first();

        if (!$p) {
            $msg = 'Data komponen tidak ditemukan!';
            if (request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        // 2. Sinkronkan pemetaan COA ke tabel sis_userpayitem
        $updatedRows = DB::table('sis_userpayitem')
            ->where('payitem_id', $id)
            ->update([
                'coa_cash'       => $p->coa_cash,
                'coa_receivable' => $p->coa_receivable,
                'coa_payable'    => $p->coa_payable,
                'coa_revenue'    => $p->coa_revenue,
                'coa_cost'       => $p->coa_cost,
            ]);

        $msg = "Data Akun Berhasil Disinkronisasi ke {$updatedRows} Data Siswa!";

        // 3. Kembalikan response JSON jika dipanggil via AJAX dari Modal
        if (request()->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => $msg,
                'affected' => $updatedRows
            ]);
        }

        // Fallback untuk request HTTP biasa (non-AJAX)
        return back()->with('success', $msg);

    } catch (\Exception $e) {
        if (request()->ajax()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyinkronkan: ' . $e->getMessage()
            ], 500);
        }

        return back()->with('error', 'Terjadi kesalahan sistem.');
    }
}

    /**
     * DataTables AJAX - Menyesuaikan Format Modern seperti StudentListController
     */
    public function data(Request $request)
    {
        // 1. Parameter Modern
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // 2. Query Utama
        $query = DB::table('sis_payitem')->where('payitem_user', 'student');
        
        $recordsTotal = $query->count();

        // 3. Filter Pencarian
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('payitem_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        $results = $query->offset($start)
                         ->limit($length)
                         ->orderBy('ordering', 'asc')
                         ->orderBy('title', 'asc')
                         ->get();

        // 1. Ambil data COA dan pastikan menjadi array
        // SEBELUMNYA:
// $coaList = DB::table('sis_coa')->pluck('title', 'id')->toArray();

// UBAH MENJADI:
$coaList = DB::table('sis_coa')->pluck('title', 'coa_code')->toArray();

        // 2. Susun Data
        $data = [];
        foreach ($results as $res) {
            $col = [];
            $accsArray = [];
            
            // LOGIKA BARU: Gunakan ?? (Null Coalescing) agar lebih kebal error
            // Jika akun ada, tampilkan namanya. Jika ID ada tapi nama tidak ketemu, tampilkan ID-nya sebagai informasi (Debugging).
            if (!empty($res->coa_cash) && $res->coa_cash != 0) {
                $accsArray[] = $coaList[$res->coa_cash] ?? "Akun ID (".$res->coa_cash.") Tdk Ditemukan";
            }
            if (!empty($res->coa_receivable) && $res->coa_receivable != 0) {
                $accsArray[] = $coaList[$res->coa_receivable] ?? "Akun ID (".$res->coa_receivable.") Tdk Ditemukan";
            }
            if (!empty($res->coa_cost) && $res->coa_cost != 0) {
                $accsArray[] = $coaList[$res->coa_cost] ?? "Akun ID (".$res->coa_cost.") Tdk Ditemukan";
            }
            if (!empty($res->coa_payable) && $res->coa_payable != 0) {
                $accsArray[] = $coaList[$res->coa_payable] ?? "Akun ID (".$res->coa_payable.") Tdk Ditemukan";
            }
            if (!empty($res->coa_revenue) && $res->coa_revenue != 0) {
                $accsArray[] = $coaList[$res->coa_revenue] ?? "Akun ID (".$res->coa_revenue.") Tdk Ditemukan";
            }

            // Tombol Aksi
            $btnEdit = '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="'.$res->id.'" title="Edit"><i class="bi bi-pencil"></i></button>';
            $btnDel  = '<form action="'.route('fincom.payitem.student.destroy', $res->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Hapus?\')">'.csrf_field().method_field('DELETE').'<button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button></form>';
            
            // Pengisian Array Kolom Sesuai Tabel HTML
            $col[] = $res->payitem_code;
            $col[] = $res->title;
            $col[] = strtoupper($res->payitem_type);
            $col[] = number_format((float)$res->payvalue, 0, ',', '.');
            // Jika kosong semua, beri tanda strip (-), jika ada gabungkan dengan <br>
            $col[] = !empty($accsArray) ? implode('<br>', $accsArray) : '<span class="text-muted">-</span>'; 
            $col[] = '<div class="btn-group">'.$btnEdit.' '.$btnDel.'</div>';
            
            $data[] = $col;
        }

        // 5. Kembalikan response JSON format Modern DataTables
        return response()->json([
            "draw"            => intval($draw),
            "recordsTotal"    => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data"            => $data
        ]);
    }

    private function getCoaData()
    {
        return [
            'coa_cash'       => DB::table('sis_coa')->whereIn('coaclass_id', [1, 2])->orderBy('title')->get(),
            'coa_payable'    => DB::table('sis_coa')->whereIn('coaclass_id', [8, 9, 10])->orderBy('title')->get(),
            'coa_cost'       => DB::table('sis_coa')->whereIn('coaclass_id', [14, 15, 16, 18])->orderBy('title')->get(),
            'coa_receivable' => DB::table('sis_coa')->whereIn('coaclass_id', [3])->orderBy('title')->get(),
            'coa_revenue'    => DB::table('sis_coa')->whereIn('coaclass_id', [13, 31, 32, 33, 34, 35, 36])->orderBy('title')->get(),
        ];
    }
}