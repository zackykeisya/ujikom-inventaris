<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventaris App')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
        }
        .main-content {
            padding: 20px;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3 text-white">
                    <h4>Inventaris App</h4>
                    <hr>
                    @if(auth()->user()->role == 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.categories') }}" class="nav-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                        <a href="{{ route('admin.items') }}" class="nav-link {{ request()->routeIs('admin.items') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i> Items
                        </a>
                        <a href="{{ route('admin.users', 'admin') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Users - Admin
                        </a>
                        <a href="{{ route('admin.users', 'staff') }}" class="nav-link">
                            <i class="fas fa-user-tie"></i> Users - Staff
                        </a>
                    @else
                        <a href="{{ route('staff.lendings') }}" class="nav-link {{ request()->routeIs('staff.lendings') ? 'active' : '' }}">
                            <i class="fas fa-hand-holding"></i> Lending
                        </a>
                        <a href="{{ route('staff.items') }}" class="nav-link {{ request()->routeIs('staff.items') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i> Items
                        </a>
                        <a href="{{ route('staff.users') }}" class="nav-link {{ request()->routeIs('staff.users') ? 'active' : '' }}">
                            <i class="fas fa-user-edit"></i> Users
                        </a>
                    @endif
                </div>
            </div>

            <div class="col-md-10 p-0">
                <nav class="navbar navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                        <span class="navbar-brand">Selamat datang, {{ auth()->user()->name }}</span>
                        <div class="ms-auto">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                <div class="main-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        function showNotification(message, type = 'success') {
            Swal.fire({
                title: type === 'success' ? 'Berhasil!' : 'Error!',
                text: message,
                icon: type,
                confirmButtonText: 'OK'
            });
        }
    </script>
    @stack('scripts')
</body>
</html>