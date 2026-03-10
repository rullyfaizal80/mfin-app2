<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f9;
        }

        /* Container Kertas A4 */
        .report-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 15mm;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); 
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-data th, .table-data td { 
            border: 1px solid #333; 
            padding: 6px; 
            vertical-align: top; 
        }
        .table-data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .btn-action {
            padding: 6px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; font-weight: bold;
        }

        @media print {
            body { background-color: #fff; padding: 0; }
            .report-container { box-shadow: none; border: none; margin: 0; width: 100%; padding: 0;}
            .no-print { display: none; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="width: 210mm; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-action" style="background: #0d6efd;">🖨️ Cetak</button>
        <button onclick="window.close()" class="btn-action" style="background: #6c757d;">Tutup</button>
    </div>

    <div class="report-container">
        
        <table border="0" cellspacing="0" cellpadding="0" style="border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px;">
            <tr>
                <td width="80" align="left" valign="middle">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 50px; height: auto;" onerror="this.style.display='none'">
                </td>
                <td align="left" valign="middle">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase;">Yayasan Fathul Huda Bandung</h2>
                    <p style="margin: 3px 0 0 0; font-size: 14px; color: #444; font-weight: bold;">Jl.Cikadut 252 Bandung 40194 Telp. 022-7212600</p>
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; text-decoration: underline; text-transform: uppercase;">{{ $page_title }}</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px;">
                Periode: <strong>{{ date('d M Y', strtotime($awal)) }}</strong> s.d <strong>{{ date('d M Y', strtotime($akhir)) }}</strong><br>
                Kasir: <strong>{{ $cashierName }}</strong>
            </p>
        </div>

        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Tanggal</th>
                    <th width="15%">No Referensi</th>
                    <th width="20%">Dibayar Kepada</th>
                    <th width="30%">Keterangan</th>
                    <th width="18%">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandTotal = 0; 
                    $no = 1;
                @endphp
                
                @forelse($expenses as $row)
                    <tr>
                        <td align="center">{{ $no++ }}</td>
                        <td align="center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                        <td align="center"><strong>{{ $row->ref_no }}</strong></td>
                        <td>{{ $row->payto }}</td>
                        <td>{{ $row->note }}</td>
                        <td align="right">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @php $grandTotal += $row->nominal; @endphp
                @empty
                    <tr>
                        <td colspan="6" align="center" style="padding: 20px;">Tidak ada data transaksi pengeluaran pada periode ini.</td>
                    </tr>
                @endforelse
                
                <tr>
                    <td colspan="5" align="right" style="font-size: 14px; font-weight: bold; background-color: #f0f0f0;">GRAND TOTAL :</td>
                    <td align="right" style="font-size: 14px; font-weight: bold; background-color: #f0f0f0;">
                        {{ number_format($grandTotal, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 40px; text-align: right;">
            <p>Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d F Y') }}</p>           
        </div>

    </div>

</body>
</html>