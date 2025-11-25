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
        <h1 class="h2">{{ $page_title }}</h1>
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
                        
                        {{-- INPUT HIDDEN (Penting agar data user tidak hilang saat error) --}}
                        <input type="hidden" name="user_id" id="user_id_hidden" value="{{ old('user_id') }}" required>
                        {{-- Simpan Nama & Tipe sementara agar bisa di-restore JS --}}
                        <input type="hidden" name="user_name_temp" id="user_name_temp" value="{{ old('user_name_temp') }}">
                        <input type="hidden" name="user_type_temp" id="user_type_temp" value="{{ old('user_type_temp') }}">
                    </div>
                </div>

                {{-- 2. Jenis Tabungan --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Jenis</label>
                    <div class="col-sm-9">
                        {{-- Tambahkan ID agar bisa dipilih kembali oleh JS --}}
                        <select name="payitem_id" id="payitem_select" class="form-select" required disabled data-old="{{ old('payitem_id') }}">
                            <option value="">-- Pilih Nasabah Terlebih Dahulu --</option>
                        </select>
                    </div>
                </div>

                {{-- 3. Tanggal & Nominal --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Tanggal</label>
                    <div class="col-sm-9">
                        {{-- Gunakan old('tdate') --}}
                        <input type="date" name="tdate" class="form-control" value="{{ old('tdate', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Nilai (Rp)</label>
                    <div class="col-sm-9">
                        {{-- Gunakan old('tvalue') --}}
                        <input type="number" name="tvalue" class="form-control form-control-lg fw-bold" placeholder="0" min="100" value="{{ old('tvalue') }}" required>
                    </div>
                </div>

                {{-- 4. Referensi --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Referensi</label>
                    <div class="col-sm-9">
                        {{-- Ref No biasanya digenerate ulang jika gagal, tapi bisa juga di-old kan jika mau konsisten --}}
                        <input type="text" name="ref_no" class="form-control bg-light" value="{{ old('ref_no', $ref_no) }}" readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Catatan</label>
                    <div class="col-sm-9">
                        {{-- Gunakan old('note') di dalam textarea --}}
                        <textarea name="note" class="form-control" rows="2" placeholder="Keterangan tambahan...">{{ old('note') }}</textarea>
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
            // 1. Setup Variable
            const allPayitems = @json($payitems); 
            const inputSearch = $('#user_search');
            const resultsBox = $('#user-search-results');
            const hiddenId = $('#user_id_hidden');
            const hiddenName = $('#user_name_temp');
            const hiddenType = $('#user_type_temp');
            const displayBox = $('#selected_user_display');
            const displayName = $('#selected_name');
            const displayType = $('#selected_type');
            const btnClear = $('#btn_clear_user');
            const payitemSelect = $('#payitem_select');

            // ==========================================
            // FUNGSI UTAMA: MENGISI DROPDOWN BERDASARKAN TIPE
            // ==========================================
            function populateDropdown(userType, selectedValue = null) {
                payitemSelect.prop('disabled', false);
                payitemSelect.empty();

                let found = false;
                allPayitems.forEach(function(item) {
                    let title = item.title.toUpperCase();
                    let shouldAdd = false;

                    if (userType === 'teacher') {
                        if (title.includes('GURU')) shouldAdd = true;
                    } else {
                        if (title.includes('TABUNGAN') && !title.includes('GURU')) shouldAdd = true;
                    }

                    if (shouldAdd) {
                        let option = new Option(item.title, item.id);
                        payitemSelect.append(option);
                        found = true;
                    }
                });

                if (!found) {
                    payitemSelect.append(new Option("-- Tidak ada jenis tabungan yang sesuai --", ""));
                }

                // Jika ada nilai lama (dari error validasi), pilih kembali
                if (selectedValue) {
                    payitemSelect.val(selectedValue);
                }
            }

            // ==========================================
            // LOGIKA RESTORE STATE (JIKA ADA ERROR)
            // ==========================================
            // Cek apakah ada old data user_id
            if (hiddenId.val()) {
                let oldNameVal = hiddenName.val();
                let oldTypeVal = hiddenType.val();
                let oldPayitemVal = payitemSelect.data('old'); // Dari atribut data-old

                // Tampilkan UI Nasabah Terpilih
                displayName.text(oldNameVal);
                displayType.text(oldTypeVal.toUpperCase());
                inputSearch.hide();
                displayBox.removeClass('d-none');

                // Jalankan logika dropdown
                populateDropdown(oldTypeVal, oldPayitemVal);
            }

            // ==========================================
            // LOGIKA PENCARIAN USER
            // ==========================================
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

            let timeout;
            inputSearch.on('keyup', function() {
                clearTimeout(timeout);
                let val = $(this).val();
                timeout = setTimeout(() => searchUser(val), 300);
            });

            // Klik User Hasil Pencarian
            $(document).on('click', '.user-item', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let type = $(this).data('type'); // student / teacher
                
                // Isi ke Input Hidden
                hiddenId.val(id);
                hiddenName.val(name); // Simpan nama ke hidden input juga
                hiddenType.val(type); // Simpan tipe ke hidden input juga

                // Update Tampilan
                displayName.text(name);
                displayType.text(type.toUpperCase());
                inputSearch.hide();
                resultsBox.hide();
                displayBox.removeClass('d-none');

                // Reset dan Isi Dropdown
                populateDropdown(type);
            });

            // Tombol Ganti User
            btnClear.click(function() {
                hiddenId.val('');
                hiddenName.val('');
                hiddenType.val('');
                
                inputSearch.val('').show().focus();
                displayBox.addClass('d-none');
                
                payitemSelect.empty();
                payitemSelect.append(new Option("-- Pilih Nasabah Terlebih Dahulu --", ""));
                payitemSelect.prop('disabled', true);
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('#user_search, #user-search-results').length) {
                    resultsBox.hide();
                }
            });
        });
    </script>
@endpush