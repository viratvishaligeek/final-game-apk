@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">

                <div class="row align-items-center justify-content-between pt-3">

                    <div class="col-auto mb-3">

                        <h1 class="page-header-title">

                            <div class="page-header-icon">
                                <i data-feather="arrow-up-right"></i>
                            </div>

                            {{ $pageName ?? 'Withdrawal Requests' }}

                        </h1>

                    </div>

                    <div class="col-auto mb-3">

                        <a href="{{ route('admin.wallet.request-add') }}" class="btn btn-sm btn-light text-primary">

                            <i class="me-1" data-feather="plus-circle"></i>
                            Add Money Requests

                        </a>

                    </div>

                </div>

            </div>
        </div>
    </header>
@endsection

@section('content')
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-2"></i>
            {{ session('error') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">

        <div class="card-header bg-white py-3">

            <div class="row align-items-center">

                <div class="col">

                    <h5 class="mb-1 fw-bold text-dark">
                        Withdrawal Requests
                    </h5>

                    <small class="text-muted">
                        Review and process user wallet withdrawal requests.
                    </small>

                </div>

                <div class="col-auto">

                    <span class="badge bg-danger-soft text-danger px-3 py-2">
                        {{ $requests->total() }} Total
                    </span>

                </div>

            </div>

        </div>

        <div class="card-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.wallet.request-withdraw') }}" class="row g-2 mb-4">

                <div class="col-md-3">

                    <select name="status" class="form-select">

                        <option value="">
                            All Status
                        </option>

                        <option value="pending" @selected(request('status') === 'pending')>
                            Pending
                        </option>

                        <option value="approved" @selected(request('status') === 'approved')>
                            Approved
                        </option>

                        <option value="rejected" @selected(request('status') === 'rejected')>
                            Rejected
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <button class="btn btn-danger">

                        <i data-feather="filter" class="me-1"></i>
                        Filter

                    </button>

                    <a href="{{ route('admin.wallet.request-withdraw') }}" class="btn btn-light">

                        Reset

                    </a>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Destination</th>
                            <th>Account / UPI</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th class="text-end">Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($requests as $request)
                            <tr>

                                {{-- Serial --}}
                                <td>
                                    #{{ $requests->firstItem() + $loop->index }}
                                </td>

                                {{-- User --}}
                                <td>

                                    <div class="fw-bold text-dark">
                                        {{ $request->user->name ?? 'N/A' }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $request->user->phone ?? 'N/A' }}
                                    </small>

                                </td>

                                {{-- Amount --}}
                                <td>

                                    <span class="fw-bold text-danger">
                                        -₹{{ number_format((float) $request->amount, 2) }}
                                    </span>

                                </td>

                                {{-- Destination --}}
                                <td>

                                    @if (($request->withdrawal_method ?? $request->payment_method) === 'bank')
                                        <span class="badge bg-primary-soft text-primary">
                                            <i data-feather="home" class="me-1"></i>
                                            Bank Account
                                        </span>
                                    @else
                                        <span class="badge bg-warning-soft text-warning">
                                            <i data-feather="smartphone" class="me-1"></i>
                                            UPI
                                        </span>
                                    @endif

                                </td>

                                {{-- Account / UPI --}}
                                <td>

                                    @if (($request->withdrawal_method ?? $request->payment_method) === 'bank')
                                        <div class="small fw-bold">
                                            {{ $request->account_name ?? ($request->holder_name ?? 'N/A') }}
                                        </div>

                                        <div class="small font-monospace">
                                            {{ $request->account_number ?? ($request->account_no ?? 'N/A') }}
                                        </div>

                                        <small class="text-muted font-monospace">
                                            IFSC:
                                            {{ $request->ifsc ?? 'N/A' }}
                                        </small>
                                    @else
                                        <div class="small fw-bold">
                                            {{ $request->upi_id ?? 'N/A' }}
                                        </div>
                                    @endif

                                </td>

                                {{-- Status --}}
                                <td>

                                    @if ($request->status === 'pending')
                                        <span class="badge bg-warning-soft text-warning">
                                            Pending
                                        </span>
                                    @elseif ($request->status === 'approved')
                                        <span class="badge bg-success-soft text-success">
                                            Approved
                                        </span>
                                    @elseif ($request->status === 'rejected')
                                        <span class="badge bg-danger-soft text-danger">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-soft text-secondary">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    @endif

                                </td>

                                {{-- Date --}}
                                <td>

                                    <div class="small">
                                        {{ $request->created_at?->format('d M Y') }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $request->created_at?->format('h:i A') }}
                                    </small>

                                </td>

                                {{-- Actions --}}
                                <td class="text-end">

                                    @if ($request->status === 'pending')
                                        {{-- Approve --}}
                                        <form action="{{ route('admin.wallet.request.approve', $request->id) }}"
                                            method="POST" class="d-inline approve-form">

                                            @csrf

                                            <button type="button" class="btn btn-sm btn-success approve-btn"
                                                data-name="{{ $request->user->name ?? 'User' }}"
                                                data-amount="{{ number_format((float) $request->amount, 2) }}">

                                                <i data-feather="check" class="me-1"></i>
                                                Approve

                                            </button>

                                        </form>

                                        {{-- Reject --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger reject-btn"
                                            data-id="{{ $request->id }}" data-name="{{ $request->user->name ?? 'User' }}"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal">

                                            <i data-feather="x" class="me-1"></i>
                                            Reject

                                        </button>
                                    @else
                                        <span class="text-muted small">
                                            Processed
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center py-5">

                                    <div class="text-muted">

                                        <i data-feather="inbox" style="width:48px;height:48px;"></i>

                                        <h6 class="mt-3 mb-1">
                                            No Withdrawal Requests
                                        </h6>

                                        <small>
                                            No wallet withdrawal requests found.
                                        </small>

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($requests->hasPages())
                <div class="mt-4">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form id="rejectForm" method="POST">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title text-danger">
                            Reject Withdrawal
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <p class="text-muted">

                            Rejecting withdrawal request for

                            <strong id="rejectUserName"></strong>.

                        </p>

                        <label class="form-label fw-bold">
                            Rejection Remark
                        </label>

                        <textarea name="admin_remark" class="form-control" rows="4" maxlength="1000" required
                            placeholder="Enter reason for rejection..."></textarea>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button type="submit" class="btn btn-danger">

                            <i data-feather="x" class="me-1"></i>
                            Reject Request

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            feather.replace();

            /*
             * Approve Request
             */
            document.querySelectorAll('.approve-btn').forEach(button => {

                button.addEventListener('click', function() {

                    const form = this.closest('form');

                    const name = this.dataset.name;
                    const amount = this.dataset.amount;

                    Swal.fire({

                        title: 'Approve Withdrawal?',

                        html: `
                        <div class="text-center">

                            <p class="mb-1">
                                User:
                                <strong>${name}</strong>
                            </p>

                            <p>
                                Amount:
                                <strong class="text-danger">
                                    ₹${amount}
                                </strong>
                            </p>

                            <small class="text-muted">
                                The withdrawal request will be marked as approved.
                            </small>

                        </div>
                    `,

                        icon: 'warning',

                        showCancelButton: true,

                        confirmButtonColor: '#1cc88a',

                        cancelButtonColor: '#858796',

                        confirmButtonText: 'Yes, Approve',

                        cancelButtonText: 'Cancel'

                    }).then((result) => {

                        if (result.isConfirmed) {
                            form.submit();
                        }

                    });

                });

            });

            /*
             * Reject Request
             */
            document.querySelectorAll('.reject-btn').forEach(button => {

                button.addEventListener('click', function() {

                    const id = this.dataset.id;

                    const name = this.dataset.name;

                    document.getElementById('rejectUserName')
                        .textContent = name;

                    document.getElementById('rejectForm').action =
                        `{{ url('/wallet/request/reject') }}/${id}`;

                });

            });

        });
    </script>
@endsection
