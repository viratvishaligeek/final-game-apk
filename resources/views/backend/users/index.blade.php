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
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.users.create') }}">
                            <i class="me-1" data-feather="user-plus"></i>
                            Add User
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
                        <th>Phone</th>
                        <th>City</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Serial</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>#{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->gender ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->city ?? 'N/A' }}</td>
                            <td><span class="badge bg-primary-soft text-primary">₹{{ number_format($user->balance) }}</span>
                            </td>
                            <td>
                                @if ($user->status === 'active')
                                    <span class="badge bg-success-soft text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">Blocked</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}</td>
                            <td>
                                {{-- Show View --}}
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                    href="{{ route('admin.users.show', $user->id) }}" title="View User">
                                    <i data-feather="eye"></i>
                                </a>

                                {{-- Edit --}}
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                    href="{{ route('admin.users.edit', $user->id) }}" title="Edit User">
                                    <i data-feather="edit"></i>
                                </a>

                                {{-- Block / Unblock Button --}}
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                    id="status-form-{{ $user->id }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if ($user->status === 'active')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-warning toggle-status-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                            data-action="block" title="Block User">
                                            <i data-feather="slash"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-success toggle-status-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                            data-action="unblock" title="Unblock User">
                                            <i data-feather="check-circle"></i>
                                        </button>
                                    @endif
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    id="delete-form-{{ $user->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-user-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}" title="Delete User">
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
            document.querySelectorAll('.toggle-status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');
                    const action = this.getAttribute('data-action');

                    Swal.fire({
                        title: `${action === 'block' ? 'Block' : 'Unblock'} User?`,
                        text: `Are you sure you want to ${action} "${userName}"?`,
                        icon: action === 'block' ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: action === 'block' ? '#e74a3b' : '#1cc88a',
                        cancelButtonColor: '#858796',
                        confirmButtonText: `Yes, ${action}!`,
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`status-form-${userId}`).submit();
                        }
                    });
                });
            });

            // Delete Swal
            document.querySelectorAll('.delete-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Delete User?',
                        text: `Do you really want to delete "${userName}"?`,
                        icon: 'error',
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
