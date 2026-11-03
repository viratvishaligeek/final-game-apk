@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                style="width: 46px; height: 46px;">
                                <i data-feather="user"></i>
                            </div>
                            <div>
                                <h1 class="page-header-title mb-1">
                                    {{ $user->name }}
                                </h1>
                                <div class="small text-muted">
                                    User #{{ $user->id }}
                                    <span class="mx-1">•</span>
                                    {{ $user->phone }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border">
                                <i data-feather="arrow-left" class="me-1"></i>
                                Back
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                <i data-feather="edit" class="me-1"></i>
                                Edit User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection


@section('content')
    <div class="container-fluid px-4">
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3"
                                    style="width: 55px; height: 55px;">
                                    <i data-feather="user"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">
                                        {{ $user->name }}
                                    </h5>
                                    <div class="text-muted small">
                                        Customer Profile
                                    </div>
                                </div>
                            </div>
                            @if ($user->status === 'active')
                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    <i data-feather="check-circle" style="width:14px;"></i>
                                    Active
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                    <i data-feather="x-circle" style="width:14px;"></i>
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="small text-muted mb-1">
                                    Phone Number
                                </div>
                                <div class="fw-semibold">
                                    {{ $user->phone ?: '-' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted mb-1">
                                    Gender
                                </div>
                                <div class="fw-semibold text-capitalize">
                                    {{ $user->gender ?: '-' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted mb-1">
                                    City
                                </div>
                                <div class="fw-semibold">
                                    {{ $user->city ?: '-' }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="small text-muted mb-1">
                                    Address
                                </div>
                                <div class="fw-semibold">
                                    {{ $user->address ?: '-' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">
                                    Registered On
                                </div>
                                <div class="fw-semibold">
                                    {{ optional($user->created_at)->format('d M Y, h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">
                                    Last Updated
                                </div>
                                <div class="fw-semibold">
                                    {{ optional($user->updated_at)->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WALLET --}}
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 text-white"
                        style="
                        background: linear-gradient(
                            135deg,
                            #dc3545 0%,
                            #b02a37 50%,
                            #842029 100%
                        );
                    ">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small opacity-75 mb-1">
                                    Available Balance
                                </div>
                                <div class="display-6 fw-bold">
                                    ₹{{ number_format($user->balance, 2) }}
                                </div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="
                                width:52px;
                                height:52px;
                                background:rgba(255,255,255,.15);
                            ">
                                <i data-feather="credit-card"></i>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="small opacity-75">
                                    Total Credit
                                </div>
                                <div class="fw-bold fs-5">
                                    ₹{{ number_format($walletStats['credit'], 2) }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small opacity-75">
                                    Total Debit
                                </div>
                                <div class="fw-bold fs-5">
                                    ₹{{ number_format($walletStats['debit'], 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-light flex-fill fw-semibold" data-bs-toggle="modal"
                                data-bs-target="#creditWalletModal">
                                <i data-feather="plus-circle" class="me-1"></i>
                                Credit
                            </button>
                            <button type="button" class="btn btn-outline-light flex-fill fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#debitWalletModal">
                                <i data-feather="minus-circle" class="me-1"></i>
                                Debit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- MAIN TABS --}}
        {{-- ========================================================= --}}
        <div class="card border-0 shadow-sm">

            {{-- TAB HEADER --}}
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs border-0 px-3" id="userDetailTabs" role="tablist">
                    {{-- WALLET TAB --}}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 px-4 fw-semibold" id="wallet-tab" data-bs-toggle="tab"
                            data-bs-target="#wallet" type="button" role="tab">
                            <i data-feather="credit-card" class="me-2"></i>
                            Wallet
                            <span class="badge bg-danger-subtle text-danger ms-2">
                                {{ $transactions->total() }}
                            </span>
                        </button>
                    </li>
                    {{-- GAME TAB --}}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 px-4 fw-semibold" id="game-tab" data-bs-toggle="tab"
                            data-bs-target="#game" type="button" role="tab">
                            <i data-feather="target" class="me-2"></i>
                            Game Activity
                            <span class="badge bg-primary-subtle text-primary ms-2">
                                {{ $gameStats['total_bids'] }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- TAB CONTENT --}}
            <div class="tab-content">
                {{-- ================================================= --}}
                {{-- WALLET TAB --}}
                {{-- ================================================= --}}
                <div class="tab-pane fade show active" id="wallet" role="tabpanel">
                    <div class="p-4">
                        {{-- WALLET STAT CARDS --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="small text-muted">
                                                Current Balance
                                            </div>
                                            <div class="fs-4 fw-bold text-success">
                                                ₹{{ number_format($user->balance, 2) }}
                                            </div>
                                        </div>
                                        <div class="text-success">
                                            <i data-feather="dollar-sign"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="small text-muted">
                                                Total Credit
                                            </div>
                                            <div class="fs-4 fw-bold text-success">
                                                ₹{{ number_format($walletStats['credit'], 2) }}
                                            </div>
                                        </div>
                                        <div class="text-success">
                                            <i data-feather="trending-up"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="small text-muted">
                                                Total Debit
                                            </div>
                                            <div class="fs-4 fw-bold text-danger">
                                                ₹{{ number_format($walletStats['debit'], 2) }}
                                            </div>
                                        </div>
                                        <div class="text-danger">
                                            <i data-feather="trending-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- TRANSACTIONS --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    Wallet Transactions
                                </h5>
                                <div class="small text-muted">
                                    Complete debit and credit history
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="70">
                                            #
                                        </th>
                                        <th>
                                            Transaction
                                        </th>
                                        <th>
                                            Type
                                        </th>
                                        <th>
                                            Amount
                                        </th>
                                        <th>
                                            Balance
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td class="text-muted">
                                                {{ $transaction->id }}
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $transaction->subject }}
                                                </div>
                                            </td>
                                            <td>
                                                @if ($transaction->type === 'credit')
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i data-feather="arrow-down-left" style="width:13px;"></i>
                                                        Credit
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        <i data-feather="arrow-up-right" style="width:13px;"></i>
                                                        Debit
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold
                                                {{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->type === 'credit' ? '+' : '-' }}
                                                    ₹{{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">
                                                    ₹{{ number_format($transaction->balance, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($transaction->status === 'completed')
                                                    <span class="badge bg-success">
                                                        Completed
                                                    </span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ optional($transaction->created_at)->format('d M Y') }}
                                                </div>
                                                <div class="small text-muted">
                                                    {{ optional($transaction->created_at)->format('h:i A') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i data-feather="credit-card" style="width:40px;height:40px;"
                                                        class="mb-2">
                                                    </i>
                                                    <div class="fw-semibold">
                                                        No wallet transactions found
                                                    </div>
                                                    <div class="small">
                                                        Credit or debit activity will appear here.
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($transactions->hasPages())
                            <div class="mt-4">
                                {{ $transactions->withQueryString()->links() }}
                            </div>
                        @endif
                    </div>
                </div>
                {{-- ================================================= --}}
                {{-- GAME TAB --}}
                {{-- ================================================= --}}
                <div class="tab-pane fade" id="game" role="tabpanel">
                    <div class="p-4">
                        {{-- GAME SUMMARY --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="small text-muted mb-1">
                                        Total Bids
                                    </div>
                                    <div class="fs-4 fw-bold">
                                        {{ number_format($gameStats['total_bids']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="small text-muted mb-1">
                                        Total Bid Amount
                                    </div>
                                    <div class="fs-4 fw-bold text-primary">
                                        ₹{{ number_format($gameStats['total_amount'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- BID TYPE BREAKDOWN --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Jodi Bets</div>
                                    <div class="fs-4 fw-bold">{{ number_format((int) $bidTypeStats->jodi_count) }}</div>
                                    <div class="small text-muted">Invested ₹{{ number_format((float) $bidTypeStats->jodi_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Haruf Bets</div>
                                    <div class="fs-4 fw-bold">{{ number_format((int) $bidTypeStats->haruf_count) }}</div>
                                    <div class="small text-muted">Invested ₹{{ number_format((float) $bidTypeStats->haruf_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Crossing Bets</div>
                                    <div class="fs-4 fw-bold">{{ number_format((int) $bidTypeStats->crossing_count) }}</div>
                                    <div class="small text-muted">Invested ₹{{ number_format((float) $bidTypeStats->crossing_amount, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        {{-- BID TABLE HEADER --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    Bidding History
                                </h5>
                                <div class="small text-muted">
                                    All game bids placed by this user
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div><h5 class="fw-bold mb-1">Games Played</h5><div class="small text-muted">Select a game to inspect its individual bids.</div></div>
                            <span class="badge bg-primary-subtle text-primary">{{ $gameBidStats->count() }} games</span>
                        </div>
                        <div class="row g-3" id="user-game-bid-cards">
                            @forelse($gameBidStats as $gameStatsRow)
                                <div class="col-12 col-sm-6 col-xl-4">
                                    <button type="button" class="btn btn-light border shadow-sm w-100 text-start p-3 h-100 js-open-game-bids"
                                        data-game-id="{{ $gameStatsRow->game_id }}" data-game-name="{{ $gameStatsRow->game?->name ?? 'Game Deleted' }}">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <strong class="text-dark">{{ $gameStatsRow->game?->name ?? 'Game Deleted' }}</strong>
                                            <i data-feather="chevron-right" class="text-muted"></i>
                                        </div>
                                        <div class="d-flex justify-content-between mt-3"><span class="small text-muted">Bids</span><strong>{{ number_format((int) $gameStatsRow->total_bids) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span class="small text-muted">Invested</span><strong class="text-primary">₹{{ number_format((float) $gameStatsRow->total_amount, 2) }}</strong></div>
                                        <div class="small text-primary mt-3">View bid details</div>
                                    </button>
                                </div>
                            @empty
                                <div class="col-12"><div class="text-center py-5 border rounded text-muted">No bidding history found.</div></div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>



    <div class="modal fade" id="gameBidsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div><h5 class="modal-title fw-bold" id="gameBidsModalTitle">Game bids</h5><div class="small text-muted" id="gameBidsModalMeta"></div></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="gameBidsLoading" class="text-center py-4 d-none"><span class="spinner-border spinner-border-sm me-2"></span>Loading bids…</div>
                    <div id="gameBidsError" class="alert alert-danger d-none" role="alert"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light"><tr><th>Bid ID</th><th>Order</th><th>Type</th><th>Number</th><th>Play date</th><th>Amount</th><th>Result / status</th><th>Placed at</th></tr></thead>
                            <tbody id="gameBidsModalRows"></tbody>
                        </table>
                    </div>
                    <div id="gameBidsEmpty" class="text-center text-muted py-4 d-none">No bids found for this game.</div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted" id="gameBidsPagination"></small>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="gameBidsLoadMore">Load more</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- CREDIT MODAL --}}
    {{-- ========================================================= --}}

    <div class="modal fade" id="creditWalletModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.wallet.credit', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">
                                Credit Wallet
                            </h5>
                            <div class="small text-muted">
                                Add funds to {{ $user->name }}'s wallet
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-success-subtle rounded p-3 mb-4">
                            <div class="small text-muted">
                                Current Balance
                            </div>
                            <div class="fs-4 fw-bold text-success">
                                ₹{{ number_format($user->balance, 2) }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    ₹
                                </span>
                                <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
                                    placeholder="0.00" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">
                                Remark
                            </label>
                            <textarea name="remark" class="form-control" rows="3" maxlength="500"
                                placeholder="Enter transaction remark..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i data-feather="plus-circle" class="me-1"></i>
                            Credit Wallet
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- DEBIT MODAL --}}
    {{-- ========================================================= --}}

    <div class="modal fade" id="debitWalletModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.wallet.debit', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">
                                Debit Wallet
                            </h5>
                            <div class="small text-muted">
                                Deduct funds from {{ $user->name }}'s wallet
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-danger-subtle rounded p-3 mb-4">
                            <div class="small text-muted">
                                Available Balance
                            </div>
                            <div class="fs-4 fw-bold text-danger">
                                ₹{{ number_format($user->balance, 2) }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    ₹
                                </span>
                                <input type="number" name="amount" class="form-control" min="0.01"
                                    max="{{ $user->balance }}" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">
                                Remark
                            </label>
                            <textarea name="remark" class="form-control" rows="3" maxlength="500"
                                placeholder="Enter transaction remark..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i data-feather="minus-circle" class="me-1"></i>
                            Debit Wallet
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection


@section('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('gameBidsModal');
    if (!modalElement || !window.bootstrap) return;
    const modal = new bootstrap.Modal(modalElement);
    const endpoint = @json(route('admin.users.bids', $user->id));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const rows = document.getElementById('gameBidsModalRows');
    const loading = document.getElementById('gameBidsLoading');
    const error = document.getElementById('gameBidsError');
    const empty = document.getElementById('gameBidsEmpty');
    const more = document.getElementById('gameBidsLoadMore');
    const meta = document.getElementById('gameBidsModalMeta');
    let gameId = null, page = 1, lastPage = 1, busy = false;

    const cell = (value, className = '') => {
        const td = document.createElement('td');
        if (className) td.className = className;
        td.textContent = value == null || value === '' ? '-' : String(value);
        return td;
    };
    const load = async (nextPage = 1) => {
        if (!gameId || busy) return;
        busy = true;
        loading.classList.remove('d-none');
        error.classList.add('d-none');
        more.disabled = true;
        try {
            const params = new URLSearchParams({ game_id: String(gameId), page: String(nextPage), per_page: '50' });
            const response = await fetch(endpoint + '?' + params.toString(), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Unable to load this game’s bids.');
            if (nextPage === 1) rows.replaceChildren();
            (payload.data || []).forEach(bid => {
                const tr = document.createElement('tr');
                tr.append(
                    cell('#' + bid.id, 'fw-semibold'),
                    cell(bid.order_no),
                    cell(String(bid.type || '').toUpperCase()),
                    cell(bid.number, 'fw-bold'),
                    cell(bid.game_date),
                    cell('₹' + Number(bid.amount || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}), 'fw-semibold'),
                    cell((bid.status || '-') + (Number(bid.winning_amount) > 0 ? ' · Won ₹' + Number(bid.winning_amount).toFixed(2) : '')),
                    cell(bid.created_at)
                );
                rows.appendChild(tr);
            });
            page = payload.pagination?.current_page || nextPage;
            lastPage = payload.pagination?.last_page || 1;
            meta.textContent = (payload.pagination?.total || 0) + ' individual bids';
            document.getElementById('gameBidsPagination').textContent = 'Page ' + page + ' of ' + lastPage;
            more.classList.toggle('d-none', page >= lastPage);
            empty.classList.toggle('d-none', rows.children.length > 0);
        } catch (exception) {
            error.textContent = exception.message || 'Unable to load bids.';
            error.classList.remove('d-none');
        } finally {
            loading.classList.add('d-none');
            more.disabled = false;
            busy = false;
        }
    };
    document.querySelectorAll('.js-open-game-bids').forEach(button => {
        button.addEventListener('click', () => {
            gameId = button.dataset.gameId;
            page = 1;
            rows.replaceChildren();
            document.getElementById('gameBidsModalTitle').textContent = button.dataset.gameName || 'Game bids';
            modal.show();
            void load(1);
        });
    });
    more.addEventListener('click', () => { if (page < lastPage) void load(page + 1); });
});
</script>
@endsection
