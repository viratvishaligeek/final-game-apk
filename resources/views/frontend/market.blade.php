@extends('frontend.include.app')
@section('title', $game->name . ' ' . $monthLabel . ' Satta King Result Chart | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', 'Browse saved date-wise Satta King results for ' . $game->name . ' in ' . $monthLabel . '. Choose a month and year to load that market\'s records.')
@section('meta_keywords', $game->name . ', Satta King result, monthly result chart')
@section('robots_content', request()->query->count() > 0 ? 'noindex,follow' : setting('robots_default', 'index,follow'))
@section('content')
<section class="page-banner page-banner--blue">
    <div class="wrap">
        <div class="breadcrumbs"><a href="{{ route('index') }}">Home</a><span>/</span><a href="{{ route('index') }}#markets">Markets</a><span>/</span><b>{{ $game->name }}</b></div>
        <p class="eyebrow">GAME RESULT</p>
        <h1>{{ $game->name }} <span>{{ $monthLabel }}</span></h1>
        <p>Monthly results are loaded from saved records. Missing values are shown as a dash.</p>
    </div>
</section>
<section class="section-block section-light">
    <div class="wrap">
        <div class="page-toolbar result-month-toolbar">
            <h2>{{ $game->name }} · <span data-month-label>{{ $monthLabel }}</span></h2>
            <form method="GET" action="{{ route('frontend.market', ['slug' => $game->slug]) }}" class="monthly-chart-filter result-month-filter" data-result-filter data-endpoint="{{ route('frontend.chart') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label for="result-year">Year</label>
                <select class="form-select" id="result-year" name="year" data-result-year>
                    @foreach ($availableYears as $yearOption)
                        <option value="{{ $yearOption }}" {{ (int) $yearOption === (int) $year ? 'selected' : '' }}>{{ $yearOption }}</option>
                    @endforeach
                </select>
                <label for="result-month">Month</label>
                <select class="form-select" id="result-month" name="month" data-result-month>
                    @for ($monthOption = 1; $monthOption <= 12; $monthOption++)
                        <option value="{{ $monthOption }}" {{ $monthOption === (int) $month ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate($year, $monthOption, 1, config('app.timezone'))->translatedFormat('F') }}</option>
                    @endfor
                </select>
                <button class="button button-dark" type="submit">Load results</button>
            </form>
        </div>
        <p class="result-fetch-status" data-result-status role="status" aria-live="polite" hidden></p>
        <div class="empty-state" data-result-empty {{ $results->contains(fn (array $row) => $row['status'] === 'published') ? 'hidden' : '' }}>
            <b>No published results for this month.</b>
            <p>Choose another month or year to review saved records.</p>
        </div>
        <div class="table-shell light-table">
            <div class="table-scroll result-month-table-scroll" role="region" aria-label="{{ $game->name }} monthly result table" tabindex="0">
                <table class="quick-record-table monthly-result-table">
                    <thead><tr><th scope="col">Date</th><th scope="col">Result</th></tr></thead>
                    <tbody data-result-body>
                        @foreach ($results as $entry)
                            <tr data-result-row="{{ $entry['date']->day }}">
                                <th scope="row">{{ $entry['date']->format('d') }} {{ $monthLabel }}</th>
                                <td data-result-cell>{{ $entry['number'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script>
(function () {
    const form = document.querySelector('[data-result-filter]');
    if (!form) return;
    const yearSelect = form.querySelector('[data-result-year]');
    const monthSelect = form.querySelector('[data-result-month]');
    const csrf = form.querySelector('input[name="_token"]').value;
    const status = document.querySelector('[data-result-status]');
    const empty = document.querySelector('[data-result-empty]');
    const tbody = document.querySelector('[data-result-body]');
    const monthLabel = document.querySelector('[data-month-label]');
    let activeController = null;
    let requestNumber = 0;

    function setStatus(message, isError) {
        status.hidden = !message;
        status.textContent = message || '';
        status.classList.toggle('is-error', Boolean(isError));
    }

    async function loadResults() {
        const thisRequest = ++requestNumber;
        if (activeController) activeController.abort();
        activeController = new AbortController();
        setStatus('Loading results…', false);
        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                signal: activeController.signal,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    slug: @json($game->slug),
                    year: yearSelect.value,
                    month: monthSelect.value
                })
            });
            let payload = {};
            try { payload = await response.json(); } catch (parseError) { payload = {}; }
            if (thisRequest !== requestNumber) return;
            if (!response.ok) throw new Error(payload.message || 'Unable to load results. Please choose a valid month and year.');

            tbody.replaceChildren();
            let hasPublished = false;
            payload.days.forEach(function (day) {
                const row = document.createElement('tr');
                row.dataset.resultRow = String(day.day);
                const dateCell = document.createElement('th');
                dateCell.scope = 'row';
                dateCell.textContent = String(day.day).padStart(2, '0') + ' ' + payload.month_label;
                const resultCell = document.createElement('td');
                resultCell.dataset.resultCell = '';
                resultCell.textContent = day.result === null ? '—' : String(day.result);
                if (day.result !== null) hasPublished = true;
                row.append(dateCell, resultCell);
                tbody.appendChild(row);
            });
            monthLabel.textContent = payload.month_label;
            empty.hidden = hasPublished;
            setStatus(hasPublished ? 'Results updated.' : 'No published results for this month.', false);
            history.replaceState(null, '', form.action + '?year=' + encodeURIComponent(yearSelect.value) + '&month=' + encodeURIComponent(monthSelect.value));
        } catch (error) {
            if (error.name === 'AbortError' || thisRequest !== requestNumber) return;
            setStatus(error.message || 'A network error occurred. Please try again.', true);
        } finally {
            if (thisRequest === requestNumber) form.removeAttribute('aria-busy');
        }
    }

    yearSelect.addEventListener('change', loadResults);
    monthSelect.addEventListener('change', loadResults);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        loadResults();
    });
})();
</script>
@endsection
