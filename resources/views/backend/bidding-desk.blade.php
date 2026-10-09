@extends('backend.include.layout')

@section('title', 'Bidding Desk')

@section('content')

    <div class="container-fluid py-3">

        {{-- HEADER --}}
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
                        {{-- GAME --}}
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
                        {{-- DATE --}}
                        <input type="date" id="dateSelect" class="form-control fw-bold"
                            value="{{ now()->toDateString() }}">
                    </div>
                </div>
            </div>

        </div>


        {{-- LOADING --}}
        <div id="loadingBox" class="text-center py-5 d-none">
            <div class="spinner-border text-warning"></div>
            <div class="mt-2 text-muted fw-semibold">
                Loading bidding data...
            </div>

        </div>


        {{-- EMPTY --}}
        <div id="emptyBox" class="alert alert-info border-0 shadow-sm rounded-4 d-none">
            Please select a game.
        </div>


        {{-- ===================================================== --}}
        {{-- JODI + CROSSING --}}
        {{-- ===================================================== --}}

        <div id="jodiCrossingSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
            <div class="card-header bg-dark text-white border-0 rounded-top-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            🎯 Jodi + 🔀 Crossing
                        </h5>
                        <small class="text-white-50">
                            Jodi and Crossing combined
                        </small>
                    </div>
                    <span id="jodiCrossingTotal" class="badge bg-warning text-dark fs-6">
                        ₹0
                    </span>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="jodiCrossingGrid" class="number-grid"></div>
            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- HARUF --}}
        {{-- ===================================================== --}}

        <div id="harufSection" class="card border-0 shadow-sm rounded-4 d-none">
            <div class="card-header bg-primary text-white border-0 rounded-top-4 p-3">
                <h5 class="mb-0 fw-bold">
                    🎲 Haruf
                </h5>
                <small class="text-white-50">
                    Ander and Bahar
                </small>
            </div>

            <div class="card-body p-3">
                {{-- ANDER --}}
                <div class="mb-4">
                    <div class="section-title ander-title">
                        <span>🅰️ Ander</span>
                        <span id="anderTotal">
                            ₹0
                        </span>
                    </div>
                    <div id="anderGrid" class="haruf-grid"></div>
                </div>
                {{-- BAHAR --}}
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


    {{-- ========================================================= --}}
    {{-- DETAILS MODAL --}}
    {{-- ========================================================= --}}

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
                    {{-- SUMMARY --}}
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
                    {{-- TABLE --}}
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
            /*
                |--------------------------------------------------------------------------
                | ELEMENTS
                |--------------------------------------------------------------------------
                */
            const gameSelect =
                document.getElementById('gameSelect');
            const dateSelect =
                document.getElementById('dateSelect');
            const loadingBox =
                document.getElementById('loadingBox');
            const emptyBox =
                document.getElementById('emptyBox');
            const jodiCrossingSection =
                document.getElementById(
                    'jodiCrossingSection'
                );
            const harufSection =
                document.getElementById(
                    'harufSection'
                );
            const jodiCrossingGrid =
                document.getElementById(
                    'jodiCrossingGrid'
                );
            const anderGrid =
                document.getElementById(
                    'anderGrid'
                );
            const baharGrid =
                document.getElementById(
                    'baharGrid'
                );

            /*
                        |--------------------------------------------------------------------------
                        | BOOTSTRAP MODAL
                        |--------------------------------------------------------------------------
                        */
            const modalElement =
                document.getElementById(
                    'bidDetailsModal'
                );
            const bidModal =
                new bootstrap.Modal(
                    modalElement
                );

            /*
                        |--------------------------------------------------------------------------
                        | LOAD DATA
                        |--------------------------------------------------------------------------
                        */
            async function loadBiddingData() {
                const gameId =
                    gameSelect.value;
                const date =
                    dateSelect.value;
                if (!gameId) {
                    jodiCrossingSection
                        .classList
                        .add('d-none');
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
                jodiCrossingSection
                    .classList
                    .add('d-none');
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
                    renderJodiCrossing(
                        result.data.jodi_crossing
                    );
                    renderHaruf(
                        result.data.haruf
                    );
                    jodiCrossingSection
                        .classList
                        .remove('d-none');
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

            /*
                        |--------------------------------------------------------------------------
                        | JODI + CROSSING RENDER
                        |--------------------------------------------------------------------------
                        */
            function renderJodiCrossing(numbers) {
                jodiCrossingGrid.innerHTML = '';
                let grandTotal = 0;
                numbers.forEach(item => {
                    grandTotal +=
                        Number(
                            item.total_amount || 0
                        );
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
                            item.number
                        )
                    );
                    jodiCrossingGrid
                        .appendChild(card);
                });
                document.getElementById(
                        'jodiCrossingTotal'
                    ).textContent =
                    '₹' + formatMoney(grandTotal);
            }

            /*
                        |--------------------------------------------------------------------------
                        | HARUF RENDER
                        |--------------------------------------------------------------------------
                        */
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
                            fullNumber
                        )
                    );
                    container.appendChild(card);
                });
            }

            /*
                        |--------------------------------------------------------------------------
                        | OPEN DETAILS
                        |--------------------------------------------------------------------------
                        */
            async function openDetails(number) {
                const gameId =
                    gameSelect.value;
                const date =
                    dateSelect.value;
                if (!gameId) {
                    return;
                }
                document.getElementById(
                    'modalNumber'
                ).textContent = number;
                document.getElementById(
                    'modalBidTable'
                ).innerHTML = `
                <tr>
            <td
                    colspan="9"
                    class="text-center py-5"
                >
                    <div class="spinner-border text-warning"></div>
                        <div class="mt-2 text-muted">
                        Loading...
                    </div>
                    </td>
                    </tr>

        `;
                document.getElementById(
                    'modalEmpty'
                ).classList.add('d-none');
                bidModal.show();
                try {
                    const params =
                        new URLSearchParams({
                            game_id: gameId,
                            date: date,
                            number: number,
                        });
                    const response =
                        await fetch(
                            `{{ route('admin.bidding-desk.details') }}?${params.toString()}`, {
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
                            'Unable to load details.'
                        );
                    }
                    renderModalDetails(
                        result.data
                    );
                } catch (error) {
                    console.error(error);
                    document.getElementById(
                        'modalBidTable'
                    ).innerHTML = `
                    <tr>
                    <td
                        colspan="9"
                        class="text-center text-danger py-4"
                    >
                        ${escapeHtml(
                            error.message ||
                            'Unable to load details.'
                        )}
                    </td>
                    </tr>
                    `;
                }
            }

            /*
                        |--------------------------------------------------------------------------
                        | MODAL TABLE
                        |--------------------------------------------------------------------------
                        */
            function renderModalDetails(data) {
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

                const tbody =
                    document.getElementById(
                        'modalBidTable'
                    );
                tbody.innerHTML = '';

                if (!data.bids.length) {
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
                        ${index + 1}
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

            /*
                        |--------------------------------------------------------------------------
                        | HELPERS
                        |--------------------------------------------------------------------------
                        */
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

            /*
                        |--------------------------------------------------------------------------
                        | EVENTS
                        |--------------------------------------------------------------------------
                        */
            gameSelect.addEventListener(
                'change',
                loadBiddingData
            );
            dateSelect.addEventListener(
                'change',
                loadBiddingData
            );

            /*
                        |--------------------------------------------------------------------------
                        | INITIAL LOAD
                        |--------------------------------------------------------------------------
                        */
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
