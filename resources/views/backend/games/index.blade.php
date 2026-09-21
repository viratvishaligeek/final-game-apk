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
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.games.create') }}">
                            <i class="me-1" data-feather="user-plus"></i>
                            Add Game
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
                        <th>Result Time</th>
                        <th>Status</th>
                        <th>Sequence</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Serial</th>
                        <th>Name</th>
                        <th>Result Time</th>
                        <th>Status</th>
                        <th>Sequence</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($games as $game)
                        <tr>
                            <td>#{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold">{{ $game->name }}</div>
                                <div class=" text-muted">Play: {{ $game->play_start }} - {{ $game->play_end }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $game->result_time }}</span>
                            </td>
                            <td>
                                @if ($game->status === 'active')
                                    <span class="badge bg-success-soft text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-soft text-danger">Inactive</span>
                                @endif
                            </td>
                             <td>
                                <span class="fw-bold">#{{ $game->serial }}</span>
                            </td>
                            <td>{{ $game->created_at ? $game->created_at->format('d M Y') : 'N/A' }}</td>
                            <td>
                                {{-- Edit Button --}}
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                    href="{{ route('admin.games.edit', $game->id) }}" title="Edit Game">
                                    <i data-feather="edit"></i>
                                </a>

                                {{-- Delete Form with SweetAlert --}}
                                <form action="{{ route('admin.games.destroy', $game->id) }}" method="POST"
                                    id="delete-form-{{ $game->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-datatable btn-icon btn-transparent-dark delete-game-btn"
                                        data-id="{{ $game->id }}" data-name="{{ $game->name }}" title="Delete Game">
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
            const deleteButtons = document.querySelectorAll('.delete-game-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const gameId = this.getAttribute('data-id');
                    const gameName = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you really want to delete "${gameName}"? This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${gameId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
