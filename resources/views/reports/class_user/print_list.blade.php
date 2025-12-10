<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Siswa - {{ $classList->title }}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fff; padding: 20px; color: #333; }
        
        /* Container Kertas */
        .container { width: 100%; margin: 0 auto; }
        
        /* Header Modern dengan Tabel Info */
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 15px; }
        .header-title { text-align: center; margin-bottom: 15px; }
        .header-title h2 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header-title p { margin: 2px 0; font-size: 12px; color: #666; }

        /* Tabel Info Header (Kiri & Kanan) */
        .info-table { width: 100%; font-size: 12px; margin-bottom: 5px; }
        .info-table td { padding: 2px; vertical-align: top; }
        .info-label { font-weight: bold; width: 100px; }
        .info-colon { width: 10px; text-align: center; }

        /* Tabel Data Siswa */
        .data-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        .data-table th { background: #f0f0f0; border: 1px solid #000; padding: 6px; text-align: left; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        
        /* Print Settings */
        .no-print { text-align: right; margin-bottom: 15px; }
        .btn-print { background: #0d6efd; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        .text-center { text-align: center; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
    </div>

    <div class="container">
        
        <div class="header">
            <div class="header-title">
                <img src="{{ asset('images/logo.jpg') }}" style="width: 60px; height:auto; display: block; margin: 0 auto 5px auto;" onerror="this.style.display='none'">
                <h2>DAFTAR SISWA KELAS {{ $classList->title }}</h2>
                <p>{{ $classList->school_name }}</p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Tahun Ajaran</td>
                    <td class="info-colon">:</td>
                    <td width="40%">{{ $classList->year_title }}</td>
                    
                    <td class="info-label">Tipe Kelas</td>
                    <td class="info-colon">:</td>
                    <td>{{ $classList->type_title }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tingkat</td>
                    <td class="info-colon">:</td>
                    <td>{{ $classList->grade_title }} {{ $classList->group_title }}</td>
                    
                    <td class="info-label">Wali Kelas</td>
                    <td class="info-colon">:</td>
                    <td>{{ $classList->wali_name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    {{-- Loop Header Kolom Dinamis --}}
                    @foreach($columns as $label => $fieldKey)
                        @if($fieldKey == 'no' || $fieldKey == 'gender_indo')
                            <th style="text-align: center; width: 30px;">{{ $label }}</th>
                        @else
                            <th>{{ $label }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $row)
                    <tr>
                        {{-- Loop Data Dinamis --}}
                        @foreach($columns as $label => $fieldKey)
                            @if($fieldKey == 'no')
                                <td class="text-center">{{ $index + 1 }}</td>
                            @elseif($fieldKey == 'gender_indo')
                                <td class="text-center">{{ $row->$fieldKey }}</td>
                            @else
                                <td>{{ $row->$fieldKey ?? '-' }}</td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" style="text-align: center; padding: 20px;">
                            Tidak ada data siswa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 20px; font-size: 10px; color: #555; text-align: right;">
            Dicetak oleh: {{ session('fullname', 'Admin') }} | 
    Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>
</body>
</html>