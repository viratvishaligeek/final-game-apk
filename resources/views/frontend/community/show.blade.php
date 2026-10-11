@extends('frontend.include.app')
@section('title', $post->title . ' | Guessing Community')
@section('meta_description', \Illuminate\Support\Str::limit($post->body, 155))
@section('robots_content', 'index,follow')
@section('content')
<section class="page-banner page-banner--blue"><div class="wrap"><div class="breadcrumbs"><a href="{{ route('community.index') }}">Guessing</a><span>/</span><b>{{ $post->game->name }}</b></div><p class="eyebrow">GAME TOPIC · {{ $post->game->name }}</p><h1>{{ $post->title }}</h1><p>Posted by {{ $post->author->name }} · {{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p></div></section>
<section class="section-block section-light"><div class="wrap community-form-wrap">
    @if (session('success'))<p class="community-notice" role="status">{{ session('success') }}</p>@endif
    <article class="community-post community-post-detail"><p class="community-post-body">{{ $post->body }}</p></article>
    <h2 class="community-replies-heading">Replies ({{ $post->replies_count }})</h2>
    @forelse ($post->replies as $reply)<article class="community-reply"><p>{{ $reply->body }}</p><small>{{ $reply->author->name }} · {{ $reply->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</small></article>@empty<p class="empty-state">No replies yet. Be the first to join the discussion.</p>@endforelse
    <form class="community-form" method="POST" action="{{ route('community.reply', $post->id) }}">@csrf<label for="body">Add a reply</label><textarea id="body" name="body" rows="4" minlength="2" maxlength="2000" required>{{ old('body') }}</textarea>@error('body')<small class="community-error">{{ $message }}</small>@enderror<button class="button button-gold" type="submit">Post reply ↗</button></form>
</div></section>
@endsection
