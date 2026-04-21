<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayitemController extends Controller
{
    public function index()
    {
        return view('fincom.payitem.index');
    }

    public function create()
    {
        $coa = $this->getCoaData();
        return view('fincom.payitem.create', $coa);
    }

    public function store(Request $request)
    {
        $request->validate(['payitem_code' => 'required']);

        DB::table('sis_payitem')->insert([
            'payitem_code' => $request->payitem_code,
            'title'        => $request->title,
            'payitem_type' => $request->payitem_type,
            'payitem_user' => 'student',
            'ordering'     => $request->ordering ?? 1,
            'coa_cash'     => $request->coa_cash ?? 0,
            'coa_payable'  => $request->coa_payable ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'  => $request->coa_revenue ?? 0,
            'coa_cost'     => $request->coa_cost ?? 0,
            'payvalue'     => str_replace(['.', ','], '', $request->payvalue),
        ]);

        return redirect()->route('fincom.payitem.student.index')->with('success', 'Komponen Berhasil Ditambahkan');
    }

    public function edit($id)
    {
        $details = DB::table('sis_payitem')->where('id', $id)->first();
        $coa = $this->getCoaData();
        return view('fincom.payitem.edit', array_merge(['details' => $details, 'id' => $id], $coa));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['payitem_code' => 'required']);

        DB::table('sis_payitem')->where('id', $id)->update([
            'payitem_code' => $request->payitem_code,
            'title'        => $request->title,
            'payitem_type' => $request->payitem_type,
            'ordering'     => $request->ordering ?? 1,
            'coa_cash'     => $request->coa_cash ?? 0,
            'coa_payable'  => $request->coa_payable ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'  => $request->coa_revenue ?? 0,
            'coa_cost'     => $request->coa_cost ?? 0,
            'payvalue'     => str_replace(['.', ','], '', $request->payvalue),
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
        $p = DB::table('sis_payitem')->where('id', $id)->first();
        DB::table('sis_userpayitem')->where('payitem_id', $id)->update([
            'coa_cash' => $p->coa_cash,
            'coa_receivable' => $p->coa_receivable,
            'coa_payable' => $p->coa_payable,
            'coa_revenue' => $p->coa_revenue,
            'coa_cost' => $p->coa_cost,
        ]);
        return back()->with('success', 'Data Akun Berhasil Disinkronisasi ke Semua Siswa');
    }

    public function data(Request $request)
    {
        $query = DB::table('sis_payitem')->where('payitem_user', 'student');
        
        // Pencarian sederhana
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('payitem_code', 'like', "%$search%")->orWhere('title', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $results = $query->offset($request->start)->limit($request->length)->orderBy('ordering')->get();
        $coaList = DB::table('sis_coa')->pluck('title', 'id');

        $data = [];
        foreach ($results as $res) {
            $accs = "KAS: ".($coaList[$res->coa_cash] ?? '-')."<br>PIUTANG: ".($coaList[$res->coa_receivable] ?? '-');
            
            $btnEdit = '<a href="'.route('fincom.payitem.student.edit', $res->id).'" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>';
            $btnDel = '<form action="'.route('fincom.payitem.student.destroy', $res->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Hapus?\')">'.csrf_field().method_field('DELETE').'<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>';

            $data[] = [
                $res->payitem_code,
                $res->title,
                strtoupper($res->payitem_type),
                number_format($res->payvalue, 0, ',', '.'),
                '<small>'.$accs.'</small>',
                $btnEdit.' '.$btnDel
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        ]);
    }

    private function getCoaData()
    {
        return [
            'coa_cash' => DB::table('sis_coa')->whereIn('coaclass_id', [1, 2])->orderBy('title')->get(),
            'coa_payable' => DB::table('sis_coa')->whereIn('coaclass_id', [8, 9, 10])->orderBy('title')->get(),
            'coa_cost' => DB::table('sis_coa')->whereIn('coaclass_id', [14, 15, 16, 18])->orderBy('title')->get(),
            'coa_receivable' => DB::table('sis_coa')->whereIn('coaclass_id', [3])->orderBy('title')->get(),
            'coa_revenue' => DB::table('sis_coa')->whereIn('coaclass_id', [13, 31, 32, 33, 34, 35, 36])->orderBy('title')->get(),
        ];
    }
}