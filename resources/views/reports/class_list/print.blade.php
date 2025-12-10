<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Kelas</title>
    <style>
        /* --- GAYA MODERN (Sama seperti Laporan Tabungan) --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9; color: #333;
            margin: 0; padding: 20px 0; line-height: 1.3;
        }
        .container {
            width: 90%; max-width: 1200px; margin: 0 auto;
            background-color: #fff; padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px;
        }
        .header-container {
            text-align: center; margin-bottom: 20px;
            border-bottom: 2px solid #eee; padding-bottom: 10px;
        }
        .header-logo { width: 70px; height: auto; margin-bottom: 5px; }
        .header h2 { margin: 0; font-size: 18px; font-weight: 700; color: #2c3e50; text-transform: uppercase; }
        
        /* Filter Info Box */
        .filter-box {
            background-color: #f8f9fa; border: 1px solid #eee;
            padding: 10px; border-radius: 5px; margin-bottom: 20px;
            font-size: 11px;
        }
        .filter-table { width: 100%; }
        .filter-table td { padding: 2px 5px; vertical-align: top; }
        .filter-label { font-weight: bold; color: #666; width: 100px; }

        /* Tabel Data */
        .data-table {
            width: 100%; border-collapse: collapse;
            font-size: 11px; margin-top: 5px;
        }
        .data-table th {
            background-color: #f8f9fa !important; color: #495057;
            font-weight: 700; text-transform: uppercase; font-size: 10px;
            padding: 8px 5px;
            border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        .data-table td {
            padding: 5px 5px; border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) { background-color: #fcfcfc !important; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { 
            display: inline-block; padding: 2px 6px; 
            font-size: 9px; font-weight: bold; 
            border: 1px solid #ccc; border-radius: 3px; 
        }

        /* Tombol Cetak */
        .action-bar { text-align: right; margin-bottom: 10px; width: 90%; margin: 0 auto 10px auto; }
        .btn-print {
            background-color: #0d6efd; color: white; border: none; padding: 8px 16px;
            border-radius: 50px; cursor: pointer; font-size: 12px;
        }

        @media print {
            @page { size: A4; margin: 10mm; }
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
            <h2>LAPORAN DATA KELAS</h2>
            <div style="font-size: 10px; color: #777; margin-top: 5px;">
                Dicetak oleh: {{ session('fullname', 'Admin') }} | Tanggal: {{ date('d-M-Y H:i') }}
            </div>
        </div>

        <div class="filter-box">
            <table class="filter-table">
                <tr>
                    <td class="filter-label">Sekolah</td>
                    <td>: {{ $filtersApplied['Sekolah'] }}</td>
                    
                    <td class="filter-label">Tahun Awal</td>
                    <td>: {{ $filtersApplied['Tahun Awal'] ?? '-' }}</td>
                    
                    <td class="filter-label">Tahun Akhir</td>
                    <td>: {{ $filtersApplied['Tahun Akhir'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="filter-label">Tingkat</td>
                    <td>: {{ $filtersApplied['Tingkat'] }}</td>
                    
                    <td class="filter-label">Tipe</td>
                    <td colspan="3">: {{ $filtersApplied['Tipe'] }}</td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="35%">Nama Kelas</th>
                    <th width="20%">Tingkat / Grup</th>
                    <th width="15%">Tipe</th>
                    <th width="25%">Sekolah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong style="font-size: 12px;">{{ $row->class_name }}</strong><br>
                            <span style="color: #666; font-size: 10px;">{{ $row->year_title }} - {{ $row->subject_title }}</span>
                        </td>
                        <td>{{ $row->grade_title }} {{ $row->group_title }}</td>
                        <td>
                            <span class="badge">{{ $row->type_title }}</span>
                        </td>
                        <td>{{ $row->school_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 5px; font-size: 9px; color: #999; text-align: center;">
            MIMHa Finance - {{ date('Y') }}
        </div>

    </div>

</body>
</html>