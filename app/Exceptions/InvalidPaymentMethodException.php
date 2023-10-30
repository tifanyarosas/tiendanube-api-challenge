<?php

namespace App\Exceptions;

class InvalidPaymentMethodException extends \Exception {

    public function __construct(string $method)
    {
        parent::__construct('Payment method not supported: ' . $method);
    }
}
