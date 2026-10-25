@extends('frontend.include.app')
@section('title', $post->title . ' | Guessing Community')
@section('meta_description', \Illuminate\Support\Str::limit($post->body, 155))
@section('robots_content', 'index,follow')
@section('content')
<section class="bg-primary text-white py-5"><div class="container py-3">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a class="link-light" href="{{ route('community.index') }}">Guessing</a></li><li class="breadcrumb-item active text-white" aria-current="page">{{ $post->game->name }}</li></ol></nav>
    <p class="small fw-bold text-uppercase mb-2">Game topic · {{ $post->game->name }}</p><h1 class="display-6 fw-bold">{{ $post->title }}</h1><p class="mb-0">Posted by {{ $post->author->name }} · {{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p>
</div></section>
<section class="py-5 bg-light"><div class="container">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <article class="card p-4 mb-4"><p class="fs-5 mb-0">{{ $post->body }}</p></article>
    <h2 class="h4 mb-3">Replies ({{ $post->replies_count }})</h2>
    @forelse ($post->replies as $reply)
        <article class="card p-3 mb-3"><p class="mb-2">{{ $reply->body }}</p><small class="text-secondary">{{ $reply->author->name }} · {{ $reply->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</small></article>
    @empty
        <div class="alert alert-light">No replies yet. Be the first to join the discussion.</div>
    @endforelse
    <form class="card p-4 mt-4" method="POST" action="{{ route('community.reply', $post->id) }}">
        @csrf
        <div class="mb-3"><label class="form-label" for="body">Add a reply</label><textarea class="form-control" id="body" name="body" rows="4" minlength="2" maxlength="2000" required>{{ old('body') }}</textarea>@error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <button class="btn btn-warning align-self-start" type="submit">Post reply ↗</button>
    </form>
</div></section>
@endsection
