<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPayItemController extends Controller
{
    public function student_list($student_id)
    {
        // 1. Ambil biodata siswa
        $student = DB::table('sis_student')
            ->leftJoin('sis_user', 'sis_student.id', '=', 'sis_user.id')
            ->leftJoin('sis_class_user', 'sis_student.id', '=', 'sis_class_user.user_id')
            ->leftJoin('sis_class_list', 'sis_class_user.class_list_id', '=', 'sis_class_list.id')
            ->where('sis_user.id', $student_id)
            ->select('sis_user.id as user_id', 'nis', 'fullname', 'sis_class_list.title as class_name')
            ->first();

        if (!$student) {
            return redirect()->route('fincom.student_list.index')->with('error', 'Data siswa tidak ditemukan.');
        }

        // 2. Ambil daftar tagihan aktif siswa
        $upitems = DB::table('sis_userpayitem')
            ->join('sis_user', 'sis_user.id', '=', 'sis_userpayitem.user_id')
            ->join('sis_payitem', 'sis_payitem.id', '=', 'sis_userpayitem.payitem_id')
            ->where('sis_userpayitem.user_id', $student_id)
            ->select(
                'sis_userpayitem.user_id',
                'sis_userpayitem.id as upid',
                'sis_userpayitem.payvalue',
                'sis_userpayitem.pay_start',
                'sis_userpayitem.pay_end',
                'sis_userpayitem.pay_repeat',
                'sis_userpayitem.coa_cash',
                'sis_userpayitem.coa_receivable',
                'sis_userpayitem.coa_revenue',
                'sis_userpayitem.coa_payable',
                'sis_userpayitem.coa_cost',
                'sis_payitem.title',
                'sis_payitem.payitem_code',
                'sis_payitem.payitem_type'
            )
            ->orderBy('sis_userpayitem.id', 'ASC')
            ->get();

        // 3. Ambil daftar komponen master untuk Dropdown Pilihan
        $payitems = DB::table('sis_payitem')
            ->where('payitem_user', 'student') 
            ->orderBy('title', 'ASC')
            ->get();

        // 4. Kamus COA lengkap untuk render teks nama akun di tabel bodi
        $coa_list = DB::table('sis_coa')->pluck('title', 'coa_code')->toArray();

        // 5. Ambil daftar pilihan Akun untuk Form Modal (Filter Query dicocokkan 100% dengan fungsi ax_get di CI2)
        $coa_cash_list       = DB::table('sis_coa')->where('title', 'like', '%KAS%')->orderBy('title', 'ASC')->get();
        $coa_payable_list    = DB::table('sis_coa')->where('title', 'like', '%HUTANG%')->orderBy('title', 'ASC')->get();
        $coa_cost_list       = DB::table('sis_coa')->where('coaclass_id', 15)->orderBy('title', 'ASC')->get(); // Mengacu pada kelas biaya ke-15
        $coa_receivable_list = DB::table('sis_coa')->where('title', 'like', '%PIUTANG%')->orderBy('title', 'ASC')->get();
        $coa_revenue_list    = DB::table('sis_coa')->where('title', 'like', '%PENDAPATAN%')->orderBy('title', 'ASC')->get();

        $data = [
            'page_title'          => 'Komponen Pembayaran Siswa',
            'student'             => $student,
            'upitems'             => $upitems,
            'payitems'            => $payitems,
            'coa_list'            => $coa_list,
            'coa_cash_list'       => $coa_cash_list,
            'coa_payable_list'    => $coa_payable_list,
            'coa_cost_list'       => $coa_cost_list,
            'coa_receivable_list' => $coa_receivable_list,
            'coa_revenue_list'    => $coa_revenue_list,
            'repeat_options'      => ['monthly', 'yearly', 'tuition', 'one time'] // Diambil dari config sis_pay_repeat CI2
        ];

        return view('fincom.userpayitem.student_list', $data);
    }

    public function add_item(Request $request, $student_id)
    {
        // Validasi input item form wajib persis tanda bintang (*) di CI2
        $request->validate([
            'payitem_id'     => 'required',
            'coa_receivable' => 'required', // Mandatory (*) di CI2
            'payvalue'       => 'required',
            'pay_start'      => 'required|date',
            'pay_end'        => 'required|date',
        ]);

        // Hilangkan separator ribuan (titik) pada nominal angka sebelum disimpan
        $clean_value = str_replace('.', '', $request->payvalue);

        // Simpan seluruh modifikasi item ke dalam tabel sis_userpayitem
        DB::table('sis_userpayitem')->insert([
            'user_id'        => $student_id,
            'payitem_id'     => $request->payitem_id,
            'payvalue'       => $clean_value,
            'pay_start'      => $request->pay_start,
            'pay_end'        => $request->pay_end,
            'pay_repeat'     => $request->pay_repeat ?? 'monthly',
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
        ]);

        return redirect()->route('fincom.userpayitem.student_list', $student_id)
                         ->with('success', 'Komponen tagihan baru berhasil ditambahkan ke siswa.');
    }

    /**
     * Menghapus komponen tagihan dari siswa
     */
    public function del_item($student_id, $id)
    {
        try {
            // Hapus data dari tabel sis_userpayitem berdasarkan ID
            DB::table('sis_userpayitem')->where('id', $id)->delete();

            // Kembali ke halaman daftar komponen siswa dengan pesan sukses
            return redirect()->route('fincom.userpayitem.student_list', $student_id)
                             ->with('success', 'Komponen tagihan berhasil dihapus dari siswa.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Jika gagal (biasanya karena sudah ada riwayat transaksi yang terkait dengan ID ini)
            return redirect()->route('fincom.userpayitem.student_list', $student_id)
                             ->with('error', 'Gagal: Komponen ini tidak bisa dihapus karena sudah memiliki riwayat transaksi/pembayaran.');
        }
    }

}