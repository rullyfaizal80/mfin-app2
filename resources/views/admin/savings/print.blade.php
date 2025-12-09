<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tabungan - {{ $identity['fullname'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 10px 20px; } /* Font sedikit diperkecil agar muat */
        
        /* Layout Header */
        .header-container { text-align: center; margin-bottom: 20px; }
        .header-logo { width: 70px; height: auto; display: block; margin: 0 auto 5px auto; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 12px; }
        .header h3 { margin-top: 5px; text-decoration: underline; font-size: 13px; }

        /* Info Siswa */
        .info-table { width: 100%; margin-bottom: 10px; font-size: 11px; }
        .info-table td { padding: 2px; vertical-align: top; }
        
        /* Tabel Data */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px 6px; }
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; vertical-align: middle; }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .bg-gray { background-color: #f9f9f9; }

        /* Tombol Cetak */
        .action-bar { margin-bottom: 15px; text-align: right; }
        .btn-print {
            background-color: #0d6efd; color: white; border: none; 
            padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;
        }
        .btn-print:hover { background-color: #0b5ed7; }

        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
    </div>

    <div class="header-container">
        {{-- Logo --}}
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
        <br><br>
        <div class="header">            
            <h2>TRANSAKSI TABUNGAN</h2>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td width="12%">Nama</td>
            <td width="2%">:</td>
            <td width="40%"><strong>{{ $identity['fullname'] }}</strong></td>
            
            <td width="12%">Periode</td>
            <td width="2%">:</td>
            <td width="32%">{{ date('d-M-Y', strtotime($ffrom)) }} s/d {{ date('d-M-Y', strtotime($fto)) }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td>{{ $identity['other'] }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            {{-- BARIS SALDO PINDAHAN --}}
            @php $current_balance = $saldo_pindahan; @endphp
            <tr>
                {{-- colspan jadi 6 karena total kolom sekarang 7 --}}
                <td colspan="6" class="text-right text-bold bg-gray">Saldo Awal (Pindahan)</td>
                <td class="text-right text-bold bg-gray">{{ number_format($current_balance, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th width="4%">No</th>
                <th width="15%">No Referensi</th>
                <th width="13%">Tanggal</th>   {{-- Kolom Baru --}}
                <th>Keterangan</th>            {{-- Kolom Baru --}}
                <th width="13%">Setor</th>
                <th width="13%">Tarik</th>
                <th width="15%">Saldo</th>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->ref_no }}</td>
                    
                    {{-- TANGGAL (Kolom Sendiri) --}}
                    <td class="text-center">{{ date('d-m-Y', strtotime($row->tdate)) }}</td>
                    
                    {{-- KETERANGAN (Kolom Sendiri) --}}
                    <td>{{ $row->note }}</td>
                    
                    <td class="text-right">{{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($current_balance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                {{-- colspan jadi 4 (No + Ref + Tgl + Ket) --}}
                <td colspan="4" class="text-right text-bold">Total Transaksi</td>
                <td class="text-right text-bold">{{ number_format($total_setor, 0, ',', '.') }}</td>
                <td class="text-right text-bold">{{ number_format($total_tarik, 0, ',', '.') }}</td>
                <td class="text-right text-bold bg-gray">{{ number_format($current_balance, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 15px; font-size: 9px; color: #555; text-align: right;">
        Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
    </div>

</body>
</html>