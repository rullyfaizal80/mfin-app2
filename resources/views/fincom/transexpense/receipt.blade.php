<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }} - {{ $exp->ref_no }}</title>
    <style>
        /* Background untuk layar komputer */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Ukuran font standar A6 */
            color: #000;
            margin: 0;
            padding: 20px 0;
            background-color: #f4f6f9;
        }

        /* --- WADAH KERTAS A6 --- */
        .a6-container {
            width: 105mm;      /* Lebar kertas A6 */
            min-height: 148mm; /* Tinggi kertas A6 */
            margin: 0 auto;
            background: #fff;
            padding: 8mm;      /* Margin aman dari tepi kertas */
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); 
            border: 1px solid #ccc;
            box-sizing: border-box; /* Agar padding tidak menambah lebar */
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        
        .btn-action {
            padding: 6px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; font-weight: bold;
        }
        
        /* Setting khusus saat di-print ke printer sungguhan */
        @media print {
            body { background-color: #fff; padding: 0; }
            .a6-container { 
                box-shadow: none; border: none; margin: 0; width: 100%; min-height: auto;
            }
            .no-print { display: none; }
            /* Memaksa printer menggunakan ukuran A6 Portrait */
            @page { size: A6 portrait; margin: 5mm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="width: 105mm; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-action" style="background: #0d6efd;">🖨️ Cetak</button>
        <button onclick="window.close()" class="btn-action" style="background: #6c757d;">Tutup</button>
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
                    <span style="font-size: 11px;">Tanggal: {{ date("d F Y", strtotime($exp->tdate)) }}</span>
                </td>
            </tr>
        </table>

        <table cellspacing="0" cellpadding="2" style="margin-bottom: 15px;">
            <tr>
                <td width="70" align="left"><strong>No Ref</strong></td>
                <td align="left">: {{ $exp->ref_no }}</td>
            </tr>
            <tr>
                <td align="left"><strong>Dari</strong></td>
                <td align="left">: {{ $exp->fullname ?? '-' }}</td>
            </tr>
            <tr>  
                <td align="left"><strong>Kepada</strong></td>
                <td align="left">: {{ $exp->payto }}</td>
            </tr>
            <tr>
                <td align="left"><strong>Keterangan</strong></td>
                <td align="left">: {{ $exp->note }}</td>
            </tr>
        </table>

        <table border="0" cellspacing="0" cellpadding="2">
            <thead>
                <tr>
                    <th width="10%" align="center" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">No</th>
                    <th width="35%" align="left" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Jenis</th>
                    <th width="25%" align="left" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Keterangan</th>
                    <th width="30%" align="right" style="border-bottom:#333 1px dashed; padding-bottom: 5px;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php $tot = 0; @endphp
                
                @forelse($items as $index => $item)
                    <tr>
                        <td align="center" style="padding-top: 5px;">{{ $index + 1 }}</td>
                        <td align="left" style="padding-top: 5px;">{{ $item->title ?? '-' }}</td>
                        <td align="left" style="padding-top: 5px; font-size: 11px;">{{ $item->note }}</td>
                        <td align="right" style="padding-top: 5px;">{{ number_format($item->debit, 0, ',', '.') }}</td>
                    </tr>
                    @php $tot += $item->debit; @endphp
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
                <td width="33%"><strong>Kasir</strong></td>
                <td width="33%"><strong>Penerima</strong></td>
                <td width="33%"><strong>Mengetahui</strong></td>
            </tr>
            <tr>
                <td style="height: 50px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>( ......................... )</td>
                <td>( ......................... )</td>
                <td>( ......................... )</td>
            </tr>
        </table>

    </div>
    </body>
</html>