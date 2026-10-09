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
                        <a class="btn btn-sm btn-light text-secondary" href="{{ route('admin.users.index') }}">
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
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Personal Details --}}
            <div class="card mb-4">
                <div class="card-header">Edit Personal Information</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="phone">Phone Number <span class="text-danger">*</span></label>
                            <input class="form-control" id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="password">New Password <small class="text-muted">(Leave blank if unchanged)</small></label>
                            <input class="form-control" id="password" name="password" type="password" placeholder="Enter new password" />
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="gender">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="city">City</label>
                            <input class="form-control" id="city" name="city" type="text" value="{{ old('city', $user->city) }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="balance">Balance <span class="text-danger">*</span></label>
                            <input class="form-control" id="balance" name="balance" type="number" value="{{ old('balance', $user->balance) }}" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive / Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small mb-1" for="address">Full Address</label>
                        <input class="form-control" id="address" name="address" type="text" value="{{ old('address', $user->address) }}" />
                    </div>
                </div>
            </div>

            {{-- Bank & Payment Info --}}
            <div class="card mb-4">
                <div class="card-header">Banking & UPI Payment Details</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="bank">Bank Name</label>
                            <input class="form-control" id="bank" name="bank" type="text" value="{{ old('bank', $user->bank_name) }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="acc">Account Number</label>
                            <input class="form-control" id="acc" name="acc" type="text" value="{{ old('acc', $user->account_number) }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="ifsc">IFSC Code</label>
                            <input class="form-control" id="ifsc" name="ifsc" type="text" value="{{ old('ifsc', $user->ifsc_code) }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="holdername">Account Holder Name</label>
                            <input class="form-control" id="holdername" name="holdername" type="text" value="{{ old('holdername', $user->account_holder_name) }}" />
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="phonepe">PhonePe Number</label>
                            <input class="form-control" id="phonepe" name="phonepe" type="text" value="{{ old('phonepe', $user->phonepe) }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="gpay">Google Pay Number</label>
                            <input class="form-control" id="gpay" name="gpay" type="text" value="{{ old('gpay', $user->gpay) }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="paytm">Paytm Number</label>
                            <input class="form-control" id="paytm" name="paytm" type="text" value="{{ old('paytm', $user->paytm) }}" />
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Update User</button>
                </div>
            </div>
        </form>
    </div>
@endsection
