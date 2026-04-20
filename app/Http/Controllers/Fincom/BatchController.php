<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function lproc($period_id)
    {
        // Ambil data periode
        $period = DB::table('sis_period')->where('id', $period_id)->first();
        
        // Jika periode tidak ada, kembalikan ke halaman daftar periode
        if (!$period) {
            return redirect()->route('fincom.period.index')->with('error', 'Data periode tidak ditemukan.');
        }

        // Siapkan data untuk view
        $page_title = "Daftar Proses Periode";
        $ttype = $period->ttype;

        return view('fincom.batch.lproc', compact('period_id', 'ttype', 'page_title', 'period'));
    }
}
