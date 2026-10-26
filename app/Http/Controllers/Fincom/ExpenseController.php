<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * HALAMAN UTAMA: Form Input & Tabel Daftar Komponen
     */
    public function index(Request $request)
    {
        $editId = $request->query('edit');
        $details = null;
        if ($editId) {
            $details = DB::table('sis_payitem')->where('id', $editId)->first();
        }

        // Ambil list semua nama COA berdasarkan coa_code (seperti fungsi coa_list() di CI2)
        $coa_list = DB::table('sis_coa')->pluck('title', 'coa_code')->toArray();

        $coa_cash       = DB::table('sis_coa')->where('title', 'like', '%KAS%')->select('coa_code', 'title')->get();
        $coa_cost       = DB::table('sis_coa')->where('title', 'like', '%BIAYA%')->select('coa_code', 'title')->get();
        $coa_payable    = DB::table('sis_coa')->where('title', 'like', '%HUTANG%')->select('coa_code', 'title')->get();
        $coa_receivable = DB::table('sis_coa')->where('title', 'like', '%PIUTANG%')->select('coa_code', 'title')->get();
        $coa_revenue    = DB::table('sis_coa')->where('title', 'like', '%PENDAPATAN%')->select('coa_code', 'title')->get();

        $search = $request->query('search');
        
        $query = DB::table('sis_payitem')->whereIn('payitem_type', ['expense', 'income']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('payitem_code', 'like', '%' . $search . '%')
                  ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $payitems = $query->orderBy('ordering', 'asc')
                          ->orderBy('title', 'asc')
                          ->paginate(10)
                          ->withQueryString();

        return view('fincom.expense.index', compact(
            'payitems', 'details', 'coa_cash', 'coa_cost', 'coa_payable', 'coa_receivable', 'coa_revenue', 'coa_list', 'search'
        ));
    }

    /**
     * SIMPAN DATA (Insert & Update digabung)
     */
    public function store(Request $request)
    {
        $request->validate([
            'payitem_code' => 'required|string|max:255',
            'title'        => 'required|string|max:255',
            'payitem_type' => 'required|in:expense,income,journal',
            'payitem_user' => 'required|string',
            'ordering'     => 'required|integer',
        ]);

        // Bersihkan format rupiah menjadi angka utuh (meniru fungsi dot_format() dari CI2)
        $payvalue = (float) preg_replace('/[^0-9]/', '', $request->payvalue ?? 0);

        $data = [
            'payitem_code'   => $request->payitem_code,
            'title'          => $request->title,
            'payitem_type'   => $request->payitem_type,
            'payitem_user'   => $request->payitem_user,
            'ordering'       => $request->ordering,
            'coa_cost'       => $request->coa_cost ?? '0',
            'coa_cash'       => $request->coa_cash ?? '0',
            'coa_receivable' => $request->coa_receivable ?? '0',
            'coa_payable'    => $request->coa_payable ?? '0',
            'coa_revenue'    => $request->coa_revenue ?? '0',
            'payvalue'       => $payvalue,
        ];

        // Tentukan apakah ini proses Insert atau Update berdasarkan keberadaan payitem_id
        if ($request->filled('payitem_id')) {
            DB::table('sis_payitem')->where('id', $request->payitem_id)->update($data);
            $msg = 'Komponen berhasil diperbarui.';
        } else {
            DB::table('sis_payitem')->insert($data);
            $msg = 'Komponen berhasil ditambahkan.';
        }

        return redirect()->route('fincom.expense.index')->with('success', $msg);
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        try {
            DB::table('sis_payitem')->where('id', $id)->delete();
            return redirect()->route('fincom.expense.index')->with('success', 'Komponen berhasil dihapus.');
        } catch (\Exception $e) {
            // Tangani error Foreign Key (1451) jika data sudah digunakan dalam transaksi
            if ($e->getCode() == '23000') {
                return back()->with('error', 'Gagal menghapus: Komponen ini masih digunakan dalam data transaksi lain.');
            }
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}