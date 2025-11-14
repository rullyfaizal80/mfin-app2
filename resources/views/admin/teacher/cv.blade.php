@extends('layouts.app')

@section('title', 'Edit CV Pendidik | MFIN')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Data Pendidik: <strong class="text-primary">{{ $teacher->fullname }}</strong></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Pendidik
        </a>
    </div>
</div>

{{-- Navigasi Tab --}}
<ul class="nav nav-tabs" id="teacherTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('teacher.edit', ['id' => $teacher->user_id]) }}">
            Personal Detail
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="cv-tab" data-bs-toggle="tab" data-bs-target="#cv" type="button" role="tab" aria-controls="cv" aria-selected="true">
            Personal CV
        </button>
    </li>
</ul>

<div class="tab-content" id="teacherTabContent">
    <div class="tab-pane fade show active" id="cv" role="tabpanel" aria-labelledby="cv-tab">
        <div class="card">
            <div class="card-body pt-3">
                
                {{-- [BARU] Tempat untuk menampilkan notifikasi error --}}
                <div id="cv-alert-container"></div>

                <div class="row g-4">

                    {{-- 1. Riwayat Pendidikan --}}
                    <div class="col-md-6">
                        <h5 class="text-primary border-bottom pb-2">Riwayat Pendidikan</h5>
                        {{-- [PERBAIKAN] Menambahkan method="POST" --}}
                        <form id="form-education" class="ajax-form" action="{{ route('teacher.cv.education.store') }}" method="POST" data-table-target="#education-table-container">
                            @csrf
                            <input type="hidden" name="teacher_id" value="{{ $teacher->user_id }}">
                            <div class="mb-2">
                                <label class="form-label">Jenjang</label>
                                <input type="text" name="level" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nama Institusi</label>
                                <input type="text" name="institution" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Jurusan</label>
                                <input type="text" name="major" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control form-control-sm">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6"><label class="form-label">Tgl Mulai</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
                                <div class="col-6"><label class="form-label">Tgl Selesai</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tambah Pendidikan</button>
                        </form>
                        <hr>
                        <div id="education-table-container">
                            @include('admin.teacher._cv_education_table', ['education' => $education])
                        </div>
                    </div>

                    {{-- 2. Pengalaman Kerja --}}
                    <div class="col-md-6">
                        <h5 class="text-primary border-bottom pb-2">Pengalaman Kerja</h5>
                        {{-- [PERBAIKAN] Menambahkan method="POST" --}}
                        <form id="form-work" class="ajax-form" action="{{ route('teacher.cv.work.store') }}" method="POST" data-table-target="#work-table-container">
                            @csrf
                            <input type="hidden" name="teacher_id" value="{{ $teacher->user_id }}">
                            <div class="mb-2">
                                <label class="form-label">Nama Institusi</label>
                                <input type="text" name="institution" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Posisi</label>
                                <input type="text" name="position" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tanggung Jawab</label>
                                <textarea name="responsibility" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control form-control-sm">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6"><label class="form-label">Tgl Mulai</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
                                <div class="col-6"><label class="form-label">Tgl Selesai</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tambah Pekerjaan</button>
                        </form>
                        <hr>
                        <div id="work-table-container">
                            @include('admin.teacher._cv_work_table', ['work' => $work])
                        </div>
                    </div>
                    
                    {{-- 3. Pelatihan / Training --}}
                    <div class="col-md-6">
                        <h5 class="text-primary border-bottom pb-2">Pelatihan / Training</h5>
                        {{-- [PERBAIKAN] Menambahkan method="POST" --}}
                        <form id="form-training" class="ajax-form" action="{{ route('teacher.cv.training.store') }}" method="POST" data-table-target="#training-table-container">
                            @csrf
                            <input type="hidden" name="teacher_id" value="{{ $teacher->user_id }}">
                            <div class="mb-2">
                                <label class="form-label">Tahun (Pilih tgl apapun di tahun tsb)</label>
                                <input type="date" name="year" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul Pelatihan</label>
                                <input type="text" name="title" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Penyelenggara</label>
                                <input type="text" name="provider" class="form-control form-control-sm">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tambah Pelatihan</button>
                        </form>
                        <hr>
                        <div id="training-table-container">
                            @include('admin.teacher._cv_training_table', ['training' => $training])
                        </div>
                    </div>

                    {{-- 4. Organisasi --}}
                    <div class="col-md-6">
                        <h5 class="text-primary border-bottom pb-2">Organisasi</h5>
                        {{-- [PERBAIKAN] Menambahkan method="POST" --}}
                        <form id="form-organization" class="ajax-form" action="{{ route('teacher.cv.organization.store') }}" method="POST" data-table-target="#organization-table-container">
                            @csrf
                            <input type="hidden" name="teacher_id" value="{{ $teacher->user_id }}">
                            <div class="mb-2">
                                <label class="form-label">Tahun (Pilih tgl apapun di tahun tsb)</label>
                                <input type="date" name="year" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nama Organisasi</label>
                                <input type="text" name="organization_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Posisi</label>
                                <input type="text" name="position" class="form-control form-control-sm">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tambah Organisasi</button>
                        </form>
                        <hr>
                        <div id="organization-table-container">
                            @include('admin.teacher._cv_organization_table', ['organization' => $organization])
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- [PERBAIKAN] Script AJAX untuk menangani 4 form CV --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const alertContainer = document.getElementById('cv-alert-container');

        // Fungsi untuk menampilkan alert
        function showAlert(message, type = 'danger') {
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            // Scroll ke atas agar user melihat alert
            window.scrollTo(0, 0); 
        }

        // Fungsi untuk menangani 'submit' form AJAX
        async function handleFormSubmit(event) {
            event.preventDefault(); // Mencegah submit GET
            const form = event.target;
            const tableTargetId = form.dataset.tableTarget;
            const tableContainer = document.querySelector(tableTargetId);
            const formData = new FormData(form);
            
            // Bersihkan alert lama
            alertContainer.innerHTML = '';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json, text/html', // Terima JSON untuk error, HTML untuk sukses
                        'X-Requested-With': 'XMLHttpRequest' // [PERBAIKAN KUNCI]
                    }
                });

                // [PERBAIKAN] Cek jika response TIDAK OK
                if (!response.ok) {
                    if (response.status === 422) { // 422 = Error Validasi
                        const errorData = await response.json(); // Ambil error JSON
                        let errorMessages = '<strong>Data tidak valid:</strong><ul class="mb-0">';
                        for (const key in errorData.errors) {
                            errorMessages += `<li>${errorData.errors[key].join(', ')}</li>`;
                        }
                        errorMessages += '</ul>';
                        throw new Error(errorMessages); // Lempar error untuk ditangkap 'catch'
                    }
                    // Error server lain (500, 404, dll)
                    throw new Error('Gagal menyimpan data. Server merespon: ' + response.statusText);
                }

                // Jika SUKSES (response.ok = true)
                const html = await response.text();
                tableContainer.innerHTML = html; // Muat ulang tabel
                form.reset(); // Kosongkan form

            } catch (error) {
                console.error('Error submitting form:', error);
                showAlert(error.message, 'danger'); // Tampilkan error di alert
            }
        }

        // Fungsi untuk menangani 'klik' hapus
        async function handleDeleteClick(event) {
            const deleteButton = event.target.closest('.btn-delete-cv');
            if (!deleteButton) {
                return;
            }
            
            event.preventDefault(); // Mencegah link/form default

            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                return;
            }

            const form = deleteButton.closest('form');
            const tableContainer = document.querySelector(form.dataset.tableTarget);
            
            // Bersihkan alert lama
            alertContainer.innerHTML = '';

            try {
                const response = await fetch(form.action, {
                    method: 'POST', // Method spoofing (DELETE)
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest' // [PERBAIKAN KUNCI]
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error('Gagal menghapus data. Server merespon: ' + errorText);
                }

                const html = await response.text();
                tableContainer.innerHTML = html; // Muat ulang tabel
                showAlert('Data berhasil dihapus.', 'success'); // Beri notifikasi sukses

            } catch (error) {
                console.error('Error deleting item:', error);
                showAlert(error.message, 'danger');
            }
        }

        // Terapkan listener 'submit' ke semua form AJAX
        document.querySelectorAll('.ajax-form').forEach(form => {
            form.addEventListener('submit', handleFormSubmit);
        });

        // Terapkan listener 'click' di level body untuk menangani tombol hapus
        document.body.addEventListener('click', handleDeleteClick);
    });
</script>
@endpush