@extends('backend.include.layout')
@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="user"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.pages.create') }}">
                            <i class="me-1" data-feather="user-plus"></i>
                            Add Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Serial</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td>#{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold">{{ $page->name }}</div>
                            </td>
                            <td>
                                @if ($page->status === 'active')
                                    <span class="badge bg-success-soft text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $page->created_at ? $page->created_at->format('d M Y') : 'N/A' }}</td>
                            <td>
                                {{-- Edit Button --}}
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                    href="{{ route('admin.pages.edit', $page->id) }}" title="Edit page">
                                    <i data-feather="edit"></i>
                                </a>

                                {{-- Delete Form with SweetAlert --}}
                                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST"
                                    id="delete-form-{{ $page->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-datatable btn-icon btn-transparent-dark delete-page-btn"
                                        data-id="{{ $page->id }}" data-name="{{ $page->name }}" title="Delete page">
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
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/datatables/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-page-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const pageId = this.getAttribute('data-id');
                    const pageName = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you really want to delete "${pageName}"? This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${pageId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
