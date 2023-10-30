<?php

namespace App\Models;

class DebitCardPayment implements PaymentMethod
{
    const DEBIT_CARD = "debit_card";
    const STATUS = 'paid';

    const FEE = 2;

    public function getName(): string
    {
        return self::DEBIT_CARD;
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
