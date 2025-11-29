<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Dikerjakan | MFIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-card {
            max-width: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            padding: 50px 30px;
            text-align: center;
            border-top: 5px solid #ffc107; /* Warna Kuning Warning */
        }
        .icon-box {
            background-color: #fff3cd;
            color: #ffc107;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        .icon-box i {
            font-size: 3rem;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.6;
        }
        .btn-home {
            margin-top: 25px;
            padding: 10px 30px;
            border-radius: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="maintenance-card">
                <div class="icon-box">
                    <i class="bi bi-cone-striped"></i>
                </div>
                
                <h1>Fitur Sedang Dalam Pengerjaan</h1>
                
                <p>
                    Mohon maaf, modul atau halaman yang Anda tuju <strong>belum tersedia</strong> di sistem baru ini.
                    Tim teknis kami sedang memigrasikan fitur ini dari aplikasi lama.
                </p>
                
                <hr class="my-4" style="opacity: 0.1">

                <p class="small text-muted mb-4">
                    Jika Anda sangat membutuhkan fitur ini sekarang, silakan hubungi Administrator.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-home">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-warning text-white btn-home">
                        <i class="bi bi-house-door"></i> Ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>