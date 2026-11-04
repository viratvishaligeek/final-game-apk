<?php

return [
    // Foreground clients heartbeat every 15 seconds. A missing lease is
    // reconciled by the scheduled presence:expire command.
    'heartbeat_interval_seconds' => (int) env('PRESENCE_HEARTBEAT_INTERVAL', 15),
    'heartbeat_timeout_seconds' => (int) env('PRESENCE_HEARTBEAT_TIMEOUT', 45),

    // Keep a user online briefly while switching apps, then expire the
    // background session if the app does not return to the foreground.
    'background_grace_seconds' => (int) env('PRESENCE_BACKGROUND_GRACE', 90),

    // A foreground app with no user interaction for this period is idle.
    'idle_timeout_seconds' => (int) env('PRESENCE_IDLE_TIMEOUT', 300),
];
