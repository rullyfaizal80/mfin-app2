<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            ->leftJoin('sis_user as u', 'e.user_id', '=', 'u.id')          // User yang mengajukan
            ->leftJoin('sis_user as cas', 'e.mdate_by', '=', 'cas.id')     // Kasir yang memproses
            ->select(
                'e.id', 'e.tdate', 'e.ref_no', 'e.payto', 'e.note', 'e.credit',
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
    // 1. Ambil data Kasir (sis_user)
    $cass = DB::table('sis_user')
        ->join('sis_usergroup', 'sis_user.id', '=', 'sis_usergroup.user_id')
        ->join('sis_group', 'sis_usergroup.group_id', '=', 'sis_group.id')
        ->select('sis_user.id', 'sis_user.fullname')
        ->where('sis_group.group_name', 'like', '%Cashier%')
        ->groupBy('sis_user.id', 'sis_user.fullname')
        ->orderBy('sis_user.fullname', 'asc')
        ->get();

    // 2. Data Dropdown Tabel
    $payitems = DB::table('sis_payitem')->where('payitem_type', 'expense')->get();
    $schools = DB::table('sis_cschool')->get();
    $grades = DB::table('sis_cgrade')->get();

    // 3. Auto Ref No
    $today = now()->format('Ymd');
    $last = DB::table('sis_expense')->where('ref_no', 'like', "EXP-$today%")->orderBy('id', 'desc')->first();
    $next = $last ? (int)substr($last->ref_no, -3) + 1 : 1;
    $autoRef = "EXP-$today-" . str_pad($next, 3, '0', STR_PAD_LEFT);

    return view('fincom.transexpense.create', compact('cass', 'payitems', 'schools', 'grades', 'autoRef'));
}

    /**
     * PROSES SIMPAN KE DATABASE (sis_expense)
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

            $userId = Auth::id() ?? 1; // Fallback ke ID 1 jika belum ada session
            $now = now()->toDateTimeString();

            // 1. Simpan Header (parent_id = 0)
            // Header biasanya menampung total atau informasi utama transaksi
            $headerId = DB::table('sis_expense')->insertGetId([
                'parent_id' => 0,
                'ref_no'    => $request->ref_no,
                'tdate'     => $request->tdate,
                'payto'     => $request->payto,
                'note'      => $request->header_note ?? '',
                'debit'     => 0, // Akan diupdate setelah detail terjumlah
                'credit'    => 0,
                'user_id'   => $userId,
                'mdate'     => $now,
                'mdate_by'  => $userId,
            ]);

            $totalAmount = 0;

            // 2. Simpan Detail (looping dari input dynamic row)
            foreach ($request->items as $item) {
                $amount = (float) str_replace(['.', ','], ['', '.'], $item['amount']);
                $totalAmount += $amount;

                DB::table('sis_expense')->insert([
                    'parent_id'  => $headerId,
                    'ref_no'     => $request->ref_no,
                    'tdate'      => $request->tdate,
                    'payto'      => $request->payto,
                    'payitem_id' => $item['payitem_id'],
                    'note'       => $item['note'] ?? '',
                    'debit'      => $amount,
                    'credit'     => 0,
                    'school_id'  => $item['school_id'] ?? 0,
                    'grade_id'   => $item['grade_id'] ?? 0,
                    'user_id'    => $userId,
                    'mdate'      => $now,
                    'mdate_by'   => $userId,
                ]);
            }

            // 3. Update Total di Header
            DB::table('sis_expense')->where('id', $headerId)->update([
                'debit' => $totalAmount
            ]);

            DB::commit();
            return redirect()->route('fincom.transexpense.index')->with('success', 'Data pengeluaran berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }
}