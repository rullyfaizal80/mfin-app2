<?php

namespace App\Http\Controllers\Fincom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassPayItemController extends Controller
{
    /**
     * Menampilkan daftar komponen pembayaran untuk kelas tertentu
     */
    public function index($class_list_id)
    {
        // 1. Ambil detail informasi kelas dengan relasi yang benar
        $class_info = DB::table('sis_class_list as cl')
            ->leftJoin('sis_csubject as sub', 'cl.csubject_id', '=', 'sub.id')
            ->leftJoin('sis_ctype as ty', 'cl.ctype_id', '=', 'ty.id')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->where('cl.id', $class_list_id)
            ->select(
                'cl.id', 
                'cl.title as class_title',
                'sub.title as subject_title',
                'ty.title as type_title',
                'sch.name as school_name', 
                'cy.title as year_title'
            )
            ->first();

        if (!$class_info) {
            return redirect()->route('fincom.class_list.index')->with('error', 'Data kelas tidak ditemukan.');
        }

        // 2. Ambil daftar komponen tagihan kelas (Pengganti query di ax_get_classpayitem)
        $payitems = DB::table('sis_classpayitem as cpi')
            ->leftJoin('sis_payitem as pi', 'cpi.payitem_id', '=', 'pi.id')
            ->where('cpi.class_list_id', $class_list_id)
            ->select(
                'cpi.*', 
                'pi.title as item_title', 
                'pi.payitem_code' // Diperlukan agar dapat dirender di view
            )
            ->orderBy('cpi.payvalue', 'asc')
            ->get();

        // 3. Kamus COA untuk me-render teks nama akun di dalam tabel Blade
        $coa_list = DB::table('sis_coa')->pluck('title', 'coa_code')->toArray();

        // 4. Siapkan data master untuk Dropdown Modal Form & Copy Data
        $master_payitems = DB::table('sis_payitem')->orderBy('title', 'asc')->get();
        $all_classes     = DB::table('sis_class_list')->orderBy('title', 'asc')->get();

        // 5. Filter List COA disamakan persis dengan logika di UserPayItemController
        $coa_cash       = DB::table('sis_coa')->where('title', 'like', '%KAS%')->orderBy('title', 'ASC')->get();
        $coa_payable    = DB::table('sis_coa')->where('title', 'like', '%HUTANG%')->orderBy('title', 'ASC')->get();
        $coa_cost       = DB::table('sis_coa')->where('coaclass_id', 15)->orderBy('title', 'ASC')->get();
        $coa_receivable = DB::table('sis_coa')->where('title', 'like', '%PIUTANG%')->orderBy('title', 'ASC')->get();
        $coa_revenue    = DB::table('sis_coa')->where('title', 'like', '%PENDAPATAN%')->orderBy('title', 'ASC')->get();

        // 6. Opsi perulangan
        $repeat_options = ['monthly', 'yearly', 'once', 'occasionaly']; 

        return view('fincom.classpayitem.index', compact(
            'class_info', 'payitems', 'master_payitems', 'all_classes',
            'coa_list', 'coa_cash', 'coa_receivable', 'coa_payable', 'coa_cost', 'coa_revenue', 'repeat_options'
        ));
    }

    /**
     * Menyimpan komponen tagihan baru (POST)
     */
    public function store(Request $request, $class_list_id)
    {
        // Validasi disamakan persis dengan logika form pada UserPayItemController
        $request->validate([
            'payitem_id'     => 'required',
            'coa_receivable' => 'required',
            'payvalue'       => 'required', 
            'pay_start'      => 'required|date',
            'pay_end'        => 'required|date',
        ]);

        // Hilangkan separator ribuan (titik) pada nominal angka sebelum disimpan
        $clean_value = str_replace('.', '', $request->payvalue);

        DB::table('sis_classpayitem')->insert([
            'class_list_id'  => $class_list_id,
            'payitem_id'     => $request->payitem_id,
            'payvalue'       => $clean_value, // Simpan angka bersih
            'pay_start'      => $request->pay_start,
            'pay_end'        => $request->pay_end,
            'pay_repeat'     => $request->pay_repeat ?? 'monthly', // Perulangan tagihan
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
        ]);

        return redirect()->back()->with('success', 'Komponen tagihan berhasil ditambahkan ke kelas.');
    }

    /**
     * Mengambil data spesifik untuk di-load ke Modal Edit (AJAX GET)
     */
    public function edit($id)
    {
        $data = DB::table('sis_classpayitem')->where('id', $id)->first();
        return response()->json($data);
    }

