@extends('frontend.include.app')
@section('title', 'Guessing Community | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', 'Join game-specific Guessing discussions, read approved community posts, and share your thoughts.')
@section('robots_content', $posts->total() ? 'index,follow' : 'noindex,follow')
@section('content')
<section class="page-banner page-banner--blue"><div class="wrap"><p class="eyebrow">COMMUNITY DISCUSSION</p><h1>Guessing <span>community</span></h1><p>Discuss a game topic with other members. Posts are displayed only after approval unless automatic approval is enabled.</p><div class="community-actions"><a class="button button-gold" href="{{ route('community.create') }}">Create a post ↗</a></div></div></section>
<section class="section-block section-light"><div class="wrap">
    @if (session('success'))<p class="community-notice" role="status">{{ session('success') }}</p>@endif
    @forelse ($posts as $post)
        <article class="community-post"><div class="community-post-meta"><span class="community-topic">{{ $post->game->name }}</span><span>{{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</span></div><h2><a href="{{ route('community.show', $post->id) }}">{{ $post->title }}</a></h2><p class="community-post-body">{{ \Illuminate\Support\Str::limit($post->body, 320) }}</p><div class="community-post-foot"><span>By {{ $post->author->name }}</span><span>{{ $post->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}</span><a href="{{ route('community.show', $post->id) }}">Open discussion ↗</a></div></article>
    @empty
        <div class="empty-state"><b>No approved community posts yet.</b><p>Start a discussion by choosing an active game and submitting a post.</p><a class="button button-dark" href="{{ route('community.create') }}">Create a post</a></div>
    @endforelse
    <nav class="mt-4" aria-label="Community feed pagination">{{ $posts->links() }}</nav>
</div></section>
@endsection
