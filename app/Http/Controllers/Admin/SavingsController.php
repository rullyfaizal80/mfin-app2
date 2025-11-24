<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    /**
     * HALAMAN UTAMA: Daftar Nasabah & Total Saldo (Optimized)
     */
   public function index(Request $request)
    {
        $search = $request->query('search');
        
        // Default pagination 10 (Sesuai aplikasi lama)
        $perPage = 10; 

        // LANGKAH 1: Cari Siapa Saja yang Punya Tabungan (Query Ringan)
        // Kita hanya ambil ID-nya dulu.
        $query = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('p.payitem_type', 'saving')
            ->select('r.user_id', DB::raw('MAX(r.tdate) as last_transaction')) // Ambil tgl terakhir
            ->groupBy('r.user_id'); // Kelompokkan biar 1 orang muncul 1x

        // Jika ada pencarian nama, kita join ke user sebentar untuk filter
        if ($search) {
            $query->join('sis_user as u', 'r.user_id', '=', 'u.id')
                  ->where('u.fullname', 'like', '%' . $search . '%');
        }

        // Urutkan: Yang baru transaksi muncul paling atas
        $query->orderBy('last_transaction', 'desc');

        // Eksekusi Pagination (Hanya ambil 10 baris data mentah)
        // INI YANG MEMBUAT LOADING CEPAT
        $savings = $query->paginate($perPage)->withQueryString();

        // LANGKAH 2: Lengkapi Data (Nama, NIS, Saldo) HANYA untuk 10 orang ini
        $savings->getCollection()->transform(function ($item) {
            
            // A. Ambil Nama & Identitas
            $user = DB::table('sis_user as u')
                ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
                ->leftJoin('sis_teacher as t', 'u.id', '=', 't.iduser')
                ->where('u.id', $item->user_id)
                ->select('u.fullname', 's.nis', 't.nik')
                ->first();

            // B. Hitung Saldo Orang Ini (Query Ringan karena spesifik ID)
            $balance = DB::table('sis_receivable as r')
                ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
                ->where('r.user_id', $item->user_id)
                ->where('p.payitem_type', 'saving')
                ->sum(DB::raw('r.credit - r.debit'));

            // Gabungkan ke object item untuk dikirim ke View
            $item->fullname = $user->fullname ?? 'Unknown';
            $item->identity_number = $user->nis ?? ($user->nik ?? '-');
            $item->total_saldo = $balance;

            return $item;
        });

        return view('admin.savings.index', [
            'savings' => $savings
        ]);
    }

    /**
     * HALAMAN RINCIAN: Detail Transaksi Per Siswa (Dengan Pagination)
     */
    public function show(Request $request, $user_id)
    {
        // 1. Ambil Data User
        $user = DB::table('sis_user')->where('id', $user_id)->first();
        if (!$user) return redirect()->route('savings.index')->with('error', 'Data tidak ditemukan.');

        $ffrom = $request->query('ffrom'); 
        $fto = $request->query('fto');

        // 2. Query Dasar untuk Transaksi
        $query = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving')
            ->select('r.*');

        if ($ffrom && $fto) {
            $query->whereDate('r.tdate', '>=', $ffrom)
                  ->whereDate('r.tdate', '<=', $fto);
        }

        // [PERUBAHAN] Gunakan paginate(20) bukan get()
        // withQueryString() penting agar filter tanggal tidak hilang saat pindah halaman
        $transactions = $query->orderBy('r.tdate', 'desc')
                              ->orderBy('r.id', 'desc')
                              ->paginate(20)
                              ->withQueryString();

        // 3. Hitung Total Saldo Akhir (Tetap hitung dari SEMUA data user ini)
        // Query ini terpisah agar angkanya tetap Saldo Total, bukan saldo per halaman
        $summaryQuery = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving');
            
        // Jika ada filter tanggal, saldo yang tampil menyesuaikan filter atau tetap total semua?
        // Biasanya saldo adalah "Saldo Akhir" (Semua Waktu). 
        // Tapi jika ingin melihat mutasi periode ini, filter tanggal di query saldo bisa diaktifkan.
        // Untuk saat ini kita biarkan hitung total SEMUA waktu (Saldo Real).
        
        $summary = $summaryQuery->select(DB::raw('SUM(credit) - SUM(debit) as saldo_akhir_total'))
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