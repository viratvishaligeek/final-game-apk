@extends('backend.include.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        <!-- Top Filter Bar -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Game Result Console</h2>
                <p class="text-muted mb-0">Declare today's live game results or update missing backdated entries.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <form action="{{ route('admin.results.index') }}" method="GET"
                    class="d-inline-flex align-items-center gap-2 bg-white p-2 rounded shadow-sm border">
                    <span class="fw-bold text-secondary ps-2">Date:</span>
                    <input type="date" name="date" class="form-control form-control-sm border-0 fw-bold text-primary"
                        value="{{ $selectedDate }}" onchange="this.form.submit()">
                    @if (\Carbon\Carbon::parse($selectedDate)->isToday())
                        <span class="badge bg-danger text-uppercase px-2 py-2">● Live Today</span>
                    @else
                        <span class="badge bg-secondary text-uppercase px-2 py-2">History</span>
                    @endif
                </form>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Game Results Grid/Table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Active Games ({{ \Carbon\Carbon::parse($selectedDate)->format('d M, Y') }})</h5>
                <span class="small text-light">Showing all {{ $allGames->count() }} active system games</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Serial</th>
                                <th>Game Name</th>
                                <th class="text-center">Declared Result</th>
                                <th class="text-center">Mode Status</th>
                                <th class="text-end pe-4">Quick Declaration / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allGames as $game)
                                @php
                                    $result = $existingResults->get($game->id);
                                    $hasValidResult = $result && $result->number !== 'Wait';
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#{{ $game->serial ?? $loop->iteration }}</td>
                                    <td>
                                        <span class="fw-bold text-dark fs-6">{{ $game->name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($hasValidResult)
                                            <span
                                                class="badge bg-success fs-5 px-3 py-2 shadow-sm">{{ $result->number }}</span>
                                        @elseif($result && $result->number === 'Wait')
                                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Wait</span>
                                        @else
                                            <span class="badge bg-light text-muted border px-3 py-2">No Record</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (\Carbon\Carbon::parse($selectedDate)->isToday())
                                            <span
                                                class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                Auto Winner & Payout
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">
                                                Record History Only
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <form action="{{ route('admin.results.storeOrUpdate') }}" method="POST"
                                                class="d-inline-flex align-items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="game_id" value="{{ $game->id }}">
                                                <input type="hidden" name="game_date" value="{{ $selectedDate }}">

                                                <input type="text" name="result"
                                                {{ $hasValidResult ? 'disabled' : '' }}
                                                    class="form-control form-control-sm text-center fw-bold shadow-sm"
                                                    style="width: 70px;" maxlength="2" placeholder="00"
                                                    value="{{ $hasValidResult ? $result->number : '' }}" pattern="\d{2}"
                                                    required>

                                                @if (!$hasValidResult)
                                                    <button type="submit" class="btn btn-sm btn-success shadow-sm">
                                                        Declare
                                                @endif
                                                </button>
                                            </form>

                                            @if ($hasValidResult)
                                                <form action="{{ route('admin.results.revert') }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to revert this result to Wait? User balances will be adjusted.');"
                                                    class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="game_id" value="{{ $game->id }}">
                                                    <input type="hidden" name="game_date" value="{{ $selectedDate }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm"
                                                        title="Revert Result">
                                                        Reset
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No active games available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
