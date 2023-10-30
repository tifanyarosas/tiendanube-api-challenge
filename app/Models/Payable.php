<?php

namespace App\Models;

class Payable
{

    public string $id;
    public float $subtotal;
    public float $discount;
    public float $total;
    public \Datetime $creationDate;
}
