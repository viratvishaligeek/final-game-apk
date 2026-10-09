<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct(private PushNotificationService $push) {}

    public function subscribePublic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', 'string', 'in:web,android,ios'],
        ]);

        $this->push->register($data['token'], $data['platform']);

        return response()->json(['success' => true, 'message' => 'Notifications enabled.']);
    }

    public function subscribeUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', 'string', 'in:web,android,ios'],
        ]);

        $this->push->register($data['token'], $data['platform'], $request->user());

        return response()->json(['success' => true, 'message' => 'Notifications enabled for this account.']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        \App\Models\PushSubscription::query()->where('token_hash', hash('sha256', $data['token']))->delete();

        return response()->json(['success' => true, 'message' => 'Notifications disabled.']);
    }
}
