@extends('layouts.app')

@section('title', 'Manage Lendings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h2>Manage Lendings</h2>
        </div>
        <div class="col-6 text-end">
            <a href="{{ route('staff.lendings.export') }}" class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#lendingModal" onclick="resetLendingForm()">
                <i class="fas fa-plus"></i> Add Lending
            </button>
        </div>
    </div>

    <!-- Debug Info -->
    @if($items->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Tidak ada item yang tersedia untuk dipinjam. Pastikan ada item dengan stock available > 0.
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Borrower Name</th>
                            <th>Item Name</th>
                            <th>Total</th>
                            <th>Lending Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lendings as $lending)
                        <tr>
                            <td>{{ $lending->id }}</td>
                            <td>{{ $lending->borrower_name }}</td>
                            <td>{{ $lending->item->name }}</td>
                            <td>{{ $lending->total }}</td>
                            <td>{{ $lending->lending_date->format('d M Y') }}</td>
                            <td>
                                {{ $lending->return_date ? $lending->return_date->format('d M Y') : '-' }}
                            </td>
                            <td>
                                @if($lending->return_date)
                                    <span class="badge bg-success">Returned</span>
                                @else
                                    <span class="badge bg-warning">Borrowed</span>
                                @endif
                            </td>
                            <td>
                                @if(!$lending->return_date)
                                    <button class="btn btn-sm btn-success" onclick="returnItem({{ $lending->id }})">
                                        <i class="fas fa-undo"></i> Return
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirm('{{ route('staff.lendings.destroy', $lending->id) }}', 'Peminjaman {{ $lending->borrower_name }} - {{ $lending->item->name }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data peminjaman</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lendingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding"></i> Add New Lending
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="lendingForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="borrower_name" class="form-label fw-bold">
                            <i class="fas fa-user"></i> Borrower Name
                        </label>
                        <input type="text" class="form-control" id="borrower_name" name="borrower_name" 
                               placeholder="Masukkan nama peminjam" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lending_date" class="form-label fw-bold">
                            <i class="fas fa-calendar"></i> Lending Date
                        </label>
                        <input type="date" class="form-control" id="lending_date" name="lending_date" 
                               value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-boxes"></i> Items
                        </label>
                        <div id="items_container">
                            <div class="item-row row mb-2">
                                <div class="col-md-5">
                                    <select class="form-control item-select" name="items[0][item_id]" required>
                                        <option value="">-- Pilih Item --</option>
                                        @foreach($items as $item)
                                        <option value="{{ $item->id }}" data-available="{{ $item->available }}">
                                            {{ $item->name }} (Available: {{ $item->available }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" class="form-control item-total" name="items[0][total]" 
                                           placeholder="Jumlah" min="1" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-item" style="display:none;">
                                        <i class="fas fa-times"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-info mt-2" id="addMoreItem">
                            <i class="fas fa-plus"></i> Tambah Item Lain
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Peminjaman
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
                    <i class="fas fa-trash"></i> Konfirmasi Hapus Peminjaman
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong id="deleteLendingName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian!</strong><br>
                    Menghapus peminjaman akan:
                    <ul class="mb-0 mt-2">
                        <li>Mengembalikan stock item jika belum dikembalikan</li>
                        <li>Menghapus history peminjaman</li>
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
                    <i class="fas fa-trash"></i> Ya, Hapus
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
    .item-row {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
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
    let itemIndex = 1;
    let itemsData = @json($items);

    function showNotification(message, type = 'success') {
        $('.notification-toast').remove();
        
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
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
        
        setTimeout(() => {
            if (notification.length) {
                notification.css('animation', 'slideOut 0.3s ease-out');
                setTimeout(() => notification.remove(), 300);
            }
        }, 3000);
    }

    function deleteConfirm(url, lendingName) {
        deleteUrl = url;
        $('#deleteLendingName').text(lendingName);
        $('#deleteModal').modal('show');
    }
    
    $('#confirmDeleteBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
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
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                let errorMessage = 'Terjadi kesalahan saat menghapus peminjaman';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Silakan coba lagi nanti.';
                }
                
                showNotification(errorMessage, 'error');
            }
        });
    });

    function resetLendingForm() {
        $('#lendingForm')[0].reset();
        $('#lending_date').val(new Date().toISOString().split('T')[0]);
        
        $('#items_container').html(`
            <div class="item-row row mb-2">
                <div class="col-md-5">
                    <select class="form-control item-select" name="items[0][item_id]" required>
                        <option value="">-- Pilih Item --</option>
                        ${generateItemOptions()}
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control item-total" name="items[0][total]" 
                           placeholder="Jumlah" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item" style="display:none;">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
            </div>
        `);
        
        itemIndex = 1;
        $('.item-select').trigger('change');
        $('.invalid-feedback').remove();
        $('.is-invalid').removeClass('is-invalid');
    }

    function generateItemOptions() {
        let options = '';
        @foreach($items as $item)
            options += `<option value="{{ $item->id }}" data-available="{{ $item->available }}">
                           {{ $item->name }} (Available: {{ $item->available }})
                       </option>`;
        @endforeach
        return options;
    }

    $('#addMoreItem').click(function() {
        const newRow = `
            <div class="item-row row mb-2">
                <div class="col-md-5">
                    <select class="form-control item-select" name="items[${itemIndex}][item_id]" required>
                        <option value="">-- Pilih Item --</option>
                        ${generateItemOptions()}
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-5">
                    <input type="number" class="form-control item-total" name="items[${itemIndex}][total]" 
                           placeholder="Jumlah" min="1" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        $('#items_container').append(newRow);
        itemIndex++;
        $('.remove-item').show();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        if ($('.item-row').length === 1) {
            $('.remove-item').hide();
        }
    });

    $(document).on('change', '.item-select', function() {
        const row = $(this).closest('.item-row');
        const selected = row.find('.item-select option:selected');
        const available = selected.data('available');
        const total = row.find('.item-total').val();
        
        if (total && available && parseInt(total) > available) {
            row.find('.item-total').addClass('is-invalid');
            row.find('.item-total').siblings('.invalid-feedback').text(`Total melebihi available (${available})`);
        } else {
            row.find('.item-total').removeClass('is-invalid');
            row.find('.item-total').siblings('.invalid-feedback').text('');
        }
    });

    $(document).on('input', '.item-total', function() {
        const row = $(this).closest('.item-row');
        const selected = row.find('.item-select option:selected');
        const available = selected.data('available');
        const total = $(this).val();
        
        if (total && available && parseInt(total) > available) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text(`Total melebihi available (${available})`);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        }
    });

    $('#lendingForm').on('submit', function(e) {
        e.preventDefault();
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        let hasError = false;
        let errorMessage = '';
        
        $('.item-row').each(function(index) {
            const selected = $(this).find('.item-select option:selected');
            const available = selected.data('available');
            const total = $(this).find('.item-total').val();
            const itemName = selected.text();
            
            if (!selected.val()) {
                hasError = true;
                errorMessage = 'Silakan pilih item';
                $(this).find('.item-select').addClass('is-invalid');
            } else if (!total || total <= 0) {
                hasError = true;
                errorMessage = 'Total harus diisi';
                $(this).find('.item-total').addClass('is-invalid');
            } else if (total && available && parseInt(total) > available) {
                hasError = true;
                errorMessage = `Item ${itemName} hanya tersedia ${available} buah`;
                $(this).find('.item-total').addClass('is-invalid');
            }
        });
        
        if (!$('#borrower_name').val()) {
            hasError = true;
            $('#borrower_name').addClass('is-invalid');
            $('#borrower_name').siblings('.invalid-feedback').text('Nama peminjam harus diisi');
        }
        
        if (!$('#lending_date').val()) {
            hasError = true;
            $('#lending_date').addClass('is-invalid');
            $('#lending_date').siblings('.invalid-feedback').text('Tanggal peminjaman harus diisi');
        }
        
        if (hasError) {
            showNotification(errorMessage || 'Mohon lengkapi semua data dengan benar', 'error');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("staff.lendings.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                showNotification(response.message);
                $('#lendingModal').modal('hide');
                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                submitBtn.html(originalText).prop('disabled', false);
                let errorMsg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
            }
        });
    });

    function returnItem(id) {
        Swal.fire({
            title: 'Konfirmasi Pengembalian',
            text: "Apakah barang sudah dikembalikan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Kembalikan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Sedang memproses pengembalian',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: `/staff/lendings/${id}/return`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.close();
                        showNotification(response.message);
                        setTimeout(() => location.reload(), 2000);
                    },
                    error: function(xhr) {
                        Swal.close();
                        showNotification(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    }
    
    $('#exportBtn').on('click', function(e) {
        e.preventDefault();
        const exportUrl = $(this).attr('href');
        
        showNotification('Sedang mengexport data peminjaman ke Excel...', 'info');
        window.location.href = exportUrl;
        
        setTimeout(() => {
            showNotification('Export Excel berhasil!', 'success');
        }, 2000);
    });
</script>
@endpush