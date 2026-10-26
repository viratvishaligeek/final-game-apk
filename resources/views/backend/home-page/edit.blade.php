@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon">
                                <i data-feather="edit" class="text-primary"></i>
                            </div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a href="{{ route('admin.home-page.index') }}" class="btn btn-sm btn-light text-secondary">
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

        <form action="{{ route('admin.home-page.update', $homePage->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="me-2" data-feather="file-text"></i>
                            Basic Information
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="small mb-1">
                                    Title
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control " id="title" name="title"
                                    value="{{ old('title', $homePage->title) }}">
                            </div>
                            <div class="mb-3">
                                <label for="content" class="small mb-1">
                                    Content
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea id="content" name="content" class="form-control ">{{ old('content', $homePage->content) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="me-2" data-feather="phone"></i>
                            Contact Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="small mb-1">
                                        Phone
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="phone" name="phone"
                                        value="{{ old('phone', $homePage->phone) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="whatsapp" class="small mb-1">
                                        WhatsApp
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="whatsapp" name="whatsapp"
                                        value="{{ old('whatsapp', $homePage->whatsapp) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telegram" class="small mb-1">
                                        Telegram
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="telegram" name="telegram"
                                        value="{{ old('telegram', $homePage->telegram) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="me-2" data-feather="settings"></i>
                            Section Settings
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="location" class="small mb-1">
                                    Location <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="location" name="location">
                                    <option value="">Select Location</option>
                                    @foreach ($locations as $slug => $title)
                                        <option value="{{ $slug }}"
                                            {{ old('location', $homePage->location) === $slug ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="small mb-1">
                                    Status
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select " id="status" name="status">
                                    <option value="active"
                                        {{ old('status', $homePage->status) === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive"
                                        {{ old('status', $homePage->status) === 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="background" class="small mb-1">
                                    Background
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control " id="background" name="background"
                                    value="{{ old('background', $homePage->background) }}">
                                <div class="form-text">
                                    Enter a color, CSS value, class name, or background identifier.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="me-1" data-feather="save"></i>
                                Update Section
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </form>

    </div>
@endsection

@section('script')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        .note-editor.note-frame .note-editing-area .note-editable {
            min-height: 300px;
        }

        .note-editor {
            margin-bottom: 1rem;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#content').summernote({
                placeholder: 'Enter page content here...',
                height: 300,
                minHeight: 300,
                dialogsInBody: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection
