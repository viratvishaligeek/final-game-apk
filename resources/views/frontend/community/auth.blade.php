@extends('frontend.include.app')
@section('title', ($mode === 'login' ? 'Member Login' : 'Create Member Account') . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('robots_content', 'noindex,follow')
@section('content')
<section class="page-banner page-banner--blue"><div class="wrap"><p class="eyebrow">MEMBER ACCESS</p><h1>{{ $mode === 'login' ? 'Sign in to the' : 'Join the' }} <span>community</span></h1><p>Use your existing member account. These credentials use the site's existing registered users.</p></div></section>
<section class="section-block section-light"><div class="wrap community-form-wrap">
<form class="community-form" method="POST" action="{{ $mode === 'login' ? route('community.login.submit') : route('community.register.submit') }}">@csrf
@if ($mode === 'register')<label for="name">Display name</label><input id="name" name="name" type="text" maxlength="200" minlength="2" value="{{ old('name') }}" required>@error('name')<small class="community-error">{{ $message }}</small>@enderror @endif
<label for="phone">Registered phone number</label><input id="phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" required>@error('phone')<small class="community-error">{{ $message }}</small>@enderror
<label for="password">Password</label><input id="password" name="password" type="password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" minlength="{{ $mode === 'register' ? 8 : 1 }}" required>@error('password')<small class="community-error">{{ $message }}</small>@enderror
@if ($mode === 'register')<label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>@endif
@if ($mode === 'login')<label class="community-checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>@endif
<button class="button button-gold" type="submit">{{ $mode === 'login' ? 'Sign in' : 'Create account' }} ↗</button>
<p>{{ $mode === 'login' ? 'Need an account?' : 'Already registered?' }} <a href="{{ route($mode === 'login' ? 'community.register' : 'community.login') }}">{{ $mode === 'login' ? 'Register' : 'Sign in' }}</a></p>
</form>
</div></section>
@endsection
