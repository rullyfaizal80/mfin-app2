@extends('layouts.app')

@section('title', $page_title . ' | MIMHa Finance')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h3">{{ $page_title }}</h1>
        <p class="text-muted mb-0">
            Periode: <strong>{{ date('d M Y', strtotime($period->period_start)) }}</strong> s/d <strong>{{ date('d M Y', strtotime($period->period_end)) }}</strong>
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('fincom.batch.lproc', $period_id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        {{-- PANEL KONTROL --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-sliders"></i> Panel Kontrol</h5>
            </div>
            <div class="card-body text-center">
                <p>Klik tombol di bawah untuk memulai *generate* tagihan SPP secara massal.</p>
                <hr>
                <button id="btnRun" class="btn btn-success btn-lg w-100 mb-2 fw-bold">
                    <i class="bi bi-play-fill"></i> MULAI PROSES
                </button>
                <button id="btnStop" class="btn btn-danger btn-lg w-100 fw-bold" disabled>
                    <i class="bi bi-stop-fill"></i> HENTIKAN (STOP)
                </button>
            </div>
            <div class="card-footer bg-light text-center">
                <span id="statusBadge" class="badge bg-secondary p-2 fs-6">Status: MENUNGGU</span>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        {{-- TERMINAL LOG --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0"><i class="bi bi-terminal"></i> Log Aktivitas</h5>
    <div>
        <span class="badge bg-secondary me-1">Total: <span id="count_total">{{ $total_siswa }}</span></span>
        
        <span class="badge bg-success me-1">Sudah: <span id="count_sudah">{{ $sudah_proses }}</span></span>
        
        <span class="badge bg-warning text-dark me-1">Belum: <span id="count_belum">{{ $belum_proses }}</span></span>
        
        <span class="badge bg-primary">Sesi Ini: <span id="count_sesi">0</span></span>
    </div>
</div>
            <div class="card-body bg-black text-success" style="height: 350px; overflow-y: auto; font-family: monospace; font-size: 13px;" id="consoleLog">
                > Sistem siap.<br>
                > Menunggu perintah proses...<br>
            </div>
        </div>
    </div>
</div>

{{-- TUTUP DULU SECTION CONTENT-NYA --}}
@endsection

{{-- BUAT SECTION BARU UNTUK SCRIPT --}}
@push('scripts')
{{-- Panggil jQuery KHUSUS untuk halaman ini saja agar tidak ganggu halaman lain --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let is_finish = false;
    let xhrRequest = null;
    const period_id = "{{ $period_id }}";
    
    // Ambil angka dari tampilan awal Blade
    let countTotal  = parseInt("{{ $total_siswa }}");
    let countSudah  = parseInt("{{ $sudah_proses }}");
    let countSesi   = 0;

    function addLog(message, isError = false) {
        const color = isError ? "text-danger" : "text-success";
        $('#consoleLog').append(`<span class="${color}">${message}</span><br>`);
        $('#consoleLog').scrollTop($('#consoleLog')[0].scrollHeight);
    }

    function runProcessChunk() {
        if (is_finish) return;

        xhrRequest = $.ajax({
            url: "{{ url('fincom/batch/process-chunk') }}/" + period_id,
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            dataType: "json",
            success: function(res) {
                if (res.status === 'finished') {
                    is_finish = true;
                    $('#btnRun').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
                    $('#btnStop').prop('disabled', true);
                    $('#statusBadge').removeClass('bg-warning text-dark').addClass('bg-success text-white').text("Status: SELESAI");
                    
                    addLog("======================================");
                    addLog("> SELURUH DATA TELAH SELESAI DIPROSES DENGAN CEPAT!", false);
                    return;
                }

                if (res.status === 'processing') {
                    countSesi  += res.processed_count;
                    countSudah += res.processed_count;
                    let sisaBelum = countTotal - countSudah;

                    $('#count_sesi').text(countSesi);
                    $('#count_sudah').text(countSudah);
                    $('#count_belum').text(sisaBelum < 0 ? 0 : sisaBelum);

                    addLog(`> Memproses data... [ ${res.processed_count} siswa ditambahkan ke database ]`);

                    // Lanjut tembak lagi (Otomatis Looping ke 50 siswa berikutnya)
                    if(!is_finish) {
                        runProcessChunk();
                    }
                }
            },
            error: function(xhr, status, error) {
                // Jika error karena admin menekan tombol STOP, abaikan
                if (status === 'abort') {
                    addLog(`> Menghentikan komunikasi dengan server...`, true);
                    return;
                }
                
                // Tangkap pesan Error murni dari Laravel
                let errorMsg = "Koneksi terputus/Internal Server Error.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status) {
                    errorMsg = "HTTP Error " + xhr.status + ": " + xhr.statusText;
                }
                
                addLog(`> ERROR KODE: ${errorMsg}`, true);
                console.log(xhr.responseText); // Tampil detail di inspect element
                stopProcess();
            }
        });
    }

    function stopProcess() {
        is_finish = true;
        if (xhrRequest) {
            xhrRequest.abort(); // Putuskan koneksi yang sedang berjalan
        }
        $('#btnRun').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        $('#btnStop').prop('disabled', true);
        if($('#statusBadge').text() !== "Status: SELESAI") {
            $('#statusBadge').removeClass('bg-warning text-dark').addClass('bg-danger text-white').text("Status: DIHENTIKAN");
        }
    }

    // Tombol Mulai
    $('#btnRun').click(function() {
        is_finish = false;
        $('#btnRun').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
        $('#btnStop').prop('disabled', false);
        $('#statusBadge').removeClass('bg-secondary bg-danger bg-success').addClass('bg-warning text-dark').text("Status: MEMPROSES MASSAL...");
        
        addLog("======================================");
        addLog("> Memulai proses massal (Kecepatan: 50 siswa / sesi)...");
        
        runProcessChunk();
    });

    // Tombol Stop
    $('#btnStop').click(function() {
        stopProcess();
        addLog("> PROSES DIHENTIKAN OLEH PENGGUNA.", true);
    });
});
</script>
@endpush