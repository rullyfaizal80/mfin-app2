<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata Guru - {{ $teacher->fullname }}</title>
    <style>
        /* --- STANDARD DEFAULT PRINT STYLE --- */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9; padding: 30px 0; margin: 0; color: #333; line-height: 1.3;
        }
        .sheet { 
            background: white; width: 210mm; min-height: 297mm; 
            margin: 0 auto; padding: 15mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            box-sizing: border-box; position: relative;
        }
        
        /* HEADER */
        .header-container {
            text-align: center; margin-bottom: 25px;
            border-bottom: 2px solid #333; padding-bottom: 10px;
        }
        .header-logo { width: 65px; height: auto; margin-bottom: 0px; display: block; margin: 0 auto; }
        .header-container h2 { margin: 5px 0 0 0; font-size: 18px; font-weight: 800; color: #000; text-transform: uppercase; line-height: 1.1; }
        .header-container p { margin: 0; font-size: 14px; color: #444; font-weight: 600; line-height: 1.1; }

        /* SECTION & DATA */
        .section-title { 
            background: #eee; padding: 6px 10px; font-weight: bold; 
            margin: 20px 0 10px 0; border-left: 4px solid #555; 
            font-size: 13px; text-transform: uppercase;
        }
        .row-data { display: flex; margin-bottom: 6px; font-size: 12px; }
        .label { width: 180px; font-weight: bold; color: #555; }
        .colon { width: 15px; text-align: center; font-weight: bold; }
        .value { flex: 1; color: #000; font-weight: 600;}

        /* TABEL */
        .data-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 5px; }
        .data-table th {
            background-color: #eee; color: #000; font-weight: 700; text-transform: uppercase; 
            font-size: 10px; padding: 6px; border: 1px solid #999; text-align: center;
        }
        .data-table td { padding: 5px; border: 1px solid #ccc; }
        .text-center { text-align: center; }

        /* PRINT BTN */
        .no-print { 
            text-align: right; margin-bottom: 20px; 
            width: 210mm; margin-left: auto; margin-right: auto; 
        }
        .btn-print { background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-print:hover { background: #0b5ed7; }

        @media print { 
            @page { size: A4 portrait; margin: 10mm; }
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none; } 
            .sheet { box-shadow: none; margin: 0; padding: 0; width: 100%; border: none; } 
            .section-title, .data-table th { background-color: #eee !important; -webkit-print-color-adjust: exact; }
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
            <h2>BIODATA GURU / KARYAWAN</h2>
            <p>MIMHa Finance</p>
        </div>

        <div class="section-title">A. Data Pribadi</div>
        <div class="row-data"><div class="label">Nama Lengkap</div><div class="colon">:</div><div class="value" style="text-transform: uppercase;">{{ $teacher->fullname }}</div></div>
        <div class="row-data"><div class="label">NIP / NIK</div><div class="colon">:</div><div class="value">{{ $teacher->nik ?? '-' }}</div></div>
        <div class="row-data"><div class="label">No. Izin Yayasan</div><div class="colon">:</div><div class="value">{{ $teacher->foundation_license_no ?? '-' }}</div></div>
        <div class="row-data">
            <div class="label">Tempat, Tgl Lahir</div>
            <div class="colon">:</div>
            <div class="value">
                {{ $teacher->placeofbirth }}, 
                
                @php
                    $isValidDate = $teacher->dateofbirth && $teacher->dateofbirth != '0000-00-00' && strtotime($teacher->dateofbirth) > 0;
                @endphp

                @if($isValidDate)
                    {{ date('d M Y', strtotime($teacher->dateofbirth)) }}
                @else
                    01 01 1970
                @endif
            </div>
        </div>
        <div class="row-data"><div class="label">Jenis Kelamin</div><div class="colon">:</div><div class="value">{{ ($teacher->gender == 'L' || $teacher->gender == 'M') ? 'Laki-laki' : 'Perempuan' }}</div></div>
        <div class="row-data"><div class="label">Alamat</div><div class="colon">:</div><div class="value">{{ $teacher->street }} {{ $teacher->city }}</div></div>
        <div class="row-data"><div class="label">Telepon / HP</div><div class="colon">:</div><div class="value">{{ $teacher->home_phone }} / {{ $teacher->mobile_phone }}</div></div>
        <div class="row-data"><div class="label">Email</div><div class="colon">:</div><div class="value">{{ $teacher->email ?? '-' }}</div></div>

        <div class="section-title">B. Pengalaman Kerja</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Instansi / Sekolah</th>
                    <th>Jabatan</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($experience as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->company_name ?? '-' }}</td> {{-- Sesuaikan nama kolom DB --}}
                        <td class="text-center">{{ $row->position ?? '-' }}</td>
                        <td class="text-center">{{ $row->start_date ? date('d-m-Y', strtotime($row->start_date)) : '-' }}</td>
                        <td class="text-center">{{ $row->end_date ? date('d-m-Y', strtotime($row->end_date)) : 'Sekarang' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 10px; color: #777;">Belum ada data pengalaman kerja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 50px; font-size: 10px; color: #555; text-align: right; border-top: 1px dashed #ccc; padding-top: 5px;">
            Dicetak oleh: {{ auth()->user()->fullname ?? 'Admin' }} | 
            Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>
</body>
</html>