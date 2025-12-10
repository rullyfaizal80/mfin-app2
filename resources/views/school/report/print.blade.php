<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kelas - {{ $classInfo->title }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        
        /* Tombol Print (Hilang saat diprint) */
        @media print { .no-print { display: none; } }
        .btn-print { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; float: right; }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
    </div>

    <div class="header">
        <img src="{{ asset('images/logo.jpg') }}" style="height: 50px; margin-bottom: 5px;" onerror="this.style.display='none'">
        <h1>MIMHa Finance</h1>
        <p>Laporan Data Siswa Kelas: <strong>{{ $classInfo->title }} - {{ $classInfo->subject }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $colName => $colKey)
                    <th>{{ $colName }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $row)
                <tr>
                    @foreach($columns as $colName => $colKey)
                        @if($colKey == 'no')
                            <td class="text-center">{{ $index + 1 }}</td>
                        @elseif($colKey == 'age')
                            <td class="text-center">{{ $row->age }}</td>
                        @else
                            <td>{{ $row->$colKey ?? '-' }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-size: 10px; color: #555;">
        Dicetak oleh: {{ session('fullname', 'Admin') }} | 
        Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
    </div>

</body>
</html>