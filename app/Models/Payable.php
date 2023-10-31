<?php

namespace App\Models;

class Payable
{
    const STATUS_PAID = 'paid';
    const STATUS_WAITING_FUNDS = 'waiting_funds';

    public string $id;
    public string $status;
    public float $subtotal;
    public float $discount;
    public float $total;
    public \Datetime $creationDate;
}
