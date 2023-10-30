<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\OrderController;
use App\Http\Requests\OrderPostRequest;
use App\Models\DebitCardPayment;
use App\Services\OrderService;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    private MockInterface|OrderService $service;
    private OrderController $controller;
    private OrderPostRequest $request;
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(OrderService::class);
        $this->controller = new OrderController($this->service);
        $this->request = new OrderPostRequest([
            'value' => '100.10',
            'description' => 'Jumper Size M',
            'paymentMethod' => DebitCardPayment::DEBIT_CARD,
            'cardNumber' => '1234',
            'cardHolderName' => 'Tifany',
            'cardExpirationDate' => '10/27',
            'cardCvv' => '333',
        ]);
    }

    public function testCreateOrderSuccessfully(): void
    {
        $this->service
            ->expects($this->once())
            ->method('createOrder')
            ->willReturn([
                'transactionId' => 'asd',
                'payableId' => 'zxc',
            ]);

        $response = $this->controller->create($this->request);
        $this->assertEquals(201, $response->status());
        $this->assertEquals('asd', $response->getData(true)['transactionId']);
        $this->assertEquals('zxc', $response->getData(true)['payableId']);
    }

    public function testCreateOrderFail(): void
    {
        $this->service
            ->expects($this->once())
            ->method('createOrder')
            ->willReturn(null);

        $response = $this->controller->create($this->request);
        $this->assertEquals(400, $response->status());
    }
}
