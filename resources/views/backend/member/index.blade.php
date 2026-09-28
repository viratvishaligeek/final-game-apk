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
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.member.create') }}">
                            <i class="me-1" data-feather="user-plus"></i>
                            Add Member
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Serial</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>#{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $member->name }}</div>
                                </td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @if ($member->roles->isNotEmpty())
                                        <span class="badge bg-primary-soft text-primary">
                                            {{ ucfirst($member->roles->first()->name) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-soft text-secondary">No Role</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($member->status === 'active')
                                        <span class="badge bg-success-soft text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $member->created_at ? $member->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                        href="{{ route('admin.member.edit', $member->id) }}" title="Edit User">
                                        <i data-feather="edit"></i>
                                    </a>
                                    <form action="{{ route('admin.member.destroy', $member->id) }}" method="POST"
                                        id="delete-form-{{ $member->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-user-btn"
                                            data-id="{{ $member->id }}" data-name="{{ $member->name }}"
                                            title="Delete User">
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
            document.querySelectorAll('.delete-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Delete User?',
                        text: `Do you really want to delete "${userName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, Delete!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${userId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
