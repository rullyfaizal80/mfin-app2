<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function lproc($period_id)
    {
        $period = DB::table('sis_period')->where('id', $period_id)->first();
        if (!$period) {
            return redirect()->route('fincom.period.index')->with('error', 'Data periode tidak ditemukan.');
        }

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

        // 1. Hitung total siswa aktif yang punya komponen tagihan di periode ini
        $querySiswa = DB::table('sis_user')
            ->where('is_student', 'yes')
            ->where('is_active', 'yes')
            ->whereExists(function ($query) use ($period) {
                $query->select(DB::raw(1))
                      ->from('sis_userpayitem as up')
                      ->join('sis_payitem as p', 'p.id', '=', 'up.payitem_id')
                      ->whereColumn('up.user_id', 'sis_user.id')
                      ->where('p.payitem_type', 'tuition')
                      ->where('up.pay_start', '<=', $period->period_end)
                      ->where('up.pay_end', '>=', $period->period_start);
            });

        $total_siswa = $querySiswa->count();

        // 2. Hitung jumlah yang sudah diproses (Bisa dari logprocess ATAU sudah ada di treceivable CI2 lama)
        $sudah_proses = DB::table('sis_user')
            ->where('is_student', 'yes')
            ->where('is_active', 'yes')
            ->whereExists(function ($query) use ($period) {
                $query->select(DB::raw(1))
                      ->from('sis_userpayitem as up')
                      ->join('sis_payitem as p', 'p.id', '=', 'up.payitem_id')
                      ->whereColumn('up.user_id', 'sis_user.id')
                      ->where('p.payitem_type', 'tuition')
                      ->where('up.pay_start', '<=', $period->period_end)
                      ->where('up.pay_end', '>=', $period->period_start);
            })
            ->where(function ($query) use ($period_id, $period) {
                $query->whereIn('id', function ($sub) use ($period_id) {
                    $sub->select('user_id')->from('sis_logprocess')->where('period_id', $period_id)->where('proc_type', 'SPP');
                })
                ->orWhereIn('id', function ($sub) use ($period) {
                    $sub->select('user_id')->from('sis_treceivable')
                        ->where('ttype', 'tuition')
                        ->where('tdate', '>=', $period->period_start)
                        ->where('tdate', '<=', $period->period_end);
                });
            })
            ->count();

        $belum_proses = max(0, $total_siswa - $sudah_proses);

        return view('fincom.batch.proc_tuition', compact(
            'period_id', 'period', 'page_title', 
            'total_siswa', 'sudah_proses', 'belum_proses'
        ));
    }

    public function processChunk(Request $request, $period_id)
    {
        $period = DB::table('sis_period')->where('id', $period_id)->first();
        if (!$period) {
            return response()->json(['status' => 'error', 'message' => 'Periode tidak valid.']);
        }

        $chunkSize = 50;
        $processedCount = 0;

        // Ambil 50 siswa yang BELUM diproses menggunakan dua whereNotIn terpisah (Sangat Aman)
        $students = DB::table('sis_user')
            ->where('is_student', 'yes')
            ->where('is_active', 'yes')
            ->whereExists(function ($query) use ($period) {
                $query->select(DB::raw(1))
                      ->from('sis_userpayitem as up')
                      ->join('sis_payitem as p', 'p.id', '=', 'up.payitem_id')
                      ->whereColumn('up.user_id', 'sis_user.id')
                      ->where('p.payitem_type', 'tuition')
                      ->where('up.pay_start', '<=', $period->period_end)
                      ->where('up.pay_end', '>=', $period->period_start);
            })
            ->whereNotIn('id', function ($query) use ($period_id) {
                $query->select('user_id')->from('sis_logprocess')
                      ->where('period_id', $period_id)->where('proc_type', 'SPP');
            })
            ->whereNotIn('id', function ($query) use ($period) {
                $query->select('user_id')->from('sis_treceivable')
                      ->where('ttype', 'tuition')
                      ->where('tdate', '>=', $period->period_start)
                      ->where('tdate', '<=', $period->period_end);
            })
            ->limit($chunkSize)
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['status' => 'finished']);
        }

        $pdate = $period->period_start;

        foreach ($students as $student) {
            try {
                DB::beginTransaction();

                $user_id = $student->id;

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
                    $ucode = date("YmdHis") . mt_rand(10000, 99999);
                    
                    $treceivable_id = DB::table('sis_treceivable')->insertGetId([
                        'user_id'   => $user_id, 'ref_no' => '', 'tdate' => $pdate, 'tvalue' => 0,
                        'note'      => sprintf("Tagihan periode: %s s/d %s", $period->period_start, $period->period_end),
                        'is_posted' => 'no', 'cdate' => now(), 'mdate' => now(),
                        'mdate_by'  => auth()->id() ?? 1, 'tid' => 0, 'ttype' => 'tuition', 'ucode' => $ucode
                    ]);

                    $tot_val = 0;

                    foreach ($upitems as $upitem) {
                        $tuition_date = date("Y-m-d");
                        if ($upitem->pay_repeat == 'yearly') $tuition_date = $upitem->pay_start;
                        else if ($upitem->pay_repeat == 'monthly') $tuition_date = $pdate;

                        $queryRcv = DB::table('sis_receivable')->where('payitem_id', $upitem->payitem_id)->where('user_id', $user_id);
                        if (in_array($upitem->pay_repeat, ['yearly', 'monthly'])) $queryRcv->where('tdate', $tuition_date);
                        
                        if (!$queryRcv->exists() && $upitem->payvalue > 0) {
                            DB::table('sis_receivable')->insert([
                                'treceivable_id' => $treceivable_id, 'user_id' => $user_id, 'ref_no' => '',
                                'tdate' => $tuition_date, 'payitem_id' => $upitem->payitem_id,
                                'debit' => $upitem->payvalue, 'credit' => 0, 'note' => $upitem->title,
                                'tstat' => 'unpaid', 'cdate' => now(), 'mdate' => now(), 'mdate_by' => auth()->id() ?? 1, 'tid' => 0
                            ]);
                            $tot_val += $upitem->payvalue;
                        }
                    }
                    DB::table('sis_treceivable')->where('id', $treceivable_id)->update(['tvalue' => $tot_val]);
                }

                DB::table('sis_logprocess')->insert([
                    'period_id' => $period_id, 'user_id' => $user_id,
                    'cdate' => now(), 'note' => 'Sukses diproses (Sync CI2)', 'proc_type' => 'SPP'
                ]);

                DB::commit();
                $processedCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                continue; 
            }
        }

        return response()->json([
            'status' => 'processing',
            'processed_count' => $processedCount
        ]);
    }
}