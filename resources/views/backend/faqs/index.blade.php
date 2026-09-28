@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="help-circle"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.faqs.create') }}">
                            <i class="me-1" data-feather="plus"></i>
                            Add FAQ
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
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Serial</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr>
                                <td>#{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $faq->question }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ Str::limit($faq->answer, 80) }}</div>
                                </td>
                                <td>{{ $faq->created_at ? $faq->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                        href="{{ route('admin.faqs.edit', $faq->id) }}" title="Edit FAQ">
                                        <i data-feather="edit"></i>
                                    </a>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                        id="delete-form-{{ $faq->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-faq-btn"
                                            data-id="{{ $faq->id }}" title="Delete FAQ">
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/datatables/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-faq-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const faqId = this.getAttribute('data-id');

                    Swal.fire({
                        title: 'Delete FAQ?',
                        text: 'Are you sure you want to delete this FAQ?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, Delete!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`delete-form-${faqId}`).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
