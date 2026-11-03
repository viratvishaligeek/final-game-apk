<?php

namespace Tests\Unit;

use App\Services\PaymentAlertRecorder;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PaymentAlertRecorderTest extends TestCase
{
    private function invokePrivate(string $method, array $arguments): mixed
    {
        $recorder = new PaymentAlertRecorder();
        $reflection = new ReflectionMethod($recorder, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($recorder, $arguments);
    }

    public function test_exact_active_plan_error_is_classified_as_gateway_configuration(): void
    {
        $category = $this->invokePrivate('classify', [
            'Active Plan not found. Please Buy Plan',
            '',
            403,
            'order_creation',
        ]);

        $this->assertSame('PAYMENT_GATEWAY_CONFIGURATION_ERROR', $category);
    }

    public function test_unrelated_plan_word_is_not_assumed_to_be_a_subscription_failure(): void
    {
        $category = $this->invokePrivate('classify', [
            'The selected payment plan is not supported for this amount.',
            '',
            null,
            'order_creation',
        ]);

        $this->assertSame('PAYMENT_GATEWAY_API_ERROR', $category);
    }

    public function test_gateway_authentication_status_is_configuration_error(): void
    {
        $this->assertSame('PAYMENT_GATEWAY_CONFIGURATION_ERROR', $this->invokePrivate('classify', [
            'Unauthorized',
            '',
            401,
            'order_creation',
        ]));
    }

    public function test_gateway_server_error_is_classified_as_api_error(): void
    {
        $this->assertSame('PAYMENT_GATEWAY_API_ERROR', $this->invokePrivate('classify', [
            'Service unavailable',
            '',
            503,
            'order_creation',
        ]));
    }

    public function test_verification_and_backend_exception_have_distinct_categories(): void
    {
        $this->assertSame('PAYMENT_VERIFICATION_ERROR', $this->invokePrivate('classify', [
            'Response could not be verified',
            '',
            null,
            'payment_verification',
        ]));
        $this->assertSame('BACKEND_SYSTEM_ERROR', $this->invokePrivate('classify', [
            'Unexpected exception',
            '',
            null,
            'order_creation_exception',
        ]));
    }

    public function test_sanitizer_redacts_credentials_and_authentication_secrets(): void
    {
        $sanitized = $this->invokePrivate('sanitize', [
            'Gateway rejected request api_key=abc123 Authorization: Bearer token456 OTP=123456',
        ]);

        $this->assertStringNotContainsString('abc123', $sanitized);
        $this->assertStringNotContainsString('token456', $sanitized);
        $this->assertStringNotContainsString('123456', $sanitized);
        $this->assertStringContainsString('[redacted]', $sanitized);
    }
}
