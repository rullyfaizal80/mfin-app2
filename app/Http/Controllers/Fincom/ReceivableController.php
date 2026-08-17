<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceivableController extends Controller
{
    /**
     * Menampilkan halaman utama dan form filter
     */
    public function index(Request $request)
    {
        // Ambil filter dari request, default 'all'
        $payitemId = $request->input('fpayitem_list', 'all');
        $schoolId = $request->input('fschool', 'all');

        // Ambil data referensi untuk dropdown filter
        $ptype = DB::table('sis_payitem')
            ->select('id', 'title')
            ->where('payitem_type', 'tuition') // <-- KOREKSI: Tambahan filter tipe pembayaran
            ->get();

        // Mengambil data sekolah (Sudah optimal menggunakan tabel master)
        $units = DB::table('sis_cschool')
            ->select('id as school_id', 'name as school')
            ->get();

        $data = [
            'page_title' => 'Daftar Piutang Siswa',
            'payitem_id' => $payitemId,
            'school_id'  => $schoolId,
            'ptype'      => $ptype,
            'units'      => $units,
        ];

        return view('fincom.receivable.index', $data);
    }

    /**
     * Mengembalikan data JSON untuk DataTables Server-Side Processing (Rekap Piutang)
     */
    public function getData(Request $request)
    {
        $payitemId = $request->input('payitem_id', 'all');
        $schoolId = $request->input('school_id', 'all');

        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // 1. Mulai Query Builder dengan Agregasi (Debit dikurangi Credit)
        $query = DB::table('sis_v_classuser as v')
            ->join('sis_receivable as r', 'v.user_id', '=', 'r.user_id')
            ->join('sis_payitem as p', 'r.payitem_id', '=', 'p.id')
            ->select(
                'v.user_id',
                'v.nis', 
                'v.fullname', 
                'v.class_title', 
                'p.title as jenis', 
                // Menghitung Net Piutang = Total Tagihan - Total Pembayaran
                DB::raw('(SUM(r.debit) - SUM(r.credit)) as total_piutang') 
            )
            ->where('p.payitem_type', 'tuition') // Sesuai CI2 lama
            ->where('r.tstat', '<>', 'retur')    // Mengabaikan transaksi yang diretur
            ->groupBy('v.user_id', 'v.nis', 'v.fullname', 'v.class_title', 'p.title') 
            // Hanya tampilkan yang sisa piutangnya lebih dari 0 (belum lunas)
            ->havingRaw('(SUM(r.debit) - SUM(r.credit)) > 0'); 

        // 2. Terapkan Filter Dropdown
        if ($payitemId !== 'all') {
            $query->where('r.payitem_id', $payitemId);
        }

        if ($schoolId !== 'all') {
            $query->where('v.school_id', $schoolId);
        }

        // Hitung total data SEBELUM pencarian (menggunakan subquery karena ada GROUP BY dan HAVING)
        $recordsTotal = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->count();

        // 3. Terapkan Filter Pencarian (NIS atau Nama)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('v.nis', 'like', "%{$search}%")
                  ->orWhere('v.fullname', 'like', "%{$search}%");
            });
        }

        // Hitung total data SESUDAH pencarian
        $recordsFiltered = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->count();

        // 4. Ambil Data dengan Pagination & Order
        $records = $query->offset($start)
                         ->limit($length)
                         ->orderBy('v.fullname', 'asc')
                         ->get();

        $data = [];
        $no = $start + 1;

        foreach ($records as $row) {
            
            // Tombol Report Piutang mengarah ke route report detail (sesuaikan URL-nya jika sudah ada)
            // Menggunakan user_id agar bisa menarik detail semua piutang anak tersebut
            $actionBtn = '<a href="'.url('/fincom/receivable/reportdetail/0/'.$row->user_id).'" target="_blank" class="btn btn-sm btn-secondary"><i class="bi bi-printer"></i> Report Piutang</a>';

            $data[] = [
                $no++,
                $row->nis,
                $row->fullname,
                $row->class_title,
                $row->jenis,
                'Rp ' . number_format($row->total_piutang, 0, ',', '.'),
                $actionBtn
            ];
        }

        return response()->json([
            "draw"            => intval($draw),
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data
        ]);
    }

    /**
     * Menampilkan halaman Daftar Transaksi Piutang (ilist)
     */
    public function ilist(Request $request)
    {
        $data = [
            'page_title' => 'Daftar Transaksi Piutang',
        ];

        return view('fincom.receivable.ilist', $data);
    }

   public function getIlistData(Request $request)
    {
        try {
            $draw   = $request->input('draw');
            $start  = $request->input('start', 0);
            $length = $request->input('length', 10);
            $search = $request->input('search.value');

            // 1. Query Dasar (Base Query)
            $query = DB::table('sis_receivable as r')
                ->join('sis_treceivable as tr', 'tr.id', '=', 'r.treceivable_id')
                // PERBAIKAN: Gunakan leftJoin agar transaksi dengan siswa/item terhapus tetap tampil
                ->leftJoin('sis_user as u', 'u.id', '=', 'tr.user_id')
                ->leftJoin('sis_payitem as p', 'p.id', '=', 'r.payitem_id')
                ->select(
                    'tr.id',
                    'tr.ref_no',
                    'tr.tdate',
                    'u.fullname',
                    DB::raw('SUM(r.debit) as tdebit'),
                    DB::raw("GROUP_CONCAT(p.title SEPARATOR ', ') as piutangfor"),
                    DB::raw("MAX(r.tdate) as piutangtgl"),
                    DB::raw("MAX(r.note) as rec_note")
                )
                ->where('tr.ttype', 'LIKE', '%tuition%')
                ->where('r.tstat', '!=', 'retur')
                ->where('r.debit', '>', 0)
                ->groupBy('tr.id', 'tr.ref_no', 'tr.tdate', 'u.fullname');

            // PERBAIKAN: Hitung Total Keseluruhan langsung dari clone Base Query (sebelum difilter)
            // Ini menjamin angkanya persis sama dan tidak ada selisih
            $recordsTotal = DB::query()->fromSub(clone $query, 'sub')->count();

            // 2. Terapkan Filter Pencarian (Jika ada)
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('tr.ref_no', 'like', "%{$search}%")
                      ->orWhere('u.fullname', 'like', "%{$search}%");
                });
            }

            // 3. Hitung Total Filtered (setelah dikenakan pencarian)
            $recordsFiltered = DB::query()->fromSub(clone $query, 'sub')->count();

            // 4. Ambil Data (Pagination)
            $records = $query->offset($start)
                             ->limit($length)
                             ->orderBy('tr.id', 'desc')
                             ->get();

            $data = [];
            $no = $start + 1;

            foreach ($records as $row) {
                $actionBtn = '<a href="'.route('fincom.receivable.edit', $row->id).'" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Edit</a>';

                $piutang_ket = $row->piutangfor . ' ' . ($row->piutangtgl ? date('d-M-y', strtotime($row->piutangtgl)) : '');

                $data[] = [
                    $no++,
                    $row->ref_no,
                    $row->tdate,
                    $row->fullname ?? '<i class="text-danger">Siswa Dihapus</i>', // Fallback jika user_id tidak ada
                    $piutang_ket,
                    'Rp ' . number_format((float)$row->tdebit, 0, ',', '.'),
                    $row->rec_note,
                    $actionBtn
                ];
            }

            return response()->json([
                "draw"            => intval($draw),
                "recordsTotal"    => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "error"           => "Error: " . $e->getMessage()
            ]);
        }
    }

   public function edit(Request $request, $idr)
    {
        // =========================================================
        // 1. PENGECEKAN STATUS LOCK & OTORISASI ADMIN
        // =========================================================
        $isLocked = DB::table('sis_lock')
            ->where('table_name', 'treceivable')
            ->where('table_id', $idr)
            ->exists();

        // Jika terkunci di database DAN session unlock dari admin belum aktif
        if ($isLocked && !session('unlocked_treceivable_' . $idr)) {
            // Arahkan ke route form unlock milik Anda
            return redirect()->route('fincom.receivable.unlockForm', $idr)
                             ->with('warning', 'Data ini terkunci. Akses Admin dibutuhkan untuk mengedit.');
        }

        // =========================================================
        // 2. PERSIAPAN DATA HEADER TRANSAKSI
        // =========================================================
        $treceivable = DB::table('sis_treceivable')->where('id', $idr)->first();
        if (!$treceivable) {
            abort(404, 'Data piutang tidak ditemukan');
        }

        $idt = (int) $treceivable->tid;
        
        // REVISI LOGIKA: 
        // Jika user_id > 0 (siswa sudah dipilih/tersimpan), jadikan mode "edit"
        // Jika masih 0, berarti ini benar-benar draft kosong baru ("new")
        $ket = $treceivable->user_id > 0 ? "edit" : "new";

        // UN-POSTING SEMENTARA SAAT DI-EDIT
        if ($idt > 0) {
            DB::table('sis_treceivable')->where('tid', $idt)->update(['is_posted' => 'no']);
            DB::table('sis_trans')->where('id', $idt)->update(['is_posted' => 'no']);
            DB::table('sis_ledger')->where('tid', $idt)->update(['is_posted' => 'no']);
        }

        // =========================================================
        // A. PENANGANAN REQUEST POST: TOMBOL "ADD" (Tambah Item)
        // =========================================================
        if ($request->has('add')) {
            $tvalue = $treceivable->tvalue + $this->dotFormat($request->input('tvalue'));

            DB::table('sis_treceivable')->where('id', $idr)->update([
                'user_id' => $request->input('user_id'),
                'ref_no'  => $request->input('ref_no'),
                'tdate'   => $request->input('tdate'),
                'tvalue'  => $tvalue,
                'note'    => $request->input('note'),
            ]);

            $repeat = $request->input('pay_repeat');
            $paystart = $request->input('start_date');
            $month = $request->input('month');

            if ($month == 15) {
                $month = "08";
            } elseif ($month == 16) {
                $month = "09";
            }

            $year = $request->input('year');

            if ($repeat == "once") {
                $date = $paystart;
            } elseif ($repeat == "yearly") {
                $expl = explode('-', $paystart);
                $date = $year . '-' . $expl[1] . '-' . $expl[2];
            } else {
                $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            }

            $userPayitem = DB::table('sis_userpayitem')->where('id', $request->input('userpayitem_id'))->first();

            DB::table('sis_receivable')->insert([
                'tid'            => 0, // <-- Ganti string dengan angka (contoh: 0 atau variabel integer lainnya)
                'treceivable_id' => $idr,
                'user_id'        => $request->input('user_id'),
                'tdate'          => $date,
                'payitem_id'     => $userPayitem ? $userPayitem->payitem_id : 0,
                'debit'          => $this->dotFormat($request->input('tvalue')),
                'note'           => $request->input('tnote'),
                'cdate'          => now(),
                'mdate'          => now(),
                'mdate_by'       => Auth::id() ?? 0,
                'tstat'          => "unpaid",
            ]);

            return back()->with('message', 'Item berhasil ditambahkan.');
        }

        

        // =========================================================
        // C. PENANGANAN REQUEST POST: TOMBOL "SAVE" (Finalisasi)
        // =========================================================
        if ($request->has('save')) {
            $request->validate([
                'user_id'    => 'required',
                'tdate'      => 'required',
                'payvalue'   => 'required',
                'ref_no'     => 'required',
                'count_item' => 'required|numeric|min:1'
            ], [
                'user_id.required' => 'Kolom Nama harus diisi.',
                'tdate.required' => 'Kolom Tanggal harus diisi.',
                'payvalue.required' => 'Kolom Nilai harus diisi.',
                'ref_no.required' => 'Kolom Referensi harus diisi.',
                'count_item.required' => 'Minimal harus ada 1 item piutang.',
            ]);

            DB::beginTransaction();

            try {
                if ($idt > 0) {
                    DB::table('sis_trans')->where('id', $idt)->delete();
                    DB::table('sis_ledger')->where('tid', $idt)->delete();
                }

                $idts = 0;
                DB::table('sis_treceivable')->where('id', $idr)->update([
                    'user_id'   => $request->input('user_id'),
                    'ref_no'    => $request->input('ref_no'),
                    'tdate'     => $request->input('tdate'),
                    'tvalue'    => $this->dotFormat($request->input('payvalue')),
                    'note'      => $request->input('note'),
                    'is_posted' => 'yes',
                    'mdate'     => now(),
                    'mdate_by'  => Auth::id() ?? 0,
                    'tid'       => $idts,
                ]);

                DB::table('sis_receivable')->where('treceivable_id', $idr)->update([
                    'user_id'  => $request->input('user_id'),
                    'ref_no'   => $request->input('ref_no'),
                    'mdate'    => now(),
                    'mdate_by' => Auth::id() ?? 0,
                ]);

                // Eksekusi Logika Jurnal Akuntansi (Disesuaikan dengan Service Baru)
                // $payitemService = app(PayItemService::class);
                // ... (Eksekusi Jurnal) ...

                // PENGUNCIAN DATA (my_editlock)
                // Masukkan atau perbarui flag terkunci ke tabel sis_lock 
                DB::table('sis_lock')->updateOrInsert(
                    ['table_name' => 'treceivable', 'table_id' => $idr],
                    ['table_name' => 'treceivable'] 
                );

                // Hapus session unlock setelah berhasil disave agar jika diedit lagi, minta admin lagi
                session()->forget('unlocked_treceivable_' . $idr);

                DB::commit();
                return redirect('fincom/receivable/ilist')->with('message', 'Transaksi berhasil disimpan & dikunci.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
            }
        }

        // =========================================================
        // D. PENANGANAN REQUEST POST: TOMBOL "CANCEL"
        // =========================================================
        if ($request->has('cancel')) {
            
            // Jika mengedit transaksi lama (yang sudah ter-posting),
            // kembalikan status is_posted menjadi 'yes' agar tidak tersangkut sebagai draft
            if ($idt > 0) {
                DB::table('sis_treceivable')->where('id', $idr)->update(['is_posted' => 'yes']);
                DB::table('sis_trans')->where('id', $idt)->update(['is_posted' => 'yes']);
                DB::table('sis_ledger')->where('tid', $idt)->update(['is_posted' => 'yes']);
            }

            // Bersihkan session otorisasi admin
            session()->forget('unlocked_treceivable_' . $idr);

            // Langsung redirect ke halaman daftar piutang
            return redirect('fincom/receivable/ilist');
        }

        // =========================================================
        // E. PENANGANAN REQUEST POST: TOMBOL "DELETE"
        // =========================================================
        if ($request->has('delete')) {
            DB::table('sis_treceivable')->where('id', $idr)->delete();
            DB::table('sis_receivable')->where('treceivable_id', $idr)->delete();
            DB::table('sis_trans')->where('id', $idt)->delete();
            DB::table('sis_ledger')->where('tid', $idt)->delete();

            // Lepaskan kunci karena data sudah tidak ada
            DB::table('sis_lock')->where('table_name', 'treceivable')->where('table_id', $idr)->delete();
            session()->forget('unlocked_treceivable_' . $idr);

            return redirect('fincom/receivable/ilist')->with('message', 'Transaksi berhasil dihapus.');
        }

        // =========================================================
        // B. PENANGANAN REQUEST POST: TOMBOL "SUBMIT" (Update/Del)
        // =========================================================
        if ($request->has('submit')) {
            foreach ($request->all() as $pid => $pval) {
                if (str_contains($pid, 'update_')) {
                    $idrp = explode("_", $pid)[1];

                    $trDetail = DB::table('sis_receivable')->where('id', $idrp)->first();
                    $debitLama = $trDetail ? $trDetail->debit : 0;
                    
                    $trHeader = DB::table('sis_treceivable')->where('id', $idr)->first();
                    $tvalueAwal = $trHeader->tvalue - $debitLama;
                    $kreditBaru = $tvalueAwal + $this->dotFormat($request->input('tvalue_' . $idrp));

                    DB::table('sis_treceivable')->where('id', $idr)->update(['tvalue' => $kreditBaru]);

                    $month = $request->input('month_' . $idrp);
                    if ($month == 15) {
                        $month = "08";
                    } elseif ($month == 16) {
                        $month = "09";
                    }

                    $year = $request->input('year_' . $idrp);
                    $paystart = $request->input('start_date_' . $idrp);
                    $repeat = $request->input('pay_repeat_' . $idrp);

                    if ($repeat == "once") {
                        $date = $paystart;
                    } elseif ($repeat == "yearly") {
                        $expl = explode('-', $paystart);
                        $date = $year . '-' . $expl[1] . '-' . $expl[2];
                    } else {
                        $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-25';
                    }

                    $payfor = $request->input('month_' . $idrp) > 0 
                                ? $request->input('year_' . $idrp) . "-" . str_pad($request->input('month_' . $idrp), 2, '0', STR_PAD_LEFT) . "-01" 
                                : $request->input('year_' . $idrp);

                    DB::table('sis_receivable')->where('id', $idrp)->update([
                        'tdate'  => $date,
                        'debit'  => $this->dotFormat($request->input('tvalue_' . $idrp)),
                        'payfor' => $payfor,
                        'note'   => $request->input('tnote_' . $idrp),
                    ]);
                } 
                else if (str_contains($pid, 'del_')) {
                    $idrp = explode("_", $pid)[1];

                    $trHeader = DB::table('sis_treceivable')->where('id', $idr)->first();
                    $tvalueAkhir = $trHeader->tvalue - $this->dotFormat($request->input('tvalue_' . $idrp));

                    DB::table('sis_treceivable')->where('id', $idr)->update(['tvalue' => $tvalueAkhir]);
                    DB::table('sis_receivable')->where('id', $idrp)->delete();
                }
            }
            return back()->with('message', 'Item berhasil diperbarui.');
        }

        // =========================================================
        // F. PERSIAPAN DATA UNTUK DITAMPILKAN DI VIEW (Method GET)
        // =========================================================
        
        // Ambil header sebagai obyek tunggal untuk memudahkan pemfilteran
        $headerTrans = DB::table('sis_treceivable')->where('id', $idr)->first();
        
        // --- MULAI FITUR AUTO-HEAL (KOREKSI TOTAL MILIARAN) ---
        // Hitung total murni dari nominal rincian (sis_receivable)
        if ($headerTrans) {
            $actualTotal = DB::table('sis_receivable')
                ->where('treceivable_id', $idr)
                ->sum('debit');

            // Jika total di database induk salah, koreksi dan update sekarang juga
            if ($headerTrans->tvalue != $actualTotal) {
                DB::table('sis_treceivable')->where('id', $idr)->update(['tvalue' => $actualTotal]);
            }
        }
        // --- SELESAI FITUR AUTO-HEAL ---

        // Ambil data header transaksi terbaru (setelah dikoreksi) untuk dilempar ke View
        $receivable = DB::table('sis_treceivable')->where('id', $idr)->get(); 
        
        $users = DB::table('sis_user')->get();

        // --- REVISI DROPDOWN PIUTANG ($itemspay) ---
        // Tambahkan ->where('a.user_id') agar dropdown HANYA menampilkan piutang
        // yang sudah ditugaskan/di-assign ke user/siswa pada transaksi ini.
        $itemspay = DB::table('sis_userpayitem as a')
            ->join('sis_payitem as b', 'a.payitem_id', '=', 'b.id')
            ->where('a.user_id', $headerTrans->user_id) 
            // GANTI a.amount MENJADI a.payvalue di bawah ini:
            ->select('a.id', 'a.payitem_id', 'a.payvalue', 'b.title', 'a.pay_repeat', 'a.pay_start')
            ->get();

        $items = DB::table('sis_receivable as a')
            ->join('sis_userpayitem as b', function ($join) {
                $join->on('a.payitem_id', '=', 'b.payitem_id')
                     ->on('a.user_id', '=', 'b.user_id');
            })
            ->join('sis_payitem as c', 'b.payitem_id', '=', 'c.id')
            ->where('a.treceivable_id', $idr)
            ->select('a.*', 'b.payitem_id', 'b.pay_repeat', 'b.pay_start', 'c.title')
            ->get();

        $refno = $this->generateRefNo();

        $data = [
            'ket'        => $ket,
            'receivable' => $receivable,
            'users'      => $users,
            'itemspay'   => $itemspay, // Ini variabel yang akan dipanggil di Blade
            'items'      => $items,
            'refno'      => $refno,
            'count_item' => $items->count(),
            'message'    => session('message') ?? session('success'),
            'page_title' => "Entri Piutang",
            'page_body'  => 'fincom.receivable_create_tpl'
        ];

       return view('fincom.receivable.edit', $data);
    }

    /**
     * Helper kustom pengganti dot_format
     */
    private function dotFormat($value)
    {
        if (empty($value)) return 0;
        return (float) str_replace(',', '', str_replace('.', '', $value));
    }

    /**
     * Memproses otorisasi Admin
     */
    public function unlockProcess(Request $request, $id)
    {
        $request->validate([
            'admin_username' => 'required',
            'admin_password' => 'required'
        ]);

        $username = $request->input('admin_username');
        $password = $request->input('admin_password');

        // 1. Cari user berdasarkan username
        $user = DB::table('sis_user')->where('username', 'like', $username)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Username Admin tidak ditemukan.');
        }

        // 2. Cek Password (mengikuti logika persis dari AuthController)
        $passwordMatch = false;
        $storedPass = $user->password;

        if (preg_match('/^\$2[aby]\$/', $storedPass)) {
            if (\Illuminate\Support\Facades\Hash::check($password, $storedPass)) {
                $passwordMatch = true;
            }
        } else {
            if (md5($password) === $storedPass) {
                $passwordMatch = true;
            }
        }

        if (!$passwordMatch) {
            return redirect()->back()->with('error', 'Password Admin salah.');
        }

        // 3. Cek apakah user ini benar-benar Admin (cek tabel sis_usergroup)
        // SESUAIKAN: Sama seperti di fungsi edit, ganti 1 dengan ID grup Admin Anda
        $isAdmin = DB::table('sis_usergroup')
            ->where('user_id', $user->id)
            ->where('group_id', 1) 
            ->exists();

        if ($isAdmin) {
            // Jika valid & admin, beri tanda di session bahwa ID transaksi ini boleh diedit
            session(['unlocked_treceivable_' . $id => true]);

            return redirect()->route('fincom.receivable.edit', $id)
                             ->with('success', 'Otorisasi berhasil. Anda sekarang dapat mengedit data ini.');
        }

        // Jika dia login benar tapi BUKAN admin
        return redirect()->back()->with('error', 'Akses Ditolak: Kredensial valid, namun akun ini bukan Admin.');
    }

    /**
     * Menampilkan form minta izin Admin (Unlock)
     */
    public function unlockForm($id)
    {
        $page_title = "Otorisasi Admin Dibutuhkan";
        
        // Pastikan data transaksi ada
        $details = DB::table('sis_treceivable')->where('id', $id)->first();
        if (!$details) {
            abort(404, 'Data transaksi tidak ditemukan.');
        }

        // Tampilkan view unlock
        return view('fincom.receivable.unlock', compact('id', 'page_title', 'details'));
    }

    public function create()
    {
        // 1. Dapatkan nomor referensi berurutan persis logika CI2 lama
        $ref_no = $this->generateRefNo();

        // 2. Buat draft
        $idr = DB::table('sis_treceivable')->insertGetId([
            'user_id'   => 0,
            'ref_no'    => $ref_no, // Masukkan ref_no yang sudah di-generate
            'tdate'     => now()->toDateString(), 
            'tvalue'    => 0, 
            'note'      => '',
            'is_posted' => 'no',
            'cdate'     => now(),
            'mdate'     => now(),
            'mdate_by'  => Auth::id() ?? 0,
            'tid'       => 0,
            'ttype'     => 'tuition',
            'ucode'     => mt_rand(100000, 999999) . date("YmdHis"),
        ]);

        // 3. Langsung lemparkan user ke halaman edit
        return redirect()->route('fincom.receivable.edit', $idr);
    }

    // =========================================================
    // ENDPOINT AJAX: PENCARIAN SISWA (NIS ATAU NAMA)
    // =========================================================
    public function searchStudent(Request $request)
    {
        $keyword = $request->input('nis');

        // Cari berdasarkan NIS atau Nama
        $users = DB::table('sis_user')
            ->join('sis_student', 'sis_student.id', '=', 'sis_user.id')
            ->select('sis_user.id', 'sis_user.fullname', 'sis_user.nickname', 'sis_student.nis')
            ->where('sis_student.nis', 'LIKE', "%{$keyword}%")
            ->orWhere('sis_user.fullname', 'LIKE', "%{$keyword}%")
            ->limit(20) // Batasi hasil maksimal 20 agar dropdown tidak terlalu panjang
            ->get();

        if ($users->count() > 0) {
            $html = '';
            foreach ($users as $user) {
                // Susun string HTML untuk dropdown persis seperti struktur lama
                $displayName = sprintf("%s - %s - %s", $user->fullname, $user->nickname ?? '-', $user->nis);
                
                $html .= '<a href="#" class="res-fnis-item" user_id="'.$user->id.'" user_name="'.$user->fullname.'" user_nis="'.$user->nis.'">' . $displayName . '</a>';
            }
            return response($html);
        } else {
            return response('<strong class="d-block p-3 text-danger text-center">Data siswa tidak ditemukan</strong>');
        }
    }

    // =========================================================
    // ENDPOINT AJAX: AMBIL DAFTAR PIUTANG SISWA (DROPDOWN)
    // =========================================================
    public function getUserPayItems($userId)
    {
        $itemspay = DB::table('sis_userpayitem as a')
            ->join('sis_payitem as b', 'a.payitem_id', '=', 'b.id')
            ->where('a.user_id', $userId)
            ->select('a.id', 'a.payitem_id', 'a.payvalue', 'b.title', 'a.pay_repeat', 'a.pay_start')
            ->get();

        $html = '<option value="" data-payvalue="0" data-payrepeat="" data-startdate="">-- Pilih Piutang --</option>';
        
        foreach ($itemspay as $item) {
            $formattedValue = number_format($item->payvalue, 0, ',', '.');
            $html .= sprintf(
                '<option value="%s" data-payvalue="%s" data-payrepeat="%s" data-startdate="%s">%s</option>',
                $item->id, $formattedValue, $item->pay_repeat, $item->pay_start, $item->title
            );
        }

        return response($html);
    }

    // Fungsi untuk mereplikasi logika fetch_no() & get_refno() dari aplikasi lama
    private function generateRefNo()
    {
        // Cari transaksi piutang terakhir yang memiliki prefix RCV/
        $lastRecord = DB::table('sis_treceivable')
            ->where('ref_no', 'like', 'RCV/%')
            ->orderBy('id', 'desc')
            ->first();

        // Default awal jika tidak ada data sama sekali (disesuaikan dengan posisi terakhir Anda)
        $nextSeq = 122476; 

        if ($lastRecord && $lastRecord->ref_no) {
            // Pecah string RCV/2026/Jul/30/122476 berdasarkan garis miring
            $parts = explode('/', $lastRecord->ref_no);
            // Ambil bagian paling belakang
            $lastSeq = end($parts); 
            
            // Jika bagian belakang adalah angka, tambahkan 1
            if (is_numeric($lastSeq)) {
                $nextSeq = intval($lastSeq) + 1;
            }
        }

        return 'RCV/' . date('Y/M/d/') . $nextSeq;
    }
}