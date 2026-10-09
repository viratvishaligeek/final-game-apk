@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="settings"></i></div>
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
        <form action="{{ route('admin.setting.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">Site Details</div>
                <div class="card-body row">
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="title">Site Title</label>
                        <input class="form-control" id="title" name="title" type="text"
                            placeholder="Enter site title"
                            value="{{ old('title', optional($setting->firstWhere('option', 'title'))->value) }}">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="email">Site Contact Email</label>
                        <input class="form-control" id="email" name="email" type="email"
                            placeholder="Enter contact email"
                            value="{{ old('email', optional($setting->firstWhere('option', 'email'))->value) }}">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="site_logo">Site Logo</label>
                        <input class="form-control" id="site_logo" name="site_logo" type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="site_favicon">Site Favicon</label>
                        <input class="form-control" id="site_favicon" name="site_favicon" type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="save"></i> Save Site Details
                    </button>
                </div>
            </div>
        </form>

        <form action="{{ route('admin.setting.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">Referral Rewards</div>
                <div class="card-body row">
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="referral_percentage">Referral Commission (%)</label>
                        <input class="form-control" id="referral_percentage" name="referral_percentage" type="number"
                            min="0" max="100" step="0.01" required
                            value="{{ old('referral_percentage', optional($setting->firstWhere('option', 'referral_percentage'))->value ?? 2) }}">
                        <small class="text-muted">Percentage of the referred user's first successful deposit.</small>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="referral_min_amount">Minimum Referral Reward (₹)</label>
                        <input class="form-control" id="referral_min_amount" name="referral_min_amount" type="number"
                            min="0" max="1000000" step="0.01" required
                            value="{{ old('referral_min_amount', optional($setting->firstWhere('option', 'referral_min_amount'))->value ?? 10) }}">
                        <small class="text-muted">The reward will not be lower than this amount.</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="save"></i> Save Referral Settings
                    </button>
                </div>
            </div>
        </form>

        <form action="{{ route('admin.update-password') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">Change Admin Password</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small mb-1" for="current_password">Current Password <span class="text-danger">*</span></label>
                        <input class="form-control" id="current_password" name="current_password" type="password"
                            autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1" for="password">New Password <span class="text-danger">*</span></label>
                        <input class="form-control" id="password" name="password" type="password"
                            autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1" for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="key"></i> Update Password
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
