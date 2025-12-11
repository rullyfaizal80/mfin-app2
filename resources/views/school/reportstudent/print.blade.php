<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Siswa</title>
    <style>
        /* --- STANDARD DEFAULT PRINT STYLE --- */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9; 
            padding: 30px 0; margin: 0; color: #333; line-height: 1.3;
        }
        .sheet { 
            background: white; width: 210mm; min-height: 297mm; 
            margin: 0 auto; padding: 15mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            box-sizing: border-box; position: relative;
        }
        
        /* HEADER */
        .header-container {
            text-align: center; margin-bottom: 20px;
            border-bottom: 2px solid #333; padding-bottom: 10px;
        }
        .header-logo { width: 65px; height: auto; margin-bottom: 0px; display: block; margin: 0 auto; }
        .header-container h2 { margin: 5px 0 0 0; font-size: 18px; font-weight: 800; color: #000; text-transform: uppercase; line-height: 1.1; }
        .header-container p { margin: 0; font-size: 14px; color: #444; font-weight: 600; line-height: 1.1; }

        /* FILTER INFO */
        .filter-section {
            margin-bottom: 15px; font-size: 11px;
            border-bottom: 1px dashed #ddd; padding-bottom: 10px;
        }
        .filter-table { width: 100%; }
        .filter-table td { padding: 1px 0; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 80px; }

        /* TABEL DATA */
        .data-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        .data-table th {
            background-color: #eee; color: #000; font-weight: 700; text-transform: uppercase; 
            font-size: 10px; padding: 8px 4px; border: 1px solid #999;
            text-align: center !important; vertical-align: middle; white-space: nowrap;
        }
        .data-table td { padding: 6px 4px; border: 1px solid #ccc; vertical-align: top; }

        /* UTILS */
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .no-wrap { white-space: nowrap; }
        
        /* PRINT BTN */
        /* Tombol Print */
        .no-print { 
            text-align: right; margin-bottom: 20px; 
            width: 210mm; margin-left: auto; margin-right: auto; 
        }
        .btn-print { background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

        @media print { 
            @page { size: A4 portrait; margin: 10mm; }
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none; } 
            .sheet { box-shadow: none; margin: 0; padding: 0; width: 100%; border: none; } 
            .data-table th { background-color: #eee !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
    </div>

    <div class="sheet">
        <div class="header-container">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
            <h2>LAPORAN PENCARIAN SISWA</h2>
            <p>MIMHa Finance</p>
        </div>

        <div class="filter-section">
            <table class="filter-table">
                <tr>
                    <td class="label">Sekolah</td>
                    <td>: <strong>{{ $filterTitle }}</strong></td>
                    <td class="label" style="text-align: right;">Total Data : </td>
                    <td width="30" style="text-align: right;"><strong>{{ count($students) }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Filter Lain</td>
                    <td colspan="3">: 
                        @if($req->snis) NIS: {{ $req->snis }}, @endif
                        @if($req->snama) Nama: {{ $req->snama }}, @endif
                        @if($req->sortu) Ortu: {{ $req->sortu }}, @endif
                        @if($req->salamat) Alamat: {{ $req->salamat }} @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Nama Orang Tua</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center no-wrap">{{ $row->nis ?? '-' }}</td>
                        <td><span class="fw-bold">{{ $row->fullname }}</span></td>
                        <td>{{ $row->class_name ?? '-' }}</td>
                        <td>
                            <div style="font-size: 10px;">
                                A: {{ $row->father_name ?? '-' }}<br>
                                I: {{ $row->mother_name ?? '-' }}
                            </div>
                        </td>
                        <td>{{ $row->street ?? '-' }} {{ $row->city }}</td>
                    </tr>
                @endforeach

                @if(count($students) == 0)
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; color: #999;">
                            Tidak ada data siswa ditemukan.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 30px; font-size: 10px; color: #555; text-align: right; border-top: 1px dashed #ccc; padding-top: 5px;">
            Dicetak oleh: {{ auth()->user()->fullname ?? 'Admin' }} | 
            Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>

    </div>
</body>
</html>