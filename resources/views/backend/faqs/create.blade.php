@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="plus-circle"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.faqs.index') }}">
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
        <form action="{{ route('admin.faqs.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">FAQ Information</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-12 mb-3">
                            <label class="small mb-1" for="question">Question <span class="text-danger">*</span></label>
                            <input class="form-control" id="question" name="question" type="text"
                                placeholder="Enter question" value="{{ old('question') }}" required autofocus />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="small mb-1" for="answer">Answer <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="answer" name="answer" rows="5"
                                placeholder="Enter answer details" required>{{ old('answer') }}</textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="check"></i> Save FAQ
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
