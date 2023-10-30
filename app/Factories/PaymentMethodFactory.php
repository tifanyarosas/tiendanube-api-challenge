<?php

namespace App\Factories;

use App\Exceptions\InvalidPaymentMethodException;
use App\Models\CreditCardPayment;
use App\Models\DebitCardPayment;
use App\Models\PaymentMethod;

class PaymentMethodFactory {

    /**
     * @throws InvalidPaymentMethodException
     */
    static public function create(string $method): PaymentMethod {
        return match ($method) {
            CreditCardPayment::CREDIT_CARD => new CreditCardPayment(),
            DebitCardPayment::DEBIT_CARD => new DebitCardPayment(),
            default => throw new InvalidPaymentMethodException($method)
        };
    }
}
