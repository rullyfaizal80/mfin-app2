<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tabungan - {{ $identity['fullname'] }}</title>
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
           HEADER DIPERKECIL
        ================================= */
        .header-container {
            text-align: center;
            margin-bottom: 10px;          /* dari 20 */
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;           /* dari 10 */
        }
        .header-logo { 
            width: 70px;                   /* dari 70 */
            height: auto; 
            /*margin-bottom: 3px;*/ 
        }
        .header h2 { 
            margin: 0; 
            font-size: 16px;               /* dari 18 */
            font-weight: 700; 
            color: #2c3e50; 
            text-transform: uppercase; 
        }
        .header-meta { font-size: 11px; color: #777; margin-top: 2px; }

        /* Info Siswa */
        .info-table { width: 100%; margin-bottom: 15px; font-size: 12px; border: none; }
        .info-table td { padding: 2px 0; vertical-align: top; border: none; }
        .label { color: #666; width: 130px; }
        .colon { width: 15px; text-align: center; }
        .value { font-weight: 600; color: #000; }

        /* ================================
           TABEL DIPERBAIKI
        ================================= */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 5px;
            /* table-layout: fixed; /* agar stabil saat print */
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

        /* Kolom nomor & kolom referensi diperbaiki */
        .data-table th:nth-child(1) {
            width: 22px !important;      /* Kolom No lebih kecil */
        }
        .data-table th:nth-child(2) {
            width: 30% !important;       /* Referensi lebih besar */
        }

        /* Isi Tabel */
        .data-table td {
            padding: 4px 4px; 
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Helper Classes */
        .nowrap { white-space: nowrap; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-green { color: #198754 !important; }
        .text-red { color: #dc3545 !important; }
        .text-gray { color: #ccc !important; }

        /* Baris Khusus */
        .saldo-row td { background-color: #e9ecef !important; font-weight: bold; color: #495057; border-bottom: 1px solid #dee2e6; }
        .footer-total td { background-color: #f8f9fa !important; border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6; font-weight: bold; padding: 8px 4px; font-size: 11px; }

        /* Zebra Striping */
        .data-table tbody tr:nth-child(even) { background-color: #fcfcfc !important; }

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
            .data-table th, .data-table td { border-bottom: 1px solid #ddd !important; }
            .data-table th { border-top: 2px solid #ccc !important; }
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
            <h3>TRANSAKSI TABUNGAN</h3>
            <div class="header-meta">Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Nasabah</td>
                <td class="colon">:</td>
                <td class="value">{{ $identity['fullname'] }}</td>
                
                <td class="label" style="text-align: right;">Periode</td>
                <td class="colon">:</td>
                <td class="value" style="width: 30%; text-align: right;">
                    {{ date('d M Y', strtotime($ffrom)) }} s/d {{ date('d M Y', strtotime($fto)) }}
                </td>
            </tr>
            <tr>
                <td class="label">Keterangan</td>
                <td class="colon">:</td>
                <td class="value">{{ $identity['other'] }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                @php $current_balance = $saldo_pindahan; @endphp
                <tr class="saldo-row">
                    <td colspan="6" class="text-right">SALDO AWAL (PINDAHAN)</td>
                    <td class="text-right">{{ number_format($current_balance, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    {{-- 
                        REVISI LEBAR KOLOM:
                        - No: style="width: 30px" (FIXED PIXEL) -> Sangat sempit
                        - Ref: 23% (Ditambah dari sisa No)
                    --}}
                    <th style="width: 30px;" class="text-center">No</th>
                    <th width="23%">No Referensi</th>
                    <th width="10%" class="text-center">Tanggal</th>
                    <th>Keterangan</th> 
                    <th width="11%" class="text-right">Setor</th>
                    <th width="11%" class="text-right">Tarik</th>
                    <th width="12%" class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $total_setor = 0;
                    $total_tarik = 0;
                @endphp

                @foreach($transactions as $index => $row)
                    @php
                        $current_balance += ($row->credit - $row->debit);
                        $total_setor += $row->credit;
                        $total_tarik += $row->debit;
                    @endphp
                    <tr>
                        <td class="text-center" style="padding-left: 0; padding-right: 0;">{{ $index + 1 }}</td>
                        
                        {{-- Ref No: Font monospace kecil --}}
                        <td class="nowrap" style="font-family: monospace; font-size: 10px;">{{ $row->ref_no }}</td>
                        
                        <td class="text-center nowrap">{{ date('d-m-Y', strtotime($row->tdate)) }}</td>
                        
                        <td style="white-space: normal;">{{ $row->note }}</td>
                        
                        <td class="text-right nowrap {{ $row->credit > 0 ? 'text-green' : 'text-gray' }}">
                            {{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right nowrap {{ $row->debit > 0 ? 'text-red' : 'text-gray' }}">
                            {{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-bold nowrap">
                            {{ number_format($current_balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td colspan="4" class="text-right">TOTAL MUTASI</td>
                    <td class="text-right text-green nowrap">{{ number_format($total_setor, 0, ',', '.') }}</td>
                    <td class="text-right text-red nowrap">{{ number_format($total_tarik, 0, ',', '.') }}</td>
                    <td class="text-right text-bold nowrap" style="background-color: #e2e6ea !important;">{{ number_format($current_balance, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 5px; font-size: 9px; color: #999; text-align: center;">
            MIMHa Finance - {{ date('Y') }}
        </div>

    </div>

</body>
</html>