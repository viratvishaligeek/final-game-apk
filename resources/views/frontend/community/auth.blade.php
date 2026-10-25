@extends('frontend.include.app')
@section('title', ($mode === 'login' ? 'Member Login' : 'Create Member Account') . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="bg-primary text-white py-5">
    <div class="container py-3">
        <p class="small fw-bold text-uppercase mb-2">Member access</p>
        <h1 class="display-6 fw-bold">{{ $mode === 'login' ? 'Sign in to the' : 'Join the' }} community</h1>
        <p class="lead">Use your existing member account. These credentials use the site's existing registered users.</p>
    </div>
</section>
<section class="py-5 bg-light">
    <div class="container">
        <form class="card p-4 p-md-5 mx-auto" method="POST" action="{{ $mode === 'login' ? route('community.login.submit') : route('community.register.submit') }}">
            @csrf
            @if ($mode === 'register')
                <div class="mb-3"><label class="form-label" for="name">Display name</label><input class="form-control" id="name" name="name" type="text" maxlength="200" minlength="2" value="{{ old('name') }}" required>@error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            @endif
            <div class="mb-3"><label class="form-label" for="phone">Registered phone number</label><input class="form-control" id="phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" required>@error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" minlength="{{ $mode === 'register' ? 8 : 1 }}" required>@error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            @if ($mode === 'register')
                <div class="mb-3"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
            @endif
            @if ($mode === 'login' && $errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <button class="btn btn-warning w-100" type="submit">{{ $mode === 'login' ? 'Sign in' : 'Create account' }} ↗</button>
            <p class="text-center mt-3 mb-0">{{ $mode === 'login' ? 'Need an account?' : 'Already registered?' }} <a href="{{ route($mode === 'login' ? 'community.register' : 'community.login') }}">{{ $mode === 'login' ? 'Register' : 'Sign in' }}</a></p>
        </form>
    </div>
</section>
@endsection
