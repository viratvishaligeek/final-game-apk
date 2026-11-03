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
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Jodi · <span id="summary-jodi-count">0</span> bets</small><strong id="summary-jodi-amount">₹0</strong><span class="small text-muted"><span id="summary-jodi-empty">0</span> unplayed numbers</span></div></div>
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Haruf · <span id="summary-haruf-count">0</span> bets</small><strong id="summary-haruf-amount">₹0</strong><span class="small text-muted"><span id="summary-haruf-empty">0</span> unplayed numbers</span></div></div>
            <div class="col-12 col-md-4"><div class="summary-card h-100"><small>Crossing · <span id="summary-crossing-count">0</span> bets</small><strong id="summary-crossing-amount">₹0</strong><span class="small text-muted"><span id="summary-crossing-empty">0</span> unplayed numbers</span></div></div>
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
                                        #
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
        document.addEventListener('DOMContentLoaded', function() {
            const gameSelect =
                document.getElementById('gameSelect');
            const dateSelect =
                document.getElementById('dateSelect');
            const loadingBox =
                document.getElementById('loadingBox');
            const emptyBox =
                document.getElementById('emptyBox');
            const jodiSection = document.getElementById('jodiSection');
            const crossingSection = document.getElementById('crossingSection');
            const betSummary = document.getElementById('betSummary');
            const harufSection =
                document.getElementById(
                    'harufSection'
                );
            const jodiGrid = document.getElementById('jodiGrid');
            const crossingGrid = document.getElementById('crossingGrid');
            const anderGrid =
                document.getElementById(
                    'anderGrid'
                );
            const baharGrid =
                document.getElementById(
                    'baharGrid'
                );
            const modalElement =
                document.getElementById(
                    'bidDetailsModal'
                );
            const bidModal =
                new bootstrap.Modal(
                    modalElement
                );
            async function loadBiddingData() {
                const gameId =
                    gameSelect.value;
                const date =
                    dateSelect.value;
                if (!gameId) {
                    jodiSection.classList.add('d-none');
                    crossingSection.classList.add('d-none');
                    betSummary.classList.add('d-none');
                    harufSection
                        .classList
                        .add('d-none');
                    emptyBox
                        .classList
                        .remove('d-none');
                    return;
                }
                emptyBox
                    .classList
                    .add('d-none');
                loadingBox
                    .classList
                    .remove('d-none');
                jodiSection.classList.add('d-none');
                crossingSection.classList.add('d-none');
                betSummary.classList.add('d-none');
                harufSection
                    .classList
                    .add('d-none');
                try {
                    const params =
                        new URLSearchParams({
                            game_id: gameId,
                            date: date,
                        });
                    const response =
                        await fetch(
                            `{{ route('admin.bidding-desk.data') }}?${params.toString()}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            }
                        );
                    const result =
                        await response.json();
                    if (!response.ok) {
                        throw new Error(
                            result.message ||
                            'Unable to load data.'
                        );
                    }
                    renderNumberGrid(jodiGrid, result.data.jodi, 'jodi', 'jodiTotal');
                    renderNumberGrid(crossingGrid, result.data.crossing, 'cross', 'crossingTotal');
                    renderHaruf(result.data.haruf);
                    renderBetSummary(result.data.summary);
                    betSummary.classList.remove('d-none');
                    jodiSection.classList.remove('d-none');
                    crossingSection.classList.remove('d-none');
                    harufSection
                        .classList
                        .remove('d-none');
                } catch (error) {
                    console.error(error);
                    alert(
                        error.message ||
                        'Unable to load bidding data.'
                    );
                } finally {
                    loadingBox
                        .classList
                        .add('d-none');
                }
            }

            function renderNumberGrid(container, numbers, type, totalId) {
                container.replaceChildren();
                const grandTotal = (numbers || []).reduce((sum, item) => sum + Number(item.total_amount || 0), 0);
                const invested = (numbers || []).map(item => Number(item.total_amount || 0)).filter(amount => amount > 0).sort((a, b) => a - b);
                const median = invested.length ? invested[Math.floor((invested.length - 1) / 2)] : 0;
                const highThreshold = invested.length ? invested[Math.floor((invested.length - 1) * 0.75)] : 0;
                (numbers || []).forEach(item => {
                    const amount = Number(item.total_amount || 0);
                    const investmentClass = amount <= 0 ? ' empty-number' : amount >= highThreshold && highThreshold > 0 ? ' high-investment' : ' low-investment';
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'number-card text-start' + (item.total_bids > 0 ? ' has-bet' : '') + investmentClass;
                    card.setAttribute('aria-label', type + ' ' + item.number + ', ' + item.total_bids + ' bets');
                    card.innerHTML = '<div class="number-value">' + escapeHtml(item.number) + '</div>' +
                        '<div class="number-amount">₹' + formatMoney(item.total_amount) + '</div>' +
                        '<div class="number-meta">' + Number(item.total_users || 0) + ' users · ' + Number(item.total_bids || 0) + ' bets</div>';
                    card.addEventListener('click', () => openDetails(item.number, type));
                    container.appendChild(card);
                });
                document.getElementById(totalId).textContent = '₹' + formatMoney(grandTotal);
            }

            function renderBetSummary(summary) {
                ['jodi', 'haruf', 'crossing'].forEach(type => {
                    const item = summary?.[type] || {};
                    document.getElementById('summary-' + type + '-count').textContent = Number(item.total_bets || 0).toLocaleString('en-IN');
                    document.getElementById('summary-' + type + '-amount').textContent = '₹' + formatMoney(item.total_amount);
                    document.getElementById('summary-' + type + '-empty').textContent = Number(item.unplayed_numbers || 0);
                });
            }

            function renderHaruf(haruf) {
                renderHarufGrid(
                    anderGrid,
                    haruf.ander,
                    'ander'
                );
                renderHarufGrid(
                    baharGrid,
                    haruf.bahar,
                    'bahar'
                );
                const anderTotal =
                    haruf.ander.reduce(
                        (sum, item) =>
                        sum +
                        Number(
                            item.total_amount || 0
                        ),
                        0
                    );
                const baharTotal =
                    haruf.bahar.reduce(
                        (sum, item) =>
                        sum +
                        Number(
                            item.total_amount || 0
                        ),
                        0
                    );
                document.getElementById(
                        'anderTotal'
                    ).textContent =
                    '₹' + formatMoney(anderTotal);
                document.getElementById(
                        'baharTotal'
                    ).textContent =
                    '₹' + formatMoney(baharTotal);
            }

            function renderHarufGrid(
                container,
                numbers,
                side
            ) {
                container.innerHTML = '';
                numbers.forEach(item => {
                    const fullNumber =
                        side + '-' + item.number;
                    const card =
                        document.createElement('div');
                    card.className =
                        'number-card' +
                        (
                            item.total_bids > 0 ?
                            ' has-bet' :
                            ''
                        );
                    card.innerHTML = `
                    <div class="number-value">
                    ${escapeHtml(item.number)}
                </div>
                <div class="number-amount">
                    ₹${formatMoney(item.total_amount)}
                </div>
                <div class="number-meta">
                    ${item.total_users} users
                    ·
                    ${item.total_bids} bets
                </div>
                `;
                    card.addEventListener(
                        'click',
                        () => openDetails(
                            fullNumber,
                            'haruf'
                        )
                    );
                    container.appendChild(card);
                });
            }

            let activeDetails = null;
            async function openDetails(number, type = 'jodi') {
                if (!gameSelect.value) return;
                activeDetails = { number, type, page: 1, lastPage: 1 };
                document.getElementById('modalNumber').textContent = number;
                document.getElementById('modalBidTable').replaceChildren();
                document.getElementById('modalUserBreakdown').replaceChildren();
                document.getElementById('modalEmpty').classList.add('d-none');
                document.getElementById('modalLoadMore').classList.add('d-none');
                bidModal.show();
                await loadDetailsPage(1, false);
            }

            async function loadDetailsPage(page, append) {
                if (!activeDetails) return;
                const button = document.getElementById('modalLoadMore');
                button.disabled = true;
                if (!append) {
                    document.getElementById('modalBidTable').innerHTML = '<tr><td colspan="9" class="text-center py-5"><div class="spinner-border text-warning"></div><div class="mt-2 text-muted">Loading...</div></td></tr>';
                }
                try {
                    const params = new URLSearchParams({
                        game_id: gameSelect.value,
                        date: dateSelect.value,
                        number: activeDetails.number,
                        type: activeDetails.type,
                        page: page,
                        per_page: 50,
                    });
                    const response = await fetch(`{{ route('admin.bidding-desk.details') }}?${params.toString()}`,
                        { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                    );
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Unable to load details.');
                    activeDetails.page = result.data.pagination.current_page;
                    activeDetails.lastPage = result.data.pagination.last_page;
                    renderModalDetails(result.data, append);
                    button.classList.toggle('d-none', activeDetails.page >= activeDetails.lastPage);
                } catch (error) {
                    const tbody = document.getElementById('modalBidTable');
                    if (!append) tbody.replaceChildren();
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 9;
                    cell.className = 'text-center text-danger py-4';
                    cell.textContent = error.message || 'Unable to load details.';
                    row.appendChild(cell);
                    tbody.appendChild(row);
                } finally {
                    button.disabled = false;
                }
            }

            document.getElementById('modalLoadMore').addEventListener('click', () => {
                if (activeDetails && activeDetails.page < activeDetails.lastPage) {
                    void loadDetailsPage(activeDetails.page + 1, true);
                }
            });

            function renderModalDetails(data, append = false) {
                document.getElementById(
                        'modalTotalBids'
                    ).textContent =
                    data.summary.total_bids;
                document.getElementById(
                        'modalTotalUsers'
                    ).textContent =
                    data.summary.total_users;
                document.getElementById(
                        'modalTotalAmount'
                    ).textContent =
                    '₹' +
                    formatMoney(
                        data.summary.total_amount
                    );
                const userContainer = document.getElementById('modalUserBreakdown');
                if (!append) userContainer.replaceChildren();
                (data.users || []).forEach(user => {
                    const card = document.createElement('section');
                    card.className = 'border rounded-3 p-3';
                    const heading = document.createElement('div');
                    heading.className = 'd-flex flex-wrap justify-content-between gap-2 mb-2';
                    const identity = document.createElement('div');
                    const name = document.createElement('strong');
                    name.textContent = user.user_name || ('User #' + user.user_id);
                    const meta = document.createElement('div');
                    meta.className = 'small text-muted';
                    meta.textContent = 'User #' + user.user_id + ' · ' + (user.user_phone || '-');
                    identity.append(name, meta);
                    const selectedTotal = document.createElement('span');
                    selectedTotal.className = 'badge bg-warning text-dark align-self-start';
                    selectedTotal.textContent = (user.matching_bids || []).length + ' matching bids';
                    heading.append(identity, selectedTotal);
                    const numbers = document.createElement('div');
                    numbers.className = 'd-flex flex-wrap gap-2';
                    (user.numbers || []).forEach(number => {
                        const badge = document.createElement('span');
                        badge.className = 'badge ' + (String(number.number) === String(data.number) ? 'bg-warning text-dark' : 'bg-light text-dark border') + ' p-2';
                        badge.textContent = number.number + ' · ' + number.total_bids + ' × ₹' + formatMoney(number.total_amount);
                        numbers.appendChild(badge);
                    });
                    const bids = document.createElement('div');
                    bids.className = 'small text-muted mt-2';
                    bids.textContent = (user.matching_bids || []).map(bid => 'Bid #' + bid.id + (bid.transaction_id ? ' · Txn #' + bid.transaction_id : '') + ' · ₹' + formatMoney(bid.amount) + ' · ' + (bid.created_at || '-')).join(' | ');
                    card.append(heading, numbers, bids);
                    userContainer.appendChild(card);
                });
                const tbody =
                    document.getElementById(
                        'modalBidTable'
                    );
                if (!append) tbody.replaceChildren();
                if (!data.summary.total_bids) {
                    document.getElementById(
                        'modalEmpty'
                    ).classList.remove('d-none');
                    return;
                }
                data.bids.forEach(
                    (bid, index) => {
                        const tr =
                            document.createElement('tr');
                        const typeBadge =
                            bid.type === 'jodi' ?
                            `<span class="badge bg-warning text-dark type-badge">Jodi</span>` :
                            bid.type === 'cross' ?
                            `<span class="badge bg-dark type-badge">Crossing</span>` :
                            `<span class="badge bg-primary type-badge">Haruf</span>`;
                        tr.innerHTML = `
    <td>
                        Bid #${bid.id}${bid.transaction_id ? ' · Txn #' + bid.transaction_id : ''}
                    </td>
                        <td>
                    <div class="fw-bold">
                            ${escapeHtml(
                                bid.user_name
                            )}
                        </div>
                        <small class="text-muted">
                            ID: ${bid.user_id}
                        </small>
                            </td>
                        <td>
                        ${escapeHtml(
                            bid.user_phone
                        )}
                    </td>
                        <td>
                        ${typeBadge}
                    </td>
                        <td class="fw-bold">
                        ${escapeHtml(
                            bid.number
                        )}
                    </td>
                        <td class="fw-bold text-success">
                        ₹${formatMoney(
                            bid.amount
                        )}
                    </td>
                        <td>
                        ${statusBadge(
                            bid.status
                        )}
                    </td>
                        <td>
                        <small class="fw-semibold">
                            ${escapeHtml(
                                bid.order_no
                            )}
                        </small>
                    </td>
                        <td>
                        <small class="text-muted">
                            ${escapeHtml(
                                bid.created_at || '-'
                            )}
                        </small>
                    </td>
                    `;
                        tbody.appendChild(tr);
                    }
                );
            }

            function formatMoney(amount) {
                return Number(
                    amount || 0
                ).toLocaleString(
                    'en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }
                );
            }

            function statusBadge(status) {
                const value =
                    String(
                        status || 'pending'
                    ).toLowerCase();
                let color = 'secondary';
                if (value === 'pending') {
                    color = 'warning text-dark';
                }
                if (value === 'won') {
                    color = 'success';
                }
                if (value === 'lost') {
                    color = 'danger';
                }
                if (value === 'cancelled') {
                    color = 'dark';
                }
                return `
            <span class="badge bg-${color} type-badge">
                ${escapeHtml(status || '-')}
            </span>
        `;
            }

            function escapeHtml(value) {
                return String(
                        value ?? ''
                    )
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            gameSelect.addEventListener(
                'change',
                loadBiddingData
            );
            dateSelect.addEventListener(
                'change',
                loadBiddingData
            );

            // Default game select first game
            if (gameSelect.options.length > 1) {
                gameSelect.selectedIndex = 1;
                loadBiddingData();
            } else {
                emptyBox
                    .classList
                    .remove('d-none');
            }
        });
    </script>
@endsection
