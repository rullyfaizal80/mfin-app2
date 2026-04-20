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
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <h5 class="card-title mb-0"><i class="bi bi-terminal"></i> Log Aktivitas</h5>
                <span class="badge bg-primary" id="counter">0 Siswa Diproses</span>
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
    let timer;
    let is_finish = false;
    let counter = 0;
    const period_id = "{{ $period_id }}";
    
    // Fungsi untuk menambah teks ke layar hitam (Console)
    function addLog(message, isError = false) {
        const color = isError ? "text-danger" : "text-success";
        $('#consoleLog').append(`<span class="${color}">${message}</span><br>`);
        // Auto scroll ke bawah
        $('#consoleLog').scrollTop($('#consoleLog')[0].scrollHeight);
    }

    function runProcess() {
        if (is_finish) {
            stopProcess();
            addLog("> PROSES SELESAI / DIHENTIKAN.", true);
            return;
        }

        // 1. AJAX AMBIL 1 SISWA
        $.ajax({
            url: "{{ url('fincom/batch/get-student') }}/" + period_id,
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            dataType: "json",
            success: function(resStudent) {
                if (resStudent.status === 'empty') {
                    is_finish = true;
                    $('#statusBadge').removeClass('bg-warning').addClass('bg-success').text("Status: SELESAI");
                    addLog("> SELURUH SISWA TELAH SELESAI DIPROSES!", false);
                    stopProcess();
                } else if (resStudent.status === 'ok') {
                    let user_id = resStudent.user_id;
                    let fullname = resStudent.fullname;
                    addLog(`> Memproses SPP: [${user_id}] ${fullname}...`);

                    // 2. AJAX PROSES TAGIHAN SISWA TERSEBUT
                    $.ajax({
                        url: "{{ url('fincom/batch/process-tuition') }}/" + period_id + "/" + user_id,
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        dataType: "json",
                        success: function(resProcess) {
                            counter++;
                            $('#counter').text(counter + " Siswa Diproses");
                            addLog(`   -- Berhasil!`);
                            
                            // Lanjut rekursif ke siswa berikutnya
                            timer = setTimeout(runProcess, 200); 
                        },
                        error: function(xhr) {
                            addLog(`   -- ERROR: Gagal memproses tagihan. Cek koneksi/server.`, true);
                            is_finish = true;
                            stopProcess();
                        }
                    });
                }
            },
            error: function(xhr) {
                            // TAMPILKAN ERROR ASLI DARI LARAVEL
                            let errorMsg = "Koneksi terputus.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                errorMsg = xhr.responseText.substring(0, 100) + "..."; // Ambil sedikit teksnya
                            }
                            
                            addLog(`   -- ERROR DATABASE: ${errorMsg}`, true);
                            is_finish = true;
                            stopProcess();
                        }
        });
    }

    function stopProcess() {
        clearTimeout(timer);
        is_finish = true;
        $('#btnRun').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        $('#btnStop').prop('disabled', true);
        if($('#statusBadge').text() !== "Status: SELESAI") {
            $('#statusBadge').removeClass('bg-warning').addClass('bg-danger').text("Status: DIHENTIKAN");
        }
    }

    // Tombol Mulai
    $('#btnRun').click(function() {
        is_finish = false;
        $('#btnRun').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
        $('#btnStop').prop('disabled', false);
        $('#statusBadge').removeClass('bg-secondary bg-danger bg-success').addClass('bg-warning text-dark').text("Status: SEDANG BERJALAN...");
        addLog("======================================");
        addLog("> Memulai proses penarikan data...");
        
        runProcess();
    });

    // Tombol Stop
    $('#btnStop').click(function() {
        stopProcess();
        addLog("> PROSES DIHENTIKAN OLEH PENGGUNA.", true);
    });
});
</script>
@endpush