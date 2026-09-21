@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="user-plus"></i></div>
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
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            {{-- Personal Details --}}
            <div class="card mb-4">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" name="name" type="text" placeholder="Enter name" value="{{ old('name') }}" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="phone">Phone Number <span class="text-danger">*</span></label>
                            <input class="form-control" id="phone" name="phone" type="text" placeholder="Enter phone" value="{{ old('phone') }}" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="password">Password <span class="text-danger">*</span></label>
                            <input class="form-control" id="password" name="password" type="password" placeholder="Enter password" required />
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="gender">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="city">City</label>
                            <input class="form-control" id="city" name="city" type="text" placeholder="Enter city" value="{{ old('city') }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="balance">Initial Balance <span class="text-danger">*</span></label>
                            <input class="form-control" id="balance" name="balance" type="number" value="{{ old('balance', 0) }}" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small mb-1" for="address">Full Address</label>
                        <input class="form-control" id="address" name="address" type="text" placeholder="Enter address" value="{{ old('address') }}" />
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
                            <input class="form-control" id="bank" name="bank" type="text" placeholder="e.g. SBI" value="{{ old('bank') }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="acc">Account Number</label>
                            <input class="form-control" id="acc" name="acc" type="text" placeholder="Enter account no" value="{{ old('acc') }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="ifsc">IFSC Code</label>
                            <input class="form-control" id="ifsc" name="ifsc" type="text" placeholder="e.g. SBIN000123" value="{{ old('ifsc') }}" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1" for="holdername">Account Holder Name</label>
                            <input class="form-control" id="holdername" name="holdername" type="text" placeholder="Holder Name" value="{{ old('holdername') }}" />
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="phonepe">PhonePe Number</label>
                            <input class="form-control" id="phonepe" name="phonepe" type="text" placeholder="PhonePe No." value="{{ old('phonepe') }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="gpay">Google Pay Number</label>
                            <input class="form-control" id="gpay" name="gpay" type="text" placeholder="GPay No." value="{{ old('gpay') }}" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1" for="paytm">Paytm Number</label>
                            <input class="form-control" id="paytm" name="paytm" type="text" placeholder="Paytm No." value="{{ old('paytm') }}" />
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Create User</button>
                </div>
            </div>
        </form>
    </div>
@endsection
