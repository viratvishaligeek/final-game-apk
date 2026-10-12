<?php

return [
    // Keep local private storage as the safe default for existing deployments.
    // Set SUPPORT_CHAT_DISK=s3 after configuring the private S3-compatible disk.
    'disk' => env('SUPPORT_CHAT_DISK', 'local'),
    'presigned_url_minutes' => max(1, (int) env('SUPPORT_CHAT_PRESIGNED_URL_MINUTES', 5)),
];
