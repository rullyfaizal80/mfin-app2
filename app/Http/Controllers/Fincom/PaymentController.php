<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Filter Default
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));
        $userId = $request->input('user_id'); 
        $fnis   = $request->input('fnis');    

        $query = DB::table('sis_treceivable as tr')
            // Join ke detail & user
            ->join('sis_receivable as r', 'tr.id', '=', 'r.treceivable_id') 
            ->join('sis_user as u', 'r.user_id', '=', 'u.id') 
            ->leftJoin('sis_user as cashier', 'tr.mdate_by', '=', 'cashier.id')
            
            ->select(
                'tr.id as item_id',
                'tr.tdate',
                'tr.ref_no',
                'u.fullname as student_name',
                'cashier.fullname as cashier_name',
                'tr.note',
                'tr.tvalue as credit'  // [PERBAIKAN] Ganti tr.nominal menjadi tr.tvalue
            )
            ->where('tr.ref_no', 'like', 'PYM%')
            
            // [PERBAIKAN] Update groupBy sesuai kolom yang di-select
            ->groupBy(
                'tr.id', 
                'tr.tdate', 
                'tr.ref_no', 
                'u.fullname', 
                'cashier.fullname', 
                'tr.note', 
                'tr.tvalue'
            )
            
            ->orderBy('tr.tdate', 'desc')
            ->orderBy('tr.id', 'desc');

        // Filter Tambahan
        if ($userId) {
            $query->where('r.user_id', $userId);
        }
        if ($month) {
            $query->whereMonth('tr.tdate', $month);
        }
        if ($year) {
            $query->whereYear('tr.tdate', $year);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('fincom.payment.index', [
            'req'      => $request,
            'payments' => $payments,
            'curMonth' => $month,
            'curYear'  => $year,
            'fnis'     => $fnis,
            'user_id'  => $userId
        ]);
    }

    // Fungsi AJAX untuk Tombol "Cari"
    public function ajaxStudent(Request $request)
    {
        $keyword = $request->input('q');

        if (!$keyword) return response()->json([]);

        $students = DB::table('sis_student as s')
            ->join('sis_user as u', 's.id', '=', 'u.id')
            ->select('u.id', 'u.fullname', 's.nis')
            ->where('s.nis', 'like', "%$keyword%")
            ->orWhere('u.fullname', 'like', "%$keyword%")
            ->limit(10)
            ->get();

        return response()->json($students);
    }
}