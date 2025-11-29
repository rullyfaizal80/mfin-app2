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

    /**
     * FORM SETORAN (DEPOSIT) - CREDIT
     */
    public function createDeposit()
    {
        return $this->showForm('credit', 'Setoran Tabungan');
    }

    /**
     * FORM PENARIKAN (WITHDRAWAL) - DEBIT
     */
    public function createWithdrawal()
    {
        return $this->showForm('debit', 'Penarikan Tabungan');
    }

    /**
     * Helper untuk menampilkan Form
     */
    /**
     * Helper untuk menampilkan Form dengan Ref No Otomatis (URUT HARIAN)
     */
    private function showForm($type, $pageTitle)
    {
        // 1. Ambil Payitem Saving
        $payitems = DB::table('sis_payitem')
            ->where('payitem_type', 'saving')
            ->select('id', 'title', 'payitem_code')
            ->orderBy('title', 'asc')
            ->get();

        // 2. Tentukan Prefix dan Format Tanggal
        // Setoran = SVG, Penarikan = WTD
        $prefix = ($type == 'credit') ? 'SVG' : 'WTD';
        
        // Format Tanggal Legacy: YYYY/Mon/DD (Misal: 2025/Nov/25)
        // 'M' = Nama bulan 3 huruf (Jan, Feb, Nov, Dec)
        $datePart = date('Y/M/d'); 
        
        // Pola yang dicari di database: SVG/2025/Nov/25
        $searchPattern = $prefix . '/' . $datePart;

        // 3. Cari Transaksi Terakhir dengan Pola Tersebut
        $lastRef = DB::table('sis_receivable')
            ->where('ref_no', 'like', $searchPattern . '%')
            ->orderBy('id', 'desc') // Ambil yang paling baru dibuat
            ->value('ref_no');      // Ambil string nomornya

        // 4. Hitung Nomor Selanjutnya
        $newNumber = 1; // Default jika belum ada transaksi hari ini
        
        if ($lastRef) {
            // Contoh lastRef: SVG/2025/Nov/25/000005
            // Kita pecah string berdasarkan '/'
            $parts = explode('/', $lastRef);
            
            // Ambil elemen terakhir (angka urut)
            $lastSeq = end($parts); 
            
            // Pastikan yang diambil benar-benar angka sebelum ditambah
            if (is_numeric($lastSeq)) {
                $newNumber = (int)$lastSeq + 1;
            }
        }

        // 5. Rakit Nomor Referensi Baru
        // str_pad: menambahkan nol di depan agar panjangnya 6 digit
        // Hasil: SVG/2025/Nov/25/000001
        $ref_no = $searchPattern . '/' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        return view('admin.savings.create', [
            'payitems' => $payitems,
            'ref_no'   => $ref_no,
            'type'     => $type,
            'page_title' => $pageTitle
        ]);
    }

    /**
     * PROSES SIMPAN (Store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer',
            'payitem_id' => 'required|integer',
            'tdate'      => 'required|date',
            'tvalue'     => 'required|numeric|min:100',
            'ref_no'     => 'required|string',
            'type'       => 'required|in:credit,debit' 
        ]);

        // Validasi Saldo (Khusus Penarikan)
        if ($request->type == 'debit') {
            $saldo = DB::table('sis_receivable')
                ->join('sis_payitem', 'sis_receivable.payitem_id', '=', 'sis_payitem.id')
                ->where('sis_receivable.user_id', $request->user_id)
                ->where('sis_payitem.payitem_type', 'saving')
                ->sum(DB::raw('credit - debit'));
            
            if ($saldo < $request->tvalue) {
                return back()->with('error', 'Saldo tidak mencukupi. Saldo: Rp ' . number_format($saldo, 0, ',', '.'))->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request) {
                
                $userId   = session('user_id', 0); // ID Admin yang login
                $datetime = now();

                // ==============================================================
                // LANGKAH 1: Simpan ke Tabel INDUK (Header) -> sis_treceivable
                // ==============================================================
                // Tabel ini wajib diisi dulu untuk mendapatkan 'id' (treceivable_id)
                $treceivableId = DB::table('sis_treceivable')->insertGetId([
                    'user_id'   => $request->user_id,
                    'ref_no'    => $request->ref_no,
                    'tdate'     => $request->tdate,
                    'tvalue'    => $request->tvalue, // Total Nilai Transaksi
                    'note'      => $request->note,
                    'ttype'     => 'saving',         // Tipe Transaksi
                    'is_posted' => 'yes',
                    'cdate'     => $datetime,
                    'mdate'     => $datetime,
                    'mdate_by'  => $userId,
                    'tid'       => 0, // Default 0 (sesuai legacy)
                    'ucode'     => mt_rand(100000,999999) . date("YmdHis") // Kode unik (legacy)
                ]);

                // ==============================================================
                // LANGKAH 2: Simpan ke Tabel DETAIL (Anak) -> sis_receivable
                // ==============================================================
                // Di sini kita masukkan 'treceivable_id' yang didapat dari Langkah 1
                $receivableId = DB::table('sis_receivable')->insertGetId([
                    'treceivable_id' => $treceivableId, // <--- INI SOLUSINYA
                    'user_id'        => $request->user_id,
                    'ref_no'         => $request->ref_no,
                    'tdate'          => $request->tdate,
                    'payitem_id'     => $request->payitem_id,
                    'note'           => $request->note,
                    'credit'         => ($request->type == 'credit') ? $request->tvalue : 0,
                    'debit'          => ($request->type == 'debit') ? $request->tvalue : 0,
                    'cdate'          => $datetime,
                    'mdate'          => $datetime,
                    'mdate_by'       => $userId,
                    'tstat'          => 'paid',
                    'is_cash'        => 'yes',
                    'tid'            => 0 // Default 0 untuk mencegah error "Field tid doesn't have default value"
                ]);

                // ==============================================================
                // LANGKAH 3: Simpan ke Buku Besar (Akuntansi) -> sis_ledger
                // ==============================================================
                $payitem = DB::table('sis_payitem')->where('id', $request->payitem_id)->first();
                
                if ($payitem) {
                    // A. Jurnal Kas (Uang Fisik)
                    DB::table('sis_ledger')->insert([
                        'tdate'     => $request->tdate, 
                        'coa_code'  => $payitem->coa_cash, 
                        'ttype'     => 'GL', 
                        'ref_no'    => $request->ref_no, 
                        'note'      => $request->note, 
                        'tid'       => $treceivableId, // Link ke ID Header (biasanya) atau Detail
                        'is_posted' => 'yes',
                        'debit'     => ($request->type == 'credit') ? $request->tvalue : 0, // Setor = Kas Debet
                        'credit'    => ($request->type == 'debit') ? $request->tvalue : 0   // Tarik = Kas Kredit
                    ]);

                    // B. Jurnal Hutang Tabungan (Kewajiban)
                    DB::table('sis_ledger')->insert([
                        'tdate'     => $request->tdate, 
                        'coa_code'  => $payitem->coa_payable, 
                        'ttype'     => 'GL', 
                        'ref_no'    => $request->ref_no, 
                        'note'      => $request->note, 
                        'tid'       => $treceivableId, 
                        'is_posted' => 'yes',
                        'debit'     => ($request->type == 'debit') ? $request->tvalue : 0,  // Tarik = Hutang Berkurang (Debet)
                        'credit'    => ($request->type == 'credit') ? $request->tvalue : 0  // Setor = Hutang Bertambah (Kredit)
                    ]);
                }
            });
            
            return redirect()->route('savings.show', $request->user_id)->with('success', 'Transaksi berhasil disimpan.');

        } catch (\Exception $e) {
            // Tampilkan pesan error detail jika gagal
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    // [PENTING] Update method AJAX agar mengembalikan tipe user (student/teacher)
   public function ajaxSearchUser(Request $request)
    {
        $term = trim($request->query('term'));
        if (empty($term)) return response()->json([]);

        $users = DB::table('sis_user as u')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_teacher as t', 'u.id', '=', 't.iduser')
            ->where('u.is_active', 'yes')
            ->where(function($q) use ($term) {
                $q->where('u.fullname', 'like', '%' . $term . '%')
                  ->orWhere('s.nis', 'like', '%' . $term . '%')
                  ->orWhere('t.nik', 'like', '%' . $term . '%');
            })
            ->select(
                'u.id', 'u.fullname', 's.nis', 't.nik',
                // [PERBAIKAN LOGIKA] Cek kolom is_teacher dari tabel sis_user langsung
                DB::raw("CASE 
                    WHEN u.is_teacher = 'yes' THEN 'teacher' 
                    WHEN u.is_student = 'yes' THEN 'student' 
                    WHEN t.id IS NOT NULL THEN 'teacher' -- Fallback jika is_teacher lupa diset
                    WHEN s.id IS NOT NULL THEN 'student' 
                    ELSE 'general' END as user_type")
            )
            ->orderBy('u.fullname', 'asc')
            ->limit(10)
            ->get();

        return view('admin.savings._user_search_list', ['users' => $users]);
    }

    /**
     * HAPUS TRANSAKSI (Rollback Jurnal & Data)
     */
    /**
     * HAPUS TRANSAKSI (Revised & Robust)
     */
    public function destroy($id)
    {
        // 1. Cari data anak (Detail)
        $receivable = DB::table('sis_receivable')->where('id', $id)->first();

        if (!$receivable) {
            return back()->with('error', 'Data transaksi tidak ditemukan.');
        }

        try {
            DB::transaction(function () use ($receivable) {
                // Ambil ID Induk
                $parentId = $receivable->treceivable_id;

                // A. Jika punya induk, hapus Ledger & Induknya
                if ($parentId && $parentId > 0) {
                    // Hapus Ledger (Jurnal Akuntansi)
                    DB::table('sis_ledger')->where('tid', $parentId)->delete();
                    
                    // Hapus Induk (Header Transaksi)
                    DB::table('sis_treceivable')->where('id', $parentId)->delete();
                }

                // B. Hapus data Anak itu sendiri (Wajib)
                DB::table('sis_receivable')->where('id', $receivable->id)->delete();
            });

            return back()->with('success', 'Transaksi berhasil dihapus.');

        } catch (\Exception $e) {
            // Tampilkan pesan error detail agar kita tahu penyebabnya
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

}