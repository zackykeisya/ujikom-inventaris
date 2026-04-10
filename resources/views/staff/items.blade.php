@extends('layouts.app')

@section('title', 'Items List')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Items List</h2>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name }}</td>
                            <td>{{ $item->total }}</td>
                            <td>{{ $item->lending_total }}</td>
                            <td>{{ $item->broken }}</td>
                            <td>
                                <span class="badge bg-{{ $item->available > 0 ? 'success' : 'danger' }}">
                                    {{ $item->available }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection