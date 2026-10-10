@extends('backend.include.layout')

@section('page-header')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="sliders"></i>
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
        <form action="{{ route('admin.mobile-app.update_limits') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    Transaction & Bid Limits
                </div>

                <div class="card-body">
                    <div class="row gx-3">

                        <div class="col-md-3 mb-4">
                            <label for="min_deposit" class="small mb-1">
                                Minimum Deposit
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" id="min_deposit"
                                name="min_deposit" value="{{ old('min_deposit', $settings['min_deposit'] ?? 0) }}">
                        </div>

                        <div class="col-md-3 mb-4">
                            <label for="max_deposit" class="small mb-1">
                                Maximum Deposit
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" id="max_deposit"
                                name="max_deposit" value="{{ old('max_deposit', $settings['max_deposit'] ?? 0) }}">
                            <div class="form-text">Use 0 for no maximum.</div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <label for="min_withdraw" class="small mb-1">
                                Minimum Withdrawal
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" id="min_withdraw"
                                name="min_withdraw" value="{{ old('min_withdraw', $settings['min_withdraw'] ?? 0) }}">
                        </div>

                        <div class="col-md-3 mb-4">
                            <label for="max_withdraw" class="small mb-1">
                                Maximum Withdrawal
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" id="max_withdraw"
                                name="max_withdraw" value="{{ old('max_withdraw', $settings['max_withdraw'] ?? 0) }}">
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="min_bid_amount_jodi" class="small mb-1">
                                Minimum Bid Amount Jodi
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="min_bid_amount_jodi" name="min_bid_amount_jodi"
                                value="{{ old('min_bid_amount_jodi', $settings['min_bid_amount_jodi'] ?? 0) }}">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="min_bid_amount_haruf" class="small mb-1">
                                Minimum Bid Amount Haruf
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="min_bid_amount_haruf" name="min_bid_amount_haruf"
                                value="{{ old('min_bid_amount_haruf', $settings['min_bid_amount_haruf'] ?? 0) }}">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="max_bid_amount_jodi" class="small mb-1">
                                Maximum Bid Amount Jodi
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="max_bid_amount_jodi" name="max_bid_amount_jodi"
                                value="{{ old('max_bid_amount_jodi', $settings['max_bid_amount_jodi'] ?? 0) }}">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="max_bid_amount_haruf" class="small mb-1">
                                Maximum Bid Amount Haruf
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="max_bid_amount_haruf" name="max_bid_amount_haruf"
                                value="{{ old('max_bid_amount_haruf', $settings['max_bid_amount_haruf'] ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Payment Notices
                </div>

                <div class="card-body">
                    <div class="row gx-3">

                        <div class="col-md-6 mb-4">
                            <label for="add_money_notice" class="small mb-1">
                                Add Money Notice / Content
                            </label>
                            <textarea class="form-control" id="add_money_notice" name="add_money_notice" rows="5"
                                placeholder="Enter notice or instructions for adding money...">{{ old('add_money_notice', $settings['add_money_notice'] ?? '') }}</textarea>
                            <div class="form-text">
                                This message can be displayed on the Add Money screen.
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="withdraw_money_notice" class="small mb-1">
                                Withdraw Money Notice / Content
                            </label>
                            <textarea class="form-control" id="withdraw_money_notice" name="withdraw_money_notice" rows="5"
                                placeholder="Enter withdrawal rules or important information...">{{ old('withdraw_money_notice', $settings['withdraw_money_notice'] ?? '') }}</textarea>
                            <div class="form-text">
                                This message can be displayed on the Withdrawal screen.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Payment Gateway Settings
                </div>

                <div class="card-body">
                    <div class="row gx-3">

                        <div class="col-md-6 mb-4">
                            <label for="api_key" class="small mb-1">
                                API Key
                            </label>
                            <input type="password" class="form-control" id="api_key" name="api_key"
                                value="{{ old('api_key') }}" placeholder="Leave blank to keep the current API key"
                                autocomplete="new-password">
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="webhook_url" class="small mb-1">
                                Webhook URL
                            </label>
                            <input type="url" class="form-control" id="webhook_url" name="webhook_url"
                                value="{{ old('webhook_url', $settings['webhook_url'] ?? '') }}"
                                placeholder="https://example.com/payment/webhook">
                        </div>
                        <hr>
                        <div class="col-md-6 mb-4">
                            <label for="webhook_url" class="small mb-1">
                                Manual Upi Id
                            </label>
                            <input type="text" class="form-control" id="manual_upi_id" name="manual_upi_id"
                                value="{{ old('manual_upi_id', $settings['manual_upi_id'] ?? '') }}"
                                placeholder="manual upi id">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="webhook_url" class="small mb-1">
                                Manual Upi Name
                            </label>
                            <input type="text" class="form-control" id="manual_upi_name" name="manual_upi_name"
                                value="{{ old('manual_upi_name', $settings['manual_upi_name'] ?? '') }}"
                                placeholder="manual upi name">
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="payment_bar_code" class="small mb-1">
                                Payment Barcode / QR Code
                            </label>

                            <input type="file" class="form-control" id="payment_bar_code" name="payment_bar_code"
                                accept="image/png,image/jpeg,image/jpg,image/webp">

                            <div class="form-text">
                                Recommended: JPG, PNG or WEBP. Maximum size: 2MB.
                            </div>

                            @if (!empty($settings['payment_bar_code']))
                                <div class="mt-3">
                                    <div class="small text-muted mb-2">
                                        Current Barcode
                                    </div>

                                    <div class="border rounded p-2 d-inline-block bg-light">
                                        <img src="{{ asset('uploads/payment/' . $settings['payment_bar_code']) }}"
                                            alt="Payment Barcode"
                                            style="max-width: 220px; max-height: 220px; object-fit: contain;">
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <button class="btn btn-primary" type="submit">
                    <i data-feather="save" class="me-1"></i>
                    Save Settings
                </button>
            </div>
        </form>

    </div>
@endsection
