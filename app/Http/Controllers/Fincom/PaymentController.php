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

    public function edit($id)
    {
        $payment = DB::table('sis_treceivable')->where('id', $id)->first();
        if (!$payment) abort(404);

        $payment->student = DB::table('sis_user as u')
            ->leftJoin('sis_student as s', 'u.id', '=', 's.id')
            ->select('u.fullname', 's.nis')
            ->where('u.id', $payment->user_id)
            ->first();

        return view('fincom.payment.form', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        if ($request->input('action') === 'delete') {
            DB::table('sis_receivable')->where('treceivable_id', $id)->delete();
            DB::table('sis_treceivable')->where('id', $id)->delete();
            return redirect('fincom/payment')->with('success', 'Data berhasil dihapus.');
        }

        return redirect('fincom/payment')->with('success', 'Data diperbarui.');
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
        $payitems = DB::table('sis_receivable as a')
            ->join('sis_treceivable as b', 'a.treceivable_id', '=', 'b.id')
            ->join('sis_userpayitem as c', 'a.payitem_id', '=', 'c.id')
            ->join('sis_payitem as d', 'c.payitem_id', '=', 'd.id')
            ->where('a.user_id', $idj)
            ->where('b.ttype', 'tuition')
            ->where('a.tstat', 'unpaid')
            ->where('c.pay_repeat', 'occasionally')
            ->select('a.*', 'd.title', 'd.note')
            ->get();

        if ($idj <= 0) return '';

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
        // [REVISI] Query dimodifikasi agar lolos MySQL Strict Mode Laravel
        $list = DB::select("
            SELECT a.payitem_id, a.tdate, b.pay_repeat, c.title, MAX(a.note) as anote,
            (SELECT (SUM(g.debit)-SUM(g.credit)) FROM sis_receivable g 
             WHERE g.user_id = a.user_id AND g.payitem_id = a.payitem_id 
             AND g.tdate = a.tdate AND g.tstat NOT LIKE '%retur%') as tot
            FROM sis_receivable a
            JOIN sis_userpayitem b ON a.payitem_id = b.payitem_id
            JOIN sis_payitem c ON b.payitem_id = c.id
            WHERE a.user_id = ? AND b.user_id = ? AND b.pay_repeat != 'occasionally'
            GROUP BY a.payitem_id, a.tdate, b.pay_repeat, c.title, a.user_id
        ", [$iduser, $iduser]);

        $html = '<table class="table table-sm table-bordered mt-2">';
        $html .= '<thead class="table-light"><tr>
                    <th>Jenis Piutang</th>
                    <th width="150">Nilai Tagihan</th>
                    <!-- PERBAIKAN: Perlebar width dan cegah wrap pada Tanggal & Tunai -->
                    <th width="120" style="white-space: nowrap;">Tanggal</th>
                    <th width="80" class="text-center" style="white-space: nowrap;">Tunai</th>
                    <th>Note</th>
                  </tr></thead><tbody>';

        $a = 0;
        foreach ($list as $l) {
            if ($l->tot > 0) {
                $tgl = date('d M Y', strtotime($l->tdate));
                $html .= "<tr>";
                $html .= "<td>
                            <div class='form-check'>
                                <input class='form-check-input list-box' type='checkbox' name='cheked_{$a}' id='cheked_{$a}' value='check' no='{$a}'>
                                <label class='form-check-label'>{$l->title}</label>
                            </div>
                            <input type='hidden' name='userpayitem_id_{$a}' value='{$l->payitem_id}'>
                          </td>";
                $html .= "<td>
                            <input type='text' name='tvaluee_{$a}' id='tvaluee_{$a}' class='form-control form-control-sm text-end listvalue' value='".number_format($l->tot, 0, ',', '.')."' no='{$a}'>
                            <input type='hidden' id='payvalue_{$a}' value='{$l->tot}'>
                          </td>";
                // PERBAIKAN: Tambahkan white-space: nowrap pada sel Tanggal agar teks "15 Aug 2026" tidak turun baris
                $html .= "<td class='text-center' style='white-space: nowrap;'>{$tgl}<input type='hidden' name='tdatee_{$a}' value='{$l->tdate}'></td>";
                $html .= "<td class='text-center'><input type='checkbox' name='is_cash_{$a}' value='yes' checked></td>";
                $html .= "<td><input type='text' name='tnotee_{$a}' class='form-control form-control-sm' value='{$l->anote}'></td>";
                $html .= "</tr>";
                $a++;
            }
        }
        $html .= '</tbody></table>';
        $html .= "<input type='hidden' name='value_row' id='value_row' value='{$a}'>";

        if ($a == 0) return "<div class='alert alert-success text-center'>Tidak ada tagihan (Lunas)</div>";

        return $html;
    }

    public function ajaxListPaymentEdit($trxId)
    {
        return "<div class='alert alert-info text-center'>Menampilkan Tagihan Edit ID: {$trxId}</div>";
    }
}