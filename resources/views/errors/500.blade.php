<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan | MFIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; text-align: center; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="error-card">
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle-fill">⚠️</i>
                </div>
                <h2 class="fw-bold text-dark">Mohon Maaf</h2>
                <p class="text-muted mt-3">
                    Sistem sedang mengalami gangguan internal atau tidak dapat memproses permintaan Anda saat ini.
                </p>
                <p class="small text-secondary">
                    Tim teknis kami otomatis menerima laporan ini. Silakan coba muat ulang halaman atau hubungi administrator jika masalah berlanjut.
                </p>
                <div class="mt-4">
                    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">Muat Ulang</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>