<div class="section-heading {{ $class ?? '' }}">
    <div><p class="eyebrow">{{ $eyebrow ?? 'RESULT DIRECTORY' }}</p><h2>{!! $title !!}</h2>@isset($description)<p class="section-description">{{ $description }}</p>@endisset</div>
    @isset($action)<a class="text-action" href="{{ $action['url'] }}">{{ $action['label'] }} <span aria-hidden="true">↗</span></a>@endisset
</div>