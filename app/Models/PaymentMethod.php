<?php

namespace App\Models;
interface PaymentMethod {

    public function getName(): string;
    public function getFee(): int;
    public function getStatusAfterPayment(): string;
}
