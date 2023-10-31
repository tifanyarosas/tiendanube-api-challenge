<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\TransactionController;
use App\Http\Requests\TransactionCreationRequest;
use App\Http\Requests\SummaryGetRequest;
use App\Models\DebitCardPayment;
use App\Models\Payable;
use App\Repositories\PayableRepository;
use App\Services\TransactionService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    private MockInterface|TransactionService $service;
    private TransactionController $controller;
    private TransactionCreationRequest $request;
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(TransactionService::class);
        $this->controller = new TransactionController($this->service);
        $this->request = new TransactionCreationRequest([
            'value' => '100.10',
            'description' => 'Jumper Size M',
            'paymentMethod' => DebitCardPayment::DEBIT_CARD,
            'cardNumber' => '1234',
            'cardHolderName' => 'Tifany',
            'cardExpirationDate' => '10/27',
            'cardCvv' => '333',
        ]);
    }

    public function testCreateTransactionSuccessfully(): void
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

    public function testCreateTransactionFail(): void
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
        $service = app()->make(TransactionService::class, ['payableRepository' => $payableRepository]);

        $request = new SummaryGetRequest([
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $controller = new TransactionController($service);
        $response = $controller->index($request);
        $data = $response->getData(true);

        $this->assertEquals($expectedTotalFee, $data['totalFees']);
        $this->assertEquals($expectedTotalToGetPaid, $data['totalToGetPaid']);
        $this->assertEquals($expectedTotalPaid, $data['totalPaid']);
    }

    public static function payableProvider(): \Iterator
    {
        yield 'test without any payable' => [
            'payables' => [],
            'startDate' => '2023/10/31',
            'endDate' => '2023/11/31',
            'expectedTotalFee' => 0,
            'expectedTotalToGetPaid' => 0,
            'expectedTotalPaid' => 0,
        ];
        yield 'test with payables, all in the date range' => [
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
        ];
        yield 'test with payables, all out the date range' => [
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
        ];
        yield 'test with payables, some in the date range' => [
            'payables' => [
                self::buildPayable(100, 2, Payable::STATUS_PAID,  new \DateTime('2023/10/31')),
                self::buildPayable(200, 4, Payable::STATUS_WAITING_FUNDS, new \DateTime('2023/11/10')),
                self::buildPayable(100, 4, Payable::STATUS_PAID, new \DateTime('2023/10/31')),
                self::buildPayable(100, 4, Payable::STATUS_WAITING_FUNDS, new \DateTime('2023/12/31')),
            ],
            'startDate' => '2023/10/01',
            'endDate' => '2023/11/31',
            'expectedTotalFee' => 6,
            'expectedTotalToGetPaid' => 196,
            'expectedTotalPaid' => 194,
        ];
    }

    private static function buildPayable(float $subtotal, float $discount, string $status, \DateTime $date): Payable {
        $payable = new Payable();
        $payable->id = substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyz', ceil(4/strlen($x)) )),1,4);
        $payable->status = $status;
        $payable->total = $subtotal - $discount;
        $payable->subtotal = $subtotal;
        $payable->discount = $discount;
        $payable->createDate = $date;
        return $payable;
    }
}
