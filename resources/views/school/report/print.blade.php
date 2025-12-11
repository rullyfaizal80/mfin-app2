<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Siswa</title>
    <style>
        /* --- 1. SETTING HALAMAN & KERTAS (Gaya Biodata) --- */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9; /* Background Abu */
            padding: 30px 0; 
            margin: 0;
            color: #333;
            line-height: 1.3;
        }

        /* Container Kertas A4 */
        .sheet { 
            background: white;
            width: 210mm; /* Lebar A4 Portrait */
            min-height: 297mm; /* Tinggi A4 */
            margin: 0 auto; 
            padding: 15mm; /* Margin dalam kertas */
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            box-sizing: border-box;
            position: relative;
        }

        /* --- 2. HEADER RAPAT (Sesuai Request Sebelumnya) --- */
        .header-container {
            text-align: center; 
            margin-bottom: 20px;
            border-bottom: 2px solid #333; 
            padding-bottom: 10px;
        }
        .header-logo { 
            width: 65px; 
            height: auto; 
            margin-bottom: 0px; /* Rapat ke bawah */
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header-container h2 { 
            margin: 0; padding: 0;
            font-size: 18px; 
            font-weight: 800; 
            color: #000; 
            text-transform: uppercase;
            line-height: 1.1; /* Jarak baris rapat */
            margin-top: 5px;  /* Jarak tipis dari logo */
        }
        .header-container p {
            margin: 0; padding: 0;
            font-size: 14px; 
            color: #444; 
            font-weight: 600;
            line-height: 1.1;
        }

        /* --- 3. FILTER INFO (Gaya Minimalis) --- */
        .filter-section {
            margin-bottom: 15px;
            font-size: 11px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 10px;
        }
        .filter-table { width: 100%; }
        .filter-table td { padding: 1px 0; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 80px; }

        /* --- 4. TABEL DATA SISWA --- */
        .data-table {
            width: 100%; border-collapse: collapse;
            font-size: 11px; 
            margin-top: 10px;
        }
        .data-table th {
            background-color: #eee; /* Warna Header Tabel Abu muda (mirip section-title biodata) */
            color: #000;
            font-weight: 700; text-transform: uppercase; font-size: 10px;
            padding: 8px 4px;
            border: 1px solid #999;
            text-align: center !important;
            vertical-align: middle;
            white-space: nowrap;
        }
        .data-table td {
            padding: 6px 4px; 
            border: 1px solid #ccc;
            vertical-align: top;
        }

        /* Helper Styles */
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .no-wrap { white-space: nowrap; }

        /* --- 5. TOMBOL & PRINT SETTINGS --- */
        .no-print { 
            text-align: right; 
            margin-bottom: 20px; 
            width: 210mm; 
            margin-left: auto; 
            margin-right: auto; 
        }
        .btn-print { 
            background: #0d6efd; color: white; 
            border: none; padding: 10px 20px; 
            border-radius: 4px; cursor: pointer; 
            font-size: 13px; font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background: #0b5ed7; }

        @media print { 
            @page { size: A4 portrait; margin: 10mm; }
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none; } 
            .sheet { 
                box-shadow: none; margin: 0; padding: 0; 
                width: 100%; min-height: auto; 
                border: none; 
            } 
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
            <h2>LAPORAN DATA SISWA</h2>
            <p>MIMHa Finance</p>
        </div>

        <div class="filter-section">
            <table class="filter-table">
                <tr>
                    <td class="label">Kelas</td>
                    <td>: <strong>{{ $classTitle }}</strong></td>
                    <td class="label" style="text-align: right;">Total Data : </td>
                    <td width="30" style="text-align: right;"><strong>{{ count($students) }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Periode</td>
                    <td>: {{ $subjectTitle }}</td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    @foreach($columns as $label => $key)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        @foreach($columns as $label => $key)
                            <td class="
                                {{ in_array($key, ['no', 'nis', 'gender', 'dob', 'mobile_phone']) ? 'text-center' : '' }}
                                {{ in_array($key, ['dob', 'nis', 'gender']) ? 'no-wrap' : '' }}
                            ">
                                @if($key == 'fullname')
                                    <span class="fw-bold">{{ $student->$key }}</span>
                                
                                @elseif($key == 'parents')
                                    <span style="color: #444; font-size: 10px;">{!! nl2br(e($student->$key ?? '-')) !!}</span>
                                
                                @elseif($key == 'gender')
                                     <b>{{ $student->$key }}</b>

                                @else
                                    {!! nl2br(e($student->$key ?? '-')) !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                @if(count($students) == 0)
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center" style="padding: 20px; color: #999;">
                            Data tidak ditemukan
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 30px; font-size: 10px; color: #555; text-align: right; border-top: 1px dashed #ccc; padding-top: 5px;">
            Dicetak oleh: {{ auth()->user()->fullname ?? 'Admin' }} | 
            Tanggal: {{ date('d F Y, H:i') }} WIB
        </div>

    </div>

</body>
</html>