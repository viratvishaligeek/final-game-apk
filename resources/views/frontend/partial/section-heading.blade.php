<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><p class="small text-uppercase fw-bold text-primary mb-2">{{ $eyebrow ?? 'RESULT DIRECTORY' }}</p><h2>{!! $title !!}</h2>@isset($description)<p class="text-secondary">{{ $description }}</p>@endisset</div>
    @isset($action)<a class="btn btn-outline-primary" href="{{ $action['url'] }}">{{ $action['label'] }} <span aria-hidden="true">↗</span></a>@endisset
</div>