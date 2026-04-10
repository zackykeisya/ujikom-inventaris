@extends('layouts.app')

@section('title', 'Manage Items')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h2>Manage Items</h2>
        </div>
        <div class="col-6 text-end">
            <a href="{{ route('admin.items.export') }}" class="btn btn-success">
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

@endsection

@push('scripts')
<script>
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
    
    $('#name, #category_id, #total, #new_broken').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });
</script>
@endpush