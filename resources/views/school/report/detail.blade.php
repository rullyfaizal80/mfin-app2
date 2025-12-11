<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata Siswa - {{ $student->fullname }}</title>
    <style>
        /* --- STANDARD DEFAULT PRINT STYLE (MIMHa Finance) --- */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9; /* Background Abu */
            padding: 30px 0; 
            margin: 0;
            color: #333;
            line-height: 1.3;
        }

        /* Container Kertas A4 Portrait */
        .sheet { 
            background: white;
            width: 210mm; 
            min-height: 297mm; 
            margin: 0 auto; 
            padding: 15mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            box-sizing: border-box;
            position: relative;
        }

        /* Header Rapat */
        .header-container {
            text-align: center; 
            margin-bottom: 25px;
            border-bottom: 2px solid #333; 
            padding-bottom: 10px;
        }
        .header-logo { 
            width: 65px; 
            height: auto; 
            margin-bottom: 0px; 
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
            line-height: 1.1; 
            margin-top: 5px; 
        }
        .header-container p {
            margin: 0; padding: 0;
            font-size: 14px; 
            color: #444; 
            font-weight: 600;
            line-height: 1.1;
        }

        /* Section Title (Biodata Style) */
        .section-title { 
            background: #eee; 
            padding: 6px 10px; 
            font-weight: bold; 
            margin: 20px 0 10px 0; 
            border-left: 4px solid #555; 
            font-size: 13px;
            text-transform: uppercase;
        }

        /* Data Rows */
        .row-data { display: flex; margin-bottom: 6px; font-size: 12px; }
        .label { width: 160px; font-weight: bold; color: #555; }
        .colon { width: 15px; text-align: center; font-weight: bold; }
        .value { flex: 1; color: #000; font-weight: 600;}

        /* Tabel History Kelas */
        .data-table {
            width: 100%; border-collapse: collapse;
            font-size: 11px; margin-top: 5px;
        }
        .data-table th {
            background-color: #eee; color: #000;
            font-weight: 700; text-transform: uppercase; font-size: 10px;
            padding: 6px; border: 1px solid #999;
            text-align: center;
        }
        .data-table td {
            padding: 5px; border: 1px solid #ccc;
        }
        .text-center { text-align: center; }

        /* Tombol Print */
        .no-print { 
            text-align: right; margin-bottom: 20px; 
            width: 210mm; margin-left: auto; margin-right: auto; 
        }
        .btn-print { 
            background: #0d6efd; color: white; border: none; padding: 10px 20px; 
            border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background: #0b5ed7; }

        /* Print Media Query */
        @media print { 
            @page { size: A4 portrait; margin: 10mm; }
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none; } 
            .sheet { box-shadow: none; margin: 0; padding: 0; width: 100%; min-height: auto; border: none; } 
            .section-title { background-color: #eee !important; -webkit-print-color-adjust: exact; }
            .data-table th { background-color: #eee !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Biodata</button>
    </div>

    <div class="sheet">
        
        <div class="header-container">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="header-logo" onerror="this.style.display='none'">
            <h2>BIODATA SISWA</h2>
            <p>MIMHa Finance</p>
        </div>

        <div class="section-title">A. Data Pribadi</div>
        <div class="row-data"><div class="label">Nama Lengkap</div><div class="colon">:</div><div class="value" style="font-size: 13px; text-transform: uppercase;">{{ $student->fullname }}</div></div>
        <div class="row-data"><div class="label">NIS / NISN</div><div class="colon">:</div><div class="value">{{ $student->nis ?? '-' }} / {{ $student->nin ?? '-' }}</div></div>
        <div class="row-data"><div class="label">Tempat, Tgl Lahir</div><div class="colon">:</div><div class="value">{{ $student->placeofbirth }}, {{ $student->dateofbirth ? date('d F Y', strtotime($student->dateofbirth)) : '-' }}</div></div>
        <div class="row-data"><div class="label">Jenis Kelamin</div><div class="colon">:</div><div class="value">{{ ($student->gender == 'L' || $student->gender == 'M') ? 'Laki-laki' : 'Perempuan' }}</div></div>
        <div class="row-data"><div class="label">Alamat</div><div class="colon">:</div><div class="value">{{ $student->street }} {{ $student->city }}</div></div>
        <div class="row-data"><div class="label">No. Telepon</div><div class="colon">:</div><div class="value">{{ $student->home_phone ?? '-' }}</div></div>
        <div class="row-data"><div class="label">No. HP Siswa</div><div class="colon">:</div><div class="value">{{ $student->mobile_phone ?? '-' }}</div></div>
        <div class="row-data"><div class="label">Anak Ke / Dari</div><div class="colon">:</div><div class="value">{{ $student->birthorder ?? '-' }} dari {{ $student->total_sibling ?? '-' }} Bersaudara</div></div>

        <div class="section-title">B. Data Orang Tua / Wali</div>
        <div class="row-data"><div class="label">Nama Ayah</div><div class="colon">:</div><div class="value">{{ $student->father_name ?? '-' }}</div></div>
        <div class="row-data"><div class="label">No. HP Ayah</div><div class="colon">:</div><div class="value">{{ $student->father_phone ?? '-' }}</div></div>
        <div class="row-data"><div class="label">Nama Ibu</div><div class="colon">:</div><div class="value">{{ $student->mother_name ?? '-' }}</div></div>
        <div class="row-data"><div class="label">No. HP Ibu</div><div class="colon">:</div><div class="value">{{ $student->mother_phone ?? '-' }}</div></div>
        @if($student->guardian_name)
        <div class="row-data"><div class="label">Nama Wali</div><div class="colon">:</div><div class="value">{{ $student->guardian_name }}</div></div>
        @endif

        <div class="section-title">C. Riwayat Kelas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Tahun Ajaran</th>
                    <th>Kelas</th>
                    <th>Jurusan / Subjek</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyKelas as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $row->year }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ $row->class_name }}</td>
                        <td class="text-center">{{ $row->subject }}</td>
                        <td class="text-center">
                            @if($row->is_active == 'yes')
                                <span style="font-weight: bold; color: green;">Aktif</span>
                            @else
                                <span style="color: #777;">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 10px; color: #777;">Belum ada riwayat kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 50px; font-size: 10px; color: #555; text-align: right; border-top: 1px dashed #ccc; padding-top: 5px;">
            Dicetak oleh: {{ auth()->user()->fullname ?? 'Admin' }} | 
            {{-- Menggunakan Carbon dengan Timezone Asia/Jakarta agar konsisten WIB --}}
            Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>

    </div>

</body>
</html>