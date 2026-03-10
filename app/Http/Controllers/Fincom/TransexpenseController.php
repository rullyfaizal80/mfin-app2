<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TransexpenseController extends Controller
{
    /**
     * HALAMAN UTAMA - DAFTAR PENGELUARAN
     */
    public function index(Request $request)
    {
        // 1. Ambil data Kasir / User untuk filter dropdown
        // (Meniru query di CI2: INNER JOIN sis_usergroup dan sis_group)
        $cashiers = DB::table('sis_user')
            ->join('sis_usergroup', 'sis_user.id', '=', 'sis_usergroup.user_id')
            ->join('sis_group', 'sis_usergroup.group_id', '=', 'sis_group.id')
            ->select('sis_user.id', 'sis_user.fullname', 'sis_group.group_name')
            // Di CI2 ada GROUP BY sis_user.id untuk menghindari duplikat
            ->groupBy('sis_user.id', 'sis_user.fullname', 'sis_group.group_name')
            ->orderBy('sis_user.fullname', 'asc')
            ->get();

        // 2. Query Utama Data Pengeluaran (Menggantikan ax_get_expense)
        $query = DB::table('sis_expense as e')
            ->leftJoin('sis_user as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('sis_user as cas', 'e.mdate_by', '=', 'cas.id')
            ->select(
                'e.id', 'e.tdate', 'e.ref_no', 'e.payto', 'e.note', 
                'e.debit', 'e.credit', // <--- PANGGIL KEDUANYA
                'u.fullname as user_name',
                'cas.fullname as cashier_name'
            )
            ->where('e.parent_id', 0)          // Hanya ambil header transaksi
            ->where('e.ref_no', 'like', '%EXP%'); // Hanya tipe Pengeluaran

        // 3. Logika Filter Pencarian
        
        // Filter Pencarian Teks (No Referensi)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('e.ref_no', 'like', "%{$search}%")
                  ->orWhere('e.note', 'like', "%{$search}%")
                  ->orWhere('e.payto', 'like', "%{$search}%");
            });
        }

        // Filter Tanggal Awal
        if ($request->filled('awal')) {
            $query->whereDate('e.tdate', '>=', $request->awal);
        }

        // Filter Tanggal Akhir
        if ($request->filled('akhir')) {
            $query->whereDate('e.tdate', '<=', $request->akhir);
        }

        // Filter Kasir / User
        if ($request->filled('cas_id') && $request->cas_id != '0') {
            $query->where('e.user_id', $request->cas_id);
        }

        // 4. Eksekusi Query dengan Pagination
        $perPage = $request->input('per_page', 10);
        $expenses = $query->orderBy('e.tdate', 'desc')
                          ->orderBy('e.id', 'desc')
                          ->paginate($perPage)
                          ->withQueryString();

        return view('fincom.transexpense.index', [
            'cashiers' => $cashiers,
            'expenses' => $expenses,
            'req' => $request
        ]);
    }

    /**
     * HALAMAN PRINT KWITANSI (RECEIPT)
     */
    public function receipt($id)
    {
        // 1. Ambil Data Transaksi Utama (Header)
        $exp = DB::table('sis_expense as e')
            ->leftJoin('sis_user as u', 'e.mdate_by', '=', 'u.id') // Mengambil nama kasir yang memproses
            ->select('e.*', 'u.fullname')
            ->where('e.id', $id)
            ->first();

        if (!$exp) abort(404, 'Data transaksi tidak ditemukan.');

        // 2. Ambil Data Detail Item (Rincian Transaksi)
        // Di aplikasi lama, rincian pengeluaran biasanya disimpan sebagai anak dari ID transaksi (parent_id)
        $items = DB::table('sis_expense as e')
            ->leftJoin('sis_payitem as p', 'e.payitem_id', '=', 'p.id')
            ->select('p.title', 'e.note', 'e.debit')
            ->where('e.parent_id', $id)
            ->get();

        return view('fincom.transexpense.receipt', [
            'page_title' => 'TANDA TERIMA PENGELUARAN',
            'exp' => $exp,
            'items' => $items
        ]);
    }

    /**
     * HALAMAN PRINT SEMUA PENGELUARAN (LAPORAN)
     */
    public function p_expense($cas_id = '0', $awal = 'all', $akhir = 'all')
    {
        // 1. CEK VALIDASI TANGGAL
        if ($awal === 'all' || $akhir === 'all') {
            return back()->with('error', 'Silakan pilih rentang Tanggal Awal dan Akhir terlebih dahulu sebelum mencetak laporan.');
        }

        // 2. CEK BATASAN 1 TAHUN UNTUK MENCEGAH ERROR MEMORI
        $startDate = \Carbon\Carbon::parse($awal);
        $endDate = \Carbon\Carbon::parse($akhir);
        
        if ($startDate->diffInDays($endDate) > 366) {
            return back()->with('error', 'Rentang waktu cetak laporan maksimal adalah 1 Tahun. Silakan persempit filter tanggal Anda.');
        }

        // 3. AMBIL NAMA KASIR (Untuk Header Laporan)
        $cashierName = 'Semua Kasir';
        if ($cas_id !== '0') {
            $kasir = DB::table('sis_user')->where('id', $cas_id)->first();
            if ($kasir) $cashierName = $kasir->fullname;
        }

        // 4. QUERY MENGGUNAKAN CURSOR (SANGAT HEMAT MEMORI)
        $query = DB::table('sis_expense as e')
            ->leftJoin('sis_user as cas', 'e.mdate_by', '=', 'cas.id')
            ->select('e.tdate', 'e.ref_no', 'e.payto', 'e.note', 'e.credit', 'cas.fullname as cashier_name')
            ->where('e.parent_id', 0)
            ->where('e.ref_no', 'like', '%EXP%')
            ->whereDate('e.tdate', '>=', $awal)
            ->whereDate('e.tdate', '<=', $akhir);

        if ($cas_id !== '0') {
            $query->where('e.user_id', $cas_id);
        }

        // Menggunakan cursor() sebagai pengganti get() agar RAM tidak penuh
        $expenses = $query->orderBy('e.tdate', 'asc')->cursor(); 

        return view('fincom.transexpense.p_expense', [
            'page_title' => 'Laporan Pengeluaran Kasir',
            'expenses' => $expenses,
            'awal' => $awal,
            'akhir' => $akhir,
            'cashierName' => $cashierName
        ]);
    }

    /**
     * TAMPILAN FORM ENTRY PENGELUARAN
     */
  public function create()
{
    $coas = DB::table('sis_coa')->get(); 
    $payitems = DB::table('sis_payitem')->where('payitem_type', 'expense')->get();
    $schools = DB::table('sis_cschool')->get();
    $grades = DB::table('sis_cgrade')->get();

    // -- LOGIKA MENCARI USER AKTIF DARI CUSTOM AUTH --
    // Sesuaikan 'user_id' dengan nama session yang Anda buat saat proses Login
    $activeUserId = session('user_id') ?? session('id') ?? 1; 
    $activeUser = DB::table('sis_user')->where('id', $activeUserId)->first();
    
    $petugasName = $activeUser ? $activeUser->fullname : 'Petugas Tidak Diketahui';
    $petugasId = $activeUser ? $activeUser->id : 1;

    // -- LOGIKA AUTO REF NO (SAMA SEPERTI SEBELUMNYA) --
    $year = date('Y');
    $mon = date('M'); 
    $day = date('d');
    
    $last = DB::table('sis_expense')
        ->where('ref_no', 'like', 'EXP/%')
        ->orderBy('id', 'desc')
        ->first();
        
    if ($last) {
        $parts = explode('/', $last->ref_no);
        $lastNumber = (int) end($parts); 
        $next = $lastNumber + 1; 
    } else {
        $next = 1; 
    }
    
    $autoRef = "EXP/$year/$mon/$day/" . str_pad($next, 6, '0', STR_PAD_LEFT);

    // Kirim $petugasName dan $petugasId ke View
    return view('fincom.transexpense.create', compact('coas', 'payitems', 'schools', 'grades', 'autoRef', 'petugasName', 'petugasId'));
}

   /**
     * PROSES SIMPAN KE DATABASE LENGKAP (Versi Fix Kolom ucode di sis_expense)
     */
    public function store(Request $request)
    {
        // Validasi Dasar
        $request->validate([
            'tdate' => 'required|date',
            'payto' => 'required|string|max:255',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id() ?? 1;
            $now = now()->toDateTimeString(); 
            
            // Generate Unique Code (ucode) yang akan dipakai di sis_trans dan sis_expense
            $ucode = date("YmdHis") . mt_rand(10000, 99999); 
            
            // Hitung Total Pengeluaran
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += (float) str_replace(['.', ','], ['', '.'], $item['amount']);
            }

            // =========================================================
            // 1. SIMPAN KE sis_trans
            // =========================================================
            $transId = DB::table('sis_trans')->insertGetId([
                'user_id'   => $userId,
                'ttype'     => 'CD',
                'ref_no'    => $request->ref_no,
                'tdate'     => $request->tdate,
                'note'      => $request->header_note ?? 'Pengeluaran Kasir',
                'tvalue'    => $totalAmount,
                'is_posted' => 'yes',
                'cdate'     => $now, 
                'mdate'     => $now,
                'mdate_by'  => $userId,
                'ucode'     => $ucode, // <-- ucode dipakai di sini
            ]);

            // =========================================================
            // 2. SIMPAN KE sis_expense (Header)
            // =========================================================
            $headerId = DB::table('sis_expense')->insertGetId([
                'parent_id' => 0,
                'ref_no'    => $request->ref_no,
                'tdate'     => $request->tdate,
                'payto'     => $request->payto,
                'note'      => $request->header_note ?? '',
                'debit'     => $totalAmount, 
                'credit'    => 0,
                'user_id'   => $userId,
                'is_posted' => 'yes',
                'cdate'     => $now,
                'mdate'     => $now,
                'mdate_by'  => $userId,
                'tid'       => $transId,
                'ucode'     => $ucode, // <-- FIX: Tambahkan ucode di Header sis_expense
            ]);

            // =========================================================
            // 3. SIMPAN KE sis_expense (Detail) & sis_ledger
            // =========================================================
            foreach ($request->items as $item) {
                $amount = (float) str_replace(['.', ','], ['', '.'], $item['amount']);

                // Insert Detail Expense
                DB::table('sis_expense')->insert([
                    'parent_id'  => $headerId,
                    'ref_no'     => $request->ref_no,
                    'tdate'      => $request->tdate,
                    'payto'      => $request->payto,
                    'payitem_id' => $item['payitem_id'],
                    'note'       => $item['note'] ?? '',
                    'debit'      => $amount,
                    'credit'     => 0,
                    'user_id'    => $userId,
                    'is_posted'  => 'yes',
                    'cdate'      => $now,
                    'mdate'      => $now,
                    'mdate_by'   => $userId,
                    'tid'        => $transId,
                    'ucode'      => $ucode, // <-- FIX: Tambahkan ucode juga di Detail sis_expense
                ]);

                // =========================================================
                // 3. SIMPAN KE sis_ledger (Buku Besar)
                // =========================================================
                $payitem = DB::table('sis_payitem')->where('id', $item['payitem_id'])->first();
                
                $coaCash = $payitem ? $payitem->coa_cash : 0; 
                $coaBiaya = 0;
                if ($payitem) {
                    if ($payitem->coa_revenue > 0) $coaBiaya = $payitem->coa_revenue;
                    else if ($payitem->coa_payable > 0) $coaBiaya = $payitem->coa_payable;
                }

                // Insert Jurnal 1: Uang Keluar (Kredit)
                DB::table('sis_ledger')->insert([
                    'tid'       => $transId,
                    'tdate'     => $request->tdate,
                    'ttype'     => 'CD',
                    'ref_no'    => $request->ref_no,
                    'note'      => $request->header_note ?? 'Pengeluaran',
                    'coa_code'  => $coaCash,
                    'debit'     => $amount, // Untuk pengeluaran, akun kas ada di debit pada array pertama CI2 lama
                    'credit'    => 0,
                    'is_posted' => 'yes',
                    // user_id, cdate, dan mdate DIHAPUS karena tabel ledger tidak memilikinya
                ]);

                // Insert Jurnal 2: Biaya Bertambah (Debit)
                DB::table('sis_ledger')->insert([
                    'tid'       => $transId,
                    'tdate'     => $request->tdate,
                    'ttype'     => 'CD',
                    'ref_no'    => $request->ref_no,
                    'note'      => $request->header_note ?? 'Pengeluaran',
                    'coa_code'  => $coaBiaya, 
                    'debit'     => 0,
                    'credit'    => $amount, // Dan ini nilai kreditnya
                    'is_posted' => 'yes',
                    // user_id, cdate, dan mdate DIHAPUS
                ]);
            }

            DB::commit();
            return redirect()->route('fincom.transexpense.index')->with('success', 'Data pengeluaran berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }
/**
 * PENCARIAN PENERIMA (PAY TO)
 */
public function searchPayto(Request $request)
{
    $keyword = $request->query('keyword');
    
    // Jika keyword kosong, kembalikan array kosong
    if (empty($keyword)) {
        return response()->json([]);
    }

    // Cari maksimal 10 data user dari tabel sis_user yang namanya mirip
    $users = DB::table('sis_user')
        ->where('fullname', 'like', "%{$keyword}%")
        ->select('id', 'fullname')
        ->limit(10)
        ->get();

    return response()->json($users);
}

}