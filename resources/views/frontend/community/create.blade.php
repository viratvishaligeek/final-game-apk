@extends('frontend.include.app')
@section('title', 'Create Guessing Post | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="bg-primary text-white py-5"><div class="container py-3"><p class="small fw-bold text-uppercase mb-2">Community discussion</p><h1 class="display-6 fw-bold">Create a Guessing post</h1><p class="lead">Choose an active game topic and write a clear, respectful post. Your content is plain text and may require administrator approval.</p></div></section>
<section class="py-5 bg-light">
    <div class="container">
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        <form class="card p-4 p-md-5 mx-auto" method="POST" action="{{ route('community.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="game_id">Game topic</label><select class="form-select" id="game_id" name="game_id" required><option value="">Select a game</option>@foreach ($games as $game)<option value="{{ $game->id }}" {{ (string) old('game_id') === (string) $game->id ? 'selected' : '' }}>{{ $game->name }}</option>@endforeach</select>@error('game_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label" for="title">Post title</label><input class="form-control" id="title" name="title" type="text" maxlength="120" minlength="4" value="{{ old('title') }}" required>@error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label" for="body">Your post</label><textarea class="form-control" id="body" name="body" rows="7" minlength="10" maxlength="5000" required>{{ old('body') }}</textarea><div class="form-text">10–5,000 characters. HTML is not supported.</div>@error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            <button class="btn btn-warning align-self-start" type="submit">Submit post ↗</button>
        </form>
    </div>
</section>
@endsection
