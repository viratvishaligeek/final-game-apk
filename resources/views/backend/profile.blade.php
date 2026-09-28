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
        <div class="row">
            <!-- Account Details Card -->
            <div class="col-xl-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">
                        <form action="{{ route('admin.update-profile') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                                <input class="form-control" id="name" name="name" type="text"
                                    placeholder="Enter full name" value="{{ old('name', $admin->name) }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1" for="email">Email Address <span class="text-danger">*</span></label>
                                <input class="form-control" id="email" name="email" type="email"
                                    placeholder="Enter email address" value="{{ old('email', $admin->email) }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">Account Status</label>
                                <div>
                                    @if ($admin->status === 'active')
                                        <span class="badge bg-success-soft text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="me-1" data-feather="save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="col-xl-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Change Password</div>
                    <div class="card-body">
                        <form action="{{ route('admin.update-password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="small mb-1" for="current_password">Current Password <span class="text-danger">*</span></label>
                                <input class="form-control" id="current_password" name="current_password" type="password"
                                    placeholder="Enter current password" required />
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1" for="password">New Password <span class="text-danger">*</span></label>
                                <input class="form-control" id="password" name="password" type="password"
                                    placeholder="Enter new password" required />
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1" for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password"
                                    placeholder="Confirm new password" required />
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="me-1" data-feather="key"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
