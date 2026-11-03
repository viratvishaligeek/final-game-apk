@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="shield"></i></div>
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
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-4">
                <div class="card-header">Edit Role Name</div>
                <div class="card-body">
                    <div class="row gx-3">
                        <div class="col-md-6">
                            <label class="small mb-1" for="name">Role Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                type="text" value="{{ old('name', $role->name) }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <!-- Permissions Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Assign Permissions</span>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-bold text-primary" for="selectAll">Select All Permissions</label>
                    </div>
                </div>
                <div class="card-body">
                    @if ($groupedPermissions->isNotEmpty())
                        @foreach ($groupedPermissions as $group => $permissions)
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <h6 class="fw-bold mb-0 text-uppercase text-dark">{{ $group }} Module</h6>
                                    <div class="form-check">
                                        <input class="form-check-input group-select" type="checkbox"
                                            id="group-{{ Str::slug($group) }}" data-group="{{ Str::slug($group) }}">
                                        <label class="form-check-label small fw-semibold"
                                            for="group-{{ Str::slug($group) }}">Select Module</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-2 col-sm-4 mb-2">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input permission-checkbox group-item-{{ Str::slug($group) }}"
                                                    type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    id="perm-{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="perm-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No permissions found for admin guard.</p>
                    @endif

                    <div class="mt-4">
                        <button class="btn btn-primary" type="submit">
                            <i class="me-1" data-feather="save"></i> Update Role & Permissions
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            const $selectAll = $('#selectAll');
            const $permissionCheckboxes = $('.permission-checkbox');
            const $groupSelects = $('.group-select');

            function updateSelectAllState() {
                if ($selectAll.length) {
                    $selectAll.prop('checked', $permissionCheckboxes.toArray().every(cb => cb.checked));
                }
            }

            $selectAll.on('change', function () {
                const checked = $(this).prop('checked');
                $permissionCheckboxes.add($groupSelects).prop('checked', checked);
            });

            $groupSelects.on('change', function () {
                const groupSlug = $(this).data('group');
                $('.group-item-' + groupSlug).prop('checked', $(this).prop('checked'));
                updateSelectAllState();
            });

            $permissionCheckboxes.on('change', updateSelectAllState);
            updateSelectAllState();
        });
    </script>
@endsection
