<?php

namespace App\Models;

class Transaction {

    public string $id;

    public float $value;
    public string $description;
    public PaymentMethod $paymentMethod;
    public Card $card;

}
