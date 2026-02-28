<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }} - {{ $exp->ref_no }}</title>
    <style>
        /* Styling dasar meniru format asli agar pas di kertas print */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px; /* Jarak aman tepi kertas */
        }
        table { border-collapse: collapse; }
        .title { font-size: 14px; font-weight: bold; }
        
        /* Menyembunyikan tombol saat di-print */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 3px;">🖨️ Cetak</button>
        <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 3px;">Tutup</button>
        <hr>
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #333; margin-bottom: 15px; padding-bottom: 10px;">
        <tr>
            <td width="75" align="left" valign="middle">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 65px; height: auto;" onerror="this.style.display='none'">
            </td>
            <td align="left" valign="middle">
                <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #000; text-transform: uppercase; line-height: 1.2;">Yayasan Fathul Huda Bandung</h2>
                <p style="margin: 0; font-size: 14px; color: #444; font-weight: 600; line-height: 1.2;">Jl.Cikadut 252 Bandung 40194 Telp. 022-7212600</p>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr class="title">
            <td align="center" valign="bottom" style="padding-bottom: 10px;">
                <span style="font-size: 16px; text-decoration: underline;">{{ strtoupper($page_title) }}</span><br>
                <span style="font-size: 12px; font-weight: normal;">Tanggal: {{ date("d F Y", strtotime($exp->tdate)) }}</span>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="1" cellpadding="4">
        <tr>
            <td width="114" align="left" valign="top"><strong>No Ref</strong></td>
            <td width="281" align="left" valign="top">: {{ $exp->ref_no }}</td>
        </tr>
        <tr>
            <td align="left" valign="top"><strong>Dari</strong></td>
            <td align="left" valign="top">: {{ $exp->fullname ?? '-' }}</td>
        </tr>
        <tr>  
            <td align="left" valign="top"><strong>Kepada</strong></td>
            <td align="left" valign="top">: {{ $exp->payto }}</td>
        </tr>
        <tr>
            <td align="left" valign="top"><strong>Keterangan</strong></td>
            <td align="left" valign="top">: {{ $exp->note }}</td>
        </tr>
    </table>
    <br />

    <table width="100%" border="0" cellspacing="0" cellpadding="2" id="table-period">
        <thead>
            <tr>
                <th width="10%" align="center" valign="middle" style="border-bottom:#333333 1px dashed; padding-bottom: 5px;">No</th>
                <th width="31%" align="left" style="border-bottom:#333333 1px dashed; padding-bottom: 5px;">Jenis</th>
                <th width="30%" align="left" valign="middle" style="border-bottom:#333333 1px dashed; padding-bottom: 5px;">Keterangan</th>
                <th width="29%" align="right" valign="middle" style="border-bottom:#333333 1px dashed; padding-bottom: 5px;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php $tot = 0; @endphp
            
            @forelse($items as $index => $item)
                <tr>
                    <td width="10%" align="center" valign="middle" style="padding-top: 5px;">{{ $index + 1 }}</td>
                    <td width="31%" align="left" style="padding-top: 5px;">{{ $item->title ?? '-' }}</td>
                    <td width="30%" align="left" valign="middle" style="padding-top: 5px;">{{ $item->note }}</td>
                    <td width="29%" align="right" valign="middle" style="padding-top: 5px;">{{ number_format($item->debit, 0, ',', '.') }}</td>
                </tr>
                @php $tot += $item->debit; @endphp
            @empty
                <tr>
                    <td colspan="4" align="center" style="padding: 10px;">Tidak ada rincian item.</td>
                </tr>
            @endforelse
            
            <tr>
                <td align="center" valign="middle" style="border-top:#999 2px solid; padding-top: 5px;">&nbsp;</td>
                <td align="left" style="border-top:#999 2px solid; padding-top: 5px;">&nbsp;</td>
                <td align="right" valign="middle" style="border-top:#999 2px solid; padding-top: 5px;"><strong>Total</strong></td>
                <td align="right" valign="middle" style="border-top:#999 2px solid; padding-top: 5px;">
                    <strong>{{ number_format($tot, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    <br /><br /><br />

    <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr>
            <td width="33%" align="center" valign="top"><strong>Kasir</strong></td>
            <td width="33%" align="center" valign="top"><strong>Yang Menerima</strong></td>
            <td width="33%" align="center" valign="top"><strong>Mengetahui</strong></td>
        </tr>
        <tr>
            <td align="center" valign="top" style="height: 60px;">&nbsp;</td>
            <td align="center" valign="top">&nbsp;</td>
            <td align="center" valign="top">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" valign="top">( ........................... )</td>
            <td align="center" valign="top">( ........................... )</td>
            <td align="center" valign="top">( ........................... )</td>
        </tr>
    </table>

</body>
</html>