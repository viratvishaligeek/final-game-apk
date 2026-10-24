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
                            <th>Platform</th>
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
                                <td><span class="badge bg-secondary">{{ strtoupper($faq->platform ?? 'both') }}</span></td>
                                <td>{{ $faq->created_at ? $faq->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                        href="{{ route('admin.faqs.edit', $faq->id) }}" title="Edit FAQ">
                                        <i data-feather="edit" class="text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                        id="delete-form-{{ $faq->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-faq-btn"
                                            data-id="{{ $faq->id }}" title="Delete FAQ">
                                            <i data-feather="trash-2" class="text-danger"></i>
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

            document.addEventListener('click', function(event) {
                const button = event.target.closest('.delete-faq-btn');

                if (!button) {
                    return;
                }

                event.preventDefault();

                const faqId = button.dataset.id;
                const deleteForm = document.getElementById('delete-form-' + faqId);

                if (!deleteForm) {
                    console.error('Delete form not found for FAQ:', faqId);
                    return;
                }

                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 failed to load. Please refresh the page.');
                    return;
                }

                Swal.fire({
                    title: 'Delete FAQ?',
                    text: 'Are you sure you want to delete this FAQ?',
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
