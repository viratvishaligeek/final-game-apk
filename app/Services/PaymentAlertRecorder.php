<?php

namespace App\Services;

use App\Events\AdminPaymentFailedPaymentAlert;
use App\Models\AdminPaymentAlert;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentAlertRecorder
{
    public function record(WalletRequest $request, array $context = []): ?AdminPaymentAlert
    {
        if ($request->payment_method !== 'gateway' || $request->request_type !== 'credit') {
            return null;
        }

        $attemptId = (string) ($context['attempt_id'] ?? $request->client_txn_id ?? ('WALLET-' . $request->id));
        try {
            return $this->persist(
                (int) $request->user_id,
                (float) $request->amount,
                $attemptId,
                (int) $request->id,
                $context
            );
        } catch (Throwable $exception) {
            Log::critical('Automatic payment alert could not be persisted.', [
                'attempt_id' => $attemptId,
                'user_id' => $request->user_id,
                'diagnostic_reference' => $context['diagnostic_reference'] ?? null,
                'exception_class' => get_class($exception),
            ]);
            return null;
        }
    }

    public function recordAttempt(User $user, float $amount, string $attemptId, array $context = []): ?AdminPaymentAlert
    {
        try {
            return $this->persist((int) $user->id, $amount, $attemptId, null, $context);
        } catch (Throwable $exception) {
            Log::critical('Pre-request automatic payment alert could not be persisted.', [
                'attempt_id' => $attemptId,
                'user_id' => $user->id,
                'diagnostic_reference' => $context['diagnostic_reference'] ?? null,
                'exception_class' => get_class($exception),
            ]);
            return null;
        }
    }

    private function persist(int $userId, float $amount, string $attemptId, ?int $walletRequestId, array $context): AdminPaymentAlert
    {
        $message = $this->sanitize($context['message'] ?? 'Automatic payment processing failed.');
        $code = $this->sanitize($context['error_code'] ?? '');
        $httpStatus = isset($context['http_status']) ? (int) $context['http_status'] : null;
        $stage = $this->sanitize($context['stage'] ?? 'gateway_processing');
        $category = strtoupper((string) ($context['category'] ?? $this->classify($message, $code, $httpStatus, $stage)));
        $diagnosticReference = (string) ($context['diagnostic_reference'] ?? (string) Str::uuid());

        $normalizedMessage = preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower(trim($message)));
        $displayMessage = match (true) {
            $category !== 'PAYMENT_GATEWAY_CONFIGURATION_ERROR' => $message,
            $httpStatus === 401 => 'Payment Gateway Configuration Issue: Gateway authentication failed. Verify the API credentials and merchant account permissions.',
            $httpStatus === 403 => 'Payment Gateway Configuration Issue: Gateway access was denied. Verify the merchant account is active and its API plan/permissions are valid.',
            $normalizedMessage === 'active plan not found please buy plan' => 'Payment Gateway Configuration Issue: The gateway reports that no active plan is available. Verify the merchant API plan and account status.',
            default => 'Payment Gateway Configuration Issue: ' . $message . ' Please verify the gateway account configuration.',
        };

        $alert = AdminPaymentAlert::query()->firstOrNew(['attempt_id' => $attemptId]);
        $isNew = !$alert->exists;
        $alert->fill([
            'wallet_request_id' => $walletRequestId ?? $alert->wallet_request_id,
            'user_id' => $userId,
            'attempt_id' => $attemptId,
            'transaction_id' => $context['gateway_transaction_id'] ?? $alert->transaction_id,
            'gateway_order_id' => $context['gateway_order_id'] ?? $alert->gateway_order_id,
            'amount' => $amount,
            'gateway' => $this->sanitize($context['gateway'] ?? 'UPI Gateway'),
            'error_category' => $category,
            'error_code' => $code ?: null,
            'http_status' => $httpStatus,
            'processing_stage' => $stage,
            'failure_reason' => $displayMessage,
            'diagnostic_reference' => $diagnosticReference,
            'severity' => in_array($category, ['PAYMENT_GATEWAY_CONFIGURATION_ERROR', 'BACKEND_SYSTEM_ERROR'], true) ? 'critical' : 'high',
            'fingerprint' => hash('sha256', implode('|', [$category, $code, $stage, mb_strtolower($message)])),
            'occurrence_count' => (int) ($alert->occurrence_count ?: 0) + 1,
            'first_occurred_at' => $alert->first_occurred_at ?: now(),
            'latest_occurred_at' => now(),
            'failed_at' => now(),
        ]);
        $alert->save();
        Log::warning('Automatic payment error alert recorded.', [
            'alert_id' => $alert->id,
            'attempt_id' => $attemptId,
            'diagnostic_reference' => $diagnosticReference,
            'error_category' => $category,
            'processing_stage' => $stage,
        ]);

        if ($isNew) {
            try {
                AdminPaymentFailedPaymentAlert::dispatch($alert->id);
            } catch (Throwable $exception) {
                Log::error('Automatic payment alert was saved but realtime broadcast failed.', [
                    'alert_id' => $alert->id,
                    'attempt_id' => $attemptId,
                    'exception_class' => get_class($exception),
                ]);
            }
        }

        return $alert;
    }

    private function classify(string $message, string $code, ?int $httpStatus, string $stage): string
    {
        $normalized = mb_strtolower(trim($message));
        $exactPlanError = preg_replace('/[^a-z0-9]+/', ' ', $normalized);
        if ($exactPlanError === 'active plan not found please buy plan') {
            return 'PAYMENT_GATEWAY_CONFIGURATION_ERROR';
        }

        if (str_contains($stage, 'verification') || str_contains($stage, 'callback')) {
            return 'PAYMENT_VERIFICATION_ERROR';
        }

        if (str_contains($stage, 'exception') || str_contains($stage, 'database')) {
            return 'BACKEND_SYSTEM_ERROR';
        }

        if ($httpStatus === 401 || $httpStatus === 403) {
            return 'PAYMENT_GATEWAY_CONFIGURATION_ERROR';
        }

        if ($httpStatus !== null && $httpStatus >= 500) {
            return 'PAYMENT_GATEWAY_API_ERROR';
        }

        return 'PAYMENT_GATEWAY_API_ERROR';
    }

    private function sanitize(mixed $value): string
    {
        $text = is_scalar($value) ? (string) $value : 'Automatic payment processing failed.';
        $text = preg_replace('/(authorization\s*[:=]\s*bearer\s+)[^\s,;]+/i', '$1[redacted]', $text);
        $text = preg_replace('/((?:api[_ -]?key|access[_ -]?token|secret|password|otp|cvv|card[_ -]?number)\s*[:=]\s*)[^\s,;]+/i', '$1[redacted]', $text);
        return mb_substr(trim(strip_tags($text)), 0, 1000);
    }
}
