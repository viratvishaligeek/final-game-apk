@extends('frontend.include.app')
@section('title', 'Create Guessing Post | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="bg-primary text-white py-5 bg-primary text-white py-5"><div class="container"><p class="small text-uppercase fw-bold text-primary mb-2">COMMUNITY DISCUSSION</p><h1>Create a <span>Guessing post</span></h1><p>Choose an active game topic and write a clear, respectful post. Your content is plain text and may require administrator approval.</p></div></section>
<section class="py-5 bg-light"><div class="container container py-4">
    @if (session('success'))<p class="alert alert-success" role="status">{{ session('success') }}</p>@endif
    <form class="card p-4 mx-auto gap-3" method="POST" action="{{ route('community.store') }}">@csrf
        <label for="game_id">Game topic</label><select id="game_id" name="game_id" required><option value="">Select a game</option>@foreach ($games as $game)<option value="{{ $game->id }}" {{ (string) old('game_id') === (string) $game->id ? 'selected' : '' }}>{{ $game->name }}</option>@endforeach</select>@error('game_id')<small class="text-danger small">{{ $message }}</small>@enderror
        <label for="title">Post title</label><input id="title" name="title" type="text" maxlength="120" minlength="4" value="{{ old('title') }}" required>@error('title')<small class="text-danger small">{{ $message }}</small>@enderror
        <label for="body">Your post</label><textarea id="body" name="body" rows="7" minlength="10" maxlength="5000" required>{{ old('body') }}</textarea><small>10–5,000 characters. HTML is not supported.</small>@error('body')<small class="text-danger small">{{ $message }}</small>@enderror
        <btn class="btn btn btn-warning" type="submit">Submit post ↗</btn>
    </form>
</div></section>
@endsection
