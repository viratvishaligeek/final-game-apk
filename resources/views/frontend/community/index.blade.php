@extends('frontend.include.app')

@section('title', 'Guessing Community | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', 'Join game-specific Guessing discussions, read approved community posts, and share your thoughts.')
@section('robots_content', $posts->total() ? 'index,follow' : 'noindex,follow')

@section('content')
    <section class="bg-primary text-white py-5">
        <div class="container py-3">
            <p class="small fw-bold text-uppercase mb-2">✦ Guessing Community</p>
            <h1 class="display-5 fw-bold">Guessing Arena.<br>Your voice. Your community.</h1>
            <p class="lead col-lg-8">Share your thoughts, discuss game topics and connect with fellow members. Join the conversation and be part of the community.</p>
            <div class="d-flex flex-wrap gap-2 my-4">
                <a class="btn btn-warning" href="{{ route('community.create') }}">+ Create a post</a>
                <a class="btn btn-outline-light" href="#community-feed">Explore discussions ↓</a>
            </div>
            <div class="row g-3">
                <div class="col-6 col-lg-4"><div class="bg-white bg-opacity-10 rounded p-3 h-100"><strong class="h3 d-block">{{ number_format($posts->total()) }}</strong><span>Approved discussions</span></div></div>
                <div class="col-6 col-lg-4"><div class="bg-white bg-opacity-10 rounded p-3 h-100"><strong class="h3 d-block">{{ number_format($posts->sum('replies_count')) }}</strong><span>Replies on this page</span></div></div>
                <div class="col-12 col-lg-4"><div class="bg-white bg-opacity-10 rounded p-3 h-100"><strong class="h3 d-block">{{ $posts->count() }}</strong><span>Posts on this page</span></div></div>
            </div>
        </div>
    </section>

    <section id="community-feed" class="py-5 bg-light">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="status">✓ {{ session('success') }}</div>
            @endif

            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="h3 mb-1">Latest discussions</h2>
                    <p class="text-secondary mb-0">Read posts and join the conversation.</p>
                </div>
                <span class="badge text-bg-primary">{{ number_format($posts->total()) }} total posts</span>
            </div>

            <div class="row g-3">
                @forelse ($posts as $post)
                    <div class="col-12">
                        <article class="card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <span class="badge text-bg-primary">✦ {{ $post->game->name ?? 'Game discussion' }}</span>
                                    <time class="small text-secondary" datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</time>
                                </div>
                                <h3 class="h4"><a class="link-primary text-decoration-none" href="{{ route('community.show', $post->id) }}">{{ $post->title }}</a></h3>
                                <p class="text-body-secondary">{{ \Illuminate\Support\Str::limit($post->body, 320) }}</p>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top pt-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle bg-primary text-white p-2 fw-bold">{{ mb_strtoupper(mb_substr($post->author->name ?? 'U', 0, 1)) }}</span>
                                        <span class="small">By {{ $post->author->name ?? 'Community member' }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <span class="small text-secondary">{{ $post->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}</span>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('community.show', $post->id) }}">Open discussion ↗</a>
                                    </div>
                                </div>
                                <div class="bg-light rounded p-3 mt-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <strong>💬 Recent replies</strong>
                                        <small class="text-secondary">{{ $post->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}</small>
                                    </div>
                                    @forelse ($post->replies as $reply)
                                        <div class="d-flex gap-3 border-bottom pb-3 mb-3">
                                            <span class="rounded-circle bg-secondary text-white p-2 align-self-start">{{ mb_strtoupper(mb_substr($reply->author->name ?? 'U', 0, 1)) }}</span>
                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                                    <strong class="small">{{ $reply->author->name ?? 'Community member' }}</strong>
                                                    <time class="small text-secondary" datetime="{{ $reply->created_at->toIso8601String() }}">{{ $reply->created_at->timezone(config('app.timezone'))->diffForHumans() }}</time>
                                                </div>
                                                <p class="mb-0">{{ \Illuminate\Support\Str::limit($reply->body, 180) }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-secondary mb-0">No replies yet. Be the first to join this discussion!</p>
                                    @endforelse
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <small class="text-secondary">Showing {{ $post->replies->count() }} of {{ $post->replies_count }} replies</small>
                                        <a href="{{ route('community.show', $post->id) }}">View all replies →</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h3 class="h5">No discussions yet</h3>
                            <p>Start the first discussion by choosing an active game.</p>
                            <a class="btn btn-primary" href="{{ route('community.create') }}">+ Create a post</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-4">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
@endsection
