@extends('backend.include.layout')
@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon">
                                <i data-feather="home"></i>
                            </div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a href="{{ route('admin.home-page.create') }}" class="btn btn-sm btn-primary">
                            <i class="me-1" data-feather="plus"></i>
                            Add Section
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <table id="datatablesSimple" class="table align-middle">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Serial</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @forelse ($homePages as $homePage)
                        <tr>
                            <td>
                                <span class="fw-bold">
                                    #{{ $loop->iteration }}</span>
                            </td>
                            <td>{{ $homePage->title }}</td>
                            <td>{{ $homePage->location }}</td>
                            <td>
                                <div class="small">
                                    <div>
                                        <strong>WhatsApp:</strong>
                                        {{ $homePage->whatsapp }}
                                    </div>
                                    <div>
                                        <strong>Phone:</strong>
                                        {{ $homePage->phone }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($homePage->status === 'active')
                                    <span class="badge bg-success-soft text-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td><span class="small text-muted">
                                    {{ $homePage->created_at?->format('d M Y') ?? 'N/A' }}
                                </span>
                            </td>
                            <td c="text-end">
                                <a href="{{ route('admin.home-page.edit', $homePage->id) }}"
                                    class="btn btn-datatable btn-icon btn-transparent-dark me-2" title="Edit">
                                    <i data-feather="edit"></i>
                                    <a>
                                        <form action="{{ route('admin.home-page.destroy', $homePage->id) }}" method="POST"
                                            class="d-inline" id="delete-form-{{ $homePage->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-datatable btn-icon btn-transparent-dark delete-home-page"
                                                data-id="{{ $homePage->id }}" data-title="{{ $homePage->title }}"
                                                title="Delete">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td c="7" class="text-center py-5">
                                <div class="mb-3">
                                    <i data-feather="inbox" style="width:45px;height:45px;" class="text-muted">
                                    </i>
                                    <div>
                                        <h5 class="mb-1">
                                            No home page sections found
                                            <h5>
                                                <p class="text-muted mb-3">
                                                    Create your first home page section.
                                                <p>
                                                    <a href="{{ route('admin.home-page.create') }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i data-feather="plus" class="me-1"></i>
                                                        Add Section
                                                    </a>
                            </td>
                        </tr>
                    @endforelse
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
            document.querySelectorAll('.delete-home-page').forEach(function(button) {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const title = this.dataset.title;
                    const form = document.getElementById('delete-form-' + id);
                    Swal.fire({
                        title: 'Delete Section?',
                        text: `Are you sure you want to delete "${title}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
