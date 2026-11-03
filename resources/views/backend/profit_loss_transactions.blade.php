@extends('backend.include.layout')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">{{ ucfirst($source) }} Wallet {{ ucfirst($type) }}s</h1>
                <p class="text-muted mb-0">Completed wallet ledger movements. These totals are informational and are not added again to game profit/loss or approved request totals.</p>
            </div>
            <a href="{{ route('admin.bidding-desk.profit_loss', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-secondary">Back to Profit / Loss</a>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="source" value="{{ $source }}">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="from">From date</label>
                        <input class="form-control" type="date" id="from" name="from" value="{{ $from }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="to">To date</label>
                        <input class="form-control" type="date" id="to" name="to" value="{{ $to }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary" type="submit">Apply filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Source</th>
                            <th>Admin</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Remark</th>
                            <th>Status</th>
                            <th>Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>#{{ $transaction->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->user?->name ?? 'Deleted user' }}</div>
                                    <small class="text-muted">ID: {{ $transaction->user_id }} · {{ $transaction->user?->phone ?? '-' }}</small>
                                </td>
                                <td>{{ $transaction->admin_id ? 'Admin' : 'User / System' }}</td>
                                <td>{{ $transaction->admin?->name ?? ($transaction->admin_id ? 'Admin #' . $transaction->admin_id : '—') }}</td>
                                <td><span class="badge {{ $type === 'credit' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($type) }}</span></td>
                                <td class="text-end fw-bold {{ $type === 'credit' ? 'text-success' : 'text-danger' }}">₹{{ number_format((float) $transaction->amount, 2) }}</td>
                                <td>{{ $transaction->subject ?: '-' }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($transaction->status) }}</span></td>
                                <td>{{ $transaction->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-5">No completed admin wallet adjustments found for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="card-footer bg-white">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
@endsection
