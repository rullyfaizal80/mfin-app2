<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function lproc($period_id)
    {
        // Ambil data periode
        $period = DB::table('sis_period')->where('id', $period_id)->first();
        
        // Jika periode tidak ada, kembalikan ke halaman daftar periode
        if (!$period) {
            return redirect()->route('fincom.period.index')->with('error', 'Data periode tidak ditemukan.');
        }

        // Siapkan data untuk view
        $page_title = "Daftar Proses Periode";
        $ttype = $period->ttype;

        return view('fincom.batch.lproc', compact('period_id', 'ttype', 'page_title', 'period'));
    }

    public function procTuition($period_id)
    {
        $period = DB::table('sis_period')->where('id', $period_id)->first();
        if (!$period || $period->ttype != 'student') {
            return redirect()->route('fincom.period.index')->with('error', 'Periode tidak valid untuk proses SPP.');
        }

        $page_title = "Proses Tagihan SPP Bulanan";
        return view('fincom.batch.proc_tuition', compact('period_id', 'period', 'page_title'));
    }

    // ===========================================================================
    // 1. FUNGSI AMBIL 1 SISWA (YANG BELUM DIPROSES)
    // ===========================================================================
    public function getStudent(Request $request, $period_id)
    {
        // Cari 1 siswa aktif yang belum ada di tabel sis_logprocess untuk periode ini
        $student = DB::table('sis_user')
            ->select('id', 'fullname')
            ->where('is_student', 'yes')
            ->where('is_active', 'yes')
            ->whereNotIn('id', function ($query) use ($period_id) {
                $query->select('user_id')
                      ->from('sis_logprocess')
                      ->where('period_id', $period_id);
            })
            ->orderBy('fullname', 'asc')
            ->first();

        if ($student) {
            return response()->json([
                'status'   => 'ok',
                'user_id'  => $student->id,
                'fullname' => $student->fullname
            ]);
        }

        // Jika sudah habis
        return response()->json(['status' => 'empty']);
    }

    // ===========================================================================
    // 2. FUNGSI PROSES TAGIHAN (SPP) PER SISWA
    // ===========================================================================
    public function processTuition(Request $request, $period_id, $user_id)
    {
        try {
            DB::beginTransaction();

            $period = DB::table('sis_period')->where('id', $period_id)->first();
            $pdate  = $period->period_start;

            // Ambil daftar komponen tagihan siswa (SPP, dll) yang aktif pada periode ini
            $upitems = DB::table('sis_userpayitem as up')
                ->join('sis_payitem as p', 'p.id', '=', 'up.payitem_id')
                ->where('p.payitem_type', 'tuition')
                ->where('up.user_id', $user_id)
                ->where('up.pay_start', '<=', $period->period_end)
                ->where('up.pay_end', '>=', $period->period_start)
                ->select('up.user_id', 'up.payitem_id', 'p.payitem_code', 'up.payvalue', 'up.pay_repeat', 'up.pay_start', 'up.pay_end', 'p.title')
                ->orderBy('p.ordering', 'asc')
                ->get();

            if ($upitems->count() > 0) {
                // 1. Buat Induk Tagihan (Treceivable / Total Receivable)
                $ucode = date("YmdHis") . mt_rand(10000, 99999);
                
                $treceivable_id = DB::table('sis_treceivable')->insertGetId([
                    'user_id'   => $user_id,
                    'ref_no'    => '', // Akan diisi jika sudah ada nomor referensi
                    'tdate'     => $pdate,
                    'tvalue'    => 0,
                    'note'      => sprintf("Tagihan periode: %s s/d %s", $period->period_start, $period->period_end),
                    'is_posted' => 'no',
                    'cdate'     => now(),
                    'mdate'     => now(),
                    'mdate_by'  => auth()->id() ?? 1,
                    'tid'       => 0,
                    'ttype'     => 'tuition',
                    'ucode'     => $ucode
                ]);

                $tot_val = 0;

                // 2. Looping Komponen Tagihan (Detail Receivable)
                foreach ($upitems as $upitem) {
                    $tuition_date = date("Y-m-d");

                    // Tentukan tanggal berdasarkan siklus berulang (pay_repeat)
                    if ($upitem->pay_repeat == 'yearly') {
                        $tuition_date = $upitem->pay_start;
                    } else if ($upitem->pay_repeat == 'monthly') {
                        $tuition_date = $pdate;
                    }

                    // Cek apakah detail tagihan ini sudah pernah dibuat sebelumnya
                    $queryRcv = DB::table('sis_receivable')
                        ->where('payitem_id', $upitem->payitem_id)
                        ->where('user_id', $user_id);

                    if ($upitem->pay_repeat == 'yearly' || $upitem->pay_repeat == 'monthly') {
                        $queryRcv->where('tdate', $tuition_date);
                    }
                    $rcvExists = $queryRcv->exists();

                    // Jika belum ada, masukkan sebagai rincian tagihan (Receivable)
                    if (!$rcvExists && $upitem->payvalue > 0) {
                        DB::table('sis_receivable')->insert([
                            'treceivable_id' => $treceivable_id,
                            'user_id'        => $user_id,
                            'ref_no'         => '',
                            'tdate'          => $tuition_date,
                            'payitem_id'     => $upitem->payitem_id,
                            'debit'          => $upitem->payvalue,
                            'credit'         => 0,
                            'note'           => $upitem->title,
                            'tstat'          => 'unpaid',
                            'cdate'          => now(),
                            'mdate'          => now(),
                            'mdate_by'       => auth()->id() ?? 1,
                            'tid'            => 0
                        ]);

                        $tot_val += $upitem->payvalue;
                    }
                }

                // Update Total Tagihan di Induk
                DB::table('sis_treceivable')->where('id', $treceivable_id)->update([
                    'tvalue' => $tot_val
                ]);

                // =========================================================================
                // CATATAN PENTING: 
                // Di aplikasi lama, di titik ini memanggil $this->libpayitem->transact();
                // Untuk tahap ini, saya melewatinya sementara agar kita bisa fokus 
                // memastikan tagihannya masuk ke database terlebih dahulu.
                // =========================================================================
            }

            // 3. Catat di tabel logprocess agar siswa ini tidak diproses 2 kali
            DB::table('sis_logprocess')->insert([
                'period_id' => $period_id,
                'user_id'   => $user_id,
                'cdate'     => now(),
                'note'      => 'Sukses diproses',
                'proc_type' => 'SPP'
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Processed']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
