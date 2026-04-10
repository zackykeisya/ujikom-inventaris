<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris App - Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .card-stats {
            transition: transform 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Inventaris App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Login
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero text-center">
        <div class="container">
            <h1 class="display-4">Sistem Inventaris Barang</h1>
            <p class="lead">Kelola inventaris barang dengan mudah dan efisien</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <i class="fas fa-boxes fa-3x text-primary"></i>
                        <h3 class="mt-3">{{ $totalItems }}</h3>
                        <p>Total Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <i class="fas fa-tags fa-3x text-success"></i>
                        <h3 class="mt-3">{{ $totalCategories }}</h3>
                        <p>Total Categories</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-3x text-info"></i>
                        <h3 class="mt-3">{{ $totalItemsAvailable }}</h3>
                        <p>Items Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <h2 class="text-center mb-4">Items Terbaru</h2>
        <div class="row">
            @foreach($recentItems as $item)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $item->name }}</h5>
                        <p class="card-text">Category: {{ $item->category->name }}</p>
                        <p class="card-text">Available: {{ $item->available }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-text" id="loginModalText"> </div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('loginModal'));
        myModal.show();
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>