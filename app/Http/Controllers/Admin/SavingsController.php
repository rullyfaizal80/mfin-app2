<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    /**
     * HALAMAN UTAMA: Daftar Nasabah & Total Saldo
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        // [PERBAIKAN] Filter tanggal dikosongkan (null) secara default
        $ffrom = $request->query('ffrom'); 
        $fto = $request->query('fto');

        // Query Utama
        $query = DB::table('sis_receivable as r')
            ->join('sis_user as u', 'r.user_id', '=', 'u.id')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_teacher as t', 'u.id', '=', 't.iduser')
            ->where('p.payitem_type', 'saving')
            ->select(
                'u.id as user_id',
                'u.fullname',
                's.nis',
                't.nik',
                // Ambil tanggal transaksi paling baru untuk user ini
                DB::raw('MAX(r.tdate) as last_transaction'), 
                // Hitung total saldo (Semua waktu)
                DB::raw('SUM(r.credit) - SUM(r.debit) as total_saldo') 
            );

        // Filter Pencarian Nama (Jika ada)
        if ($search) {
            $query->where('u.fullname', 'like', '%' . $search . '%');
        }

        // Filter Tanggal (Hanya jika user mengisi)
        if ($ffrom && $fto) {
            // Catatan: Hati-hati filter tanggal di halaman saldo, 
            // karena saldo seharusnya akumulasi semua waktu.
            // Tapi jika diminta filter, kita filter berdasarkan transaksi terakhirnya.
            $query->having('last_transaction', '>=', $ffrom)
                  ->having('last_transaction', '<=', $fto);
        }

        // Grouping
        $query->groupBy('u.id', 'u.fullname', 's.nis', 't.nik');

        // [PERBAIKAN] Urutkan berdasarkan Transaksi Terakhir (Terbaru di atas)
        $query->orderBy('last_transaction', 'desc');

        // Pagination
        $savings = $query->paginate(20)->withQueryString();

        return view('admin.savings.index', [
            'savings' => $savings,
            'ffrom' => $ffrom,
            'fto' => $fto
        ]);
    }

    /**
     * HALAMAN RINCIAN: Detail Transaksi Per Siswa
     */
    public function show(Request $request, $user_id)
    {
        // 1. Ambil Data User
        $user = DB::table('sis_user')->where('id', $user_id)->first();
        if (!$user) return redirect()->route('savings.index')->with('error', 'Data tidak ditemukan.');

        // 2. Filter Tanggal (Khusus rincian, default kosongkan juga biar tampil semua)
        $ffrom = $request->query('ffrom'); 
        $fto = $request->query('fto');

        // 3. Ambil Transaksi
        $query = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving')
            ->select('r.*');

        if ($ffrom && $fto) {
            $query->whereDate('r.tdate', '>=', $ffrom)
                  ->whereDate('r.tdate', '<=', $fto);
        }

        $transactions = $query->orderBy('r.tdate', 'desc')
                              ->orderBy('r.id', 'desc')
                              ->get();

        // 4. Hitung Total Saldo User Ini (Semua Waktu)
        $summary = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving')
            ->select(DB::raw('SUM(credit) - SUM(debit) as saldo_akhir_total'))
            ->first();

        return view('admin.savings.show', [
            'user' => $user,
            'transactions' => $transactions,
            'ffrom' => $ffrom,
            'fto' => $fto,
            'saldo_total' => $summary->saldo_akhir_total ?? 0
        ]);
    }
}