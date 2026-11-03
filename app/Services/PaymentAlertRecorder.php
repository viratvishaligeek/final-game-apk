<?php

namespace App\\Services;

use App\\Events\\AdminPaymentFailedPaymentAlert;
use App\\Models\\AdminPaymentAlert;
use App\\Models\\WalletRequest;
use Illuminate\\Support\\Str;
use Throwable;

class PaymentAlertRecorder
{
    public function record(WalletRequest $request, array $context = []): ?AdminPaymentAlert
    {
        if ($request->payment_method !== 'gateway' || $request->request_type !== 'credit') {
            return null;
        }

        $message = $this->sanitize($context['message'] ?? 'Automatic payment processing failed.');
        $code = $this->sanitize($context['error_code'] ?? '');
        $httpStatus = isset($context['http_status']) ? (int) $context['http_status'] : null;
        $category = strtoupper((string) ($context['category'] ?? $this->classify($message, $code, $httpStatus, $context['stage'] ?? '')));
        $stage = $this->sanitize($context['stage'] ?? 'gateway_processing');
        $diagnosticReference = (string) ($context['diagnostic_reference'] ?? (string) Str::uuid());

        $configurationMessage = $category === 'PAYMENT_GATEWAY_CONFIGURATION_ERROR'
            ? 'Payment Gateway Configuration Issue: Merchant API plan may have expired or become inactive. Please verify the gateway account.'
            : $message;

        $alert = AdminPaymentAlert::query()->firstOrNew([
            'wallet_request_id' => $request->id,
        ]);

        $isNew = !$alert->exists;
        $alert->fill([
            'user_id' => $request->user_id,
            'attempt_id' => (string) ($context['attempt_id'] ?? $request->client_txn_id ?? ('WALLET-' . $request->id)),
            'transaction_id' => $context['gateway_transaction_id'] ?? $alert->transaction_id,
            'gateway_order_id' => $context['gateway_order_id'] ?? $alert->gateway_order_id,
            'amount' => $request->amount,
            'gateway' => $this->sanitize($context['gateway'] ?? 'UPI Gateway'),
            'error_category' => $category,
            'error_code' => $code ?: null,
            'http_status' => $httpStatus,
            'processing_stage' => $stage,
            'failure_reason' => $configurationMessage,
            'diagnostic_reference' => $diagnosticReference,
            'severity' => in_array($category, ['PAYMENT_GATEWAY_CONFIGURATION_ERROR', 'BACKEND_SYSTEM_ERROR'], true) ? 'critical' : 'high',
            'fingerprint' => hash('sha256', implode('|', [$category, $code, $stage, mb_strtolower($message)])),
            'occurrence_count' => (int) ($alert->occurrence_count ?: 0) + 1,
            'first_occurred_at' => $alert->first_occurred_at ?: now(),
            'latest_occurred_at' => now(),
            'failed_at' => now(),
        ]);
        $alert->save();

        if ($isNew) {
            AdminPaymentFailedPaymentAlert::dispatch($alert->id);
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

        if (in_array(strtoupper($code), ['NO_ACTIVE_PLAN', 'PLAN_EXPIRED', 'MERCHANT_INACTIVE', 'INVALID_API_CREDENTIALS', 'AUTHENTICATION_FAILED'], true)) {
            return 'PAYMENT_GATEWAY_CONFIGURATION_ERROR';
        }

        if (str_contains($stage, 'verification') || str_contains($stage, 'callback')) {
            return 'PAYMENT_VERIFICATION_ERROR';
        }

        if ($httpStatus === 401 || $httpStatus === 403) {
            return 'PAYMENT_GATEWAY_API_ERROR';
        }

        if ($httpStatus !== null && $httpStatus >= 500) {
            return 'PAYMENT_GATEWAY_API_ERROR';
        }

        if (str_contains($stage, 'exception') || str_contains($stage, 'database')) {
            return 'BACKEND_SYSTEM_ERROR';
        }

        return 'PAYMENT_GATEWAY_API_ERROR';
    }

    private function sanitize(mixed $value): string
    {
        $text = is_scalar($value) ? (string) $value : 'Automatic payment processing failed.';
        $text = preg_replace('/(authorization\\s*[:=]\\s*bearer\\s+)[^\\s,;]+/i', '$1[redacted]', $text);
        $text = preg_replace('/((?:api[_ -]?key|access[_ -]?token|secret|password|otp|cvv|card[_ -]?number)\\s*[:=]\\s*)[^\\s,;]+/i', '$1[redacted]', $text);
        return mb_substr(trim(strip_tags($text)), 0, 1000);
    }
}
