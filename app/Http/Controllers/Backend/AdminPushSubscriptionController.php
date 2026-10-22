<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPushSubscriptionController extends Controller
{
    public function __construct(private PushNotificationService $push) {}

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', 'in:web'],
        ]);

        $this->push->registerAdmin($data['token'], $request->user('admin'));

        return response()->json(['success' => true, 'message' => 'Admin background notifications enabled.']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        $this->push->unregisterAdmin($data['token'], $request->user('admin'));

        return response()->json(['success' => true, 'message' => 'Admin background notifications disabled.']);
    }
}
