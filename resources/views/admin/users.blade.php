@extends('layouts.app')

@section('title', 'Manage Users - ' . ucfirst($role))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h2>Manage Users - {{ ucfirst($role) }}</h2>
        </div>
        <div class="col-6 text-end">
            <a href="{{ route('admin.users.export', $role) }}" class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
                <i class="fas fa-plus"></i> Add User
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'info' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>
                                {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editUser(@json($user))'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                @if($role == 'staff')
                                <button class="btn btn-sm btn-info" onclick="resetPassword({{ $user->id }})">
                                    <i class="fas fa-key"></i> Reset Password
                                </button>
                                @endif
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirm('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data user</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit User -->
<div class="modal fade" id="userModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userModalTitle">
                    <i class="fas fa-user-plus"></i> Add User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                @csrf
                <input type="hidden" id="user_id" name="user_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Password akan digenerate otomatis dari 4 karakter awal email + nomor acak
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-user"></i> Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Masukkan nama lengkap" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Masukkan email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label fw-bold">
                            <i class="fas fa-shield-alt"></i> Role
                        </label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3" id="password_field" style="display:none;">
                        <label for="new_password" class="form-label fw-bold">
                            <i class="fas fa-key"></i> New Password (Optional)
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="Masukkan password baru">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
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
                    <i class="fas fa-trash"></i> Konfirmasi Hapus User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian!</strong><br>
                    Menghapus user akan menghapus semua data terkait user ini.
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
                    <i class="fas fa-trash"></i> Ya, Hapus User
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
    
    function resetUserForm() {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#userModalTitle').html('<i class="fas fa-user-plus"></i> Add User');
        $('#password_field').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#role').val('');
    }

    function editUser(user) {
        resetUserForm();
        $('#user_id').val(user.id);
        $('#name').val(user.name);
        $('#email').val(user.email);
        $('#role').val(user.role);
        $('#password_field').show();
        $('#userModalTitle').html('<i class="fas fa-user-edit"></i> Edit User');
        $('#userModal').modal('show');
    }
    
    function deleteConfirm(url, userName) {
        deleteUrl = url;
        $('#deleteUserName').text(userName);
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
                let errorMessage = 'Terjadi kesalahan saat menghapus user';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Silakan coba lagi nanti.';
                }
                
                showNotification(errorMessage, 'error');
            }
        });
    });

    function resetPassword(userId) {
        Swal.fire({
            title: 'Reset Password?',
            text: "Password akan direset sesuai aturan default (4 karakter awal email + nomor acak)!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Sedang mereset password',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: `/admin/users/${userId}/reset-password`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Password Reset!',
                            html: `Password baru: <strong>${response.password}</strong><br><br>Simpan password ini dengan aman.`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        // Reset errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        const id = $('#user_id').val();
        const url = id ? `/admin/users/${id}` : '/admin/users';
        const method = id ? 'PUT' : 'POST';
        const formData = $(this).serialize();
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.password) {
                    Swal.fire({
                        title: 'User Created!',
                        html: `Password untuk <strong>${$('#email').val()}</strong> adalah:<br><br>
                               <code style="font-size: 20px; font-weight: bold; background: #f0f0f0; padding: 10px; border-radius: 5px;">${response.password}</code><br><br>
                               Simpan password ini dengan aman.`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showNotification(response.message);
                    $('#userModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                }
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
        
        showNotification('Sedang mengexport data user ke Excel...', 'info');
        
        // Redirect to export URL
        window.location.href = exportUrl;
        
        // Show success after 2 seconds (assuming export works)
        setTimeout(() => {
            showNotification('Export Excel berhasil!', 'success');
        }, 2000);
    });
    
    // Real-time validation
    $('#name, #email, #role, #new_password').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });
</script>
@endpush