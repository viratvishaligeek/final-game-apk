@extends('backend.auth.layout')
@section('content')
    <div class="container-xl px-4">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header justify-content-center">
                        <h3 class="fw-light my-4">Login</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('try_login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="small mb-1" for="inputEmailAddress">Email</label>
                                <input class="form-control" name="email" id="inputEmailAddress" type="email"
                                    value="{{ old('email') }}" placeholder="Enter email address" />
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1" for="inputPassword">Password</label>
                                <input class="form-control" id="inputPassword" type="password" name="password"
                                    value="{{ old('password') }}" placeholder="Enter password" />
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" id="rememberPasswordCheck" type="checkbox"
                                        value="" />
                                    <label class="form-check-label" for="rememberPasswordCheck">Remember
                                        password</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                <a class="small" href="auth-password-basic.html">Forgot Password?</a>
                                <button type="submit" class="btn btn-primary">Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
