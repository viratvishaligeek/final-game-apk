<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'upi_gateway' => [
        'key' => env('UPI_GATEWAY_KEY'),
        'create_order_url' => env(
            'UPI_GATEWAY_CREATE_ORDER_URL',
            'https://api.ekqr.in/api/create_order'
        ),
        'status_url' => env(
            'UPI_GATEWAY_STATUS_URL',
            'https://api.ekqr.in/api/check_order_status'
        ),
        'return_url' => env('UPI_GATEWAY_RETURN_URL'),
        'webhook_url' => env('UPI_GATEWAY_WEBHOOK_URL'),
    ],

    'manual_upi' => [
        'upi_id' => env('MANUAL_UPI_ID'),
        'name' => env('MANUAL_UPI_NAME'),
        'qr_url' => env('MANUAL_UPI_QR_URL'),
    ],

    /*
     * Generic JSON SMS endpoint used for password-reset OTP delivery.
     * The endpoint must accept a bearer token and JSON fields: to, message, sender.
     */
    'sms_gateway' => [
        'url' => env('SMS_GATEWAY_URL'),
        'token' => env('SMS_GATEWAY_TOKEN'),
        'sender' => env('SMS_GATEWAY_SENDER_ID'),
    ],

];
