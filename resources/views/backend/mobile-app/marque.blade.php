@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon">
                                <i data-feather="type"></i>
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
        <form action="{{ route('admin.mobile-app.update_marque') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    Marquee Settings
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="marquee" class="small mb-1">
                            Marquee Text
                        </label>
                        <textarea class="form-control" id="marquee" name="marquee" rows="4" placeholder="Enter marquee text">{{ old('marquee', $settings['marquee'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button class="btn btn-primary btn-sm" type="submit">
                    <i data-feather="save" class="me-1"></i>
                    Update Marquee
                </button>
            </div>
        </form>
    </div>
@endsection
