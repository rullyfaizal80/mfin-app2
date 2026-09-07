<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f9;
        }

        .report-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 15mm;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); 
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-data th, .table-data td { 
            border: 1px solid #333; 
            padding: 6px; 
            vertical-align: top; 
        }
        .table-data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .btn-action {
            padding: 6px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; font-weight: bold; margin-left: 5px;
        }

        .info-table td { border: none; padding: 3px 0; font-size: 13px; }

        @media print {
            body { background-color: #fff; padding: 0; }
            .report-container { box-shadow: none; border: none; margin: 0; width: 100%; padding: 0;}
            .no-print { display: none; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi (Tidak ikut tercetak ke kertas) --}}
    <div class="no-print" style="width: 210mm; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-action" style="background: #0d6efd;">🖨️ Cetak Laporan</button>
        <button onclick="window.close()" class="btn-action" style="background: #6c757d;">Tutup</button>
    </div>

    <div class="report-container">
        
        {{-- Header Yayasan --}}
        <table border="0" cellspacing="0" cellpadding="0" style="border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px;">
            <tr>
                <td width="80" align="left" valign="middle">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 50px; height: auto;" onerror="this.style.display='none'">
                </td>
                <td align="left" valign="middle">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase;">Yayasan Fathul Huda Bandung</h2>
                    <p style="margin: 3px 0 0 0; font-size: 14px; color: #444; font-weight: bold;">Jl.Cikadut 252 Bandung 40194 Telp. 022-7212600</p>
                </td>
            </tr>
        </table>

        {{-- Judul Laporan --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; text-decoration: underline; text-transform: uppercase;">{{ $page_title }}</h3>
        </div>

        {{-- Info Siswa --}}
        <table class="info-table" style="width: 60%; margin-bottom: 20px;">
            <tr>
                <td width="25%"><strong>Nama Siswa</strong></td>
                <td width="5%">:</td>
                <td width="70%">{{ $student->fullname }}</td>
            </tr>
            <tr>
                <td><strong>Kelas</strong></td>
                <td>:</td>
                <td>{{ $student->kelas }} {{ $student->tgroup }} {{ $student->tsubject }}</td>
            </tr>
            <tr>
                <td><strong>Tahun</strong></td>
                <td>:</td>
                <td>{{ $year }}</td>
            </tr>
        </table>

        {{-- Tabel Data Rincian Pembayaran --}}
        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="25%">Referensi</th>
                    <th width="35%">Jenis Pembayaran (Komponen)</th>
                    <th width="20%">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $no = 1;
                    $tot = 0;
                @endphp
                
                @forelse($rcvs as $row)
                    <tr>
                        <td align="center">{{ $no++ }}</td>
                        <td align="center">{{ date('d/m/Y', strtotime($row->tdate)) }}</td>
                        <td>{{ $row->ref_no }}</td>
                        <td>{{ $row->title }}</td>
                        <td align="right">{{ number_format($row->payment, 0, ',', '.') }}</td>
                    </tr>      
                    @php $tot += $row->payment; @endphp
                @empty
                    <tr>
                        <td colspan="5" align="center" style="padding: 20px;">Belum ada data rincian pembayaran di tahun {{ $year }}.</td>
                    </tr>
                @endforelse
                
                {{-- Total Keseluruhan --}}
                <tr>
                    <td colspan="4" align="right" style="font-size: 14px; font-weight: bold; background-color: #f0f0f0;">TOTAL PEMBAYARAN :</td>
                    <td align="right" style="font-size: 14px; font-weight: bold; background-color: #f0f0f0; color: #198754;">
                        {{ number_format($tot, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

</body>
</html>