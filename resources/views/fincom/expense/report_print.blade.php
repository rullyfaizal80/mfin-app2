<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi per Akun - MFIN</title>
    <style>
        /* --- RESET & GAYA UMUM --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9; 
            color: #333;
            margin: 0;
            padding: 20px 0;
            line-height: 1.3;
        }

        /* Container Kertas */
        .container {
            width: 95%; 
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        /* ================================
           HEADER 
        ================================= */
        .header-container {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .header-logo { 
            width: 70px;
            height: auto; 
        }
        .header h3 { 
            margin: 0; 
            font-size: 16px;
            font-weight: 700; 
            color: #2c3e50; 
            text-transform: uppercase; 
        }
        
        /* Info Laporan */
        .info-table { width: 100%; margin-bottom: 15px; font-size: 12px; border: none; }
        .info-table td { padding: 2px 0; vertical-align: top; border: none; }
        .label { color: #666; width: 130px; }
        .colon { width: 15px; text-align: center; }
        .value { font-weight: 600; color: #000; }

        /* ================================
           TABEL DATA
        ================================= */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* Header Tabel */
        .data-table th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            padding: 6px 4px;
            border-top: 2px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        /* Kolom khusus lebar */
        .data-table th.col-no { width: 30px !important; text-align: center; }
        .data-table th.col-ref { width: 15% !important; }
        .data-table th.col-date { width: 10% !important; text-align: center; }

        /* Isi Tabel */
        .data-table td {
            padding: 4px 4px; 
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        /* Header Kategori (Pemasukan / Pengeluaran) */
        .cat-header th {
            font-size: 12px;
            padding: 8px 4px;
            border-bottom: none;
        }
        .cat-masuk th { background-color: #d1e7dd !important; color: #0f5132; }
        .cat-keluar th { background-color: #f8d7da !important; color: #842029; }

        /* Helper Classes */
        .nowrap { white-space: nowrap; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-green { color: #198754 !important; }
        .text-red { color: #dc3545 !important; }
        
        /* Font monospace untuk referensi */
        .font-mono { font-family: monospace; font-size: 10px; }

        /* Baris Total */
        .footer-total td { 
            background-color: #f8f9fa !important; 
            border-top: 2px solid #dee2e6; 
            border-bottom: 2px solid #dee2e6; 
            font-weight: bold; 
            padding: 8px 4px; 
            font-size: 11px; 
        }

        /* Zebra Striping */
        .data-table tbody tr:nth-child(even) { background-color: #fcfcfc !important; }

        /* Tabel Ringkasan Saldo */
        .saldo-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 11px;
        }
        .saldo-table th { background-color: #e2e6ea !important; padding: 6px; font-size: 11px; border-bottom: 2px solid #dee2e6; }
        .saldo-table td { padding: 6px; border-bottom: 1px solid #eee; }
        .saldo-akhir td { background-color: #f8f9fa !important; font-size: 13px; font-weight: bold; border-top: 2px solid #dee2e6; }

        /* Tombol Cetak */
        .action-bar { text-align: right; margin-bottom: 10px; width: 95%; margin-left: auto; margin-right: auto; }
        .btn-print {
            background-color: #0d6efd; color: white; border: none; padding: 8px 16px;
            border-radius: 50px; cursor: pointer; font-size: 12px; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }
        .btn-print:hover { background-color: #0b5ed7; }

        /* --- PENGATURAN CETAK --- */
        @media print {
            @page { size: A4; margin: 10mm 5mm; }
            body { background-color: white; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .container { width: 100%; max-width: 100%; box-shadow: none; padding: 0; margin: 0; }
            .action-bar, .no-print { display: none; }
            .data-table th, .data-table td, .saldo-table th, .saldo-table td { border-bottom: 1px solid #ddd !important; }
            .data-table th, .saldo-table th, .footer-total td, .saldo-akhir td { border-top: 2px solid #ccc !important; }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
    </div>

    <div class="container">
        
        <div class="header-container">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
            <div class="header">
                <h3>LAPORAN TRANSAKSI PER AKUN (KOMPONEN)</h3>
            </div>
            <div style="font-size: 10px; color: #777; margin-top: 5px;">
                Dicetak oleh: {{ session('fullname', 'Admin') }} | 
                Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Jenis Komponen</td>
                <td class="colon">:</td>
                <td class="value">{{ $coaTitle }}</td>
                
                <td class="label" style="text-align: right;">Periode</td>
                <td class="colon">:</td>
                <td class="value" style="width: 30%; text-align: right;">
                    {{ date('d M Y', strtotime($awal)) }} s/d {{ date('d M Y', strtotime($akhir)) }}
                </td>
            </tr>
        </table>

        {{-- TABEL PEMASUKAN --}}
        <table class="data-table">
            <thead>
                <tr class="cat-header cat-masuk">
                    <th colspan="7" class="text-center">DAFTAR PEMASUKAN</th>
                </tr>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-ref">Ref No</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th width="10%" class="text-center"></th>
                    <th width="12%" class="text-right">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $tot_terima = 0; @endphp
                @forelse($terima as $index => $res)
                    @php $tot_terima += $res->debit; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center nowrap">{{ date('d M y', strtotime($res->tdate)) }}</td>
                        <td class="font-mono nowrap">{{ $res->ref_no }}</td>
                        <td>
                            <strong>{{ $res->fullname }}</strong>
                            @if(!empty($res->class))
                                <div style="font-size: 9px; color: #777;">{{ $res->class }}</div>
                            @endif
                            @if(!empty($res->school))
                                <div style="font-size: 9px; color: #777;">{{ $res->school }}</div>
                            @endif
                        </td>
                        {{-- Format keterangan: tnote - note (persis seperti aplikasi lama) --}}
                        <td>
                            @php
                                $ketTerima = trim(($res->tnote ?? '') . (!empty($res->tnote) && !empty($res->note) ? ' - ' : '') . ($res->note ?? ''));
                            @endphp
                            {{ $ketTerima }}
                        </td>
                        {{-- Kolom tipe identik CI2: hanya cetak 'Siswa' jika is_student == 'yes', jika tidak kosong --}}
                        <td class="text-center">{{ $res->is_student == 'yes' ? 'Siswa' : '' }}</td>
                        <td class="text-right text-green nowrap">{{ number_format($res->debit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center" style="padding:15px; color:#999;">Tidak ada transaksi pemasukan pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td colspan="6" class="text-right">TOTAL PEMASUKAN</td>
                    <td class="text-right text-green nowrap">{{ number_format($tot_terima, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- TABEL PENGELUARAN (Telah diperbaiki: Tambah Kolom Alokasi Kelas) --}}
        <table class="data-table">
            <thead>
                <tr class="cat-header cat-keluar">
                    <th colspan="8" class="text-center">DAFTAR PENGELUARAN</th>
                </tr>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-ref">Ref No</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th>Alokasi Kelas</th>
                    <th width="10%" class="text-center"></th>
                    <th width="12%" class="text-right">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $tot_keluar = 0; @endphp
                @forelse($keluar as $index => $res)
                    @php $tot_keluar += $res->credit; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center nowrap">{{ date('d M y', strtotime($res->tdate)) }}</td>
                        <td class="font-mono nowrap">{{ $res->ref_no }}</td>
                        <td>
                            <strong>{{ $res->fullname }}</strong>
                            @if(!empty($res->class) || !empty($res->school))
                                <div style="font-size: 9px; color: #777;">
                                    {{ trim(($res->class ?? '') . ' ' . ($res->school ?? '')) }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $res->note }}</td>
                        <td class="nowrap">{{ $res->ledger_class ?? '' }}</td>
                        {{-- Kolom tipe identik CI2: hanya cetak 'Siswa' jika is_student == 'yes', jika tidak kosong --}}
                        <td class="text-center">{{ $res->is_student == 'yes' ? 'Siswa' : '' }}</td>
                        <td class="text-right text-red nowrap">{{ number_format($res->credit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding:15px; color:#999;">Tidak ada transaksi pengeluaran pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td colspan="7" class="text-right">TOTAL PENGELUARAN</td>
                    <td class="text-right text-red nowrap">{{ number_format($tot_keluar, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- TABEL SALDO AKHIR --}}
        <table class="saldo-table">
            <thead>
                <tr><th colspan="2" class="text-center">RINGKASAN SALDO</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left fw-bold">Total Pemasukan</td>
                    <td class="text-right fw-bold text-green">{{ number_format($tot_terima, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">Total Pengeluaran</td>
                    <td class="text-right fw-bold text-red">{{ number_format($tot_keluar, 0, ',', '.') }}</td>
                </tr>
                <tr class="saldo-akhir">
                    <td class="text-right">SALDO AKHIR :</td>
                    <td class="text-right">{{ number_format($tot_terima - $tot_keluar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 5px; font-size: 9px; color: #999; text-align: center;">
            MIMHa Finance - {{ date('Y') }}
        </div>

    </div>

</body>
</html>