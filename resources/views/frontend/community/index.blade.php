@extends('frontend.include.app')

@section('title', 'Guessing Community | ' . setting('title', 'Play Online Khaiwal'))

@section('meta_description', 'Join game-specific Guessing discussions, read approved community posts, and share your
    thoughts.')

@section('robots_content', $posts->total() ? 'index,follow' : 'noindex,follow')

@section('content')

    

    <div>

        <section>
            <div>
                <p>✦ &nbsp; GUESSING COMMUNITY</p>

                <h1>Guessing <span>Arena.</span><br>Your voice. Your community.</h1>

                <p>
                    Share your thoughts, discuss game topics and connect with
                    fellow members. Join the conversation and be part of the community.
                </p>

                <div>
                    <a href="{{ route('community.create') }}">
                        + Create a post
                    </a>
                    <a href="#community-feed">
                        Explore discussions ↓
                    </a>
                </div>

                <div>
                    <div>
                        <strong>{{ number_format($posts->total()) }}</strong>
                        <span>Approved discussions</span>
                    </div>
                    <div>
                        <strong>{{ number_format($posts->sum('replies_count')) }}</strong>
                        <span>Replies on this page</span>
                    </div>
                    <div>
                        <strong>{{ $posts->count() }}</strong>
                        <span>Posts on this page</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="community-feed">
            <div>

                @if (session('success'))
                    <p role="status">
                        ✓ {{ session('success') }}
                    </p>
                @endif

                <div>
                    <div>
                        <h2>Latest discussions</h2>
                        <p>Read posts and join the conversation.</p>
                    </div>
                    <span>
                        {{ number_format($posts->total()) }} total posts
                    </span>
                </div>

                @forelse ($posts as $post)
                    <article>

                        <div>
                            <span>
                                ✦ {{ $post->game->name ?? 'Game discussion' }}
                            </span>
                            <time datetime="{{ $post->created_at->toIso8601String() }}">
                                {{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}
                            </time>
                        </div>

                        <h3>
                            <a href="{{ route('community.show', $post->id) }}">
                                {{ $post->title }}
                            </a>
                        </h3>

                        <p>
                            {{ \Illuminate\Support\Str::limit($post->body, 320) }}
                        </p>

                        <div>
                            <div>
                                <span>
                                    {{ mb_strtoupper(mb_substr($post->author->name ?? 'U', 0, 1)) }}
                                </span>
                                <span>By {{ $post->author->name ?? 'Community member' }}</span>
                            </div>

                            <div>
                                <span>
                                    {{ $post->replies_count }}
                                    {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}
                                </span>
                                <a href="{{ route('community.show', $post->id) }}">
                                    Open discussion ↗
                                </a>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div>💬 Recent replies</div>
                                <small>
                                    {{ $post->replies_count }}
                                    {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}
                                </small>
                            </div>

                            @forelse ($post->replies as $reply)
                                <div>
                                    <span>
                                        {{ mb_strtoupper(mb_substr($reply->author->name ?? 'U', 0, 1)) }}
                                    </span>

                                    <div>
                                        <div>
                                            <span>
                                                {{ $reply->author->name ?? 'Community member' }}
                                            </span>
                                            <time
                                                datetime="{{ $reply->created_at->toIso8601String() }}">
                                                {{ $reply->created_at->timezone(config('app.timezone'))->diffForHumans() }}
                                            </time>
                                        </div>

                                        <p>{{ \Illuminate\Support\Str::limit($reply->body, 180) }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p>
                                    No replies yet. Be the first to join this discussion!
                                </p>
                            @endforelse

                            <div>
                                <small>
                                    Showing {{ $post->replies->count() }} of
                                    {{ $post->replies_count }} replies
                                </small>
                                <a href="{{ route('community.show', $post->id) }}">
                                    View all replies →
                                </a>
                            </div>
                        </div>

                    </article>
                @empty
                    <div>
                        <h3>No discussions yet</h3>
                        <p>Start the first discussion by choosing an active game.</p>
                        <a href="{{ route('community.create') }}">
                            + Create a post
                        </a>
                    </div>
                @endforelse

                @if ($posts->hasPages())
                    <div>
                        {{ $posts->links() }}
                    </div>
                @endif

            </div>
        </section>
    </div>

@endsection
