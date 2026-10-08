@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">

                            <div class="page-header-icon"><i data-feather="phone"></i>
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
        <form action="{{ route('admin.mobile-app.update_contact_details') }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    Contact Information
                </div>

                <div class="card-body">
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="small mb-1">Phone Number (with
                                +91)
                            </label>
                            <input type="text" class="form-control" id="contact_phone"
                                name="contact_phone"value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}"
                                placeholder="Enter phone number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_whatsapp" class="small mb-1">WhatsApp Number (with
                                +91)
                            </label>
                            <input type="text" class="form-control" id="contact_whatsapp"
                                name="contact_whatsapp"value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '') }}"
                                placeholder="Enter WhatsApp number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_telegram" class="small mb-1">Telegram
                            </label>
                            <input type="text" class="form-control" id="contact_telegram"
                                name="contact_telegram"value="{{ old('contact_telegram', $settings['contact_telegram'] ?? '') }}"
                                placeholder="@username or Telegram URL">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="small mb-1">Email
                            </label>
                            <input type="email" class="form-control" id="contact_email"
                                name="contact_email"value="{{ old('contact_email', $settings['contact_email'] ?? '') }}"
                                placeholder="support@example.com">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="contact_message" class="small mb-1">Content / Contact Message /
                                Support Line
                            </label>

                            <textarea class="form-control" id="contact_message" name="contact_message" rows="3" placeholder="Enter address">{{ old('contact_message', $settings['contact_message'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary" type="submit">
                    <i data-feather="save" class="me-1"></i>
                    Save Contact Details
                </button>
            </div>
        </form>
    </div>
@endsection
