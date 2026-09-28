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
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.member.index') }}">
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
        <form action="{{ route('admin.member.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-4">
                <div class="card-header">Edit Personal Information</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                type="text" value="{{ old('name', $member->name) }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="email">Email Address <span
                                    class="text-danger">*</span></label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                type="email" value="{{ old('email', $member->email) }}" required />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="role">Assign Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                                required>
                                <option value="" disabled>Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $memberRole) == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                <option value="active" {{ old('status', $member->status) == 'active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="inactive"
                                    {{ old('status', $member->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="password">Password <small class="text-muted">(Leave blank to keep
                                    existing)</small></label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password"
                                name="password" type="password" placeholder="Enter new password" />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="password_confirmation">Confirm Password</label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation"
                                type="password" placeholder="Confirm new password" />
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Update Member</button>
                </div>
            </div>
        </form>
    </div>
@endsection
