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

        <form action="{{ route('admin.setting.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">Frontend Content, SEO & Results Chart</div>
                <div class="card-body row">
                    @php($globalFields = [
                        'site_description' => ['Site Description', 'textarea'],
                        'homepage_heading_line1' => ['Homepage Heading — Line 1', 'text'],
                        'homepage_heading_line2' => ['Homepage Heading — Line 2', 'text'],
                        'homepage_heading_line3' => ['Homepage Heading — Line 3', 'text'],
                        'homepage_intro' => ['Homepage Introduction', 'textarea'],
                        'announcement_text' => ['Announcement Text', 'textarea'],
                        'meta_title' => ['Default SEO Title', 'text'],
                        'meta_description' => ['Default Meta Description', 'textarea'],
                        'meta_keywords' => ['Default SEO Keywords', 'textarea'],
                        'copyright_text' => ['Copyright Text', 'text'],
                        'footer_description' => ['Footer Description', 'textarea'],
                        'contact_phone' => ['Public Contact Phone', 'text'],
                        'contact_address' => ['Public Contact Address', 'textarea'],
                        'social_facebook' => ['Facebook URL', 'url'],
                        'social_instagram' => ['Instagram URL', 'url'],
                        'social_youtube' => ['YouTube URL', 'url'],
                        'social_telegram' => ['Telegram URL', 'url'],
                        'ticker_text' => ['Header Ticker Text', 'textarea'],
                        'disclaimer_content' => ['Shared Disclaimer (plain text)', 'textarea'],
                    ])
                    @foreach ($globalFields as $key => $field)
                        <div class="col-lg-6 mb-3">
                            <label class="small mb-1" for="{{ $key }}">{{ $field[0] }}</label>
                            @if ($field[1] === 'textarea')
                                <textarea class="form-control" id="{{ $key }}" name="{{ $key }}" rows="3">{{ old($key, optional($setting->firstWhere('option', $key))->value) }}</textarea>
                            @else
                                <input class="form-control" id="{{ $key }}" name="{{ $key }}" type="{{ $field[1] }}"
                                    value="{{ old($key, optional($setting->firstWhere('option', $key))->value) }}">
                            @endif
                            @error($key)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    @endforeach
                    <div class="col-lg-6 mb-3">
                        <label class="small mb-1" for="chart_chunk_size">Monthly Chart Games Per Page</label>
                        <input class="form-control" id="chart_chunk_size" name="chart_chunk_size" type="number" min="1" max="50"
                            value="{{ old('chart_chunk_size', optional($setting->firstWhere('option', 'chart_chunk_size'))->value ?? 10) }}">
                        <small class="text-muted">Choose 1–50 markets per page. Invalid or missing values use 10.</small>
                        @error('chart_chunk_size')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit"><i class="me-1" data-feather="save"></i> Save Frontend Settings</button>
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
