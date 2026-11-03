<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminPaymentAlert;
use App\Models\AdminPaymentAlertRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentAlertController extends Controller
{
    public function pending(Request $request): JsonResponse
    {
        $adminId = (int) $request->user('admin')->id;

        $alerts = AdminPaymentAlert::query()
            ->with('user:id,name,phone')
            ->whereDoesntHave('reads', fn ($query) => $query->where('admin_id', $adminId))
            ->orderBy('failed_at')
            ->limit(100)
            ->get()
            ->map(fn (AdminPaymentAlert $alert) => [
                'id' => $alert->id,
                'user_name' => $alert->user?->name ?? 'Unknown user',
                'user_id' => $alert->user_id,
                'phone' => $alert->user?->phone,
                'transaction_id' => $alert->transaction_id,
                'amount' => (float) $alert->amount,
                'gateway' => $alert->gateway,
                'failure_reason' => $alert->failure_reason,
                'failed_at' => $alert->failed_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $alerts]);
    }

    public function acknowledge(Request $request, AdminPaymentAlert $alert): JsonResponse
    {
        AdminPaymentAlertRead::query()->firstOrCreate(
            [
                'admin_payment_alert_id' => $alert->id,
                'admin_id' => (int) $request->user('admin')->id,
            ],
            ['read_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}
