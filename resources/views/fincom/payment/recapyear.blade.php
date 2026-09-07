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

        /* Diubah menjadi 297mm untuk kertas A4 Landscape (Mendatar) */
        .report-container {
            width: 297mm; 
            min-height: 210mm;
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
            body, html { background-color: #fff; padding: 0; margin: 0; }
            .report-container { 
                box-shadow: none; 
                border: none; 
                margin: 0; 
                width: 100%; 
                padding: 0;
                min-height: auto !important; /* Menghapus paksaan tinggi */
                height: auto !important;
            }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 10mm; }
            
            /* Mencegah tabel terpotong di tengah baris jika datanya banyak */
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi (Tidak ikut tercetak) --}}
    <div class="no-print" style="width: 297mm; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-action" style="background: #0d6efd;">🖨️ Cetak Laporan</button>
        <button onclick="window.close()" class="btn-action" style="background: #6c757d;">Tutup</button>
    </div>

    <div class="report-container">
        
        {{-- Header Yayasan Konsisten --}}
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
        <table class="info-table" style="width: 50%; margin-bottom: 20px;">
            <tr>
                <td width="30%"><strong>Nama Siswa</strong></td>
                <td width="5%">:</td>
                <td width="65%">{{ $student->fullname }}</td>
            </tr>
            <tr>
                <td><strong>Kelas</strong></td>
                <td>:</td>
                <td>{{ $student->kelas }} {{ $student->tgroup }} {{ $student->tsubject }}</td>
            </tr>
            <tr>
                <td><strong>Tahun Pembayaran</strong></td>
                <td>:</td>
                <td>{{ $year }}</td>
            </tr>
        </table>

        {{-- Tabel Data Matriks 12 Bulan --}}
        <table class="table-data">
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="15%">Pembayaran</th>
                    <th width="6%">Jan</th>
                    <th width="6%">Feb</th>
                    <th width="6%">Mar</th>
                    <th width="6%">Apr</th>
                    <th width="6%">Mei</th>
                    <th width="6%">Jun</th>
                    <th width="6%">Jul</th>
                    <th width="6%">Ags</th>
                    <th width="6%">Sep</th>
                    <th width="6%">Okt</th>
                    <th width="6%">Nov</th>
                    <th width="6%">Des</th>
                    <th width="7%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $n = 1;
                    $totalall = 0;
                    $totalBulan = array_fill(1, 12, 0);
                @endphp

                @forelse($lrcv as $rcv)
                    <tr>
                        <td align="center"><strong>{{ $n++ }}</strong></td>
                        <td>{{ $rcv['title'] }}</td>

                        @php $subtot = 0; @endphp
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $val = $rcv['mons'][$m];
                                $subtot += $val;
                                $totalBulan[$m] += $val;
                            @endphp
                            <td align="right">{{ $val > 0 ? number_format($val, 0, ',', '.') : '0' }}</td>
                        @endfor

                        @php $totalall += $subtot; @endphp
                        <td align="right" style="font-weight: bold; background-color: #f9f9f9;">
                            {{ number_format($subtot, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" align="center" style="padding: 20px;">Belum ada data pembayaran di tahun ini.</td>
                    </tr>
                @endforelse

                {{-- Total Keseluruhan --}}
                @if(count($lrcv) > 0)
                <tr>
                    <td colspan="2" align="center" style="font-size: 13px; font-weight: bold; background-color: #f0f0f0;">TOTAL KESELURUHAN</td>
                    @for($m = 1; $m <= 12; $m++)
                        <td align="right" style="font-size: 12px; font-weight: bold; background-color: #f0f0f0;">
                            {{ number_format($totalBulan[$m], 0, ',', '.') }}
                        </td>
                    @endfor
                    <td align="right" style="font-size: 13px; font-weight: bold; background-color: #f0f0f0; color: #198754;">
                        {{ number_format($totalall, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

    </div>

</body>
</html>