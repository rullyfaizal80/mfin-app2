<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        // Default tahun adalah tahun berjalan
        $year = $request->get('fyear', date('Y'));

        // Ambil data periode berdasarkan tahun dari period_start
        $periods = DB::table('sis_period')
            ->whereYear('period_start', $year)
            ->orderBy('period_start', 'asc')
            ->get();

        $page_title = "Daftar Periode";
        
        return view('fincom.period.index', compact('periods', 'year', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date',
            'ttype'        => 'required',
        ]);

        DB::table('sis_period')->insert([
            'period_start' => $request->period_start,
            'period_end'   => $request->period_end,
            'note'         => $request->note,
            'ttype'        => $request->ttype,
            'ucode'        => date("YmdHis") . mt_rand(10000, 99999),
            'cdate'        => now(),
            'mdate'        => now(),
            'mdate_by'     => auth()->id() ?? 1, // Sesuaikan dengan session user login Anda
        ]);

        return redirect()->route('fincom.period.index')->with('success', 'Periode baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date',
            'ttype'        => 'required',
        ]);

        DB::table('sis_period')->where('id', $id)->update([
            'period_start' => $request->period_start,
            'period_end'   => $request->period_end,
            'note'         => $request->note,
            'ttype'        => $request->ttype,
            'mdate'        => now(),
            'mdate_by'     => auth()->id() ?? 1,
        ]);

        return redirect()->route('fincom.period.index')->with('success', 'Data periode berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            DB::table('sis_period')->where('id', $id)->delete();
            return redirect()->route('fincom.period.index')->with('success', 'Data periode berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error 1451 (Foreign Key Constraint) sama seperti di CI2
            if ($e->getCode() == "23000") {
                return redirect()->route('fincom.period.index')->with('error', 'Gagal dihapus! Periode ini sudah digunakan dalam transaksi/proses lain.');
            }
            return redirect()->route('fincom.period.index')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}