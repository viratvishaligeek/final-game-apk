@extends('frontend.include.app')
@section('title', 'Create Guessing Post | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="page-banner page-banner--blue"><div class="wrap"><p class="eyebrow">COMMUNITY DISCUSSION</p><h1>Create a <span>Guessing post</span></h1><p>Choose an active game topic and write a clear, respectful post. Your content is plain text and may require administrator approval.</p></div></section>
<section class="section-block section-light"><div class="wrap community-form-wrap">
    @if (session('success'))<p class="community-notice" role="status">{{ session('success') }}</p>@endif
    <form class="community-form" method="POST" action="{{ route('community.store') }}">@csrf
        <label for="game_id">Game topic</label><select id="game_id" name="game_id" required><option value="">Select a game</option>@foreach ($games as $game)<option value="{{ $game->id }}" {{ (string) old('game_id') === (string) $game->id ? 'selected' : '' }}>{{ $game->name }}</option>@endforeach</select>@error('game_id')<small class="community-error">{{ $message }}</small>@enderror
        <label for="title">Post title</label><input id="title" name="title" type="text" maxlength="120" minlength="4" value="{{ old('title') }}" required>@error('title')<small class="community-error">{{ $message }}</small>@enderror
        <label for="body">Your post</label><textarea id="body" name="body" rows="7" minlength="10" maxlength="5000" required>{{ old('body') }}</textarea><small>10–5,000 characters. HTML is not supported.</small>@error('body')<small class="community-error">{{ $message }}</small>@enderror
        <button class="button button-gold" type="submit">Submit post ↗</button>
    </form>
</div></section>
@endsection
