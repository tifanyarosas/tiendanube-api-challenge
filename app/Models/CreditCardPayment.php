<?php

namespace App\Models;

class CreditCardPayment implements PaymentMethod
{
    const CREDIT_CARD = "credit_card";
    const STATUS = 'waiting_funds';
    const FEE = 4;

    public function getName(): string
    {
        return self::CREDIT_CARD;
    }

    public function getFee(): int
    {
        return self::FEE;
    }

    public function getStatusAfterPayment(): string
    {
        return self::STATUS;
    }
}
