<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function broadcast(Request $request, PushNotificationService $push): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $sent = $push->broadcast($data['subject'], $data['message']);

        return back()->with('success', "Public notification queued. Push sent to {$sent} subscriber(s).");
    }
}
