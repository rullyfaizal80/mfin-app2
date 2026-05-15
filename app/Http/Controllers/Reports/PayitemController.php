<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayitemController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }
    */

    public function index(Request $request)
    {
        // 1. Logika Tanggal/Pencarian Request
        if ($request->has('ssearch')) {
            $cashier_id = $request->input('cashier_id');
            $start_date = $request->input('start_date');
            $end_date   = $request->input('end_date');
        } else {
            $cashier_id = 0;
            $start_date = Carbon::now()->format('Y-m-d');
            $end_date   = Carbon::now()->format('Y-m-d');
        }

        // Ambil konfigurasi daftar tipe yang sah (bisa ditaruh di config/app.php jika mau)
        $lpayitem = config('app.sis_payitem_type', [
            "fee", "salary", "tuition", "loan", "expense", "saving", "deposit"
        ]);

        // ====================================================================
        // GOLONGAN 1: AUDIT PAYITEMS
        // ====================================================================
        $payitems = DB::select("SELECT * FROM sis_payitem");
        $e_payitem = [];
        foreach ($payitems as $pi) {
            $pi = (array) $pi; 
            if (!in_array($pi['payitem_type'], $lpayitem)) {
                $pi['error'] = "TIPE tidak ada dalam daftar";
                $e_payitem[] = $pi;                            
            }
            
            $c_acc = 0;
            if (!empty($pi['coa_receivable']) && $pi['coa_receivable'] != 0) $c_acc++;
            if (!empty($pi['coa_cash']) && $pi['coa_cash'] != 0) $c_acc++;
            if (!empty($pi['coa_payable']) && $pi['coa_payable'] != 0) $c_acc++;
            if (!empty($pi['coa_revenue']) && $pi['coa_revenue'] != 0) $c_acc++;
            if (!empty($pi['coa_cost']) && $pi['coa_cost'] != 0) $c_acc++;
            
            if ($c_acc < 2) {
                $pi['error'] = "Jumlah akun kurang dari 2";
                $e_payitem[] = $pi;                            
            }
        }

        // ====================================================================
        // GOLONGAN 2: AUDIT CLASSPAYITEM
        // ====================================================================
        $cpayitems = DB::select("
            SELECT sis_classpayitem.*, sis_payitem.payitem_code, sis_payitem.title, sis_class_list.title as class 
            FROM `sis_classpayitem`, sis_payitem, sis_class_list, sis_cyear 
            WHERE sis_classpayitem.payitem_id=sis_payitem.id 
              AND sis_class_list.id=class_list_id 
              AND cyear_id=sis_cyear.id 
              AND sis_cyear.is_active='yes'
        ");
        $e_cpayitem = [];            
        foreach ($cpayitems as $cpi) {
            $cpi = (array) $cpi;
            
            $c_acc = 0;
            if (!empty($cpi['coa_receivable']) && $cpi['coa_receivable'] != 0) $c_acc++;
            if (!empty($cpi['coa_cash']) && $cpi['coa_cash'] != 0) $c_acc++;
            if (!empty($cpi['coa_payable']) && $cpi['coa_payable'] != 0) $c_acc++;
            if (!empty($cpi['coa_revenue']) && $cpi['coa_revenue'] != 0) $c_acc++;
            if (!empty($cpi['coa_cost']) && $cpi['coa_cost'] != 0) $c_acc++;
            
            if ($c_acc < 2) {
                $cpi['error'] = "Jumlah akun kurang dari 2";
                $e_cpayitem[] = $cpi;                            
            }
        }
        
        // ====================================================================
        // GOLONGAN 3: AUDIT USERPAYITEM (KOMPONEN SATUAN SISWA)
        // ====================================================================
        $upayitems = DB::select("
            SELECT fullname, cp.class_title as class, cp.payitem_title as title, cp.payitem_code, sis_userpayitem.*
            FROM `sis_userpayitem`
            JOIN sis_payitem ON sis_payitem.id = `sis_userpayitem`.payitem_id
            JOIN sis_user ON sis_user.id = CAST(sis_userpayitem.user_id AS UNSIGNED)
            JOIN (
                SELECT class_title, CAST(`sis_class_user`.user_id AS UNSIGNED) AS user_id, CAST(`sis_class_user`.class_list_id AS UNSIGNED) AS class_list_id, payitem_id, sis_payitem.title AS payitem_title, payitem_code
                FROM `sis_class_user`
                JOIN (
                    SELECT class_list_id, sis_class_list.title AS class_title, payitem_id
                    FROM `sis_classpayitem`
                    JOIN sis_class_list ON sis_class_list.id = class_list_id
                    JOIN sis_cyear ON sis_cyear.id = sis_class_list.cyear_id
                    WHERE sis_cyear.is_active = 'yes'
                ) AS cp ON cp.class_list_id = CAST(`sis_class_user`.class_list_id AS UNSIGNED)
                JOIN sis_payitem ON sis_payitem.id = cp.payitem_id
            ) AS cp ON cp.payitem_id = `sis_userpayitem`.payitem_id AND cp.user_id = CAST(`sis_userpayitem`.user_id AS UNSIGNED)
            ORDER BY cp.class_title, fullname
        ");
        
        $e_upayitem = [];            
        foreach ($upayitems as $upi) {
            $upi = (array) $upi;
            
            $c_acc = 0;
            // Evaluasi tipe data modern PHP 8 yang membaca string akun secara utuh
            if (!empty($upi['coa_receivable']) && $upi['coa_receivable'] != 0) $c_acc++;
            if (!empty($upi['coa_cash']) && $upi['coa_cash'] != 0) $c_acc++;
            if (!empty($upi['coa_payable']) && $upi['coa_payable'] != 0) $c_acc++;
            if (!empty($upi['coa_revenue']) && $upi['coa_revenue'] != 0) $c_acc++;
            if (!empty($upi['coa_cost']) && $upi['coa_cost'] != 0) $c_acc++;
            
            if ($c_acc < 2) {
                $upi['error'] = "Jumlah akun kurang dari 2";
                $e_upayitem[] = $upi;                            
            }
        }
        
        // ====================================================================
        // GOLONGAN 4: KELAS TANPA KOMPONEN BIAYA
        // ====================================================================
        $wopitems = DB::select("
            SELECT sis_class_list.id, sis_class_list.title 
            FROM `sis_class_list`, sis_cyear 
            WHERE cyear_id=sis_cyear.id 
              AND sis_cyear.is_active='yes' 
              AND sis_class_list.id NOT IN (SELECT class_list_id FROM `sis_classpayitem`) 
            ORDER BY sis_class_list.title
        ");
        
        $lwopi = "";
        foreach ($wopitems as $wop) {
            $lwopi .= $wop->title . ", ";
        }

        /// Mengarahkan ke resources/views/reports/payitems/index.blade.php
return view('reports.payitems.index', [
    'page_title' => "Laporan Pengecekan Komponen Keuangan",
    'e_payitem'  => $e_payitem,
    'e_cpayitem' => $e_cpayitem,
    'e_upayitem' => $e_upayitem,
    'wopi'       => trim($lwopi, ", ")
]);
	
    }
}
