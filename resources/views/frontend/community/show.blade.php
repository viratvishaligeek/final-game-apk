@extends('frontend.include.app')
@section('title', $post->title . ' | Guessing Community')
@section('meta_description', \Illuminate\Support\Str::limit($post->body, 155))
@section('robots_content', 'index,follow')
@section('content')
<section class="bg-primary text-white py-5 bg-primary text-white py-5"><div class="container"><div class="small mb-3"><a href="{{ route('community.index') }}">Guessing</a><span>/</span><b>{{ $post->game->name }}</b></div><p class="small text-uppercase fw-bold text-primary mb-2">GAME TOPIC · {{ $post->game->name }}</p><h1>{{ $post->title }}</h1><p>Posted by {{ $post->author->name }} · {{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p></div></section>
<section class="py-5 bg-light"><div class="container container py-4">
    @if (session('success'))<p class="alert alert-success" role="status">{{ session('success') }}</p>@endif
    <article class="card p-3 mb-3 card p-4"><p class="fs-5">{{ $post->body }}</p></article>
    <h2 class="h4 mt-4">Replies ({{ $post->replies_count }})</h2>
    @forelse ($post->replies as $reply)<article class="card p-3 mb-3"><p>{{ $reply->body }}</p><small>{{ $reply->author->name }} · {{ $reply->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</small></article>@empty<p class="alert alert-info">No replies yet. Be the first to join the discussion.</p>@endforelse
    <form class="card p-4 mx-auto" method="POST" action="{{ route('community.reply', $post->id) }}">@csrf<label for="body">Add a reply</label><textarea id="body" name="body" rows="4" minlength="2" maxlength="2000" required>{{ old('body') }}</textarea>@error('body')<small class="text-danger small">{{ $message }}</small>@enderror<button class="btn btn btn-warning" type="submit">Post reply ↗</button></form>
</div></section>
@endsection
