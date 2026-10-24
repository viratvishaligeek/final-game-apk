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
                            <td>
                                <span class="badge bg-primary-soft text-primary user-balance"
                                    data-user-id="{{ $user->id }}"
                                    data-balance="{{ number_format((float) $user->balance, 2, '.', '') }}">
                                    ₹{{ number_format((float) $user->balance, 2) }}
                                </span>
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
                                <button type="button"
                                    class="btn btn-success add-money-btn "
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-balance="{{ number_format((float) $user->balance, 2, '.', '') }}"
                                    data-action="{{ route('admin.wallet.credit', $user->id) }}"
                                    title="Add Money / Credit">
                                    <i data-feather="plus-circle"></i>
                                </button>

                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                    href="{{ route('admin.users.show', $user->id) }}" title="View User">
                                    <i data-feather="eye" class="text-success"></i>
                                </a>

                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-1"
                                    href="{{ route('admin.users.edit', $user->id) }}" title="Edit User">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                    id="status-form-{{ $user->id }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if ($user->status === 'active')
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-warning toggle-status-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                            data-action="block" title="Block User">
                                            <i data-feather="slash" class="text-warning"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                            class="btn btn-datatable btn-icon btn-transparent-dark text-success toggle-status-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                            data-action="unblock" title="Unblock User">
                                            <i data-feather="check-circle" class="text-info"></i>
                                        </button>
                                    @endif
                                </form>

                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    id="delete-form-{{ $user->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="btn btn-datatable btn-icon btn-transparent-dark text-danger delete-user-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}" title="Delete User">
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

    {{-- Admin credit modal: remains on the User List page. --}}
    <div class="modal fade" id="addMoneyModal" tabindex="-1" aria-labelledby="addMoneyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addMoneyForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMoneyModalLabel">Add Money to User</h5>
                        <button type="button" class="btn-close" id="closeAddMoneyModal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="addMoneyError" class="alert alert-danger d-none" role="alert"></div>
                        <div id="addMoneySuccess" class="alert alert-success d-none" role="alert"></div>

                        <div class="rounded border bg-light p-3 mb-3">
                            <div class="small text-muted">Selected User</div>
                            <div class="fw-bold" id="creditUserName">—</div>
                            <div class="small text-muted">User ID: <span id="creditUserId">—</span></div>
                            <div class="mt-2">Current Balance: <strong id="creditCurrentBalance">₹0.00</strong></div>
                        </div>

                        <input type="hidden" id="creditActionUrl" value="">
                        <input type="hidden" name="idempotency_key" id="creditIdempotencyKey" value="">

                        <div class="mb-3">
                            <label for="creditAmount" class="form-label fw-bold">Amount (₹)</label>
                            <input type="number" class="form-control" id="creditAmount" name="amount"
                                min="0.01" max="1000000" step="0.01" inputmode="decimal" required>
                            <div class="form-text">Enter a positive amount, up to two decimal places.</div>
                        </div>

                        <div class="mb-2">
                            <label for="creditRemark" class="form-label fw-bold">Remark / Reason (optional)</label>
                            <textarea class="form-control" id="creditRemark" name="remark" rows="2" maxlength="255"
                                placeholder="Reason for admin credit"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" id="cancelAddMoneyModal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="submitCreditButton">
                            <i data-feather="plus-circle" class="me-1"></i> Add Money
                        </button>
                    </div>
                </form>
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
            const creditModalElement = document.getElementById('addMoneyModal');
            const creditModal = bootstrap.Modal.getOrCreateInstance(creditModalElement);
            const creditForm = document.getElementById('addMoneyForm');
            const creditAmount = document.getElementById('creditAmount');
            const creditRemark = document.getElementById('creditRemark');
            const creditActionUrl = document.getElementById('creditActionUrl');
            const creditIdempotencyKey = document.getElementById('creditIdempotencyKey');
            const creditError = document.getElementById('addMoneyError');
            const creditSuccess = document.getElementById('addMoneySuccess');
            const creditSubmitButton = document.getElementById('submitCreditButton');
            const creditCancelButton = document.getElementById('cancelAddMoneyModal');
            const creditCloseButton = document.getElementById('closeAddMoneyModal');

            const createRequestKey = () => {
                if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                    return window.crypto.randomUUID();
                }
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    const r = Math.random() * 16 | 0;
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                });
            };

            const showCreditError = (message) => {
                creditError.textContent = message;
                creditError.classList.remove('d-none');
                creditSuccess.classList.add('d-none');
            };

            document.addEventListener('click', function(event) {
                const creditButton = event.target.closest('.add-money-btn');
                if (creditButton) {
                    event.preventDefault();
                    document.getElementById('creditUserName').textContent = creditButton.dataset.userName || '—';
                    document.getElementById('creditUserId').textContent = creditButton.dataset.userId || '—';
                    const balance = Number(creditButton.dataset.balance || 0);
                    document.getElementById('creditCurrentBalance').textContent = '₹' + balance.toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    creditActionUrl.value = creditButton.dataset.action;
                    creditIdempotencyKey.value = createRequestKey();
                    creditAmount.value = '';
                    creditRemark.value = '';
                    creditError.classList.add('d-none');
                    creditSuccess.classList.add('d-none');
                    creditSubmitButton.disabled = false;
                    creditCancelButton.disabled = false;
                    creditCloseButton.disabled = false;
                    creditSubmitButton.innerHTML = '<i data-feather="plus-circle" class="me-1"></i> Add Money';
                    if (window.feather) feather.replace();
                    creditModal.show();
                    return;
                }

                // Block / Unblock User
                const statusButton = event.target.closest('.toggle-status-btn');

                if (statusButton) {
                    event.preventDefault();

                    const userId = statusButton.dataset.id;
                    const userName = statusButton.dataset.name;
                    const action = statusButton.dataset.action;
                    const statusForm = document.getElementById('status-form-' + userId);

                    if (!statusForm) {
                        console.error('Status form not found for user:', userId);
                        return;
                    }

                    if (typeof Swal === 'undefined') {
                        alert('SweetAlert2 failed to load. Please refresh the page.');
                        return;
                    }

                    Swal.fire({
                        title: (action === 'block' ? 'Block' : 'Unblock') + ' User?',
                        text: 'Are you sure you want to ' + action + ' "' + userName + '"?',
                        icon: action === 'block' ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: action === 'block' ? '#e74a3b' : '#1cc88a',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Yes, ' + action + '!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            statusForm.submit();
                        }
                    });

                    return;
                }

                // Delete User
                const deleteButton = event.target.closest('.delete-user-btn');

                if (deleteButton) {
                    event.preventDefault();

                    const userId = deleteButton.dataset.id;
                    const userName = deleteButton.dataset.name;
                    const deleteForm = document.getElementById('delete-form-' + userId);

                    if (!deleteForm) {
                        console.error('Delete form not found for user:', userId);
                        return;
                    }

                    if (typeof Swal === 'undefined') {
                        alert('SweetAlert2 failed to load. Please refresh the page.');
                        return;
                    }

                    Swal.fire({
                        title: 'Delete User?',
                        text: 'Do you really want to delete "' + userName + '"?',
                        icon: 'error',
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
                }

            });

            creditForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                creditError.classList.add('d-none');
                creditSuccess.classList.add('d-none');

                const amount = creditAmount.value.trim();
                if (!amount || !Number.isFinite(Number(amount)) || Number(amount) <= 0 || !/^\d+(?:\.\d{1,2})?$/.test(amount)) {
                    showCreditError('Enter a valid positive amount with no more than two decimal places.');
                    creditAmount.focus();
                    return;
                }

                creditSubmitButton.disabled = true;
                creditCancelButton.disabled = true;
                creditCloseButton.disabled = true;
                creditSubmitButton.textContent = 'Processing…';

                try {
                    const response = await fetch(creditActionUrl.value, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': creditForm.querySelector('input[name="_token"]').value
                        },
                        body: new FormData(creditForm)
                    });
                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        let message = result.message || 'Unable to credit wallet. Please try again.';
                        if (result.errors) {
                            const firstError = Object.values(result.errors).flat()[0];
                            if (firstError) message = firstError;
                        }
                        showCreditError(message);
                        creditSubmitButton.disabled = false;
                        creditCancelButton.disabled = false;
                        creditCloseButton.disabled = false;
                        creditSubmitButton.innerHTML = '<i data-feather="plus-circle" class="me-1"></i> Retry Add Money';
                        if (window.feather) feather.replace();
                        return;
                    }

                    const updatedBalance = Number(result.data.balance || 0);
                    const rowBalance = document.querySelector('.user-balance[data-user-id="' + result.data.user_id + '"]');
                    if (rowBalance) {
                        rowBalance.dataset.balance = updatedBalance.toFixed(2);
                        rowBalance.textContent = '₹' + updatedBalance.toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }

                    creditSuccess.textContent = result.message || 'Wallet credited successfully.';
                    creditSuccess.classList.remove('d-none');
                    creditError.classList.add('d-none');
                    creditModal.hide();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Credit successful',
                            text: result.message || 'Wallet credited successfully.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    // Reuse the same idempotency key when retrying an uncertain
                    // network response, so a committed credit cannot be repeated.
                    showCreditError('Network error. Please retry without closing this modal.');
                    creditSubmitButton.disabled = false;
                    creditSubmitButton.innerHTML = '<i data-feather="plus-circle" class="me-1"></i> Retry Add Money';
                } finally {
                    if (window.feather) feather.replace();
                }
            });

        });
    </script>
@endsection
