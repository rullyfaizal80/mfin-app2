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

    public function createDeposit()
    {
        $this->checkApprovalSaving(); // Logika CI2 termuat di sini
        return $this->showForm('credit', 'Setoran Tabungan');
    }

    public function createWithdrawal()
    {
        return $this->showForm('debit', 'Penarikan Tabungan');
    }

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

    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer',
            'payitem_id' => 'required|integer',
            'tdate'      => 'required|date',
            'tvalue'     => 'required|numeric|min:1',
            'ref_no'     => 'required|string',
            'type'       => 'required|in:credit,debit' 
        ]);

        // LOGIKA CI2: Limit Max Penarikan Berdasarkan Grade (Expense Max)
        if ($request->type == 'debit') {
            $u_grade = session('login_grade', 'default');
            // Catatan: Definisikan array expense_max ini di config/savings.php pada Laravel Anda
            $exp_max = config('savings.expense_max.' . $u_grade, 999999999); 
            
            if ($request->tvalue > $exp_max) {
                return back()->with('error', 'Nilai Total terlalu besar, maksimal adalah Rp' . number_format($exp_max, 0, ',', '.'))->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request) {
                // Ambil ID admin yang sedang login (sesuaikan dengan key session Anda)
                $adminId  = session('user_id') ?? session('login_user_id', 0); 
                $datetime = now();

                // 1. Simpan Header (Treceivable) dengan tid = 0 terlebih dahulu
                $treceivableId = DB::table('sis_treceivable')->insertGetId([
                    'user_id'   => $request->user_id,
                    'ref_no'    => $request->ref_no,
                    'tdate'     => $request->tdate,
                    'tvalue'    => $request->tvalue,
                    'note'      => $request->note,
                    'ttype'     => 'saving',
                    'is_posted' => 'yes',
                    'cdate'     => $datetime,
                    'mdate'     => $datetime,
                    'mdate_by'  => $adminId,
                    'tid'       => 0, 
                    'ucode'     => mt_rand(100000,999999) . date("YmdHis")
                ]);

               // 3. LOGIKA CI2: Injeksi ke sis_trans terlebih dahulu untuk mendapatkan $tid
                $ttype_trans = ($request->type == 'credit') ? 'CR' : 'CD';
                $tid = DB::table('sis_trans')->insertGetId([
                    'user_id'   => $request->user_id,
                    'ttype'     => $ttype_trans,
                    'ref_no'    => $request->ref_no,
                    'tdate'     => $request->tdate,
                    'tvalue'    => $request->tvalue,
                    'note'      => $request->note,
                    'is_posted' => 'yes',
                    'cdate'     => $datetime, // <-- Ditambahkan agar tidak error Field 'cdate' doesn't have a default value
                    'mdate'     => $datetime, // <-- Ditambahkan sebagai praktik standar pencatatan waktu
                    'mdate_by'  => $adminId   // <-- Ditambahkan untuk mencatat ID admin yang menginput
                ]);

                // 2. Simpan Detail (Receivable) - SEKARANG DISERTAKAN KOLOM 'tid'
                DB::table('sis_receivable')->insert([
                    'treceivable_id' => $treceivableId,
                    'user_id'        => $request->user_id,
                    'ref_no'         => $request->ref_no,
                    'tdate'          => $request->tdate,
                    'payitem_id'     => $request->payitem_id,
                    'note'           => $request->note,
                    'credit'         => ($request->type == 'credit') ? $request->tvalue : 0,
                    'debit'          => ($request->type == 'debit') ? $request->tvalue : 0,
                    'cdate'          => $datetime,
                    'mdate'          => $datetime,
                    'mdate_by'       => $adminId,
                    'tstat'          => 'paid',
                    'tid'            => $tid // <-- Ditambahkan agar tidak error Field 'tid' doesn't have a default value
                ]);

                // 4. Update Header dengan TID dari sis_trans
                DB::table('sis_treceivable')->where('id', $treceivableId)->update(['tid' => $tid]);

                // 5. Simpan Buku Besar (Ledger) berdasarkan $tid
                $payitem = DB::table('sis_payitem')->where('id', $request->payitem_id)->first();
                if ($payitem) {
                    // Jurnal Kas
                    DB::table('sis_ledger')->insert([
                        'tdate' => $request->tdate, 'coa_code' => $payitem->coa_cash, 
                        'ttype' => 'GL', 'ref_no' => $request->ref_no, 'note' => $request->note, 
                        'tid' => $tid, 'is_posted' => 'yes',
                        'debit' => ($request->type == 'credit') ? $request->tvalue : 0, 
                        'credit' => ($request->type == 'debit') ? $request->tvalue : 0
                    ]);

                    // Jurnal Hutang
                    DB::table('sis_ledger')->insert([
                        'tdate' => $request->tdate, 'coa_code' => $payitem->coa_payable, 
                        'ttype' => 'GL', 'ref_no' => $request->ref_no, 'note' => $request->note, 
                        'tid' => $tid, 'is_posted' => 'yes',
                        'debit' => ($request->type == 'debit') ? $request->tvalue : 0,  
                        'credit' => ($request->type == 'credit') ? $request->tvalue : 0
                    ]);
                }
            });
            return redirect()->route('savings.show', $request->user_id)->with('success', 'Transaksi berhasil.');
        } catch (\Exception $e) {
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

    public function destroy(Request $request, $idtr)
    {
        // 1. Validasi Input Username & Password dari Modal
        $request->validate([
            'admin_username' => 'required|string',
            'admin_password' => 'required|string'
        ]);

        // 2. Ambil data Admin berdasarkan username inputan
        $admin = DB::table('sis_user')
            ->where('username', $request->admin_username)
            ->where('is_admin', 'yes')
            ->where('is_active', 'yes')
            ->first();
        
        if (!$admin) {
            return back()->with('error', 'Otorisasi Gagal: Username tidak valid atau bukan administrator.');
        }

        // 3. Logika Ganda Validasi Password (Bcrypt & MD5)
        $inputPassword = $request->admin_password;
        $dbPassword = $admin->password;
        $isPasswordValid = false;

        if (\Illuminate\Support\Facades\Hash::check($inputPassword, $dbPassword)) {
            $isPasswordValid = true;
        } elseif (md5($inputPassword) === $dbPassword) {
            $isPasswordValid = true;
        }

        if (!$isPasswordValid) {
            return back()->with('error', 'Otorisasi Gagal: Kata sandi Admin tidak valid.');
        }

        // 4. Eksekusi Hapus Transaksi (Tanpa Cek Tabel Lock)
        $treceivable = DB::table('sis_treceivable')->where('id', $idtr)->first();
        if (!$treceivable) return back()->with('error', 'Data tidak ditemukan.');

        try {
            DB::transaction(function () use ($treceivable, $idtr) {
                $tid = $treceivable->tid;

                // Hapus berdasar relasi TID ke pembukuan
                if ($tid > 0) {
                    DB::table('sis_trans')->where('id', $tid)->delete();
                    DB::table('sis_ledger')->where('tid', $tid)->delete();
                }
                
                // Hapus data transaksi anak dan induk
                DB::table('sis_receivable')->where('treceivable_id', $idtr)->delete();
                DB::table('sis_treceivable')->where('id', $idtr)->delete();
            });

            return back()->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * CETAK BUKU TABUNGAN (RECAP USER) - FIX PRIORITAS KELAS REGULER
     */
    public function printRecap(Request $request, $user_id)
    {
        $awal  = $request->query('ffrom') ?? '1970-01-01';
        $akhir = $request->query('fto') ?? date('Y-m-d');

        // 1. Ambil Data User Utama
        $user = DB::table('sis_user')->where('id', $user_id)->first();
        
        if (!$user) return redirect()->back()->with('error', 'User tidak ditemukan');

        $identity = [];
        $identity['fullname'] = $user->fullname;

        // 2. Logika Identitas
        if ($user->is_student == 'yes') {
            
            // A. Ambil NIS
            $studentInfo = DB::table('sis_student')->where('id', $user->id)->first();
            $nis = $studentInfo->nis ?? '-';

            // B. Ambil Kelas (PRIORITAS: REGULER > LAINNYA)
            $lastClass = DB::table('sis_class_user as cu')
                ->join('sis_class_list as cl', 'cu.class_list_id', '=', 'cl.id')
                ->leftJoin('sis_ctype as ct', 'cl.ctype_id', '=', 'ct.id') // Join ke Tipe Kelas
                ->where('cu.user_id', $user_id)
                // [LOGIKA BARU] Urutkan Prioritas:
                // 1. Jika tipe REGULER, beri nilai 1 (Paling Atas)
                // 2. Jika bukan REGULER, beri nilai 2 (Bawah)
                ->orderByRaw("CASE WHEN ct.title = 'REGULER' THEN 1 ELSE 2 END ASC")
                // 3. Jika sama-sama Reguler, ambil yang paling baru (tahun ajarannya)
                ->orderBy('cu.join_start', 'desc')
                ->select('cl.title as class_name', 'ct.title as type_name')
                ->first();

            $className = $lastClass->class_name ?? '-';

            // Format: Kelas: X / NIS: 123
            $identity['other'] = sprintf("Kelas: %s / NIS: %s", $className, $nis);

        } else {
            // Jika Guru
            $teacherData = DB::table('sis_teacher')->where('iduser', $user_id)->first();
            $nik = $teacherData->nik ?? '-';
            
            // Format: NIK: 123
            $identity['other'] = sprintf("NIK: %s", $nik);
        }

        // 3. Hitung Saldo Pindahan
        $saldoPindahan = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving')
            ->where('r.tdate', '<', $awal)
            ->sum(DB::raw('credit - debit'));

        // 4. Ambil Daftar Transaksi
        $transactions = DB::table('sis_receivable as r')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->where('r.user_id', $user_id)
            ->where('p.payitem_type', 'saving')
            ->whereDate('r.tdate', '>=', $awal)
            ->whereDate('r.tdate', '<=', $akhir)
            ->select('r.*')
            ->orderBy('r.tdate', 'asc')
            ->orderBy('r.id', 'asc')
            ->get();

        return view('admin.savings.print', [
            'identity'       => $identity,
            'transactions'   => $transactions,
            'saldo_pindahan' => $saldoPindahan,
            'ffrom'          => $awal,
            'fto'            => $akhir
        ]);
    }

   private function checkApprovalSaving()
    {
        // Bypass atau nonaktifkan pengecekan approval sementara
        return true;
    }

    public function globalReport(Request $request)
    {
        $ffrom = $request->query('ffrom') ?? '1970-01-01';
        $fto   = $request->query('fto') ?? date('Y-m-d');

        $savings = DB::table('sis_receivable as r')
            ->join('sis_user as u', 'r.user_id', '=', 'u.id')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->leftJoin('sis_teacher as t', 'u.id', '=', 't.iduser')
            ->where('p.payitem_type', 'saving')
            ->whereDate('r.tdate', '>=', $ffrom)
            ->whereDate('r.tdate', '<=', $fto)
            ->select(
                'u.fullname', 'r.tdate', 'r.note', 'r.credit', 'r.debit', 'r.ref_no', 
                't.nik', 's.nis'
            )
            ->orderBy('r.id', 'asc')
            ->get();

        return view('admin.savings.p_trans', [
            'savings' => $savings,
            'ffrom'   => $ffrom,
            'fto'     => $fto
        ]);
    }

}