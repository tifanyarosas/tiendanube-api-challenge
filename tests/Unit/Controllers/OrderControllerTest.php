<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\OrderController;
use App\Http\Requests\OrderPostRequest;
use App\Http\Requests\SummaryGetRequest;
use App\Models\DebitCardPayment;
use App\Models\Payable;
use App\Repositories\PayableRepository;
use App\Services\OrderService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('payableProvider')]
    public function testGetSummary(
        array $payables,
        string $startDate,
        string $endDate,
        float $expectedTotalFee,
        float $expectedTotalToGetPaid,
        float $expectedTotalPaid,
    ): void {
        $payableRepository = $this->createMock(PayableRepository::class);
        $payableRepository->expects($this->once())
            ->method('getAll')
            ->willReturn($payables);
        $service = app()->make(OrderService::class, ['payableRepository' => $payableRepository]);

        $request = new SummaryGetRequest([
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $controller = new OrderController($service);
        $response = $controller->index($request);
        $data = $response->getData(true);

        $this->assertEquals($expectedTotalFee, $data['totalFees']);
        $this->assertEquals($expectedTotalToGetPaid, $data['totalToGetPaid']);
        $this->assertEquals($expectedTotalPaid, $data['totalPaid']);
    }

    public static function payableProvider(): array
    {
        return [
            [
                'payables' => [],
                'startDate' => '2023/10/31',
                'endDate' => '2023/11/31',
                'expectedTotalFee' => 0,
                'expectedTotalToGetPaid' => 0,
                'expectedTotalPaid' => 0,
            ],
            [
                'payables' => [
                    self::buildPayable(100, 2, Payable::STATUS_PAID,  new \DateTime('2023/10/31')),
                    self::buildPayable(200, 4, Payable::STATUS_WAITING_FUNDS, new \DateTime('2023/11/10')),
                    self::buildPayable(100, 4, Payable::STATUS_PAID, new \DateTime('2023/10/31')),
                ],
                'startDate' => '2023/10/31',
                'endDate' => '2023/11/31',
                'expectedTotalFee' => 6,
                'expectedTotalToGetPaid' => 196,
                'expectedTotalPaid' => 194,
            ],
            [
                'payables' => [
                    self::buildPayable(100, 2, Payable::STATUS_PAID,  new \DateTime('2023/10/31')),
                    self::buildPayable(200, 4, Payable::STATUS_WAITING_FUNDS, new \DateTime('2023/11/10')),
                    self::buildPayable(100, 4, Payable::STATUS_PAID, new \DateTime('2023/10/31')),
                ],
                'startDate' => '2023/01/31',
                'endDate' => '2023/02/31',
                'expectedTotalFee' => 0,
                'expectedTotalToGetPaid' => 0,
                'expectedTotalPaid' => 0,
            ],
        ];
    }

    private static function buildPayable(float $subtotal, float $discount, string $status, \DateTime $date): Payable {
        $payable = new Payable();
        $payable->id = substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyz', ceil(4/strlen($x)) )),1,4);
        $payable->status = $status;
        $payable->total = $subtotal - $discount;
        $payable->subtotal = $subtotal;
        $payable->discount = $discount;
        $payable->creationDate = $date;
        return $payable;
    }
}
