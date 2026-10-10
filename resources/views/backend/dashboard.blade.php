@extends('backend.include.layout')

@section('content')
    <br>
    <div class="row">
        <a href="{{ route('admin.users.index') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">Users</div>
                            <div class="text-lg fw-bold">
                                {{ number_format($totalUsers) }}
                            </div>
                        </div>
                        <i class="feather-xl text-success" data-feather="users"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Games --}}
        <a href="{{ route('admin.games.index') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">Games</div>
                            <div class="text-lg fw-bold">
                                {{ number_format($totalGames) }}
                            </div>
                        </div>
                        <i class="feather-xl text-danger" data-feather="play-circle"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Today Total Bids --}}
        <a href="{{ route('admin.bidding-desk.index') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Today Total Bids
                            </div>
                            <div class="text-lg fw-bold">
                                {{ number_format($todayTotalBids) }}
                            </div>
                        </div>
                        <i class="feather-xl text-success" data-feather="check-square"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Today Bid Amount --}}
        <a href="{{ route('admin.bidding-desk.index') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Today Bid Amount
                            </div>
                            <div class="text-lg fw-bold">
                                ₹{{ number_format((float) $todayBidAmount, 2) }}
                            </div>
                        </div>
                        <i class="feather-xl text-danger" data-feather="message-circle"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Pages --}}
        <a href="{{ route('admin.pages.index') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Pages
                            </div>
                            <div class="text-lg fw-bold">
                                {{ number_format($totalPages) }}
                            </div>
                        </div>
                        <i class="feather-xl text-primary" data-feather="calendar"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Pending Withdraw --}}
        <a href="{{ route('admin.wallet.request-withdraw') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Pending Withdraw
                            </div>
                            <div class="text-lg fw-bold">
                                ₹{{ number_format((float) $pendingWithdraw, 2) }}
                            </div>
                        </div>
                        <i class="feather-xl text-danger" data-feather="arrow-down-left"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Complete Withdraw --}}
        <a href="{{ route('admin.wallet.request-withdraw') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Complete Withdraw
                            </div>
                            <div class="text-lg fw-bold">
                                ₹{{ number_format((float) $completeWithdraw, 2) }}
                            </div>
                        </div>
                        <i class="feather-xl text-success" data-feather="check-square"></i>
                    </div>
                </div>
            </div>
        </a>
        {{-- Pending Add Money --}}
        <a href="{{ route('admin.wallet.request-add') }}" class="col-lg-3 col-sm-6 col-6 col-xl-3 mb-2">
            <div class="card bg-white text-black h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-dark small fw-bold">
                                Pending Add Money
                            </div>
                            <div class="text-lg fw-bold">
                                ₹{{ number_format((float) $pendingAddMoney, 2) }}
                            </div>
                        </div>
                        <i class="feather-xl text-danger" data-feather="battery-charging"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="row">
        {{-- Withdraw Chart --}}
        <div class="col-12 col-md-6  mb-4">
            <div class="card card-header-actions h-100">
                <div class="card-header">
                    Withdraw Completed
                    <div class="dropdown no-caret">
                        <button class="btn text-white bg-primary btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="more-vertical"></i>
                            Sort
                        </button>
                        <div class="dropdown-menu dropdown-menu-end animated--fade-in-up">
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '12months']) }}">
                                Last 12 Months
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '30days']) }}">
                                Last 30 Days
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '7days']) }}">
                                Last 7 Days
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => 'month']) }}">
                                This Month
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="withdrawChart" width="100%" height="30"></canvas>
                    </div>
                </div>
            </div>
        </div>
        {{-- Money Added Chart --}}
        <div class="col-12 col-md-6  mb-4">
            <div class="card card-header-actions h-100">
                <div class="card-header">
                    Money Added Completed
                    <div class="dropdown no-caret">
                        <button class="btn text-white bg-primary btn-sm dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="more-vertical"></i>
                            Sort
                        </button>
                        <div class="dropdown-menu dropdown-menu-end animated--fade-in-up">
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '12months']) }}">
                                Last 12 Months
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '30days']) }}">
                                Last 30 Days
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => '7days']) }}">
                                Last 7 Days
                            </a>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['period' => 'month']) }}">
                                This Month
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="moneyAddedChart" width="100%" height="30"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Today Winners</span>
            <div>
                <span class="badge bg-success text-white rounded-pill">
                    {{ number_format($todayWinnerCount) }} Winners
                </span>
                <span class="badge bg-primary text-white rounded-pill">
                    ₹{{ number_format((float) $todayWinningAmount, 2) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>User Name</th>
                            <th>Bid Number / Type</th>
                            <th>Bid Amount</th>
                            <th>Winning Amount</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Game</th>
                            <th>User Name</th>
                            <th>Bid Number / Type</th>
                            <th>Bid Amount</th>
                            <th>Winning Amount</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse($todayWinners as $winner)
                            <tr>
                                <td>
                                    {{ $winner->game?->name ?? 'N/A' }}
                                </td>
                                <td>
                                    <div class="fw-bold">
                                        {{ $winner->user?->name ?? 'N/A' }}
                                    </div>
                                    @if ($winner->user?->phone)
                                        <small class="text-muted">
                                            {{ $winner->user->phone }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">
                                        {{ $winner->number }}
                                    </span>
                                    <span class="badge bg-primary text-white rounded-pill ms-1">
                                        {{ strtoupper($winner->type) }}
                                    </span>
                                </td>
                                <td>
                                    ₹{{ number_format((float) $winner->amount, 2) }}
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ₹{{ number_format((float) $winner->winning_amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="badge bg-primary text-white rounded-pill">
                                        {{ $winner->created_at?->format('h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.winner.index', [
                                        'date' => today()->toDateString(),
                                        'game_id' => $winner->game_id,
                                    ]) }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark" title="View Winner">
                                        <i class="fa-regular fa-eye text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No winners found for today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = @json($chartLabels);
            const withdrawValues = @json($withdrawValues);
            const moneyAddedValues = @json($moneyAddedValues);
            const formattedLabels = labels.map(function(label) {
                if (label.length === 7) {
                    const parts = label.split('-');
                    const date = new Date(
                        parseInt(parts[0]),
                        parseInt(parts[1]) - 1,
                        1
                    );
                    return date.toLocaleDateString('en-IN', {
                        month: 'short',
                        year: 'numeric'
                    });
                }
                const date = new Date(label + 'T00:00:00');
                return date.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short'
                });
            });
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return ' ₹' +
                                Number(tooltipItem.yLabel)
                                .toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return '₹' +
                                    Number(value)
                                    .toLocaleString('en-IN');
                            }
                        }
                    }]
                },
                legend: {
                    display: false
                }
            };
            const withdrawCanvas =
                document.getElementById('withdrawChart');
            if (withdrawCanvas) {
                new Chart(withdrawCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: formattedLabels,
                        datasets: [{
                            label: 'Withdraw Completed',
                            data: withdrawValues,
                            backgroundColor: 'rgba(220, 53, 69, 0.10)',
                            borderColor: '#dc3545',
                            borderWidth: 2,
                            pointBackgroundColor: '#dc3545',
                            pointBorderColor: '#dc3545',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: true,
                            lineTension: 0.3
                        }]
                    },
                    options: commonOptions
                });
            }
            const moneyAddedCanvas =
                document.getElementById('moneyAddedChart');
            if (moneyAddedCanvas) {
                new Chart(moneyAddedCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: formattedLabels,
                        datasets: [{
                            label: 'Money Added',
                            data: moneyAddedValues,
                            backgroundColor: 'rgba(13, 110, 253, 0.75)',
                            borderColor: '#0d6efd',
                            borderWidth: 1
                        }]
                    },
                    options: commonOptions
                });
            }
            const table =
                document.getElementById('datatablesSimple');
            if (table && typeof simpleDatatables !== 'undefined') {
                new simpleDatatables.DataTable(table, {
                    searchable: true,
                    perPage: 10,
                    perPageSelect: [10, 25, 50]
                });
            }
        });
    </script>
@endsection
