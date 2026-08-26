<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran - {{ $payment->ref_no }}</title>
    <style>
        /* Background untuk layar komputer */
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 20px 0;
            background-color: #f4f6f9;
        }

        /* --- WADAH KERTAS A6 --- */
        .a6-container {
            width: 105mm;      
            min-height: 148mm; 
            margin: 0 auto;
            background: #fff;
            padding: 8mm;      
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); 
            border: 1px solid #ccc;
            box-sizing: border-box; 
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: middle; }
        
        .btn-action {
            padding: 6px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; font-weight: bold; margin-left: 5px;
        }
        
        .section-title {
            text-align: center; border-bottom: 1px dashed #000; font-weight: bold; padding: 5px 0; margin-bottom: 5px;
        }
        
        @media print {
            body { background-color: #fff; padding: 0; }
            .a6-container { 
                box-shadow: none; border: none; margin: 0; width: 100%; min-height: auto;
            }
            .no-print { display: none; }
            @page { size: A6 portrait; margin: 5mm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="width: 105mm; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-action" style="background: #0d6efd;">🖨️ Cetak</button>
        <a href="{{ url('fincom/payment') }}" class="btn-action" style="background: #6c757d; text-decoration: none; display: inline-block; font-size: 11px;">Kembali</a>
    </div>

    <div class="a6-container">

        <!-- HEADER YAYASAN -->
        <table border="0" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid #333; margin-bottom: 10px; padding-bottom: 10px;">
            <tr>
                <td width="50" align="left">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 40px; height: auto;" onerror="this.style.display='none'">
                </td>
                <td align="center">
                    <h2 style="margin: 0; font-size: 13px; font-weight: bold; text-transform: uppercase;">Yayasan Fathul Huda Bandung</h2>
                    <p style="margin: 2px 0 0 0; font-size: 10px;">Jl. Cikadut 252 Bandung 40194 Telp. 022-7212600</p>
                </td>
            </tr>
        </table>

        <!-- INFO TRANSAKSI -->
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px; font-size: 12px;">
            BUKTI PEMBAYARAN<br>
            <span style="font-size: 10px; font-weight: normal;">{{ date("d-M-Y H:i", strtotime($payment->mdate)) }}</span>
        </div>

       <table cellspacing="0" cellpadding="2" style="margin-bottom: 10px; font-size: 11px;">
            <tr><td width="30%">Kode</td><td>: {{ $payment->ref_no }}</td></tr>
            <tr><td>Petugas</td><td>: {{ $payment->cashier ?? 'Kasir' }}</td></tr>
            <tr><td>Siswa</td><td>: <strong>{{ $payment->fullname }}</strong></td></tr>
            <tr><td>Kelas</td><td>: {{ $payment->kelas ?? '-' }}</td></tr>
            <tr><td>Catatan</td><td>: {{ $payment->note ?: '-' }}</td></tr>
        </table>

        <!-- 1. TABEL PEMBAYARAN (PAID) -->
        <div class="section-title">PEMBAYARAN</div>
        <table border="0" cellspacing="0" cellpadding="2" style="margin-bottom: 10px; font-size: 10px;">
            <thead>
                <tr>
                    <th width="5%" align="center">No</th>
                    <th width="35%" align="left">Jenis</th>
                    <th width="20%" align="left">Bulan</th>
                    <th width="40%" align="right">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php $n = 1; $tot = 0; @endphp
                @foreach($items as $i)
                    <tr>
                        <td align="center">{{ $n++ }}</td>
                        <td align="left">{{ $i->title }}</td>
                        <td align="left">{{ $i->pay_repeat == 'monthly' ? date("M y", strtotime($i->tdate)) : '-' }}</td>
                        <td align="right">{{ number_format($i->credit, 0, ',', '.') }}</td>
                    </tr>
                    @php $tot += $i->credit; @endphp
                @endforeach
                <tr>
                    <td colspan="3" align="right" style="padding-top: 5px;"><strong>TOTAL :</strong></td>
                    <td align="right" style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ number_format($tot, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- 2. TABEL RETUR (OPSIONAL) -->
        @if(count($returs) > 0)
        <div class="section-title" style="margin-top: 10px;">RETUR</div>
        <table border="0" cellspacing="0" cellpadding="2" style="margin-bottom: 10px; font-size: 10px;">
            <tbody>
                @php $n = 1; @endphp
                @foreach($returs as $r)
                    <tr>
                        <td width="5%" align="center">{{ $n++ }}</td>
                        <td width="35%" align="left">{{ $r->title }}</td>
                        <td width="20%" align="left">{{ $r->pay_repeat == 'monthly' ? date("M y", strtotime($r->tdate)) : '-' }}</td>
                        <td width="40%" align="right">{{ number_format($r->credit, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- TANDA TANGAN -->
        <table border="0" cellspacing="0" cellpadding="2" style="margin-top: 20px; text-align: center; font-size: 11px;">
            <tr>
                <td width="50%">Penyetor</td>
                <td width="50%">Kasir</td>
            </tr>
            <tr>
                <td style="height: 50px;">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>( ......................... )</td>
                <td>( ......................... )</td>
            </tr>
        </table>

    </div>
</body>
</html>