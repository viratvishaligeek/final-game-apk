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
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="live_status_filter">Live presence</label>
                    <select class="form-select" id="live_status_filter" name="live_status">
                        <option value="">All clients</option>
                        <option value="active" @selected(($liveStatus ?? '') === 'active')>Active clients</option>
                        <option value="inactive" @selected(($liveStatus ?? '') === 'inactive')>Inactive clients</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-primary" type="submit">Apply</button></div>
                <div class="col-auto"><a class="btn btn-light border" href="{{ route('admin.users.index') }}">Reset</a></div>
            </form>
        </div>
    </div>
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
                        <th>Live Presence</th>
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
                            <td data-live-status-cell="{{ $user->id }}">
                                <span class="badge {{ $user->live_status === 'active' ? 'bg-success' : 'bg-secondary' }}"
                                    data-live-status="{{ $user->live_status ?? 'inactive' }}">
                                    {{ ucfirst($user->live_status ?? 'inactive') }}
                                </span>
                                <small class="d-block text-muted" data-live-last-seen="{{ $user->id }}">
                                    {{ $user->live_last_seen_at?->timezone(config('app.timezone'))->format('d M, h:i A') ?? '—' }}
                                </small>
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
        $(function () {
            const $creditModalElement = $('#addMoneyModal');
            if (!$creditModalElement.length || !window.bootstrap) return;

            const creditModal = bootstrap.Modal.getOrCreateInstance($creditModalElement.get(0));
            const $creditForm = $('#addMoneyForm');
            const $creditAmount = $('#creditAmount');
            const $creditRemark = $('#creditRemark');
            const $creditActionUrl = $('#creditActionUrl');
            const $creditIdempotencyKey = $('#creditIdempotencyKey');
            const $creditError = $('#addMoneyError');
            const $creditSuccess = $('#addMoneySuccess');
            const $creditSubmitButton = $('#submitCreditButton');
            const $creditCancelButton = $('#cancelAddMoneyModal');
            const $creditCloseButton = $('#closeAddMoneyModal');

            const createRequestKey = () => {
                if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                    return window.crypto.randomUUID();
                }
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (character) {
                    const random = Math.random() * 16 | 0;
                    return (character === 'x' ? random : (random & 0x3 | 0x8)).toString(16);
                });
            };

            const showCreditError = message => {
                $creditError.text(message).removeClass('d-none');
                $creditSuccess.addClass('d-none');
            };

            $(document).on('click', '.add-money-btn', function (event) {
                event.preventDefault();
                const $button = $(this);
                const balance = Number($button.data('balance') || 0);

                $('#creditUserName').text($button.data('user-name') || '—');
                $('#creditUserId').text($button.data('user-id') || '—');
                $('#creditCurrentBalance').text('₹' + balance.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $creditActionUrl.val($button.data('action'));
                $creditIdempotencyKey.val(createRequestKey());
                $creditAmount.val('');
                $creditRemark.val('');
                $creditError.addClass('d-none');
                $creditSuccess.addClass('d-none');
                $creditSubmitButton.prop('disabled', false).html('<i data-feather="plus-circle" class="me-1"></i> Add Money');
                $creditCancelButton.add($creditCloseButton).prop('disabled', false);
                if (window.feather) feather.replace();
                creditModal.show();
            });

            $(document).on('click', '.toggle-status-btn', function (event) {
                event.preventDefault();
                const $button = $(this);
                const userId = $button.data('id');
                const userName = $button.data('name');
                const action = $button.data('action');
                const $statusForm = $('#status-form-' + userId);

                if (!$statusForm.length) {
                    console.error('Status form not found for user:', userId);
                    return;
                }
                if (!window.Swal || typeof window.Swal.fire !== 'function') {
                    window.alert('SweetAlert2 failed to load. Please refresh the page.');
                    return;
                }

                window.Swal.fire({
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
                }).then(result => {
                    if (result.isConfirmed) $statusForm.get(0).submit();
                });
            });

            $creditForm.on('submit', async function (event) {
                event.preventDefault();
                $creditError.add($creditSuccess).addClass('d-none');

                const amount = String($creditAmount.val() || '').trim();
                if (!amount || !Number.isFinite(Number(amount)) || Number(amount) <= 0 || !/^\d+(?:\.\d{1,2})?$/.test(amount)) {
                    showCreditError('Enter a valid positive amount with no more than two decimal places.');
                    $creditAmount.trigger('focus');
                    return;
                }

                $creditSubmitButton.prop('disabled', true).text('Processing…');
                $creditCancelButton.add($creditCloseButton).prop('disabled', true);

                try {
                    const result = await $.ajax({
                        url: $creditActionUrl.val(),
                        method: 'POST',
                        data: new FormData($creditForm.get(0)),
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $creditForm.find('input[name="_token"]').val()
                        }
                    });

                    if (!result.success) {
                        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
                        throw { responseJSON: result, message: validationMessage || result.message };
                    }

                    const updatedBalance = Number(result.data.balance || 0);
                    const $rowBalance = $('.user-balance[data-user-id="' + Number(result.data.user_id) + '"]');
                    if ($rowBalance.length) {
                        $rowBalance.attr('data-balance', updatedBalance.toFixed(2));
                        $rowBalance.text('₹' + updatedBalance.toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    }

                    $creditSuccess.text(result.message || 'Wallet credited successfully.').removeClass('d-none');
                    $creditError.addClass('d-none');
                    creditModal.hide();

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'success',
                            title: 'Credit successful',
                            text: result.message || 'Wallet credited successfully.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    const result = error.responseJSON || {};
                    const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
                    showCreditError(firstError || error.message || 'Network error. Please retry without closing this modal.');
                    $creditSubmitButton.prop('disabled', false).html('<i data-feather="plus-circle" class="me-1"></i> Retry Add Money');
                    $creditCancelButton.add($creditCloseButton).prop('disabled', false);
                } finally {
                    if (window.feather) feather.replace();
                }
            });
        });

        $(window).on('admin:user-presence', function (event) {
            const presence = event.originalEvent?.detail || event.detail || {};
            const userId = Number(presence.user_id);
            const $cell = $('[data-live-status-cell="' + userId + '"]');
            if (!$cell.length) return;

            const status = presence.live_status === 'active' ? 'active' : 'inactive';
            const activeFilter = @json($liveStatus ?? null);
            if (activeFilter && activeFilter !== status) {
                $cell.closest('tr').remove();
                return;
            }

            $cell.find('[data-live-status]')
                .attr('data-live-status', status)
                .text(status.charAt(0).toUpperCase() + status.slice(1))
                .attr('class', 'badge ' + (status === 'active' ? 'bg-success' : 'bg-secondary'));

            if (presence.last_seen_at) {
                const date = new Date(presence.last_seen_at);
                $cell.find('[data-live-last-seen]').text(
                    Number.isNaN(date.getTime()) ? presence.last_seen_at : date.toLocaleString('en-IN')
                );
            }
            showCreditError(message);
            setCreditBusy(false);
        } finally {
            if (window.feather) feather.replace();
        }
    });

    $(window).on('admin:user-presence.adminUsers', function (event) {
        const presence = event.originalEvent?.detail || event.detail || {};
        const $cell = $('[data-live-status-cell="' + Number(presence.user_id) + '"]');
        if (!$cell.length) return;
        const status = presence.live_status === 'active' ? 'active' : 'inactive';
        const activeFilter = @json($liveStatus ?? null);
        if (activeFilter && activeFilter !== status) {
            $cell.closest('tr').remove();
            return;
        }
        const $badge = $cell.find('[data-live-status]');
        $badge.attr('data-live-status', status)
            .text(status.charAt(0).toUpperCase() + status.slice(1))
            .attr('class', 'badge ' + (status === 'active' ? 'bg-success' : 'bg-secondary'));
        if (presence.last_seen_at) {
            const date = new Date(presence.last_seen_at);
            $cell.find('[data-live-last-seen]').text(Number.isNaN(date.getTime()) ? presence.last_seen_at : date.toLocaleString('en-IN'));
        }
    });
});
</script>
@endsection
