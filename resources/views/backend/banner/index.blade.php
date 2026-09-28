@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="image"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.banner.create') }}">
                            <i class="me-1" data-feather="plus"></i>
                            Add New Banner
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table id="datatablesSimple" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Image</th>
                            <th>Title/Name</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banners as $banner)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->name }}"
                                        class="img-thumbnail rounded" style="width: 80px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $banner->name }}</div>
                                </td>
                                <td>
                                    @if ($banner->status === 'active')
                                        <span class="badge bg-success-soft text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $banner->created_at ? $banner->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>
                                    <a class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                        href="{{ route('admin.banner.edit', $banner->id) }}" title="Edit Banner">
                                        <i data-feather="edit"></i>
                                    </a>

                                    <form action="{{ route('admin.banner.destroy', $banner->id) }}" method="POST"
                                        id="delete-form-{{ $banner->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark delete-banner-btn"
                                            data-id="{{ $banner->id }}" data-name="{{ $banner->name }}"
                                            title="Delete Banner">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/datatables/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-banner-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const bannerId = this.getAttribute('data-id');
                    const bannerName = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you really want to delete "${bannerName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${bannerId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
