<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseReportController extends Controller
{
   public function index()
    {
        $coas = DB::table('sis_coa')
            ->orderBy('coa_code')
            ->get();

        $schools  = DB::table('sis_cschool')->orderBy('name')->get();
        $grades   = DB::table('sis_cgrade')->orderBy('title')->get();
        
        $classes  = DB::table('sis_class_list')
            ->join('sis_cyear', 'sis_class_list.cyear_id', '=', 'sis_cyear.id')
            ->where('sis_cyear.is_active', 'yes')
            ->select('sis_class_list.id', 'sis_class_list.title')
            ->orderBy('sis_class_list.title')
            ->get();

        // Mengambil daftar unit unik langsung dari data transaksi expense yang ada
        $sunits = DB::table('sis_expense')
            ->whereNotNull('sunit')
            ->where('sunit', '<>', '')
            ->distinct()
            ->pluck('sunit');

        return view('fincom.expense.report_filter', compact('coas', 'schools', 'grades', 'classes', 'sunits'));
    }

    public function print(Request $request)
    {
        $awal       = $request->query('awal', date('Y-m-d'));
        $akhir      = $request->query('akhir', date('Y-m-d'));
        $coa_code   = $request->query('coa_code', 0); 
        $school_id  = $request->query('school_id', 0);
        $level_id   = $request->query('level_id', 0);
        $class_id   = $request->query('class_id', 0);
        $sunit      = $request->query('sunit', 'all'); 

        $coaTitle = 'Semua Akun / Komponen';
        if ($coa_code != 0) {
            $coa = DB::table('sis_coa')->where('coa_code', $coa_code)->first();
            if ($coa) $coaTitle = $coa->coa_code . ' - ' . $coa->title;
        }

        // ==========================================
        // BASE QUERY (Mengikuti logika sis_ledger & sis_trans aplikasi lama)
        // ==========================================
        $baseQuery = DB::table('sis_ledger as l')
            ->join('sis_trans as t', 't.id', '=', 'l.tid')
            ->leftJoin('sis_user as u', 't.user_id', '=', 'u.id')
            ->leftJoin('sis_v_activeclass as vac', 't.user_id', '=', 'vac.user_id')
            ->leftJoin('sis_class_list as scl', 'scl.id', '=', 'l.class_id')
            ->whereBetween('t.tdate', [$awal, $akhir])
            ->select(
                'l.coa_code',
                'l.ref_no',
                'l.debit',
                'l.credit',
                'l.note',
                't.user_id',
                't.tdate',
                't.note as tnote',
                'u.fullname',
                'u.is_student',
                'vac.class',
                'vac.school',
                'vac.school_id',
                'vac.class_id',
                'scl.title as ledger_class'
            );

        if ($coa_code > 0) {
            $baseQuery->where('l.coa_code', $coa_code);
        }

        // Filter Kelas / Sekolah / Level (Persis logika CI2)
        if ($class_id > 0) {
            $baseQuery->where(function($q) use ($class_id) {
                $q->where('l.class_id', $class_id)
                  ->orWhere('vac.class_id', $class_id);
            });
        } elseif ($school_id > 0) {
            $classListsQuery = DB::table('sis_class_list as cl')
                ->join('sis_cschool as sc', 'cl.cschool_id', '=', 'sc.id')
                ->where('sc.id', $school_id);
            
            if ($level_id > 0) {
                $classListsQuery->where('cl.cgrade_id', $level_id);
            }
            $class_ids = $classListsQuery->pluck('cl.id')->toArray();

            $baseQuery->where(function($q) use ($class_ids) {
                $q->whereIn('l.class_id', $class_ids)
                  ->orWhereIn('vac.class_id', $class_ids);
            });
        }

        if ($sunit !== 'all' && !empty($sunit)) {
            $baseQuery->where('l.sunit', $sunit);
        }

        // 1. QUERY PEMASUKAN (Debit > 0)
        $qTerima = clone $baseQuery;
        $terima = $qTerima->where('l.debit', '>', 0)
            ->orderBy('t.tdate', 'asc')
            ->orderBy('vac.school_id', 'asc')
            ->orderBy('vac.class_id', 'asc')
            ->get();

        // 2. QUERY PENGELUARAN (Credit > 0)
        $qKeluar = clone $baseQuery;
        $keluar = $qKeluar->where('l.credit', '>', 0)
            ->orderBy('t.tdate', 'asc')
            ->orderBy('u.fullname', 'asc')
            ->orderBy('vac.class_id', 'asc')
            ->get();

        return view('fincom.expense.report_print', compact(
            'terima', 'keluar', 'awal', 'akhir', 'coaTitle'
        ));
    }
}