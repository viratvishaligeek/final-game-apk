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
                                        <i data-feather="edit" class="text-primary"></i>
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
                                                <i data-feather="trash-2" class="text-danger"></i>
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

            document.addEventListener('click', function(event) {
                const button = event.target.closest('.delete-role-btn');

                if (!button) {
                    return;
                }

                event.preventDefault();

                const roleId = button.dataset.id;
                const roleName = button.dataset.name;
                const deleteForm = document.getElementById('delete-form-' + roleId);

                if (!deleteForm) {
                    console.error('Delete form not found for role:', roleId);
                    return;
                }

                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 failed to load. Please refresh the page.');
                    return;
                }

                Swal.fire({
                    title: 'Delete Role?',
                    text: 'Are you sure you want to delete role "' + roleName + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            });

        });
    </script>
@endsection
