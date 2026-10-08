@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="user"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <form class="row" action="{{ route('admin.setting.store') }}" method="POST">
            <div class="col-xl-12 mb-4">
                <div class="card h-100">
                    <div class="card-header">Account Details</div>
                    <div class="card-body row">
                        @csrf
                        <div class="col-lg-6 mb-3">
                            <label class="small mb-1" for="title">Site Title <span class="text-danger">*</span></label>
                            <input class="form-control" id="title" name="title" type="text"
                                placeholder="Enter full title" value="{{ old('title') }}" />
                        </div>
                        <div class="col-lg-6  mb-3">
                            <label class="small mb-1" for="email">Site Keyword <span class="text-danger">*</span></label>
                            <input class="form-control" id="email" name="email" type="email"
                                placeholder="Enter email address" value="{{ old('email') }}" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Change Password</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small mb-1" for="current_password">Current Password <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="current_password" name="current_password" type="password"
                                placeholder="Enter current password" />
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="password">New Password <span class="text-danger">*</span></label>
                            <input class="form-control" id="password" name="password" type="password"
                                placeholder="Enter new password" />
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="password_confirmation">Confirm New Password <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation"
                                type="password" placeholder="Confirm new password" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 text-center">
                <button class="btn-sm btn btn-primary" type="submit">
                    <i class="me-1" data-feather="key"></i> Update Settings
                </button>
            </div>
        </form>
    </div>
@endsection
