@extends('backend.include.layout')

@section('content')
    <div class="container-fluid ">
        <div class="container-xl  mt-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-header-title"> Profit / Loss History </h1>
                    <p class="page-header-subtitle mb-0"> View game-wise bidding, winning and profit/loss summary. </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="icon-stack icon-stack-sm bg-primary text-white me-2"> <i class="fas fa-calendar"></i>
                        </div> <span class="fw-500">Filter Report</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bidding-desk.profit_loss') }}">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3 mb-md-0"> <label class="form-label" for="from"> From Date </label>
                                <input type="date" class="form-control" id="from" name="from"
                                    value="{{ $from }}">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0"> <label class="form-label" for="to"> To Date </label>
                                <input type="date" class="form-control" id="to" name="to"
                                    value="{{ $to }}">
                            </div>
                            <div class="col-md-4"> <button type="submit" class="btn btn-primary"> <i
                                        class="fas fa-filter me-1"></i> Apply Filter </button>
                                <a href="{{ route('admin.bidding-desk.profit_loss') }}" class="btn btn-light"> <i
                                        class="fas fa-sync-alt me-1"></i> Reset </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="small text-muted mb-1"> Total Add Balance </div>
                                    <div class="h4 mb-0 text-success"> ₹ {{ number_format($totalUser ?? 0, 2) }} </div>
                                    <div class="small text-muted mt-2"> Approved wallet credits </div>
                                </div>
                                <div class="icon-stack icon-stack-lg bg-success-soft text-success"> <i
                                        class="fas fa-wallet"></i> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="small text-muted mb-1"> Total Withdrawal Request </div>
                                    <div class="h4 mb-0 text-warning"> ₹ {{ number_format($totalMatch ?? 0, 2) }} </div>
                                    <div class="small text-muted mt-2"> Debit requests </div>
                                </div>
                                <div class="icon-stack icon-stack-lg bg-warning-soft text-warning"> <i
                                        class="fas fa-money-bill-wave"></i> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="small text-muted mb-1"> Paid Withdrawal </div>
                                    <div class="h4 mb-0 text-primary"> ₹ {{ number_format($totalTrans ?? 0, 2) }} </div>
                                    <div class="small text-muted mt-2"> Approved withdrawals </div>
                                </div>
                                <div class="icon-stack icon-stack-lg bg-primary-soft text-primary"> <i
                                        class="fas fa-check-circle"></i> </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- Profit Loss Table --}}
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="card-title mb-1">
                                Game Wise Profit / Loss
                            </h2>
                            <div class="small text-muted">
                                {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                                -
                                {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary-soft text-primary">
                                {{ count($gameStats) }} Games
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="profitLossTable" width="100%">
                            <thead class="bg-light">
                                <tr>
                                    <th width="70">#</th>
                                    <th>Game Name</th>
                                    <th class="text-end">Total Bid</th>
                                    <th class="text-end">Total Win</th>
                                    <th class="text-end">Profit / Loss</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($gameStats as $game)
                                    <tr>
                                        <td>
                                            <span class="text-muted">
                                                {{ $loop->iteration }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-stack icon-stack-sm bg-primary-soft text-primary me-2">
                                                    <i class="fas fa-gamepad"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-500">
                                                        {{ $game['name'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-500 text-dark">
                                                ₹ {{ number_format($game['total_bid'], 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-500 text-danger">
                                                ₹ {{ number_format($game['total_win'], 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if ($game['profit_loss'] > 0)
                                                <span class="badge bg-success-soft text-success px-3 py-2">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    ₹ {{ number_format($game['profit_loss'], 2) }}
                                                </span>
                                            @elseif ($game['profit_loss'] < 0)
                                                <span class="badge bg-danger-soft text-danger px-3 py-2">
                                                    <i class="fas fa-arrow-down me-1"></i>
                                                    - ₹ {{ number_format(abs($game['profit_loss']), 2) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-soft text-secondary px-3 py-2">
                                                    <i class="fas fa-minus me-1"></i>
                                                    ₹ 0.00
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <div class="icon-stack icon-stack-xl bg-light text-muted mb-3">
                                                    <i class="fas fa-chart-line"></i>
                                                </div>
                                                <h5 class="mb-1">
                                                    No Data Found
                                                </h5>
                                                <p class="mb-0">
                                                    There is no profit/loss data for the selected date range.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($gameStats->count() > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="2" class="text-end">
                                            Grand Total
                                        </th>
                                        <th class="text-end">
                                            <span class="fw-bold text-dark">
                                                ₹ {{ number_format($grandTotalBid, 2) }}
                                            </span>
                                        </th>
                                        <th class="text-end">
                                            <span class="fw-bold text-danger">
                                                ₹ {{ number_format($grandTotalWin, 2) }}
                                            </span>
                                        </th>
                                        <th class="text-end">
                                            @if ($grandProfitLoss > 0)
                                                <span class="fw-bold text-success">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    ₹ {{ number_format($grandProfitLoss, 2) }}
                                                </span>
                                            @elseif ($grandProfitLoss < 0)
                                                <span class="fw-bold text-danger">
                                                    <i class="fas fa-arrow-down me-1"></i>
                                                    - ₹ {{ number_format(abs($grandProfitLoss), 2) }}
                                                </span>
                                            @else
                                                <span class="fw-bold text-secondary">
                                                    ₹ 0.00
                                                </span>
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

            </div>
        </div>

    @endsection

    @section('page_script')
        {{-- DataTables --}}
        <script src="{{ URL::asset('backend/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ URL::asset('backend/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                $('#profitLossTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    order: [
                        [1, 'asc']
                    ],
                    language: {
                        search: "INPUT",
                        searchPlaceholder: "Search game..."
                    },
                    columnDefs: [{
                        targets: [2, 3, 4],
                        className: 'text-end'
                    }]
                });

            });
        </script>
    @endsection
