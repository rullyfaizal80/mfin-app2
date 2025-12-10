<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata - {{ $student->fullname }}</title>
    <style>
        body { font-family: sans-serif; padding: 30px; font-size: 13px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 30px; }
        
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        
        .section-title { background: #eee; padding: 5px 10px; font-weight: bold; margin: 20px 0 10px; border-left: 5px solid #333; }
        
        .row { display: flex; margin-bottom: 5px; }
        .label { width: 180px; font-weight: bold; }
        .sep { width: 20px; text-align: center; }
        .val { flex: 1; }

        @media print { .no-print { display: none; } .container { border: none; } }
        .btn { background: #28a745; color: white; padding: 8px 15px; border: none; cursor: pointer; float: right; border-radius: 4px;}
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Cetak</button>
    </div>

    <div class="container">
        <div class="header">
            <h2>MIMHa Finance</h2>
            <p>Biodata Lengkap Siswa</p>
        </div>

        <div class="section-title">DATA PRIBADI</div>
        <div class="row"><div class="label">Nama Lengkap</div><div class="sep">:</div><div class="val">{{ $student->fullname }}</div></div>
        <div class="row"><div class="label">NIS</div><div class="sep">:</div><div class="val">{{ $student->nis ?? '-' }}</div></div>
        <div class="row"><div class="label">NISN</div><div class="sep">:</div><div class="val">{{ $student->nin ?? '-' }}</div></div>
        <div class="row"><div class="label">Tempat, Tgl Lahir</div><div class="sep">:</div><div class="val">{{ $student->placeofbirth }}, {{ $student->dateofbirth }}</div></div>
        <div class="row"><div class="label">Jenis Kelamin</div><div class="sep">:</div><div class="val">{{ ($student->gender == 'L' || $student->gender == 'M') ? 'Laki-laki' : 'Perempuan' }}</div></div>
        <div class="row"><div class="label">Alamat</div><div class="sep">:</div><div class="val">{{ $student->street }} {{ $student->city }}</div></div>
        <div class="row"><div class="label">Telepon / HP</div><div class="sep">:</div><div class="val">{{ $student->home_phone }} / {{ $student->mobile_phone }}</div></div>
        <div class="row"><div class="label">Email</div><div class="sep">:</div><div class="val">{{ $student->email }}</div></div>
        <div class="row"><div class="label">Agama</div><div class="sep">:</div><div class="val">{{ $student->religion }}</div></div>
        <div class="row"><div class="label">Kelas Terakhir</div><div class="sep">:</div><div class="val">{{ $className }}</div></div>

        <div class="section-title">DATA ORANG TUA / WALI</div>
        <div class="row"><div class="label">Nama Ayah</div><div class="sep">:</div><div class="val">{{ $student->father_name ?? '-' }}</div></div>
        <div class="row"><div class="label">Nama Ibu</div><div class="sep">:</div><div class="val">{{ $student->mother_name ?? '-' }}</div></div>
        <div class="row"><div class="label">Nama Wali</div><div class="sep">:</div><div class="val">{{ $student->guardian_name ?? '-' }}</div></div>
        <div class="row"><div class="label">Hubungan Wali</div><div class="sep">:</div><div class="val">{{ $student->parent_relation ?? '-' }}</div></div>

        <div class="section-title">LAIN-LAIN</div>
        <div class="row"><div class="label">Tinggi / Berat</div><div class="sep">:</div><div class="val">{{ $student->height }} cm / {{ $student->weight }} kg</div></div>
        <div class="row"><div class="label">Anak Ke</div><div class="sep">:</div><div class="val">{{ $student->birthorder }} dari {{ $student->total_sibling }} bersaudara</div></div>
        <div class="row"><div class="label">Jarak ke Sekolah</div><div class="sep">:</div><div class="val">{{ $student->distancetoschool ?? '-' }}</div></div>
        <div class="row"><div class="label">Transportasi</div><div class="sep">:</div><div class="val">{{ $student->gotoschool_with ?? '-' }}</div></div>

        {{-- [BARU] REKAPITULASI KELAS --}}
        <div class="section-title">RIWAYAT KELAS & EKSTRAKURIKULER</div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="border: 1px solid #ccc; padding: 6px; text-align: left;">Tahun Ajaran</th>
                    <th style="border: 1px solid #ccc; padding: 6px; text-align: left;">Nama Kelas</th>
                    <th style="border: 1px solid #ccc; padding: 6px; text-align: left;">Tipe</th>
                    <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyKelas as $h)
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 5px;">{{ $h->year_name }}</td>
                        <td style="border: 1px solid #ccc; padding: 5px; font-weight: bold;">{{ $h->class_name }}</td>
                        <td style="border: 1px solid #ccc; padding: 5px;">{{ $h->type_name }}</td>
                        <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">
                            @if($h->is_active == 'yes')
                                <span style="color: green; font-weight: bold;">Aktif</span>
                            @else
                                <span style="color: #888;">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="border: 1px solid #ccc; padding: 10px; text-align: center;">Belum ada riwayat kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 30px; text-align: right; font-size: 10px; color: #555;">
        <div style="margin-top: 30px; text-align: right; font-size: 10px; color: #555;">
            Dicetak oleh: {{ session('fullname', 'Admin') }} | 
            Tanggal: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

</body>
</html>