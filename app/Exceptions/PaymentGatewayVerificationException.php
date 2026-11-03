<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentGatewayVerificationException extends RuntimeException
{
    public function __construct(
        public readonly string $category,
        public readonly string $errorCode,
        public readonly ?int $httpStatus = null,
        string $message = 'Gateway payment verification failed.'
    ) {
        parent::__construct($message);
    }
}
