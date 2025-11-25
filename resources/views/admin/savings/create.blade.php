@extends('layouts.app')

@section('title', $page_title . ' | MFIN')

@push('styles')
    <style>
        #user-search-results {
            position: absolute; z-index: 1000; width: 100%;
            max-height: 200px; overflow-y: auto; background: #fff;
            border: 1px solid #ced4da; border-top: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: none;
        }
        .user-item { cursor: pointer; padding: 10px; border-bottom: 1px solid #eee; }
        .user-item:hover { background-color: #f8f9fa; color: #0d6efd; }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $page_title }}</h1> {{-- Judul dinamis: Setoran / Penarikan --}}
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('savings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card" style="max-width: 800px;">
        <div class="card-header @if($type=='credit') bg-success @else bg-danger @endif text-white">
            Form {{ $page_title }}
        </div>
        <div class="card-body">
            <form action="{{ route('savings.store') }}" method="POST">
                @csrf
                {{-- HIDDEN INPUT: Menentukan Setor/Tarik --}}
                <input type="hidden" name="type" value="{{ $type }}">

                {{-- 1. Cari Nasabah --}}
                <div class="mb-3 row position-relative">
                    <label class="col-sm-3 col-form-label fw-bold">Cari Nasabah</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input type="text" id="user_search" class="form-control" placeholder="Ketik Nama / NIS / NIK..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="btn_search"><i class="bi bi-search"></i></button>
                        </div>
                        <div id="user-search-results"></div>
                        
                        {{-- Hasil Terpilih --}}
                        <div id="selected_user_display" class="mt-2 p-2 bg-light border rounded d-none">
                            <strong class="text-success" id="selected_name"></strong>
                            <span class="badge bg-secondary ms-2" id="selected_type"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2 float-end" id="btn_clear_user">[Ganti]</button>
                        </div>
                        <input type="hidden" name="user_id" id="user_id_hidden" required>
                    </div>
                </div>

                {{-- 2. Jenis Tabungan (Kosong Awalnya) --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Jenis</label>
                    <div class="col-sm-9">
                        <select name="payitem_id" id="payitem_select" class="form-select" required disabled>
                            <option value="">-- Pilih User Terlebih Dahulu --</option>
                        </select>
                    </div>
                </div>

                {{-- 3. Tanggal & Nominal --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Tanggal</label>
                    <div class="col-sm-9">
                        <input type="date" name="tdate" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Nilai (Rp)</label>
                    <div class="col-sm-9">
                        <input type="number" name="tvalue" class="form-control form-control-lg fw-bold" placeholder="0" min="100" required>
                    </div>
                </div>

                {{-- 4. Referensi --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Referensi</label>
                    <div class="col-sm-9">
                        <input type="text" name="ref_no" class="form-control bg-light" value="{{ $ref_no }}" readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Catatan</label>
                    <div class="col-sm-9">
                        <textarea name="note" class="form-control" rows="2" placeholder="Keterangan tambahan..."></textarea>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn @if($type=='credit') btn-success @else btn-danger @endif">
                            <i class="bi bi-save"></i> Simpan Transaksi
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. SIMPAN DATA PAYITEM DARI PHP KE JS
            const allPayitems = @json($payitems); 

            const inputSearch = $('#user_search');
            const resultsBox = $('#user-search-results');
            const hiddenId = $('#user_id_hidden');
            const displayBox = $('#selected_user_display');
            const displayName = $('#selected_name');
            const displayType = $('#selected_type');
            const btnClear = $('#btn_clear_user');
            const payitemSelect = $('#payitem_select');

            // Fungsi Cari User
            function searchUser(term) {
                if(term.length < 2) { resultsBox.hide(); return; }
                resultsBox.html('<div class="p-2 text-center">Loading...</div>').show();
                
                $.ajax({
                    url: "{{ route('savings.ajax_user') }}",
                    data: { term: term },
                    success: function(html) {
                        resultsBox.html(html).show();
                    }
                });
            }

            // Event Ketik
            let timeout;
            inputSearch.on('keyup', function() {
                clearTimeout(timeout);
                let val = $(this).val();
                timeout = setTimeout(() => searchUser(val), 300);
            });

            // --- EVENT KLIK USER (LOGIKA FILTER DROPDOWN) ---
            $(document).on('click', '.user-item', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let type = $(this).data('type'); // student / teacher
                
                // 1. Set Tampilan User Terpilih
                hiddenId.val(id);
                displayName.text(name);
                displayType.text(type.toUpperCase());
                
                inputSearch.hide();
                resultsBox.hide();
                displayBox.removeClass('d-none');

                // 2. Buka & Kosongkan Dropdown
                payitemSelect.prop('disabled', false);
                payitemSelect.empty();

                // 3. Filter Payitem Sesuai Tipe User
                let found = false;
                
                allPayitems.forEach(function(item) {
                    let title = item.title.toUpperCase();
                    let shouldAdd = false;

                    if (type === 'teacher') {
                        // Jika GURU -> Hanya ambil yang judulnya mengandung "GURU"
                        if (title.includes('GURU')) {
                            shouldAdd = true;
                        }
                    } else {
                        // Jika SISWA -> Ambil "TABUNGAN" TAPI JANGAN yang ada kata "GURU"
                        if (title.includes('TABUNGAN') && !title.includes('GURU')) {
                            shouldAdd = true;
                        }
                    }

                    if (shouldAdd) {
                        // Tambahkan Option ke Select
                        payitemSelect.append(new Option(item.title, item.id));
                        found = true;
                    }
                });

                // Jika tidak ada yang cocok (Jaga-jaga)
                if (!found) {
                    payitemSelect.append(new Option("-- Tidak ada jenis tabungan yang sesuai --", ""));
                }
            });

            // Event Reset / Ganti User
            btnClear.click(function() {
                hiddenId.val('');
                inputSearch.val('').show().focus();
                displayBox.addClass('d-none');
                
                // Reset Dropdown ke kondisi awal
                payitemSelect.empty();
                payitemSelect.append(new Option("-- Pilih Nasabah Terlebih Dahulu --", ""));
                payitemSelect.prop('disabled', true);
            });

            // Klik luar tutup hasil
            $(document).click(function(e) {
                if (!$(e.target).closest('#user_search, #user-search-results').length) {
                    resultsBox.hide();
                }
            });
        });
    </script>
@endpush