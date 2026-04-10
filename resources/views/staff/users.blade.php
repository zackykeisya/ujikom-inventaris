@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Profile</h4>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password (Optional)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Leave empty to keep current password</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("staff.users.update", auth()->user()->id) }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                showNotification(response.message);
                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let key in errors) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}`).siblings('.invalid-feedback').text(errors[key][0]);
                    }
                } else {
                    showNotification(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            }
        });
    });
</script>
@endpush