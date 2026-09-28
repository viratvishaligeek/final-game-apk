@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="shield"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.roles.create') }}">
                            <i class="me-1" data-feather="plus"></i>
                            Create Role
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card">
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Serial</th>
                            <th>Role Name</th>
                            <th>Assigned Permissions</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Serial</th>
                            <th>Role Name</th>
                            <th>Assigned Permissions</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>#{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-bold text-primary">{{ ucfirst($role->name) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-soft text-info fw-bold">
                                        {{ $role->permissions_count }} Permissions
                                    </span>
                                </td>
                                <td>{{ $role->created_at ? $role->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                        href="{{ route('admin.roles.edit', $role->id) }}" title="Edit Role & Permissions">
                                        <i data-feather="edit"></i>
                                    </a>

                                    @if ($role->name !== 'super-admin')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                            id="delete-form-{{ $role->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-role-btn"
                                                data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                                title="Delete Role">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    @endif
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
            document.querySelectorAll('.delete-role-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const roleId = this.getAttribute('data-id');
                    const roleName = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Delete Role?',
                        text: `Are you sure you want to delete role "${roleName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, Delete!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${roleId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
