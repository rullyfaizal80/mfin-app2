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
        // 1. Ambil detail informasi kelas dengan relasi yang benar (sis_class_list / sis_csubject / sis_ctype)
        $class_info = DB::table('sis_class_list as cl')
            ->leftJoin('sis_csubject as sub', 'cl.csubject_id', '=', 'sub.id') // Mengambil data Mata Pelajaran / Rumpun
            ->leftJoin('sis_ctype as ty', 'cl.ctype_id', '=', 'ty.id')        // Mengambil data Tipe Kelas (Misal: Ekstra)
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->where('cl.id', $class_list_id)
            ->select(
                'cl.id', 
                'cl.title as class_title',     // Contoh: E.FUTSAL
                'sub.title as subject_title',   // Contoh: NON-JURUSAN
                'ty.title as type_title',       // Contoh: EKSTRA
                'sch.name as school_name', 
                'cy.title as year_title'
            )
            ->first();

        if (!$class_info) {
            return redirect()->route('fincom.class_list.index')->with('error', 'Data kelas tidak ditemukan.');
        }

        // 2. Ambil daftar komponen tagihan yang sudah tersimpan untuk kelas ini
        $payitems = DB::table('sis_classpayitem as cpi')
            ->leftJoin('sis_payitem as pi', 'cpi.payitem_id', '=', 'pi.id')
            ->where('cpi.class_list_id', $class_list_id)
            ->select('cpi.*', 'pi.title as item_title')
            ->get();

        // 3. Siapkan data master untuk Dropdown di dalam Modal Form
        $master_payitems = DB::table('sis_payitem')->orderBy('title', 'asc')->get();
        $all_classes     = DB::table('sis_class_list')->orderBy('title', 'asc')->get();

        // Master COA dipisahkan berdasarkan rumpun Akuntansi
        $coa_cash       = DB::table('sis_coa')->where('coaclass_id', 11)->orderBy('coa_code', 'asc')->get();
        $coa_receivable = DB::table('sis_coa')->where('coaclass_id', 12)->orderBy('coa_code', 'asc')->get();
        $coa_payable    = DB::table('sis_coa')->where('coaclass_id', 13)->orderBy('coa_code', 'asc')->get();
        $coa_cost       = DB::table('sis_coa')->where('coaclass_id', 15)->orderBy('coa_code', 'asc')->get();
        $coa_revenue    = DB::table('sis_coa')->where('coaclass_id', 41)->orderBy('coa_code', 'asc')->get();

        return view('fincom.classpayitem.index', compact(
            'class_info', 'payitems', 'master_payitems', 'all_classes',
            'coa_cash', 'coa_receivable', 'coa_payable', 'coa_cost', 'coa_revenue'
        ));
    }

    /**
     * Menyimpan komponen tagihan baru (POST)
     */
    public function store(Request $request, $class_list_id)
    {
        $request->validate([
            'payitem_id' => 'required',
            'payvalue'   => 'required|numeric',
            'pay_start'  => 'required|date',
            'pay_end'    => 'required|date',
        ]);

        DB::table('sis_classpayitem')->insert([
            'class_list_id'  => $class_list_id,
            'payitem_id'     => $request->payitem_id,
            'payvalue'       => $request->payvalue,
            'pay_start'      => $request->pay_start,
            'pay_end'        => $request->pay_end,
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
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
        $request->validate([
            'payitem_id' => 'required',
            'payvalue'   => 'required|numeric',
            'pay_start'  => 'required|date',
            'pay_end'    => 'required|date',
        ]);

        DB::table('sis_classpayitem')->where('id', $id)->update([
            'payitem_id'     => $request->payitem_id,
            'payvalue'       => $request->payvalue,
            'pay_start'      => $request->pay_start,
            'pay_end'        => $request->pay_end,
            'coa_cash'       => $request->coa_cash ?? 0,
            'coa_payable'    => $request->coa_payable ?? 0,
            'coa_cost'       => $request->coa_cost ?? 0,
            'coa_receivable' => $request->coa_receivable ?? 0,
            'coa_revenue'    => $request->coa_revenue ?? 0,
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
        // (Sesuaikan relasi join 'sis_student' atau tabel mapping kelas di DB Anda jika berbeda)
        $students = DB::table('sis_student')
            ->where('class_list_id', $class_list_id) // atau jika menggunakan sis_user_class silakan disesuaikan
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
                    
                    // Cek apakah siswa sudah memiliki tagihan item ini di rentang waktu yang sama (menghindari duplikasi dobel klik)
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
                            'coa_cash'       => $item->coa_cash,
                            'coa_payable'    => $item->coa_payable,
                            'coa_cost'       => $item->coa_cost,
                            'coa_receivable' => $item->coa_receivable,
                            'coa_revenue'    => $item->coa_revenue,
                            'created_at'     => now(), // Opsional jika tabel laravel menggunakan timestamp
                        ]);
                        $inserted_count++;
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Sinkronisasi Berhasil! Tagihan massal berhasil disuntikkan ke seluruh dompet siswa.');
    }

    /**
     * Datatables AJAX (Diadaptasi dari CodeIgniter ke Laravel)
     */
    public function ax_get_classpayitem(Request $request, $class_id)
    {
        $nums   = $request->input('iDisplayLength', 10);
        $offs   = $request->input('iDisplayStart', 0);
        $search = $request->input('sSearch');

        // 1. Query Dasar Datatables
        $query = DB::table('sis_classpayitem')
            ->leftJoin('sis_payitem', 'sis_classpayitem.payitem_id', '=', 'sis_payitem.id')
            ->where('sis_classpayitem.class_list_id', $class_id);

        // Pencarian (Search)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('sis_classpayitem.id', 'like', "%{$search}%")
                  ->orWhere('sis_classpayitem.payvalue', 'like', "%{$search}%");
            });
        }

        $iTotal = $query->count();
        $iFilteredTotal = $iTotal;

        // 2. Ambil Data dengan Limit (Pagination Datatables)
        $result = $query->select(
                'sis_classpayitem.class_list_id', 'sis_classpayitem.id', 'sis_payitem.payitem_code', 
                'sis_classpayitem.payvalue', 'sis_classpayitem.coa_cash', 'sis_classpayitem.coa_receivable', 
                'sis_classpayitem.coa_revenue', 'sis_classpayitem.coa_payable', 'sis_classpayitem.coa_cost', 
                'sis_classpayitem.pay_repeat', 'sis_classpayitem.pay_start', 'sis_classpayitem.pay_end', 
                'sis_payitem.title'
            )
            ->orderBy('sis_classpayitem.payvalue', 'asc')
            ->skip($offs)
            ->take($nums)
            ->get();

        // 3. Mengambil kamus data nama akun langsung dari sis_coa berdasarkan coa_code 
        $coa_query = DB::table('sis_coa')->select('coa_code', 'title')->get();
        $cl = [];
        foreach ($coa_query as $c) {
            $cl[$c->coa_code] = $c->title;
        }

        // 4. Persiapan Output Data
        $output = [
            "sEcho" => intval($request->input('sEcho')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => []
        ];

        // 5. Mapping Baris (Rows)
        foreach ($result as $res) {
            $col = [];

            // ---> TAMBAHKAN BARIS INI <---
            // Kolom 0 (Placeholder untuk Nomor Urut agar total array menjadi 7 sesuai kolom HTML)
            $col[] = null; 

            // Kolom 1: JENIS & NAMA
            $col[] = sprintf("<div class='mb-0 fw-semibold text-dark'>%s</div><small class='text-muted' style='font-size:0.75rem;'>%s</small>", $res->title, $res->payitem_code); 
            
            // Kolom 2: PERIODE
            $col[] = sprintf("<span class='text-success fw-medium'>%s</span><br><small class='text-danger'>%s</small>", $res->pay_start, $res->pay_end); 
            
            // Kolom 3: AKUN COA Bertumpuk
            $accs = "<div class='lh-sm'>";
            if (!empty($res->coa_cash) && isset($cl[$res->coa_cash])) $accs .= "<span class='badge bg-light text-dark border me-1 mb-1'>Cash: " . $cl[$res->coa_cash] . "</span>";
            if (!empty($res->coa_receivable) && isset($cl[$res->coa_receivable])) $accs .= "<span class='badge bg-light text-dark border me-1 mb-1'>Piutang: " . $cl[$res->coa_receivable] . "</span>";
            if (!empty($res->coa_revenue) && isset($cl[$res->coa_revenue])) $accs .= "<span class='badge bg-light text-dark border me-1 mb-1'>Pendapatan: " . $cl[$res->coa_revenue] . "</span>";
            if (!empty($res->coa_payable) && isset($cl[$res->coa_payable])) $accs .= "<span class='badge bg-light text-dark border me-1 mb-1'>Hutang: " . $cl[$res->coa_payable] . "</span>";
            if (!empty($res->coa_cost) && isset($cl[$res->coa_cost])) $accs .= "<span class='badge bg-light text-dark border me-1 mb-1'>Biaya: " . $cl[$res->coa_cost] . "</span>";
            $accs .= "</div>";
            $col[] = $accs;
            
            // Kolom 4: ULANG
            $col[] = sprintf("<span class='badge bg-info-subtle text-info px-2 py-1 rounded-pill'>%s</span>", $res->pay_repeat);
            
            // Kolom 5: NILAI NOMINAL
            $col[] = "Rp " . number_format($res->payvalue, 0, ',', '.'); 

            // Kolom 6: AKSI (Tombol bergaya modern)
            $btn_edit   = "<button type='button' class='btn btn-sm btn-light border text-warning py-1 px-2 btn-edit' data-id='{$res->id}' title='Edit'><i class='bi bi-pencil-square'></i></button>";
            $btn_delete = "<button type='button' class='btn btn-sm btn-light border text-danger py-1 px-2 btn-delete' data-id='{$res->id}' title='Hapus'><i class='bi bi-trash'></i></button>";

            $col[] = "<div class='btn-group shadow-sm rounded'>{$btn_edit} {$btn_delete}</div>";  

            $output['aaData'][] = $col;
        }

        // 6. Return JSON response gaya Laravel
        return response()->json($output);
    }
}