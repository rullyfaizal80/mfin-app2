<?php

// PERHATIKAN NAMESPACE-NYA SEKARANG ADA \Fincom
namespace App\Http\Controllers\Fincom; 

use App\Http\Controllers\Controller; // Wajib dipanggil karena berada di sub-folder
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransincomeController extends Controller
{
    /**
     * MENAMPILKAN DAFTAR PEMASUKAN
     */
    public function index(Request $request)
    {
        $cashiers = DB::table('sis_user')
            ->join('sis_usergroup', 'sis_user.id', '=', 'sis_usergroup.user_id')
            ->join('sis_group', 'sis_usergroup.group_id', '=', 'sis_group.id')
            ->select('sis_user.id', 'sis_user.fullname')
            ->where('sis_group.group_name', 'like', '%kasir%')
            ->groupBy('sis_user.id', 'sis_user.fullname')
            ->orderBy('sis_user.fullname', 'asc')
            ->get();

        $query = DB::table('sis_expense as e')
            ->leftJoin('sis_user as cas', 'e.user_id', '=', 'cas.id')
            ->select('e.*', 'cas.fullname as cashier_name')
            ->where('e.parent_id', 0)
            ->where('e.ref_no', 'like', '%INC%');

        if ($request->awal && $request->akhir) {
            $query->whereDate('e.tdate', '>=', $request->awal)
                  ->whereDate('e.tdate', '<=', $request->akhir);
        }

        if ($request->cas_id && $request->cas_id != '0') {
            $query->where('e.user_id', $request->cas_id);
        }

        $incomes = $query->orderBy('e.cdate', 'desc')->paginate(10);

        return view('fincom.transincome.index', [
            'page_title' => 'Daftar Pemasukan Kasir',
            'incomes'    => $incomes,
            'cashiers'   => $cashiers,
            'req'        => $request
        ]);
    }

    /**
     * HALAMAN TAMBAH PEMASUKAN
     */
    public function create()
    {
        $payitems = DB::table('sis_payitem')->where('payitem_type', 'income')->get();
        
        $activeUserId = auth()->id() ?? session('user_id') ?? session('id') ?? 1; 
        $activeUser = DB::table('sis_user')->where('id', $activeUserId)->first();
        
        $petugasName = $activeUser ? $activeUser->fullname : 'Petugas Tidak Diketahui';

        $year = date('Y');
        $mon = date('M'); 
        $day = date('d');
        
        $last = DB::table('sis_expense')
            ->where('ref_no', 'like', 'INC/%')
            ->orderBy('id', 'desc')
            ->first();
            
        if ($last) {
            $parts = explode('/', $last->ref_no);
            $lastNumber = (int) end($parts); 
            $next = $lastNumber + 1; 
        } else {
            $next = 1; 
        }
        
        $autoRef = "INC/$year/$mon/$day/" . str_pad($next, 6, '0', STR_PAD_LEFT);

        return view('fincom.transincome.create', compact('payitems', 'autoRef', 'petugasName'));
    }

    /**
     * FUNGSI UNTUK CETAK LAPORAN PEMASUKAN
     */
    public function p_income($cas_id, $awal, $akhir)
    {
        // 1. Ambil data pemasukan berdasarkan filter
        $query = DB::table('sis_expense as e')
            ->where('e.parent_id', 0)
            ->where('e.ref_no', 'like', '%INC%');

        // Filter Tanggal
        if ($awal && $akhir) {
            $query->whereDate('e.tdate', '>=', $awal)
                  ->whereDate('e.tdate', '<=', $akhir);
        }

        // Filter Kasir
        if ($cas_id && $cas_id != '0') {
            $query->where('e.user_id', $cas_id);
        }

        // Urutkan berdasarkan tanggal transaksi paling lama ke baru
        $incomes = $query->orderBy('e.tdate', 'asc')->get();

        // 2. Modifikasi properti nominal agar sesuai dengan debit/kredit
        foreach ($incomes as $row) {
            $row->nominal = $row->debit > 0 ? $row->debit : $row->credit;
        }

        return view('fincom.transincome.p_income', [
            'page_title' => 'Laporan Transaksi Pemasukan',
            'incomes'    => $incomes,
            'awal'       => $awal,
            'akhir'      => $akhir
        ]);
    }

    /**
     * FUNGSI UNTUK CETAK KWITANSI (PER TRANSAKSI)
     */
    public function print_kwitansi($id)
    {
        // 1. Ambil Data Header Pemasukan & Nama Kasir
        $inc = DB::table('sis_expense as e')
            ->leftJoin('sis_user as u', 'e.user_id', '=', 'u.id')
            ->select('e.*', 'u.fullname')
            ->where('e.id', $id)
            ->first();

        if (!$inc) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan.');
        }

        // 2. Ambil Data Rincian Item Pemasukan
        $items = DB::table('sis_expense as e')
            ->leftJoin('sis_payitem as p', 'e.payitem_id', '=', 'p.id')
            ->select('e.*', 'p.title')
            ->where('e.parent_id', $inc->id)
            ->get();

        return view('fincom.transincome.print_kwitansi', [
            'page_title' => 'Bukti Penerimaan Kas',
            'inc'        => $inc,
            'items'      => $items
        ]);
    }

    /**
     * MENYIMPAN DATA PEMASUKAN KE DATABASE
     */
    public function store(Request $request)
    {
        // Validasi Dasar
        $request->validate([
            'tdate'  => 'required|date',
            'payto'  => 'required|string|max:255',
            'items'  => 'required|array|min:1',
            'ref_no' => 'required|string', // Pastikan validasi ref_no masuk
        ]);

        try {
            DB::beginTransaction();

            // =========================================================
            // Deteksi ID Kasir yang Sedang Login
            // =========================================================
            $userId = auth()->id() ?? session('user_id') ?? session('id');
            
            if (!$userId) {
                return redirect()->back()->with('error', 'Sesi login tidak valid atau telah habis. Silakan login ulang.');
            }

            $now = now()->toDateTimeString(); 
            $ucode = date("YmdHis") . mt_rand(10000, 99999); 

            // Hitung Total Pemasukan
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += (float) str_replace(['.', ','], ['', '.'], $item['amount']);
            }

            // =========================================================
            // 1. SIMPAN KE sis_trans
            // =========================================================
            $transId = DB::table('sis_trans')->insertGetId([
                'user_id'   => $userId,
                'ttype'     => 'CR', 
                'ref_no'    => $request->ref_no, // <-- KUNCI PERBAIKAN: Gunakan ref_no dari Form!
                'tdate'     => $request->tdate,
                'note'      => $request->header_note ?? 'Pemasukan Kasir',
                'tvalue'    => $totalAmount,
                'is_posted' => 'yes',
                'cdate'     => $now, 
                'mdate'     => $now,
                'mdate_by'  => $userId,
                'ucode'     => $ucode, 
            ]);

            // =========================================================
            // 2. SIMPAN KE sis_expense (Header Induk)
            // =========================================================
            $headerId = DB::table('sis_expense')->insertGetId([
                'parent_id' => 0,
                'ref_no'    => $request->ref_no, // <-- Gunakan ref_no dari Form!
                'tdate'     => $request->tdate,
                'payto'     => $request->payto,
                'note'      => $request->header_note ?? '',
                'debit'     => 0, 
                'credit'    => $totalAmount, 
                'user_id'   => $userId,
                'is_posted' => 'yes',
                'cdate'     => $now,
                'mdate'     => $now,
                'mdate_by'  => $userId,
                'tid'       => $transId,
                'ucode'     => $ucode, 
            ]);

            // =========================================================
            // 3. SIMPAN KE sis_expense (Detail) & sis_ledger (Jurnal)
            // =========================================================
            foreach ($request->items as $item) {
                $amount = (float) str_replace(['.', ','], ['', '.'], $item['amount']);

                // Insert Detail Item ke sis_expense
                DB::table('sis_expense')->insert([
                    'parent_id'  => $headerId,
                    'ref_no'     => $request->ref_no, // <-- Gunakan ref_no dari Form!
                    'tdate'      => $request->tdate,
                    'payto'      => $request->payto,
                    'payitem_id' => $item['payitem_id'],
                    'note'       => $item['note'] ?? '',
                    'debit'      => 0,
                    'credit'     => $amount, 
                    'user_id'    => $userId,
                    'is_posted'  => 'yes',
                    'cdate'      => $now,
                    'mdate'      => $now,
                    'mdate_by'   => $userId,
                    'tid'        => $transId,
                    'ucode'      => $ucode, 
                ]);

                // =========================================================
                // 4. SIMPAN KE sis_ledger (Jurnal Akuntansi)
                // =========================================================
                $payitem = DB::table('sis_payitem')->where('id', $item['payitem_id'])->first();
                
                $coaCash = $payitem ? $payitem->coa_cash : 0; 
                $coaPendapatan = 0;
                
                if ($payitem) {
                    if ($payitem->coa_revenue > 0) $coaPendapatan = $payitem->coa_revenue;
                    else if ($payitem->coa_payable > 0) $coaPendapatan = $payitem->coa_payable;
                }

                // Jurnal 1: Kas Bertambah (Debit)
                DB::table('sis_ledger')->insert([
                    'tid'       => $transId,
                    'tdate'     => $request->tdate,
                    'ttype'     => 'CR',
                    'ref_no'    => $request->ref_no, // <-- Gunakan ref_no dari Form!
                    'note'      => $item['note'] ?? 'Pemasukan',
                    'coa_code'  => $coaCash,
                    'debit'     => $amount, 
                    'credit'    => 0,
                    'is_posted' => 'yes',
                ]);

                // Jurnal 2: Pendapatan Bertambah (Kredit)
                DB::table('sis_ledger')->insert([
                    'tid'       => $transId,
                    'tdate'     => $request->tdate,
                    'ttype'     => 'CR',
                    'ref_no'    => $request->ref_no, // <-- Gunakan ref_no dari Form!
                    'note'      => $item['note'] ?? 'Pemasukan',
                    'coa_code'  => $coaPendapatan, 
                    'debit'     => 0,
                    'credit'    => $amount, 
                    'is_posted' => 'yes',
                ]);
            }

            DB::commit();
            return redirect()->route('fincom.transincome.index')->with('success', "Data pemasukan berhasil disimpan dengan Referensi: " . $request->ref_no);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * MENCARI NAMA PENYETOR (PAY TO) UNTUK AUTOCOMPLETE
     */
    public function searchPayto(Request $request)
    {
        $query = $request->get('q');
        if (empty($query)) return response()->json([]);

        // Mencari dari histori tabel expense atau tabel user/siswa (sesuaikan tabelnya)
        $results = DB::table('sis_expense')
            ->select('payto as fullname')
            ->whereNotNull('payto')
            ->where('payto', '!=', '')
            ->where('payto', 'LIKE', "%{$query}%")
            ->distinct()
            ->limit(5)
            ->get();

        return response()->json($results);
    }

    /**
     * PROSES VALIDASI UNLOCK TANGGAL VIA AJAX (PEMASUKAN)
     */
    public function verifyAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            // 1. Cari user berdasarkan username
            $user = DB::table('sis_user')->where('username', $request->username)->first();

            if ($user) {
                $dbPassword = $user->password ?? ''; 
                $inputPassword = $request->password;
                $isPasswordValid = false;

                // 2. Cek Password: Prioritaskan MD5 (Legacy CI2) terlebih dahulu
                if (md5($inputPassword) === $dbPassword) {
                    $isPasswordValid = true;
                } 
                // Jika bukan MD5, baru coba cek menggunakan standar Hash Laravel
                else if (\Illuminate\Support\Facades\Hash::check($inputPassword, $dbPassword)) {
                    $isPasswordValid = true;
                }

                // 3. Jika password cocok, pastikan dia Admin
                if ($isPasswordValid) {
                    if (isset($user->is_admin) && $user->is_admin === 'yes') {
                        return response()->json(['success' => true, 'message' => 'Otorisasi berhasil.']);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Akses Ditolak: User ini bukan Administrator.']);
                    }
                } else {
                    // Jika password salah
                    return response()->json(['success' => false, 'message' => 'Username atau Password salah.']);
                }
            }

            // Jika Username tidak ditemukan
            return response()->json(['success' => false, 'message' => 'Username atau Password salah.']);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Sistem Error: ' . $e->getMessage()
            ], 500);
        }
    }
}