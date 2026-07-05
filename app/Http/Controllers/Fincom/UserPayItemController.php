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

        // 2. Ambil daftar tagihan (LENGKAP SESUAI GAMBAR CI2)
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

        // 3. Ambil daftar komponen untuk Dropdown Tambah
        $payitems = DB::table('sis_payitem')
            ->where('payitem_user', 'student') 
            ->orderBy('title', 'ASC')
            ->get();

        // 4. Ambil Kamus COA (Chart of Account) pengganti fungsi coa_list() di CI2
        // Ini untuk menerjemahkan kode akun (misal 1-101) jadi teks (misal "KAS")
        $coa_list = DB::table('sis_coa')->pluck('title', 'coa_code')->toArray();

        $data = [
            'page_title' => 'Komponen Pembayaran Siswa',
            'student'    => $student,
            'upitems'    => $upitems,
            'payitems'   => $payitems,
            'coa_list'   => $coa_list, // Lempar kamus COA ke View
        ];

        return view('fincom.userpayitem.student_list', $data);
    }
}