@extends('backend.include.layout')
@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon">
                                <i data-feather="award"></i>
                            </div>
                            {{ $pageName }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection
@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <div class="me-2 text-primary">
                    <i data-feather="filter"></i>
                </div>
                <h6 class="mb-0">
                    Winner Filters
                </h6>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.winner.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold text-gray-600 mb-1">
                            Winner Date
                        </label>
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                    </div>

                    <div class="col-md-3">
                        <label class="small fw-bold text-gray-600 mb-1">
                            Game
                        </label>
                        <select name="game_id" class="form-select">
                            <option value="">
                                All Games
                            </option>
                            @foreach ($games as $game)
                                <option value="{{ $game->id }}" {{ request('game_id') == $game->id ? 'selected' : '' }}>
                                    {{ $game->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="small fw-bold text-gray-600 mb-1">
                            Type
                        </label>
                        <select name="type" class="form-select">
                            <option value="">
                                All Types
                            </option>
                            <option value="jodi" {{ request('type') == 'jodi' ? 'selected' : '' }}>
                                Jodi
                            </option>
                            <option value="haruf" {{ request('type') == 'haruf' ? 'selected' : '' }}>
                                Haruf
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="small fw-bold text-gray-600 mb-1">
                            Customer
                        </label>
                        <input type="text" name="user" class="form-control" placeholder="Name or phone..."
                            value="{{ request('user') }}">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" title="Apply Filter">
                            <i data-feather="search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= SUMMARY CARDS ================= --}}
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="card border-start-lg border-start-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-primary mb-1">
                                TOTAL WINNERS
                            </div>
                            <div class="h4 mb-0">
                                {{ number_format($totalWinners) }}
                            </div>
                            <div class="small text-muted mt-1">
                                {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                            </div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-primary-soft text-primary">
                                <i data-feather="users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-3">
            <div class="card border-start-lg border-start-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-success mb-1">
                                TOTAL WINNING AMOUNT
                            </div>
                            <div class="h4 mb-0">
                                ₹{{ number_format($totalWinningAmount, 2) }}
                            </div>
                            <div class="small text-muted mt-1">
                                {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                            </div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-success-soft text-success">
                                <i data-feather="trending-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= WINNER TABLE ================= --}}
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="me-2 text-primary">
                        <i data-feather="award"></i>
                    </div>
                    <h6 class="mb-0">
                        Winner History
                    </h6>
                </div>
                <span class="badge bg-primary-soft text-primary">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Customer</th>
                        <th>Game</th>
                        <th>Number</th>
                        <th>Type</th>
                        <th>Bid Amount</th>
                        <th>Winning Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Serial</th>
                        <th>Customer</th>
                        <th>Game</th>
                        <th>Number</th>
                        <th>Type</th>
                        <th>Bid Amount</th>
                        <th>Winning Amount</th>
                        <th>Date</th>
                    </tr>
                </tfoot>
                <tbody>
                    @forelse ($winners as $winner)
                        <tr>
                            <td>#{{ $loop->iteration }}</td>
                            <td>
                                @if ($winner->user)
                                    <a href="{{ url('/admin/users/' . $winner->user->id) }}" class="text-decoration-none">
                                        <div class="fw-bold text-dark">
                                            {{ $winner->user->name }} |
                                            {{ $winner->user->phone }}
                                        </div>
                                    </a>
                                @else
                                    <div class="fw-bold text-muted">
                                        User Deleted
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($winner->game)
                                    <span class="fw-bold">
                                        {{ $winner->game->name }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        Deleted Game
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge bg-primary-soft text-primary">
                                    {{ str_pad($winner->number, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td>
                                @if ($winner->type === 'jodi')
                                    <span class="badge bg-primary-soft text-primary">
                                        Jodi
                                    </span>
                                @elseif ($winner->type === 'haruf')
                                    <span class="badge bg-warning-soft text-warning">
                                        Haruf
                                    </span>
                                @else
                                    <span class="badge bg-secondary-soft text-secondary">
                                        {{ ucfirst($winner->type) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">
                                    ₹{{ number_format((float) $winner->amount, 2) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-success-soft text-success">
                                    ₹{{ number_format((float) $winner->winning_amount, 2) }}
                                </span>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($winner->game_date)->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-center py-5">
                                <div class="text-muted">
                                    <i data-feather="award" class="mb-2" style="width: 40px; height: 40px;">
                                    </i>
                                    <div class="fw-bold">
                                        No Winners Found
                                    </div>
                                    <small>
                                        No winner records found for
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}.
                                    </small>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/datatables/datatables-simple-demo.js"></script>
@endsection
