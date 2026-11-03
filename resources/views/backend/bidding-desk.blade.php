@extends('backend.include.layout')

@section('title', 'Bidding Desk')

@section('content')
    <div class="container-fluid py-3">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i>
                            Bidding Desk
                        </h4>
                        <small class="text-muted">
                            Number-wise betting overview
                        </small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <select id="gameSelect" class="form-select fw-bold" style="min-width:220px;">
                            <option value="">
                                Select Game
                            </option>
                            @foreach ($games as $game)
                                <option value="{{ $game->id }}">
                                    {{ $game->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="date" id="dateSelect" class="form-control fw-bold"
                            value="{{ now()->toDateString() }}">
                    </div>
                </div>
            </div>
        </div>

        <div id="loadingBox" class="text-center py-5 d-none">
            <div class="spinner-border text-warning"></div>
            <div class="mt-2 text-muted fw-semibold">
                Loading bidding data...
            </div>
        </div>

        <div id="emptyBox" class="alert alert-info border-0 shadow-sm rounded-4 d-none">
            Please select a game.
        </div>
        <div id="betSummary" class="row g-2 mb-3 d-none">
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Jodi · <span id="summary-jodi-count">0</span> bets</small><strong id="summary-jodi-amount">₹0</strong><span class="small text-muted">Empty: <span id="summary-jodi-empty">0</span> numbers · ₹<span id="summary-jodi-empty-amount">0</span></span></div></div>
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Haruf · <span id="summary-haruf-count">0</span> bets</small><strong id="summary-haruf-amount">₹0</strong><span class="small text-muted">Empty: <span id="summary-haruf-empty">0</span> numbers · ₹<span id="summary-haruf-empty-amount">0</span></span></div></div>
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Crossing · <span id="summary-crossing-count">0</span> bets</small><strong id="summary-crossing-amount">₹0</strong><span class="small text-muted">Empty: <span id="summary-crossing-empty">0</span> numbers · ₹<span id="summary-crossing-empty-amount">0</span></span></div></div>
        </div>
        <div class="d-flex flex-wrap gap-3 small text-muted mb-3" aria-label="Investment legend">
            <span><i class="d-inline-block rounded border me-1" style="width:10px;height:10px;background:#f8f9fa"></i>Unplayed</span>
            <span><i class="d-inline-block rounded border me-1" style="width:10px;height:10px;background:#f8fbff;border-color:#b6d4fe!important"></i>Lower investment</span>
            <span><i class="d-inline-block rounded border me-1" style="width:10px;height:10px;background:#fff5f5;border-color:#dc3545!important"></i>Higher investment</span>
        </div>
        <div id="jodiSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
            <div class="card-header bg-info text-white border-0 rounded-top-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-dark mb-0 fw-bold">
                            🎯 Jodi
                        </h4>
                        <small class="text-dark">
                            Jodi · 00–99
                        </small>
                    </div>
                    <span id="jodiTotal" class="badge bg-warning text-dark fs-6">
                        ₹0
                    </span>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="jodiGrid" class="number-grid"></div>
            </div>
        </div>
        <div id="crossingSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
            <div class="card-header bg-info text-white border-0 rounded-top-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h4 class="text-dark mb-0 fw-bold">🔀 Crossing</h4><small class="text-dark">Crossing · 00–99</small></div>
                    <span id="crossingTotal" class="badge bg-warning text-dark fs-6">₹0</span>
                </div>
            </div>
            <div class="card-body p-2"><div id="crossingGrid" class="number-grid"></div></div>
        </div>
        <div id="harufSection" class="card border-0 shadow-sm rounded-4 d-none">
            <div class="card-header bg-info text-white border-0 rounded-top-4 p-3">
                <h5 class="mb-0 fw-bold text-dark">
                    🎲 Haruf
                </h5>
                <small class="text-dark">
                    Ander and Bahar
                </small>
            </div>
            <div class="card-body p-3">
                <div class="mb-4">
                    <div class="section-title ander-title">
                        <span>🅰️ Ander</span>
                        <span id="anderTotal">
                            ₹0
                        </span>
                    </div>
                    <div id="anderGrid" class="haruf-grid"></div>
                </div>
                <div>
                    <div class="section-title bahar-title">
                        <span>🅱️ Bahar</span>
                        <span id="baharTotal">
                            ₹0
                        </span>
                    </div>
                    <div id="baharGrid" class="haruf-grid"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bidDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <div>
                        <h5 class="modal-title fw-bold">
                            Number:
                            <span id="modalNumber" class="text-warning">
                                --
                            </span>
                        </h5>
                        <small class="text-white-50">
                            Bidding details
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="summary-card">
                                <small>
                                    Total Bids
                                </small>
                                <strong id="modalTotalBids">
                                    0
                                </strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="summary-card">
                                <small>
                                    Users
                                </small>
                                <strong id="modalTotalUsers">
                                    0
                                </strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="summary-card amount-card">
                                <small>
                                    Total Amount
                                </small>
                                <strong id="modalTotalAmount">
                                    ₹0
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div id="modalUserBreakdown" class="d-grid gap-3 mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        Bid / Txn ID
                                    </th>
                                    <th>
                                        User
                                    </th>
                                    <th>
                                        Phone
                                    </th>
                                    <th>
                                        Type
                                    </th>
                                    <th>
                                        Number
                                    </th>
                                    <th>
                                        Amount
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                    <th>
                                        Order
                                    </th>
                                    <th>
                                        Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="modalBidTable">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" id="modalLoadMore" class="btn btn-outline-primary btn-sm d-none">Load more users</button>
                    </div>
                    <div id="modalEmpty" class="text-center text-muted py-4 d-none">
                        No bidding found.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .number-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 8px;
        }

        .haruf-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 8px;
        }

        .number-card {
            min-height: min-content;
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 14px;
            padding: 7px;
            cursor: pointer;
            transition: all .18s ease;
            position: relative;
        }

        .number-card:hover {
            transform: translateY(-3px);
            border-color: #ffc107;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .10);
        }

        .number-card.has-bet {
            border-color: #ffc107;
            background: linear-gradient(135deg,
                    #fffdf2,
                    #fff8d9);
        }
        .number-card.empty-number {
            border-style: dashed;
            background: #f8f9fa;
            color: #6c757d;
        }
        .number-card.low-investment {
            border-color: #b6d4fe;
            background: #f8fbff;
        }
        .number-card.high-investment {
            border-color: #dc3545;
            background: #fff5f5;
            box-shadow: inset 0 0 0 1px rgba(220, 53, 69, .08);
        }

        .number-value {
            font-size: 16px;
            font-weight: 800;
            color: #212529;
            text-align: center;
        }

        .number-amount {
            font-size: 12px;
            font-weight: 800;
            color: #198754;
            text-align: center;
            margin-top: 2px;
        }

        .number-meta {
            font-size: 10px;
            color: #6c757d;
            text-align: center;
            margin-top: 2px;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .ander-title {
            background: #eef4ff;
            color: #0d6efd;
        }

        .bahar-title {
            background: #e8fbff;
            color: #087990;
        }

        .summary-card {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 13px;
            text-align: center;
            border: 1px solid #eee;
        }

        .summary-card small {
            display: block;
            color: #6c757d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-card strong {
            display: block;
            font-size: 20px;
            margin-top: 3px;
        }

        .amount-card {
            background: #fff8dc;
            border-color: #ffe69c;
        }

        .amount-card strong {
            color: #198754;
        }

        .type-badge {
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 20px;
            font-weight: 700;
        }

        @media (max-width: 992px) {

            .number-grid,
            .haruf-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {

            .number-grid,
            .haruf-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 5px;
            }

            .number-card {
                min-height: 78px;
                padding: 6px;
                border-radius: 10px;
            }

            .number-value {
                font-size: 16px;
            }

            .number-amount {
                font-size: 11px;
            }

            .number-meta {
                font-size: 9px;
            }
        }
    </style>
@endsection

@section('script')
    <script>
        $(function () {
            const $gameSelect = $('#gameSelect');
            const $dateSelect = $('#dateSelect');
            const $loadingBox = $('#loadingBox');
            const $emptyBox = $('#emptyBox');
            const $jodiSection = $('#jodiSection');
            const $crossingSection = $('#crossingSection');
            const $betSummary = $('#betSummary');
            const $harufSection = $('#harufSection');
            const $jodiGrid = $('#jodiGrid');
            const $crossingGrid = $('#crossingGrid');
            const $anderGrid = $('#anderGrid');
            const $baharGrid = $('#baharGrid');
            const $modalBidTable = $('#modalBidTable');
            const $modalUserBreakdown = $('#modalUserBreakdown');
            const $modalLoadMore = $('#modalLoadMore');
            const bidModalElement = $('#bidDetailsModal').get(0);
            if (!bidModalElement || !window.bootstrap) return;

            const bidModal = bootstrap.Modal.getOrCreateInstance(bidModalElement);
            const dataEndpoint = @json(route('admin.bidding-desk.data'));
            const detailsEndpoint = @json(route('admin.bidding-desk.details'));
            let activeDetails = null;
            let dataRequest = null;
            let detailsRequest = null;

            const money = value => Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const setVisible = ($element, visible) => $element.toggleClass('d-none', !visible);
            const getJson = (url, data) => $.ajax({
                url,
                method: 'GET',
                data,
                dataType: 'json',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            async function loadBiddingData() {
                const gameId = $gameSelect.val();
                const date = $dateSelect.val();

                if (dataRequest) dataRequest.abort();
                if (!gameId) {
                    setVisible($jodiSection, false);
                    setVisible($crossingSection, false);
                    setVisible($betSummary, false);
                    setVisible($harufSection, false);
                    setVisible($emptyBox, true);
                    return;
                }

                setVisible($emptyBox, false);
                setVisible($loadingBox, true);
                setVisible($jodiSection, false);
                setVisible($crossingSection, false);
                setVisible($betSummary, false);
                setVisible($harufSection, false);

                const request = getJson(dataEndpoint, { game_id: gameId, date });
                dataRequest = request;
                try {
                    const result = await request;
                    if (!result?.success && !result?.status) {
                        throw new Error(result?.message || 'Unable to load data.');
                    }
                    const payload = result.data || {};
                    renderNumberGrid($jodiGrid, payload.jodi, 'jodi', 'jodiTotal');
                    renderNumberGrid($crossingGrid, payload.crossing, 'cross', 'crossingTotal');
                    renderHaruf(payload.haruf || { ander: [], bahar: [] });
                    renderBetSummary(payload.summary || {});
                    setVisible($betSummary, true);
                    setVisible($jodiSection, true);
                    setVisible($crossingSection, true);
                    setVisible($harufSection, true);
                } catch (error) {
                    if (error.statusText === 'abort') return;
                    console.error(error);
                    window.alert(error.responseJSON?.message || error.message || 'Unable to load bidding data.');
                } finally {
                    if (dataRequest === request) {
                        dataRequest = null;
                        setVisible($loadingBox, false);
                    }
                }
            }

            function renderNumberGrid($container, numbers, type, totalId) {
                $container.empty();
                const items = Array.isArray(numbers) ? numbers : [];
                const grandTotal = items.reduce((sum, item) => sum + Number(item.total_amount || 0), 0);
                const invested = items.map(item => Number(item.total_amount || 0))
                    .filter(amount => amount > 0)
                    .sort((a, b) => a - b);
                const highThreshold = invested.length
                    ? invested[Math.max(0, Math.ceil(invested.length * 0.75) - 1)]
                    : 0;

                items.forEach(item => {
                    const amount = Number(item.total_amount || 0);
                    const investmentClass = amount <= 0
                        ? ' empty-number'
                        : amount >= highThreshold && highThreshold > 0
                            ? ' high-investment'
                            : ' low-investment';
                    const $card = $('<button>', {
                        type: 'button',
                        class: 'number-card text-start' + (Number(item.total_bids) > 0 ? ' has-bet' : '') + investmentClass,
                        'aria-label': type + ' ' + item.number + ', ' + item.total_bids + ' bets'
                    });
                    $card.append($('<div>', { class: 'number-value' }).text(item.number));
                    $card.append($('<div>', { class: 'number-amount' }).text('₹' + money(item.total_amount)));
                    $card.append($('<div>', { class: 'number-meta' }).text(
                        Number(item.total_users || 0) + ' users · ' + Number(item.total_bids || 0) + ' bets'
                    ));
                    $card.on('click', () => openDetails(item.number, type));
                    $container.append($card);
                });

                $('#' + totalId).text('₹' + money(grandTotal));
            }

            function renderBetSummary(summary) {
                ['jodi', 'haruf', 'crossing'].forEach(type => {
                    const item = summary[type] || {};
                    $('#summary-' + type + '-count').text(Number(item.total_bets || 0).toLocaleString('en-IN'));
                    $('#summary-' + type + '-amount').text('₹' + money(item.total_amount));
                    $('#summary-' + type + '-empty').text(Number(item.unplayed_numbers || 0));
                    $('#summary-' + type + '-empty-amount').text(money(item.unplayed_amount || 0));
                });
            }

            function renderHaruf(haruf) {
                renderHarufGrid($anderGrid, haruf.ander || [], 'ander');
                renderHarufGrid($baharGrid, haruf.bahar || [], 'bahar');
                const anderTotal = (haruf.ander || []).reduce((sum, item) => sum + Number(item.total_amount || 0), 0);
                const baharTotal = (haruf.bahar || []).reduce((sum, item) => sum + Number(item.total_amount || 0), 0);
                $('#anderTotal').text('₹' + money(anderTotal));
                $('#baharTotal').text('₹' + money(baharTotal));
            }

            function renderHarufGrid($container, numbers, side) {
                $container.empty();
                numbers.forEach(item => {
                    const fullNumber = side + '-' + item.number;
                    const $card = $('<button>', {
                        type: 'button',
                        class: 'number-card text-start' + (Number(item.total_bids) > 0 ? ' has-bet' : ''),
                        'aria-label': side + ' ' + item.number + ', ' + item.total_bids + ' bets'
                    });
                    $card.append($('<div>', { class: 'number-value' }).text(item.number));
                    $card.append($('<div>', { class: 'number-amount' }).text('₹' + money(item.total_amount)));
                    $card.append($('<div>', { class: 'number-meta' }).text(
                        Number(item.total_users || 0) + ' users · ' + Number(item.total_bids || 0) + ' bets'
                    ));
                    $card.on('click', () => openDetails(fullNumber, 'haruf'));
                    $container.append($card);
                });
            }

            async function openDetails(number, type = 'jodi') {
                if (!$gameSelect.val()) return;
                if (detailsRequest) detailsRequest.abort();
                activeDetails = { number, type, page: 1, lastPage: 1 };
                $('#modalNumber').text(number);
                $modalBidTable.empty();
                $modalUserBreakdown.empty();
                setVisible($('#modalEmpty'), false);
                setVisible($modalLoadMore, false);
                bidModal.show();
                await loadDetailsPage(1, false);
            }

            async function loadDetailsPage(page, append) {
                if (!activeDetails) return;
                if (detailsRequest) detailsRequest.abort();
                $modalLoadMore.prop('disabled', true);

                if (!append) {
                    $modalBidTable.html('<tr><td colspan="9" class="text-center py-5"><div class="spinner-border text-warning"></div><div class="mt-2 text-muted">Loading...</div></td></tr>');
                }

                const request = getJson(detailsEndpoint, {
                    game_id: $gameSelect.val(),
                    date: $dateSelect.val(),
                    number: activeDetails.number,
                    type: activeDetails.type,
                    page,
                    per_page: 50
                });
                detailsRequest = request;

                try {
                    const result = await request;
                    if (!result?.success && !result?.status) {
                        throw new Error(result?.message || 'Unable to load details.');
                    }
                    const payload = result.data || {};
                    activeDetails.page = Number(payload.pagination?.current_page || page);
                    activeDetails.lastPage = Number(payload.pagination?.last_page || 1);
                    renderModalDetails(payload, append);
                    setVisible($modalLoadMore, activeDetails.page < activeDetails.lastPage);
                } catch (error) {
                    if (error.statusText === 'abort') return;
                    if (!append) $modalBidTable.empty();
                    const $row = $('<tr>');
                    $row.append($('<td>', { colspan: 9, class: 'text-center text-danger py-4' })
                        .text(error.responseJSON?.message || error.message || 'Unable to load details.'));
                    $modalBidTable.append($row);
                } finally {
                    if (detailsRequest === request) detailsRequest = null;
                    $modalLoadMore.prop('disabled', false);
                }
            }

            function renderModalDetails(data, append = false) {
                const summary = data.summary || {};
                $('#modalTotalBids').text(summary.total_bids || 0);
                $('#modalTotalUsers').text(summary.total_users || 0);
                $('#modalTotalAmount').text('₹' + money(summary.total_amount));

                if (!append) $modalUserBreakdown.empty();
                (data.users || []).forEach(user => {
                    const $card = $('<section>', { class: 'border rounded-3 p-3' });
                    const $heading = $('<div>', { class: 'd-flex flex-wrap justify-content-between gap-2 mb-2' });
                    const $identity = $('<div>');
                    $identity.append($('<strong>').text(user.user_name || ('User #' + user.user_id)));
                    $identity.append($('<div>', { class: 'small text-muted' })
                        .text('User #' + user.user_id + ' · ' + (user.user_phone || '-')));
                    $heading.append($identity);
                    $heading.append($('<span>', { class: 'badge bg-warning text-dark align-self-start' })
                        .text((user.matching_bids || []).length + ' matching bids'));

                    const $numbers = $('<div>', { class: 'd-flex flex-wrap gap-2' });
                    (user.numbers || []).forEach(number => {
                        const selected = String(number.number) === String(data.number);
                        $numbers.append($('<span>', {
                            class: 'badge ' + (selected ? 'bg-warning text-dark' : 'bg-light text-dark border') + ' p-2'
                        }).text(number.number + ' · ' + number.total_bids + ' × ₹' + money(number.total_amount)));
                    });

                    const bidText = (user.matching_bids || []).map(bid =>
                        'Bid #' + bid.id +
                        (bid.transaction_ids?.length ? ' · Txn #' + bid.transaction_ids.join(', #') : '') +
                        ' · ₹' + money(bid.amount) + ' · ' + (bid.created_at || '-')
                    ).join(' | ');
                    $card.append($heading, $numbers, $('<div>', { class: 'small text-muted mt-2' }).text(bidText));
                    $modalUserBreakdown.append($card);
                });

                if (!append) $modalBidTable.empty();
                if (!Number(summary.total_bids || 0)) {
                    setVisible($('#modalEmpty'), true);
                    return;
                }
                setVisible($('#modalEmpty'), false);

                (data.bids || []).forEach(bid => {
                    const $row = $('<tr>');
                    const bidLabel = 'Bid #' + bid.id + (bid.transaction_id ? ' · Txn #' + bid.transaction_id : '');
                    const $type = $('<span>', {
                        class: 'badge type-badge ' + (bid.type === 'jodi' ? 'bg-warning text-dark' : bid.type === 'cross' ? 'bg-dark' : 'bg-primary')
                    }).text(bid.type === 'jodi' ? 'Jodi' : bid.type === 'cross' ? 'Crossing' : 'Haruf');
                    const status = String(bid.status || 'pending').toLowerCase();
                    const statusClass = status === 'pending' ? 'bg-warning text-dark'
                        : status === 'won' ? 'bg-success'
                            : status === 'lost' ? 'bg-danger'
                                : status === 'cancelled' ? 'bg-dark' : 'bg-secondary';
                    const $status = $('<span>', { class: 'badge ' + statusClass + ' type-badge' }).text(bid.status || '-');

                    $row.append($('<td>').text(bidLabel));
                    const $userCell = $('<td>');
                    $userCell.append($('<div>', { class: 'fw-bold' }).text(bid.user_name || ''));
                    $userCell.append($('<small>', { class: 'text-muted' }).text('ID: ' + bid.user_id));
                    $row.append($userCell);
                    $row.append($('<td>').text(bid.user_phone || ''));
                    $row.append($('<td>').append($type));
                    $row.append($('<td>', { class: 'fw-bold' }).text(bid.number || ''));
                    $row.append($('<td>', { class: 'fw-bold text-success' }).text('₹' + money(bid.amount)));
                    $row.append($('<td>').append($status));
                    $row.append($('<td>').append($('<small>', { class: 'fw-semibold' }).text(bid.order_no || '')));
                    $row.append($('<td>').append($('<small>', { class: 'text-muted' }).text(bid.created_at || '-')));
                    $modalBidTable.append($row);
                });
            }

            $modalLoadMore.on('click', function () {
                if (activeDetails && activeDetails.page < activeDetails.lastPage) {
                    void loadDetailsPage(activeDetails.page + 1, true);
                }
            });
            $gameSelect.add($dateSelect).on('change', loadBiddingData);

            if ($gameSelect.find('option').length > 1) {
                $gameSelect.prop('selectedIndex', 1);
                void loadBiddingData();
            } else {
                setVisible($emptyBox, true);
            }
        });
    </script>
@endsection
