@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Dashboard {{ auth()->user()->name }}</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card card-stats bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Items</h5>
                            <h2 class="mb-0">{{ $totalItems }}</h2>
                        </div>
                        <i class="fas fa-boxes fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stats bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Categories</h5>
                            <h2 class="mb-0">{{ $totalCategories }}</h2>
                        </div>
                        <i class="fas fa-tags fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stats bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="mb-0">{{ $totalUsers }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stats bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Active Lendings</h5>
                            <h2 class="mb-0">{{ $activeLendings }}</h2>
                        </div>
                        <i class="fas fa-hand-holding fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Statistik Peminjaman</h5>
                </div>
                <div class="card-body">
                    <canvas id="lendingChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.categories') }}" class="btn btn-primary">
                            <i class="fas fa-tags"></i> Kelola Categories
                        </a>
                        <a href="{{ route('admin.items') }}" class="btn btn-success">
                            <i class="fas fa-boxes"></i> Kelola Items
                        </a>
                        <a href="{{ route('admin.users', 'admin') }}" class="btn btn-info">
                            <i class="fas fa-users"></i> Kelola Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('lendingChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Lendings', 'Active Lendings', 'Completed'],
            datasets: [{
                label: 'Jumlah',
                data: [{{ $totalLendings }}, {{ $activeLendings }}, {{ $totalLendings - $activeLendings }}],
                backgroundColor: ['#007bff', '#ffc107', '#28a745'],
                borderWidth: 1
            }]
        }
    });
</script>
@endpush