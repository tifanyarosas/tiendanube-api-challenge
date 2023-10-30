<?php

namespace Tests\Unit\Factories;

use App\Exceptions\InvalidPaymentMethodException;
use App\Factories\PaymentMethodFactory;
use App\Models\CreditCardPayment;
use App\Models\DebitCardPayment;
use PHPUnit\Framework\TestCase;

class PaymentMethodFactoryTest extends TestCase
{
    public function testCreateDebitCardPayment(): void
    {
        $method = PaymentMethodFactory::create(DebitCardPayment::DEBIT_CARD);
        $this->assertEquals(DebitCardPayment::DEBIT_CARD, $method->getName());
        $this->assertEquals(DebitCardPayment::STATUS, $method->getStatusAfterPayment());
        $this->assertEquals(DebitCardPayment::FEE, $method->getFee());
    }

    public function testCreateCreateCardPayment(): void
    {
        $method = PaymentMethodFactory::create(CreditCardPayment::CREDIT_CARD);
        $this->assertEquals(CreditCardPayment::CREDIT_CARD, $method->getName());
        $this->assertEquals(CreditCardPayment::STATUS, $method->getStatusAfterPayment());
        $this->assertEquals(CreditCardPayment::FEE, $method->getFee());
    }

    public function testInvalidPaymentMethodCreation(): void
    {
        $method = 'other';
        $this->expectException(InvalidPaymentMethodException::class);
        PaymentMethodFactory::create($method);
    }
}
