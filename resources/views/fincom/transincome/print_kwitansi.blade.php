<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }} - {{ $inc->ref_no }}</title>
    <style>
        /* Background untuk layar komputer */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Ukuran font standar A6 */
            color: #000;
            margin: 0;
            padding: 20px 0;
            background-color: #525659;
        }

        /* --- WADAH KERTAS A6 --- */
        .a6-container {
            width: 105mm;      /* Lebar kertas A6 */
            min-height: 148mm; /* Tinggi kertas A6 */
            margin: 0 auto;
            background: #fff;
            padding: 8mm;      /* Margin aman dari tepi kertas */
            box-shadow: 0 4px 10px rgba(0,0,0,0.5); 
            box-sizing: border-box; /* Agar padding tidak menambah lebar */
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        
        .btn-action {
            padding: 6px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; font-weight: bold; margin-left: 5px;
        }
        
        /* Setting khusus saat di-print ke printer sungguhan */
        @media print {
            body { background-color: #fff; padding: 0; }
            .a6-container { 
                box-shadow: none; border: none; margin: 0; width: 100%; min-height: auto; padding: 0;
            }
            .no-print { display: none !important; }
            /* Memaksa printer menggunakan ukuran A6 Portrait */
            @page { size: A6 portrait; margin: 5mm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="width: 105mm; margin: 0 auto 15px auto; text-align: right; background: #fff; padding: 10px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); box-sizing: border-box;">
        <button onclick="window.print()" class="btn-action" style="background: #198754;">🖨️ Cetak</button>
        <button onclick="window.close()" class="btn-action" style="background: #dc3545;">Tutup Tab</button>
    </div>

    <div class="a6-container">

        <table border="0" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #333; margin-bottom: 15px; padding-bottom: 10px;">
            <tr>
                <td width="60" align="left" valign="middle">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 30px; height: auto;" onerror="this.style.display='none'">
                </td>
                <td align="left" valign="middle">
                    <h2 style="margin: 0; font-size: 12px; font-weight: 800; color: #000; text-transform: uppercase;">Yayasan Fathul Huda Bandung</h2>
                    <p style="margin: 2px 0 0 0; font-size: 10px; color: #444; font-weight: 600;">Jl.Cikadut 252 Bandung 40194 Telp. 022-7212600</p>
                </td>
            </tr>
        </table>

        <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 15px;">
            <tr>
                <td align="center">
                    <span style="font-size: 14px; font-weight: bold; ">{{ strtoupper($page_title) }}</span><br>
                    <span style="font-size: 11px;">Tanggal: {{ date("d F Y", strtotime($inc->tdate)) }}</span>
                </td>
            </tr>
        </table>

        <table cellspacing="0" cellpadding="2" style="margin-bottom: 15px; font-size: 11px;">
            <tr>
                <td width="75" align="left"><strong>No Referensi</strong></td>
                <td align="left">: <strong>{{ $inc->ref_no }}</strong></td>
            </tr>
            <tr>
                <td align="left"><strong>Diterima Dari</strong></td>
                <td align="left">: {{ $inc->payto }}</td>
            </tr>
            <tr>  
                <td align="left"><strong>Kasir</strong></td>
                <td align="left">: {{ $inc->fullname ?? '-' }}</td>
            </tr>
            <tr>
                <td align="left"><strong>Keterangan</strong></td>
                <td align="left">: {{ $inc->note }}</td>
            </tr>
        </table>

        <table border="0" cellspacing="0" cellpadding="2" style="font-size: 11px;">
            <thead>
                <tr>
                    <th width="8%" align="center" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">No</th>
                    <th width="35%" align="left" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Jenis</th>
                    <th width="32%" align="left" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Keterangan</th>
                    <th width="25%" align="right" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php $tot = 0; @endphp
                
                @forelse($items as $index => $item)
                    @php 
                        // Ambil nilai tertinggi antara debit atau credit untuk antisipasi format data
                        $nominal = $item->credit > 0 ? $item->credit : $item->debit; 
                    @endphp
                    <tr>
                        <td align="center" style="padding-top: 5px;">{{ $index + 1 }}</td>
                        <td align="left" style="padding-top: 5px;">{{ $item->title ?? '-' }}</td>
                        <td align="left" style="padding-top: 5px;">{{ $item->note }}</td>
                        <td align="right" style="padding-top: 5px;">{{ number_format($nominal, 0, ',', '.') }}</td>
                    </tr>
                    @php $tot += $nominal; @endphp
                @empty
                    <tr>
                        <td colspan="4" align="center" style="padding: 10px;">Tidak ada rincian item.</td>
                    </tr>
                @endforelse
                
                <tr>
                    <td colspan="3" align="right" style="border-top:#333 1px solid; padding-top: 8px;"><strong>TOTAL :</strong></td>
                    <td align="right" style="border-top:#333 1px solid; padding-top: 8px;">
                        <strong>{{ number_format($tot, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <table border="0" cellspacing="0" cellpadding="2" style="margin-top: 30px; text-align: center; font-size: 11px;">
            <tr>
                <td width="33%"><strong>Penyetor</strong></td>
                <td width="33%"><strong>Kasir</strong></td>
                <td width="33%"><strong>Mengetahui</strong></td>
            </tr>
            <tr>
                <td style="height: 50px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>( {{ $inc->payto != '' ? $inc->payto : '.........................' }} )</td>
                <td>( {{ $inc->fullname != '' ? $inc->fullname : '.........................' }} )</td>
                <td>( ......................... )</td>
            </tr>
        </table>

    </div>
</body>
</html>