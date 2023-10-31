<?php

namespace App\Repositories;

use App\Models\Payable;
use App\Models\PaymentMethod;
use Cassandra\Date;
use Illuminate\Support\Facades\Http;

class PayableRepository {

    const SERVER_URL = 'http://0.0.0.0:8080/payables/';

    public function create(PaymentMethod $paymentMethod, float $value): string|null {
        $discount = $paymentMethod->getFee() * $value / 100;
        $response = Http::post(
            self::SERVER_URL,
            [
                'status' => $paymentMethod->getStatusAfterPayment(),
                'create_date' => date_format(new \DateTime(), 'd/m/Y'),
                'subtotal' => $this->formatNumber($value),
                'discount' => $this->formatNumber($discount),
                'total' => $this->formatNumber($value - $discount),
            ]
        );

        return $response->created() ? $response->json('id') : null;
    }

    public function getById(string $payableId): Payable {
        $response = Http::get(
            self::SERVER_URL . $payableId,
        );

        if ($response->notFound()) {
            throw new \Exception('Not found');
        }

        $payable = new Payable();
        $payable->id = $response->json('id');
        $payable->total = $response->json('total');
        $payable->subtotal = $response->json('subtotal');
        $payable->discount = $response->json('discount');
        $payable->creationDate = \DateTime::createFromFormat('d/m/Y', $response->json('create_date'));
        return $payable;
    }

    public function getAll(): array {
        $response = Http::get(
            self::SERVER_URL,
        );
        $payables = [];

        foreach ($response->collect() as $item) {
            $payable = new Payable();
            $payable->id = $item['id'];
            $payable->total = $item['total'];
            $payable->subtotal = $item['subtotal'];
            $payable->discount = $item['discount'];
            $payable->creationDate = \DateTime::createFromFormat('d/m/Y', $item['create_date']);
            $payables[] = $payable;
        }

        return $payables;
    }

    private function formatNumber(float $number): string {
        return number_format($number, 2, '.', '');
    }
}
