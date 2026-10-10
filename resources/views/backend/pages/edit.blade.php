@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="edit"></i></div>
                            {{ $pageName }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.pages.index') }}">
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
        <div class="card mb-4">
            <div class="card-header">Edit Page Details</div>
            <div class="card-body">
                <form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row gx-3 mb-3">
                        <div class="col-md-8 mb-3">
                            <label class="small mb-1" for="name">Page Title <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" name="name" type="text"
                                value="{{ old('name', $page->name) }}" required />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $page->status) === 'active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="inactive"
                                    {{ old('status', $page->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-none">
                            <label class="small mb-1" for="is_editable">is_editable <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="is_editable" name="is_editable" required>
                                <option value="yes"
                                    {{ old('is_editable', $page->is_editable) === 'yes' ? 'selected' : '' }}>
                                    yes</option>
                                <option value="no"
                                    {{ old('is_editable', $page->is_editable) === 'no' ? 'selected' : '' }}>no</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1" for="editor">Page Content</label>
                        <textarea class="form-control" id="editor" name="content">{{ old('content', $page->content) }}</textarea>
                    </div>
                    <div class="row gx-3 mb-3">
                        <div class="col-md-3 mb-3"><label class="small mb-1" for="menu_visible">Show in public menu</label><select class="form-select" id="menu_visible" name="menu_visible"><option value="1" {{ old('menu_visible', $page->menu_visible ?? true) ? 'selected' : '' }}>Yes</option><option value="0" {{ !old('menu_visible', $page->menu_visible ?? true) ? 'selected' : '' }}>No</option></select></div>
                        <div class="col-md-3 mb-3"><label class="small mb-1" for="menu_order">Menu order</label><input class="form-control" id="menu_order" name="menu_order" type="number" min="0" max="100000" value="{{ old('menu_order', $page->menu_order ?? 0) }}"></div>
                        <div class="col-md-3 mb-3"><label class="small mb-1" for="noindex">Search indexing</label><select class="form-select" id="noindex" name="noindex"><option value="0" {{ !old('noindex', $page->noindex ?? false) ? 'selected' : '' }}>Index page</option><option value="1" {{ old('noindex', $page->noindex ?? false) ? 'selected' : '' }}>No index</option></select></div>
                    </div>
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6 mb-3"><label class="small mb-1" for="meta_title">SEO title</label><input class="form-control" id="meta_title" name="meta_title" maxlength="255" value="{{ old('meta_title', $page->meta_title) }}"></div>
                        <div class="col-md-6 mb-3"><label class="small mb-1" for="meta_keywords">SEO keywords</label><input class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}"></div>
                        <div class="col-12 mb-3"><label class="small mb-1" for="meta_description">SEO description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                    </div>
                    <button class="btn btn-primary" type="submit">Update Page</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- CKEditor 5 CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable_inline {
            min-height: 300px;
        }
    </style>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endsection
