<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $month  = $request->input('month', date('m'));
        $year   = $request->input('year', date('Y'));
        $userId = $request->input('user_id'); 
        $fnis   = $request->input('fnis');    

        $query = DB::table('sis_treceivable as tr')
            ->join('sis_receivable as r', 'tr.id', '=', 'r.treceivable_id') 
            ->join('sis_user as u', 'r.user_id', '=', 'u.id') 
            ->leftJoin('sis_user as cashier', 'tr.mdate_by', '=', 'cashier.id')
            ->select(
                'tr.id as item_id',
                'tr.tdate',
                'tr.ref_no',
                'u.fullname as student_name',
                'cashier.fullname as cashier_name',
                'tr.note',
                'tr.tvalue as credit'
            )
            ->where('tr.ref_no', 'like', 'PYM%')
            ->groupBy('tr.id', 'tr.tdate', 'tr.ref_no', 'u.fullname', 'cashier.fullname', 'tr.note', 'tr.tvalue')
            ->orderBy('tr.tdate', 'desc')
            ->orderBy('tr.id', 'desc');

        if ($userId) $query->where('r.user_id', $userId);
        if ($month) $query->whereMonth('tr.tdate', $month);
        if ($year) $query->whereYear('tr.tdate', $year);

        $payments = $query->paginate(20)->withQueryString();

        return view('fincom.payment.index', [
            'req'      => $request,
            'payments' => $payments,
            'curMonth' => $month,
            'curYear'  => $year,
            'fnis'     => $fnis,
            'user_id'  => $userId
        ]);
    }

    /**
     * TAMPILAN FORM ENTRI BARU
     */
    public function create()
    {
        // Preview Generate Ref No untuk tampilan (akan digenerate ulang saat disave agar tidak bentrok)
        $lastRecord = DB::table('sis_treceivable')
            ->where('ref_no', 'like', 'PYM/%')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextSeqNum = 1;
        if ($lastRecord && !empty($lastRecord->ref_no)) {
            $parts = explode('/', $lastRecord->ref_no);
            $lastStringNumber = end($parts); 
            if (is_numeric($lastStringNumber)) {
                $nextSeqNum = (int) $lastStringNumber + 1;
            }
        }

        $nextSeqFormatted = str_pad($nextSeqNum, 6, '0', STR_PAD_LEFT);
        $refno = "PYM/" . date('Y') . "/" . date('M') . "/" . date('d') . "/" . $nextSeqFormatted;
        
        return view('fincom.payment.form', compact('refno'));
    }

    /**
     * SIMPAN DATA PEMBAYARAN BARU
     */
    public function store(Request $request)
    {
        // 1. Pastikan siswa sudah dipilih (user_id tidak boleh kosong)
        $request->validate([
            'user_id' => 'required|integer',
            'tdate'   => 'required|date',
        ], [
            'user_id.required' => 'Nama Siswa belum dipilih!',
            'user_id.integer'  => 'Pilih nama siswa dari daftar pencarian yang muncul.',
        ]);

        $userId = $request->input('user_id');
        $tdate = $request->input('tdate');
        $payvalue = (float) str_replace('.', '', $request->input('payvalue', 0));

        DB::beginTransaction();
        try {
            // Generate Ulang + Kunci tabel agar nomor referensi tidak ganda jika ada 2 kasir
            $lastRecord = DB::table('sis_treceivable')
                ->where('ref_no', 'like', 'PYM/%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSeqNum = 1;
            if ($lastRecord && !empty($lastRecord->ref_no)) {
                $parts = explode('/', $lastRecord->ref_no);
                $lastStringNumber = end($parts); 
                if (is_numeric($lastStringNumber)) {
                    $nextSeqNum = (int) $lastStringNumber + 1;
                }
            }

            $nextSeqFormatted = str_pad($nextSeqNum, 6, '0', STR_PAD_LEFT);
            $refNo = "PYM/" . date('Y') . "/" . date('M') . "/" . date('d') . "/" . $nextSeqFormatted;

            // Insert Header (sis_treceivable)
            $trId = DB::table('sis_treceivable')->insertGetId([
                'user_id'   => $userId,
                'ref_no'    => $refNo,
                'tdate'     => $tdate,
                'tvalue'    => $payvalue,
                'note'      => $request->input('note') ?? '', // Cegah Null
                'is_posted' => 'yes',
                'cdate'     => now(),
                'mdate'     => now(),
                'mdate_by'  => Auth::id() ?? 1,
                'ttype'     => 'tuition_payment',
                'tid'       => 0, // [Perbaikan] Warisan CI2, harus didefinisikan agar tidak error
                'ucode'     => mt_rand(100000, 999999) . date('YmdHis'),
            ]);

            // 2. Insert Detail Pembayaran (sis_receivable)
            $inputs = $request->all();
            foreach ($inputs as $key => $value) {
                // Deteksi input checkbox dari AJAX
                if (str_starts_with($key, 'cheked_')) {
                    $n = str_replace('cheked_', '', $key);
                    
                    $payitemId = $inputs["userpayitem_id_$n"] ?? 0;
                    $nominal = (float) str_replace('.', '', $inputs["tvaluee_$n"] ?? 0);
                    
                    // [PERBAIKAN] Ambil tanggal TAGIHAN ASLI, bukan tanggal bayar hari ini
                    $originalBillDate = $inputs["tdatee_$n"] ?? $tdate; 
                    
                    if ($nominal > 0) {
                        DB::table('sis_receivable')->insert([
                            'treceivable_id' => $trId,
                            'user_id'        => $userId,
                            'ref_no'         => $refNo,
                            
                            // Gunakan tanggal tagihan asli agar query SUM(debit)-SUM(credit) sinkron
                            'tdate'          => $originalBillDate, 
                            'payitem_id'     => $payitemId,
                            'payfor'         => $originalBillDate, 
                            
                            'credit'         => $nominal,
                            'note'           => $inputs["tnotee_$n"] ?? '',
                            'cdate'          => now(),
                            'mdate'          => now(),
                            'mdate_by'       => Auth::id() ?? 1,
                            'tstat'          => 'paid',
                            'mode'           => '',
                            'is_cash'        => isset($inputs["is_cash_$n"]) ? 'yes' : 'no',
                            'tid'            => 0,
                            'debit'          => 0, 
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->input('action') === 'save_print') {
                return redirect('fincom/payment/reportall/' . $trId);
            }
            return redirect('fincom/payment')->with('success', 'Pembayaran berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Akan melempar error ke session 'error' yang baru saja kita pasang di Blade
            return back()->with('error', $e->getMessage());
        }
    }

   /**
     * TAMPILAN FORM EDIT (DENGAN PROTEKSI ADMIN)
     */
    public function edit($id)
    {
        // CEK LOCK: Jika session unlock untuk ID ini belum ada, arahkan ke halaman Unlock
        if (!session("unlocked_payment_{$id}")) {
            return redirect()->route('fincom.payment.unlock', $id);
        }

        $payment = DB::table('sis_treceivable')->where('id', $id)->first();
        if (!$payment) abort(404);

        // Ambil data siswa
        $payment->student = DB::table('sis_user as u')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->select('u.fullname', 's.nis')
            ->where('u.id', $payment->user_id)
            ->first();

        return view('fincom.payment.form', compact('payment'));
    }

    /**
     * PROSES UPDATE ATAU DELETE
     */
    public function update(Request $request, $id)
    {
        if ($request->input('action') === 'delete') {
            DB::table('sis_receivable')->where('treceivable_id', $id)->delete();
            DB::table('sis_treceivable')->where('id', $id)->delete();
            
            session()->forget("unlocked_payment_{$id}"); // Kunci kembali setelah dihapus
            return redirect('fincom/payment')->with('success', 'Data berhasil dihapus.');
        }

        // Logic Update (Sesuai dengan kebutuhan, misal retur / update nominal)
        // ... (Bisa dikembangkan sesuai struktur update CI2) ...

        session()->forget("unlocked_payment_{$id}"); // Kunci kembali setelah update berhasil
        return redirect('fincom/payment')->with('success', 'Data diperbarui.');
    }

    /**
     * TAMPILKAN FORM UNLOCK ADMIN
     */
    public function unlock($id)
    {
        $page_title = "Buka Kunci Edit Pembayaran";
        return view('fincom.payment.unlock', compact('id', 'page_title'));
    }

    /**
     * PROSES VALIDASI PASSWORD ADMIN
     */
    public function processUnlock(Request $request, $id)
    {
        $request->validate([
            'admin_username' => 'required',
            'admin_password' => 'required',
        ]);

        // Cek data admin dari tabel sis_user
        // CATATAN: Ubah kolom 'username' jika di tabel Anda namanya berbeda (misal: 'email' atau 'userid')
        $admin = DB::table('sis_user')
            ->where('username', $request->admin_username)
            ->first();

        if ($admin) {
            // [PENTING] Jika password DB lama (CI2) menggunakan MD5, gunakan: 
            // if (md5($request->admin_password) === $admin->password) {
            
            // Jika sudah menggunakan enkripsi Bcrypt Laravel (standar modern), gunakan:
            if (\Hash::check($request->admin_password, $admin->password)) { 
                
                // Beri tanda bahwa ID ini sudah di-unlock di session
                session(["unlocked_payment_{$id}" => true]);
                
                return redirect()->route('fincom.payment.edit', $id);
            }
        }

        // Jika gagal
        return back()->with('error', 'Username atau Password Admin salah!');
    }

    /* =========================================================================
       FUNGSI AJAX
       ========================================================================= */

    public function ajaxGetNis(Request $request)
    {
        $nis = $request->input('nis');
        
        $users = DB::table('sis_v_classuser')
            ->where('nis', 'like', "%$nis%")
            ->orWhere('fullname', 'like', "%$nis%")
            ->limit(20)
            ->get();

        if ($users->isEmpty()) {
            return '<strong style="padding:6px;">No Data</strong>';
        }

        $html = '';
        foreach ($users as $user) {
            $html .= '<div class="res-fnis-item" data-user_id="'.$user->user_id.'" data-user_name="'.$user->fullname.'" data-user_nis="'.$user->nis.'">';
            $html .= sprintf("%s - %s - %s", $user->fullname, $user->class_title, $user->nis);
            $html .= '</div>';
        }

        return $html;
    }

   public function ajaxGetUserPayId($idj)
    {
        if ($idj <= 0) return '';

        // 1. Cari payitem_id yang statusnya masih unpaid untuk user ini (Sangat cepat karena index user_id)
        $unpaidIds = DB::table('sis_receivable')
            ->where('user_id', $idj)
            ->where('tstat', 'unpaid')
            ->pluck('payitem_id');

        // 2. Ambil data dropdown secara mandiri
        $payitems = DB::table('sis_userpayitem as up')
            ->join('sis_payitem as p', 'up.payitem_id', '=', 'p.id')
            ->where('up.user_id', $idj)
            ->where('up.pay_repeat', 'occasionally')
            ->whereIn('up.payitem_id', $unpaidIds)
            ->select('p.id as payitem_id', 'p.title', 'p.note')
            ->get();

        $html = '<option value="0" id="pilih">-- Pilih Jenis Pembayaran --</option>';
        foreach ($payitems as $item) {
            $html .= '<option value="'.$item->payitem_id.'">'.$item->title.' - '.$item->note.'</option>';
        }

        return $html;
    }

    public function ajaxGetUserPayment($uid, $id)
    {
        $row = DB::table('sis_receivable as a')
            ->join('sis_userpayitem as b', 'a.payitem_id', '=', 'b.id')
            ->where('a.user_id', $uid)
            ->where('b.id', $id)
            ->where('b.user_id', $uid)
            ->whereRaw("a.treceivable_id IN (SELECT id FROM sis_treceivable WHERE ttype = 'tuition')")
            ->select('a.*', 'b.pay_repeat')
            ->first();

        return response()->json($row ?: []);
    }

   public function ajaxListPayment($iduser)
    {
        // 1. Load setting tagihan siswa ke memori (Menghindari Full Table Scan akibat tidak ada index)
        $userPayItems = DB::table('sis_userpayitem')
            ->where('user_id', $iduser)
            ->get()
            ->keyBy('payitem_id');

        // 2. Ambil referensi master nama tagihan
        $payItemsMaster = DB::table('sis_payitem')->get()->keyBy('id');

        // 3. Query Agregasi Murni (Hanya query ke 1 tabel yang punya Index)
        $receivables = DB::table('sis_receivable')
            ->select('payitem_id', 'tdate', DB::raw('MAX(note) as anote'), DB::raw('(SUM(debit) - SUM(credit)) as tot'))
            ->where('user_id', $iduser)
            ->where('tstat', '!=', 'retur') 
            ->groupBy('payitem_id', 'tdate')
            ->having('tot', '>', 0)
            ->orderBy('tdate', 'asc')
            ->get();

        $html = '<table class="table table-sm table-bordered mt-2 align-middle">';
        $html .= '<thead class="table-light"><tr>
                    <th>Jenis Piutang</th>
                    <th width="150">Nilai Tagihan</th>
                    <th width="120" style="white-space: nowrap;">Tanggal Piutang</th>
                    <th width="80" class="text-center" style="white-space: nowrap;">Tunai</th>
                    <th>Note</th>
                  </tr></thead><tbody>';

        $a = 0;
        foreach ($receivables as $l) {
            // Filter: Abaikan jika pay_repeat = occasionally
            $up = $userPayItems->get($l->payitem_id);
            if ($up && $up->pay_repeat == 'occasionally') {
                continue; 
            }

            $title = $payItemsMaster->has($l->payitem_id) ? $payItemsMaster->get($l->payitem_id)->title : 'Unknown';
            $tgl = date('d M Y', strtotime($l->tdate));
            
            $html .= "<tr>";
            $html .= "<td>
                        <div class='form-check'>
                            <input class='form-check-input list-box' type='checkbox' name='cheked_{$a}' id='cheked_{$a}' value='check' no='{$a}'>
                            <label class='form-check-label'>{$title}</label>
                        </div>
                        <input type='hidden' name='userpayitem_id_{$a}' value='{$l->payitem_id}'>
                      </td>";
            $html .= "<td>
                        <input type='text' name='tvaluee_{$a}' id='tvaluee_{$a}' class='form-control form-control-sm text-end listvalue' value='".number_format($l->tot, 0, ',', '.')."' no='{$a}'>
                        <input type='hidden' id='payvalue_{$a}' value='{$l->tot}'>
                      </td>";
            $html .= "<td class='text-center' style='white-space: nowrap;'>{$tgl}<input type='hidden' name='tdatee_{$a}' value='{$l->tdate}'></td>";
            $html .= "<td class='text-center'><input type='checkbox' name='is_cash_{$a}' value='yes' checked></td>";
            $html .= "<td><input type='text' name='tnotee_{$a}' class='form-control form-control-sm' value='{$l->anote}'></td>";
            $html .= "</tr>";
            $a++;
        }
        
        $html .= '</tbody></table>';
        $html .= "<input type='hidden' name='value_row' id='value_row' value='{$a}'>";

        if ($a == 0) return "<div class='alert alert-success text-center'>Tidak ada tagihan (Lunas)</div>";

        return $html;
    }

   public function ajaxListPaymentEdit($trxId)
    {
        // 1. Ambil rincian tagihan dari database
        $list = DB::table('sis_receivable as a')
            ->join('sis_userpayitem as b', function($join) {
                $join->on('a.user_id', '=', 'b.user_id')
                     ->on('a.payitem_id', '=', 'b.payitem_id');
            })
            ->join('sis_payitem as c', 'b.payitem_id', '=', 'c.id')
            ->select('a.*', 'b.pay_repeat', 'c.title')
            ->where('a.treceivable_id', $trxId)
            ->get();

        // 2. Bangun Header Tabel
        $html = '<table class="table table-sm table-bordered mt-2 align-middle">';
        $html .= '<thead class="table-light"><tr>
                    <th>Jenis Piutang</th>
                    <th width="150">Nilai</th>
                    <th width="120" style="white-space: nowrap;">Tanggal Piutang</th>
                    <th>Note</th>
                    <th width="160" class="text-center">Aksi</th>
                  </tr></thead><tbody>';

        // 3. Bangun Isi Tabel (Looping Data)
        if ($list->isEmpty()) {
            $html .= '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada rincian tagihan.</td></tr>';
        } else {
            foreach ($list as $l) {
                $isRetur = ($l->tstat === 'retur');
                $bgClass = $isRetur ? 'bg-light text-muted' : '';
                $tgl = date('d M Y', strtotime($l->tdate));
                $creditFormat = number_format($l->credit, 0, ',', '.');
                
                $html .= "<tr class='{$bgClass}'>";
                
                // Kolom Jenis Piutang
                $html .= "<td>{$l->title}<input type='hidden' name='payitem_id_{$l->id}' value='{$l->payitem_id}'></td>";
                
                if ($isRetur) {
                    // Tampilan jika statusnya RETUR (di-disable / tidak bisa diedit)
                    $html .= "<td><input type='text' class='form-control form-control-sm text-end' value='{$creditFormat}' disabled></td>";
                    $html .= "<td class='text-center' style='white-space: nowrap;'>{$tgl}</td>";
                    $html .= "<td><input type='text' class='form-control form-control-sm' value='{$l->note}' disabled></td>";
                    $html .= "<td class='text-center fw-bold text-danger' style='font-size: 12px;'>-- RETUR --</td>";
                } else {
                    // Tampilan normal (bisa diedit)
                    $html .= "<td>
                                <input type='text' name='tvalue_{$l->id}' class='form-control form-control-sm text-end' value='{$creditFormat}'>
                              </td>";
                    $html .= "<td class='text-center' style='white-space: nowrap;'>
                                {$tgl}<input type='hidden' name='date_{$l->id}' value='{$l->tdate}'>
                              </td>";
                    $html .= "<td>
                                <input type='text' name='tnote_{$l->id}' class='form-control form-control-sm' value='{$l->note}'>
                              </td>";
                    
                    // Kolom Tombol Aksi (Upd, Del, Retur)
                    $html .= "<td class='text-center'>
                                <div class='btn-group btn-group-sm'>
                                    <button type='submit' name='action' value='update_{$l->id}' class='btn btn-outline-primary' title='Update'>Upd</button>
                                    <button type='submit' name='action' value='del_{$l->id}' class='btn btn-outline-danger' title='Delete' onclick=\"return confirm('Hapus item ini?')\">Del</button>
                                    <button type='submit' name='action' value='ret_{$l->id}' class='btn btn-outline-warning' title='Retur' onclick=\"return confirm('Retur item ini?')\">Retur</button>
                                </div>
                              </td>";
                }
                
                $html .= "</tr>";
            }
        }
        
        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * CETAK BUKTI PEMBAYARAN (STRUK)
     */
    public function reportall($id)
    {
        // 1. Ambil Data Header Transaksi
        $payment = DB::table('sis_treceivable as tr')
            ->join('sis_user as u', 'tr.user_id', '=', 'u.id')
            ->leftJoin('sis_user as c', 'tr.mdate_by', '=', 'c.id')
            ->leftJoin('sis_v_classuser as vc', 'tr.user_id', '=', 'vc.user_id')
            ->select('tr.*', 'u.fullname', 'vc.class_title as kelas', 'c.fullname as cashier')
            ->where('tr.id', $id)
            ->first();

        if (!$payment) abort(404, 'Data Pembayaran tidak ditemukan.');

        // 2. Ambil Item yang Dibayar (tstat = 'paid')
        $items = DB::table('sis_receivable as a')
            ->join('sis_payitem as p', 'a.payitem_id', '=', 'p.id')
            ->leftJoin('sis_userpayitem as up', function($join) {
                $join->on('a.payitem_id', '=', 'up.payitem_id')
                     ->on('a.user_id', '=', 'up.user_id');
            })
            ->select('a.*', 'p.title', 'up.pay_repeat')
            ->where('a.treceivable_id', $id)
            ->where('a.tstat', 'paid')
            ->get();

        // 3. Ambil Item Retur (jika ada)
        $returs = DB::table('sis_receivable as a')
            ->join('sis_payitem as p', 'a.payitem_id', '=', 'p.id')
            ->leftJoin('sis_userpayitem as up', function($join) {
                $join->on('a.payitem_id', '=', 'up.payitem_id')
                     ->on('a.user_id', '=', 'up.user_id');
            })
            ->select('a.*', 'p.title', 'up.pay_repeat')
            ->where('a.treceivable_id', $id)
            ->where('a.tstat', 'retur')
            ->get();

        // [REVISI] Query blmLunas / tunggakan sudah dihapus dari sini

        // Jangan lupa variabel $blmLunas juga dihapus dari compact()
        return view('fincom.payment.reportall', compact('payment', 'items', 'returs'));
    }
}