    /**
     * Memperbarui data komponen tagihan (POST/PUT)
     */
    public function update(Request $request, $id)
    {
        // Validasi disamakan persis dengan logika di UserPayItemController
        $request->validate([
            'payitem_id'     => 'required',
            'coa_receivable' => 'required',
            'payvalue'       => 'required',
            'pay_start'      => 'required|date',
            'pay_end'        => 'required|date',
        ]);

        // Bersihkan format titik pada nominal sebelum di-update
        $clean_value = str_replace('.', '', $request->payvalue);

        DB::table('sis_classpayitem')->where('id', $id)->update([
            'payitem_id'     => $request->payitem_id,
            'payvalue'       => $clean_value, // Simpan angka bersih
            'pay_start'      => $request->pay_start,
            'pay_end'        => $request->pay_end,
            'pay_repeat'     => $request->pay_repeat ?? 'monthly',
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
        ]);

        return redirect()->back()->with('success', 'Komponen tagihan berhasil diperbarui.');
    }

    /**
     * Menghapus komponen tagihan dari kelas ini
     */
    public function destroy($id)
    {
        DB::table('sis_classpayitem')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Komponen tagihan berhasil dihapus dari kelas.');
    }

    /**
     * Menyalin komponen tagihan dari kelas lain (Copy Data)
     */
    public function copy(Request $request, $class_list_id)
    {
        $from_class_id = $request->input('from_class_list_id');
        
        if (!$from_class_id || $from_class_id == '-') {
            return redirect()->back()->with('error', 'Silakan pilih kelas asal sumber data!');
        }

        // Ambil semua item tagihan dari kelas asal
        $source_items = DB::table('sis_classpayitem')->where('class_list_id', $from_class_id)->get();

        if ($source_items->isEmpty()) {
            return redirect()->back()->with('error', 'Kelas asal tidak memiliki komponen tagihan apa pun.');
        }

        // Duplikasikan ke kelas tujuan saat ini
        foreach ($source_items as $item) {
            DB::table('sis_classpayitem')->insert([
                'class_list_id'  => $class_list_id,
                'payitem_id'     => $item->payitem_id,
                'payvalue'       => $item->payvalue,
                'pay_start'      => $item->pay_start,
                'pay_end'        => $item->pay_end,
                'pay_repeat'     => $item->pay_repeat, // Disalin agar tetap akurat
                'coa_cash'       => $item->coa_cash,
                'coa_payable'    => $item->coa_payable,
                'coa_cost'       => $item->coa_cost,
                'coa_receivable' => $item->coa_receivable,
                'coa_revenue'    => $item->coa_revenue,
            ]);
        }

        return redirect()->back()->with('success', 'Berhasil menyalin ' . $source_items->count() . ' komponen tagihan dari kelas asal.');
    }

    /**
     * Tombol Sakti: Sinkronisasi massal komponen kelas ke dompet tagihan masing-masing Siswa
     */
    public function sync($class_list_id)
    {
        // 1. Cari seluruh siswa yang terdaftar di kelas ini
        $students = DB::table('sis_student')
            ->where('class_list_id', $class_list_id) 
            ->select('id')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang terdaftar aktif di kelas ini. Sinkronisasi dibatalkan.');
        }

        // 2. Ambil semua komponen tagihan milik kelas ini
        $class_items = DB::table('sis_classpayitem')->where('class_list_id', $class_list_id)->get();

        if ($class_items->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada komponen tagihan di kelas ini. Silakan isi terlebih dahulu.');
        }

        $inserted_count = 0;

        // 3. Lakukan distribusi massal menggunakan transaksi aman (DB transaction)
        DB::transaction(function () use ($students, $class_items, &$inserted_count) {
            foreach ($students as $std) {
                foreach ($class_items as $item) {
                    
                    // Cek apakah siswa sudah memiliki tagihan item ini di rentang waktu yang sama
                    $exists = DB::table('sis_userpayitem')
                        ->where('user_id', $std->id)
                        ->where('payitem_id', $item->payitem_id)
                        ->where('pay_start', $item->pay_start)
                        ->exists();

                    if (!$exists) {
                        DB::table('sis_userpayitem')->insert([
                            'user_id'        => $std->id,
                            'payitem_id'     => $item->payitem_id,
                            'payvalue'       => $item->payvalue,
                            'pay_start'      => $item->pay_start,
                            'pay_end'        => $item->pay_end,
                            'pay_repeat'     => $item->pay_repeat, // Disinkronkan juga
                            'coa_cash'       => $item->coa_cash,
                            'coa_payable'    => $item->coa_payable,
                            'coa_cost'       => $item->coa_cost,
                            'coa_receivable' => $item->coa_receivable,
                            'coa_revenue'    => $item->coa_revenue,
                            'created_at'     => now(), 
                        ]);
                        $inserted_count++;
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Sinkronisasi Berhasil! Tagihan massal berhasil disuntikkan ke seluruh dompet siswa.');
    }

    /**
     * Mengambil data default dari Master Payitem untuk auto-fill form (AJAX GET)
     */
    public function get_master_payitem($id)
    {
        $data = DB::table('sis_payitem')->where('id', $id)->first();
        
        // Kembalikan response berupa JSON untuk ditangkap oleh JavaScript
        return response()->json($data);
    }
}
