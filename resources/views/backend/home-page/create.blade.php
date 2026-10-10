@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"> <i data-feather="home"></i> </div> {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3"> <a href="{{ route('admin.home-page.index') }}"
                            class="btn btn-sm btn-light text-secondary"> <i class="me-1" data-feather="arrow-left"></i>
                            Back </a> </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <form action="{{ route('admin.home-page.store') }}" method="POST">@csrf
            <div class="row">
                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header"> <i class="me-2" data-feather="file-text"></i> Basic Information </div>
                        <div class="card-body">
                            <div class="mb-3"> <label for="title" class="small mb-1"> Title <span
                                        class="text-danger">*</span> </label>
                                <input type="text" class="form-control " id="title" name="title"
                                    value="{{ old('title') }}" placeholder="Enter section title" required>
                            </div>
                            <div class="mb-3">
                                <label for="short_desc" class="small mb-1"> Short Description <span
                                        class="text-danger">*</span> </label>
                                <textarea class="form-control " id="short_desc" name="short_desc" rows="5" placeholder="Enter short description">{{ old('short_desc') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="small mb-1"> Content <span class="text-danger">*</span>
                                </label>
                                <textarea id="content" name="content" class="form-control ">{{ old('content') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header"> <i class="me-2" data-feather="phone"></i> Contact Information </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="small mb-1"> Phone <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="phone" name="phone"
                                        value="{{ old('phone') }}" placeholder="Enter phone number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="whatsapp" class="small mb-1"> WhatsApp <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="whatsapp" name="whatsapp"
                                        value="{{ old('whatsapp') }}" placeholder="Enter WhatsApp number/link">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telegram" class="small mb-1"> Telegram <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control " id="telegram" name="telegram"
                                        value="{{ old('telegram') }}" placeholder="Enter Telegram username/link">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header"> <i class="me-2" data-feather="settings"></i> Section Settings </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="location" class="small mb-1"> Location <span class="text-danger">*</span>
                                </label>
                                <select class="form-select " id="location" name="location" required>
                                    @foreach ($locations as $slug => $title)
                                        <option value="{{ $slug }}"
                                            {{ old('location') === $slug ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="small mb-1"> Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select " id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                        Active </option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                        Inactive </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="background" class="small mb-1"> Background <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control " id="background" name="background"
                                    value="{{ old('background') }}" placeholder="e.g. #f8f9fa or CSS class">
                                <div class="form-text"> Enter a color, CSS value, class name, or background identifier.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="me-1" data-feather="save"></i> Create Section
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <style>
        .ck-editor__editable_inline {
            min-height: 300px;
        }

        .ck-editor {
            width: 100%;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contentEditor = document.querySelector('#content');
            if (contentEditor) {
                ClassicEditor.create(contentEditor).catch(error => {
                    console.error(error);
                });
            }

        });
    </script>
@endsection
