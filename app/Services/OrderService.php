<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OrderService {

    const SERVER_URL = 'http://0.0.0.0:8080/';
    public function createOrder(Transaction $transaction): array {
        $transactionResponse = $this->createTransaction($transaction);
        if (!$transactionResponse->created()) {
            return [
                'status' => $transactionResponse->status(),
                'reason' => $transactionResponse->reason(),
            ];
        }
        $payableResponse = $this->createPayable($transaction);

        if (!$payableResponse->created()) {
            $this->deleteTransaction($transactionResponse->json(['id']));
            return [
                'status' => $transactionResponse->status(),
                'reason' => $transactionResponse->reason(),
            ];
        }

        return [
            'status' => $transactionResponse->status(),
            'transactionId' => $transactionResponse->json('id'),
            'payableId' => $payableResponse->json('id'),
        ];
    }

    private function createTransaction(Transaction $transaction): Response
    {
        return Http::post(
            self::SERVER_URL . 'transactions',
            [
                'value' => $this->formatNumber($transaction->value),
                'description' => $transaction->description,
                'method' => $transaction->paymentMethod->getName(),
                'cardNumber' => $transaction->card->number,
                'cardHolderName' => $transaction->card->owner,
                'cardExpirationDate' => $transaction->card->expirationDate,
                'cardCvv' => (string) $transaction->card->cvv,
            ]
        );
    }

    private function createPayable(Transaction $transaction): Response {
        $discount = $transaction->paymentMethod->getFee() * $transaction->value / 100;
        return Http::post(
            self::SERVER_URL . 'payables',
            [
                'status' => $transaction->paymentMethod->getStatusAfterPayment(),
                'create_date' => date_format(new \DateTime(), 'd/m/Y'),
                'subtotal' => $this->formatNumber($transaction->value),
                'discount' => $this->formatNumber($discount),
                'total' => $this->formatNumber($transaction->value - $discount),
            ]
        );
    }

    private function deleteTransaction(string $transactionId): void {
        Http::delete(
            self::SERVER_URL . 'transactions/' . $transactionId,
        );
    }

    private function formatNumber(float $number): string {
        return number_format($number, 2, '.', '');
    }
}
