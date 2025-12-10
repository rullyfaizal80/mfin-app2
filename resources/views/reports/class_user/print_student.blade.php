<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata - {{ $student->fullname }}</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            padding: 20px 0; 
            color: #333;
        }
        /* Container Kertas A4 */
        .profile-card { 
            background: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto; 
            padding: 40px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            box-sizing: border-box;
        }

        .header { 
            text-align: center;
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
            margin-bottom: 30px; 
        }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 22px; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #555; }

        .row { display: flex; margin-bottom: 8px; font-size: 13px; }
        .label { width: 180px; font-weight: bold; color: #555; }
        .colon { width: 20px; text-align: center; }
        .value { flex: 1; font-weight: 600; color: #000; }
        
        .section-title { 
            background: #eee; 
            padding: 8px 10px; 
            font-weight: bold; 
            margin: 25px 0 15px 0; 
            border-left: 5px solid #333; 
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .no-print { text-align: right; margin-bottom: 20px; width: 210mm; margin-left: auto; margin-right: auto; }
        .btn-print { background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }

        @media print { 
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none; } 
            .profile-card { box-shadow: none; margin: 0; padding: 0; width: 100%; min-height: auto; border: none; } 
            @page { margin: 20mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Biodata</button>
    </div>

    <div class="profile-card">
        <div class="header">
            <img src="{{ asset('images/logo.jpg') }}" style="width: 70px; margin-bottom: 10px;" onerror="this.style.display='none'">
            <h2>Biodata Siswa</h2>
            <p>MIMHa Finance</p>
        </div>

        <div class="section-title">A. Data Pribadi</div>
        <div class="row"><div class="label">Nama Lengkap</div><div class="colon">:</div><div class="value">{{ $student->fullname }}</div></div>
        <div class="row"><div class="label">NIS</div><div class="colon">:</div><div class="value">{{ $student->nis ?? '-' }}</div></div>
        <div class="row"><div class="label">NISN</div><div class="colon">:</div><div class="value">{{ $student->nin ?? '-' }}</div></div>
        <div class="row"><div class="label">Tempat, Tgl Lahir</div><div class="colon">:</div><div class="value">{{ $student->placeofbirth }}, {{ $student->dateofbirth ? date('d F Y', strtotime($student->dateofbirth)) : '-' }}</div></div>
        <div class="row"><div class="label">Jenis Kelamin</div><div class="colon">:</div><div class="value">{{ ($student->gender == 'L' || $student->gender == 'M') ? 'Laki-laki' : 'Perempuan' }}</div></div>
        <div class="row"><div class="label">Alamat</div><div class="colon">:</div><div class="value">{{ $student->street }} {{ $student->city }}</div></div>
        <div class="row"><div class="label">No. Telepon / HP</div><div class="colon">:</div><div class="value">{{ $student->home_phone }} / {{ $student->mobile_phone }}</div></div>

        <div class="section-title">B. Data Orang Tua / Wali</div>
        <div class="row"><div class="label">Nama Ayah</div><div class="colon">:</div><div class="value">{{ $student->father_name ?? '-' }}</div></div>
        <div class="row"><div class="label">No. HP Ayah</div><div class="colon">:</div><div class="value">{{ $student->father_phone ?? '-' }}</div></div>
        <div class="row"><div class="label">Nama Ibu</div><div class="colon">:</div><div class="value">{{ $student->mother_name ?? '-' }}</div></div>
        <div class="row"><div class="label">No. HP Ibu</div><div class="colon">:</div><div class="value">{{ $student->mother_phone ?? '-' }}</div></div>
        <div class="row"><div class="label">Nama Wali</div><div class="colon">:</div><div class="value">{{ $student->guardian_name ?? '-' }}</div></div>

        {{-- Footer Standar --}}
        <div style="margin-top: 50px; font-size: 10px; color: #555; text-align: right; border-top: 1px dashed #ccc; padding-top: 5px;">
            Dicetak oleh: {{ session('fullname', 'Admin') }} | 
            Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>
</body>
</html>