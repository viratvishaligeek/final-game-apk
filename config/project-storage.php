<?php

return [
    // Existing deployments remain on Laravel's local public/private disks by default.
    // Set these disk names once to move new uploads to a configured compatible provider.
    'public_disk' => env('PROJECT_PUBLIC_STORAGE_DISK', 'public'),
    'private_disk' => env('PROJECT_PRIVATE_STORAGE_DISK', env('SUPPORT_CHAT_DISK', 'local')),
];
