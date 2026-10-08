@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon">
                                <i data-feather="bell"></i>
                            </div>
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
        <form action="{{ route('admin.mobile-app.update_notice') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    Notice Settings
                </div>
                <div class="card-body">
                    <div class="row gx-3">
                        <div class="col-md-4 mb-3">
                            <label for="notice_status" class="small mb-1">
                                Status
                            </label>
                            <select class="form-select" id="notice_status" name="notice_status" required>
                                <option value="active"
                                    {{ old('notice_status', $settings['notice_status'] ?? 'inactive') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive"
                                    {{ old('notice_status', $settings['notice_status'] ?? 'inactive') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="admin_notice" class="small mb-1">
                                Admin Notice
                            </label>
                            <textarea class="form-control" id="admin_notice" name="admin_notice" rows="6"
                                placeholder="Enter notice for users">{{ old('admin_notice', $settings['admin_notice'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary" type="submit">
                    <i data-feather="save" class="me-1"></i>
                    Save Notice
                </button>
            </div>
        </form>
    </div>
@endsection
