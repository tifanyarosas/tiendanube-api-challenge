<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\OrderController;
use App\Http\Requests\OrderPostRequest;
use App\Models\DebitCardPayment;
use App\Repositories\PayableRepository;
use App\Repositories\TransactionRepository;
use App\Services\OrderService;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    private OrderService $service;
    private OrderController $controller;
    private TransactionRepository $transactionRepository;
    private PayableRepository $payableRepository;
    private OrderPostRequest $request;
    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionRepository = new TransactionRepository();
        $this->payableRepository = new PayableRepository();
        $this->service = new OrderService($this->transactionRepository, $this->payableRepository);
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
        $response = $this->controller->create($this->request);
        $this->assertEquals(201, $response->status());
        $transactionId = $response->getData(true)['transactionId'];
        $payableId = $response->getData(true)['payableId'];

        $transaction = $this->transactionRepository->getById($transactionId);
        $this->assertEquals($transactionId, $transaction->id);
        $this->assertEquals($this->request->value, $transaction->value);
        $this->assertEquals($this->request->description, $transaction->description);
        $this->assertEquals($this->request->paymentMethod, $transaction->paymentMethod->getName());
        $this->assertEquals($this->request->cardNumber, $transaction->card->number);
        $this->assertEquals($this->request->cardHolderName, $transaction->card->owner);
        $this->assertEquals($this->request->cardExpirationDate, $transaction->card->expirationDate);
        $this->assertEquals($this->request->cardCvv, $transaction->card->cvv);

        $payable = $this->payableRepository->getById($payableId);
        $this->assertEquals($payableId, $payable->id);
        $this->assertEquals(
            number_format($this->request->value * 0.98, 2, '.', ''),
            $payable->total
        );
        $this->assertEquals($this->request->value, $payable->subtotal);
        $this->assertEquals(
            number_format($this->request->value * 0.02, 2, '.', ''),
            $payable->discount
        );
    }
}
