<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Global Transaksi Tabungan</title>
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
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .header-logo { 
            width: 70px; 
            height: auto; 
        }
        .header-container h3 { 
            margin: 0; 
            font-size: 16px; 
            font-weight: 700; 
            color: #2c3e50; 
            text-transform: uppercase; 
        }

        /* Info Periode */
        .period-info { 
            font-size: 12px; 
            margin-bottom: 15px; 
            font-weight: 600; 
            color: #555; 
        }

        /* ================================
           TABEL
        ================================= */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 5px;
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

        /* Isi Tabel */
        .data-table td {
            padding: 5px 4px; 
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Helper Classes */
        .nowrap { white-space: nowrap; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-green { color: #198754 !important; }
        .text-red { color: #dc3545 !important; }
        .text-gray { color: #ccc !important; }

        /* Footer Total */
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

        /* Tombol Cetak */
        .action-bar { text-align: right; margin-bottom: 10px; width: 95%; margin-left: auto; margin-right: auto; }
        .btn-print {
            background-color: #0d6efd; color: white; border: none; padding: 8px 16px;
            border-radius: 50px; cursor: pointer; font-size: 12px; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }
        .btn-print:hover { background-color: #0b5ed7; }

        /* --- PENGATURAN CETAK --- */
        @media print {
            @page { size: A4 landscape; margin: 10mm 5mm; }
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
            <h3>LAPORAN GLOBAL TRANSAKSI TABUNGAN</h3>
            <div style="font-size: 10px; color: #777; margin-top: 5px;">
                Dicetak oleh: {{ session('fullname', 'Admin') }} | 
                Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <div class="period-info">
            Periode: {{ date('d M Y', strtotime($ffrom)) }} s/d {{ date('d M Y', strtotime($fto)) }}
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th width="15%" class="text-center">Tanggal</th>
                    <th width="20%">No Referensi</th>
                    <th>Nama Nasabah</th>
                    <th width="12%" class="text-center">ID (NIS/NIK)</th>
                    <th>Keterangan</th> 
                    <th width="11%" class="text-right">Setor</th>
                    <th width="11%" class="text-right">Tarik</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $sum_credit = 0;
                    $sum_debit = 0;
                @endphp

                @forelse($savings as $index => $row)
                    @php
                        $sum_credit += $row->credit;
                        $sum_debit += $row->debit;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center nowrap">{{ date('d-m-Y', strtotime($row->tdate)) }}</td>
                        <td class="nowrap" style="font-family: monospace; font-size: 10px;">{{ $row->ref_no }}</td>
                        <td><strong>{{ $row->fullname }}</strong></td>
                        <td class="text-center">{{ $row->nis ?? ($row->nik ?? '-') }}</td>
                        <td style="white-space: normal;">{{ $row->note }}</td>
                        <td class="text-right nowrap {{ $row->credit > 0 ? 'text-green' : 'text-gray' }}">
                            {{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right nowrap {{ $row->debit > 0 ? 'text-red' : 'text-gray' }}">
                            {{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td colspan="6" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right text-green nowrap">{{ number_format($sum_credit, 0, ',', '.') }}</td>
                    <td class="text-right text-red nowrap">{{ number_format($sum_debit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 5px; font-size: 9px; color: #999; text-align: center;">
            MIMHa Finance - {{ date('Y') }}
        </div>

    </div>

</body>
</html>