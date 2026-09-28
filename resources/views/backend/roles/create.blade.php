@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="shield-plus"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.roles.index') }}">
                            <i class="me-1" data-feather="arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">Role Details</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="name">Role Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                                placeholder="e.g. manager, editor, accountant" value="{{ old('name') }}" required autofocus />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">Role name will be converted to lowercase automatically.</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="check"></i> Save Role
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
