@extends('layouts.app')

@section('title', 'Manage Items')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h2>Manage Items</h2>
        </div>
        <div class="col-6 text-end">
            <a href="{{ route('admin.items.export') }}" class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="resetItemForm()">
                <i class="fas fa-plus"></i> Add Item
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
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Total</th>
                            <th>Lending</th>
                            <th>Broken</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name ?? 'No Category' }}</td>
                            <td>{{ $item->total }}</td>
                            <td>{{ $item->lending_total }}</td>
                            <td>{{ $item->broken }}</td>
                            <td>
                                <span class="badge bg-{{ $item->available > 0 ? 'success' : 'danger' }}">
                                    {{ $item->available }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editItem({{ $item }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirm('{{ route('admin.items.destroy', $item->id) }}', '{{ $item->name }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data items</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Item -->
<div class="modal fade" id="itemModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="itemModalTitle">
                    <i class="fas fa-box"></i> Add Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="itemForm">
                @csrf
                <input type="hidden" id="item_id" name="item_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-box"></i> Item Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Masukkan nama item..." required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-bold">
                            <i class="fas fa-tags"></i> Category
                        </label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">-- Pilih Category --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }} 
                                @if($category->division_pj)
                                    (PJ: {{ $category->division_pj }})
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Pilih kategori untuk item ini</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total" class="form-label fw-bold">
                            <i class="fas fa-calculator"></i> Total Stock
                        </label>
                        <input type="number" class="form-control" id="total" name="total" 
                               placeholder="Jumlah stock" min="1" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="broken_field" style="display:none;">
                        <label for="new_broken" class="form-label fw-bold">
                            <i class="fas fa-tools"></i> New Broke Item
                        </label>
                        <input type="number" class="form-control" id="new_broken" name="new_broken" 
                               placeholder="Jumlah item rusak baru" min="0">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Current broken: <span id="current_broken">0</span></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Item
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
                    <i class="fas fa-trash"></i> Konfirmasi Hapus Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus item <strong id="deleteItemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian!</strong><br>
                    Menghapus item akan menghapus semua data terkait:
                    <ul class="mb-0 mt-2">
                        <li>History peminjaman item ini</li>
                        <li>Data kerusakan item</li>
                        <li>Semua transaksi yang berkaitan</li>
                    </ul>
                </div>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle"></i> 
                    <small>Tindakan ini tidak dapat dibatalkan!</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Ya, Hapus Item
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
    /* Toast notification styles */
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            <div class="notification-toast alert shadow-lg">
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
    
    function resetItemForm() {
        $('#itemForm')[0].reset();
        $('#item_id').val('');
        $('#itemModalTitle').html('<i class="fas fa-box"></i> Add Item');
        $('#broken_field').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#category_id').val('');
    }

    function editItem(item) {
        resetItemForm();
        $('#item_id').val(item.id);
        $('#name').val(item.name);
        $('#category_id').val(item.category_id);
        $('#total').val(item.total);
        $('#current_broken').text(item.broken);
        $('#broken_field').show();
        $('#itemModalTitle').html('<i class="fas fa-edit"></i> Edit Item');
        $('#itemModal').modal('show');
    }
    
    function deleteConfirm(url, itemName) {
        deleteUrl = url;
        $('#deleteItemName').text(itemName);
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
                let errorMessage = 'Terjadi kesalahan saat menghapus item';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Silakan coba lagi nanti.';
                }
                
                showNotification(errorMessage, 'error');
            }
        });
    });

    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const id = $('#item_id').val();
        const url = id ? `/admin/items/${id}` : '/admin/items';
        const method = id ? 'PUT' : 'POST';
        const formData = $(this).serialize();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                showNotification(response.message);
                $('#itemModal').modal('hide');
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
                    const firstError = Object.values(errors)[0][0];
                    showNotification(firstError, 'error');
                } else {
                    showNotification(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            }
        });
    });
    
    // Handle export with loading notification
    $('#exportBtn').on('click', function(e) {
        e.preventDefault();
        const exportUrl = $(this).attr('href');
        
        showNotification('Sedang mengexport data...', 'info');
        
        // Redirect to export URL
        window.location.href = exportUrl;
        
        // Show success after 2 seconds (assuming export works)
        setTimeout(() => {
            showNotification('Export Excel berhasil!', 'success');
        }, 2000);
    });
    
    $('#name, #category_id, #total, #new_broken').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });
</script>
@endpush