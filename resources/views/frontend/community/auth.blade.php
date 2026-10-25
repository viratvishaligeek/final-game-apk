@extends('frontend.include.app')
@section('title', ($mode === 'login' ? 'Member Login' : 'Create Member Account') . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="bg-primary text-white py-5 bg-primary text-white py-5"><div class="container"><p class="small text-uppercase fw-bold text-primary mb-2">MEMBER ACCESS</p><h1>{{ $mode === 'login' ? 'Sign in to the' : 'Join the' }} <span>community</span></h1><p>Use your existing member account. These credentials use the site's existing registered users.</p></div></section>
<section class="py-5 bg-light"><div class="container container py-4">
<form class="card p-4 mx-auto gap-3" method="POST" action="{{ $mode === 'login' ? route('community.login.submit') : route('community.register.submit') }}">@csrf
@if ($mode === 'register')<label for="name">Display name</label><input id="name" name="name" type="text" maxlength="200" minlength="2" value="{{ old('name') }}" required>@error('name')<small class="text-danger small">{{ $message }}</small>@enderror @endif
<label for="phone">Registered phone number</label><input id="phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" required>@error('phone')<small class="text-danger small">{{ $message }}</small>@enderror
<label for="password">Password</label><input id="password" name="password" type="password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" minlength="{{ $mode === 'register' ? 8 : 1 }}" required>@error('password')<small class="text-danger small">{{ $message }}</small>@enderror
@if ($mode === 'register')<label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>@endif
@if ($mode === 'login')<label><input type="checkbox" name="remember" value="1"> Remember me</label>@endif
<btn class="btn btn btn-warning" type="submit">{{ $mode === 'login' ? 'Sign in' : 'Create account' }} ↗</btn>
<p>{{ $mode === 'login' ? 'Need an account?' : 'Already registered?' }} <a href="{{ route($mode === 'login' ? 'community.register' : 'community.login') }}">{{ $mode === 'login' ? 'Register' : 'Sign in' }}</a></p>
</form>
</div></section>
@endsection
