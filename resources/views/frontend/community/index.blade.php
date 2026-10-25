@extends('frontend.include.app')

@section('title', 'Guessing Community | ' . setting('title', 'Play Online Khaiwal'))

@section('meta_description', 'Join game-specific Guessing discussions, read approved community posts, and share your
    thoughts.')

@section('robots_content', $posts->total() ? 'index,follow' : 'noindex,follow')

@section('content')

    <style>
        .guess-arena {
            background: #080f1e;
            color: #edf4ff;
            min-height: 100vh;
            overflow: hidden
        }

        .guess-wrap {
            width: min(1100px, calc(100% - 32px));
            margin: auto
        }

        .guess-hero {
            position: relative;
            overflow: hidden;
            padding: 65px 0 45px;
            background: radial-gradient(ellipse at 15% 20%, #0874a744, transparent 45%), radial-gradient(ellipse at 90% 10%, #8147b955, transparent 40%), linear-gradient(135deg, #07101e, #111b38, #091425);
            border-bottom: 1px solid #253651
        }

        .guess-hero:before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            top: -130px;
            left: 25%;
            border-radius: 50%;
            background: #168bff;
            filter: blur(90px);
            opacity: .3;
            animation: guessfloat 8s ease-in-out infinite alternate;
            pointer-events: none
        }

        .guess-eyebrow {
            color: #67dcff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.5px;
            margin-bottom: 16px
        }

        .guess-hero h1 {
            font-size: clamp(36px, 6vw, 62px);
            font-weight: 900;
            line-height: 1.12;
            color: #fff;
            margin: 0;
            letter-spacing: -1px
        }

        .guess-hero h1 span {
            background: linear-gradient(90deg, #67dcff, #b09aff, #ffd477);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent
        }

        .guess-description {
            max-width: 620px;
            color: #b3c3dc;
            font-size: 14px;
            line-height: 1.9;
            margin: 18px 0 25px
        }

        .guess-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px
        }

        .guess-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 19px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none !important;
            transition: .2s
        }

        .guess-btn:hover {
            transform: translateY(-2px)
        }

        .guess-gold {
            background: linear-gradient(120deg, #ffe49e, #ffc45b);
            color: #182033 !important
        }

        .guess-outline {
            background: #ffffff09;
            border: 1px solid #ffffff20;
            color: #edf5ff !important
        }

        .guess-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 35px
        }

        .guess-stat {
            padding: 17px;
            background: #0d1d33cc;
            border: 1px solid #ffffff19;
            border-radius: 14px
        }

        .guess-stat strong {
            display: block;
            color: #fff;
            font-size: 23px
        }

        .guess-stat span {
            display: block;
            margin-top: 5px;
            color: #9cacc4;
            font-size: 11px
        }

        .guess-feed {
            padding: 40px 0 60px;
            background: radial-gradient(ellipse at 100% 10%, #4c4db118, transparent 40%), #080f1e
        }

        .guess-feed-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 23px
        }

        .guess-feed-heading h2 {
            margin: 0;
            color: #fff;
            font-size: 27px;
            font-weight: 850
        }

        .guess-feed-heading p {
            margin: 7px 0 0;
            color: #9cacc4;
            font-size: 12px
        }

        .guess-total {
            padding: 9px 12px;
            color: #a9e8ff;
            background: #4cbfff12;
            border: 1px solid #4cbfff30;
            border-radius: 9px;
            font-size: 11px
        }

        .guess-notice {
            padding: 13px 15px;
            color: #a9f5d4;
            background: #2fcd8e12;
            border: 1px solid #2fcd8e35;
            border-radius: 11px;
            margin-bottom: 20px
        }

        .guess-post {
            padding: 23px;
            margin-bottom: 20px;
            background: linear-gradient(145deg, #122139, #0c192c);
            border: 1px solid #ffffff19;
            border-radius: 16px;
            box-shadow: 0 12px 35px #0002;
            animation: guessenter .4s ease both;
            transition: .25s
        }

        .guess-post:hover {
            border-color: #67dcff66;
            transform: translateY(-2px)
        }

        .guess-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
            color: #9cacc4;
            font-size: 11px
        }

        .guess-topic {
            padding: 7px 10px;
            color: #91e4ff;
            background: #4abfff14;
            border: 1px solid #4abfff30;
            border-radius: 7px;
            font-weight: 800
        }

        .guess-title {
            margin: 0 0 10px;
            font-size: clamp(18px, 3vw, 23px);
            font-weight: 800;
            line-height: 1.45;
            overflow-wrap: anywhere
        }

        .guess-title a {
            color: #f5f7ff;
            text-decoration: none
        }

        .guess-title a:hover {
            color: #67dcff
        }

        .guess-body {

            text-align: center;
            margin: 0;
            color: #b8c7da;
            font-size: 25px;
            line-height: 1.9;
            /* white-space: pre-line; */
            overflow-wrap: anywhere
        }

        .guess-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 19px;
            padding-top: 16px;
            border-top: 1px solid #ffffff12
        }

        .guess-author {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #dce9fa;
            font-size: 12px;
            font-weight: 700
        }

        .guess-avatar {
            display: grid;
            flex-shrink: 0;
            width: 31px;
            height: 31px;
            place-items: center;
            border-radius: 50%;
            color: #a8eaff;
            background: linear-gradient(135deg, #204c70, #36336e);
            border: 1px solid #83c9ff44;
            font-weight: 900
        }

        .guess-footer-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px
        }

        .guess-count {
            color: #9cacc4;
            font-size: 11px
        }

        .guess-link {
            color: #ffd477 !important;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none !important
        }

        .guess-replies {
            margin-top: 19px;
            padding: 14px;
            background: #040c1978;
            border: 1px solid #ffffff12;
            border-radius: 12px
        }

        .guess-replies-heading {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            color: #e3efff;
            font-size: 12px;
            font-weight: 800
        }

        .guess-replies-heading small {
            color: #8fa6c2;
            font-size: 10px;
            font-weight: 500
        }

        .guess-reply {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 12px 0;
            border-bottom: 1px solid #ffffff10
        }

        .guess-reply:last-of-type {
            border-bottom: 0
        }

        .guess-reply .guess-avatar {
            width: 28px;
            height: 28px;
            font-size: 10px
        }

        .guess-reply-content {
            flex: 1;
            min-width: 0
        }

        .guess-reply-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 5px
        }

        .guess-reply-name {
            color: #8fe2ff;
            font-size: 11px;
            font-weight: 800;
            overflow-wrap: anywhere
        }

        .guess-reply-time {
            color: #8195af;
            font-size: 10px
        }

        .guess-reply-body {
            margin: 0;
            color: #bac9dc;
            font-size: 20px;
            line-height: 1.75;
            white-space: pre-line;
            overflow-wrap: anywhere
        }

        .guess-no-replies {
            margin: 0;
            padding: 8px 0;
            color: #91a3bb;
            font-size: 12px;
            line-height: 1.8
        }

        .guess-replies-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 10px
        }

        .guess-replies-bottom small {
            color: #91a3bb;
            font-size: 10px
        }

        .guess-empty {
            text-align: center;
            padding: 45px 20px;
            background: #101d32;
            border: 1px dashed #ffffff35;
            border-radius: 16px
        }

        .guess-empty h3 {
            color: #fff;
            font-size: 22px;
            margin: 0 0 10px
        }

        .guess-empty p {
            color: #9cacc4;
            font-size: 13px;
            margin-bottom: 20px
        }

        .guess-pagination {
            margin-top: 28px;
            overflow-x: auto
        }

        .guess-pagination nav {
            display: flex;
            justify-content: center
        }

        .guess-pagination nav span,
        .guess-pagination nav a {
            border-color: #293b55 !important
        }

        .guess-pagination nav a {
            color: #e1edff !important;
            background: #122139 !important
        }

        .guess-pagination nav span[aria-current=page] span {
            color: #101a2d !important;
            background: #67dcff !important;
            border-color: #67dcff !important
        }

        @keyframes guessfloat {
            to {
                transform: translate(35px, 25px) scale(1.2)
            }
        }

        @keyframes guessenter {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @media(max-width:640px) {
            .guess-hero {
                padding: 45px 0 32px
            }

            .guess-stats {
                gap: 7px;
                margin-top: 25px
            }

            .guess-stat {
                padding: 11px 8px
            }

            .guess-stat strong {
                font-size: 19px
            }

            .guess-stat span {
                font-size: 9px
            }

            .guess-post {
                padding: 16px
            }

            .guess-footer-links {
                width: 100%;
                justify-content: space-between
            }

            .guess-replies {
                padding: 10px
            }
        }

        @media(prefers-reduced-motion:reduce) {
            .guess-arena * {
                animation: none !important;
                transition: none !important
            }
        }
    </style>

    <div class="guess-arena">

        <section class="guess-hero">
            <div class="guess-wrap">
                <p class="guess-eyebrow">✦ &nbsp; GUESSING COMMUNITY</p>

                <h1>Guessing <span>Arena.</span><br>Your voice. Your community.</h1>

                <p class="guess-description">
                    Share your thoughts, discuss game topics and connect with
                    fellow members. Join the conversation and be part of the community.
                </p>

                <div class="guess-actions">
                    <a class="guess-btn guess-gold" href="{{ route('community.create') }}">
                        + Create a post
                    </a>
                    <a class="guess-btn guess-outline" href="#community-feed">
                        Explore discussions ↓
                    </a>
                </div>

                <div class="guess-stats">
                    <div class="guess-stat">
                        <strong>{{ number_format($posts->total()) }}</strong>
                        <span>Approved discussions</span>
                    </div>
                    <div class="guess-stat">
                        <strong>{{ number_format($posts->sum('replies_count')) }}</strong>
                        <span>Replies on this page</span>
                    </div>
                    <div class="guess-stat">
                        <strong>{{ $posts->count() }}</strong>
                        <span>Posts on this page</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="guess-feed" id="community-feed">
            <div class="guess-wrap">

                @if (session('success'))
                    <p class="guess-notice" role="status">
                        ✓ {{ session('success') }}
                    </p>
                @endif

                <div class="guess-feed-heading">
                    <div>
                        <h2>Latest discussions</h2>
                        <p>Read posts and join the conversation.</p>
                    </div>
                    <span class="guess-total">
                        {{ number_format($posts->total()) }} total posts
                    </span>
                </div>

                @forelse ($posts as $post)
                    <article class="guess-post">

                        <div class="guess-meta">
                            <span class="guess-topic">
                                ✦ {{ $post->game->name ?? 'Game discussion' }}
                            </span>
                            <time datetime="{{ $post->created_at->toIso8601String() }}">
                                {{ $post->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}
                            </time>
                        </div>

                        <h3 class="guess-title">
                            <a href="{{ route('community.show', $post->id) }}">
                                {{ $post->title }}
                            </a>
                        </h3>

                        <p class="guess-body">
                            {{ \Illuminate\Support\Str::limit($post->body, 320) }}
                        </p>

                        <div class="guess-footer">
                            <div class="guess-author">
                                <span class="guess-avatar">
                                    {{ mb_strtoupper(mb_substr($post->author->name ?? 'U', 0, 1)) }}
                                </span>
                                <span>By {{ $post->author->name ?? 'Community member' }}</span>
                            </div>

                            <div class="guess-footer-links">
                                <span class="guess-count">
                                    {{ $post->replies_count }}
                                    {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}
                                </span>
                                <a class="guess-link" href="{{ route('community.show', $post->id) }}">
                                    Open discussion ↗
                                </a>
                            </div>
                        </div>

                        <div class="guess-replies">
                            <div class="guess-replies-heading">
                                <div>💬 Recent replies</div>
                                <small>
                                    {{ $post->replies_count }}
                                    {{ \Illuminate\Support\Str::plural('reply', $post->replies_count) }}
                                </small>
                            </div>

                            @forelse ($post->replies as $reply)
                                <div class="guess-reply">
                                    <span class="guess-avatar">
                                        {{ mb_strtoupper(mb_substr($reply->author->name ?? 'U', 0, 1)) }}
                                    </span>

                                    <div class="guess-reply-content">
                                        <div class="guess-reply-meta">
                                            <span class="guess-reply-name">
                                                {{ $reply->author->name ?? 'Community member' }}
                                            </span>
                                            <time class="guess-reply-time"
                                                datetime="{{ $reply->created_at->toIso8601String() }}">
                                                {{ $reply->created_at->timezone(config('app.timezone'))->diffForHumans() }}
                                            </time>
                                        </div>

                                        <p class="guess-reply-body">{{ \Illuminate\Support\Str::limit($reply->body, 180) }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="guess-no-replies">
                                    No replies yet. Be the first to join this discussion!
                                </p>
                            @endforelse

                            <div class="guess-replies-bottom">
                                <small>
                                    Showing {{ $post->replies->count() }} of
                                    {{ $post->replies_count }} replies
                                </small>
                                <a class="guess-link" href="{{ route('community.show', $post->id) }}">
                                    View all replies →
                                </a>
                            </div>
                        </div>

                    </article>
                @empty
                    <div class="guess-empty">
                        <h3>No discussions yet</h3>
                        <p>Start the first discussion by choosing an active game.</p>
                        <a class="guess-btn guess-gold" href="{{ route('community.create') }}">
                            + Create a post
                        </a>
                    </div>
                @endforelse

                @if ($posts->hasPages())
                    <div class="guess-pagination">
                        {{ $posts->links() }}
                    </div>
                @endif

            </div>
        </section>
    </div>

@endsection
