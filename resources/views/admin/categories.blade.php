@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h2>Manage Categories</h2>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Division PJ</th>
                            <th>Total Items</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $category->division_pj ?? 'Not Set' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $category->items_count }}</span>
                            </td>
                            <td>{{ $category->created_at->format('d M Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editCategory({{ $category }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirm('{{ route('admin.categories.destroy', $category->id) }}', '{{ $category->name }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Category -->
<div class="modal fade" id="categoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-folder-plus"></i> Add Category Forms
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="category_id" name="category_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please fill all input form with right value.
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-tag"></i> Name
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="name" 
                               name="name" 
                               placeholder="Masukkan nama kategori..."
                               required>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Contoh: Alat Dapur, Elektronik, Furniture, dll.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="division_pj" class="form-label fw-bold">
                            <i class="fas fa-user-tie"></i> Division PJ
                        </label>
                        <select class="form-select form-select-lg" id="division_pj" name="division_pj" required>
                            <option value="">Select Division PJ</option>
                            <option value="Sarpras">Sarpras</option>
                            <option value="Tata Usaha">Tata Usaha</option>
                            <option value="Tefa">Tefa</option>
                        </select>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Pilih penanggung jawab untuk kategori ini</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <small>Perhatian: Menghapus kategori juga akan menghapus semua item yang terkait dengan kategori ini!</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .alert-info {
        background-color: #e7f3ff;
        border-left: 4px solid #2196f3;
        border-radius: 8px;
    }
    /* Toast notification styles */
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let deleteUrl = '';
    
    function showNotification(message, type = 'success') {
        // Hapus notifikasi yang sudah ada
        $('.notification-toast').remove();
        
        // Tentukan warna berdasarkan tipe
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        // Buat elemen notifikasi
        const notification = $(`
            <div class="notification-toast alert alert-${type === 'success' ? 'success' : 'danger'} shadow-lg">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas ${icon} fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>${type === 'success' ? 'Berhasil!' : 'Error!'}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close" onclick="$(this).closest('.notification-toast').remove()"></button>
                </div>
            </div>
        `).css({
            'background-color': bgColor,
            'color': 'white',
            'border': 'none',
            'border-radius': '8px'
        });
        
        $('body').append(notification);
        
        // Auto hide setelah 3 detik
        setTimeout(() => {
            if (notification.length) {
                notification.css('animation', 'slideOut 0.3s ease-out');
                setTimeout(() => notification.remove(), 300);
            }
        }, 3000);
    }
    
    function resetForm() {
        $('#categoryForm')[0].reset();
        $('#category_id').val('');
        $('#modalTitle').html('<i class="fas fa-folder-plus"></i> Add Category Forms');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
    
    function editCategory(category) {
        resetForm();
        $('#category_id').val(category.id);
        $('#name').val(category.name);
        $('#division_pj').val(category.division_pj);
        $('#modalTitle').html('<i class="fas fa-edit"></i> Edit Category Forms');
        $('#categoryModal').modal('show');
    }
    
    function deleteConfirm(url, categoryName) {
        deleteUrl = url;
        $('#deleteCategoryName').text(categoryName);
        $('#deleteModal').modal('show');
    }
    
    // Handle delete confirmation
    $('#confirmDeleteBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        // Show loading state
        btn.html('<i class="fas fa-spinner fa-spin"></i> Menghapus...').prop('disabled', true);
        
        $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification(response.message, 'success');
                $('#deleteModal').modal('hide');
                // Reload after 1.5 seconds
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                let errorMessage = 'Terjadi kesalahan saat menghapus kategori';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Silakan coba lagi nanti.';
                }
                
                showNotification(errorMessage, 'error');
            }
        });
    });
    
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        
        // Reset error states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const id = $('#category_id').val();
        const url = id ? `/admin/categories/${id}` : '/admin/categories';
        const method = id ? 'PUT' : 'POST';
        const formData = $(this).serialize();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                showNotification(response.message);
                $('#categoryModal').modal('hide');
                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                submitBtn.html(originalText).prop('disabled', false);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let key in errors) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}`).siblings('.invalid-feedback').text(errors[key][0]);
                    }
                    // Show first error in notification
                    const firstError = Object.values(errors)[0][0];
                    showNotification(firstError, 'error');
                } else {
                    showNotification(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            }
        });
    });
    
    // Real-time validation
    $('#name, #division_pj').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });
</script>
@endpush