<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportClassListController extends Controller
{
    /**
     * HALAMAN UTAMA (INDEX)
     */
    public function index(Request $request)
    {
        // 1. Ambil Data Master untuk Dropdown Filter
        $schools = DB::table('sis_cschool')->orderBy('name', 'asc')->get();
        $grades  = DB::table('sis_cgrade')->orderBy('title', 'asc')->get();
        $types   = DB::table('sis_ctype')->orderBy('title', 'asc')->get();

        // 2. Query Utama (Mirip ax_get_clist di kode lama)
        $query = $this->buildQuery($request);

        // 3. Pagination
        $classes = $query->paginate(20)->withQueryString();

        return view('reports.class_list.index', [
            'classes' => $classes,
            'schools' => $schools,
            'grades'  => $grades,
            'types'   => $types,
            // Kirim input user kembali ke view agar form tidak reset
            'f_cschool' => $request->f_cschool,
            'f_cyear_start' => $request->f_cyear_start,
            'f_cyear_end' => $request->f_cyear_end,
            'f_cgrade' => $request->f_cgrade,
            'f_ctype' => $request->f_ctype,
        ]);
    }

    /**
     * HALAMAN CETAK (PRINT)
     */
    public function print(Request $request)
    {
        // 1. Query Data (Tanpa Pagination)
        $query = $this->buildQuery($request);
        $classes = $query->get();

        // 2. Susun Info Filter untuk Header Laporan
        $filtersApplied = [];
        
        $filtersApplied['Sekolah'] = $request->f_cschool ?: 'Semua';
        if ($request->f_cyear_start) $filtersApplied['Tahun Awal'] = $request->f_cyear_start;
        if ($request->f_cyear_end)   $filtersApplied['Tahun Akhir'] = $request->f_cyear_end;
        $filtersApplied['Tingkat'] = $request->f_cgrade ?: 'Semua';
        $filtersApplied['Tipe']    = $request->f_ctype ?: 'Semua';

        return view('reports.class_list.print', [
            'classes' => $classes,
            'filtersApplied' => $filtersApplied
        ]);
    }

    /**
     * PRIVATE: Helper untuk membangun Query Builder
     * Agar logic filter bisa dipakai di Index dan Print sekaligus
     */
    private function buildQuery(Request $request)
    {
        $query = DB::table('sis_class_list as cl')
            ->leftJoin('sis_cschool as sch', 'cl.cschool_id', '=', 'sch.id')
            ->leftJoin('sis_cyear as cy', 'cl.cyear_id', '=', 'cy.id')
            ->leftJoin('sis_csubject as csub', 'cl.csubject_id', '=', 'csub.id')
            ->leftJoin('sis_cgrade as cg', 'cl.cgrade_id', '=', 'cg.id')
            ->leftJoin('sis_cgroup as cgrp', 'cl.cgroup_id', '=', 'cgrp.id')
            ->leftJoin('sis_ctype as ct', 'cl.ctype_id', '=', 'ct.id')
            ->select(
                'cl.id',
                'cl.title as class_name',
                'sch.name as school_name',
                'cy.title as year_title',
                'cy.date_start', // Untuk filter tahun
                'cy.date_end',   // Untuk filter tahun
                'csub.title as subject_title',
                'cg.title as grade_title',
                'cgrp.title as group_title',
                'ct.title as type_title'
            )
            ->orderBy('cl.title', 'asc');

        // --- Terapkan Filter ---
        
        // Filter Sekolah
        if ($request->filled('f_cschool')) {
            $query->where('sch.name', $request->f_cschool);
        }

        // Filter Tahun Ajaran (Start) - Pakai Year() function MySQL
        if ($request->filled('f_cyear_start')) {
            $query->whereYear('cy.date_start', '>=', $request->f_cyear_start);
        }

        // Filter Tahun Ajaran (End)
        if ($request->filled('f_cyear_end')) {
            $query->whereYear('cy.date_end', '<=', $request->f_cyear_end);
        }

        // Filter Tingkat
        if ($request->filled('f_cgrade')) {
            $query->where('cg.title', $request->f_cgrade);
        }

        // Filter Tipe
        if ($request->filled('f_ctype')) {
            $query->where('ct.title', $request->f_ctype);
        }

        return $query;
    }
}