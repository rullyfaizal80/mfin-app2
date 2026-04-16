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
}