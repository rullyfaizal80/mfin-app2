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
        $userId = $request->input('user_id'); // Hidden ID dari hasil pencarian
        $fnis   = $request->input('fnis');    // Text yang diketik (untuk display)

        // Query Dasar Pembayaran (Join Table)
        $query = DB::table('sis_receivable as r')
            ->join('sis_treceivable as tr', 'r.treceivable_id', '=', 'tr.id')
            ->join('sis_user as u', 'r.user_id', '=', 'u.id') // Join User untuk ambil Nama
            ->select(
                'tr.tdate',
                'tr.ref_no',
                'u.fullname as student_name', // Nama Siswa
                'r.payfor',
                'r.credit',
                'r.note',
                'r.id as item_id',
                'tr.id as trans_id'
            )
            ->where('r.credit', '>', 0) // Hanya pembayaran masuk
            ->orderBy('tr.tdate', 'desc');

        // Filter User ID (Jika ada yang dipilih)
        if ($userId) {
            $query->where('r.user_id', $userId);
        }

        // Filter Bulan (Jika dipilih)
        if ($month) {
            $query->whereMonth('tr.tdate', $month);
        }

        // Filter Tahun
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